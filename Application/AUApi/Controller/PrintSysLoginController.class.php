<?php
/**
 * 打印系统登录模块 前台登陆、用户注册、物流信息查询、会员个人中心  服务器端
 */
namespace AUApi\Controller;
use Think\Controller\HproseController;
class PrintSysLoginController extends HproseController{

    /**
     * 登陆验证
     * @param  [type] $name [用户输入的账号名]
     * @param  [type] $pwd  [用户输入的密码]
     * @param  [type] $type [账号登录类型：用户名，邮箱]
     */
    public function _loginning($name, $pwd, $type='username'){
        
        $map[$type] = array('eq',$name);

        $user = M('UserList')->where($map)->find();

        if(!$user){
            return array('state' => 'no', 'code' => 'login_01','msg'=>'账户不存在', 'lng'=>'user_not_exist');
        }

        if($user['pwd'] != $pwd){
            return array('state' => 'no', 'code' => 'login_05', 'msg' => '密码错误', 'lng'=>'pwd_is_wrong');
        }

        if($user['step'] < 5){
            return array('state' => 'no', 'code' => 'login_06', 'msg' => '您的账户资料尚未完善', 'lng'=>'info_not_perfect');
        }

        if($user['status'] == 0){
            return array('state' => 'no', 'code' => 'login_07', 'msg' => '账户未审核通过', 'lng'=>'must_examine');

        }else if($user['status'] > 1){

            return array('state' => 'no', 'code' => 'login_08', 'msg' => '您的账户资料审核不通过，请登录官网完善资料再次审核', 'lng'=>'not_examine_need_perfect');
        }

        return array('user'=>$user);
    }

    /**
     * 保存token相关信息
     * @param  [type] $data_a   [description]
     * @param  [type] $data_u   [description]
     * @param  [type] $user     [description]
     * @param  string $time_out [超时时间，默认10分钟]
     * @return [type]           [description]
     */
    public function check_print_user($data_a, $data_u, $user, $time_out='600'){
        $uesr_app_model = M('AppUserPrint');
        // 查询是否存在
        $user_app = $uesr_app_model->where($data_a)->find();

        if($user_app){

            // 暂时取消登录限制  20170926
            // //如果token已经存在，且尚未过期，则不能登录
            // if((time()-intval($user_app['time_out'])) <= intval($time_out)){
            //     return 'already_logined';
            // }else{
                $idc = $uesr_app_model->where($data_a)->data($data_u)->save();
            // }

        }else{
            $data_u['user_id']      = $user['id'];
            $idc = $uesr_app_model->data($data_u)->add();
        }

        return $idc;
    }

    //账户退出
    public function _login_out($token){
        $del = M('AppUserPrint')->where(array('token'=>$token))->delete();
        if($del !== false){
            return array('state' => 'yes', 'msg' => '退出成功', 'lng'=>'logout_success');
        }else{
            return array('state' => 'no', 'msg' => '退出失败', 'lng'=>'logout_failed');
        }
    }

    //检查登录状态
    public function _is_login($token){
        $find = M('AppUserPrint')->where(array('token'=>$token))->find();
        return $find;
    }

    //刷新超时时间
    public function hold_login($token){
        M('AppUserPrint')->where(array('token'=>$token))->setField('time_out',time());
    }
}