<?php


namespace Common\Lib;

class ChannelNotifyData
{
    public $pay_orderid;
    public $trans_id;
    public $success_url;

    public function __construct($pay_orderid, $trans_id = '', $success_url = '')
    {
        $this->pay_orderid  = $pay_orderid;
        $this->trans_id     = $trans_id;
        $this->success_url  = $success_url;
    }
}