<?php
/**
 * Created by PhpStorm.
 * User: Legend.Xie
 * Date: 2019/5/12
 * Time: 12:04
 */

namespace Org\Util;


/**
 * Class P59Pay
 * @package Org\Util
 * 云短信充值平台SDK
 */
class P59Pay
{

    const API_URL_PREFIX = "http://47.244.212.45:5101/59";

    const UNIFIEDORDER_URL = "/payment";
    //查询订单URL
    const ORDERQUERY_URL = "/query";
    //关闭订单URL
    const DEPOSIT_URL = "/txapply";

    private $appkey;// =  "YlLPzfT3ij";
    private $appsecret;// =  "tGnAwMqpf8ANaPryblDM";
    private $notifyUrl;

    private $gateway = "";

    /**参数
    appKey	商户ID	是	系统分配给商户的唯一标识
    face	面值	是	面值 单位:元 例如：100
    orderNo	商户订单号	是	商户订单请求唯一编号（不能重复）
    payType	支付方式	是	支付方式：详见附录
    timestamp	请求时间	是	格式：yyyyMMddHHmmss
    notifyUrl	通知地址	否	处理结果通知地址（不参与加密）
    sign	签名	是	sign=MD5(key1=value1&key2=value…+key)
    transUrl	备选参数	否	不参与加密 只有在充值方式是微信H5：1005 的情况下传此参数 值为true
     */


    public function __construct($appkey, $appsecret, $notifyUrl){
        $this->appkey = $appkey;
        $this->appsecret = $appsecret;
        $this->notifyUrl = $notifyUrl;
        $this->gateway = self::API_URL_PREFIX;
    }

    public function setGateway($gateway) {
        $this->gateway = $gateway;
    }


    // 下单接口
    public function unifiedOrder( $params ){
        $uri =  $this->gateway . self::UNIFIEDORDER_URL;
        $query = [];
        $query["appKey"] = $this->appkey;
        $query["face"] = $params['total_fee'];
        $query["orderNo"] = $params['out_trade_no'];
        $query["payType"] = $params['trade_type'];
        $query["timestamp"] = date("YmdHis", time());
        $query["sign"] = $this->MakeSign($query);
        $query["notifyUrl"] = $this->notifyUrl;

        if ($params['trade_type'] == 1005) {
//            $query["transUrl"] = true;
        }

        $result = sendForm($uri, $query);
        $result = json_decode($result, true);
        return $result;
    }

    // 订单查询接口
    public function checkOrder( $orderId ){

        /**
         * appKey	商户ID	是	系统分配给商户的唯一标识
        orderNo	商户订单号	是	商户订单编号
        timestamp	请求时间	是	格式：yyyyMMddHHmmss
        sign	签名	是	sign=MD5(key1=value1&key2=value…+key)

         */

        /**
         *
         * {code: "4000", message: "查询成功",…}
            code: "4000"
            data: {orderId: "MP190512230948547101540872", orderNo: "E2019051215094439920", createTime: "20190512230949",…}
            createTime: "20190512230949"
            orderId: "MP190512230948547101540872"
            orderNo: "E2019051215094439920"
            orderStatus: 3
            payMoney: 10
            message: "查询成功"
         * 1	处理中
        3	支付失败
        4	订单成功

         */

        $query = [
            "appKey"  => $this->appkey,
            "orderNo" => $orderId,
            "timestamp" => $this->getDtime(),
        ];
        $query['sign'] = $this->MakeSign($query);
        $uri = $this->gateway . self::ORDERQUERY_URL;
        $result = sendForm($uri, $query);
        $result = json_decode($result, true);
        return $result['code'] == 4000 && result['orderStatus'] == 4;
        return $result;
    }

    protected function MakeSign( $params ){
        //签名步骤一：按字典序排序数组参数
        ksort($params);
        $string = $this->ToUrlParams($params);
        //签名步骤二：在string后加入KEY
        $string = $string.$this->appsecret;
        //签名步骤三：MD5加密
        $string = md5($string);
        //签名步骤四：所有字符转为小写
        $result = strtolower($string);
        return $result;
    }


