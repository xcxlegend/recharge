<?php


namespace Common\Lib;


use Think\Exception;

class TranserManager
{
    static public function order(&$channel, &$pool) {
        $classname ='Common\\Lib\\' . ucfirst($channel['code']) . 'Lib';
        if ( !class_exists($classname) ) {
            throw new Exception("渠道方式不存在");
            return false;
        }
        $class = new $classname($channel);
        if ($class instanceof IPoolTranser){
            return $class->order($pool);
        }
        throw new Exception('渠道不是转发渠道');
    }

}