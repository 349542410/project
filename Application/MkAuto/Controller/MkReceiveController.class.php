<?php
/**
 * 物流揽收系统 
 * 功能：揽件   会员账户充值
 * 创建时间：2017-08-10
 * 创建人：jie
 */
namespace MkAuto\Controller;
use Think\Controller;
class MkReceiveController extends Controller{

	//接收的数据 预处理
	public function __construct(){
		parent::__construct();

		$Language = new \MkAuto\Controller\LanguageController();//载入多语言控制器
		$this->Language = $Language;

		$json = trim(I('info',''));

		//验证是否 info 为空
		if($json == ''){
			$result = array('state'=>'no','msg'=>'缺少必要的相关资料', 'lng'=>'miss_parameter');
			$Language->get_lang($result);exit;
		}

		$info = json_decode(urldecode(base64_decode($json)),true);

		$this->type = $info['type'];	//请求function类型
		$this->data = $info['data'];	//请求的数据

		vendor('Hprose.HproseHttpClient');
        $client = new \HproseHttpClient(C('RAPIURL').'/MkReceive');
		$this->client = $client;
		
		// 非 登录 的请求，才执行以下步骤
		if($info['type'] != 'login'){

	        $mkuser = session('appuser');

	        //检查用户是否登录，登录通行证验证
	        if(!$mkuser || $mkuser['isLoged'] != md5(md5('passed'))){

				$header = get_all_headers();
				$token  = $header['token'];

				//检查登录状态
				$is_login = $client->_check_login($token);

				//检查登录状态，检查是否存在此账户信息
				if(!$is_login || $is_login['status'] != '200' || (time()-intval($is_login['time_out'])) > C('MkReceive_Set.time_out')){
					$result = array('state' => 'noLogin', 'msg'=>'未登陆或登录超时', 'lng'=>'login_timeout');
			        $Language->get_lang($result);exit;
				}else{

					//刷新登录状态和时间
					$client->hold_login($token);

			        $author = array(
						'uid'      => $is_login['id'],			//登入的id值
						'isLoged'  => md5(md5('passed')),
			        );
			        $this->tokenID = $is_login['user_id'];
			        session('appuser',$author); //session赋值
				}
	        }
		}

	}

//=========================================
	/**
	 * 公共入口，根据调用函数名自动调用对应的函数进行操作
	 * @return [this->type] [调用的函数方法名]
	 * @return [this->data] [调用函数需要的数据]
	 * @return [type] [description]
	 */
	function console(){

		if(!method_exists($this, $this->type)){
			$result = array('state'=>'no','msg'=>$this->type.'函数不存在', 'lng'=>'function_not_exist');
			$this->Language->get_lang($result);exit;
		}

		$backArr = call_user_func_array(array($this, $this->type), array($this->data));

		$this->Language->get_lang($backArr);
	}
//==========================================

