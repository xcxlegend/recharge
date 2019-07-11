<?php
/**
 * Created by PhpStorm.
 * User: Legend.Xie
 * Date: 2019/6/25
 * Time: 21:55
 */

namespace Admin\Controller;


class PoolProviderMoneyController extends BaseController
{

    protected $timestamp;

    public function __construct()
    {
        parent::__construct();
        $this->timestamp = time();
    }


    /**
     * 给号码商加钱
     */
    public function AddProviderBalance()
    {
        if (!IS_POST) {
            $this->assign('id', I('id'));
            $this->display();
            exit;
        }

        $post = I('request.');
        $id = $post['id'];
        $balance = $post['money'] / 100;

        if ( $balance <= 0) {
            $this->ajaxReturn(['status' => 0, 'msg' => '增加金额数值无效']);
            return;
        }

        if (!$post['remark']) {
            $this->ajaxReturn(['status' => 0, 'msg' => '需要填写原因']);
            return;
        }

        $model = M('PoolProvider');
        M()->startTrans();
        $provider = $model->lock(true)->find($id);
        if (!$provider) {
            M()->rollback();
            $this->ajaxReturn(['status' => 0, 'msg' => '号码商ID不存在']);
            return;
        }

        /*
  `pid` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '商户ID',
  `uid` int(11) NOT NULL DEFAULT '0' COMMENT '操作人ID 增加填写',
  `ymoney` decimal(15,4) unsigned NOT NULL DEFAULT '0.0000' COMMENT '原金额',
  `money` decimal(15,4) NOT NULL DEFAULT '0.0000' COMMENT '变动金额',
  `gmoney` decimal(15,4) unsigned NOT NULL DEFAULT '0.0000' COMMENT '变动后金额',
  `datetime` datetime DEFAULT NULL COMMENT '修改时间',
  `recid` int(11) DEFAULT NULL COMMENT 'rec号-消耗才记录',
  `lx` tinyint(3) unsigned NOT NULL DEFAULT '1' COMMENT '类型 0=增加 1=消耗',
  `orderid` varchar(50) DEFAULT NULL COMMENT '订单号',
  `contentstr` varchar(255) DEFAULT NULL COMMENT '备注',
         */
        $data = [
            "pid" => $id,
            "uid" => UID,
            "ymoney" => $provider['balance'],
            "money" => $balance,
            "gmoney" => $provider['balance'] + $balance,
            "datetime" => $this->timestamp,
            "recid" => 0,
//            "lx" => 0,
            "orderid" => "",
            "contentstr" => "后台增加金额"
        ];


        if (!D('PoolMoneychange')->addData($id, UID, $provider['balance'], $balance, $post['remark'])){

//            if (!M('PoolMoneychange')->add($data)){
            echo M('PoolMoneychange')->getError();
            var_dump(M('PoolMoneychange'));
            M()->rollback();
            $this->ajaxReturn(['status' => 0, 'msg' => '保存日志失败']);
            return;
        }
        if (!$model->where(['id' => $id])->setInc('balance', $balance)){
            M()->rollback();
            $this->ajaxReturn(['status' => 0, 'msg' => '添加失败']);
            return;
        }
        M()->commit();
        $this->ajaxReturn(['status' => 1]);
    }


}