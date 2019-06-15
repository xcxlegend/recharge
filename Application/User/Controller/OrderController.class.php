<?php
/**
 * Created by PhpStorm.
 * User: gaoxi
 * Date: 2017-08-22
 * Time: 14:34
 */
namespace User\Controller;

use Org\Util\P59Pay;
use Think\Page;

/**
 * 订单管理控制器
 * Class OrderController
 * @package User\Controller
 */
class OrderController extends UserController
{

    public function __construct()
    {
        parent::__construct();
        $this->assign("Public", MODULE_NAME); // 模块名称
    }

    public function index()
    {

        //通道
        $products = M('ProductUser')
            ->join('LEFT JOIN __PRODUCT__ ON __PRODUCT__.id = __PRODUCT_USER__.pid')
            ->where(['pay_product_user.status'=>1,'pay_product_user.userid'=>$this->fans['uid']])
            ->field('pay_product.name,pay_product.id,pay_product.code')
            ->select();
        $this->assign("banklist", $products);

        $where = array();
        $orderid = I("request.orderid");
        if ($orderid) {
            $where['out_trade_id'] = $orderid;
        }
        $this->assign("orderid", $orderid);
        $ddlx = I("request.ddlx","");
        if($ddlx != ""){
            $where['ddlx'] = array('eq',$ddlx);
        }
        $this->assign("ddlx", $ddlx);
        $tongdao = I("request.tongdao");
        if ($tongdao) {
            $where['channel_id'] = array('eq', $tongdao);
        }
        $this->assign("tongdao", $tongdao);
		$body = I("request.body");
        if ($body) {
            $where['pay_productname'] = array('eq', $body);
        }
        $this->assign("body", $body);
        $status = I("request.status",0,'intval');
        if ($status) {
            $where['pay_status'] = array('eq',$status);
        }
        $this->assign("status", $status);
        $createtime = urldecode(I("request.createtime"));
        if ($createtime) {
            list($cstime,$cetime) = explode('|',$createtime);
            $where['pay_applydate'] = $sumMap['pay_applydate'] = $failMap['pay_applydate'] = $map['create_at'] = ['between',[strtotime($cstime),strtotime($cetime)?strtotime($cetime):time()]];
        }
        $this->assign("createtime", $createtime);
        $successtime = urldecode(I("request.successtime"));
        if ($successtime) {
            list($sstime,$setime) = explode('|',$successtime);
            $where['pay_successdate'] = $sumMap['pay_successdate'] = $failMap['pay_successdate'] = $map['create_at'] = ['between',[strtotime($sstime),strtotime($setime)?strtotime($setime):time()]];
        }
        $this->assign("successtime", $successtime);
        $where['isdel'] = 0;
        $where['pay_memberid'] = $this->fans['memberid'];
        $count = M('Order')->where($where)->count();
        $size = 15;
        $rows  = I('get.rows', $size, 'intval');
        if(!$rows){
            $rows = $size;
        }
        //统计今日交易数据
        if ($status == '2') {
            //今日成功交易总额
            $todayBegin = date('Y-m-d').' 00:00:00';
            $todyEnd = date('Y-m-d').' 23:59:59';
            $stat['todaysum'] = M('Order')->where(['pay_memberid'=>10000+$this->fans['uid'],'pay_successdate'=>['between', [strtotime($todayBegin), strtotime($todyEnd)]], 'pay_status'=>['in', '1,2']])->sum('pay_amount');
            //今日实际到账总额
            $stat['taodayactualamount'] = M('Order')->where(['pay_memberid'=>10000+$this->fans['uid'],'pay_successdate'=>['between', [strtotime($todayBegin), strtotime($todyEnd)]], 'pay_status'=>['in', '1,2']])->sum('pay_actualamount');
            //今日成功笔数
            $stat['todaysuccesscount'] = M('Order')->where(['pay_memberid'=>10000+$this->fans['uid'],'pay_successdate'=>['between', [strtotime($todayBegin), strtotime($todyEnd)]], 'pay_status'=>['in', '1,2']])->count();
            //今日失败笔数
            $stat['todayfailcount'] = M('Order')->where(['pay_memberid'=>10000+$this->fans['uid'],'pay_applydate'=>['between', [strtotime($todayBegin), strtotime($todyEnd)]], 'pay_status'=>0])->count();
            foreach($stat as $k => $v) {
                $stat[$k] = $v+0;
            }
            $this->assign('stat', $stat);
        }

        //如果指定时间范围则按搜索条件做统计
        if ($createtime || $successtime) {
            $sumMap = $failMap = $where;
            $field                    = ['sum(`pay_amount`) pay_amount', 'sum(`pay_actualamount`) pay_actualamount', 'count(`id`) success_count'];
            $sumMap['pay_status'] = ['in', '0, 1, 2'];
            $sum                      = M('Order')->field($field)->where($sumMap)->find();
            foreach ($sum as $k => $v) {
                $sum[$k] += 0;
            }
            //失败笔数
            $failMap['pay_status'] = 0;
            $sum['fail_count'] =  M('Order')->where($failMap)->count();
            //投诉保证金冻结金额
            $map['C.user_id'] = $this->fans['uid'];
            $map['C.status'] = 0;
            $sum['complaints_deposit_freezed'] = M('complaints_deposit')->alias('as C')->join('LEFT JOIN __ORDER__ AS O ON C.pay_orderid=O.pay_orderid')
                ->where($map)
                ->sum('freeze_money');
            $sum['complaints_deposit_freezed'] += 0;
            $map['C.status'] = 1;
            $sum['complaints_deposit_unfreezed'] = M('complaints_deposit')->alias('as C')->join('LEFT JOIN __ORDER__ AS O ON C.pay_orderid=O.pay_orderid')
                ->where($map)
                ->sum('freeze_money');
            $sum['complaints_deposit_unfreezed'] += 0;
            $this->assign('sum', $sum);
        }
        $page            = new Page($count, $rows);
        $list = M('Order')
            ->where($where)
            ->limit($page->firstRow.','.$page->listRows)
            ->order('id desc')
            ->select();
        $this->assign('rows', $rows);
        $this->assign("list", $list);
        $this->assign('page',$page->show());
        C('TOKEN_ON',false);
        $this->display();
    }

