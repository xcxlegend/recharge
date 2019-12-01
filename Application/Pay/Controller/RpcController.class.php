<?php
namespace Pay\Controller;

use Common\Lib\TranserManager;
use Think\Exception;
use Common\Lib\ChannelManagerLib;
use \Think\Log;

/**
 * 内部RPC请求
 * Class RpcController
 * @package Pay\Controller
 */

class RpcController extends PayController
{

    // 认证
    public function __construct(){
        parent::__construct();
        //

    }


    public function index() {
        $body = file_get_contents('php://input');
        $this->request = json_decode($body, true);
        $call = $this->request['call'];
        if ($call){
            return call_user_func_array([$this, $call], $this->request);
        }
        $this->result_error('no call', true);
    }

    public function getPayUrl() {

        $params = I("post.");
        $channel = D('Common/Channel')->getById(7);//测试通道
        $notify_url = $this->_site . 'Pay_Notify_Index_Method_' . $channel['code'];
        $manager = new ChannelManagerLib( $channel );

        //获取支付链接
        $randPay = M('ChannelPay')->where(['id'=>$params['channel']])->find();
        $randPay = json_decode($randPay['config'],true);
        $proSum = array_sum($randPay); 
        //概率数组
        foreach ($randPay as $key => $proCur) { 
            $randNum = mt_rand(1, $proSum);
            if ($randNum <= $proCur) { 
                $params['pay_code'] = $key; 
                break; 
            } else { 
                $proSum -= $proCur;   
            } 
        }

        
        $result = $manager->order($params, $notify_url, $params['order_id']);
        if (!$result['pay_no'] || !$result['pay_url']) {
            $manager->reset();
            //号码商通知
            $signArray = [
                'appkey'        => $params['appkey'],
                'phone'         => $params['phone'],
                'money'         => $params['money'],
                'out_trade_id'  => $params['out_trade_id'],
                'status'        => -2,
            ];
    
            $sign =  createSign($params['appsecret'], $signArray);
            $signArray["sign"] = $sign;
            $signArray['msg'] = $result['msg'];
    
            $contents = sendForm($params['notify_url'], $signArray);
    
            Log::write("payurl error notify: ". $params["order_id"] . " url: " . $params["notify_url"] .'?'. http_build_query($signArray) . " resp: " . json_encode($result));

            M('PoolPhones')->where(['id' => $params['id']])->delete();
            exit(ChannelManagerLib::notifyOK($channel['code']));
        }else{
            $data['pay_no'] =$result['pay_no'];
            $data['pay_url'] = $result['pay_url'];
            $data['pay_code'] = $params['pay_code'];
            if (!M("PoolPhones")->where(["id" => $params["id"]])->save($data)){
                Log::write("payurl save error:" . json_encode($data));
            }
            exit;
        }
    }

    public function transPhone(){
        $id = I("post.id");
        if (!$id) {
            return $this->result_error('no param id');
        }
        $pool = M('PoolPhones')->find($id);
        if (!$pool) {
            return $this->result_error('no pool phones');
        }
        if ($pool['status'] != 2) {
            return $this->result_error('pool status need 2');
        }

        $data = json_decode($pool['data'], true);
        // $success = false;

        if ($data['transe']) {
            $channel = M('Channel')->find($data['transe']);
            try{
                $notify_url = $this->_site . 'Pay_Trans_Notify_Method_' . $channel['code'];
                $manger = new TranserManager($channel);
                $result = $manger->order($pool, $notify_url);
                if ($result) {
                    // save and delete
                    M()->startTrans();
                    $order = [
                        'pool_id'       => $pool['id'],
                        'pid'           => $pool['pid'],
                        'phone'         => $pool['phone'],
                        'money'         => $pool['money'],
                        'notify_url'    => $pool['notify_url'],
                        'time'          => $pool['time'],
                        'channel'       => $pool['channel'],
                        'out_trade_id'  => $pool['out_trade_id'],
                        'order_id'      => $pool['order_id'],
                        'data'          => $pool['data'],
                        'phone_code'    => $pool['phone_code'],
                        'status'        => 0, //  其实不需要状态
                        'cid'           => $channel['id'],
                        'order_time'    => $this->timestamp,
                        'pay_trade_id'  => $result['pay_trade_id'] ?: '',
                    ];
                    if (!M('PoolOrder')->add($order)) {
                        M()->rollback();
                        throw new Exception("save order error");
                    }
                    M()->commit();
                    return $this->result_success($order);
                }
            }catch(Exception $e) {
               // $this->result_error($e->getMessage());
            }
        }

        parse_str($data['query_timeout'], $urlarr);
        $contents = sendForm($pool['notify_url'], $urlarr);
        Log::write(" pool order faild: " . $pool["notify_url"].'?'. $data['query_timeout']. " resp: " . $contents);
        // delete
        M('PoolPhones')->delete($pool['id']);
        $this->result_error('deleted');

        // if ($success) {
        //     $this->result_success('order');
        // } else {

            // $contents = sendForm($pool['notify_url'], $data['query_timeout']);
            // Log::write(" pool order faild: " . $pool["notify_url"].'?'. $data['query_timeout']. " resp: " . $contents);
            // // delete
            // M('PoolPhones')->delete($pool['id']);
            // $this->result_error('deleted');
        // }

        // $this->result_success('');
    }

