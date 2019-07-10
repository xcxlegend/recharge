<?php
namespace Pool\Controller;

class PoolController extends BaseController
{
    public $provider;
    public function __construct()
    {
        parent::__construct();
        //验证登录
        $pool_auth = session("pool_auth");
        ksort($pool_auth); //排序
        $code = http_build_query($pool_auth); //url编码并生成query字符串
        $sign = sha1($code);
        if($sign != session('pool_auth_sign') || !$pool_auth['uid']){
            $module = strtolower(trim(__MODULE__, '/'));
            $module = trim($module, './');
            header("Location: ".U($module.'/Login/index'));
        }
        //用户信息
        $this->provider = $pool_auth;
		
        $this->assign('provider',$this->provider);
    }
}
?>
