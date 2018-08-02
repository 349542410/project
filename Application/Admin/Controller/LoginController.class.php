<?php
/**
 * 后台登录 客户端
 */
namespace Admin\Controller;
use Think\Controller;
class LoginController extends Controller {

	public function index(){

	    $admin = session('admin');

        if($admin){
        	$this->redirect('/Index/index');
        }
		$this->display();
	}

	/**
	 * 登入
	 */
	public function Login_in(){
		if(IS_POST){
			$name   = trim(I('post.name'));
			$pwd    = trim(I('post.pwd'));
			$verify = trim(I('post.verify'));

			if(!$name){
				$result = array('state' => 'no', 'msg' => '请输入用户名！');
				$this->ajaxReturn($result);
			}
			if(!$pwd){
				$result = array('state' => 'no', 'msg' => '请输入账号密码！');
				$this->ajaxReturn($result);
			}
			if(!$verify){
				$result = array('state' => 'no', 'msg' => '请输入验证码！');
				$this->ajaxReturn($result);
			}

			$check_verify = new \Think\Verify();

            if(!$check_verify->check($verify)){
                $result = array('state' => 'no', 'msg' => '验证码错误');
                $this->ajaxReturn($result);
            }
            $map['name'] = array('eq',$name);
            vendor('Hprose.HproseHttpClient');
            $client = new \HproseHttpClient(C('RAPIURL')."/AdLogin");
            $admin  = $client->is_login($map,$pwd);

	        if($admin['state'] == 'no'){
	        	$result = array('state' => 'no', 'msg' => $admin['msg']);
	        	$this->ajaxReturn($result);

	        }else if($admin['state'] == 'yes'){

		        $author = array(
		        	'adid' 			=> $admin['res']['id'],
		        	'adname' 		=> $admin['res']['name'],
		        	'adtname' 		=> $admin['res']['tname'],
		        	'ademail' 		=> $admin['res']['email'],
		        	'auth_group_id' => $admin['res']['auth_group'],
		        
		        );
				
		        session('admin',$author);
				
		        //超级管理员
		        $author['auth'] = C('ADMINISTRATOR');
		        //允许禁用访问  
		        $author['apauth'] = C('ALLOW_PROHIBIT_AUTH');
		        
		        //print_r(array_filter($author['auth']));
		        //exit;
		        
		        /* 获取某个管理员所拥有的权限 20170628 jie */
		        $group = $client->power($author);
		        
		        if(!empty($group['errorstr'])){
		        	$result = array('state' => 'no', 'msg'=>$group['errorstr']);
		        	$this->ajaxReturn($result);
		        	exit;
		        }
		        //print_r($group);
		        //exit;
				$group = array_reverse($group);
				$url = $group[0]['name'];
		        // 验证账号所属的界面浏览权限
//		        $pwr = C('pwr');
//		        $arr = array();
//		        foreach($pwr as $key=>$pow){
//		            if($pow[0] == 'g0'){
//		                $arr[$key] = $pow;
//		            }
//		        }
//
//		        $rule = '';
//		        foreach($arr as $item){
//		            if($group['power'][$item[1]] == 'on'){
//		                $rule = $item[3];
//		                //如果界面权限第一个是 商品报备，则继续获取下一个界面权限；商品报备 页面不作为首选显示
//		                if($rule == 'CustomsReport'){
//		                	continue;
//		                }else{
//		                	break;
//		                }
//
//		            }
//		        }
		        // 验证账号所属的界面浏览权限 End
				if($url){
					$result = array('state' => 'yes', 'url' => $url, 'msg'=>'验证成功');
	        	}else{
	        		$result = array('state' => 'no', 'msg'=>'没有操作权限，请与管理员联系');
	        	}
	        	$this->ajaxReturn($result);

	        }else{
	        	$result = array('state' => 'no', 'msg' => "系统错误，请与管理员联系");
	        	$this->ajaxReturn($result);
	        }


		}else{
			$this->error('页面不存在');
		}
	}

	/**
	 * 登出
	 */
	public function Login_out(){

		session('admin',null); // 销毁session
		session('group',null); // 销毁session
		session('power',null); // 销毁session
		$this->redirect('Login/index');
	}

	/**
	 * 验证码生成
	 */
    public function verify_c(){
        ob_end_clean();
		$Verify             = new \Think\Verify();
		$Verify->fontSize   = 100;
		$Verify->length     = 4;
		$Verify->useNoise   = false;
		//$Verify->useCurve = false;
		$Verify->codeSet    = '02345689';
		$Verify->imageW     = 0;
		$Verify->imageH     = 0;
		//$Verify->expire   = 600;
		$Verify->entry();
    }
}