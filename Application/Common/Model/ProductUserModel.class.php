<?php
/**
 * Created by PhpStorm.
 * User: Legend.Xie
 * Date: 2019/7/4
 * Time: 22:04
 */

namespace Common\Model;


class ProductUserModel extends UseCacheModel
{

    const CACHE_KEY_PREFIX_MIX = "product_user:mix:";

    static public function getCacheKeyPIdAndMid( $pid, $mid ) {
        return self::CACHE_KEY_PREFIX_MIX . $pid . ':'. $mid;
    }

    public function addAll($dataList,$options=array(),$replace=false){
        $result = parent::addAll($dataList,$options,$replace);
        if ($result) {
            foreach ($dataList as $data) {
                $this->setDataCache($data);
            }
        }
    }

    protected function setDataCache(&$data) {
        $this->getCache()->set( self::getCacheKeyPIdAndMid($data['pid'], $data['userid']), json_encode($data) );
    }

    public function getByMix( $pid, $mid ) {
        return $this->getCache()->getOrSet( self::getCacheKeyPIdAndMid($pid, $mid), function () use ($pid, $mid ){
            return $this->where([
                'userid' => $mid,
                'pid'    => $pid
            ])->find();
        }, true, 60);
    }


}