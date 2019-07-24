<?php
namespace Admin\Model;

use Think\Model;

class PhoneCodeModel extends Model
{
    /**
     * 查询是否已存在的opt
     * @param null $id
     * @return mixed
     */
    public function isExistOpt($code,$id=null)
    {
        $where = array(
            'code' => $code,
            
        );
        if($id){
            $where['id'] = array('neq',$id);
        }
        return $this->where($where)->find();
    }


    /**
     * 是否存在子级
     * @param $id
     * @return mixed
     */
    public function isExistSon($id)
    {
        $where = array(
            'pid' => $id,
        );

        return $this->where($where)->find();
    }


    
}