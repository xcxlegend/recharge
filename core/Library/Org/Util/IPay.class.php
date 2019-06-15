<?php
/**
 * Created by PhpStorm.
 * User: Legend.Xie
 * Date: 2019/5/31
 * Time: 22:43
 */

namespace Org\Util;


abstract class IPay
{

    protected $appkey;// =  "YlLPzfT3ij";
    protected $appsecret;// =  "tGnAwMqpf8ANaPryblDM";
    protected $notifyUrl;

    protected $gateway = "";


    public function __construct($appkey, $appsecret, $notifyUrl){
        $this->appkey = $appkey;
        $this->appsecret = $appsecret;
        $this->notifyUrl = $notifyUrl;
    }

    public function setGateway($gateway) {
        $this->gateway = $gateway;
    }


    // 下单接口
    abstract public function unifiedOrder($params);
    // 订单查询接口
    abstract public function checkOrder( $orderId );

    // 回调
    abstract public function notify($post);
}