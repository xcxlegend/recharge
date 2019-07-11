<?php
/**
 * Created by PhpStorm.
 * User: Legend.Xie
 * Date: 2019/6/20
 * Time: 21:12
 */

namespace Pay\Controller;
use \Think\Log;

class OrderController extends PayController
{
    //商家信息
//    protected $merchants;
    //通道信息
//    protected $channel;



    public function __construct()
    {
        parent::__construct();
    }


    /**
     * 创建订单
     * @param $parameter
     * @return array
     */
    public function orderadd( $order, $product, $channel )
    {

        $userid = $order['pay_memberid'];

        $pay_amount = $order['pay_amount'];

        //通道信息
//        $this->channel = $parameter['channel'];
//        $this->merchants = $this->channel['userid'];
        //用户信息
        $usermodel       = D('Member');
//        $this->merchants = $usermodel->get_Userinfo($userid);
//        if (!$this->merchants) {
//            $this->showmessage('商户信息未找到');
//            return;
//        }

        if (!$product) {
//            $product = $this->cache->getOrSet("Product:pay_code:" . $order['pay_code'], function () use (&$order) {
//                M('Product')->where(['code' => $order['pay_code']])->find();
//            });
            $product = D('Common/Product')->getByCode($order['pay_code']);
        }
        if (!$product) {
            $this->result_error("支付方式错误");
            return;
        }

        // 通道名称
//        $PayName = $parameter["code"];
        // 交易金额比例
        $moneyratio = 1;//$parameter["exchange"];
        //商户编号
        $return["memberid"] = $userid;// = $this->merchants['id'] + 10000;
        $m_Tikuanconfig     = M('Tikuanconfig');
        $tikuanconfig       = $m_Tikuanconfig->where(['userid' => $userid])->find();
        if (!$tikuanconfig || $tikuanconfig['tkzt'] != 1 || $tikuanconfig['systemxz'] != 1) {
            $tikuanconfig = $m_Tikuanconfig->where(['issystem' => 1])->find();
        }
        //费率
        $_userrate = M('Userrate')
            ->where(["userid" =>  $userid, "payapiid" => $product['id']])
            ->find();
        //银行通道费率
        if (!$channel) {
            $channel_id = $order['channel_id'];
           /* $channel = $this->cache->getOrSet("Channel:id:". $channel_id, function () use ($channel_id) {
                return M('Channel')->find($channel_id);
            }, true);*/
           $channel = D('Common/Channel')->getById( $channel_id );
        }
        $syschannel = $channel;//M('Channel')
//            ->where(['code' => 'Pool'])
//            ->find();

        //---------------------------子账号风控start------------------------------------
//        $channel_account_list        = M('channel_account')->where(['channel_id' => $syschannel['id'], 'status' => '1'])->select();
//        $account_ids                 = M('UserChannelAccount')->where(['userid' => $this->channel['userid'], 'status' => 1])->getField('account_ids');
//        if($account_ids){
//            $account_ids  = explode(',',  $account_ids );
//            foreach($channel_account_list as $k => $v){
//                //如果不在指定的子账号，将其删除
//                if(!in_array($v['id'], $account_ids )){
//                    unset($channel_account_list[$k]);
//                }
//            }
//        }

//        $l_ChannelAccountRiskcontrol = new \Pay\Logic\ChannelAccountRiskcontrolLogic($pay_amount);
        $channel_account_item        = [];
        $error_msg                   = '已下线';
//        foreach ($channel_account_list as $k => $v) {
//            if ($v['offline_status'] && $v['control_status']) {
//                //判断是自定义还是继承渠道的风控
//                $temp_info               = $v['is_defined'] ? $v : $syschannel;
//                $temp_info['account_id'] = $v['id']; //用于子账号风控类继承渠道风控机制时修改数据的id
//                //子账号风控
//                $l_ChannelAccountRiskcontrol->setConfigInfo($temp_info);
//                $error_msg = $l_ChannelAccountRiskcontrol->monitoringData();
//                if ($error_msg === true) {
//                    $channel_account_item[] = $v;
//                }
//            } else if ($v['control_status'] == 0) {
//                $channel_account_item[] = $v;
//            }
//        }
//        if (empty($channel_account_item)) {
//            $this->showmessage('账户:' . $error_msg);
//        }

        //-------------------------子账号风控end-----------------------------------------

        // 计算权重
//        if (count($channel_account_item) == 1) {
//            $channel_account = current($channel_account_item);
//        } else {
//            $channel_account = getWeight($channel_account_item);
//        }
//
//        $syschannel['mch_id']    = $channel_account['mch_id'];
//        $syschannel['signkey']   = $channel_account['signkey'];
//        $syschannel['appid']     = $channel_account['appid'];
//        $syschannel['appsecret'] = $channel_account['appsecret'];
//        $syschannel['account']   = $channel_account['title'];
//
//        // 定制费率
//        if ($channel_account['custom_rate']) {
//            $syschannel['defaultrate'] = $channel_account['defaultrate'];
//            $syschannel['fengding']    = $channel_account['fengding'];
//            $syschannel['fengding']    = $channel_account['fengding'];
//            $syschannel['rate']        = $channel_account['rate'];
//        }

        //平台通道
//        $platform = M('Product')
//            ->where(['id' => $this->channel['pid']])
//            ->find();


        //回调参数

        //用户优先通道
        if ($tikuanconfig['t1zt'] == 0) { //T+0费率
            $feilv    = $_userrate['t0feilv'] ? $_userrate['t0feilv'] : $syschannel['t0defaultrate']; // 交易费率
            $fengding = $_userrate['t0fengding'] ? $_userrate['t0fengding'] : $syschannel['t0fengding']; // 封顶手续费
        } else { //T+1费率
            $feilv    = $_userrate['feilv'] ? $_userrate['feilv'] : $syschannel['defaultrate']; // 交易费率
            $fengding = $_userrate['fengding'] ? $_userrate['fengding'] : $syschannel['fengding']; // 封顶手续费
        }
        $fengding = $fengding == 0 ? 9999999 : $fengding; //如果没有设置封顶手续费自动设置为一个足够大的数字

        //金额格式化

        if (!$pay_amount || !is_numeric($pay_amount) || $pay_amount <= 0) {
            $this->showmessage('金额错误');
        }
        $return["amount"] = floatval($pay_amount) * $moneyratio; // 交易金额
        $pay_sxfamount    = (($pay_amount * $feilv) > ($pay_amount * $fengding)) ? ($pay_amount * $fengding) :
            ($pay_amount * $feilv); // 手续费
        $pay_shijiamount = $pay_amount - $pay_sxfamount; // 实际到账金额
        if ($tikuanconfig['t1zt'] == 0) { //T+0费率
            $cost = bcmul($syschannel['t0rate'], $pay_amount, 2); //计算成本
        } else {
            $cost = bcmul($syschannel['rate'], $pay_amount, 2); //计算成本
        }

        $order['pay_poundage']        = $pay_sxfamount; // 手续费
        $order['pay_actualamount']    = $pay_shijiamount; // 到账金额
//        $order['pay_tongdao']         = $syschannel['code'];
        $order['pay_zh_tongdao']      = $syschannel['title'];
        $order['pay_tjurl']           = $_SERVER['HTTP_REFERER'];
        $order['cost']                = $cost;
        $order['cost_rate']           = 0;//$tikuanconfig['t1zt'] == 0 ? $syschannel['t0rate'] : $syschannel['rate'];
        $order['account_id']          = 0;//$channel_account['id'];
        $order['t']                   = $tikuanconfig['t1zt'] ?: 0;

        //添加订单
        return M("Order")->add($order);
    }

