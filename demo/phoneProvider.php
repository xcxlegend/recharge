<?php
error_reporting(E_ALL & ~E_NOTICE);
    $action = $_GET['action'];
    if($action == 'debug'){
        $data = $_POST;
        $data['appkey'] = '40950f2b84a9b976';
        $data['out_trade_id'] = time();
        $data['notify_url'] = $_SERVER['HTTP_HOST']. '?action=notify';


        $phoneInfo =  file_get_contents('https://cx.shouji.360.cn/phonearea.php?number='.$data['phone']);
        $phoneInfo =json_decode($phoneInfo,true);

        if($phoneInfo['data']['sp']=='移动'){
            $data['channel'] = '1';
        }elseif($phoneInfo['data']['sp']=='联通'){
            $data['channel'] = '2';
        }else{
            $data['channel'] = '3';
        }

        array_multisort($data);
        $data['key'] = '40950f2b84a9b976';

        $signUrl = '';
        foreach($data as $key => $value){
            $signUrl .= $key .'='.$value.'&';
        }

        $data['sign'] = md5(trim($signUrl,'&'));
        $result = http($data['url'],$data);
        echo $result;

    }

    function http($url,$data)
    {
        
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
?>

<!DOCTYPE html>
<html>
<head>

    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <meta content="width=device-width, initial-scale=1, maximum-scale=1.0, user-scalable=0" name="viewport">
    <title>号码商接口调试</title>
    <link href="/Public/Front/css/bootstrap.min.css" rel="stylesheet" type="text/css">
    <script src="/Public/Front/js/jquery.min.js"></script>
    <style>
        header{background-color: #fff;box-shadow: 0 2px 2px rgba(0, 0, 0, 0.05), 0 1px 0 rgba(0, 0, 0, 0.05);color:#58666e;height:70px;line-height:70px;font-size:20px;margin-bottom:30px;}
        footer{margin-top:100px}
        .btn-primary { background-color: #1a8ae1;border-color: #2196F3;}   
    </style>
</head>
<body style="background-color:#f9f9f9">
    <header>
        <div class="container">号码商接口调试</div>
    </header>


<div class="container">
<form method="post" action="?action=debug" autocomplete="off">
<div class="form-group">
    <label for="exampleInputPassword1">接口地址</label>
    <input type="input" class="form-control" name="url">
  </div>
  <div class="form-group">
    <label for="exampleInputPassword1">手机号（数字）</label>
    <input type="number" class="form-control" name="phone">
  </div>
  <div class="form-group">
    <label for="exampleInputPassword1">金额（分）</label>
    <input type="number" class="form-control" name="money">
  </div>
  
  <button type="submit" class="btn btn-primary ">发起调试</button>
</form>
</div>

<!--底部-->
<footer class="text-center text-muted">
    <p>Copyright © 2018 聚合支付 版权所有</p>
</footer>

</form>

</body>
</html>