    /**
     * 导出交易订单
     * */
    public function exportorder()
    {
        $orderid = I("request.orderid");
        if ($orderid) {
            $where['out_trade_id'] = $orderid;
        }
        $ddlx = I("request.ddlx","");
        if($ddlx != ""){
            $where['ddlx'] = array('eq',$ddlx);
        }
        $tongdao = I("request.tongdao");
        if ($tongdao) {
            $where['pay_tongdao'] = array('eq',$tongdao);
        }
        $bank = I("request.bank",'','strip_tags');
        if ($bank) {
            $where['pay_bankname'] = array('eq',$bank);
        }

        $status = I("request.status",0,'intval');
        if ($status) {
            $where['pay_status'] = array('eq',$status);
        }
        $createtime = urldecode(I("request.createtime"));
        if ($createtime) {
            list($cstime,$cetime) = explode('|',$createtime);
            $where['pay_applydate'] = ['between',[strtotime($cstime),strtotime($cetime)?strtotime($cetime):time()]];
        }
        $successtime = urldecode(I("request.successtime"));
        if ($successtime) {
            list($sstime,$setime) = explode('|',$successtime);
            $where['pay_successdate'] = ['between',[strtotime($sstime),strtotime($setime)?strtotime($setime):time()]];
        }
        $where['isdel'] = 0;
        $where['pay_memberid'] = $this->fans['memberid'];

        $title = array('ID','订单号','商户编号','交易金额','手续费','实际金额','提交时间','成功时间','支付通道','支付状态');
//        $data = M('Order')->where($where)->select();



        $query = M('Order')->where($where);


        $total = $query->count();
        $limit = 5000;

        $pager = intval($total / $limit ) + 1;

        for ($i = 1; $i <= $pager; $i++){
            $data =  M('Order')->where($where)->limit($limit)->page($i)->select();

            foreach ($data as $item){

                switch ($item['pay_status']){
                    case 0:
                        $status = '未处理';
                        break;
                    case 1:
                        $status = '成功，未返回';
                        break;
                    case 2:
                        $status = '成功，已返回';
                        break;
                }
                $list[] = array(
                    'id'               => $item['id'],
                    'pay_orderid'=>$item['out_trade_id'] ? $item['out_trade_id']:$item['pay_orderid'],
                    'pay_memberid'=>$item['pay_memberid'],
                    'pay_amount'=>$item['pay_amount'],
                    'pay_poundage'=>$item['pay_poundage'],
                    'pay_actualamount'=>$item['pay_actualamount'],
                    'pay_applydate'=>date('Y-m-d H:i:s',$item['pay_applydate']),
                    'pay_successdate'=>date('Y-m-d H:i:s',$item['pay_successdate']),
                    'pay_bankname'=>$item['pay_bankname'],
                    'pay_status'=>$status,
                );
            }
            unset($data);
        }
        $numberField = ['pay_amount', 'pay_poundage', 'pay_actualamount'];
        exportexcel($list, $title, $numberField);
    }


