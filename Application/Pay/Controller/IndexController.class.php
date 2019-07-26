<?php

namespace Pay\Controller;

use Common\Lib\ChannelOrder;
use Think\Exception;
use \Think\Log;
use Common\Lib\ChannelManagerLib;
use Common\Lib\PoolDevLib;
use Common\Lib\PaytypeMgrLib;
use Common\Model\RedisCacheModel;

/**
 * Class IndexController
 * @package Pay\Controller
 * @prief 充值接口
 */
class IndexController extends OrderController
{

    protected $member;
    protected $product;
    protected $channel;
    protected $userProduct;
    protected $pool;
    protected static $RPC_PHONE_URL;

    const RPC_ORDER_API = "/v1/order/pay";

    public function __construct()
    {
        parent::__construct();
        self::$RPC_PHONE_URL = C('RPC_POOL_PHONE');
    }


    public function index()
    {

        if (!$this->check()) {
            return;
        }

        list($msec, $sec) = explode(' ', microtime());
        $pay_orderid = 'MP' . date('YmdHis', $sec) . intval($msec * 10000);

        // channel不在这里获取
        if (!$this->checkChannel()) {
            return;
        }

        $ptmgr = new PaytypeMgrLib(new PoolDevLib);
        
        try {
            // 获取channel 和 pool
            $ptmgr->query($this->userProduct, $this->request);
        } catch (Exception $e) {
            Log::write( "58: " . $e->getMessage());
            $ptmgr->reset();
            $this->result_error($e->getMessage());
            return
        }

        $this->channel = $ptmgr->channel;
        // function return $channel -> notifyurl 
        $notify_url = $this->_site . 'Pay_Notify_Index_Method_' . $this->channel['code'];

        $manager = new ChannelManagerLib($ptmgr);
        try {
            $c_order = $manager->order($this->request, $notify_url, $pay_orderid);

            if ($c_order instanceof ChannelOrder) {

                $order = [
                    'pay_memberid' => $this->member['id'],
                    'pay_orderid' => $pay_orderid,
                    'pay_amount' => round($this->request['pay_amount'] / 100, 2),
                    'pay_applydate' => time(),
                    'pay_code' => $this->request['pay_bankcode'],
                    'pay_notifyurl' => $this->request['pay_notifyurl'],
                    'pay_callbackurl' => $this->request['pay_returnurl'] ?: '',
                    'pay_status' => 0,
                    'out_trade_id' => $this->request['pay_orderid'],
                    'memberid' => $c_order->poolPid,
                    'attach' => $this->request['pay_attach'],
                    'pay_productname' => $this->request['pay_productname'],
                    'pay_url' => $c_order->wapUrl ?: $c_order->qrUrl ?: '',
                    'pool_phone_id' => $c_order->poolId,
                    'channel_id' => $this->channel['id'],
                    'trade_id' => $c_order->tradeID
                ];

                if (!$this->orderadd($order, $this->product, $this->channel)) {
                    throw new Exception("订单保存失败");
                }

                $resp = [
                    'orderId' => $pay_orderid,
                    'orderNo' => $this->request['pay_orderid'],
                    'url'     => $c_order->wapUrl,
                    'qr_url'  => $c_order->qrUrl,
                ];
                $this->result_success($resp, "创建订单成功");
                return true;
            }
            throw new Exception("支付Lib返回信息错误");
        } catch (Exception $e) {
            Log::write($e->getMessage());
            $ptmgr->reset();
            //            $c_order->reset();
            $this->result_error($e->getMessage());
            //            $this->result_error('订单生成失败');
            return;
        }
    }

