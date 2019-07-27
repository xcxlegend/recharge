<?php 
error_reporting(E_ALL & ~E_NOTICE);

function http($url,$data){
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_ENCODING, 'gzip,deflate');
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
        curl_setopt($ch, CURLOPT_REFERER, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0');
        $data = curl_exec($ch);
        curl_close($ch);
        return $data;
}

function sendPoolPhone(){
    $data = $_POST;
    unset($data['action']);
    $url = 'http://' . $_SERVER['HTTP_HOST']. '/Pay_Pool';
    $data['appkey'] = '40950f2b84a9b976';

    $data['out_trade_id'] = 'PP' . time();
    $data['notify_url'] = 'http://' . $_SERVER['HTTP_HOST']. '/demo/notify.php';

    ksort($data);
    $signUrl = '';
    foreach($data as $key => $value){
        $signUrl .= $key .'='.$value.'&';
    }
    $signUrl .= "key=40950f2b84a9b976";
    $data['sign'] = md5(trim($signUrl,'&'));
    // var_dump($data);
    $resp = http($url, $data);
    exit($resp);
}

function sendOrder() {
    $http_type = ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https')) ? 'https://' : 'http://';
    $native = array(
        "pay_memberid" => "10062",
        "pay_orderid" => 'E' . date('YmdHis'),
        "pay_amount" => 1000,
        "pay_applydate" => date("Y-m-d H:i:s"),
        "pay_bankcode" => 'ali_scan_pay',
        "pay_notifyurl" => $http_type . $_SERVER['HTTP_HOST'] . "/demo/server.php",
        "pay_callbackurl" => $http_type . $_SERVER['HTTP_HOST'] . "/demo/page.php"
    );
    ksort($native);
    $md5str = "";
    foreach ($native as $key => $val) {
        $md5str = $md5str . $key . "=" . $val . "&";
    }
    //echo($md5str . "key=" . $Md5key);
    $md5str .= "key=lvjip0x4sqeni4h69pzbpgorp3u2ea3w";
    $sign = md5($md5str);
    $native["pay_md5sign"] = $sign;
    $tjurl = $http_type . $_SERVER['HTTP_HOST'] . "/Pay_Index_index.html";   //提交地址
    $resp = http($tjurl, $native);
    exit($resp);
}



if ($_POST['action']) {

    switch ($_POST['action']) {
        case 'poolPhone': 
            sendPoolPhone();
            break;
        case 'order':
            sendOrder();
            break;
    }
    exit;
} 

?>
<!DOCTYPE html>
<html>
<head>
    <meta charset='utf-8'>
    <meta http-equiv='X-UA-Compatible' content='IE=edge'>
    <title>一键测试</title>
    <meta name='viewport' content='width=device-width, initial-scale=1'>
    <script src="/Public/Front/js/jquery.min.js"></script>
</head>
<body>
    <div>
        <button type="button" id="poolPhoneBtn">开始创建号码</button>
    </div>
    <div>
        <pre id="poolPhoneResp"></pre>
    </div>
    <div>
        <button type="button" id="orderBtn">开始请求订单</button>
    </div>
    <div>
        <pre id="orderResp"></pre>
    </div>
    <div>
        回调orderid: <input id="notify_order_id" value="" />
        <button type="button" id="notifyBtn">开始回调</button>
    </div>
    <div>
        <pre id="notifyResp"></pre>
    </div>
</body>
<script>

    var poolPhonePost = function() {
        $('#poolPhoneResp').html("")
        var data = {
            action: "poolPhone",
            phone: "18081159865",
            // appkey: "40950f2b84a9b976",
            channel: 2,
            // url: "http://recharge.in:8001/Pay_Pool",
            money: 1000,
            province_code: "01",
            area_code: "0101"
        };
        $('#poolPhoneResp').append("request: " + JSON.stringify(data) + "<br/><br/>")
        $.post("", data, function (res) {
            console.log(res);
            $('#poolPhoneResp').append("resp:" + res + "<br/>");
        })
    }

    var orderPost = function() {
        $('#orderResp').html("")
        /*
            amount: 1000
            orderid: E2019072713065557641
            channel: ali_scan_pay
        */
        var data = {
            amount: 1000,
            channel: 'ali_scan_pay',
            action: "order",
        }
        $('#orderResp').append("request: " + JSON.stringify(data) + "<br/><br/>")
        $.post("", data, function(res) {
            console.log(res);
            $('#orderResp').append("resp:" + res + "<br/>");
        });
    }

    $('#poolPhoneBtn').click(poolPhonePost);
    $('#orderBtn').click(orderPost);
</script>
</html>