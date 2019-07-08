<?php
/**
 * Created by PhpStorm.
 * User: Legend.Xie
 * Date: 2019/7/4
 * Time: 21:27
 */

namespace Common\Model;


use Think\Model;

class UseCacheModel extends Model
{

    static $cache = null;

    protected function _initialize() {
        if (!self::$cache) {
            self::$cache = RedisCacheModel::instance();
        }
    }

    protected function getCache() {
        return self::$cache;
    }

    protected function _after_update($data, $options) {
        $data = $this->find($data[$this->getPk()]);
        $data && $this->setDataCache($data);
    }

    protected function _after_insert($data, $options) {
        $this->setDataCache($data);
    }

    protected function setDataCache(&$data) {}

}