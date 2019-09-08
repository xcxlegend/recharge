<?php


namespace Common\Lib;


use Think\Exception;
use Think\Log;

class TranserManager
{

    protected $transer = null;
    public function __construct(&$channel)
    {
        $classname ='Common\\Lib\\' . ucfirst($channel['code']) . 'Lib';
        if ( !class_exists($classname) ) {
            Log::write($classname);
            throw new Exception("渠道方式不存在");
            return false;
        }
        $this->transer = new $classname($channel);
        if (!($this->transer instanceof IPoolTranser)){
            throw new Exception('渠道不是转发渠道');
        }
    }

    public function order(&$pool, $notify_url) {
        return $this->transer->order($pool, $notify_url);
    }

    public function notify(&$request) {
        return $this->transer->notify($request);
    }

    public function notifySuccess() {
        return $this->transer->notifySuccess();
    }


}