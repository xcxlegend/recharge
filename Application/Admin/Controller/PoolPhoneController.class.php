<?php
namespace Admin\Controller;
use Think\Page;
use \Think\Log;

class PoolPhoneController extends BaseController
{

    //列表
    public function index()
    {
        $param = I("get.");
        if(empty($param['status'])){
            $where['a.lock'] = 0;
        }else{
            $where['a.lock'] = 1;
        }

        if($param['phone']){
            $where['a.phone'] = $param['phone'];
        }
        if($param['user_order_id']){
            $where['b.out_trade_id'] = $param['user_order_id'];
        }
        if($param['order_id']){
            $where['a.order_id'] = $param['order_id'];
        }
        if($param['out_trade_id']){
            $where['a.out_trade_id'] = $param['out_trade_id'];
        }

        if(!empty($param['time'])){
            $where['a.time'] = $param['time'];
            list($stime, $etime)  = explode('|', $param['time']);
            $where['a.time'] = ['between', [strtotime($stime), strtotime($etime) ? strtotime($etime) : time()]];
        }

        $size  = 15;
        $rows  = I('get.rows', $size, 'intval');
        if (!$rows) {
            $rows = $size;
        }
        

        if($param['status']==1){
            $count  = M('PoolPhones')
                ->alias('a')
                ->field('a.*,b.out_trade_id as user_order_id')
                ->join('LEFT JOIN pay_order b ON a.id=b.pool_phone_id')
                ->where($where)
                ->count();
            $page           = new Page($count, $rows);
            $list           = M('PoolPhones')
                ->alias('a')
                ->field('a.*,b.out_trade_id as user_order_id')
                ->join('LEFT JOIN pay_order b ON a.id=b.pool_phone_id')
                ->where($where)
                ->limit($page->firstRow . ',' . $page->listRows)
                ->order('a.id desc')
                ->select();
        }else{
            $count  = M('PoolPhones')->alias('a')->where($where)->count();
            $page           = new Page($count, $rows);
            $list           = M('PoolPhones')
                ->alias('a')
                ->field('a.*')
                ->where($where)
                ->limit($page->firstRow . ',' . $page->listRows)
                ->order('a.id desc')
                ->select();
        }

        
        $sp_list = array('1'=>'移动','2'=>'联通','3'=>'电信');
        $this->assign("sp_list", $sp_list);
        $this->assign("list", $list);
        $this->assign("param", $param);
        $this->assign('page', $page->show());
        $this->display();
    }

    public function drawback()
    {
        $id = I("get.id");
        if (!$id) {
            $this->ajaxReturn(['info'=>'参数错误', 'status'=>false]);
            return;
        }
        $pool = M('PoolPhones')->find($id);
        if (!$pool) {
            return $this->ajaxReturn(['info'=>'no pool phones', 'status'=>false]);
        }

        $data = json_decode($pool['data'], true);

        parse_str($data['query_timeout'], $urlarr);
        $contents = sendForm($pool['notify_url'], $urlarr);
        Log::write(" pool order faild by admin: " . $pool["notify_url"].'?'. $data['query_timeout']. " resp: " . $contents);
        // delete
        M('PoolPhones')->delete($pool['id']);

        $this->ajaxReturn(['info'=>'操作成功', 'status' => true]);
    }

    public function blacklist()
    {
        $param = I("get.");
        if($param['pid']){
            $where['pid'] = $param['pid'];
        }
        if($param['phone']){
            $where['phone'] = $param['phone'];
        }

        
        $count          = M('blacklist')->where($where)->count();

        $size  = 15;
        $rows  = I('get.rows', $size, 'intval');
        if (!$rows) {
            $rows = $size;
        }
        $page           = new Page($count, $rows);

        $list = M('blacklist')
                ->alias('a')
                ->field('a.*,b.title as channel_title')
                ->join('LEFT JOIN pay_channel b ON a.cid=b.id')
                ->where($where)
                ->limit($page->firstRow . ',' . $page->listRows)
                ->order('a.id DESC')
                ->select();

        $sp_list = array('1'=>'移动','2'=>'联通','3'=>'电信');
        $this->assign("sp_list", $sp_list);
        $this->assign("list", $list);
        $this->assign("param", $param);
        $this->assign('page', $page->show());
        $this->display();
    }

    


}
?>
