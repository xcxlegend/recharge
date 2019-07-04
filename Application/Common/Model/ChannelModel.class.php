<?php
/**
 * Created by PhpStorm.
 * User: Legend.Xie
 * Date: 2019/7/4
 * Time: 22:00
 */

namespace Common\Model;


class ChannelModel extends UseCacheModel
{

    const CACHE_KEY_PREFIX_ID = "channel:id:";
    const CACHE_KEY_PREFIX_CODE = "channel:code:";

    static public function getCacheKeyId( $id ) {
        return self::CACHE_KEY_PREFIX_ID . $id;
    }

    static public function getCacheKeyCode( $code ) {
        return self::CACHE_KEY_PREFIX_CODE . $code;
    }

    protected function setDataCache(&$data) {
        $this->getCache()->set( self::getCacheKeyId($data['id']), json_encode($data) );
        $this->getCache()->set( self::getCacheKeyCode($data['code']), json_encode($data) );
    }

    public function getByCode( $code ) {
        return $this->getCache()->getOrSet( self::getCacheKeyCode($code), function () use ($code){
            return $this->where(['code' => $code])->find();
        }, true, 60);
    }

    public function getById( $id ) {
        return $this->getCache()->getOrSet( self::getCacheKeyId($id), function () use ($id){
            return $this->where(['id' => $id])->find();
        }, true, 60);
    }


}
