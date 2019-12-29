<?php


namespace Common\Lib;
use Common\Lib\ChannelOrder;
use Think\Exception;
use \Think\Log;


class AppRobotRechargeLib
{


    const GATEWAY   = 'http://47.108.119.14';
    const API_ORDER = '/charge-app/charge/createOrder.do';
    const API_QUERY = '/charge-app/charge/queryOrder.do';

    const APIKEY = 'phonesystem';
    const APISECRET = '6541230000012301';

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
        $query['outOrderNo'] = $pay_orderid;
        $query['phone'] = $params['phone'];
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

        //处理充值失败
        if ($params['orderStatus'] == 5) {
            $order = M('Order')->where(['pay_orderid' => $params['outOrderNo']])->find();
            if (!$order || $order['pay_status']==3) {
                exit(json_encode(['code' => -1]));
                return;
            }
            M('Order')->where(['id' => $order['id']])->save([
                'pay_status' => 3,
                'pay_successdate' => time(),
            ]);


            $pool = M('PoolPhones')->where(['id' => $order['pool_phone_id']])->find();
            $trans_id = $params['serialNo'];

            $provider = M('PoolProvider')->where(['id' => $pool['pid']])->find();

            $poolOrder = [
                'pool_id'           => $pool['id'],
                'pid'               => $pool['pid'],
                'out_trade_id'      => $pool['out_trade_id'],
                'order_id'          => $pool['order_id'],
                'data'              => json_encode($pool),
                'status'            => 1,
                'time'              => time(),
                'year'              => date('Y', time()),
                'month'             => date('m', time()),
                'day'               => date('d',time()),
                'money'             => $pool['money'],
                'channel'           => $pool['channel'],
                'phone'             => $pool['phone']
            ];

            if (!M('PoolFaild')->add($poolOrder)){
                exit(json_encode(['code' => -1]));
                Log::write("add poolFaildOrder err:" . json_encode($poolOrder));
                return;
            }

            //商户通知
            // $params = [ // 返回字段
            //     "memberid" => $order["pay_memberid"], // 商户ID
            //     "orderid" => $order['out_trade_id'], // 订单号
            //     'transaction_id' => $order["pay_orderid"], //支付流水号
            //     "amount" => intval($order["pay_amount"] * 100), // 交易金额
            //     "datetime" => date("YmdHis", $order['pay_successdate']), // 交易时间
            //     "status" => -1, // 交易状态
            // ];

            // $member_info = M('Member')->where(['id' => $order['pay_memberid']])->find();
            // $sign = $this->createSign($member_info['apikey'], $params);
            // $params["sign"] = $sign;
            // $params["attach"] = $order["attach"];
    
            // $contents = sendForm($order['pay_notifyurl'], $params);
    
            // Log::write("order notify faild: " . $order["id"] . " url: " . $order["pay_notifyurl"] . '?' . http_build_query($params) . " resp: " . $contents . '|' .json_encode($member_info));
            

            //号码商通知
            $params = [
                'appkey'        => $provider['appkey'],
                'phone'         => $pool['phone'],
                'money'         => intval($pool['money'] * 100),
                'out_trade_id'  => $pool['out_trade_id'],
                'status'        => -2,
            ];
    
            $sign = $this->createSign($provider['appsecret'], $params);
            $params["sign"] = $sign;
            $params['trans_id'] = $trans_id;
    
            $contents = sendForm($pool['notify_url'], $params);
    
            Log::write(" pool notify faild: ". $order["pay_orderid"] . " url: " . $pool["notify_url"] . http_build_query($params) . " resp: " . $contents);

            M('PoolPhones')->where(['id' => $pool['id']])->delete();
            exit(json_encode(['code' => 0]));
        }

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

    /**
     * 创建签名
     * @param $Md5key
     * @param $list
     * @return string
     */
    protected function createSign($Md5key, $list)
    {
        ksort($list);
        $md5str = "";
        foreach ($list as $key => $val) {
            // if (!empty($val)) {
                $md5str = $md5str . $key . "=" . $val . "&";
            // }
        }
        $sign = md5($md5str . "key=" . $Md5key);
        return $sign;
    }

}