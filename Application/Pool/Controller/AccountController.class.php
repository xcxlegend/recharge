<?php
/**
 * Created by PhpStorm.
 * User: gaoxi
 * Date: 2017-08-22
 * Time: 14:34
 */
namespace Pool\Controller;

use Intervention\Image\ImageManagerStatic;
use Think\Page;
use Think\Upload;

/**
 *  商家账号相关控制器
 * Class AccountController
 * @package Pool\Controller
 */

class AccountController extends PoolController
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * 编辑个人资料
     */
    public function profile()
    {
        if (IS_POST) {
            $data = I('post.');
            $res = M('PoolProvider')->where(['id' => $this->provider['uid']])->save($data);
            if($res) {
                $this->success('编辑成功');
            } else {
                $this->error('编辑失败');
            }
        }else{
            $item = M("PoolProvider")->where(['id' => $this->provider['uid']])->find();
            $this->assign("item", $item);
            $this->display();
        }
        
    }

    public function loginlog()
    {
        $maps['pid'] = $this->provider['uid'];
        //$maps['type']   = 0;
        $count          = M('PoolLoginrecord')->where($maps)->count();

        $size  = 15;
        $rows  = I('get.rows', $size, 'intval');
        if (!$rows) {
            $rows = $size;
        }
        $page           = new Page($count, $rows);
        $list           = M('PoolLoginrecord')
            ->where($maps)
            ->limit($page->firstRow . ',' . $page->listRows)
            ->order('id desc')
            ->select();
        $this->assign("list", $list);
        $this->assign('page', $page->show());
        $this->display();
    }

    public function addLog()
    {
        $maps['pid'] = $this->provider['uid'];
        //$maps['type']   = 0;
        $count          = M('PoolMoneychange')->where($maps)->count();

        $size  = 15;
        $rows  = I('get.rows', $size, 'intval');
        if (!$rows) {
            $rows = $size;
        }
        $page           = new Page($count, $rows);
        $list           = M('PoolMoneychange')
            ->where($maps)
            ->limit($page->firstRow . ',' . $page->listRows)
            ->order('id desc')
            ->select();
        $this->assign("list", $list);
        $this->assign('page', $page->show());
        $this->display();
    }


    public function returnLog()
    {
        $maps['a.pid'] = $this->provider['uid'];
        $maps['status']   = 2;
        $join = 'LEFT JOIN pay_pool_drawback b ON a.id=b.rec_id';
        $field = 'a.*,b.time,b.reason';
        

        $count = M('PoolRec')->alias('a')->join($join)->where($maps)->count();

        $size  = 15;
        $rows  = I('get.rows', $size, 'intval');
        if (!$rows) {
            $rows = $size;
        }
        $page           = new Page($count, $rows);
        $list           = M('PoolRec')
            ->alias('a')
            ->field($field)
            ->join($join)
            ->where($maps)
            ->limit($page->firstRow . ',' . $page->listRows)
            ->order('id desc')
            ->select();
        $this->assign("list", $list);
        $this->assign('page', $page->show());
        $this->display();
    }





    /**
     * 修改密码
     */
    public function password()
    {
        $data = M("PoolProvider")->where(['id' => $this->provider['uid']])->find();
        $this->assign('p', $data);
        //查询是否开启短信验证
        $sms_is_open = smsStatus();

        if (IS_POST) {
            //验证验证码
            $code = I('request.code');
            if ($sms_is_open) {
                $res = check_auth_error($this->provider['uid'], 2);
                if(!$res['status']) {
                    $this->ajaxReturn(['status' => 0, 'msg' => $res['msg']]);
                }
                if (session('send.editPassword') == $code && $this->checkSessionTime('editPassword', $code)) {
                    clear_auth_error($this->provider['uid'],2);
                    session('send', null);
                } else {
                    log_auth_error($this->provider['uid'],2);
                    $this->ajaxReturn(['status' => 0, 'msg' => '验证码错误']);
                }
            }


            $p    = I('post.p');
            if (!$p['oldpwd'] || !$p['newpwd'] || !$p['secondpwd'] || $p['newpwd'] != $p['secondpwd'] ) {
                $this->ajaxReturn(['status' => 0, 'msg' => '输入错误']);
            }
            $res = M("PoolProvider")->where(['id' => $this->provider['uid']])->save(['password' => md5($p['newpwd'])]);
            if($res !== false) {
                $this->ajaxReturn(['status' => 1, 'msg' => '修改密码成功']);
            } else {
                $this->ajaxReturn(['status' => 0, 'msg' => '修改密码失败']);
            }
        } else {
            if ($sms_is_open) {
                //
            }
            $this->assign('sms_is_open', $sms_is_open);
            $this->display();
        }
    }




}
