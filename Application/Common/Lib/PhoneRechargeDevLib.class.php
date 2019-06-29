<?php
/**
 * Created by PhpStorm.
 * User: Legend.Xie
 * Date: 2019/6/26
 * Time: 20:14
 */

namespace Common\Lib;
use \Think\Log;

/**
 * Class PoolProviderDevLib
 * @package Lib
 *
 * 1. 用phone, money 去请求订单
 * 2. 回调处理
 * 3. 查单处理
 */


class PhoneRechargeDevLib extends IPhoneRechagerLib
{
    const API_URL = "http://148.70.91.219/api/reptile/pay.html";
    const MID = "2019828315";
    const SECRET = "a854278887892da7e3dadad7d7ae34f7";
    const Sences = [
        "wx_scan_pay"   => "hf_wx_scan_pay",
        "wx_wap_pay"    => "hf_wx_wap_pay",
        "ali_scan_pay"  => "hf_ali_scan_pay",
        "ali_wap_pay"   => "hf_ali_wap_pay",
    ];

    // 请求订单
    public function order( array $params, $notify ) {
        $pool = $params['pool'] ?: [];
        $phone = $pool['phone'];

        $query = [
            "merchant_no"       => self::MID,
            "merchant_order_no" => $params['pay_orderid'],
            "start_time"        => date('YmdHis'),
            "mobile"            => $phone,
            "amount"            => round($params['pay_amount'] / 100, 2),
            "type"              => $params['pool']['channel'],
            "pay_sence"         => $this->getSence( $params['pay_bankcode'] ),
            "notify_url"        => $notify ?: '',
            "return_url"        => $params['pay_returnurl'] ?: '',
            "sign_type"         => 1,
        ];

        var_dump($query);exit();

        $signData = $query;

        foreach ($signData as $key => $value) {
            if ($value == '') {
                unset($signData[$key]);
            }
        }

        $query['sign'] = createSign( self::SECRET, $signData );
        $data = sendForm(self::API_URL, $query);
        $data = json_decode($data, true);
        if ($data['code'] != 1) {
            Log::write(json_encode($data), Log::WARN);
            return false;
        }

        return new ChannelOrder( $data['data']['no'], $data['data']['wap_url'], $data['data']['code_url'], $pool['id'] );
    }
    // 查询订单
    public function query( $pay_orderid ) {}
    // 回调验证并且返回transID
    public function notify( array $request ) {

        if ($request['status'] != "Success") {
            return false;
        }

        $sign = createSign( self::SECRET , [
            'status'            => $request['status'],
            'msg'               => $request['msg'],
            'amount'            => $request['amount'],
            'merchant_order_no' => $request['merchant_order_no'],
            'no'                => $request['no'],
            'payment_time'      => $request['payment_time'],
            'pay_channel'       => $request['pay_channel'],
            'pay_channel_name'  => $request['pay_channel_name'],
        ]);

        if (!($sign === $request['sign'])){
            Log::write("sign err. sign: " . $sign . " === " . $request['sign'] );
            return false;
        }
        return $request['merchant_order_no'];

    }


    protected function getSence( $pay_bankcode ) {
        return self::Sences[$pay_bankcode] ?: '';
    }

    protected function sign(  $params ) {

    }


}