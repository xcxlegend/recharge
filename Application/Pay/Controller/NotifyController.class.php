<?php
/**
 * Created by PhpStorm.
 * User: Legend.Xie
 * Date: 2019/6/20
 * Time: 18:15
 */

namespace Pay\Controller;


class NotifyController extends OrderController
{
    protected $request;

    protected $timestamp;

    public function __construct()
    {
        parent::__construct();
        $this->timestamp = time();
    }


    /*
     * status	是	String	返回码
2	msg	是	String	状态描述
3	amount	是	String	交易金额（单位：元）
4	merchant_order_no	是	String	商户订单号
5	no	是	String	交易订单号
6	payment_time	是	String	业务结束时间（例：2019-06-04 15:28:24）
7	pay_channel	是	String	交易类型
8	pay_channel_name	是	String	交易类型描述
9	sign	是	String	签名
     */

    // 处理回调
    public function index() {

        // 1. 回调参数/签名判定

        if ($this->request['status'] != 1) {
            $this->result_error( "status" );
            return;
        }

        if ( !$this->check() ){
            $this->result_error("sign", $this->request);
            return;
        }

        $order = M('Order')->where(['pay_orderid' => $this->request['merchant_order_no']])->find();
        if (!$order) {
            $this->result_error('no order', $this->request);
            return;
        }

        // 如果状态不为0 则直接返回ok
        if ($order['pay_status'] != 0) {
            exit('success');
        }

        $pool = M('PoolPhones')->where(['id' => $order['pool_phone_id']])->find();
        if (!$pool) {
            $this->result_error('no pool info', $this->request);
            return;
        }

        M()->startTrans();
        M('Order')->where(['id' => $order['id']])->save([
            'pay_status' => 1,
            'pay_successdate' => $this->timestamp,
        ]);

        // #TODO 处理商户金额 ???

        // 2. 验证订单情况
                // 确定订单状态已处理 直接返回success
        // 3. 订单状态修改为已处理

        // 4. 保存号码商订单




        // 5. 回调处理

            // 第一次同步回调 然后处理订单状态


        // 6. or 异步回调

    }


    protected function check()
    {
        return createSign(C('RPC_PHONE_MKEY'), [
                'status'            => $this->result(['status']),
                'msg'               => $this->result(['msg']),
                'amount'            => $this->result(['amount']),
                'merchant_order_no' => $this->result(['merchant_order_no']),
                'no'                => $this->result(['no']),
                'payment_time'      => $this->result(['payment_time']),
                'pay_channel'       => $this->result(['pay_channel']),
                'pay_channel_name'  => $this->result(['pay_channel_name']),
            ]) === $this->request['sign'];


    }

}