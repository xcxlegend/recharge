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
        /**
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

        $request = I('request.');

        if (!$this->check($request)) {
            return;
        }




    }


    protected function check( $request ) {

        if ( !$request['pay_memberid']
        || !$request['pay_orderid']
        || !$request['pay_applydate']
        || !$request['pay_bankcode']
        || !$request['pay_notifyurl']
        || !$request['pay_amount']
        || !$request['pay_md5sign']
        ){
            $this->result_error("参数不足");
            return;
        }

        $userid = intval($request["pay_memberid"] - 10000); // 商户ID

        $member = M('Member')->where(['id' => $userid])->find();
        if (!$member) {
            $this->result_error('商户不存在');
            return;
        }

        $sign = $request['pay_md5sign'];
        unset($request['pay_md5sign']);
        return $sign == createSign( $member['apikey'], $request );

        return false;
    }







}
