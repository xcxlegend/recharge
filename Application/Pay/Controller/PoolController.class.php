<?php
/**
 * Created by PhpStorm.
 * User: Legend.Xie
 * Date: 2019/6/18
 * Time: 20:45
 */

namespace Pay\Controller;


class PoolController extends PayController
{


    public function __construct()
    {
        parent::__construct();
    }

    public function Index() {

    /*
     *  appkey: 提供给商户的身份标识appkey 16位
        phone: 电话号码
        money: 金额 (单位分)
        out_trade_id: 商户系统的订单ID 用于回调
        notify_url:  回调地址 用于充值完成后回调
        channel: 手机对应运营商 1=移动 2=联调 3=电信
        sign: 签名  签名方案按照 k=v&k2=v2 k升序形式进行 最后并上  &key=APIKEY (APIKEY为分配给商户的密钥)  做md5 转小写.  具体查看后面的签名说明
     */


    }




   /* public function getPhone() {
        M()->startTrans();
        $phone = M('pool_phones')->lock(true)->where(['lock' => 0, 'money' => 1000])->find();
        print_r($phone);
        if ($phone) {
            $phone['lock'] = 1;
            M('pool_phones')->save($phone);
        }
        M()->commit();
    }*/




}