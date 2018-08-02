<?php
/* 不上传此文件  无关重要  */
namespace WebUser\Controller;
use Think\Controller;
use Think\Log;

class IndexController extends Controller {
	
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
		$this->display('index');
	}

	/**
	 * 登入 方法
	 */
	public function Login_in(){

		if(IS_POST){
			$name = trim(I('post.name'));
			$pwd  = strtolower(trim(I('post.pwd')));

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

	        $map['username'] = array('eq',$name);

	        vendor('Hprose.HproseHttpClient');
	        $client = new \HproseHttpClient(C('RAPIURL').'/Server');
	        $user = $client->is_login($map,$pwd);

	        if($user['do'] == 'no'){

				$result = array('state' => 'no', 'msg' => $user['msg']);
				$this->ajaxReturn($result);

	        	// $this->error($user['msg']);

	        }else if($user['do'] == 'yes'){

		        $author = array(
					'uid'      => $user['res']['id'],			//登入的id值
					'username' => $user['res']['username'],		//登入的用户名
					'isLoged'  => md5(md5('passed')),
		        );
		        
		        session('mkuser',$author);						//session赋值
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
		session('mkuser', null); // 清空session
		$this->redirect('Index/index');
	}


	/**
	 * 验证码生成
	 */
    public function verify_c(){  
	   
        verify_c(); 
    }

}