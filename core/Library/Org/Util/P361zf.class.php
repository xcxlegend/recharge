<?php
/**
 * Created by PhpStorm.
 * User: Legend.Xie
 * Date: 2019/5/22
 * Time: 22:17
 */

namespace Org\Util;

/**
 * Class P361zf
 * @package Org\Util
 */
class P361zf
{


    const API_URL_PREFIX = "http://25.361zf.com";

    const UNIFIEDORDER_URL = "/merpay/topay";
    //查询订单URL
    const ORDERQUERY_URL = "/merpay/toquery";
    //关闭订单URL
    const DEPOSIT_URL = "/xxx";

    private $appkey;// =  "YlLPzfT3ij";
    private $appsecret;// =  "tGnAwMqpf8ANaPryblDM";
    private $notifyUrl;
    private $gateway = "";

    /**参数
    商户ID	parter
    支付类型	type
    金额	value
    商户订单号	orderid
    下行异步通知地址	callbackurl
    x下行同步通知地址	hrefbackurl
    x支付用户IP	payerIp
    备注消息	attach
    MD5签名	sign
    代理ID	agent

     */


    public function __construct($appkey, $appsecret, $notifyUrl){
        $this->appkey = $appkey;
        $this->appsecret = $appsecret;
        $this->notifyUrl = $notifyUrl;
    }

    // 下单接口
    public function unifiedOrder( $params ){
//        $uri = self::API_URL_PREFIX . self::UNIFIEDORDER_URL;
        $uri =  $this->gateway . self::UNIFIEDORDER_URL;
        $query = [];
        $query["parter"] = $this->appkey;
        $query["value"] = $params['total_fee'];
        $query["orderid"] = $params['out_trade_no'];
        $query["type"] = $params['trade_type'];
        $query["callbackurl"] = $this->notifyUrl;
        $query["sign"] = $this->makeOrderSign($query);
        $result = HttpClient::get($uri, $query);
        $result = json_decode($result, true);
        return $result;
    }

    public function setGateway($gateway) {
        $this->gateway = $gateway;
    }

    // 订单查询接口
    public function checkOrder( $orderId ){


        /**
         *
        商户订单号	orderid	Y	请求的商户订单号
        订单结果	opstate	Y	3：请求参数无效
        2：签名错误
        1：商户订单号无效
        0：支付成功
        其他：用户还未完成支付或者支付失败
        订单金额	ovalue	Y	订单实际金额，单位元

         */

        $query = [
            "parter"  => $this->appkey,
            "orderid" => $orderId,
        ];
        $query['sign'] = $this->MakeSign('orderid='.$orderId.'&parter='.$this->appkey.$this->appsecret);
//        $uri = self::API_URL_PREFIX . self::ORDERQUERY_URL;
        $uri = $this->gateway . self::ORDERQUERY_URL;
        $result = file_get_contents($uri . '?' . http_build_query($query));//sendForm($uri, $query);
        $result = $this->convertUrlQuery($result);
        return $result['opstate'] == '0';
        return $result;
    }

    function convertUrlQuery($query)
    {
        $queryParts = explode('&', $query);

        $params = array();
        foreach ($queryParts as $param)
        {
            $item = explode('=', $param);
            $params[$item[0]] = $item[1];
        }

        return $params;
    }


    // parter={}&type={}&value={}&orderid ={}&callbackurl={}key
    protected function makeOrderSign( $params ) {
        $string = 'parter='.$params['parter'].
            '&type='.$params['type'].
            '&value='.$params['value'].
            '&orderid='.$params['orderid'].
            '&callbackurl='.$params['callbackurl'] . $this->appsecret;
        return strtolower(md5($string));

    }

    protected function makeNotifySign( $params ) {
        //orderid={}&opstate={}&ovalue={}key
        $string = 'orderid='.$params['orderid'].
            '&opstate='.$params['opstate'].
            '&ovalue='.$params['ovalue'] . $this->appsecret;
        return strtolower(md5($string));

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
        商户订单号	orderid	Y	上行过程中商户系统传入的orderid。
        订单结果	opstate	Y	0：支付成功
        -1 请求参数无效
        -2 签名错误
        订单金额	ovalue	Y	订单实际支付金额，单位元
        MD5签名	sign	-	32位小写MD5签名值，GB2312编码
        支付订单号	sysorderid	N	此次订单过程中支付接口系统内的订单Id
        支付订单时间	completiontime	N	此次订单过程中支付接口系统内的订单结束时间。格式为
        年/月/日 时：分：秒，如2010/04/05 21:50:58
        备注信息	attach	N	备注信息，上行中attach原样返回
        订单结果说明	msg	N	订单结果说明
         */
        $sign = $post['sign'];
        $orderId = $post['orderid'];
        $md5 = $this->makeNotifySign($post);
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