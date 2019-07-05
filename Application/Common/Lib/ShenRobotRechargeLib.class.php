<?php
/**
 * Created by PhpStorm.
 * User: Legend.Xie
 * Date: 2019/7/3
 * Time: 22:58
 */

namespace Common\Lib;
use Think\Exception;
use \Think\Log;


class ShenRobotRechargeLib extends IPhoneRechagerLib
{
    const API_URL = "http://118.190.53.24/api/";

    const Channels = [
        '1' => '1',
        '2' => '3',
        '3' => '2'
    ];

    public function order(array $params, $gateway, $notify, $pay_orderid)
    {
        if (!$gateway){
            $gateway = self::API_URL;
        }

        $this->poolQuery(new PoolDevLib(), $params);

        $pool = $params['pool'] ?: [];
        $phone = $pool['phone'];

        /**
         * {
        "pay_user":"",
        "pay_password":"",
        "pay_orderid":"12345678900987654321",
        "pay_applydate":"2019-06-24 18:18:18",
        "pay_mobile":"13635279266",
        "pay_amount":"30.00",
        "pay_type":"1",
        "pay_code":"ali_wap_pay",
        "pay_notifyurl":"暂无",
        "pay_returnurl":"暂无",
        "sign":"暂无"
        }
         */

        $query = [
            "pay_user" => "",
            "pay_password" => "",
            "pay_orderid"          => $pay_orderid,
            "pay_applydate"        => date('Y-m-d H:i:s'),
            "pay_mobile"           => $phone,
            "pay_amount"           => strval(number_format($params['pay_amount'] / 100, 2)),
            "pay_type"             => $this->getChannel($params['pool']['channel']),
            "pay_code"             => $params['pay_bankcode'],
            "pay_notifyurl"        => $notify ?: '',
            "pay_returnurl"        => $params['pay_returnurl'] ?: 'http://',
        ];

        $query['sign'] = md5('liangye&'. date('Ymd') . '@/#A');
//        echo $gateway;
        $data = sendJson($gateway, $query);
        $data = json_decode($data, true);
        if ($data['status'] != 'success') {
            Log::write(json_encode($data), Log::WARN);
            throw new Exception( '[RECHARGER] ' . $data['message']);
            return false;
        }

        /**
         * "data": {
        "orderId": "12345678900987654321",
        "orderNo": "454889443175194094",
        "mobile": "13635279266",
        "amount": "30",
        "type": "1",
        "url": "https://qr.alipay.com/upx03812k5qaehskymui2080",
        "createTime": "2019/07/03 22:10:43"
        }
         */

        return new ChannelOrder( $data['data']['orderNo'], $data['data']['url'], $data['data']['url'], $pool['id'] );

    }

    public function query($pay_orderid)
    {
        // TODO: Implement query() method.
    }

    public function notify(array $request)
    {
        if ($request['status'] != 1) {
            return false;
        }

        return $request['orderid'];
    }

    protected function getChannel( $channel ) {
        return self::Channels[$channel] ?: '';
    }

}