    protected function PhoneTimeout(){
        // $id = $this->request['id'];
        // if (!$id) {
        //     return $this->result_error('no param id');
        // }
        // $pool = M('PoolPhones')->find($id);
        // if (!$pool) {
        //     return $this->result_error('no pool phones');
        // }
        // if ($pool['status'] != 2) {
        //     return $this->result_error('pool status need 2');
        // }

        // $data = json_decode($pool['data'], true);
        // $success = false;
        // if ($data['transe']) {
        //     $channel = M('Channel')->find($data['transe']);
        //     try{
        //         $notify_url = $this->_site . 'Pay_Trans_Notify_Method_' . $channel['code'];
        //         $manger = new TranserManager($channel);
        //         $result = $manger->order($pool, $notify_url);
        //         if ($result) {
        //             // save and delete
        //             M()->startTrans();
        //             $order = [
        //                 'pool_id'       => $pool['id'],
        //                 'pid'           => $pool['pid'],
        //                 'phone'         => $pool['phone'],
        //                 'money'         => $pool['money'],
        //                 'notify_url'    => $pool['notify_url'],
        //                 'time'          => $pool['time'],
        //                 'channel'       => $pool['channel'],
        //                 'out_trade_id'  => $pool['out_trade_id'],
        //                 'order_id'      => $pool['order_id'],
        //                 'data'          => $pool['data'],
        //                 'phone_code'    => $pool['phone_code'],
        //                 'status'        => 0, //其实不需要状态
        //                 'cid'           => $channel['id'],
        //                 'order_time'    => $this->timestamp,
        //                 'pay_trade_id'  => $result['pay_trade_id'] ?: '',
        //             ];
        //             if (!M('PoolOrder')->add($order)) {
        //                 M()->rollback();
        //                 throw new Exception("save order error");
        //             }
        //             M()->commit();
        //             return $this->result_success($order);
        //         }
        //     }catch(Exception $e) {
        //         $this->result_error($e->getMessage());
        //     }
        // }

        // if ($success) {
        //     $this->result_success('order');
        // } else {
        //     // 直接回调匹配超时 并且删除缓存 删除数据库数据
        //     // notify
        //     /*sendForm($pool['notify_url'], $data['query_timeout']);
        //     // clear cache
        //     $this->cache->Client()->zDelete("pool_phone_timeout", $pool['id']);
        //     // delete
        //     M('PoolPhones')->delete($pool['id']);
        //     $this->result_success('deleted');*/
        //     $this->result_error('deleted');
        // }


        // 开始对下游进行请求
//        $provider = M('PoolProvider')->find($pool['pid']);
//        if (!$provider) {
//            return $this->result_error('no provider info ' . $pool['pid']);
//        }
//
//        $config = json_decode($provider['config'], true);
//        if ($config[''])




        $this->result_success('');
    }


}