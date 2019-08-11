<?php
namespace Pay\Controller;

use Common\Lib\TranserManager;
use Think\Controller;

use

/**
 * 内部RPC请求
 * Class RpcController
 * @package Pay\Controller
 */
use Think\Exception;class RpcController extends PayController
{

    // 认证
    public function __construct(){
        parent::__construct();
        //
    }


    public function index() {
        $call = $this->request['call'];
        if ($call){
            return call_user_func_array([$this, $call], $this->request);
        }
        $this->result_error('no call');
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
        if ($pool['status'] != 3) {
            return $this->result_error('pool status need 3');
        }

        $data = json_decode($pool['data'], true);
        $success = false;
        if ($data['transe']) {
            $channel = M('Channel')->find($data['transe']);
            try{
                $result = TranserManager::order($channel, $pool);
                if ($result) {
                    // save and delete
                    M()->startTrans();
                }
            }catch(Exception $e) {
                $this->result_error($e->getMessage());
            }
        }

        if ($success) {

        } else {

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