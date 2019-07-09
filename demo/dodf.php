<?php
error_reporting(0);
header("Content-type: text/html; charset=utf-8");

//const HOST = "http://118.31.46.85";


$mchid = $_POST['mchid'];
$Md5key = $_POST['md5key'];// "lvjip0x4sqeni4h69pzbpgorp3u2ea3w";
$out_trade_no = date("YmdHis",time());    //订单号
$_POST['out_trade_no'] = $out_trade_no;
$money =  $_POST["money"];    //交易金额
$_POST['mchid'] = $mchid;
if(empty($mchid)||empty($_POST['money'])||empty($_POST['bankname'])
    || empty($_POST['subbranch']) || empty($_POST['accountname'])
    || empty($_POST['cardnumber']) || empty($_POST['province'])
    || empty($_POST['city'])){
    die("信息不完整！");
}
if(!empty($_POST['extends'])) {
    $_POST['extends'] = base64_encode(json_encode($_POST['extends']));
}
ksort($_POST);
//var_dump($_POST);die;
$md5str = "";
$params = $_POST;
unset($_POST['md5key']);
foreach ($_POST as $key => $val) {
    $md5str = $md5str . $key . "=" . $val . "&";
}
$sign = strtoupper(md5($md5str . "key=" . $Md5key));
$param = $_POST;
$param["pay_md5sign"] = $sign;
$http_type = ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https')) ? 'https://' : 'http://';

//api接口提交
$url = $http_type . $_SERVER['HTTP_HOST'] . "/Payment_Dfpay_add.html";   //提交地址

//$url = "https://kkapi.kuai168.cc/Payment_Dfpay_add.html";   //提交地址
$data = http_build_query($param);
list($returnCode, $returnContent) = curl($url, $data);
echo $returnContent;

function curl($url, $data){
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type:application/x-www-form-urlencoded; charset=utf-8"));
    ob_start();
    curl_exec($ch);
    $returnContent = ob_get_contents();
    ob_end_clean();
    $returnCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    return [$returnCode, $returnContent];
}