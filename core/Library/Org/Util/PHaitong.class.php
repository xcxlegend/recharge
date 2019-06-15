<?php
/**
 * Created by PhpStorm.
 * User: Legend.Xie
 * Date: 2019/5/31
 * Time: 22:42
 */

namespace Org\Util;


class PHaitong extends IPay
{
    const API_URL_PREFIX = "http://www.haitongv2.com";


    const UNIFIEDORDER_URL = "/apisubmit";
    //查询订单URL
    const ORDERQUERY_URL = "/apiorderquery";
    //关闭订单URL
//    const DEPOSIT_URL = "/txapply";


    public function __construct($appkey, $appsecret, $notifyUrl){
        parent::__construct($appkey, $appsecret, $notifyUrl);
        $this->setGateway(self::API_URL_PREFIX);
    }


    // 下单接口
    public function unifiedOrder($params){
        $query = [];
        $version = $query['version']='1.0';
        $customerid = $query['customerid'] = $this->appkey;
        $sdorderno = $query['sdorderno'] = $params['out_trade_no'];
        $total_fee = $query['total_fee'] = $params['total_fee'];
        $paytype = $query['paytype'] =   $params['trade_type'];
//        $bankcode = '';//$_POST['bankcode'];
        $notifyurl = $query['notifyurl'] = $this->notifyUrl;
        $returnurl = $query['returnurl'] = $this->notifyUrl;
        $query['sign'] = md5('version='.$version.'&customerid='.$customerid.'&total_fee='.$total_fee.'&sdorderno='.$sdorderno.'&notifyurl='.$notifyurl.'&returnurl='.$returnurl.'&'.$this->appsecret);
//        $result = sendForm($this->gateway. self::UNIFIEDORDER_URL, $query);
        $url = $this->gateway . self::UNIFIEDORDER_URL;
//        echo $url . '?' . http_build_query($query);exit;


        return '<!doctype html><html><head>    <meta charset="utf8">    <title>正在转到付款页</title></head><body onLoad="document.pay.submit()">    <form name="pay" action="'.$url.'" method="post">        <input type="hidden" name="version" value="' . $version .'">        <input type="hidden" name="customerid" value="' . $customerid .'">        <input type="hidden" name="sdorderno" value="' . $sdorderno .'">        <input type="hidden" name="total_fee" value="' . $total_fee .'">        <input type="hidden" name="paytype" value="' . $paytype .'">        <input type="hidden" name="notifyurl" value="' . $notifyurl .'">        <input type="hidden" name="returnurl" value="' . $returnurl .'">               <input type="hidden" name="sign" value="' .  $query['sign'] .'">             </form></body></html>';
//        $result = file_get_contents($url . '?' . http_build_query($query));
//        return json_decode($result, true);
    }

    // 订单查询接口
    public function checkOrder( $orderId ){
        /**
         * 商户编号	customerid	int(8)
        商户订单号	sdorderno	varchar(20)
        时间戳	reqtime	varchar(14)
        md5验证签名串	sign	varchar(32)
         */

        /**
         * {"status":1,"msg":"成功订单","sdorderno":"商户订单号","total_fee":"订单金额","sdpayno":"平台订单号"}
        {"status":0,"msg":"失败订单"}
         */
        $url= $this->gateway . self::ORDERQUERY_URL;
        $query = [
            'customerid' => $this->appkey,
            'sdorderno' => $orderId,
            'reqtime' => date('YmdHis', time())
        ];

        $query['sign'] = md5('customerid='.$query['customerid'].'&sdorderno='.$query['sdorderno'].'&reqtime='.$query['reqtime'].'&'.$this->appsecret);

        $result = sendForm($url, $query);
        $result = json_decode($result, true);
        return $result['status'] == 1;
    }

    // 回调
    public function notify($post){
        $status=$post['status'];
        $customerid=$post['customerid'];
        $sdorderno=$post['sdorderno'];
        $total_fee=$post['total_fee'];
        $paytype=$post['paytype'];
        $sdpayno=$post['sdpayno'];
        $remark=$post['remark'];
        $sign=$post['sign'];
//customerid={value}&status={value}&sdpayno={value}&sdorderno={value}&total_fee={value}&paytype={value}&{apikey}
        $mysign=md5('customerid='.$customerid.'&status='.$status.'&sdpayno='.$sdpayno.'&sdorderno='.$sdorderno.'&total_fee='.$total_fee.'&paytype='.$paytype.'&'.$this->appsecret);
        return $sign==$mysign;
    }
}