<?php
/**
 * 自助打印终端---扫码登录(扫描其他二维码的接入口，扫描微信二维码之后的转接口)
 * 功能：
 * 创建时间：2017-08-14
 * 创建人：jie
 */
namespace MkAuto\Controller;
use Think\Controller;
class PublicloginController extends Controller{

	// 用户端 登录界面
	public function login(){
//         $no = I('no');//() ? trim(I('info')) : '';
//         // //验证是否 info 为空
//         // if($json == ''){
//         //     $result = array('state'=>'no','msg'=>'缺少必要的相关资料', 'lng'=>'miss_parameter');
//         //     echo json_encode($result);exit;
//         // }
// // no
// // dump($info);die;

		$no  = trim(I('get.no'));
		$wx  = (I('get.wx')) ? trim(I('get.wx')) : '';
		$lng = (I('get.lng')) ? trim(I('get.lng')) : 'zh-cn';

		if($no == ''){
			$backArr = array('state'=>'no', 'lng'=>'lack_paramer');

			$backArr['msg'] = ($lng == 'zh-cn') ? '缺少必要参数' : 'Required parameter missing';
			echo json_encode($backArr);exit;
		}

		vendor('Hprose.HproseHttpClient');
		$client = new \HproseHttpClient(C('RAPIURL').'/Publiclogin');

		$res = $client->_login($no, $wx);

		//无登录记录，且成功生成准备登录的记录
		if($res['state'] == 'enter_username_and_pwd'){

			$this->assign('type', $res['state']);
			$this->assign('lng', $lng);
			$this->assign('no', authcode($no, 'ENCODE', C('Print_Sys_Set.private_key'), 0));//加密
			$this->assign('wx', authcode($wx, 'ENCODE', C('Print_Sys_Set.private_key'), 0));//加密
			$this->display();
		}else if($res['state'] == 'at_once_login'){//点击确认键直接登录
			$username = $res['data']['username'];
			$pwd      = $res['data']['pwd'];

			$this->assign('type', $res['state']);
			$this->assign('lng', $lng);
			$this->assign('username', authcode($username, 'ENCODE', C('Print_Sys_Set.private_key'), 0));//加密
			$this->assign('pwd', authcode($pwd, 'ENCODE', C('Print_Sys_Set.private_key'), 0));//加密

			$this->assign('no', authcode($no, 'ENCODE', C('Print_Sys_Set.private_key'), 0));//加密
			$this->assign('wx', authcode($wx, 'ENCODE', C('Print_Sys_Set.private_key'), 0));//加密
			$this->display('confirm');
		}else{
			($lng == 'zh-cn') ? die('参数错误') : die('parameter error');
		}
	}

	//用户输入账户，密码之后的数据处理
	public function save(){

		$type     = trim(I('post.type')); //必须
		$no       = trim(I('post.no')); //必须
		$wx       = (I('post.wx')) ? trim(I('post.wx')) : '';	//可以为空
		$username = (I('post.username')) ? trim(I('post.username')) : '';//必须
		$pwd      = (I('post.pwd')) ? trim(I('post.pwd')) : '';//必须
		$verify   = trim(I('post.verify'));//必须
		$lng      = (I('post.lng')) ? trim(I('post.lng')) : 'zh-cn';

		$no = authcode($no, 'DECODE', C('Print_Sys_Set.private_key'), 0); //解密
		$wx = authcode($wx, 'DECODE', C('Print_Sys_Set.private_key'), 0); //解密

		if($type == 'at_once_login'){
			$username = authcode($username, 'DECODE', C('Print_Sys_Set.private_key'), 0); //解密
			$pwd = authcode($pwd, 'DECODE', C('Print_Sys_Set.private_key'), 0); //解密
		}else{
			$pwd = md5($pwd);
		}

		//验证阶段
		$backArr = array();

		// if($type != 'at_once_login'){

  //       	if($verify == ''){
		// 		$backArr = array('state'=>'no', 'lng'=>'verify_is_empty');
		// 		$backArr['msg'] = ($lng == 'zh-cn') ? '验证码不能为空' : 'Verification code is required';
  //       	}
  //       	if(!check_verify($verify)){
		// 		$backArr = array('state'=>'no', 'lng'=>'verify_not_right');
		// 		$backArr['msg'] = ($lng == 'zh-cn') ? '验证码错误' : 'Verification code error';
  //       	}
		// }

        if($username == ''){
			$backArr = array('state'=>'no', 'lng'=>'username_is_empty');
			$backArr['msg'] = ($lng == 'zh-cn') ? '账户名不能为空' : 'Account name is required';
        }
        if($pwd == ''){
			$backArr = array('state'=>'no', 'lng'=>'pwd_is_empty');
			$backArr['msg'] = ($lng == 'zh-cn') ? '密码不能为空' : 'Password is required';
        }
        if($no == ''){
			$backArr = array('state'=>'no', 'lng'=>'lack_paramer');
			$backArr['msg'] = ($lng == 'zh-cn') ? '缺少必要参数' : 'Required parameter missing';
        }

		//当发生验证某项有误的时候，就跳转到错误页面，且返回错误信息到页面
		if(isset($backArr['state']) && $backArr['state'] == 'no'){
			$this->assign('msg', $backArr['msg']);
			$this->display('Publiclogin/fail');exit;
		}

        vendor('Hprose.HproseHttpClient');
		$client = new \HproseHttpClient(C('RAPIURL').'/Publiclogin');

		$intime = time();
		$res = $client->_save($no, $wx, $username, $pwd, $intime, C('Print_Sys_Set.wait_time'), $type , C('Print_Sys_Set.time_out'));

		if($lng == 'zh-cn'){
			$this->assign('msg', $res['msg']);
		}else{
			$this->assign('msg', $res['en_msg']);
		}

		$this->assign('lng', $lng);
		// 成功
		if($res['state'] == 'yes'){
			$this->display('Publiclogin/success');
		}else{// 失败
			$this->display('Publiclogin/fail');
		}
	}

