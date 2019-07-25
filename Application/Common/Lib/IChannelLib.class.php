<?php

/**
 * Created by PhpStorm.
 * User: Legend.Xie
 * Date: 2019/6/26
 * Time: 20:18
 */

namespace Common\Lib;


class ChannelOrder
{
    // 流水号
    // public $transID;
    // 外部订单号
    public $tradeID;
    // 支付地址
    public $wapUrl;
    // 二维码地址
    public $qrUrl;
    // poolID 可不设置
    public $poolId;
    public $poolPid;

    public function __construct($tradeID, $wapUrl, $qrUrl = "", $poolId = 0, $poolPid = 0)
    {
        // $this->transID  = $transID;
        $this->tradeID  = $tradeID;
        $this->wapUrl   = $wapUrl;
        $this->qrUrl    = $qrUrl;
        $this->poolId   = $poolId;
        $this->poolPid  = $poolPid;
    }
}

// 上游接口
interface IChannelLib
{
    /**
     * 请求订单 function
     *
     * @param array $params
     * @param string $gateway
     * @param string $notify
     * @param string $pay_orderid
     * @return ChannelOrder
     */
    public function order(array $params, $gateway, $notify, $pay_orderid, array $pool = []);
    /**
     * 查询订单 function
     *
     * @param string $gateway
     * @param array $order
     * @param array $pool
     * @return bool
     */
    public function query(string $gateway, array &$order, array &$pool);
    // 回调验证并且返回transID
    public function notify(array $request);
    public static function notify_ok();
    public static function notify_err();
    public function reset();
}
