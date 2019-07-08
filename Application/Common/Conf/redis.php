<?php
/**
 * Created by PhpStorm.
 * User: Legend.Xie
 * Date: 2019/7/3
 * Time: 19:28
 */


return [
    'DATA_CACHE_PREFIX' => 'recharge:',
    'DATA_CACHE_TYPE'=>'Redis',         //默认动态缓存为Redis
    'REDIS_HOST'=>'127.0.0.1',          //redis服务器ip，多台用逗号隔开；读写分离开启时，第一台负责写，其它[随机]负责读；
    'REDIS_PORT'=>'6379',
    'REDIS_PASS' => ''
];