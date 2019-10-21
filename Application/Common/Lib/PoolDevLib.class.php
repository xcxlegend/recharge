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
use Think\Log;

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
        //请口请求入库
        D('Admin/OrderStatis')->setStatis(intval($params["pay_memberid"] - 10000),'do_order');

        $money = $params['pay_amount'] / 100;
        $pay_code = $params['pay_bankcode'];
        $query = [
            'balance' => ['egt', $money],
        ];

        $ids = M('PoolProvider')->where($query)->getField("id", true);
        if (!$ids) {
            throw new Exception("号码商查询失败");
            return false;
        }
        Log::record("request: " . json_encode($params, JSON_UNESCAPED_UNICODE), LOG::DEBUG);
        $i = 0;
        while($i < 3) {
            M()->startTrans();
            $count = M('PoolPhones')->where([
                'pid' => ['in', $ids],
                'lock' => 0,
                'pay_code'=>$pay_code,
                'money' => $money,
            ])->count();
            if (!$count) {
                Log::record("FAIL for count == 0", LOG::WARN);
                break;
            }
            $startId = M('PoolPhones')->where([
                'pid' => ['in', $ids],
                'lock' => 0,
                'pay_code'=>$pay_code,
                'money' => $money,
            ])->limit(1)->getField('id');
            $id = $startId + mt_rand(0, $count - 1);
            $order = M('PoolPhones')->where(
                [
                    'pid' => ['in', $ids],
                    'lock' => 0,
                    'pay_code'=>$pay_code,
                    'money' => $money,
                    'id' => ['egt', $id]
                ]
            )->limit(1)->order('id asc')->lock(true)->find();
            if (!$order) {
                M()->rollback();
                $i++;
                continue;
            }
            if (!M('PoolPhones')->where(['id' => $order['id'], 'lock' => 0])->setField('lock', 1)) {
                Log::record("FAIL save lock", LOG::WARN);
                M()->rollback();
                $order = null;
                $i++;
                continue;
            }
            M()->commit();
            break;
        }

        if (!$order) {
            Log::record("FAIL USE times: {$i}", LOG::WARN);
            throw new Exception("号码查询失败");
            return false;
        }

        $params['pool'] = $order;
        $this->pool = $order;

        //
        $timeout = C('POOL_PHONE_ORDER_WX_TIMEOUT', null, 90);//90;
        if (strpos($params['pay_bankcode'], 'ali') === 0 || $params['pay_bankcode'] == 'wx_wap_pay') {
            $timeout = C('POOL_PHONE_ORDER_ALI_TIMEOUT', null, 240);//240;
        }

        // $pipe = $this->cache->Client()->multi();
        // $pipe->zDelete( self::CACHE_KEY_POOL_TIMEOUT, $order['id'] );
        $this->cache->Client()->zAdd(self::CACHE_KEY_POOL_TIMEOUT, time() + $timeout, $order['id']);
        // $pipe->exec();

        D('Admin/PoolStatis')->setStatis($order['pid'],'match_num');
        D('Admin/PoolStatis')->setStatis($order['pid'],'match_money',$order['money']);

        return $order;
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
