<?php


namespace Common\Lib;

class BigPayLib implements IDirectPayLib
{

    const API_ORDER = '/api/order';

    protected $username;
    protected $appkey;
    protected $gateway;
    protected $notify_url;

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
            'money'         => $pool['money'] / 100,
            'notify_url'    => $this->notify_url,
        ];

        $data['sign'] = createSign($this->appkey, $data);
        $response = sendForm($url, $data);
        $response = json_decode($response, true);
    }

    public function phoneNotify(&$request)
    {
        // TODO: Implement phoneNotify() method.
    }

}