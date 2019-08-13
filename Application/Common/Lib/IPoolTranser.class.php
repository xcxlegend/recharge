<?php


namespace Common\Lib;

use Think\Exception;

/**
 * 号码转发下游的接口
 * Interface IPoolTranser
 * @package Common\Lib
 */
interface IPoolTranser
{
    public function order(&$pool, $notify_url);
    public function query(&$poolOrder);

    /**
     * @param $request
     * @return ChannelNotifyData
     */
    public function notify(&$request): ChannelNotifyData;
    public function notifySuccess();
}
