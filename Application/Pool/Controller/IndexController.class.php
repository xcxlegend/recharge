<?php
/**
 * Created by PhpStorm.
 * User: gaoxi
 * Date: 2017-08-22
 * Time: 14:34
 */
namespace Pool\Controller;
use Think\Verify;
use Think\Page;

/**
 * 用户中心首页控制器
 * Class IndexController
 * @package Pool\Controller
 */

class IndexController extends PoolController
{

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * 首页
     */
    public function index()
    {
        $module = strtolower(trim(__MODULE__, '/'));
        $module = trim($module, './');
        $loginout = U($module . "/Login/loginout");
        $this->assign('loginout', $loginout);
        $this->display();
    }

    public function main()
    {
        $provider = $this->provider;
        $where['pid'] = $provider['uid'];
        $lastlogin = M('PoolLoginrecord')->where($where)->order('id DESC')->find();
        $stat['today_count'] = M('PoolPhones')->where($where)->count();
        $stat['today_pay_success'] = M('PoolRec')->where($where)->count();
        $where['lock'] = 1;
        $stat['today_lock'] = M('PoolPhones')->where(['lock'=>1])->count();

        
        $this->assign('lastlogin', $lastlogin);
        $this->assign('provider', $provider);
        $this->assign('stat', $stat);
        $this->display();
    }

}