    /**
     * 充值接口
     * 1. 调用接口获取充值的手机和金额
     * 2. 返回接口对外
     */
    public function index2()
    {
        /*
        pay_memberid

        pay_orderid

        pay_applydate

        pay_bankcode

        pay_notifyurl

        pay_amount

        pay_md5sign

        pay_attach

         */

        // 1. 检查参数, 签名等

        // 2. 调用RPC 获取充值信息

        // 3. 存储订单信息

        // 4. 返回接口内容

        if (!$this->check()) {
            return;
        }

        list($msec, $sec) = explode(' ', microtime());
        $pay_orderid = 'MP' . date('YmdHis', $sec) . intval($msec * 1000);

        $poolOrder = $this->getPoolOrder($pay_orderid);
        /*
 *  {
            "pool_id": '',
            "order":  {
                no
                wap_url
                code_url
               }
        }
 */
        if (!$poolOrder) {
            return;
        }

        $order = [
            'pay_memberid' => $this->member['id'],
            'pay_orderid'  => $pay_orderid,
            'pay_amount'   => round($this->request['pay_amount'] / 100, 2),
            'pay_applydate' => time(),
            'pay_code' => $this->request['pay_bankcode'],
            'pay_notifyurl' => $this->request['pay_notifyurl'],
            'pay_callbackurl' => $this->request['pay_returnurl'] ?: '',
            'pay_status' => 0,
            'out_trade_id' => $this->request['pay_orderid'],
            'memberid' => $poolOrder['pool_pid'],
            'attach' => $this->request['pay_attach'],
            'pay_productname' => $this->request['pay_productname'],
            'pay_url' => $poolOrder['order']['wap_url'] ?: $poolOrder['order']['code_url'] ?: '',
            'pool_phone_id' => $poolOrder['pool_id'],
            'channel_id' => $this->channel['id'],
            'trade_id' => $poolOrder['order']['no'],
        ];

        //        $orderModel = M('Order');
        //        $r = $orderModel->add($order);
        if (!$this->orderadd($order)) {
            $this->result_error("订单保存失败");
            return;
        }


        // return fields

        /*
         *  "orderId":"xxxxx",
        "orderNo":"ddddd",
        "url":"weixin://wxpay/bizpayurl?pr=xxxxx",
        "createTime":"2019-01-11 11:09:03"
         */
        $resp = [
            'orderId' => $pay_orderid,
            'orderNo' => $this->request['pay_orderid'],
            'url'     => $poolOrder['order']['wap_url'],
            'qr_url'  => $poolOrder['order']['code_url']
        ];
        $this->result_success($resp, "创建订单成功");
    }


    // 参数验证 签名验证
    protected function check()
    {

        $request = $this->request;

        if (
            !$request['pay_memberid']
            || !$request['pay_orderid']
            || !$request['pay_applydate']
            || !$request['pay_bankcode']
            || !$request['pay_notifyurl']
            || !$request['pay_amount']
            || !$request['pay_md5sign']
        ) {
            $this->result_error("参数不足");
            return;
        }

        $userid = intval($request["pay_memberid"] - 10000); // 商户ID

        //        $cache = RedisCacheModel::instance();

        //        $member = $this->cache->getOrSet("member:".$userid, function () use ($userid){
        //            return M('Member')->where(['id' => $userid])->find();
        //        }, true);

        $member = D('Common/Member')->getById($userid);

        //        $member = M('Member')->where(['id' => $userid])->find();
        if (!$member) {
            $this->result_error('商户不存在');
            return;
        }

        if (!$member['status'] || !$member['open_charge']) {
            $this->result_error("商户状态不可充值");
            return;
        }

        $this->member = $member;

        //        $this->product = $this->cache->getOrSet("product:".$request['pay_bankcode'], function () use (&$request) {
        //            return M('Product')->where(['code' => $request['pay_bankcode']])->find();
        //        }, true);
        $this->product = D('Common/Product')->getByCode($request['pay_bankcode']);

        if (!$this->product) {
            $this->result_error('支付方式错误');
            return;
        }

        $sign = $request['pay_md5sign'];
        //        unset($request['pay_md5sign']);
        //        unset($request['pay_attach']);
        //        unset($request['pay_productname']);

        $checkParams = [
            "pay_memberid"      =>  $request["pay_memberid"],
            "pay_orderid"       =>  $request["pay_orderid"],
            "pay_amount"        =>  $request["pay_amount"],
            "pay_applydate"     =>  $request["pay_applydate"],
            "pay_bankcode"      =>  $request["pay_bankcode"],
            "pay_notifyurl"     =>  $request["pay_notifyurl"],
            "pay_callbackurl"   =>  $request["pay_callbackurl"],
        ];


        if (!($sign == createSign($member['apikey'], $checkParams))) {
            $this->result_error("签名错误");
            return false;
        }

        if (!$this->judgeRepeatOrder()) {
            $this->result_error('重复订单！请尝试重新提交订单');
            return false;
        }

        return true;
    }


