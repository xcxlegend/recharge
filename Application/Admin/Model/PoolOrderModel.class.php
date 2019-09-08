<?php
namespace Admin\Model;

class PoolOrderModel extends BaseModel
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



}