<?php
function createSign($Md5key, $params){
    ksort($params);
    $md5str = "";
    foreach ($params as $key => $val) {
//        if (!empty($val)) {
        $md5str = $md5str . $key . "=" . $val . "&";
//        }
    }
    $md5str .= "key=" . $Md5key;


    $sign = md5($md5str);

    return $sign;
}

function sendForm($url,$data,$referer = ''){
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

$appKey = '40950f2b84a9b976';
$appSect = '40950f2b84a9b976';
$out_trade_id = '1562845612';
//$url = 'http://39.98.106.129/Pay_Pool_Query';
$url = 'http://recharge.in:8001/Pay_Pool_Query';
$params = [
    'appkey'            => $appKey,
    'out_trade_id'      => $out_trade_id,
];


$params['sign']  = createSign($appSect, $params);
echo $url;
var_dump($params);
$data = sendForm($url, $params);
echo $data;