    /**
     * 回调处理订单
     * @param $TransID
     * @param $PayName
     * @param int $returntypepay_code
     */
    protected function EditMoney($pay_orderid)
    {

        $m_Order    = M("Order");
        $order_info = $m_Order->where(['pay_orderid' => $pay_orderid])->find(); //获取订单信息

        if (!$order_info) {
            $this->result_error('no order', $this->request);
            return;
        }

        $pool = M('PoolPhones')->where(['id' => $order_info['pool_phone_id']])->find();
        if (!$pool) {
            $this->result_error('no pool info', $this->request);
            return;
        }

        $userid     = $order_info["pay_memberid"]; // 商户ID

        //********************************************订单支付成功上游回调处理********************************************//
        if ($order_info["pay_status"] == 0) {

//            $product = M('Product')->where(['code' => $order_info['pay_code']])->find();
            $product = D('Common/Product')->getByCode( $order_info['pay_code'] );
            //开启事物
            M()->startTrans();
            //查询用户信息
            $m_Member    = M('Member');
            $member_info = $m_Member->where(['id' => $userid])->lock(true)->find();
            if (!$member_info) {
                $this->result_error("no member", $this->request);
                return false;
            }

            $provider = D('Common/PoolProvider')->getById( $pool['pid'] ); // M('PoolProvider')->where(['id' => $pool['pid']])->find();
            if (!$provider){
                log::write("pool provider not exist:" . json_encode($pool));
                $this->result_error("no pool provider", $this->request);
                return;
            }

            //更新订单状态 1 已成功未返回 2 已成功已返回
            $res = $m_Order->where(['id' => $order_info['id']])->save([
                'pay_status' => 1,
                'pay_successdate' => $this->timestamp,
            ]);
            if (!$res) {
                M()->rollback();
                return false;
            }
            //-----------------------------------------修改用户数据 商户余额、冻结余额start-----------------------------------
            //要给用户增加的实际金额（扣除投诉保证金）
            $actualAmount          = $order_info['pay_actualamount'];
            $complaintsDepositRule = $this->getComplaintsDepositRule($userid);
            if (isset($complaintsDepositRule['status']) && $complaintsDepositRule['status'] == 1) {
                if ($complaintsDepositRule['ratio'] > 100) {
                    $complaintsDepositRule['ratio'] = 100;
                }
                $depositAmount = round($complaintsDepositRule['ratio'] / 100 * $actualAmount, 4);
                $actualAmount -= $depositAmount;
            }

            //创建修改用户修改信息
            $member_data = [
                'last_paying_time'   => $this->timestamp,
                'unit_paying_number' => ['exp', 'unit_paying_number+1'],
                'unit_paying_amount' => ['exp', 'unit_paying_amount+' . $actualAmount],
                'paying_money'       => ['exp', 'paying_money+' . $actualAmount],
            ];

            //判断用结算方式
            switch ($order_info['t']) {
                case '0':
                    //t+0结算
                case '7':
                    //t+7 只限制提款和代付时间，每周一允许提款
                case '30':
                    //t+30 只限制提款和代付时间，每月第一天允许提款
                    $ymoney                 = $member_info['balance']; //改动前的金额
                    $gmoney                 = bcadd($member_info['balance'], $actualAmount, 4); //改动后的金额
                    $member_data['balance'] = ['exp', 'balance+' . $actualAmount]; //防止数据库并发脏读
                    break;
                case '1':
                    //t+1结算，记录冻结资金
                    $blockedlog_data = [
                        'userid'     => $userid,
                        'orderid'    => $order_info['pay_orderid'],
                        'amount'     => $actualAmount,
                        'thawtime'   => (strtotime('tomorrow') + rand(0, 7200)),
                        'pid'        => $product['id'],
                        'createtime' => $this->timestamp,
                        'status'     => 0,
                    ];
                    $blockedlog_result = M('Blockedlog')->add($blockedlog_data);
                    if (!$blockedlog_result) {
                        M()->rollback();
                        return false;
                    }
                    $ymoney                        = $member_info['blockedbalance']; //原冻结资金
                    $gmoney                        = bcadd($member_info['blockedbalance'], $actualAmount, 4); //改动后的冻结资金
                    $member_data['blockedbalance'] = ['exp', 'blockedbalance+' . $actualAmount]; //防止数据库并发脏读

                    break;
                default:
                    # code...
                    break;
            }

            $member_result = $m_Member->where(['id' => $userid])->save($member_data);
            if ($member_result != 1) {
                M()->rollback();
                return false;
            }

            // 商户充值金额变动
            $moneychange_data = [
                'userid'     => $userid,
                'ymoney'     => $ymoney ?: 0, //原金额或原冻结资金
                'money'      => $actualAmount,
                'gmoney'     => $gmoney ?: 0, //改动后的金额或冻结资金
                'datetime'   => date('Y-m-d H:i:s'),
                'tongdao'    => $product['id'],
                'transid'    => $order_info['pay_orderid'],
                'orderid'    => $order_info['out_trade_id'],
                'contentstr' => $order_info['out_trade_id'] . '订单充值,结算方式：t+' . ($order_info['t'] ?: 0),
                'lx'         => 1,
                't'          => $order_info['t'] ?: 0,
            ];

            $moneychange_result = $this->MoenyChange($moneychange_data); // 资金变动记录

            if ($moneychange_result == false) {
                M()->rollback();
                return false;
            }

            // 记录投诉保证金
            if (isset($depositAmount) && $depositAmount > 0) {
                $depositResult = M('ComplaintsDeposit')->add([
                    'user_id'       => $userid,
                    'pay_orderid'   => $pay_orderid,
                    'out_trade_id'  => $order_info['out_trade_id'],
                    'freeze_money'  => $depositAmount,
                    'unfreeze_time' => $this->timestamp + $complaintsDepositRule['freeze_time'],
                    'status'        => 0,
                    'create_at'     => $this->timestamp,
                    'update_at'     => $this->timestamp,
                ]);
                if ($depositResult == false) {
                    M()->rollback();
                    return false;
                }
            }

            // 通道ID
            $bianliticheng_data = [
                "userid"  => $userid, // 用户ID
                "transid" => $pay_orderid, // 订单号
                "money"   => $order_info["pay_amount"], // 金额
                "tongdao" => $product['id'],
            ];
            $this->bianliticheng($bianliticheng_data); // 提成处理

            M()->commit();

            //-----------------------------------------修改用户数据 商户余额、冻结余额end-----------------------------------

            //-----------------------------------------修改通道风控支付数据start----------------------------------------------
//            $m_Channel     = M('Channel');
//            $channel_where = ['id' => $order_info['channel_id']];
//            $channel_info  = D('Common/Channel')->getById( $order_info['channel_id'] );//   $m_Channel->where($channel_where)->find();
            //判断当天交易金额并修改支付状态
           /* $this->saveOfflineStatus(
                $m_Channel,
                $order_info['channel_id'],
                $order_info['pay_amount'],
                $channel_info
            );*/

            //-----------------------------------------修改通道风控支付数据end------------------------------------------------

            //-----------------------------------------修改子账号风控支付数据start--------------------------------------------
//            $m_ChannelAccount      = M('ChannelAccount');
//            $channel_account_where = ['id' => $order_info['account_id']];
//            $channel_account_info  = $m_ChannelAccount->where($channel_account_where)->find();
//            if ($channel_account_info['is_defined'] == 0) {
//                //继承自定义风控规则
//                $channel_info['paying_money'] = $channel_account_info['paying_money']; //当天已交易金额应该为子账号的交易金额
//                $channel_account_info         = $channel_info;
//            }
//            //判断当天交易金额并修改支付状态
//            $channel_account_res = $this->saveOfflineStatus(
//                $m_ChannelAccount,
//                $order_info['account_id'],
//                $order_info['pay_amount'],
//                $channel_account_info
//            );
//            if ($channel_account_info['unit_interval']) {
//                $m_ChannelAccount->where([
//                    'id' => $order_info['account_id'],
//                ])->save([
//                    'unit_paying_number' => ['exp', 'unit_paying_number+1'],
//                    'unit_paying_amount' => ['exp', 'unit_paying_amount+' . $order_info['pay_actualamount']],
//                ]);
//            }

            //-----------------------------------------修改子账号风控支付数据end----------------------------------------------


            // 转存poolphone订单信息
            $this->handlePoolOrderSuccess( $pool, $provider );


        } else {
            $member_info = M('Member')->where(['id' => $userid])->lock()->find();
        }

        //************************************************回调，支付跳转*******************************************//
         $this->sendOrderNotify($order_info, $member_info);
         return true;
    }


