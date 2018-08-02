<?php
/**
 * 美快云存储空间登录验证  服务器端  Jie 2015-10-13 暂时停用
 */
namespace Api\Controller;
use Think\Controller\HproseController;
class PicLoginController extends HproseController{    
    protected $crossDomain =    true;
    protected $P3P         =    true;
    protected $get         =    true;
    protected $debug       =    true;

    /**
     * 登陆方法
     * @param  [type] $map [查找条件]
     */
    public function is_login($map,$pwd){

        // $res = M('UserList')->where($map)->find();
        $res = M()->table('MIS_User')->where($map)->find();

        if(!$res || $res['IsUsed'] == '0'){
            $puser = array('state' => 'no', 'msg' => '账户不存在或被禁用');

            return $puser;
        }

        if($res['Password'] != md5($pwd)){
            $puser = array('state' => 'no', 'msg' => '密码错误');

            return $puser;
        }

        $puser = array('state' => 'yes', 'msg' => '登陆成功', 'res' => $res);

        return $puser;
    }


}