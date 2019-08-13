<?php

    /*
        appkey:         提供给商户的身份标识appkey 16位
        phone:          电话号码
        money:          金额 (单位分)
        out_trade_id:   商户系统的订单ID
        status:         1 表示成功
        sign:           签名 

     */
   file_put_contents("pool_notify.txt", "receive notify message:" . http_build_query($_REQUEST) . "\n" , FILE_APPEND);
   exit('ok');
   $ReturnArray = array( // 返回字段
        "appkey"       =>  $_REQUEST["appkey"],         // 商户ID
        "phone"        =>  $_REQUEST["phone"],          // 订单号
        "money"        =>  $_REQUEST["money"],          // 交易金额
        "out_trade_id" =>  $_REQUEST["out_trade_id"],   // 交易时间
        "status"       =>  $_REQUEST["status"],
    );
  
    $Md5key = "40950f2b84a9b976";
    // $Md5key = '3d9ffdc0512f223db2b17b036b72406f';//"40950f2b84a9b976";

    ksort($ReturnArray);
    reset($ReturnArray);
    $md5str = "";
    foreach ($ReturnArray as $key => $val) {
        $md5str = $md5str . $key . "=" . $val . "&";
    }
    $sign = md5($md5str . "key=" . $Md5key);
    if ($sign == $_REQUEST["sign"]) {
        if ($_REQUEST["status"] == "1") {
               $str = "交易成功！订单号：".$_REQUEST["out_trade_id"];
               file_put_contents("pool_notify.txt",$str."\n", FILE_APPEND);
               exit("ok");
        }
    }
    exit('err');
?>