    protected function handlePoolOrderSuccess( $pool, $provider ) {

        $poolOrder = M('PoolRec')->where(['pool_id' => $pool['id']])->find();
        $config = json_decode(htmlspecialchars_decode($provider['config']), true);
        $rate = 0;
        if ($config['rate'] && $config['rate'][$pool['channel']]) {
            $rate = floatval($config['rate'][$pool['channel']]);
        }

        if ($rate > 1) {
            $rate = 0;
        }

        if (!$poolOrder){
            M()->startTrans();
            /*
             *  `pool_id` int(11) NOT NULL DEFAULT '0' COMMENT 'POOL序列ID order里对应字段索引',
  `pid` int(11) NOT NULL DEFAULT '0' COMMENT '号码商ID',
  `out_trade_id` varchar(50) NOT NULL DEFAULT '' COMMENT '号码商订单号',
  `order_id` varchar(50) NOT NULL DEFAULT '' COMMENT '平台订单号',
  `data` mediumtext COMMENT 'json格式的poolphones数据',
  `status` tinyint(1) NOT NULL COMMENT '0=未回调 1=回调完成',
  `time` int(11) NOT NULL DEFAULT '0' COMMENT '时间戳',
  `year` int(4) NOT NULL DEFAULT '0' COMMENT '年',
  `month` int(2) NOT NULL DEFAULT '0' COMMENT '月',
  `day` int(2) NOT NULL DEFAULT '0' COMMENT '日',

             */
            
            $pound    = $pool['money'] * $rate;
            $actmoney = $pool['money'] - $pound;

            $poolOrder = [
                'pool_id'           => $pool['id'],
                'pid'               => $pool['pid'],
                'out_trade_id'      => $pool['out_trade_id'],
                'order_id'          => $pool['order_id'],
                'data'              => json_encode($pool),
                'status'            => 0,
                'time'              => $this->timestamp,
                'year'              => date('Y', $this->timestamp),
                'month'             => date('m', $this->timestamp),
                'day'               => date('d', $this->timestamp),
                'money'             => $pool['money'],
                'channel'           => $pool['channel'],
                'actmoney'          => $actmoney,
                'pound'             => $pound,
                'phone'             => $pool['phone']
            ];
            // $poolOrder['actmoney']
            if (!M('PoolRec')->add($poolOrder)){
                M()->rollback();
                Log::write("add poolOrder err:" . json_encode($poolOrder));
                return;
            }
            $poolOrder['id'] = M('PoolRec')->getLastInsID();

            // 给号码商上增加金额和余额进去
           /* if (!M('PoolProvider')->where(['id' => $pool['pid']])->setInc("money", $pool['money'])){
                M()->rollback();
                Log::write("add PoolProvider money err:" . json_encode($poolOrder));
                return;
            }

            if (!M('PoolProvider')->where(['id' => $pool['pid']])->setDec("balance", $pool['money'])){
                M()->rollback();
                Log::write("dec PoolProvider balance err:" . json_encode($poolOrder));
                return;
            }*/

            if (!M('PoolProvider')->where(['id' => $pool['pid']])->save(
                [
                    'money' => [ 'exp', ' money + ' . $actmoney ],
                    'balance' => [ 'exp', ' balance - ' . $actmoney ]
                ]
            )){
                M()->rollback();
                Log::write("dec PoolProvider balance err:" . json_encode($poolOrder));
                return;
            }

            if (!D('PoolMoneychange')->addData($provider['id'], UID, $provider['balance'], -$actmoney, "支付订单: " . $poolOrder['id'] , $poolOrder['id'])){
                M()->rollback();
                Log::write("dec PoolProvider balance log err:" . json_encode($poolOrder));
                return;
            }

            if (!M('PoolPhones')->where(['id' => $pool['id']])->delete()){
                M()->rollback();
                Log::write("delete PoolPhones err:" . json_encode($poolOrder));
                return;
            }

            M()->commit();
        } else {
            // 如果存在也执行删除逻辑
            M('PoolPhones')->where(['id' => $pool['id']])->delete();
        }
        $this->sendPoolNotify($poolOrder, $pool);
    }

