<?php
namespace Admin\Controller;
use Think\Page;

class PoolPhoneController extends BaseController
{

    //列表
    public function index()
    {
        $param = I("get.");
        if(empty($param['status'])){
            $where['lock'] = 0;
        }else{
            $where['lock'] = 1;
        }

        
        $count          = M('PoolPhones')->where($where)->count();

        $size  = 15;
        $rows  = I('get.rows', $size, 'intval');
        if (!$rows) {
            $rows = $size;
        }
        $page           = new Page($count, $rows);


        $list           = M('PoolPhones')
            ->where($where)
            ->limit($page->firstRow . ',' . $page->listRows)
            ->order('id desc')
            ->select();
        $sp_list = array('1'=>'移动','2'=>'电信','3'=>'联通');
        $this->assign("sp_list", $sp_list);
        $this->assign("list", $list);
        $this->assign('page', $page->show());
        $this->display();
    }


}
?>
