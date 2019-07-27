<?php
namespace Common\Lib;

use Think\Exception;
use Common\Model\PhoneCodeModel;

class PaytypeMgrLib
{

    public $channel;
    public $pool;
    public $poolMgr;

    public function __construct(IPoolLib $poolMgr)
    {
        $this->poolMgr = $poolMgr;
    }

    /**
     * 获取支付通道和pool pool可能为空
     *
     * @param array $userProduct
     * @return array [$channel, $pool]
     */
    public function query(array $userProduct, array &$request)
    {
        $timestamp = time();
        // 获取pool
        $this->poolMgr->query($request);
        $this->pool = $request['pool'];

        // 获取channel
        if (!$this->pool['phone_code']) {
            throw new Exception("号码地区标识未设置");
            return;
        }
        
        $phoneCode = M('PhoneCode')->where(['code' => $this->pool['phone_code']])->find();
        if (!$phoneCode) {
            throw new Exception("号码地区标识查询失败");
            return;
        }
        if ($phoneCode['status'] == PhoneCodeModel::STATUS_BAN) {
            throw new Exception("号码地区标识被禁止");
            return;
        }

        // 如果是登录 或者是 非登录超时
        if ($phoneCode['status'] == PhoneCodeModel::STATUS_LOGIN || $timestamp > $phoneCode['last_time'] + 1800) {
            $channel_id = $userProduct['lg_channel'];
        } else {
            $channel_id = $userProduct['nlg_channel'];
        }

        $this->channel = D('Common/Channel')->getById($channel_id);

        if (!$this->channel) {
            throw new Exception("商户未设置支付渠道");
            return;
        }
    }

    public function setError($err)
    {
        $this->poolMgr->setError($err);
    }

    public function reset()
    {
        $this->poolMgr->reset();
    }

}