    /**
     * 查单并且如果成功则进行补单
     */
    public function checkOrder(){
        $id = I('request.orderid');
        $order = M('Order')
            ->join('LEFT JOIN __MEMBER__ ON (__MEMBER__.id + 10000) = __ORDER__.pay_memberid')
            ->field('pay_member.id as userid,pay_member.username,pay_member.realname,pay_order.*')
            ->where(['pay_order.id' => $id])
            ->find();
        if (!$order){
            $this->ajaxReturn(['status' => 0, 'msg' => '订单信息错误']);
            return;
        }

        if ($order['pay_status'] != 0) {
            $this->ajaxReturn(['status' => 0, 'msg' => '当前订单已经是成功订单']);
            return;
        }

        $channel_info = M('Channel')->where(['id' => $order['channel_id']])->find();

        switch ($order['pay_bankcode']){
            // 901
            //902
            //903
            //904'
            case '901':
            case '902':
            case '903':
            case '904':
                break;
            default:
                $this->ajaxReturn(['status' => 0, 'msg' => '支付通道无查单功能']);
                return;
        }

        /*$p59Pay = new P59Pay("YlLPzfT3ij", "tGnAwMqpf8ANaPryblDM", "");
        $result = $p59Pay->checkOrder($order["out_trade_id"]);
        if ($result['code'] != 4000) {
            $this->ajaxReturn(['status' => 0, 'msg' => $result['message']]);
            return;
        }

        if ($result['data'] && $result['data']['orderStatus'] == 4) {
            $payModel = D('Pay');
            $res = $payModel->completeOrder($order['pay_orderid'], '', 0);
            if ($res) {
                $this->ajaxReturn(['status' => 1, 'msg' => "查询成功, 已将订单置为成功状态, 并回调商户！"]);
            } else {
                $this->ajaxReturn(['status' => 0, 'msg' => "查询成功, 设置订单失败"]);
            }
            // 查询成功
        } else {
            $this->ajaxReturn(['status' => 0, 'msg' => '当前订单查询到未支付']);
        }*/

        if ($order['pay_zh_tongdao']) {
            switch ($order['pay_zh_tongdao']) {
                case 'P59':
                    $appkey = C('P59PAY.APPKEY');
                    $appsecret = C('P59PAY.APPSECT');
                    $p59Pay = new P59Pay($appkey, $appsecret, "");
                    if ($channel_info && $channel_info['gateway']) {
                        $p59Pay->setGateway($channel_info['gateway']);
                    }
                    $ret = $p59Pay->checkOrder($order["out_trade_id"]);
                    break;
                case 'P361zf':
                    $appkey = C('P361ZF.APPKEY');
                    $appsecret = C('P361ZF.APPSECT');
                    $pay = new P361zf($appkey, $appsecret, "");
                    if ($channel_info && $channel_info['gateway']) {
                        $pay->setGateway($channel_info['gateway']);
                    }
                    $ret = $pay->checkOrder($order["out_trade_id"]);
                    break;
                case 'PHaitong':
                    $appkey = C('PHaitong.APPKEY');
                    $appsecret = C('PHaitong.APPSECT');
                    $pay = new PHaitong($appkey, $appsecret, "");
                    if ($channel_info && $channel_info['gateway']) {
                        $pay->setGateway($channel_info['gateway']);
                    }
                    $ret = $pay->checkOrder($order["out_trade_id"]);
                    break;
                default:
                    $this->ajaxReturn(['status' => 0, 'msg' => "无通道信息"]);
            }
        }

        if ($ret) {
            $payModel = D('Pay');
            $res = $payModel->completeOrder($order['pay_orderid'], '', 0);
            if ($res) {
                $this->ajaxReturn(['status' => 1, 'msg' => "查询成功, 已将订单置为成功状态, 并回调商户！"]);
            } else {
                $this->ajaxReturn(['status' => 0, 'msg' => "查询成功, 设置订单失败"]);
            }
            // 查询成功
        } else {
            $this->ajaxReturn(['status' => 0, 'msg' => '当前订单查询到未支付']);
        }

        return;
    }

    /**
     * 查看订单
     */
    public function show()
    {
        $id = I("get.oid",0,'intval');
        if($id){
            $order = M('Order')
                ->where(['id'=>$id])
                ->find();
        }

        if($order['pay_memberid'] != $this->fans['memberid']) {
            $parentId = M('Member')->where(['id'=>$order['pay_memberid']-10000])->getField('parentid');
            if($parentId != $this->fans['uid']) {
                $this->error('没有权限查看该订单');
            }
        }
        $this->assign('order',$order);
        $this->display();
    }

    /**
     *  伪删除订单
     */
    /*
    public function delOrder()
    {
        if(IS_POST){
            $id = I('post.id',0,'intval');
            if($id){
                $res = M('Order')->where(['id'=>$id,'pay_memberid'=>$this->fans['memberid']])->setField('isdel',1);
            }
            $this->ajaxReturn(['status'=>$res]);
        }
    }
    */
}
?>