    protected function sendPoolNotify( $poolOrder ,  $pool) {

        $provider = M('PoolProvider')->where(['id' => $poolOrder['pid']])->find();
        if (!$provider){
            log::write("pool provider not exist:" . json_encode($poolOrder));
            return;
        }
        /**
         * id: 商户ID
        phone: 电话号码
        money: 金额 (单位分)
        out_trade_id: 商户系统的订单ID
        sign: 签名
         */
        /**
         * `pid` int(11) NOT NULL DEFAULT '0' COMMENT '号码商ID 使用member表',
        `phone` char(15) NOT NULL DEFAULT '' COMMENT '号码',
        `money` int(11) NOT NULL DEFAULT '0' COMMENT '充值金额 分',
        `notify_url` varchar(255) DEFAULT NULL COMMENT '商户回调地址',
        `time` int(11) NOT NULL DEFAULT '0' COMMENT '时间戳',
        `channel` tinyint(1) NOT NULL DEFAULT '0' COMMENT '运营商标识 1=移动 2=电信 3=联通',
        `out_trade_id` varchar(50) NOT NULL DEFAULT '' COMMENT '商户订单号 号码商订单号',
        `order_id` varchar(50) NOT NULL DEFAULT '' COMMENT '平台订单号',

         */
        if (!$pool){
            $pool = json_decode($poolOrder['data'], true);
        }
        $params = [
            'appkey'        => $provider['appkey'],
            'phone'         => $pool['phone'],
            'money'         => intval($pool['money'] * 100),
            'out_trade_id'  => $pool['out_trade_id'],
            'status'        => 1,
        ];

        $sign = $this->createSign($provider['appsecret'], $params);
        $params["sign"] = $sign;

        $contents = sendForm($pool['notify_url'], $params);

        Log::write(" pool notify: ". $poolOrder["id"] . " url: " . $pool["notify_url"] . http_build_query($params) . " resp: " . $contents);
        if (strstr(strtolower($contents), "ok") != false) {
            M('PoolRec')->where(['id' => $poolOrder['id']])->setField("status", 1);
            return true;
        }

        $notifystr = "";
        foreach ($params as $key => $val) {
            $notifystr = $notifystr . $key . "=" . $val . "&";
        }
        $notifystr = rtrim($notifystr, '&');
        $notifyType = 1;

        if (! $this->checkNotifyExist( $poolOrder['id'], $notifyType ) ) {
            $this->syncNotify( $notifyType, $poolOrder['id'], $pool['notify_url'],  $notifystr);
        }

        return true;
    }

