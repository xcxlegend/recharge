<?php
namespace Admin\Model;

class PoolProviderSuccessModel extends BaseModel
{

    protected $tableName = 'pool_rec';

    public function getList($where)
    {

        
        
        // $join = 'LEFT JOIN pay_order b ON b.pool_phone_id=a.pool_id  LEFT JOIN pay_product c ON b.pay_code = c.code';
        // $field = 'a.*,b.pay_memberid,b.trade_id,b.pay_applydate,b.pay_successdate,b.out_trade_id as pool_order_id,b.success_url,c.name as pay_name';
        
        // $join = 'LEFT JOIN pay_order b ON b.pool_phone_id=a.pool_id  
        // LEFT JOIN pay_pool_order d ON a.order_id = d.order_id';
        // $field = 'a.*,
        // b.pay_memberid,b.trade_id,b.pay_applydate,b.pay_successdate,b.out_trade_id as pool_order_id,b.success_url,
        // d.order_time as pool_order_time,d.finish_time as pool_finish_time,d.trans_id as pool_trans_id,d.notify_url as pool_notify_url';
        
        $join = 'LEFT JOIN pay_order b ON b.pool_phone_id=a.pool_id';
        $field = 'a.*,
        b.pay_memberid,b.trade_id,b.pay_applydate,b.pay_successdate,b.out_trade_id as pool_order_id,b.success_url';
        

        $count = $this->alias('a')->join($join)->where($where)->count();
        //echo($this->getLastSql());
       // exit;
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
        $field = 'a.*,b.pay_memberid,b.trade_id,b.pay_applydate,b.pay_successdate,b.out_trade_id as pool_order_id,b.success_url,c.name as pay_name';
        
        $list = $this->alias('a')->field($field)->join($join)->where($where)->order('a.id DESC')->select();
        
        return  $list;
    }

    public function getExportList($where,$limit)
    {

        $join = 'LEFT JOIN pay_order b ON b.pool_phone_id=a.pool_id  
        LEFT JOIN pay_product c ON b.pay_code = c.code  
        LEFT JOIN pay_pool_order d ON a.order_id = d.order_id';
        $field = 'a.*,
        b.pay_memberid,b.trade_id,b.pay_applydate,b.pay_successdate,b.out_trade_id as pool_order_id,b.success_url,
        c.name as pay_name,
        d.order_time as pool_order_time,d.finish_time as pool_finish_time,d.trans_id as pool_trans_id,d.notify_url as pool_notify_url';
        
        $list = $this->alias('a')->field($field)->join($join)->where($where)->limit($limit)->order('a.id DESC')->select();
        return  $list;
    }

    public function getCount($where)
    {

        $join = 'LEFT JOIN pay_order b ON b.pool_phone_id=a.pool_id  LEFT JOIN pay_product c ON b.pay_code = c.code';
        return $this->alias('a')->join($join)->where($where)->order('a.id DESC')->count();
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
        b.success_url,
        c.name,c.contact,c.contact_tel,
        d.username,
        e.name as pay_name';

        return $this->alias('a')->field($field)->join($join)->where($where)->find();  
    }

}