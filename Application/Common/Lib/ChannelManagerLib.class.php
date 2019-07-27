<?php

/**
 * Created by PhpStorm.
 * User: Legend.Xie
 * Date: 2019/6/26
 * Time: 20:51
 */

namespace Common\Lib;

use Think\Exception;


class ChannelManagerLib
{

    public $IPhoneRechagerImpl;
    protected $channel;
    public $ptmgr;

    public function __construct()
    {
        /* $this->ptmgr = $ptmgr;
        $this->channel = $ptmgr->channel; */
    }


    static protected function  create($method, $ptmgr)
    {
        $classname = 'Common\\Lib\\' . ucfirst($method) . 'Lib';

        if (!class_exists($classname)) {
            throw new Exception("渠道方式不存在");
            return false;
        }
        $class = new $classname($ptmgr);
        return $class;
    }

    // 处理获取上游订单
    public function order(PaytypeMgrLib $ptmgr, $params, $notify_url, $pay_orderid)
    {
        $this->ptmgr = $ptmgr;
        $this->channel = $ptmgr->channel;

        $method = $this->channel['code'];
        $gateway = $this->channel['gateway'];

        $this->IPhoneRechagerImpl = self::create($method, $ptmgr);
        if (!$this->IPhoneRechagerImpl) {
            return false;
        }

        if ($this->IPhoneRechagerImpl instanceof IChannelLib) {

            //            if ($pool){
            //                if (!$pool->query($params)){
            //                    return false;
            //                }
            //            }
            if ($this->IPhoneRechagerImpl instanceof IPhoneRechagerLib) {
                $params['pool'] = $this->ptmgr->pool;
            }
            $order = $this->IPhoneRechagerImpl->order($params, $gateway, $notify_url, $pay_orderid);
            if (!($order && $order instanceof ChannelOrder)) {
                throw new Exception("渠道接口返回数据错误");
            }
            return $order;
        }

        throw new Exception("渠道方式接口错误不存在");
        return false;
    }

    public function reset()
    {

        $this->ptmgr->reset();

        // if ($this->IPhoneRechagerImpl && $this->IPhoneRechagerImpl instanceof IChannelLib) {
        //     $this->IPhoneRechagerImpl->reset();
        // }
    }


    // 处理回调信息
    static public function notify(PaytypeMgrLib $ptmgr, $method, $request)
    {

        $class = self::create($method, $ptmgr);
        if (!$class) {
            throw new Exception("渠道方式接口错误不存在");
            return false;
        }

        if ($class instanceof IChannelLib) {
            return $class->notify($request);
        }

        throw new Exception("渠道方式接口错误不存在");
        return false;
    }

    static public function notifyOK($method)
    {
        $class = self::create($method);
        return $class->notify_ok();
    }

    static public function notifyErr($method)
    {
        $class = self::create($method);
        return $class->notify_err();
    }


    // $request => pay_order
    public function query(PaytypeMgrLib $ptmgr, array &$order, array &$pool)
    {
        $method = $this->channel['code'];
        $gateway = $this->channel['gateway'];
        $class = self::create($method);
        if (!$class) {
            return false;
        }

        if ($class instanceof IChannelLib) {
            return $class->query($gateway, $order, $pool);
        }

        throw new Exception("渠道方式接口错误不存在");
        return false;
    }
}
