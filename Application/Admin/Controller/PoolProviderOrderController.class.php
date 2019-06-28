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
        if(empty($param['status'])){
            $where['status'] = 0;
        }else{
            $where['status'] = array('gt',0);
        }
        if(!empty($param['k'])){
            $where['name|contact|contact_tel'] = $param['k'];
        }

        $data = D('PoolProvider')->getList($where);


        $this->assign('param', $param);
        $this->assign('list', $data['list']);
        $this->assign('page', $data['page']);
        $this->display();
    }

    //列表
    public function export()
    {
        $param = I("get.");
        if(empty($param['status'])){
            $where['status'] = 0;
        }else{
            $where['status'] = array('gt',0);
        }
        if(!empty($param['k'])){
            $where['name|contact|contact_tel'] = $param['k'];
        }

        
        $title = array('商户名称', '联系人', '联系方式', '总收入', '余额', '状态', '创建时间');
        $data = D('PoolProvider')->where($where)->select();
        $list = array();
        foreach ($data as $item) {
            switch ($item['status']) {
                case 0:
                    $status = '未认证';
                    break;
                case 1:
                    $status = '正常';
                    break;
                case 2:
                    $status = '已关闭';
                    break;
            }

            $list = array(
                'name'    => $item['name'],
                'contact'      => $item['contact'],
                'contact_tel'     => $item['contact'],
                'money'    => $item['money'],
                'balance'      => $item['balance'],
                'status'  => $status,
                'create_time' => date('Y-m-d H:i:s', $item['create_time']),
            );
        }
        exportexcel($list, $title);

    }

    

}
?>
