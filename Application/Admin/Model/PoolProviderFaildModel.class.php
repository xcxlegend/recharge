<?php
namespace Admin\Model;

class PoolProviderFaildModel extends BaseModel
{

    protected $tableName = 'pool_faild';

    public function getList($where)
    {

        
        
        $join = 'LEFT JOIN pay_order b ON b.pool_phone_id=a.pool_id  LEFT JOIN pay_product c ON b.pay_code = c.code';
        $field = 'a.*,b.pay_memberid,b.trade_id,b.pay_applydate,b.pay_successdate,b.out_trade_id as pool_order_id,b.success_url,c.name as pay_name';
        

        $count = $this->alias('a')->join($join)->where($where)->count();

        $page = new \Think\Page($count, parent::PAGE_LIMIT);
        $list = $this->alias('a')->field($field)->join($join)->where($where)->limit($page->firstRow, $page->listRows)->order('a.id DESC')->select();
        
        return array(
            'list' => $list,
            'page' => $page->show(),
        );
    }

    public function getExportList($where,$limit)
    {

        $join = 'LEFT JOIN pay_order b ON b.pool_phone_id=a.pool_id  LEFT JOIN pay_product c ON b.pay_code = c.code';
        $field = 'a.*,b.pay_memberid,b.trade_id,b.pay_applydate,b.pay_successdate,b.out_trade_id as pool_order_id,b.success_url,c.name as pay_name';
        
        $list = $this->alias('a')->field($field)->join($join)->where($where)->limit($limit)->order('a.id DESC')->select();
        return  $list;
    }

    public function getCount($where)
    {

        $join = 'LEFT JOIN pay_order b ON b.pool_phone_id=a.pool_id  LEFT JOIN pay_product c ON b.pay_code = c.code';
        return $this->alias('a')->join($join)->where($where)->order('a.id DESC')->count();
    }



}