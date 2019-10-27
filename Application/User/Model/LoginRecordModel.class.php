<?php
namespace User\Model;
use Think\Model;

class LoginRecordModel extends Model
{

    protected $tableName = 'loginrecord';

    public function getList($where)
    {

        $count = $this->where($where)->count();
        $page = new \Think\Page($count, 15);
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
            $user_info = session('user_auth');
            $log['userid']=$user_info['uid'];
            $log['url']=$_SERVER['REQUEST_URI'];
            if(!empty($data)){
                $log['data']=json_encode($data);
            }
            $log['loginip']=get_client_ip();
            $log['logindatetime']=date("Y-m-d H:i:s");
            $log['loginaddress']='-';
            //print_r($log);
            if($this->add($log)){
                
                return true;
            }
        }
        return false;
    }

}