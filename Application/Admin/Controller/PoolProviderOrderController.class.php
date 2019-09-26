<?php
namespace Admin\Controller;
use Think\Exception;

class PoolProviderOrderController extends BaseController
{
    public function __construct()
    {
        parent::__construct();

    }

    //列表
    public function index()
    {
        $param = I("get.");
        if(!empty($param['pid'])){
            $where['a.pid'] = $param['pid'];
        }
        if(!empty($param['pay_memberid'])){
            $where['b.pay_memberid'] = $param['pay_memberid'];
        }
        if(!empty($param['order_id'])){
            $where['a.order_id'] = $param['order_id'];
        }
        if(!empty($param['pool_order_id'])){
            $where['b.out_trade_id'] = $param['pool_order_id'];
        }
        if(!empty($param['out_trade_id'])){
            $where['a.out_trade_id'] = $param['out_trade_id'];
        }
        if(!empty($param['trade_id'])){
            $where['b.trade_id'] = $param['trade_id'];
        }
        if(!empty($param['phone'])){
            $where['a.phone'] = $param['phone'];
        }
        if(!empty($param['create_time'])){
            $where['b.pay_applydate'] = $param['pay_applydate'];
            list($stime, $etime)  = explode('|', $param['create_time']);
            $where['b.pay_applydate'] = ['between', [strtotime($stime), strtotime($etime) ? strtotime($etime) : time()]];
        }
        if(!empty($param['money'])){
            $where['a.money'] = $param['money']*100;//分
        }
        if(!empty($param['sp'])){
            $where['a.channel'] = $param['sp'];
        }
        if(is_numeric($param['status'])){
            $where['a.status'] = $param['status'];
        }

        $sp_list = array('1'=>'移动','2'=>'电信','3'=>'联通');

        if(!empty($param['export'])){
            set_time_limit(0);
            header ( "Content-type:application/vnd.ms-excel" );
            header ( "Content-Disposition:filename=" . iconv ( "UTF-8", "GB18030", "话充订单" ) . ".csv" );
            
            $fp = fopen('php://output', 'a'); 
            
            $title = array('平台订单号', '充值流水号', '商户订单号','商户ID', '号码商ID', '手机号', '金额', '运营商', '支付方式', '创建时间', '成功时间', '状态', '添加时间');
            foreach ($title as $i => $v) {  
                $title[$i] = iconv('utf-8', 'GB18030', $v);  
            }

            fputcsv($fp, $title);

            $count = D('PoolProviderSuccess')->getCount($where);
            
            
            $limit = 5000;
            for ($i=0;$i<intval($count/$limit)+1;$i++){

                $data = D('PoolProviderSuccess')->getExportList($where,strval($i*$limit).",{$limit}");

                foreach ( $data as $item ) {
                    $rows = array();
                    switch ($item['status']) {
                        case 0:
                            $status = '未回调';
                            break;
                        case 1:
                            $status = '回调成功';
                            break;
                        case 2:
                            $status = '退单';
                            break;
                    }
                    $trade_id = !$item['trade_id'] ? $item['pool_trans_id'] :$item['trade_id'];
                    $pay_applydate = !$item['pay_applydate'] ? $item['pool_order_time'] :$item['pay_applydate'];
                    $pay_successdate = !$item['pay_successdate'] ? $item['pool_finish_time'] :$item['pay_successdate'];

    
                    $info = array(
                        'order_id'    => $item['order_id'],
                        'trade_id'      => $trade_id,
                        'pool_order_id'     => $item['pool_order_id'],
                        'pay_memberid'    => $item['pay_memberid'],
                        'pid'    => $item['pid'],
                        'phone'    => $item['phone'],
                        'money'      => $item['money'],
                        'channel'      => $sp_list[$item['channel']],
                        'pay_name'      => $item['pay_name'],
                        'pay_applydate'      =>date('Y-m-d H:i:s',$pay_applydate),
                        'pay_successdate'      => date('Y-m-d H:i:s',$pay_successdate),
                        'status'  => $status,
                        'time'      => date('Y-m-d H:i:s',$item['time']),
                    );

                    foreach ( $info as $text){
                        $rows[] = iconv('utf-8', 'GB18030', $text);
                    }
                    fputcsv($fp, $rows);
                }
                
                //释放内存
                unset($data);
                ob_flush();
                flush();
            }
            exit;
            
        }

        $data = D('PoolProviderSuccess')->getList($where);
        

        //交易总额
        // $money['total'] = D('PoolProviderSuccess')->field('sum(`money`) as money')->find();

        // //上月
        // $monthWhere['month'] = date('m',strtotime('last month'));
        // $money['month'] = D('PoolProviderSuccess')->field('sum(`money`) as money')->where($monthWhere)->find();

        // //上周
        // $sWeek =  date("Y-m-d H:i:s",mktime(0, 0 , 0,date("m"),date("d")-date("w")+1-7,date("Y")));
        // $eweek =  date("Y-m-d H:i:s",mktime(23,59,59,date("m"),date("d")-date("w")+7-7,date("Y")));
        // $weekWhere['time'] = ['between', [strtotime($sWeek), strtotime($eweek)]];
        // $money['week'] = D('PoolProviderSuccess')->field('sum(`money`) money')->where($weekWhere)->find();
        // //今日
        // $todayWhere['day'] = date("d");
        // $money['today'] = D('PoolProviderSuccess')->field('sum(`money`) as money')->where($todayWhere)->find();


        // //订单总量
        // $money['total']['count'] = D('PoolProviderSuccess')->count();

        // //今日订单量
        // $money['today']['count'] = D('PoolProviderSuccess')->where($todayWhere)->count();


        

        $this->assign('param', $param);
        // $this->assign('count', $money);
        $this->assign('sp_list', $sp_list);
        $this->assign('list', $data['list']);
        $this->assign('page', $data['page']);
        $this->display();
    }


