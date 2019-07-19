<?php
/**
 * Created by PhpStorm.
 * User: Legend.Xie
 * Date: 2019/6/29
 * Time: 21:34
 */

namespace Common\Model;

use Think\Model;

class PoolMoneychangeModel extends Model
{

    public function addData( $pid, $uid,$ymoney, $balance, $contentstr, $recid = 0 ,$type=1) {

        $data = [
            "pid" => $pid,
            "uid" => $uid,
            "type"=>$type,
            "ymoney" => $ymoney,
            "money" => $balance,
            "gmoney" => $ymoney + $balance,
            "datetime" =>  time(),
            "recid" => $recid,
            "contentstr" => $contentstr
        ];
        return $this->add($data);

    }




}