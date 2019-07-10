<?php
/**
 * Created by PhpStorm.
 * User: gaoxi
 * Date: 2017-08-22
 * Time: 14:34
 */
namespace Pool\Controller;

use Org\Util\P59Pay;
use Think\Page;

/**
 * 订单管理控制器
 * Class OrderController
 * @package Pool\Controller
 */
class OrderController extends PoolController
{

    public function __construct()
    {
        parent::__construct();
        $this->assign("Public", MODULE_NAME); // 模块名称
    }

    //列表
    public function index()
    {
        $param = I("get.");
        $where['a.pid'] = $this->provider['uid'];

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
            $where['a.channel'] = $param['sp'];
        }
        if(is_numeric($param['status'])){
            $where['a.status'] = $param['status'];
        }

        $sp_list = array('1'=>'移动','2'=>'电信','3'=>'联通');

        if(!empty($param['export'])){
            
            $data = D('Admin/PoolProviderSuccess')->getAllList($where);
            $list = array();
            foreach ($data as $item) {
                switch ($item['status']) {
                    case 0:
                        $status = '未回调';
                        break;
                    case 1:
                        $status = '回调成功';
                        break;
                }

                $list[] = array(
                    'order_id'    => $item['order_id'],
                    'trade_id'      => $item['trade_id'],
                    
                    'phone'    => $item['phone'],
                    'money'      => $item['money'],
                    'channel'      => $sp_list[$item['channel']],
                    'pay_applydate'      =>date('Y-m-d H:i:s',$item['pay_applydate']),
                    'pay_successdate'      => date('Y-m-d H:i:s',$item['pay_successdate']),
                    'status'  => $status,
                );
            }
            $title = array('平台订单号', '充值流水号', '手机号', '金额', '运营商', '创建时间', '成功时间', '状态');
            exportexcel($list, $title);
            exit;
            
        }

        $data = D('Admin/PoolProviderSuccess')->getList($where);
        

         //交易总额
         $totalWhere['pid'] = $this->provider['uid'];
         $money['total'] = D('PoolProviderSuccess')->field('sum(`money`) as money')->where($totalWhere)->find();
 
         //上月
         $monthWhere['month'] = date('m',strtotime('last month'));
         $monthWhere['pid'] = $this->provider['uid'];
         $money['month'] = D('PoolProviderSuccess')->field('sum(`money`) as money')->where($monthWhere)->find();
 
         //上周
         $sWeek =  date("Y-m-d H:i:s",mktime(0, 0 , 0,date("m"),date("d")-date("w")+1-7,date("Y")));
         $eweek =  date("Y-m-d H:i:s",mktime(23,59,59,date("m"),date("d")-date("w")+7-7,date("Y")));
         $weekWhere['time'] = ['between', [strtotime($sWeek), strtotime($eweek)]];
         $weekWhere['pid'] = $this->provider['uid'];
         $money['week'] = D('PoolProviderSuccess')->field('sum(`money`) money')->where($weekWhere)->find();
         //今日
         $todayWhere['day'] = date("d");
         $todayWhere['pid'] = $this->provider['uid'];
         $money['today'] = D('PoolProviderSuccess')->field('sum(`money`) as money')->where($todayWhere)->find();
 
         //订单总量
         $money['total']['count'] = D('PoolProviderSuccess')->where($totalWhere)->count();
 
         //今日订单量
         $money['today']['count'] = D('PoolProviderSuccess')->where($todayWhere)->count();


        

        $this->assign('param', $param);
        $this->assign('count', $money);
        $this->assign('sp_list', $sp_list);
        $this->assign('list', $data['list']);
        $this->assign('page', $data['page']);
        $this->display();
    }


}
?>
