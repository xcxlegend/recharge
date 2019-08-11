<?php


namespace Common\Lib;

class TestTransePhoneLib implements IPoolTranser
{
    public function __construct()
    {

    }

    public function order(&$pool){

    }


    public function query(&$poolOrder){}

    /**
     * @param $request
     * @return array
     */
    public function notify(&$request){}

}