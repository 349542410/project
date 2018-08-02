<?php
/**
 * 物流揽收系统 
 * 功能：揽件   会员账户充值
 * 创建时间：2017-08-10
 * 创建人：jie
 */
namespace MkAuto\Controller;
use Think\Controller;
class AutoReceiveController extends AutoSysBaseController{

	//接收的数据 预处理
	public function __construct(){
		parent::__construct();
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

		if(empty($MKNO)){
			return array('state'=>'no','msg'=>'缺少必要参数', 'lng'=>'lack_paramer');
		}

		$client = $this->client;

		return $client->_index($MKNO, C('ilstarr'), C('RMB_Free_Duty'), C('US_TO_RMB_RATE'));

	}

    /**
     * [extra_step 当揽收扫描第一次，有发送重量的时候，才执行]
     * @param  [type] $id     [tran_list.id]
     * @param  [type] $weight [新的称重重量]
     * @return [type]         [description]
     */
	public function extra_step($info){

		$id           = (isset($info['id'])) ? trim($info['id']) : '';//tran_list.id
		$operator_id  = (isset($info['uid'])) ? trim($info['uid']) : '';//操作人id
		$new_weight   = (isset($info['new_weight'])) ? sprintf("%.2f", trim($info['new_weight'])) : '';//最新称重重量
		$new_cost     = (isset($info['new_cost'])) ? sprintf("%.2f", trim($info['new_cost'])) : '';//最新消费金额
		$new_freight  = (isset($info['new_freight'])) ? sprintf("%.2f", trim($info['new_freight'])) : '';//最新运费
		$new_discount = (isset($info['new_discount'])) ? sprintf("%.2f", trim($info['new_discount'])) : '';//最新优惠金额
		$xml          = (isset($info['xml'])) ? $info['xml'] : '';//揽收报文  base64加密的json报文

		if($id == '' || $operator_id == '' || $new_weight == '' || $new_cost == '' || $new_freight == '' || $new_discount == '' || !is_array($xml)){
			return array('state'=>'no','msg'=>'缺少必要参数', 'lng'=>'lack_paramer');
		}
		if($new_weight == 0){
            return array('state'=>'no','msg'=>'请务必称重', 'lng'=>'please_weigh');
        }

		// 检查重量是否有传入数值
		if($new_weight == 0){
			return array('state'=>'no','msg'=>'请务必称重', 'lng'=>'please_weigh');
		}

		// 校验 揽收报文中的重量 是否与最新重量 一致
		if($xml['toMKIL']['0']['Weight'] != $new_weight){
			return array('state'=>'no','msg'=>'重量参数不一致', 'lng'=>'weight_not_same');
		}

		$client = $this->client;

		return $client->_extra_step($info);
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
        $ip      = $info['ip'];
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
		return $client->_recharging($user_id, $member, $amount, $paykind, $pwd, $ip);

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