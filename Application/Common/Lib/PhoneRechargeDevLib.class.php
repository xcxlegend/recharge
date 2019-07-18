<?php
/**
 * Created by PhpStorm.
 * User: Legend.Xie
 * Date: 2019/6/26
 * Time: 20:14
 */

namespace Common\Lib;
use Think\Exception;
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


    const GATEWAY = 'http://148.70.91.219';
    const API_ORDER = '/api/reptile/pay.html';
    const API_QUERY = '/api/reptile/find';

    const MID = "2019828315";
    const SECRET = "a854278887892da7e3dadad7d7ae34f7";
    const Sences = [
        "wx_scan_pay"   => "hf_wx_scan_pay",
        "wx_wap_pay"    => "hf_wx_wap_pay",
        "ali_scan_pay"  => "hf_ali_scan_pay",
        "ali_wap_pay"   => "hf_ali_wap_pay",
    ];

    // 请求订单
    public function order( array $params, $gateway, $notify, $pay_orderid ) {
        if (!$gateway){
            $gateway = self::GATEWAY;
        }
        $api_url = $gateway . self::API_ORDER;

        $this->poolQuery(new PoolDevLib(), $params);

        $pool = $params['pool'] ?: [];
        $phone = $pool['phone'];

        $query = [
            "merchant_no"       => self::MID,
            "merchant_order_no" => $pay_orderid,
            "start_time"        => date('YmdHis'),
            "mobile"            => $phone,
            "amount"            => round($params['pay_amount'] / 100, 2),
            "type"              => $params['pool']['channel'],
            "pay_sence"         => $this->getSence( $params['pay_bankcode'] ),
            "notify_url"        => $notify ?: '',
            "return_url"        => $params['pay_returnurl'] ?: '',
            "sign_type"         => 1,
        ];

        $signData = $query;

        foreach ($signData as $key => $value) {
            if ($value == '') {
                unset($signData[$key]);
            }
        }

        $query['sign'] = createSign( self::SECRET, $signData );
        $data = sendForm($api_url, $query);
        $data = json_decode($data, true);
        if ($data['code'] != 1) {
            Log::write(json_encode($data), Log::WARN);
            throw new Exception( '[RECHARGER] ' . $data['msg']);
            return false;
        }

        return new ChannelOrder( $data['data']['no'], $data['data']['wap_url'], $data['data']['code_url'], $pool['id'], $pool['pid']);
    }

    // 查询订单
    public function query( $gateway, array &$order, &$pool ) {
        if (!$gateway){
            $gateway = self::GATEWAY;
        }
        $api_url = $gateway . self::API_QUERY;
        /**
         * merchant_no	是	String	10	网厅分配的唯一商户号	2019061212
        2	no	是	String	35	网厅订单号	452958731168671921
        3	type	否	String	1	运营商,移动(默认) 2电信 3联通	1
        4	sign_type	是	String	1	签名类型(1->md5)	1
        5	sign
         */



        $params = [
            'merchant_no' => MID,
            'no'          => $order['pay_orderid'],
            'type'        => $pool['channel'],
            'sign_type'   => '1',
        ];

        $params['sign'] = $this->sign($params);
        $data = sendForm( $api_url, $params );
        if (!$data) {
            return false;
        }
        $data = json_decode($data, true);
        if ($data['code'] != 1) {
            return false;
        }
        return $data['data']['status'] == 1;
    }

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

    public static function notify_ok(){
        return 'success';
    }
    public static function notify_err(){
        return 'err';
    }

    protected function getSence( $pay_bankcode ) {
        return self::Sences[$pay_bankcode] ?: '';
    }

    protected function sign( &$params ) {
        return createSign( self::SECRET, $params);
    }


}