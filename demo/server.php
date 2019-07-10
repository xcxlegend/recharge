<?php

    /*
    
        memberid:       提供给商户的商户ID
        orderid:        商户订单号
        transaction_id: 平台订单号
        amount:         金额 (单位分)
        datetime:       完成时间 格式如 2019-07-06 16:34:53
        status:         1 表示成功
        sign:           签名 

     */

   $ReturnArray = array( // 返回字段
        "memberid"       =>  $_REQUEST["memberid"],         // 商户ID
        "orderid"        =>  $_REQUEST["orderid"],          // 订单号
        "amount"         =>  $_REQUEST["amount"],           // 交易金额
        "datetime"       =>  $_REQUEST["datetime"],         // 交易时间
        "transaction_id" =>  $_REQUEST["transaction_id"],   // 支付流水号
        "status"         =>  $_REQUEST["status"],
    );
  
    $Md5key = "lvjip0x4sqeni4h69pzbpgorp3u2ea3w";
    // $Md5key = '9f9quocsb5ake9i7f0i02kosyegzjt1t';//"lvjip0x4sqeni4h69pzbpgorp3u2ea3w";

	ksort($ReturnArray);
    // reset($ReturnArray);
    $md5str = "";
    foreach ($ReturnArray as $key => $val) {
        $md5str = $md5str . $key . "=" . $val . "&";
    }
    $sign = md5($md5str . "key=" . $Md5key);


    echo $sign;

    if ($sign == $_REQUEST["sign"]) {
        if ($_REQUEST["status"] == "1") {
			   $str = "交易成功！订单号：".$_REQUEST["orderid"];
               file_put_contents("success.txt",$str."\n", FILE_APPEND);
			   exit("ok");
        }
    }
    exit('err');
?>