	// 查询状态并返回  软件端用
	public function get_state(){
		$Language = new \MkAuto\Controller\LanguageController();//载入多语言控制器

        $json = (I('info')) ? trim(I('info')) : '';

        //验证是否 info 为空
        // if($json == ''){
        //     $result = array('state'=>'no','msg'=>'缺少必要的相关资料', 'lng'=>'miss_parameter');
        //     echo json_encode($result);exit;
        // }

        $info = urldecode(base64_decode($json));
        $info = explode("=",$info);

        $no = $info[1];

		// $no = (I('post.no')) ? trim(I('post.no')) : '';
		
		// 检查是否为空
		if($no == ''){
			$backArr = array('state'=>'0', 'no'=>$no, 'lng'=>'miss_parameter');
			// echo json_encode($backArr);exit;
			$Language->get_lang($backArr);exit;
		}

		vendor('Hprose.HproseHttpClient');
		$client = new \HproseHttpClient(C('RAPIURL').'/Publiclogin');
		$redata = $client->_get_state($no);

		if($redata['status'] !== 0){
			$backArr = array();
			$backArr['no_id'] = $no;
			$backArr['state'] = $redata['status'];
			// $backArr = array(
			// 	'no_id'=>$no,
			// 	'state'=>$redata['status'],
			// );

			switch ($redata['status']) {
				case '10':// 10 准备登录装填
					$backArr['wait_time'] = C('Print_Sys_Set.wait_time'); //10 等待用户输入账户和密码
					// echo json_encode($backArr);exit;
					break;

				case '15':// 15 直接点击确认登录
					$backArr['wait_time'] = C('Print_Sys_Set.wait_time'); //15 直接点击确认登录
					// echo json_encode($backArr);exit;
					break;

				case '20':// 20 成功登录
					$user = $redata['user'];

			        $author = array(
						'uid'      => $user['id'],			//登入的id值
						'username' => $user['username'],		//登入的用户名
						'isLoged'  => md5(md5('passed')),
			        );
			        
			        session('appuser',$author); //session赋值

					$token = set_token();
					$data_u['token'] 		= $token;
					$data_u['time_out'] 	= time();
					$data_u['status'] 		= 200; 			//token设为正常状态 20170707

					$data_a['user_id'] 		= $user['id'];
					
					$client = new \HproseHttpClient(C('RAPIURL').'/PrintSysLogin');
					//保存token相关信息
					$check_print_user = $client->check_print_user($data_a, $data_u, $user, C('Print_Sys_Set.time_out'));
					
					if($check_print_user < 0){
						$backArr['msg']   = '登录异常请重新登录';
						$backArr['state'] = '04';
						$backArr['lng']   = 'login_again';
						// $backArr = array('state' => '04', 'msg'=>'登录异常请重新登录', 'lng'=>'login_again');
			        	// echo json_encode($backArr);exit;
			        	$Language->get_lang($backArr);exit;
					}

					$return_s = array(
						'uid'        => $user['id'],
						'user_name'  => $user['username'],
						'user_type'  => $user['type'], //用户所属注册类型
						'sess_id'    => session_id(),
						'token'      => $token,
					);

					$backArr['msg']     = '验证成功';
					$backArr['appuser'] = $return_s;
					$backArr['lng']     = 'verify_success';

			        // $backArr = array('msg'=>'验证成功','appuser'=>$return_s, 'lng'=>'verify_success');
			        // echo json_encode($backArr);exit;
					break;

				default:
					# code...
					break;
			}

			// echo json_encode($backArr);exit;
			$Language->get_lang($backArr);exit;

		}else{
			$backArr = array('no_id'=>$no, 'state'=>'0', 'msg'=>'查无资料', 'lng'=>'no_info');
			// echo json_encode($backArr);
			$Language->get_lang($backArr);exit;
		}
		
	}

	//成功页面
	public function success(){
		$this->display();
	}

	//失败页面
	public function fail(){
		$this->display();
	}
//===============================================
	/**
	 * 验证码生成
	 */
    public function verify_c(){  
	   
        verify_c();
    }

}