<?php
/**
 * Created by PhpStorm.
 * User: Legend.Xie
 * Date: 2019/6/20
 * Time: 18:15
 */

namespace Pay\Controller;
use Common\Lib\ChannelManagerLib;
use Think\Exception;
use \Think\Log;


class NotifyController extends OrderController
{

    public function __construct()
    {
        parent::__construct();
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

        Log::write("notify request:" . http_build_query($this->request));
        try {
            $pay_orderid = ChannelManagerLib::notify($this->request['Method'], $this->request);
            if (!$pay_orderid) {
//              exit('err');
                ChannelManagerLib::notifyErr($this->request['Method']);
            }

            $trans_id = $this->request['no'];

            $this->EditMoney($pay_orderid, $trans_id);
//            exit('success');
            exit(ChannelManagerLib::notifyOK($this->request['Method']));
        } catch (Exception $e){
            Log::write( json_encode(I('request.')) . " err: " . $e->getMessage() );
//            $this->result_error( $e->getMessage() );
            exit(ChannelManagerLib::notifyErr($this->request['Method']));
            return;
        }

//        if ($this->request['status'] != "Success") {
//            $this->result_error( "status" );
//            return;
//        }
//
//        if ( !$this->check() ){
//            $this->result_error("sign", $this->request);
//            return;
//        }

//        $order = M('Order')->where(['pay_orderid' => $this->request['merchant_order_no']])->find();
//        if (!$order) {
//            $this->result_error('no order', $this->request);
//            return;
//        }
//
//        // 如果状态不为0 则直接返回ok
//        if ($order['pay_status'] != 0) {
//            exit('success');
//        }
//
//        $pool = M('PoolPhones')->where(['id' => $order['pool_phone_id']])->find();
//        if (!$pool) {
//            $this->result_error('no pool info', $this->request);
//            return;
//        }

//        Log::write(" Pay_Notify notify: " . http_build_query($this->request));
//        $this->EditMoney($this->request['merchant_order_no']);
//        exit('success');
//        M()->startTrans();
//        M('Order')->where(['id' => $order['id']])->save([
//            'pay_status' => 1,
//            'pay_successdate' => $this->timestamp,
//        ]);



    }


    protected function check()
    {
        return createSign(C('RPC_PHONE_MKEY'), [
                'status'            => $this->request['status'],
                'msg'               => $this->request['msg'],
                'amount'            => $this->request['amount'],
                'merchant_order_no' => $this->request['merchant_order_no'],
                'no'                => $this->request['no'],
                'payment_time'      => $this->request['payment_time'],
                'pay_channel'       => $this->request['pay_channel'],
                'pay_channel_name'  => $this->request['pay_channel_name'],
            ]) === $this->request['sign'];


    }

}