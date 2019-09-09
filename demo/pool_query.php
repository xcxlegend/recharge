<?php
/**
 * Created by PhpStorm.
 * User: Legend.Xie
 * Date: 2019/5/15
 * Time: 21:09
 */
error_reporting(E_ALL ^ E_WARNING ^E_NOTICE);
function sendJson($url, $jsonStr)
{
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POSTFIELDS, '{"phone":"15051871409"}');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json; charset=utf-8',
            'Content-Length: ' . strlen($jsonStr)
        )
    );
    $data = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    print_r($code);
    exit;
    curl_close($ch);
    return $data;
}
function sendForm($url,$data,$referer){
    $headers['Content-Type'] = "application/x-www-form-urlencoded; charset=utf-8";
    $headerArr = array();
    foreach( $headers as $n => $v ) {
        $headerArr[] = $n .':' . $v;
    }
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headerArr);
    curl_setopt($ch, CURLOPT_REFERER, "http://".$referer."/");
    $data = curl_exec($ch);
    curl_close($ch);
    return $data;
}
$Md5key = "lvjip0x4sqeni4h69pzbpgorp3u2ea3w";   //密钥
const MD5KEY = '3d9ffdc0512f223db2b17b036b72406f';
function sign( $query ){
    ksort($query);
    $md5str = "";
    foreach ($query as $key => $val) {
        $md5str = $md5str . $key . "=" . $val . "&";
    }
    $sign = md5($md5str . "key=" . MD5KEY);
    return $sign;
}

$query = [
    'appkey'  => '62fa7a1588478a5d',
    'out_trade_id' => '1562686685'
];
$query['sign'] = sign($query);

$data = sendForm('http://47.244.237.40/Pay_Pool_Query', $query);
var_dump($data);exit;

$http_type = ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https')) ? 'https://' : 'http://';

if ($_GET['act'] == 'tx') {
    /**
     * mchid    商户编号    是   是   平台分配商户号
    out_trade_no    商户订单号   是   是   保证唯一值
    money   订单金额    是   是   单位：元
    bankname    开户行名称   是   是
    subbranch   支行名称    是   是
    accountname 开户名 是   是
    cardnumber  银行卡号    是   是
    province    省份  是   是
    city    城市  是   是
    extends 附加字段    否   是
    sign
     */
    $query = [
        'mchid' => 10062,
        'out_trade_no' => 'T'.date('Ymd', time()).rand(0, 999999),
        'money' => 10,
        'bankname' => '开户行名字1',
        'subbranch' => '支行名字1',
        'accountname' => '开户名1',
        'cardnumber' => '卡号1',
        'province' => '省份1',
        'city' => '城市1',
    ];
    $query['pay_md5sign'] = sign($query);
    $tjurl = $http_type . $_SERVER['HTTP_HOST'] . "/Payment_Dfpay_add.html";   //提交地址
    $res = sendForm($tjurl, $query, $refer);
    echo $res;
    exit;
}

$query = [
    'pay_memberid' => 10062,
    'pay_orderid'  => 'E20190515102245600761',
];

$query["pay_md5sign"] = sign($query);

$tjurl = $http_type . $_SERVER['HTTP_HOST'] . "/Pay_Trade_Query.html";   //提交地址
$refer = $http_type . $_SERVER['HTTP_HOST'] . '/demo';

$res = sendForm($tjurl, $query, $refer);
echo $res;