	//登录
	public function login($info){

		//验证字段是否为空
		if(trim($info['uname']) == '' || trim($info['ucode']) == ''){
			return array('state'=>'no','msg'=>'未正确提交相关资料', 'lng'=>'no_info');
		}

		//用户名和密码
		$UserName = trim($info['uname']);
		$UserPwd  = trim($info['ucode']);

		$client = $this->client;
		
		$user = $client->is_login($UserName, $UserPwd, 'name');//Api数据校验

        if($user['state'] == 'no'){

			return $user;

        }else{

        	//验证key
			if(!$this->ckey($info['key'],$user['name'],$user['pwd'])){
				return array('state' => 'no', 'msg'=>'key验证失败', 'lng'=>'verify_failed');
			};

	        $author = array(
				'uid'      => $user['id'],			//登入的id值
				'username' => $user['name'],		//登入的用户名
				'isLoged'  => md5(md5('passed')),
	        );
	        
	        session('appuser',$author); //session赋值

	        // 登录成功	
			$token = set_token();
			$data_u['token']    = $token;
			$data_u['time_out'] = time();
			$data_u['status']   = 200; 			//token设为正常状态 20170707
			
			$data_a['user_id']  = $user['id'];

			//保存token相关信息
			$check_print_user = $client->check_print_user($data_a, $data_u, $user, C('MkReceive_Set.time_out'));

			//如果token已经存在，且尚未过期，则不能登录
			if($check_print_user == 'already_logined'){
				return array('state' => 'no', 'msg' => '您已通过其他终端登录，请退出后再登陆', 'lng'=>'already_logined');
			}
			
			if($check_print_user < 0){
				return array('state' => 'no', 'msg'=>'登录异常请重新登录', 'lng'=>'login_again');
			}

			$return_s = array(
				'uid'        => $user['id'],//ID
				'user_name'  => $user['name'],//昵称
				'true_name'  => $user['tname'],//真实姓名
				'sess_id'    => session_id(),
				'token'      => $token,
			);

	        return array('state' => 'yes', 'msg'=>'验证成功','appuser'=>$return_s, 'lng'=>'verify_success');

        }

	}

	// 根据MKNO或运单号查询该订单数据，并返回数据给软件端
	public function index($info){

		$MKNO = ($info['MKNO']) ? trim($info['MKNO']) : '';

		if($MKNO == ''){
			return array('state'=>'no','msg'=>'缺少必要参数', 'lng'=>'lack_paramer');
		}

		$client = $this->client;

		$res = $client->_index($MKNO, C('ilstarr'));

		return $res;
	}

//======================== 充值界面 ===============================
	// 充值记录
	public function charge_list($info){
		$user_id = $this->tokenID;

		$client = $this->client;

		$list = $client->_recharge_list($user_id);

		if(count($list) > 0){
			$arr = array('state'=>'yes', 'zdata'=>$list);
		}else{
			$arr = array('state'=>'no', 'msg'=>'暂无充值记录', 'lng'=>'no_recharge_list');
		}
		return $arr;
	}

	// 充值 方法
	public function recharging($info){
		//验证字段是否为空
		if(trim($info['member_name']) == '' || trim($info['amount']) == '' || trim($info['paykind']) == '' || trim($info['pwd']) == ''){
			return array('state'=>'no','msg'=>'未正确提交相关资料', 'lng'=>'no_info');
		}

		$member  = $info['member_name'];
		$amount  = $info['amount'];
		$paykind = $info['paykind'];
		$pwd     = $info['pwd'];
		$user_id = $this->tokenID;

        // 验证金额是否为数字
        if(!is_numeric($amount)){
            return array('state'=>'no', 'msg'=>'金额格式必须为数字', 'lng'=>'enter_number');
        }

        //验证金额格式
        $chemon = "/^\d+(\.{0,1}\d+){0,1}$/";
        if(!preg_match($chemon,$amount)){
            return array('state'=>'no', 'msg'=>'错误的金额格式', 'lng'=>'enter_wrong_format');
        }

		$client = $this->client;

		return $client->_recharging($user_id, $member, $amount, $paykind, $pwd);

	}

	//账户登出
	public function login_out($info){
		//验证字段是否为空
		if(trim($info['uname']) == '' || trim($info['dictate']) == ''){
			return array('state'=>'no','msg'=>'未正确提交相关资料', 'lng'=>'no_info');
		}

		if($info['dictate'] != md5('user_want_login_out')){
			return array('state'=>'no','msg'=>'指令验证失败', 'lng'=>'no_info');
		}

		$client = $this->client;

        $header = get_all_headers();

		return $client->_login_out($header['token']);

	}

//========================= 自定义函数 ======================
	//验证密匙key
	private function ckey($str,$uname,$ucode){
		$_md5 = md5(base64_encode($uname.$ucode.C('MkReceive_Set.MkWl2Key')));
		return $str == $_md5;
	}

}