<?php


namespace Common\Lib;

class BigPayLib implements IDirectPayLib
{

    const API_ORDER = '/api/order';

    protected $username;
    protected $appkey;
    protected $gateway;
    protected $notify_url;


    const ERROR_CODE_OK = 1;

    /**
     * 1 成功
    -1
    参数错误
    -2
    签名错误
    -3
    用户信息错误
    -4
    用户被冻结
    -5
    用户无权限
    -6
    订单号重复
    -7
    金额无效
    -8
    保存订单错误 (服务器错误)
    -9
    无订单信息

     */


    public function __construct($notify_url)
    {
        $this->username = C('BigPay.username');
        $this->appkey   = C('BigPay.appkey');
        $this->gateway  = C('BigPay.gateway');
        $this->notify_url = $notify_url;
    }


    public function phoneOrder(&$pool)
    {
        $url = $this->gateway . self::API_ORDER;
        $data = [
            'username'      => $this->username,
            'out_trade_id'  => $pool['order_id'],
            'phone'         => $pool['phone'],
            'money'         => $pool['money'],
            'notify_url'    => $this->notify_url,
        ];

        $data['sign'] = createSign($this->appkey, $data);
        $response = sendForm($url, $data);
        $response = json_decode($response, true);
        return $response['code'] == self::ERROR_CODE_OK;
    }

    public function phoneNotify(&$request)
    {
        return true;
        // TODO: Implement phoneNotify() method.
    }

}