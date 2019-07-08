<?php
namespace Admin\Model;

class AdminLogModel extends BaseModel
{


    public function getList($where)
    {

        $count = $this->where($where)->count();
        $page = new \Think\Page($count, parent::PAGE_LIMIT);
        $list = $this->where($where)->limit($page->firstRow, $page->listRows)->order('id DESC')->select();
        return array(
            'list' => $list,
            'page' => $page->show(),
        );
    }

    public function addLog($mark='')
    {
        if(IS_POST){
            $data = I('post.');
            $user_info = session('admin_auth');
            $log['admin_id']=$user_info['uid'];
            $log['url']=$_SERVER['REQUEST_URI'];
            if(!empty($data)){
                $log['data']=json_encode($data);
            }
            if(!empty($mark)){
                $log['mark']=$mark;
            }
            $log['ip']=get_client_ip();
            $log['create_time']=time();
            $this->add($log);
            if($this->add($log)){
                return true;
            }
        }
        return false;
    }

}