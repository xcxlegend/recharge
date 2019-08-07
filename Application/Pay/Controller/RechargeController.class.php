<?php
/**
 * Created by PhpStorm.
 * User: Legend.Xie
 * Date: 2019年7月30日
 */

namespace Pay\Controller;
use Common\Model\RedisCacheModel;
use Think\Log;


class RechargeController extends PayController
{
    protected $secret;
    public function __construct()
    {
        parent::__construct();
        $this->secret = C('RECHARGE_API_SECRET');
    }

    public function index()
    {
        /*
        appcode:		身份标识 请询问管理员获取各自的标识
phone: 			号码
orderid:			来自话充平台的订单号
type:*/
        if (
            !$this->request['appcode'] 
            ||  !$this->request['phone'] 
            ||  !$this->request['orderid'] 
            ||  !$this->request['type'] 
        ) {
            $this->result_error('param error', true);
            return;
        }

        $sign_param = [
            'appcode'   => $this->request['appcode'],
            'phone'     => $this->request['phone'],
            'orderid'   => $this->request['orderid'],
            'type'      => $this->request['type']
        ];

        $sign = createSign(
            $this->secret,
            $sign_param
        );

        if ($sign !== $this->request['sign']) {
            Log::write("sign:" . $sign);
            return $this->result_error('sign error');
        }
        $channel = D('Common/Channel')->getByCode($this->request['appcode']);
        if (!$channel) {
            $this->result_error('channel code err', true);
            return;
        }
        $channel_id = $channel['id'];
        
        /*
 `phone` char(20) NOT NULL DEFAULT '',
  `cid` int(11) NOT NULL DEFAULT '0' COMMENT '通道ID',
  `time` int(11) NOT NULL DEFAULT '0' COMMENT '创建时间',
  `channel` tinyint(1) NOT NULL DEFAULT '0' COMMENT '运营商',
  `pid` int(11) NOT NULL DEFAULT '0' COMMENT 'pool_id 号码商id',
  `count` int(11) NOT NULL DEFAULT '1' COMMENT '失败次数',
        */

        $data = [
            'phone'     => $this->request['phone'],
            'channel'   => $this->request['type'],
            'cid'       => $channel_id,
            'time'      => $this->timestamp,
            'count'     => 1,
            'orderid'   => $this->request['orderid']
        ];

/*        $order = M('Order')->where(['pay_orderid' => $this->request['orderid']])->find();
        if ($order && $order['pool_phone_id']) {
            $pool = M('PoolPhones')->find($order['pool_phone_id']);
            $data['pid'] = $pool['pid'];
        }*/

        M('Blacklist')->add($data);

        // cache
        $redisKey = "blacklist.phone." . $this->request['phone'];
        if (!$this->cache->get($redisKey)){
            $this->cache->set($redisKey, json_encode($data), 24*3600);
        }

        return $this->result_success('');
    }


}