<?php
/**
 * Created by PhpStorm.
 * User: Legend.Xie
 * Date: 2019/6/26
 * Time: 20:13
 */

namespace Common\Lib;


// 手机充值网厅接口
abstract class IPhoneRechagerLib implements IChannelLib
{

    protected $poolMgr;

    public function poolQuery( IPoolLib $poolMgr,  array &$param) {
        $this->poolMgr = $poolMgr;
        $poolMgr->query($param);
    }

    public function reset() {
        if ($this->poolMgr) {
            $this->poolMgr->reset();
        }
    }

}



