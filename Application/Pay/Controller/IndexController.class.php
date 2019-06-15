<?php
namespace Pay\Controller;


/**
 * Class IndexController
 * @package Pay\Controller
 * @prief 充值接口
 */
class IndexController extends PayController
{


    public function __construct()
    {
        parent::__construct();
    }


    /**
     * 充值接口
     * 1. 调用接口获取充值的手机和金额
     * 2. 返回接口对外
     */
    public function index() {


        // 1. 检查参数, 签名等

        // 2. 调用RPC 获取充值信息

        // 3. 存储订单信息

        // 4. 返回接口内容


    }



}
