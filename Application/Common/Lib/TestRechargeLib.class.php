<?php
/**
 * Created by PhpStorm.
 * User: Legend.Xie
 * Date: 2019/7/5
 * Time: 23:29
 */

namespace Common\Lib;


class TestRechargeLib extends IPhoneRechagerLib
{

    public function order(array $params, $gateway, $notify, $pay_orderid)
    {
        $this->poolQuery(new PoolDevLib(), $params);
        $pool = $params['pool'] ?: [];
        $url = 'http://testurl';
        $orderNo = createUUID("CR");
        return new ChannelOrder( $orderNo, $url, $url, $pool['id'], 0, '123123' );
    }

    public function query($gateway, array &$order, &$pool)
    {
        return true;
    }

    public function notify(array $request)
    {
        return [$request['orderid'], $request['no']];
    }

    public static function notify_ok(){
        return 'success';
    }

    public static function notify_err(){
        return 'err';
    }


}