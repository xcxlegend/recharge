<?php


namespace Pay\Controller;


use Common\Lib\TranserManager;
use Think\Exception;

class TransController extends OrderController
{
    /**
     * 处理回调
     */
    public function Notify()
    {
        $method = $this->request['Method'];
        $channel = M('Channel')->where(['code' => $method])->find();
        try{
            $manager = new TranserManager($channel);
            $res = $manager->notify($this->request);
            if ($res) {
                $order_id = $res->pay_orderid;
                $order = D('PoolOrder')->where(['order_id' => $order_id])->find();
                if (!$order) {
                    throw new Exception("no order");
                    return;
                }
                $provider = D('PoolProvider')->find($order['pid']);
                if (!$provider) {
                    throw new Exception("no provider");
                    return;
                }
                $pool = D('PoolPhones')->find($order['pool_id']);
                if (!$pool) {
                    throw new Exception("no pool phones");
                    return;
                }

                $this->handlePoolOrderSuccess($pool, $provider,$res->trans_id);
                D('PoolOrder')->where(['id' => $order['id']])->setField([
                    'status' => 1,
                    'finish_time' => $this->timestamp,
                    'trans_id' => $res->trans_id
                ]);
                $res = $manager->notifySuccess();
                $this->log($res);
                echo $res;
                return;
                $this->result_success('');
            }
        } catch (Exception $e) {
            $this->result_error($e->getMessage());
            exit;
        }
    }


}