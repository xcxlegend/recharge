<?php
/**
 * Created by PhpStorm.
 * User: Legend.Xie
 * Date: 2019/6/18
 * Time: 20:45
 */

namespace Pay\Controller;
use Common\Lib\BigPayLib;
use \Think\Log;
use Common\Lib\ChannelManagerLib;
use Common\Model\RedisCacheModel;


class PoolController extends PayController
{
    const WHITEIP = ['114.55.63.138','47.98.108.243','47.98.242.25','47.98.242.25','47.244.237.40'];

    public function __construct()
    {
        $ip = get_client_ip();
        print_r($ip);
        if(in_array($ip,self::WHITEIP)){
            echo $ip;
            return;
        }
        parent::__construct();
    }

    public function Index() {

    /*
     *
     * phone
        money
        notify_url
        channel
        out_trade_id
     *
     *  appkey: 提供给商户的身份标识appkey 16位
        phone: 电话号码
        money: 金额 (单位分)
        out_trade_id: 商户系统的订单ID 用于回调
        notify_url:  回调地址 用于充值完成后回调
        channel: 手机对应运营商 1=移动 2=电信 3=联调
        sign: 签名  签名方案按照 k=v&k2=v2 k升序形式进行 最后并上  &key=APIKEY (APIKEY为分配给商户的密钥)  做md5 转小写.  具体查看后面的签名说明
     */


        if (
            !$this->request['appkey']
        ||  !$this->request['phone']
        ||  !$this->request['money']
        ||  !$this->request['out_trade_id']
        ||  !$this->request['notify_url']
        ||  !$this->request['channel']
        ||  !$this->request['sign']
        ) {
            $this->result_error("param error", true);
            return;
        }

        $provider = M('PoolProvider')->where(['appkey' => $this->request['appkey']])->find();
        if (!$provider) {
            $this->result_error("no provider", true);
            return;
        }

        if (!$provider['status']) {
            $this->result_error("通道关闭", true);
            return;
        }

        D('Admin/PoolStatis')->setStatis($provider['id'],'do_order');

        $signArray = [
            "appkey"        => $this->request['appkey'],
            "phone"         => $this->request['phone'],
            "money"         => $this->request['money'],
            "out_trade_id"  => $this->request['out_trade_id'],
            "notify_url"    => $this->request['notify_url'],
            "channel"       => $this->request['channel'],
        ];

        $sign = createSign( $provider['appsecret'], $signArray );
        if ($sign != $this->request['sign']) {
            $this->result_error("sign error", $sign);
            return;
        }

        // 检查黑名单
        if ($this->cache->Client()->exists("blacklist.phone." . $this->request['phone'])){
            M('Blacklist')->where(['phone' => $this->request['phone']])->setInc('count');
            $this->result_error('phone in blacklist');
            return;
        }


        if (M('PoolPhones')->where(['out_trade_id' => $this->request['out_trade_id'], 'pid' => $provider['id']])->count()) {
            $this->result_error("out_trade_id exist", $sign);
            return;
        }

        $data = $signArray;
        unset($data['appkey']);
        $data['pid'] = $provider['id'];
        $data['order_id'] = createUUID('PL');

        //获取支付链接

        $randPay = M('ChannelPay')->where(['id'=>$data['channel']])->find();
        $randPay = json_decode($randPay['config'],true);
        $data['pay_code'] = '';
        $proSum = array_sum($randPay); 
        //概率数组循环 
        foreach ($randPay as $key => $proCur) { 
            $randNum = mt_rand(1, $proSum);
            if ($randNum <= $proCur) { 
                $data['pay_code'] = $key; 
                break; 
            } else { 
                $proSum -= $proCur;   
            } 
        } 
        $paydata = $this->getPay($data);
        $data['memberid'] = $provider['id'] ;
        $data['pay_no'] =$paydata['pay_no'];
        $data['pay_url'] = $paydata['pay_url'];

        $data['time'] = $this->timestamp;
        $data['money'] = floatval($data['money']/100);

        // 创建失败回调的参数
        $this->createData($data, $provider);
        $lock = false;

        $result = M('PoolPhones')->add($data);
        if (!$result){
            $this->result_error("save db error", true);
            Log::write(json_encode(M('PoolPhones')));
            return;
        }
        $data['id'] = $result;
        

        if (!$lock) {
            // 如果没有直接转发则进入超时
            $this->setTimeout($data);
        }

        D('Admin/PoolStatis')->setStatis($provider['id'],'order_num');
        D('Admin/PoolStatis')->setStatis($provider['id'],'order_money',$data['money']);

        $this->result_success(
            [
                'order_id' => $data['order_id'],
            ], "创建成功"
        );
    }

    
    protected function getPay(&$params) {
        $channel = D('Common/Channel')->getById(2);//测试通道

        $notify_url = $this->_site . 'Pay_Notify_Index_Method_' . $channel['code'];
        $manager = new ChannelManagerLib( $channel );

        $result = $manager->order($params, $notify_url, $params['order_id']);
        if (!$result['pay_no'] || !$result['pay_url']) {
            Log::write($result['msg']);
            $manager->reset();
            $this->result_error($result['msg']);
        }

        return $result;
    }


