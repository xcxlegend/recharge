<?php
$check['phone'] = '15051871409';
$checkPhone = sendJson('http://47.111.146.122:5561/api/detect',json_encode($check));
$checkPhone = json_decode($checkPhone,true);
    print_r($checkPhone);
    if(!$checkPhone['status']){
        print_r($data.'kkkk');
    }
function sendJson($url, $jsonStr)
{
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonStr);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json; charset=utf-8'
        )
    );
    $data = curl_exec($ch);
    curl_close($ch);
    
    //print_r($data);
    return $data;
}
