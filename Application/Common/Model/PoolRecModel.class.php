<?php
/**
 * Created by PhpStorm.
 * User: Legend.Xie
 * Date: 2019-07-08
 * Time: 21:34
 */

namespace Common\Model;
use Think\Model;
use Think\Exception;

class PoolRecModel extends Model
{

    /**
     * 需要try catch
     * @param  [type] $rec_id [description]
     * @return [type]         [description]
     */
    public function drawback( $uid, $rec_id, $reason = '' ){
        $rec = $this->find( $rec_id );
        if (!$rec || $rec['status'] == 2) {
            throw new Exception("无订单或订单已退单");
            return;
        }

        $pid        = $rec['pid'];
        $provider = D('Common/PoolProvider')->find( $pid  );
        if (!$provider){
            throw new Exception("号码商信息错误");
            return;
        }

        $actmoney   = $rec['actmoney'];
        $m = M();
        $m->startTrans();

        if (!D('PoolMoneychange')->addData($provider['id'], UID, $provider['balance'], $actmoney, "订单退单: " . $rec['id'] , $rec['id'])){
                $m->rollback();
                throw new Exception("操作失败: 金额记录添加失败");
                return;
        }

        /*
          `rec_id` int(11) NOT NULL DEFAULT '0' COMMENT 'recID',
          `pid` int(11) NOT NULL DEFAULT '0' COMMENT '号码商ID',
          `time` int(11) NOT NULL DEFAULT '0' COMMENT '时间戳',
          `uid` int(11) NOT NULL DEFAULT '0' COMMENT '管理员ID',
          `reason` varchar(50) DEFAULT '' COMMENT '退单原因',
         */

        $drawback = [
            "rec_id"    => $rec['id'],
            "pid"       => $provider['id'],
            "time"      => time(),
            "uid"       => $uid,
            "reason"    => $reason,
        ];

        if (!M('PoolDrawback')->add($drawback)) {
            $m->rollback();
            throw new Exception("操作失败: 添加退单记录失败");
            return;
        }

        if (!$this->where(['id' => $rec['id']])->setField('status', 2)) {
            $m->rollback();
            throw new Exception("操作失败: 修改订单状态失败");
            return;
        }

        if (!M('PoolProvider')->where(['id' => $pid])->save(
                [
                    'money' => [ 'exp', ' money - ' . $actmoney ],
                    'balance' => [ 'exp', ' balance + ' . $actmoney ]
                ])){
                $m->rollback();
                throw new Exception("操作失败: 操作provider金额失败");
                return;
        }

        $m->commit();
        return true;
    }

}