    public function judgeRepeatOrder()
    {
        // 默认不允许
        $is_repeat_order = false; // M('Websiteconfig')->getField('is_repeat_order');
        if (!$is_repeat_order) {
            return !$this->cache->Client()->sIsMember("orders:member_pay_orderid:" . $this->member['id'], $this->request['pay_orderid']);
        }
        return true;
    }

    // 获取网厅订单
    protected function getPoolOrder($pay_orderid)
    {

        $url = self::$RPC_PHONE_URL . self::RPC_ORDER_API;
        $param = [
            'money' => $this->request['pay_amount'],
            'code'  => $this->request['pay_bankcode'],
            'pay_orderid' => $pay_orderid,
            'pay_returnurl' => $this->request['pay_returnurl'],
            'pay_notifyurl' => $this->_site . 'Pay_Notify'
        ];
        $param['sign'] = createSign(C('RPC_POOL_PHONE_SECRET'), $param);

        $data = sendForm($url, $param);
        $data = json_decode($data, true);

        if (!$data || $data['status'] != 0) {
            $this->result_error("RPC " . $data['info'] ?: '订单请求失败');
            return false;
        }

        /*
         * // 外部订单号
         * No string `json:"no"`
        // wap地址
        WapUrl string `json:"wap_url"`
        // 扫码地址
        CodeUrl string `json:"code_url"`
         */

        return $data['data'];
    }

    protected function checkChannel()
    {
        $ProductUser = D('Common/ProductUser')->getByMix($this->product['id'], $this->member['id']);
        if (!$ProductUser) {
            $this->result_error("商户未设置支付渠道", true);
            return false;
        }
        $this->userProduct = $ProductUser;
/*         $channel_id =  $ProductUser['channel'];
        $this->channel = D('Common/Channel')->getById($channel_id);

        if (!$this->channel) {
            $this->result_error("商户未设置支付渠道", true);
            return false;
        } */
        return true;
    }


    public function Query()
    {

        $params = [
            'out_trade_id' => $this->request['out_trade_id'],
            'pay_memberid' => $this->request['pay_memberid']
        ];

        $member = D('Common/Member')->getById($this->request['pay_memberid'] - 10000);

        if (!$member) {
            $this->result_error("商户不存在");
            return;
        }

        if (createSign($member['apikey'], $params) !==  $this->request['sign']) {
            $this->result_error("签名错误");
            return;
        }

        $params['pay_memberid'] = $member['id'];

        $order = D('Order')->where($params)->find();
        if (!$order || $order['pay_status'] == "0") {
            $this->result_error("订单不存在或未支付");
            return;
        }

        $this->result_success([
            "time"          => date('Y-m-d H:i:s', $order['pay_successdate']),
            "amount"        => intval($order['pay_amount'] * 100),
            "out_trade_id"  => $order['out_trade_id'],
            "order_id"      => $order['pay_orderid']
        ]);
    }

    public function QueryBalance()
    {
        if (!$this->request['memberid']) {
            $this->result_error("需要商户ID");
            return;
        }
        if (!$this->request['time']) {
            $this->result_error("参数不足");
            return;
        }
        $member = M('Member')->where(['id' => $this->request['memberid'] - 10000])->find();
        if (!$member) {
            $this->result_error("商户不存在");
            return;
        }
        $params = [
            'memberid' => $this->request['memberid'],
            'time'         => $this->request['time'],
        ];
        if (createSign($member['apikey'], $params) !==  $this->request['sign']) {
            $this->result_error("签名错误");
            return;
        }
        $params['balance'] = $member['balance'];
        $this->result_success($params);
    }
}
