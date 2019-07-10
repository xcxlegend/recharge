<?php
namespace Pool\Controller;

class PoolController extends BaseController
{
    public $provider;
    public function __construct()
    {
        parent::__construct();
        //验证登录
        $user_auth = session("user_auth");
        ksort($user_auth); //排序
        $code = http_build_query($user_auth); //url编码并生成query字符串
        $sign = sha1($code);
        if($sign != session('user_auth_sign') || !$user_auth['uid']){
            $module = strtolower(trim(__MODULE__, '/'));
            $module = trim($module, './');
            header("Location: ".U($module.'/Login/index'));
        }
        //用户信息
        $this->provider = $user_auth;
		
        $this->assign('provider',$this->provider);
    }
}
?>
