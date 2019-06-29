<?php
namespace Admin\Controller;

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
        if(!empty($param['pay_memberid'])){
            $where['b.pay_memberid'] = $param['pay_memberid'];
        }
        if(!empty($param['order_id'])){
            $where['a.order_id'] = $param['order_id'];
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
            $where['a.sp'] = $param['sp'];
        }
        if(isset($param['status'])){
            $where['a.status'] = $param['status'];
        }
        $data = D('PoolProviderSuccess')->getList($where);
        

        //交易总额
        $money['total'] = D('PoolProviderSuccess')->field('sum(`money`) as money')->find();

        //上月
        $monthWhere['month'] = date('m',strtotime('last month'));
        $money['month'] = D('PoolProviderSuccess')->field('sum(`money`) as money')->where($monthWhere)->find();

        //上周
        $sWeek =  date("Y-m-d H:i:s",mktime(0, 0 , 0,date("m"),date("d")-date("w")+1-7,date("Y")));
        $eweek =  date("Y-m-d H:i:s",mktime(23,59,59,date("m"),date("d")-date("w")+7-7,date("Y")));
        $weekWhere['time'] = ['between', [strtotime($sWeek), strtotime($eweek)]];
        $money['week'] = D('PoolProviderSuccess')->field('sum(`money`) money')->where($weekWhere)->find();
        //今日
        $todayWhere['day'] = date("d");
        $money['today'] = D('PoolProviderSuccess')->field('sum(`money`) as money')->where($todayWhere)->find();

        //成功总额
        $money['success_total'] = D('PoolProviderSuccess')->field('sum(`money`) as money')->find();

        //今日成功总额
        $money['success_today'] = D('PoolProviderSuccess')->field('sum(`money`) as money')->where($todayWhere)->find();

        //订单总量
        $money['total']['count'] = D('PoolProviderSuccess')->count();

        //今日订单量
        $money['today']['count'] = D('PoolProviderSuccess')->where($todayWhere)->count();

        //成功订单总量
        $money['success_total']['count'] = D('PoolProviderSuccess')->count();

        //今日成功总量
        $money['success_today']['count'] = D('PoolProviderSuccess')->where($todayWhere)->count();

        $sp_list = array('1'=>'移动','2'=>'联通','3'=>'电信');

        $this->assign('param', $param);
        $this->assign('count', $money);
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

    

}
?>