    protected function ToUrlParams( $params ){
        $string = '';
        if( !empty($params) ){
            $array = array();
            foreach( $params as $key => $value ){
                $array[] = $key.'='.$value;
            }
            $string = implode("&",$array);
        }
        return $string;
    }


    public function notify($post){
        /**
         * appKey=test01&orderNo=373557108010& payMoney =30.0&status=4&sign=d8d4705a4aba9c38cdc7f714c5fc8a92
         * orderId
         */
        $sign = $post['sign'];
        $orderId = $post['orderId'];
        unset($post['sign']);
        unset($post['orderId']);
        $md5 = $this->MakeSign($post);
        return $sign == $md5;
    }


    public function subdf( $wttlList ) {
        /**$wttlList
         * [id] => 6
        [userid] => 62
        [bankname] => 招商银行
        [bankzhiname] => 测试支行
        [banknumber] => 测试卡号
        [bankfullname] => 测试名称
        [sheng] => 四川
        [shi] => 成都
        [sqdatetime] => 2019-05-17 16:03:09
        [cldatetime] =>
        [status] => 0
        [tkmoney] => 10.0000
        [sxfmoney] => 0.0000
        [money] => 10
        [t] => 0
        [payapiid] => 0
        [memo] =>
        [additional] =>
        [code] =>
        [df_id] => 0
        [df_name] =>
        [orderid] => I0517801894643864
        [cost] => 0.00
        [cost_rate] => 0.0000
        [rate_type] => 0
        [extends] =>
        [out_trade_no] =>
        [df_api_id] => 0
        [auto_submit_try] => 0
        [is_auto] => 0
        [last_submit_time] => 0
        [df_lock] => 0
        [auto_query_num] => 0
        [channel_mch_id] =>
        [df_charge_type] => 0
         */

        /**
         * P59
         * orderid	申请流水	否	申请流水号 不参与加密
        accountHolder	开户人	是	银行卡持有人 例如：张三
        appKey	商户ID	是	系统分配给商户的唯一标识
        bankName	开户行
        是	银行卡所属银行 例如：中国银行
        branchName
        支行名称
        是	开户行所在地支行名称
        cardNum
        卡号	是	银行卡卡号
        cardId	身份证号	是	开户人身份证号
        cashMoney	申请金额	是	金额 单位：元 例如：100.0
        territory
        所属地
        是	开户行所在地
        timestamp	请求时间	是	格式：yyyyMMddHHmmss
        sign	签名	是	sign=MD5(key1=value1&key2=value…+key)
        notifyUrl	提现结果回调地址	是	不参与加密
        URLEncoder.encode(参数, "UTF-8")
        没有请传www
         *
         * return
         * {"code":"6003","message":"申请的金额超额"}
        {"code":"6000","message":"申请成功","data":{"txId":"TX20190218092837793"}}

         */

        $query = [
            'accountHolder' => $wttlList['bankfullname'],
            'bankName' => $wttlList['bankname'],
            'branchName' => $wttlList['bankzhiname'],
            'cardNum' => $wttlList['banknumber'],
            'cardId' => 'cardId',
            'cashMoney' => $wttlList['money'],
            'territory' => $wttlList['sheng'] . $wttlList['shi'] ,
            'timestamp' => $this->getDtime(),
        ];
        $query['appKey'] = $this->appkey;
        $query['sign'] = $this->MakeSign($query);
        $query['orderid'] = $wttlList['orderid'];
        $query['notifyUrl'] = $this->notifyUrl;
        $uri = self::API_URL_PREFIX . self::DEPOSIT_URL;
        $result = sendForm($uri, $query);
        $result = json_decode($result, true);
        return $result;
    }


    protected function getDtime(){
        return date("YmdHis", time());
    }

}