    public function info()
    {
        $id = I("get.id");
        $info = D('PoolProviderSuccess')->getInfo($id);
        $sp_list = array('1'=>'移动','2'=>'联通','3'=>'电信');
        
        $this->assign('sp_list', $sp_list);
        $this->assign('info', $info);
        $this->display();
    }

    public function drawback()
    {
        $id = I("get.id");
        if (!$id) {
            $this->ajaxReturn(['info'=>'参数错误', 'status'=>false]);
            return;
        }

        try {
            D('Common/PoolRec')->drawback( UID, $id, '退单');
        } catch (Exception $e) {
            $this->ajaxReturn(['info'=>$e->getMessage(), 'status' => false]);
            return;
        }
        $order = M('PoolRec')->where(['id' => $id])->find();

        D('Admin/PoolStatis')->setStatis($order['pool_id'],'refund_order');
        D('Admin/PoolStatis')->setStatis($order['pool_id'],'refund_money',$order['money']);

        $this->ajaxReturn(['info'=>'退单成功', 'status' => true]);
    }

    //列表
    public function transe()
    {
        $param = I("get.");
        if(!empty($param['pid'])){
            $where['pid'] = $param['pid'];
        }
        if(!empty($param['order_id'])){
            $where['order_id'] = $param['order_id'];
        }

        if(!empty($param['out_trade_id'])){
            $where['out_trade_id'] = $param['out_trade_id'];
        }
        if(!empty($param['pay_trade_id'])){
            $where['pay_trade_id'] = $param['pay_trade_id'];
        }
        if(!empty($param['phone'])){
            $where['phone'] = $param['phone'];
        }
        if(!empty($param['order_time'])){
            list($stime, $etime)  = explode('|', $param['order_time']);
            $where['order_time'] = ['between', [strtotime($stime), strtotime($etime) ? strtotime($etime) : time()]];
        }
        if(!empty($param['money'])){
            $where['money'] = $param['money']*100;//分
        }
        if(!empty($param['sp'])){
            $where['channel'] = $param['sp'];
        }
        if(is_numeric($param['status'])){
            $where['status'] = $param['status'];
        }

        $sp_list = array('1'=>'移动','2'=>'电信','3'=>'联通');

        if(!empty($param['export'])){
            set_time_limit(0);
            header ( "Content-type:application/vnd.ms-excel" );
            header ( "Content-Disposition:filename=" . iconv ( "UTF-8", "GB18030", "转号订单" ) . ".csv" );
            
            $fp = fopen('php://output', 'a'); 
            
            $title = array('平台订单号', '号码商订单号', '上游流水号','号码商ID', '手机号', '金额', '运营商', '创建时间', '成功时间', '状态', '添加时间');
            foreach ($title as $i => $v) {  
                $title[$i] = iconv('utf-8', 'GB18030', $v);  
            }

            fputcsv($fp, $title);

            $count = D('PoolOrder')->where($where)->count();
            
            
            $limit = 5000;
            for ($i=0;$i<intval($count/$limit)+1;$i++){

                $data = D('PoolOrder')->where($where)->limit(strval($i*$limit).",{$limit}")->order('id DESC')->select();

                foreach ( $data as $item ) {
                    $rows = array();
                    switch ($item['status']) {
                        case 0:
                            $status = '未支付';
                            break;
                        case 1:
                            $status = '支付成功';
                            break;

                    }
    
                    $info = array(
                        'order_id'    => $item['order_id'],
                        'trade_id'      => $item['out_trade_id'],
                        'pool_order_id'     => $item['trans_id'],
                        'pid'    => $item['pid'],
                        'phone'    => $item['phone'],
                        'money'      => $item['money'],
                        'channel'      => $sp_list[$item['channel']],
                        'pay_applydate'      =>date('Y-m-d H:i:s',$item['pay_applydate']),
                        'pay_successdate'      => date('Y-m-d H:i:s',$item['pay_successdate']),
                        'status'  => $status,
                        'time'      => date('Y-m-d H:i:s',$item['time']),
                    );

                    foreach ( $info as $text){
                        $rows[] = iconv('utf-8', 'GB18030', $text);
                    }
                    fputcsv($fp, $rows);
                }
                
                //释放内存
                unset($data);
                ob_flush();
                flush();
            }
            exit;
            
        }

        $data = D('PoolOrder')->getList($where);
        

        $this->assign('param', $param);
        $this->assign('sp_list', $sp_list);
        $this->assign('list', $data['list']);
        $this->assign('page', $data['page']);
        $this->display();
    }


