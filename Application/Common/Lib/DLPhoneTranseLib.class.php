<?php


namespace Common\Lib;


use Think\Exception;
use Think\Log;


class DLPhoneTranseLib extends BaseTransLib implements IPoolTranser
{

    const MID    = "hrxfqg";
    const PASSWORD = "hrxfqg123456";
    const SECRET = "hrxfqg98474186345212";

    const CHANNELS = [
        '10' => 'Y10Y10',
        '20' => 'Y10Y20',
        '30' => 'Y10Y30',
        '50' => 'Y10Y50',
        '100' => 'Y10Y100',
        '200' => 'Y10Y200',
        '500' => 'Y10Y500',

    ];

    const APIORDER = 'http://119.147.44.182/index.php/Api/Order/flow'; 
    const APIURL = 'http://119.147.44.198/index.php/Api/Order/order_search'; 




    public function order(&$pool, $notify_url)
    {

        $params = [
            'account'         => self::MID, //用户名
            'orderNumber'       => $pool['order_id'],
            'mobile'       => $pool['phone'], //所充话费的手机号码
            'flowCode'    => self::CHANNELS[$pool['money']], //产品编码
            'chargeType'    => 0, //充值类型  0 快充(默认) 1 自动慢充 2 手动慢充 
            'callbackURL'   => urlencode($notify_url), //回调地址
        ];

        //大写( md5( md5(密码)+手机号码+产品编码+商户订单号+秘钥 ))
        $params['sign'] = strtoupper(md5(md5(self::PASSWORD).$params['mobile'].$params['flowCode'].$params['orderNumber'].self::SECRET));

        $api = self::APIORDER;

        $res = sendForm($api, $params);

        LogApiQuery($api, $params, $res);

        $resData = json_decode($res, true);

        return $resData['status'] == "2000";
    }

    public function query(&$poolOrder)
    {
        $params = [
            'account'         => self::MID, //用户名
            'user_ordernum'       => $pool['order_id'],//商户订单号
        ];

        //大写( md5( md5(密码)+ 商户订单号/平台订单号+秘钥 ) )
        $params['sign'] = strtoupper(md5(md5(self::PASSWORD).$params['user_ordernum'].self::SECRET));

        $api = self::APIURL;

        $res = sendForm($api, $params);

        return json_decode($res, true);
    }

    /**
     * @param $request
     * @return array
     */
    public function notify(&$request)
    {

        //大写( md5( md5(密码)+ 平台订单号+秘钥 ) )
        $sign = strtoupper(md5(md5(self::PASSWORD).$request['order_number'].self::SECRET));

        if ($sign !== $request['sign']) {
            Log::write("sign err: {$sign} !== {$request['sign']}");
            throw new Exception('sign error');
        }
        return new ChannelNotifyData($request['user_ordernum'], $request['voucher'], '' );

    }



    public function notifySuccess()
    {
        return 'success';
    }


}
