<?php
/**
 * Created by PhpStorm.
 * User: gaoxi
 * Date: 2017-04-02
 * Time: 23:01
 */

namespace Admin\Controller;

use Org\Util\P361zf;
use Org\Util\P59Pay;
use Org\Util\PHaitong;
use Think\Page;

/**
 * 订单管理控制器
 * Class OrderController
 * @package Admin\Controller
 * @model  对应数据库  M('Order')
 */
class OrderController extends BaseController
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * 订单列表
     * 1. 获取订单列表
     * 2. 重构页面 只展示部分信息 按照原页面的功能进行筛选和搜索
     * 3. 操作:  查看订单  设置已付  补发回调
     */
    public function index()
    {
        // Admin/Order/index.html
        $this->display();
    }


    // 设置已付
    public function setPay() {

    }


    // 补发回调
    public function notify() {

    }


}
