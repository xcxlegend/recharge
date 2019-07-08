<?php
namespace Admin\Model;

class PoolProviderSuccessModel extends BaseModel
{

    protected $tableName = 'pool_rec';

    public function getList($where)
    {

        
        
        $join = 'LEFT JOIN pay_order b ON b.pool_phone_id=a.pool_id  LEFT JOIN pay_product c ON b.pay_code = c.code';
        $field = 'a.*,b.pay_memberid,b.trade_id,b.pay_applydate,b.pay_successdate,b.out_trade_id as pool_order_id,c.name as pay_name';
        
        $count = $this->alias('a')->where($where)->count();
        $page = new \Think\Page($count, parent::PAGE_LIMIT);
        $list = $this->alias('a')->field($field)->join($join)->where($where)->limit($page->firstRow, $page->listRows)->order('a.id DESC')->select();
        
        return array(
            'list' => $list,
            'page' => $page->show(),
        );
    }

    public function getAllList($where)
    {

        $join = 'LEFT JOIN pay_order b ON b.pool_phone_id=a.pool_id  LEFT JOIN pay_product c ON b.pay_code = c.code';
        $field = 'a.*,b.pay_memberid,b.trade_id,b.pay_applydate,b.pay_successdate,b.out_trade_id as pool_order_id,c.name as pay_name';
        
        $list = $this->alias('a')->field($field)->join($join)->where($where)->order('a.id DESC')->select();
        
        return  $list;
    }

    public function getInfo($id)
    {

        $where['a.id'] = $id;
        $join = 'LEFT JOIN pay_order b ON b.pool_phone_id=a.pool_id  
        LEFT JOIN pay_pool_provider c ON a.pid = c.id 
        LEFT JOIN pay_member d ON b.pay_memberid = d.id 
        LEFT JOIN pay_product e ON b.pay_code = e.code';

        $field = 'a.*,
        b.pay_memberid,
        b.trade_id,
        b.pay_amount,
        b.pay_actualamount,
        b.pay_poundage,
        b.pay_status,
        b.pay_notifyurl,
        b.pay_applydate,
        b.pay_successdate,
        b.out_trade_id as pool_order_id,
        c.name,c.contact,c.contact_tel,
        d.username,
        e.name as pay_name';

        return $this->alias('a')->field($field)->join($join)->where($where)->find();  
    }

}