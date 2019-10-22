<?php
namespace Admin\Controller;

class AdminLogController extends BaseController
{
    public function __construct()
    {
        parent::__construct();

    }

    //列表
    public function index()
    {
        $param = I("get.");

        if(!empty($param['keywords'])){
            $where['admin_id|url|ip'] = $param['keywords'];
        }
        if(!empty($param['create_time'])){
            list($stime, $etime)  = explode('|', $param['create_time']);
            $where['create_time'] = ['between', [strtotime($stime), strtotime($etime) ? strtotime($etime) : time()]];
        }
    
        $data = D('AdminLog')->getList($where);


        $this->assign('param', $param);
        $this->assign('list', $data['list']);
        $this->assign('page', $data['page']);
        $this->display();
    }

    
}
?>
