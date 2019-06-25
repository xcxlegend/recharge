<?php
namespace Admin\Controller;

class PoolProviderController extends BaseController
{
    public function __construct()
    {
        parent::__construct();

    }

    //列表
    public function index()
    {
        $data = D('PoolProvider')->getList();
        $this->assign('list', $data['list']);
        $this->assign('page', $data['page']);
        $this->display();
    }

    public function add(){
        if(IS_POST){

            $post=I("post.");
            if(!$post["name"]){
                $this->ajaxReturn(['status'=>0,'msg'=>'请输入名称!']);
            }

            $str = $this->randomStr();

            $data["name"] = $post['name'];
            $data["appkey"] = substr(md5($str), 8, 16);
            $data["appsecret"] = md5($this->randomStr());
            $data["create_time"]=time();

            $status = D('PoolProvider')->add($data);

            $this->ajaxReturn(['status'=>$status]);

        }else{
            $this->display();
        }
        

    }
    public function delete()
    {    

        $id = I('id', 0, 'intval');
        if(!$id){
            $this->ajaxReturn(['status'=>0,'msg'=>'非法请求!']);
        }
        $where = array(
            'id'     => $id
        );

        $status = D('PoolProvider')->where($where)->delete();
        $this->ajaxReturn(['status'=>$status]);
        
    }

    public function reset(){

        $id = I('id', 0, 'intval');
        if(!$id){
            $this->ajaxReturn(['status'=>0,'msg'=>'非法请求!']);
        }
        $str = $this->randomStr();

        $data["appsecret"] = md5($str);
        $data["update_time"]=time();
        $data["id"]=$id;

        $status = D('PoolProvider')->save($data);
        $this->ajaxReturn(['status'=>$status]);
         
     }

    public function edit(){
       if(IS_POST){

            $data=I("post.");

            if(!$data['id']){
                $this->ajaxReturn(['status'=>0,'msg'=>'非法请求!']);
            }
            if(!$data["name"]){
                $this->ajaxReturn(['status'=>0,'msg'=>'请输入名称!']);
            }
            $data["update_time"]=time();
            $status = D('PoolProvider')->save($data);
            $this->ajaxReturn(['status'=>$status]);

        }else{
            $id = I('id', 0, 'intval');
            if(!$id){
                $this->ajaxReturn(['status'=>0,'msg'=>'非法请求!']);
            }
            $where = array(
                'id'     => $id
            );

            $info = D('PoolProvider')->where($where)->find();
            
            $this->assign('info',$info);
            $this->display();
        }
    }


    /**
     * 生成随机字符串
     */
    private function randomStr() {
        $year_code = array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J');
        $order_sn = $year_code[intval(date('Y')) - 2010] .
            strtoupper(dechex(date('m'))) . date('d') .
            substr(time(), -5) . substr(microtime(), 2, 5) . sprintf('d', rand(0, 99));
        return $order_sn;
    }


}
?>
