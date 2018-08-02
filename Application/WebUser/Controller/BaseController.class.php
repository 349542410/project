<?php
/**
 * 验证是否已经登陆和资料完善度
 */
namespace WebUser\Controller;
use Think\Controller;
class BaseController extends Controller {
    /**
     * 验证是否合法登录
     * @return [type] [description]
     */
    public function _initialize(){

        /*根据session是否有值验证登陆*/

        $mkuser = session('mkuser');

        if(in_array(CONTROLLER_NAME, array(
            'Member','Order','WebRecharge','Topup','Addressee','Sender','OrderSource','AmountOpt','Announcement',
        ))){
            //检查用户是否登录，登录通行证验证
            if(!$mkuser || $mkuser['isLoged'] != md5(md5('passed'))){
                $this->redirect(U('Login/index'));
                // redirect(U('Login/index'));
                die;
            }
        }
        /* end*/

        /* 资料完善验证 */
        vendor('Hprose.HproseHttpClient');
        $client = new \HproseHttpClient(C('RAPIURL').'/Server');
        $map['id'] = array('eq',$mkuser['uid']);

        $userlist = $client->userlist($map);
        $this->user_type = $userlist['type'];

        //step步骤少于5的都是表示尚未完善注册步骤
        if($userlist['step'] < 5){
            $reg_info = array(
                'id'          => $mkuser['uid'],
                'type'        => $userlist['type'],//注册类型
                'countryId'   => $userlist['countryId'],//注册类型
                'FirstName'   => ($userlist['type'] == 'person') ? $userlist['FirstName'] : '',
                'LastName'    => ($userlist['type'] == 'person') ? $userlist['LastName'] : '',
                'companyname' => ($userlist['type'] == 'company') ? $userlist['CompanyName'] : '',
            );
            session('reg_info',$reg_info);
        }

        $this->chaos = '';//默认为空白，用于邮箱验证步骤
        $this->surl = '';//默认为空白，用于放置一个跳转的网址

        //step属于1-4范围内的都是表示尚未完善注册步骤
        switch ($userlist['step']) {
            
            //等于0，那些账户有可能是旧版系统注册的，所以重新从邮箱验证开始补充资料
            case '0':
                $this->surl = U('Register/ensure');
                $this->chaos = U('Register/supple_eamil');
                break;

            //如果邮箱未认证
            case '1':
                $this->surl = U('Register/ensure');
                $this->chaos = U('Register/supple_eamil');
                break;

            //完善个人资料
            case '2':
                if($userlist['type'] == 'person'){
                    $this->surl = U('Register/CertStep1ForPerson');
                }else{
                    $this->surl = U('Register/CertStep1ForCompany');
                }
                break;

            //上传证件照片
            case '3':
                if($userlist['type'] == 'person'){
                    $this->surl = U('Register/CertUploadForPerson');
                }else{
                    $this->surl = U('Register/CertUploadForCompany');
                }
                break;

            //签署授权
            case '4':
                if($userlist['type'] == 'person'){
                    $this->surl = U('Register/CertificationAuthorizePerson');
                }else{
                    $this->surl = U('Register/CertificationAuthorizeCompany');
                }
                break;

            default:
                # code...
                break;
        }
        /* 资料完善验证 End */

        // session存储user_id
        // 2017-10-12
        // liao
        session('user_id',$mkuser['uid']);

        //检验会员的状态是否通过审核，不通过的不可进行充值或下单
        if(in_array(CONTROLLER_NAME, array('Order','WebRecharge','OrderSource','Addressee','Sender'))){
            // 会员下单之前先检查会员是否已经通过审核，只有审核通过的会员才能下单 Jie 20151130
            $user_auth = $userlist['status'];//$client->authInfo($mkuser);

            //审核状态
            if($user_auth != '1'){
                session('duns','not_examine');//未审核状态的标识
                $this->redirect('Member/index');exit;
            }
        }

        /* end */
        $this->assign('mkuser',$mkuser['username']);        //加载登入的用户名到页面头部显示
        $this->assign('user_type',$userlist['type']);       //20160401 Jie 添加用户登录(个人，企业)类型
        $this->assign('user_status',$userlist['status']);   //用户审核状态
        $this->assign('user_step',$userlist['step']);       //用户已注册到达的步骤

        //资金
        $amount = $client->amount($map);
        
        if($amount == ''){
            $this->assign('amount','0');
        }else{
            $this->assign('amount',$amount);
        }

        //获取可用可选的中转线路
        $tranline = $client->getline();
        
        //获取所有的中转线路
        $all_line = $client->all_line();

        $this->tranline = $tranline;
        $this->all_line = $all_line;
    }

    //清除 未审核状态的标识，避免Member/index不断弹出提示
    public function setNull(){
        session('duns',null);
    }
}