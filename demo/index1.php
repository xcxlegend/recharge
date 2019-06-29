<?php
error_reporting(E_ALL ^ E_WARNING ^E_NOTICE);

header("Content-type: text/html; charset=utf-8");


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




$pay_memberid = "10062";//商户ID
$pay_orderid = $_POST["orderid"];    //订单号
$pay_amount =  $_POST["amount"];    //交易金额
$pay_bankcode = $_POST["channel"];   //银行编码
if(empty($pay_memberid)||empty($pay_amount)||empty($pay_bankcode)){
    die("信息不完整！");
}
$pay_applydate = date("Y-m-d H:i:s");  //订单时间
$http_type = ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https')) ? 'https://' : 'http://';
$pay_notifyurl = $http_type . $_SERVER['HTTP_HOST'] . "/demo/server.php";   //服务端返回地址
$pay_callbackurl = $http_type. $_SERVER['HTTP_HOST'] . "/demo/page.php";  //页面跳转返回地址
$Md5key = "lvjip0x4sqeni4h69pzbpgorp3u2ea3w";   //密钥
$tjurl = $http_type . $_SERVER['HTTP_HOST'] . "/Pay_Index_index.html";   //提交地址


//扫码
$native = array(
    "pay_memberid" => $pay_memberid,
    "pay_orderid" => $pay_orderid,
    "pay_amount" => $pay_amount,
    "pay_applydate" => $pay_applydate,
    "pay_bankcode" => $pay_bankcode,
    "pay_notifyurl" => $pay_notifyurl,
    "pay_callbackurl" => $pay_callbackurl,
);
ksort($native);
$md5str = "";
foreach ($native as $key => $val) {
    $md5str = $md5str . $key . "=" . $val . "&";
}
//echo($md5str . "key=" . $Md5key);
$md5str .= "key=" . $Md5key;
$sign = md5($md5str);
$native["pay_md5sign"] = $sign;
$native['pay_attach'] = "1234|456";
$native['pay_productname'] ='测试商品';

$res = sendForm($tjurl, $native);
//print_r($native);
echo $res;exit;
$res = json_decode($res, true);

if ($res['status'] != 'success') {
    echo $res['msg'];
    exit;
}



if ($pay_bankcode == 901) {
    echo '<!DOCTYPE html><html lang="en"><head>    <meta charset="UTF-8">    <title>Title</title></head><body><a id="btn" href="'.$res['data']['url'].'"></a></body><script>    document.getElementById("btn").click();</script></html>';
    exit;
}


if ($pay_bankcode != 902 && $pay_bankcode != 903) {
    header('Location: ' . $res['data']['url']);
    exit;
}

if ($pay_bankcode == 902) {
    $view = 'weixin';
} else {
    $view = 'alipay';
}
$query = http_build_query(
    [
        'view' => $view,
        'url'  => $res['data']['url'],
        'orderid' => $res['data']['orderId'],
        'amount' => $pay_amount,
    ]
);
$qr_uri = $http_type . $_SERVER['HTTP_HOST'] . "/Pay_Pay_QR.html?".$query;
header('Location:' . $qr_uri);

//echo $res;

exit;
?>

<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title></title>

</head>
<body>
<div class="container">
    <div class="row" style="margin:15px;0;">
        <div class="col-md-12">
            <form class="form-inline" id="payform" method="post" action="<?php echo $tjurl; ?>">
                <?php
                foreach ($native as $key => $val) {
                    echo '<input type="hidden" name="' . $key . '" value="' . $val . '">';
                }
                ?>
                <button type="submit" style='display:none;' ></button>
            </form>
        </div>
    </div>
</div>
<script>
    document.forms['payform'].submit();
</script>
</body>
</html>
