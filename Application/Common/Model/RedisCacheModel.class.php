<?php
/**
 * Created by PhpStorm.
 * User: Legend.Xie
 * Date: 2019/7/3
 * Time: 19:30
 */

namespace Common\Model;
use \Redis;

class RedisCacheModel
{

    protected $RedisClient;

    public function __construct()
    {
        $this->RedisClient = new \Redis();
        $this->RedisClient->connect(C('REDIS_HOST'),C('REDIS_PORT')) or die("redie connect error");
        if (C('REDIS_PASS')) {
            $this->RedisClient->auth(C('REDIS_PASS'));
        }
    }

    static public function instance() {
        return new self();
    }

    public function Client() {
        return $this->RedisClient;
    }

    public function get($key) {
        return $this->RedisClient->get($key);
    }

    public function set( $key, $value, $timeout = 0 ) {
        return $this->RedisClient->set( $key, $value, $timeout);
    }

    public function setWithFormat( $key, $value, $timeout = 0  ) {
        return $this->RedisClient->set( $key, json_encode($value), $timeout);
    }

    /**
     * @param $key
     * @param $valFunc call function 延迟调用
     * @return string
     */
    public function getOrSet( $key, $valFunc, $formatter = false, $timeout = 0 ) {
        $val = $this->get($key);
        if ( $val === false && is_callable($valFunc) ) {
            $val = $formatter ? json_encode($valFunc()) : '';
            $this->set( $key, $val, $timeout );
        }
        return $val ? ($formatter ? json_decode($val, true) : '' ): '';
    }






}