     //失败列表
     public function faild()
     {
         $param = I("get.");
         if(!empty($param['pid'])){
             $where['a.pid'] = $param['pid'];
         }
         if(!empty($param['pay_memberid'])){
             $where['b.pay_memberid'] = $param['pay_memberid'];
         }
         if(!empty($param['order_id'])){
             $where['a.order_id'] = $param['order_id'];
         }
         if(!empty($param['pool_order_id'])){
             $where['b.out_trade_id'] = $param['pool_order_id'];
         }
         if(!empty($param['out_trade_id'])){
             $where['a.out_trade_id'] = $param['out_trade_id'];
         }
         if(!empty($param['trade_id'])){
             $where['b.trade_id'] = $param['trade_id'];
         }
         if(!empty($param['phone'])){
             $where['a.phone'] = $param['phone'];
         }
         if(!empty($param['create_time'])){
             $where['b.pay_applydate'] = $param['pay_applydate'];
             list($stime, $etime)  = explode('|', $param['create_time']);
             $where['b.pay_applydate'] = ['between', [strtotime($stime), strtotime($etime) ? strtotime($etime) : time()]];
         }
         if(!empty($param['money'])){
             $where['a.money'] = $param['money']*100;//分
         }
         if(!empty($param['sp'])){
             $where['a.channel'] = $param['sp'];
         }
         if(is_numeric($param['status'])){
             $where['a.status'] = $param['status'];
         }
 
         $sp_list = array('1'=>'移动','2'=>'电信','3'=>'联通');
 
         if(!empty($param['export'])){
             set_time_limit(0);
             header ( "Content-type:application/vnd.ms-excel" );
             header ( "Content-Disposition:filename=" . iconv ( "UTF-8", "GB18030", "话充订单" ) . ".csv" );
             
             $fp = fopen('php://output', 'a'); 
             
             $title = array('平台订单号', '充值流水号', '商户订单号','商户ID', '号码商ID', '手机号', '金额', '运营商', '支付方式', '创建时间', '成功时间', '状态', '添加时间');
             foreach ($title as $i => $v) {  
                 $title[$i] = iconv('utf-8', 'GB18030', $v);  
             }
 
             fputcsv($fp, $title);
 
             $count = D('PoolProviderFaild')->getCount($where);
             
             
             $limit = 5000;
             for ($i=0;$i<intval($count/$limit)+1;$i++){
 
                 $data = D('PoolProviderFaild')->getExportList($where,strval($i*$limit).",{$limit}");
 
                 foreach ( $data as $item ) {
                     $rows = array();
                     switch ($item['status']) {
                         case 0:
                             $status = '未回调';
                             break;
                         case 1:
                             $status = '回调成功';
                             break;
                     }
                     $trade_id = !$item['trade_id'] ? $item['pool_trans_id'] :$item['trade_id'];
                     $pay_applydate = !$item['pay_applydate'] ? $item['pool_order_time'] :$item['pay_applydate'];
                     $pay_successdate = !$item['pay_successdate'] ? $item['pool_finish_time'] :$item['pay_successdate'];
 
     
                     $info = array(
                         'order_id'    => $item['order_id'],
                         'trade_id'      => $trade_id,
                         'pool_order_id'     => $item['pool_order_id'],
                         'pay_memberid'    => $item['pay_memberid'],
                         'pid'    => $item['pid'],
                         'phone'    => $item['phone'],
                         'money'      => $item['money'],
                         'channel'      => $sp_list[$item['channel']],
                         'pay_name'      => $item['pay_name'],
                         'pay_applydate'      =>date('Y-m-d H:i:s',$pay_applydate),
                         'pay_successdate'      => date('Y-m-d H:i:s',$pay_successdate),
                         'status'  => $status,
                         'time'      => date('Y-m-d H:i:s',$item['time']),
                     );
 
                     foreach ( $info as $text){
                         $rows[] = iconv('utf-8', 'GB18030', $text);
                     }
                     fputcsv($fp, $rows);
                 }
                 
                 //释放内存
                 unset($data);
                 ob_flush();
                 flush();
             }
             exit;
             
         }
 
         $data = D('PoolProviderFaild')->getList($where);
         $this->assign('param', $param);
         $this->assign('count', $money);
         $this->assign('sp_list', $sp_list);
         $this->assign('list', $data['list']);
         $this->assign('page', $data['page']);
         $this->display();
     }

