<?php
/**
 * 验证是否已经登陆和资料完善度
 */
namespace Web\Controller;
use Think\Controller;
class BaseController extends Controller {
    /**
     * 验证是否合法登录
     * @return [type] [description]
     */
    public function _initialize(){
        /*根据session是否有值验证登陆*/

        $mkuser = session('mkuser');

        if(CONTROLLER_NAME == 'Member' || CONTROLLER_NAME == 'Order'){
            if(!$mkuser || $mkuser['isLoged'] != md5(md5('passed'))){
                $this->redirect('Login/index');
            }
        }
        /* end*/

        /* 资料完善验证 */
        vendor('Hprose.HproseHttpClient');
        $client = new \HproseHttpClient(C('RAPIURL').'/Server');
        $map['id'] = array('eq',$mkuser['uid']);

        $userlist = $client->userlist($map);
        $this->user_type = $userlist['type'];
        // dump($userlist);

        $map2['user_id'] = array('eq',$mkuser['uid']);
        // $userinfo = $client->userinfo($userlist,$map2);

        if($userlist['type'] == 'person'){

            $userinfo = $client->userinfo($userlist,$map2);
            
            //如果基础资料不完善
            if($userinfo['status'] == '2' || $userinfo['status'] == '3'){

                //如果基础资料不完善的情况下，图片也没有上传
                if($userinfo['photo1'] == '' || $userinfo['photo3'] == ''){
                    $reg_info = array(
                        'id'          =>$mkuser['uid'],
                        'FirstName'   =>$userlist['FirstName'],
                        'LastName'    =>$userlist['LastName'],
                        'switch_type' => 'all_info',    //代表需要完善所有资料
                    );
                    session('reg_info',$reg_info); 

                    $this->surl = U('Supple/per_base');
                    // $this->error('请完善个人资料',U('Supple/per_base')); 
                }else{
                    $reg_info = array(
                        'id'          =>$mkuser['uid'],
                        'FirstName'   =>$userlist['FirstName'],
                        'LastName'    =>$userlist['LastName'],
                        'switch_type' => 'base_info',    //代表只需要完善基础资料
                    );
                    session('reg_info',$reg_info); 

                    $this->surl = U('Supple/per_base');
                    // $this->error('请完善个人资料',U('Supple/per_base')); 
                }

            }
            if($userinfo['status'] == '4' || $userinfo['status'] == '5'){

                $reg_info = array(
                    'id'          =>$mkuser['uid'],
                    'switch_type' => 'pic_info',    //代表只需要完善图片资料
                );
                session('reg_info',$reg_info); 

                $this->surl = U('Supple/perpic_view');
                // $this->error('请上传您的个人认证文件',U('Supple/perpic_view'));
            }

        }else if($userlist['type'] == 'company'){

            $userinfo = $client->userinfo($userlist,$map2);

            //如果基础资料不完善
            if($userinfo['status'] == '6' || $userinfo['status'] == '7'){

                //如果基础资料不完善的情况下，图片也没有上传
                if($userinfo['photo1'] == ''){
                    $reg_info = array(
                        'id'          =>$mkuser['uid'],
                        'companyname' =>$userlist['CompanyName'],
                        'switch_type' => 'all_info',    //代表需要完善所有资料
                    );
                    session('reg_info',$reg_info); 

                    $this->surl = U('Supple/com_base');
                    // $this->error('请完善企业资料',U('Supple/com_base')); 
                }else{
                    $reg_info = array(
                        'id'          =>$mkuser['uid'],
                        'companyname' =>$userlist['CompanyName'],
                        'switch_type' => 'base_info',    //代表只需要完善基础资料
                    );
                    session('reg_info',$reg_info); 

                    $this->surl = U('Supple/com_base');
                    // $this->error('请完善企业资料',U('Supple/com_base')); 
                }

            }
            if($userinfo['status'] == '8'){

                $reg_info = array(
                    'id'          => $mkuser['uid'],
                    'switch_type' => 'pic_info',    //代表只需要完善图片资料
                );
                session('reg_info',$reg_info); 
                
                $this->surl = U('Supple/compic_view');
                // $this->error('请上传您的营业执照',U('Supple/compic_view'));
            }   
        }

        /* end */
        $this->assign('mkuser',$mkuser['username']);    //加载登入的用户名到页面头部显示
        $this->assign('user_type',$userlist['type']);       //20160401 Jie 添加载入用户登录类型

        //资金
        $amount = $client->amount($map);
        
        if($amount == ''){
            $this->assign('amount','0');
        }else{
            $this->assign('amount',$amount);
        }
        
    }
}