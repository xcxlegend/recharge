<?php
namespace Pay\Controller;

use Common\Lib\TranserManager;
use Think\Exception;

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

    protected function PhoneTimeout(){
        $id = $this->request['id'];
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
        $success = false;
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
                $this->result_error($e->getMessage());
            }
        }

        if ($success) {
            $this->result_success('order');
        } else {
            // 直接回调匹配超时 并且删除缓存 删除数据库数据
            // notify
            /*sendForm($pool['notify_url'], $data['query_timeout']);
            // clear cache
            $this->cache->Client()->zDelete("pool_phone_timeout", $pool['id']);
            // delete
            M('PoolPhones')->delete($pool['id']);
            $this->result_success('deleted');*/
            $this->result_error('deleted');
        }


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