    protected function checkNotifyExist( $orderid, $type ) {
        $notifys = M('OrderNotify')->where(['order_id' => $orderid])->select();
        foreach ($notifys as $key => $notify) {
            if ($notify['type'] == $type) {
                return true;
            }
        }
        return false;
    }


    protected function sendOrderNotify( $order, &$member_info )
    {
        $params = [ // 返回字段
            "memberid" => $order["pay_memberid"], // 商户ID
            "orderid" => $order['out_trade_id'], // 订单号
            'transaction_id' => $order["pay_orderid"], //支付流水号
            "amount" => intval($order["pay_amount"] * 100), // 交易金额
            "datetime" => date("YmdHis", $order['pay_successdate']), // 交易时间
            "status" => 1, // 交易状态
        ];

        $sign = createSign($member_info['apikey'], $params);
        $params["sign"] = $sign;
        $params["attach"] = $order["attach"];

        $contents = sendForm($order['pay_notifyurl'], $params);

        \Think\Log::write("order notify: " . $order["id"] . " url: " . $order["pay_notifyurl"] . '?' . http_build_query($params) . " resp: " . $contents . '|' .json_encode($member_info));
        if (strstr(strtolower($contents), "ok") != false) {
            //更新交易状态
            $order_where = [
                'id' => $order['id']
            ];
            $order_result = M('Order')->where($order_where)->setField("pay_status", 2);
            return true;
        }

        $notifystr = "";
        foreach ($params as $key => $val) {
            $notifystr = $notifystr . $key . "=" . $val . "&";
        }
        $notifystr = rtrim($notifystr, '&');


        $notifyType = 0;

        if (! $this->checkNotifyExist( $order['id'], $notifyType ) ) {
            $this->syncNotify( $notifyType, $order['id'], $order['pay_notifyurl'],  $notifystr);
        }

        return true;
    }

