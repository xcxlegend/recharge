<?php

namespace Admin\Controller;

class AreaController extends BaseController
{
    public function __construct()
    {
        parent::__construct();
        $area_model = D('PhoneCode');
        $this->area_model = $area_model;
    }

    //列表
    public function index()
    {
        $area = M('PhoneCode')->order('id asc')->select();
        $area = get_column($area);

        $this->assign('area',$area);
        $this->display();
    }
    

    public function add()
    {
        $pid = I('get.pid',0,'intval');
        if(IS_POST){
            $pid = I('post.pid',0,'intval');


            $data['name'] = I('post.name');
            $data['code'] = I('post.code');
            $data['status'] = I('post.status');
            $data['level'] = I('post.level',0,'intval');
            $data['pid'] = I('post.pid',0,'intval');

            if($this->area_model->isExistOpt($data['code'])){
                $this->ajaxReturn(['status'=>0,'msg'=>"该标识已存在"]);
            }
            $res = $this->area_model->add($data);
            $this->ajaxReturn(['status'=>$res]);
        }else{
            $this->assign('id',$pid);
            $this->display();
        }
    }


    public function edit()
    {
        if(IS_POST){
            $data = I('post.');
            if($this->area_model->isExistOpt($data['code'],$data['id'])){
                $this->ajaxReturn(['status'=>0,'msg'=>'该标识已存在']);
            }
            $result = M('PhoneCode')->save($data);
            if($result !== false){
                $this->ajaxReturn(['status'=>1,'msg'=>'更新成功']);
            }else{
                $this->ajaxReturn(['status'=>1,'msg'=>'更新失败']);
            }
        }else{
            $id = I('get.id','','intval');

            $info = M('PhoneCode')->where(['id'=>$id])->find();

            $this->assign('info',$info);
            $this->display();
        }
    }


    public function del()
    {
        $id = I('post.id',0,'intval');
        if($this->area_model->isExistSon($id)){
            $this->ajaxReturn(['status'=>0,'msg'=>'请先删除该省份下的市']);
        }
        $res = M('PhoneCode')->where(['id'=>$id])->delete();
        $this->ajaxReturn(['status'=>$res]);
    }


}