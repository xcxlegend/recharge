<?php

namespace Common\Lib;

/** 直接充值接口
 * Interface IDirectPay
 * @package Common\Lib
 */
interface IDirectPayLib
{
    /**
     * @param $pool
     * @return mixed
     */
    public function phoneOrder(&$pool);

    /**
     * @param $request
     * @return mixed
     */
    public function phoneNotify(&$request);
}