     //失败列表
    public function statis()
    {
        $param = I("get.");
        if(!empty($param['pid'])){
            $where['pool_id'] = $param['pid'];
            $where1['a.pool_id'] = $param['pid'];
        }
        if(!empty($param['day'])){
            list($stime, $etime)  = explode('|', $param['day']);
            $where['day'] = ['between', [strtotime($stime), strtotime($etime) ? strtotime($etime) : time()]];
            $where1['a.day'] = ['between', [strtotime($stime), strtotime($etime) ? strtotime($etime) : time()]];
        }
        $count['do_order'] = M('PoolStatis')->field('sum(`do_order`) as do_order')->where($where)->find();
        $count['order'] = M('PoolStatis')->field('sum(`order`) as order_num')->where($where)->find();
        $count['order_money'] = M('PoolStatis')->field('sum(`order_money`) as order_money')->where($where)->find();
        
        $count['match'] = M('PoolStatis')->field('sum(`match`) as match_num')->where($where)->find();
        $count['match_money'] = M('PoolStatis')->field('sum(`match_money`) as match_money')->where($where)->find();
        
        $count['pay_order'] = M('PoolStatis')->field('sum(`pay_order`) as pay_order')->where($where)->find();
        $count['pay_money'] = M('PoolStatis')->field('sum(`pay_money`) as pay_money')->where($where)->find();
        $count['deduction_order'] = M('PoolStatis')->field('sum(`deduction_order`) as deduction_order')->where($where)->find();
        $count['deduction_money'] = M('PoolStatis')->field('sum(`deduction_money`) as deduction_money')->where($where)->find();
        $count['refund_order'] = M('PoolStatis')->field('sum(`refund_order`) as refund_order')->where($where)->find();
        $count['refund_money'] = M('PoolStatis')->field('sum(`refund_money`) as refund_money')->where($where)->find();
        $count['success_notify'] = M('PoolStatis')->field('sum(`success_notify`) as success_notify')->where($where)->find();
        $count['success_money'] = M('PoolStatis')->field('sum(`success_money`) as success_money')->where($where)->find();

        $join = 'LEFT JOIN pay_pool_provider b ON a.pool_id=b.id';
        $field = 'a.*,b.name';
        $countnum = M('PoolStatis')->alias('a')->join($join)->where($where1)->count();
        $page = new \Think\Page($countnum, 15);

        $list = M('PoolStatis')->alias('a')->join($join)->field($field)->where($where1)->limit($page->firstRow, $page->listRows)->order('id DESC')->select();
        
        $this->assign('count', $count);
        $this->assign('param', $param);
        $this->assign('list', $list);
        $this->assign('page', $page->show());
        $this->display();
    }

}
?>
