<?php
namespace WebUser\Controller;
use Think\Controller;
use Think\Log;

class LoginController extends Controller {
	
	/**
	 * 登陆视图
	 */
	public function index(){
        /*根据session是否有值，验证登陆*/

        $mkuser = session('mkuser');

        if($mkuser && $mkuser['isLoged'] == md5(md5('passed'))){
        	$this->redirect('Member/index');
            // $this->error('您已经登陆了',U('Index/index'));
            exit;
        }
        /* end*/
		$this->display();
	}

	/**
	 * 登入 方法
	 */
	public function Login_in(){
		if(IS_POST){
			// $name = trim(I('post.name'));
			// $pwd  = strtolower(trim(I('post.pwd')));
			$name = I('post.name');
			$pwd  = (I('post.pwd'));

			$verify = trim(I('post.verify'));

			if($name == ''){
				$result = array('state' => 'no', 'msg' => L('Rd_Please_name'));
				$this->ajaxReturn($result);
			}
			if($pwd == ''){
				$result = array('state' => 'no', 'msg' => L('Lr_Please_password'));
				$this->ajaxReturn($result);
			}
			if($verify == ''){
				$result = array('state' => 'no', 'msg' => L('Please_code_re'));
				$this->ajaxReturn($result);
			}
	        if(!check_verify($verify)){
				$result = array('state' => 'no', 'msg' => L('V_code_error_re'));
				$this->ajaxReturn($result);
	            // $this->error("亲，验证码输错了哦！");
	        }

	        //判断账号格式是邮箱或者账号名
	        if(!get_login_way($name)){
	        	$type = 'username';
	        }else{
	        	$type = 'email';
	        }

	        vendor('Hprose.HproseHttpClient');
	        $client = new \HproseHttpClient(C('RAPIURL').'/Server');
	        $user = $client->is_login($name, $pwd, $type);

	        if($user['do'] == 'no'){

	        	switch ($user['code']) {
	        		case 'login_01':
	        			$result = array('state' => 'no', 'msg' => L('user_not_exit'));
	        			break;
	        		case 'login_02':
	        			$result = array('state' => 'no', 'msg' => sprintf(L('locked_user'),$user['time']));
	        			break;
	        		case 'login_03':
	        			$result = array('state' => 'no', 'msg' => sprintf(L('wrong_pwd_at_num_limit'), $user['num'],$user['num_total']));
	        			break;
	        		case 'login_04':
	        			$result = array('state' => 'no', 'msg' => sprintf(L('wrong_pwd_at_last_limit'),$user['num_total']));
	        			break;
	        		case 'login_05':
	        			$result = array('state' => 'no', 'msg' => L('wrong_pwd'));
	        			break;
	        		default:
	        			# code...
	        			break;
	        	}

				// $result = array('state' => 'no', 'msg' => $user['msg']);
				$this->ajaxReturn($result);

	        }else if($user['do'] == 'yes'){

		        $author = array(
					'uid'      => $user['res']['id'],			//登入的id值
					'username' => $user['res']['username'],		//登入的用户名
					'isLoged'  => md5(md5('passed')),
		        );
		        
		        session('mkuser',$author);						//session赋值
                Log::write('session'.json_encode(session('mkuser')));
		        // $this->success($user['msg'],U('Index/index'));
		        // $this->redirect('Index/index');
		        $result = array('state' => 'yes', 'url' => $_SERVER["HTTP_HOST"]."/index.php?m=Home&c=Index&a=index", 'msg'=>L('Validation_is_su_rc'));
		        $this->ajaxReturn($result);

	        }else{
				$result = array('state' => 'no', 'msg' => L('Lr_longin_failed'));
				$this->ajaxReturn($result);
	        	// $this->error('登陆失败');
	        }

		}else{
			$this->display();
		}
	}

	/**
	 * 登出 方法
	 */
	public function Login_out(){

		session('mkuser',null); // 清空session
		$this->redirect('Index/index');
	
	}


	/**
	 * 验证码生成
	 */
    public function verify_c(){
        verify_c(); 
    }

}
