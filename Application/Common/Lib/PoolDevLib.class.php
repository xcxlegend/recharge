<?php
/**
 * Created by PhpStorm.
 * User: Legend.Xie
 * Date: 2019/6/26
 * Time: 22:16
 */

namespace Common\Lib;

use Common\Model\RedisCacheModel;
use Think\Exception;

class PoolDevLib implements IPoolLib
{
    public $pool;
    protected $RPC_PHONE_URL;
    const RPC_ORDER_API = "/v1/order/pay";
    // const CACHE_KEY_POOL_NOPAY = 'pool_phone_nopay';
    const CACHE_KEY_POOL_TIMEOUT = 'pool_phone_timeout';

    protected $cache;
    public function __construct()
    {
        $this->RPC_PHONE_URL = C('RPC_POOL_PHONE');
        $this->cache = RedisCacheModel::instance();
    }

    public function query(&$params)
    {
        $money = $params['pay_amount'] / 100;
        $query = [
            'balance' => ['egt', $money],
        ];

        $ids = M('PoolProvider')->where($query)->getField("id", true);
        if (!$ids) {
            throw new Exception("号码查询失败");
            return false;
        }

        M()->startTrans();
        $order = M('PoolPhones')->where(
            [
                'pid' => ['in', $ids],
                'lock' => 0,
                'money' => $money,
            ]
        )->limit(1)->order('id desc')->find();
        if (!$order) {
            M()->rollback();
            throw new Exception("号码查询失败");
            return false;
        }
        if (!M('PoolPhones')->where(['id' => $order['id']])->setField('lock', 1)) {
            M()->rollback();
            throw new Exception("设置号码lock失败");
            return false;
        }
        M()->commit();
        $params['pool'] = $order;
        $this->pool = $order;

        //
        $timeout = C('POOL_PHONE_ORDER_WX_TIMEOUT', null, 90);//90;
        if (strpos($params['pay_bankcode'], 'ali') === 0 || $params['pay_bankcode'] == 'wx_wap_pay') {
            $timeout = C('POOL_PHONE_ORDER_ALI_TIMEOUT', null, 90);//240;
        }

        // $pipe = $this->cache->Client()->multi();
        // $pipe->zDelete( self::CACHE_KEY_POOL_TIMEOUT, $order['id'] );
        $this->cache->Client()->zAdd(self::CACHE_KEY_POOL_TIMEOUT, time() + $timeout, $order['id']);
        // $pipe->exec();

        return true;
    }

    public function reset()
    {
        if ($this->pool) {
            M('PoolPhones')->where(['id' => $this->pool['id']])->setField('lock', 0);
//            $this->cache->Client()->zDelete( self::CACHE_KEY_POOL_NOPAY, $this->pool['id'] );
            // $pipe = $this->cache->Client()->multi();
            // $pipe->zDelete( self::CACHE_KEY_POOL_NOPAY, $this->pool['id'] );
            $timeout = C('POOL_PHONE_TIMEOUT', null, 30);
            $this->cache->Client()->zAdd(self::CACHE_KEY_POOL_TIMEOUT, $this->pool['time'] + $timeout, $this->pool['id']);
            // $pipe->exec();

        }
    }

}
