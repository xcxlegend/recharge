<?php
/**
 * Created by PhpStorm.
 * User: Legend.Xie
 * Date: 2019/7/4
 * Time: 21:23
 */

namespace Common\Model;


class MemberModel extends UseCacheModel
{

    const CACHE_KEY_PREFIX_ID = "member:id:";

    static public function getCacheKeyId( $memberid ) {
        return self::CACHE_KEY_PREFIX_ID . $memberid;
    }

    protected function setDataCache(&$data) {
        $this->getCache()->set( self::getCacheKeyId($data['id']), json_encode($data) );
    }

    public function getById( $memberid ) {
        return $this->getCache()->getOrSet( self::getCacheKeyId($memberid), function () use ($memberid){
            return $this->where(['id' => $memberid])->find();
        }, true, 60);
    }


}