<?php


namespace Common\Lib;

use Think\Log;

class TestTransePhoneLib implements IPoolTranser
{
    protected $channel;

    public function __construct($channel)
    {
        $this->channel = $channel;
    }

    public function order(&$pool, $notify_url){
        Log::write("TestTransePhoneLib order:" . $notify_url);
        return true;
    }


    public function query(&$poolOrder){}

    public function notify(&$request): ChannelNotifyData
    {
        return true;
        // TODO: Implement notify() method.
    }

    /**
     * @param $request
     * @return array
     */

    public function notifySuccess()
    {
        // TODO: Implement notifySuccess() method.
    }

}