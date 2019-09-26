<?php
namespace Admin\Model;

class OrderStatisModel extends BaseModel
{


    public function setStatis($member_id,$field,$step=1)
    {

        
        $where['day'] = strtotime(date("Y-m-d"),time());
        $where['member_id'] = $member_id;
        $have = $this->where($where)->find();

        if($have){
            return $this->where($where)->setInc($field,$step);
        }else{
            $where[$field] = $step; 
            $where['create_time'] = time();
            return  $this->add($where);
        }
        
    }
}