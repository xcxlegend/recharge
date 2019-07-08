<?php
/**
 * Created by PhpStorm.
 * User: Legend.Xie
 * Date: 2019/7/4
 * Time: 22:25
 */

namespace Common\Model;


class PoolProviderModel extends UseCacheModel
{

    const CACHE_KEY_PREFIX_ID = "pool_provider:id:";

    static public function getCacheKeyId( $id ) {
        return self::CACHE_KEY_PREFIX_ID . $id;
    }

    protected function setDataCache(&$data) {
        $this->getCache()->set( self::getCacheKeyId($data['id']), json_encode($data) );
    }

    public function getById( $id ) {
        return $this->getCache()->getOrSet( self::getCacheKeyId($id), function () use ($id){
            return $this->where(['id' => $id])->find();
        }, true, 60);
    }


}