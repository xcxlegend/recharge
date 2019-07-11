<?php
/**
 * Created by PhpStorm.
 * User: Legend.Xie
 * Date: 2019/6/26
 * Time: 22:16
 */

namespace Common\Lib;


use Think\Exception;

class PoolDevLib implements IPoolLib
{
    public $pool;
    protected $RPC_PHONE_URL;
    const RPC_ORDER_API = "/v1/order/pay";

    public function __construct()
    {
       $this->RPC_PHONE_URL = C('RPC_POOL_PHONE');
    }


    public function query(&$params)
    {
        $money = $params['pay_amount']/100;
        $query = [
            'balance' => ['egt', $money]
        ];

        $ids = M('PoolProvider')->where( $query )->getField("id", true);
        if (!$ids) {
            throw new Exception("号码查询失败");
            return false;
        }

        M()->startTrans();
        $order = M('PoolPhones')->where(
            [
                'pid'   => ['in', $ids],
                'lock'  => 0,
                'money' => $money
            ]
        )->find();
        if (!$order) {
            M()->rollback();
            throw new Exception("号码查询失败");
            return false;
        }
        if (!M('PoolPhones')->where([ 'id' => $order['id']])->setField('lock', 1)){
            M()->rollback();
            throw new Exception("设置号码lock失败");
            return false;
        }
        M()->commit();
        $params['pool'] = $order;
        $this->pool = $order;
        return true;
    }

    public function reset()
    {
        if ($this->pool) {
            M('PoolPhones')->where([ 'id' => $this->pool['id']])->setField('lock', 0);
        }
    }

}