    protected function syncNotify( $type,  $id, $url, $notifystr ) {
        $notifyData = [
            'order_id'      => $id,
            'notify_url'    => $url,
            'body'          => $notifystr,
            'times'         => 0,
            'last'          => $this->timestamp + 15,
            "type"          => $type, // 订单回调类型
            'status'        => 0,
        ];
        return M('OrderNotify')->add($notifyData);
    }


    //修改渠道跟账号风控状态
    protected function saveOfflineStatus($model, $id, $pay_amount, $info)
    {
        if ($info['offline_status'] && $info['control_status'] && $info['all_money'] > 0) {
            //通道是否开启风控和支付状态为上线
            $data['paying_money']     = bcadd($info['paying_money'], $pay_amount, 4);
            $data['last_paying_time'] = time();

            if ($data['paying_money'] >= $info['all_money']) {
                $data['offline_status'] = 0;
            }
            return $model->where(['id' => $id])->save($data);
        }
        return true;
    }

    /**
     *  验证签名
     * @return bool
     */
    protected function verify()
    {
        //POST参数
        $requestarray = array(
            'pay_memberid'    => I('request.pay_memberid', 0, 'intval'),
            'pay_orderid'     => I('request.pay_orderid', ''),
            'pay_amount'      => I('request.pay_amount', ''),
            'pay_applydate'   => I('request.pay_applydate', ''),
            'pay_bankcode'    => I('request.pay_bankcode', ''),
            'pay_notifyurl'   => I('request.pay_notifyurl', ''),
            'pay_callbackurl' => I('request.pay_callbackurl', ''),
        );
        $md5key        = $this->merchants['apikey'];
        $md5keysignstr = $this->createSign($md5key, $requestarray);
        $pay_md5sign   = I('request.pay_md5sign');
        if ($pay_md5sign == $md5keysignstr) {
            return true;
        } else {
            return false;
        }
    }

    public function setHtml($tjurl, $arraystr)
    {
        $str = '<form id="Form1" name="Form1" method="post" action="' . $tjurl . '">';
        foreach ($arraystr as $key => $val) {
            $str .= '<input type="hidden" name="' . $key . '" value="' . $val . '">';
        }
        $str .= '</form>';
        $str .= '<script>';
        $str .= 'document.Form1.submit();';
        $str .= '</script>';
        exit($str);
    }

    public function jiankong($orderid)
    {
        ignore_user_abort(true);
        set_time_limit(3600);
        $Order    = M("Order");
        $interval = 10;
        do {
            if ($orderid) {
                $_where['pay_status']  = 1;
                $_where['num']         = array('lt', 3);
                $_where['pay_orderid'] = $orderid;
                $find                  = $Order->where($_where)->find();
            } else {
                $find = $Order->where("pay_status = 1 and num < 3")->order("id desc")->find();
            }
            if ($find) {
                $this->EditMoney($find["pay_orderid"], $find["pay_tongdao"], 0);
                $Order->where(["id" => $find["id"]])->save(['num' => ['exp', 'num+1']]);
            }

            sleep($interval);
        } while (true);
    }

    /**
     * 资金变动记录
     * @param $arrayField
     * @return bool
     */
    protected function MoenyChange($arrayField)
    {
        // 资金变动
        $Moneychange = M("Moneychange");
        foreach ($arrayField as $key => $val) {
            $data[$key] = $val;
        }
        $result = $Moneychange->add($data);
        return $result ? true : false;
    }