    protected function setTimeout(&$data) {
        $cache = RedisCacheModel::instance();
        $key = 'pool_phone_timeout';
        $timeout = C('POOL_PHONE_TIMEOUT', null,150);
        $cache->Client()->zAdd( $key, $this->timestamp + $timeout, $data['id'] );
    }

    protected function createData( &$data, &$provider) {

        /*
            appkey:     提供给商户的身份标识appkey 16位
            phone:      电话号码
            money:      金额 (单位分)
            status:     1 表示成功
        */
        $param2 = $param = [
            'appkey'        => $provider['appkey'],
            'phone'         => $this->request['phone'],
            'money'         => $this->request['money'],
            'out_trade_id'  => $data['out_trade_id'],
            'status'        => -1,
        ];
        $param['sign'] = createSign( $provider['appsecret'], $param );
        $query = http_build_query($param);

        $param2['status'] = -2;
        $param2['sign'] = createSign( $provider['appsecret'], $param2 );
        $query2 = http_build_query($param2);

        // 获取provider的配置 保存是否可以被转发
        $pconfig = json_decode($provider['config'], true);
        $config = [
            'query_timeout' => $query,
            'query_nopay'   => $query2,
            'transe'        => $pconfig['transe'] ?: 0, // 0 == no
        ];
        $data['data'] = json_encode($config);
    }

    public function Query() {
        if (
            !$this->request['appkey']
            ||  !$this->request['out_trade_id']
            ||  !$this->request['sign']
        ) {
            $this->result_error("param error", true);
            return;
        }

        $provider = M('PoolProvider')->where(['appkey' => $this->request['appkey']])->find();
        if (!$provider) {
            $this->result_error("no provider", true);
            return;
        }

        $signArray = [
            "appkey"        => $this->request['appkey'],
            "out_trade_id"  => $this->request['out_trade_id'],
        ];

        $sign = createSign( $provider['appsecret'], $signArray );
        if ($sign != $this->request['sign']) {
            $this->result_error("sign error", $sign);
//            Log::write("sign:" . $sign, Log::WARN);
            return;
        }

        $query = ['out_trade_id' => $this->request['out_trade_id'], 'pid' => $provider['id']];
        $rec = M('PoolRec')->where($query)->find();
        if (!$rec) {
            $pool = M('PoolPhones')->where($query)->find();
            if (!$pool || !$pool['lock']){
                $this->result_error("匹配中");
                return;
            }
            $this->result_error("订单未支付");
            return;
        }

        $data = json_decode($rec['data'], true);
        unset($data['id']);
        unset($data['pid']);
        unset($data['notify_url']);
        unset($data['lock']);
        $data['time'] = $rec['time'];
        $data['money'] = intval( $data['money'] * 100);
        $this->result_success($data, "查询成功");
        return;
    }



    public function QueryBalance() {

        if (
            !$this->request['appkey']
            ||  !$this->request['time']
            ||  !$this->request['sign']
        ) {
            $this->result_error("param error", true);
            return;
        }

        $provider = M('PoolProvider')->where(['appkey' => $this->request['appkey']])->find();
        if (!$provider) {
            $this->result_error("no provider", true);
            return;
        }

        $signArray = [
            "appkey"    => $this->request['appkey'],
            "time"      => $this->request['time'],
        ];

        $sign = createSign( $provider['appsecret'], $signArray );
        if ($sign != $this->request['sign']) {
            $this->result_error("sign error", $sign);
            return;
        }

        $data = [
            'time'      => $this->request['time'],
            'balance'   => number_format($provider['balance'], 2),
            'currency'  => 'RMB',
            'unit'      => '元',
        ];
        $this->result_success($data, "查询成功");
    }




   /* public function getPhone() {
        M()->startTrans();
        $phone = M('pool_phones')->lock(true)->where(['lock' => 0, 'money' => 1000])->find();
        print_r($phone);
        if ($phone) {
            $phone['lock'] = 1;
            M('pool_phones')->save($phone);
        }
        M()->commit();
    }*/




}