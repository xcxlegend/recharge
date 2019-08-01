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
    const API_URL = "/api/recharge";

    const GATEWAY   = 'http://118.190.244.135';
    const API_ORDER = '/api/recharge';
    const API_QUERY = '/api/query';

    const SECRET = 'secret=1db533b8a718d50468ada8ad2a961e73';

    const Channels = [
        '1' => '1',
        '2' => '2',
        '3' => '3'
    ];

    const Sences = [
        "wx_scan_pay"   => "hf_wx_scan_pay",
        "wx_wap_pay"    => "hf_wx_wap_pay",
        "ali_scan_pay"  => "hf_ali_scan_pay",
        "ali_wap_pay"   => "hf_ali_wap_pay",
    ];

    public function order(array $params, $gateway, $notify, $pay_orderid)
    {
        if (!$gateway){
            $gateway = self::GATEWAY;
        }

        $api_url = $gateway . self::API_ORDER;

        $this->poolQuery(new PoolDevLib(), $params);

        $pool = $params['pool'] ?: [];
        $phone = $pool['phone'];
      
        $query = [
            "merchant_order_no" => $pay_orderid, //'22345678901234567890123456789012542',//$pay_orderid,
            "start_time"        => date('YmdHis'),
            "mobile"            => $phone,
            "amount"            => number_format($params['pay_amount'] / 100, 3),
            "type"              => $params['pool']['channel'],
            "pay_sence"         => strval($this->getSence( $params['pay_bankcode'] )),
            "notify_url"        => $notify ?: '',
            // "return_url"        => $params['pay_returnurl'] ?: $notify,//'',
            "sign_type"         => '1',
        ];

        /*
        "merchant_order_no": "12345678901234567890123456789012345",
        "notify_url": "http://www.baidu.com",
        "start_time": "20190630192450",
        "mobile": "13635279255",
        "amount": "10.000",
        "type": "1",
        "pay_sence": "hf_ali_wap_pay",
        "sign_type": "1",
        "sign":
         */


//         $query = json_decode('{
//         "merchant_order_no": "12345678901234567890123456789012345",
//         "notify_url": "http://www.baidu.com",
//         "start_time": "20190630192450",
//         "mobile": "13635271568",
//         "amount": "10.000",
//         "type": "1",
//         "pay_sence": "hf_ali_wap_pay",
//         "sign_type": "1",
//         "sign": "9572d838e0ee5d3ee88856dd6928a2f7"
// }', true);
        

        $query['sign'] = $this->sign($query);
        $data = sendJson($api_url, $query);
        // $data = sendForm($api_url, $query);
        /*
         "no": "455835544175192664",
        "wap_url": "https://qr.alipay.com/upx08672yckvwcfa0d0x0010",
        "code_url": "",
        "sign": "8fce09f609c71efebcf0bfbb13236834",
        "status": 1000,
        "desc": "保存订单成功"
         */
        if (!$data) {
            throw new Exception( '[RECHARGER] fail');
            return false;
        }
        $data = json_decode($data, true);
         if ($data['code'] != 1) {
            Log::write(json_encode($data), Log::WARN);
            throw new Exception( '[RECHARGER] ' . $data['msg']);
            return false;
        }

        /**
         "no": "455835544175192664",
        "wap_url": "https://qr.alipay.com/upx08672yckvwcfa0d0x0010",
        "code_url": "",
        "sign": "8fce09f609c71efebcf0bfbb13236834",
        "status": 1000,
        "desc": "保存订单成功"
        }
         */

        return new ChannelOrder( $data['data']['no'], $data['data']['wap_url'], $data['data']['code_url'], $pool['id'], $pool['pid']);

    }

    public function query($gateway, array &$order, &$pool)
    {
        if (!$gateway){
            $gateway = self::GATEWAY;
        }
        $api_url = $gateway . self::API_QUERY;

        /*
            "no":"457103664175297384",
            "type":"1",
            "sign_type":"1",
            "sign":"fe9d818131fb9d4f695032302e4a025d"
        */
        $params = [
            'no'          => $order['trade_id'],
            'type'        => $pool['channel'],
            'sign_type'   => '1',
        ];

        $params['sign'] = $this->sign($params);
        $data = sendJson( $api_url, $params );
        if (!$data) {
            return false;
        }
        $data = json_decode($data, true);
        if ($data['code'] != 1) {
            return false;
        }
        return $data['data']['status'] == 1;
    }

    public function notify(array $request)
    {
        /*
        status
msg
amount
merchant_order_no
no
payment_time
pay_channel
pay_channel_name
sign

         */
        // status=Success&msg=%E4%BA%A4%E6%98%93%E6%88%90%E5%8A%9F&amount=10.000&merchant_order_no=MP201907192317053792&no=456275813178491711&payment_time=2019-07-19+23%3A18%3A07&pay_channel=hf_ali_wap_pay&pay_channel_name=hf_ali_wap_pay&sign=4a8e17501321de96cdb2febc8a32d2e9&Method=ShenRobotRecharge
        $params = [
            'status'            => $request['status'],
            'msg'               => $request['msg'],
            'amount'            => $request['amount'],
            'merchant_order_no' => $request['merchant_order_no'],
            'no'                => $request['no'],
            'payment_time'      => $request['payment_time'],
            'pay_channel'       => $request['pay_channel'],
            'pay_channel_name'  => $request['pay_channel_name'],
            'success_url'       => $request['success_url'],
        ];

        if ( $this->sign($params) !== $request['sign']) {
            return false;
        }


        if ($request['status'] != 'Success') {
            return false;
        }

        return new ChannelNotifyData($request['merchant_order_no'], $request['no'], $request['success_url']); //[$request['merchant_order_no'], $request['no']];
    }

    public static function notify_ok(){
        return json_encode(['status' => 1, 'msg' => 'success']);
        return 'success';
    }
    public static function notify_err(){
        return json_encode(['status' => 0, 'msg' => 'error']);
        return 'err';
    }
    protected function getChannel( $channel ) {
        return self::Channels[$channel] ?: '';
    }
    protected function getSence( $pay_bankcode ) {
        return self::Sences[$pay_bankcode] ?: '';
    }

    protected function sign( $params ) {
        ksort($params);
        $md5str = "";
        foreach ($params as $key => $val) {
           if (!empty($val)) {
                $md5str = $md5str . $key . "=" . $val . "&";
           }
        }
        $md5str .= self::SECRET;
        $sign = md5($md5str);
        return $sign;
    }

}