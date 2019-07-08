<?php
/**
 * Created by PhpStorm.
 * User: Legend.Xie
 * Date: 2019/6/26
 * Time: 21:39
 */

namespace Common\Lib;


// 号码池处理接口
interface IPoolLib
{

    public function query( &$params );
    public function reset( );

}