<?php
namespace Pay\Controller;

use Think\Controller;

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
        // 开始对下游进行请求


        $this->result_success('');
    }


}