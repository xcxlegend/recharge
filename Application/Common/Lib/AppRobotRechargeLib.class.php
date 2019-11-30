<?php


namespace Common\Lib;
use Common\Lib\ChannelOrder;
use Think\Exception;
use \Think\Log;


class AppRobotRechargeLib
{


    const GATEWAY   = 'http://39.100.96.66:11080';
    const API_ORDER = '/charge-app/charge/createOrder.do';
    const API_QUERY = '/charge-app/charge/queryOrder.do';

    const APIKEY = 'niexq1';
    const APISECRET = '1234561234567890';

    const Channels = [
        '1' => 'CMCC',
        '2' => 'CUCC',
        '3' => 'CT'
    ];

    const Sences = [
        "wx_scan_pay"   => "wx_scan",  //微信扫码
        "wx_wap_pay"    => "wx_h5", //微信H5
        "ali_scan_pay"  => "ali_scan",  //支付宝扫码
        "ali_wap_pay"   => "ali_h5",  //支付宝H5
    ];

    public function order(array $params, $gateway, $notify, $pay_orderid)
    {
        if (!$gateway){
            $gateway = self::GATEWAY;
        }

        $api_url = $gateway . self::API_ORDER;


        $query = [
            "apiKey"        => self::APIKEY,
            "amount"        => $params['money'],
            "notifyUrl"     => $notify ?: '',
            "outOrderNo"    => $this->encrypt($pay_orderid),
            "outTime"       => date('YmdHis'),
            "paySence"      => self::Sences[$params['pay_code']],
            "phone"         => $this->encrypt($params['phone']),
            "phoneType"     => self::Channels[$params['channel']],
            "signType"      => 'MD5'
        ];


        $query['sign'] = $this->sign($query);

        $request_time = date('Y-m-d h:i:s');
        $data = sendJson($api_url, $query);
        if (!$data) {
            return false;
        }
        $data = json_decode($data, true);

        $query['request_time'] = $request_time;
        $data['response_time'] = date('Y-m-d h:i:s');
        
        LogApiQuery($api_url, $query, $data);
        
        if ($data['code'] != 0) {
            Log::write(json_encode($data), Log::WARN);
            return  ['msg'=>$data['msg']];
        }

        return ['pay_no'=>$this->decrypt($data['data']['serialNo']),'pay_url'=>$this->decrypt($data['data']['payUrl'])];



    }

    public function query($gateway, array &$order, &$pool)
    {
        if (!$gateway){
            $gateway = self::GATEWAY;
        }
        $api_url = $gateway . self::API_QUERY;
        $params = [
            'apiKey'         => self::APIKEY,
            'orderNo'        => $this->encrypt($order['trade_id']),
            'outTime'        => date('YmdHis'),
            'signType'       => 'MD5',
        ];

        $params['sign'] = $this->sign($params);
        $data = sendJson( $api_url, $params );
        if (!$data) {
            return false;
        }
        LogApiQuery($api_url, $params, $data);
        $data = json_decode($data, true);
        $status = $this->decrypt($data['data']['orderStatus']);
        if ($status != 3) {
            return false;
        }
        return $status;
    }

    public function notify(array $request)
    {
       $params = [
            'id'            => $request['id'],
            'orderNo'       => $request['orderNo'],
            'orderStatus'   => $request['orderStatus'],
            'outTime'       => $request['outTime'],
            'signType'      => $request['signType'],
            'outOrderNo'      => $request['outOrderNo'],
            'serialNo'      => $request['serialNo'],
        ];

        if ( $this->sign($params) !== $request['sign']) {
            Log::write('sign error:'.json_encode($params), Log::WARN);
            return false;
        }

        $params['orderNo'] = $this->decrypt($request['orderNo']);
        $params['orderStatus'] = $this->decrypt($request['orderStatus']);
        $params['outOrderNo'] = $this->decrypt($request['outOrderNo']);
        $params['serialNo'] = $this->decrypt($request['serialNo']);

        Log::write('notify data:'.json_encode($params), Log::WARN);

        if ($params['orderStatus'] != 3) {
            return false;
        }
        

        return new ChannelNotifyData($params['outOrderNo'], $params['serialNo'], $request['success_url']); 
    }

    public static function notify_ok(array $request){

        return json_encode(['code' => 0]);
        return 'success';
    }
    public static function notify_err(array $request){
        return json_encode(['code' => -1]);
        return 'err';
    }

    protected function sign( $params ) {
        ksort($params);
        $md5str = http_build_query($params);
        $md5str .= self::APISECRET;
        $sign = md5(urldecode($md5str));
        return strtoupper($sign);
    }

    protected function encrypt($input, $key=self::APISECRET) {
        $size = mcrypt_get_block_size(MCRYPT_RIJNDAEL_128, MCRYPT_MODE_ECB);
        
        //pkcs5_pad
        $pad = $size - (strlen($input) % $size);
        $input =$input . str_repeat(chr($pad), $pad);

        $td = mcrypt_module_open(MCRYPT_RIJNDAEL_128, '', MCRYPT_MODE_ECB, '');
        $iv = mcrypt_create_iv (mcrypt_enc_get_iv_size($td), MCRYPT_RAND);
        mcrypt_generic_init($td, $key, $iv);
        $data = mcrypt_generic($td, $input);
        mcrypt_generic_deinit($td);
        mcrypt_module_close($td);
        $data = base64_encode($data);
        return $data;
    }


    protected function decrypt($str, $key=self::APISECRET) {
        $decrypted= mcrypt_decrypt(
            MCRYPT_RIJNDAEL_128,
            $key,
            base64_decode($str),
            MCRYPT_MODE_ECB
        );
        $dec = strlen($decrypted);
        $padding = ord($decrypted[$dec-1]);
        $decrypted = substr($decrypted, 0, -$padding);
        return $decrypted;
    }

}