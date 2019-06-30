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

    static protected function  create( $method )  {
        $classname = 'Common\\Lib\\' . ucfirst($method) . 'Lib';

        if ( !class_exists($classname) ) {
            throw new Exception("渠道方式不存在");
            return false;
        }
        $class = new $classname();
        return $class;
    }

    // 处理获取上游订单
    static public function order( $method, $params, $notify_url, IPoolLib $pool = null ) {
        $class = self::create($method);
        if  (!$class){
            return false;
        }

        if ($class instanceof IChannelLib) {

            if ($pool){
                if (!$pool->query($params)){
                    return false;
                }
            }

            $order = $class->order( $params, $notify_url );
            if ( ! ( $order && $order instanceof ChannelOrder ) ) {
                throw new Exception("渠道接口返回数据错误");
            }
            return $order;
        }

        throw new Exception("渠道方式接口错误不存在");
        return false;
    }

    // 处理回调信息
    static public function notify( $method, $request ) {

        $class = self::create($method);
        if  (!$class){
            return false;
        }

        if ($class instanceof IChannelLib) {
            return $class->notify( $request );
        }

        throw new Exception("渠道方式接口错误不存在");
        return false;
    }

    static public function query( $method, $request ) {

        $class = self::create($method);
        if  (!$class){
            return false;
        }

        if ($class instanceof IChannelLib) {
            return $class->query( $request );
        }

        throw new Exception("渠道方式接口错误不存在");
        return false;
    }



}