    /**
     * 佣金处理
     * @param $arrayStr
     * @param int $num
     * @param int $tcjb
     * @return bool
     */
    private function bianliticheng($arrayStr, $num = 3, $tcjb = 1)
    {
        if ($num <= 0) {
            return false;
        }
        $userid    = $arrayStr["userid"];
        $tongdaoid = $arrayStr["tongdao"];
        $trans_id  = $arrayStr["transid"];
        $feilvfind = $this->huoqufeilv($userid, $tongdaoid, $trans_id);

        if ($feilvfind["status"] == "error") {
            return false;
        } else {
            //商户费率（下级）
            $x_feilv    = $feilvfind["feilv"];
            $x_fengding = $feilvfind["fengding"];

            //代理商(上级)
            $parentid = M("Member")->where(["id" => $userid])->getField("parentid");
            if ($parentid <= 1) {
                return false;
            }
            $parentRate = $this->huoqufeilv($parentid, $tongdaoid, $trans_id);

            if ($parentRate["status"] == "error") {
                return false;
            } else {

                //代理商(上级）费率
                $s_feilv    = $parentRate["feilv"];
                $s_fengding = $parentRate["fengding"];

                //费率差
                $ratediff = (($x_feilv * 1000) - ($s_feilv * 1000)) / 1000;
                if ($ratediff <= 0) {
                    return false;
                } else {
                    $parent = M('Member')->where(['id' => $parentid])->field('id,balance')->find();
                    if (empty($parent)) {
                        return false;
                    }
                    $brokerage = $arrayStr['money'] * $ratediff;
                    //代理佣金
                    $rows = [
                        'balance' => array('exp', "balance+{$brokerage}"),
                    ];
                    M('Member')->where(['id' => $parentid])->save($rows);

                    //代理商资金变动记录
                    $arrayField = array(
                        "userid"   => $parentid,
                        "ymoney"   => $parent['balance'],
                        "money"    => $arrayStr["money"] * $ratediff,
                        "gmoney"   => $parent['balance'] + $brokerage,
                        "datetime" => date("Y-m-d H:i:s"),
                        "tongdao"  => $tongdaoid,
                        "transid"  => $arrayStr["transid"],
                        "orderid"  => "tx" . date("YmdHis"),
                        "tcuserid" => $userid,
                        "tcdengji" => $tcjb,
                        "lx"       => 9,
                    );
                    $this->MoenyChange($arrayField); // 资金变动记录
                    $num                = $num - 1;
                    $tcjb               = $tcjb + 1;
                    $arrayStr["userid"] = $parentid;
                    $this->bianliticheng($arrayStr, $num, $tcjb);
                }
            }
        }
    }

    private function huoqufeilv($userid, $payapiid, $trans_id)
    {
        $return = array();
        $order  = M('Order')->where(['pay_orderid' => $trans_id])->find();
        //用户费率
        $userrate = M("Userrate")->where(["userid" => $userid, "payapiid" => $payapiid])->find();
        //支付通道费率
        $syschannel = M('Channel')->where(['id' => $payapiid])->find();
        if ($order['t'] == 0) { //T+0费率
            $feilv    = $userrate['t0feilv'] ? $userrate['t0feilv'] : $syschannel['t0defaultrate']; // 交易费率
            $fengding = $userrate['t0fengding'] ? $userrate['t0fengding'] : $syschannel['t0fengding']; // 封顶手续费
        } else { //T+1费率
            $feilv    = $userrate['feilv'] ? $userrate['feilv'] : $syschannel['defaultrate']; // 交易费率
            $fengding = $userrate['fengding'] ? $userrate['fengding'] : $syschannel['fengding']; // 封顶手续费
        }
        $return["status"]   = "ok";
        $return["feilv"]    = $feilv;
        $return["fengding"] = $fengding;
        return $return;
    }

    /**
     * 创建签名
     * @param $Md5key
     * @param $list
     * @return string
     */
    protected function createSign($Md5key, $list)
    {
        ksort($list);
        $md5str = "";
        foreach ($list as $key => $val) {
            // if (!empty($val)) {
                $md5str = $md5str . $key . "=" . $val . "&";
            // }
        }
        $sign = md5($md5str . "key=" . $Md5key);
        return $sign;
    }

    public function bufa()
    {
        header('Content-type:text/html;charset=utf-8');
        $TransID    = I("get.TransID");
        $PayName    = I("get.tongdao");
        $m          = M("Order");
        $pay_status = $m->where(array("pay_orderid" => $TransID))->getField("pay_status");
        if (intval($pay_status) == 1) {
            echo ("订单号：" . $TransID . "|" . $PayName . "已补发服务器点对点通知，请稍后刷新查看结果！<a href='javascript:window.close();'>关闭</a>");
            $this->EditMoney($TransID);
        } else {
            echo "补发失败";
        }
    }

    public function poolbufa() {
        header('Content-type:text/html;charset=utf-8');
        $id    = I("get.id");
        $pool          = M("PoolRec")->find($id);
        if (!$pool) {
            exit('补发失败');
        }
        if ($pool['status'] == 0) {
            echo ("订单号：" . $pool['order_id']  . "已补发服务器点对点通知，请稍后刷新查看结果！<a href='javascript:window.close();'>关闭</a>");
            $this->sendPoolNotify($pool);
        } else {
            echo "补发失败";
        }
    }


