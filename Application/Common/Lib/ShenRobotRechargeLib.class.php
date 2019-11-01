<?php
/**
 * Created by PhpStorm.
 * User: Legend.Xie
 * Date: 2019/7/3
 * Time: 22:58
 */

namespace Common\Lib;
use Common\Lib\ChannelOrder;
use Think\Exception;
use \Think\Log;


class ShenRobotRechargeLib
{
    const API_URL = "/api/recharge";

    const GATEWAY   = 'http://118.190.244.135';
    const API_ORDER = '/api/recharge';
    const API_QUERY = '/api/query';

    const SECRET = 'secret=helloworld';

    const Channels = [
        '1' => '1',
        '2' => '2',
        '3' => '3'
    ];

    const Sences = [
        "wx_scan_pay"   => "wxpay",  //微信扫码
        "wx_wap_pay"    => "wapwxpay", //微信H5
        "ali_scan_pay"  => "alipay",  //支付宝扫码
        "ali_wap_pay"   => "wapalipay",  //支付宝H5
    ];

    public function order(array $params, $gateway, $notify, $pay_orderid)
    {
        if (!$gateway){
            $gateway = self::GATEWAY;
        }

        $api_url = $gateway . self::API_ORDER;


        $query = [
            "merchant_order_no" => $pay_orderid,
            "start_time"        => date('YmdHis'),
            "mobile"            => $params['phone'],
            "amount"            => number_format($params['money'] / 100, 0),
            "type"              => $params['channel'],
            "pay_sence"         => strval($this->getSence($params['pay_code'] )),
            "notify_url"        => $notify ?: '',
            "sign_type"         => '1',
        ];

        $query['sign'] = $this->sign($query);
        $request_time = date('Y-m-d h:i:s');
        $data = sendJson($api_url, $query);
        if (!$data) {
            //throw new Exception( '[RECHARGER] fail');
            return false;
        }
        $data = json_decode($data, true);

        $query['request_time'] = $request_time;
        $data['response_time'] = date('Y-m-d h:i:s');

        
        LogApiQuery($api_url, $query, $data);

        $data = json_decode($data, true);
        
        if ($data['code'] != 200) {
            Log::write(json_encode($data), Log::WARN);
            //throw new Exception( '[RECHARGER] ' . $message);
            return  ['msg'=>$data['msg']];
        }

        return ['pay_no'=>$data['data']['order_no'],'pay_url'=>$data['data']['pay_url']];

        //return new ChannelOrder($data['data']['order_no'], $data['data']['wap_url'], $data['data']['pay_url'], $pool['id'], $pool['pid']);

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
            'order_no'          => $order['trade_id'],
            'type'        => $pool['channel'],
            //'sign_type'   => '1',
        ];

        //$params['sign'] = $this->sign($params);
        $data = sendJson( $api_url, $params );
        if (!$data) {
            return false;
        }
        LogApiQuery($api_url, $params, $data);
        $data = json_decode($data, true);
        if ($data['data']['status'] == -1) {
            return false;
        }
        return $data['data']['status'];
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
            Log::write('sign error:'.json_encode($params), Log::WARN);
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