<?php


namespace Common\Lib;


abstract class BaseTransLib implements IPoolTranser
{
    protected $channel;

    public function __construct($channel)
    {
        $this->channel = $channel;
    }
}