    /**
     * 扫码订单状态检查
     *
     */
    public function checkstatus()
    {
        $orderid = I("post.orderid");
        $Order   = M("Order");
        $order   = $Order->where(array('pay_orderid' => $orderid))->find();
        if ($order['pay_status'] != 0) {
            echo json_encode(array('status' => 'ok', 'callback' => $this->_site . "Pay_" . $order['pay_tongdao'] . "_callbackurl.html?orderid="
                . $orderid . "&pay_memberid=" . $order['pay_memberid'] . '&bankcode=' . $order['pay_bankcode']));
            exit();
        } else {
            exit("no-$orderid");
        }
    }

    /**
     * 错误返回
     * @param string $msg
     * @param array $fields
     */
    protected function showmessage($msg = '', $fields = array())
    {
        header('Content-Type:application/json; charset=utf-8');
        $data = array('status' => 'error', 'msg' => $msg, 'data' => $fields);
        echo json_encode($data, 320);
        exit;
    }


    protected function showOk($msg = '', $fields = array()){
        header('Content-Type:application/json; charset=utf-8');
        $data = array('status' => 'success', 'msg' => $msg, 'data' => $fields);
        echo json_encode($data, 320);
        exit;
    }

    /**
     * 来路域名检查
     * @param $pay_memberid
     */
    protected function domaincheck($pay_memberid)
    {
        $referer      = $_SERVER["HTTP_REFERER"]; // 获取完整的来路URL
        $domain       = $_SERVER['HTTP_HOST'];
        $pay_memberid = intval($pay_memberid) - 10000;
        $User         = M("User");
        $num          = $User->where(["id" => $pay_memberid])->count();
        if ($num <= 0) {
            $this->showmessage("商户编号不存在");
        } else {
            $websiteid     = $User->where(["id" => $pay_memberid])->getField("websiteid");
            $Websiteconfig = M("Websiteconfig");
            $websitedomain = $Websiteconfig->where(["websiteid" => $websiteid])->getField("domain");

            if ($websitedomain != $domain) {
                $Userverifyinfo = M("Userverifyinfo");
                $domains        = $Userverifyinfo->where(["userid" => $pay_memberid])->getField("domain");
                if (!$domains) {
                    $this->showmessage("域名错误 ");
                } else {
                    $arraydomain = explode("|", $domains);
                    $checktrue   = true;
                    foreach ($arraydomain as $key => $val) {
                        if ($val == $domain) {
                            $checktrue = false;
                            break;
                        }
                    }
                    if ($checktrue) {
                        $this->showmessage("域名错误 ");
                    }
                }
            }
        }
    }

    protected function getParameter($title, $channel, $className, $exchange = 1)
    {
        if (substr_count($className, 'Controller')) {
            $length = strlen($className) - 25;
            $code   = substr($className, 15, $length);
        }
        $parameter = array(
            'code'         => $code, // 通道名称
            'title'        => $title, //通道名称
            'exchange'     => $exchange, // 金额比例
            'gateway'      => '',
            'orderid'      => '',
            'out_trade_id' => I('request.pay_orderid', ''), //外部订单号
            'channel'      => $channel,
            'body'         => I('request.pay_productname', ''),
        );
        $return = $this->orderadd($parameter);
        //如果生成错误，自动跳转错误页面
        $return["status"] == "error" && $this->showmessage($return["errorcontent"]);

        //跳转页面，优先取数据库中的跳转页面
        $return["notifyurl"] || $return["notifyurl"]     = $this->_site . 'Pay_' . $code . '_notifyurl.html';
        $return['callbackurl'] || $return['callbackurl'] = $this->_site . 'Pay_' . $code . '_callbackurl.html';
        return $return;
    }

    protected function showQRcode($url, $return, $view = 'weixin')
    {
        import("Vendor.phpqrcode.phpqrcode", '', ".php");
        $QR = "Uploads/codepay/" . $return["orderid"] . ".png"; //已经生成的原始二维码图
        \QRcode::png($url, $QR, "L", 20);
        $this->assign("imgurl", $this->_site . $QR);
        $this->assign('params', $return);
        $this->assign('orderid', $return['orderid']);
        $this->assign('money', $return['amount']);
        $this->display("WeiXin/" . $view);
    }

    /**
     * 获取投诉保证金金额
     * @param $userid
     * @return array
     */
    private function getComplaintsDepositRule($userid)
    {
        $complaintsDepositRule = M('ComplaintsDepositRule')->where(['user_id' => $userid])->find();
        if (!$complaintsDepositRule || $complaintsDepositRule['status'] != 1) {
            $complaintsDepositRule = M('ComplaintsDepositRule')->where(['is_system' => 1])->find();
        }
        return $complaintsDepositRule ? $complaintsDepositRule : [];
    }
}
