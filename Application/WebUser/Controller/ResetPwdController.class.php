<?php
/**
 * 邮箱找回密码  密码重置
 */
namespace WebUser\Controller;
use Think\Controller;
class ResetPwdController extends Controller{

    function _initialize(){
        vendor('Hprose.HproseHttpClient');
        $client = new \HproseHttpClient(C('WAPIURL').'/Reset');      //读取、查询操作
        $this->client = $client;
    }

    /**
     * 忘记密码 视图
     * @return [type] [description]
     */
    public function index(){

        if(!isset($this->mkuser)){
            $this->mkuser = '';
        }
        $this->display();
    }

    /**
     * 发送电子邮件重置密码
     * @return [type] [description]
     */
    public function forgetPwd(){

        if(!IS_POST){
            die(L('LAY_MesPar'));
        }

        $receiver = trim(I('post.username'));   //用户名
        $address  = trim(I('post.email'));      //邮箱
        $verify   = trim(I('post.verify'));     //验证码
        if($receiver == ''){
            $result = array('status' => '0', 'msg' => L('Rd_Please_name'));
            $this->ajaxReturn($result);
        }
        if($address == ''){
            $result = array('status' => '0', 'msg' => L('Please_e_re'));
            $this->ajaxReturn($result);
        }

        $eamil_match="/^([0-9A-Za-z\\-_\\.]+)@([0-9a-z]+\\.[a-z]{2,3}(\\.[a-z]{2})?)$/i";
        if(preg_match($eamil_match,$address) != true){
            $result = array('status' => '0', 'msg' => L('Email_ree_re'));
            $this->ajaxReturn($result);
        }

        if(!check_verify($verify)){
            $result = array('status' => '0', 'msg' => L('Rd_Veri_error'));
            $this->ajaxReturn($result);
        }

        $uuid = $this->cuid();     //生成标识码

        $res = $this->client->getInfo($receiver,$address,$uuid);        //全局变量

        $info = $res['info'];

        $sendUrl = "http://".$_SERVER['HTTP_HOST'].U('ResetPwd/reFindPwd',array('code'=>md5($info['reg_time'].$info['username'].$info['pwd']),'uucode'=>base64_encode($uuid)));

        if($res['status'] == '1'){

            //找回密码报文  中英文互译
            $content = get_pwd_back_content($receiver,$sendUrl);

            $this->send_email($address,$content,$receiver);

        }else{
            if($res['code'] == 'f001'){
                $msg = L('username_not_exist');//用户名或邮箱不存在
            }
            $result = array('status' => '0', 'msg' => $msg);
            $this->ajaxReturn($result);
        }

    }

    /**
     * 设置新密码 视图
     * @return [type] [description]
     */
    public function reFindPwd(){
        $code   = trim(I('get.code'));
        $uucode = trim(I('get.uucode'));
        $uuid = base64_decode($uucode);

        $res = $this->client->vInfo($uuid);
        
        //通过uuid核对数据不存在的时候
        if($res['status'] == '0'){
            die(L('link_expired'));//链接已过期
        }

        $info = $res['info'];
        $mt = md5($info['reg_time'].$info['username'].$info['pwd']);
        $gtime = intval(strtotime($res['time']));  //发送修改密码的邮件的时间
        $nowtime = intval(time());//现在的时间

        $EMAIL_SET = C('EMAIL_SET');
        $max = 60*intval($EMAIL_SET['MAXTIME']);        //获取时限参数值

        //验证规则
        if($mt == $code){
            if(($nowtime - $gtime) > intval($max)){
                die(L('link_expired'));//链接已过期
            }else{
                $this->display();//显示重置密码的页面
            }
        }else{
            die(L('link_invalid'));
        }

    }

    /**
     * 设置新密码 方法
     * @return [type] [description]
     */
    public function newPwd(){
        if(!IS_POST){
            die(L('ErrorParameter'));
        }

        // $pwd    = strtolower(trim(I('post.pwd')));
        $pwd    = (trim(I('post.pwd')));
        $repwd  = trim(I('post.repwd'));
        $verify = trim(I('post.verify'));
        $code   = trim(I('post.code'));
        $uucode = trim(I('post.uucode'));

        // dump($_POST);die;

        if(!check_verify($verify)){
            $result = array('status' => '0', 'msg' => L('Rd_Veri_error'));
            $this->ajaxReturn($result);
        }

        if(strlen($pwd) < 6 || strlen($repwd) <6){
            $result = array('status' => '0', 'msg' => L('Password_le_re'));
            $this->ajaxReturn($result);
        }

        if($pwd != $repwd){
            $result = array('status' => '0', 'msg' => L('You_two_re'));
            $this->ajaxReturn($result);
        }

        $maxtime = C('EMAIL_SET.MAXTIME');

        $result = $this->client->setPwd($code,$uucode,md5($pwd),$maxtime);

        if($result['status'] == '1'){
            $result['msg'] = L('pwd_reset_success');//密码修改成功
        }else{
            switch ($result['code']) {
                case 's400':
                    $result['msg'] = L('pwd_reset_fail');//密码修改失败
                    break;
                case 's001':
                    $result['msg'] = L('old_eq_new');//新旧密码不能相同
                    break;
                case 's002':
                    $result['msg'] = L('link_expired_to_operate');//链接已超时，请重新申请再操作
                    break;
                case 's003':
                    $result['msg'] = L('user_not_exit');//账户不存在
                    break;
                case 's004':
                    $result['msg'] = L('link_invalid');//链接无效
                    break;
                default:
                    # code...
                    break;
            }
        }
        $this->ajaxReturn($result);

    }

    /**
     * 邮件发送  请注意发送邮件的邮箱是否开启了SMTP功能，未开启则无法使用第三方发送功能
     * @param  [type] $receiver [收件人]
     * @param  [type] $content  [发送的内容]
     * @param  [type] $address  [收件邮箱]
     */
    public function send_email($address,$content,$receiver){

        $EMAIL_SET = C('EMAIL_SET');
        $args = array(
            'to' => array(
                $address,
            ),
            'title' => L($EMAIL_SET['TITLE']),
            'content' => $content,
            'Subject' => '[' . L('re_Meikuai') . ']' . L('re_Email_v'),
            'type' => 'html',
            'FromName' => L($EMAIL_SET['FROMNAME']),
        );
        
        $phpmail = new \Lib11\PHPMailer\PHPMailerTools();
        $res = $phpmail->sendMail($args);

        if(!$res['success']) {
            $result = array('status'=>'0','msg'=>"Mailer Error: " . $res['info']);
            $this->ajaxReturn($result);
            exit;

        } else {
            $result = array('status'=>'1','msg'=>"",'email'=>$address);   //成功
            $this->ajaxReturn($result);
            exit;
        }


    }

    /**
     * 标识码生成方法
     * @return [type] [description]
     */
    public function cuid(){
        if (function_exists('com_create_guid')){
            return com_create_guid();
        }else{
            mt_srand((double)microtime()*10000);//optional for php 4.2.0 and up.
            $charid = strtoupper(md5(uniqid(rand(), true)));
            $hyphen = chr(45);// "-"
            $uuid = //chr(123)// "{"
                    substr($charid, 0, 8).$hyphen
                    .substr($charid, 8, 4).$hyphen
                    .substr($charid,12, 4).$hyphen
                    .substr($charid,16, 4).$hyphen
                    .substr($charid,20,12);
                    //.chr(125);// "}"
            return $uuid;
        }
    }

    /**
     * 验证码生成
     */
    public function verify_c(){
        verify_c();
    }

}