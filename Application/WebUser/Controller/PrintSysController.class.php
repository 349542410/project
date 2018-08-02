<?php
/**
 * 提供会员在ERP软件端登录，操作订单打印
 */
namespace WebUser\Controller;
use Think\Controller;
class PrintSysController extends Controller{

	public function _initialize(){
		$json = trim(I('info',''));
		//验证是否 info 为空
		if($json == ''){
			$result = array('state'=>'no','msg'=>'缺少必要的相关资料', 'lng'=>'miss_parameter');
			echo json_encode($result);exit;
		}

		$info = json_decode(urldecode(base64_decode($json)),true);

		$this->type = $info['type'];
		$this->data = $info['data'];

		vendor('Hprose.HproseHttpClient');

		// 非 登录/退出 的请求，才执行以下步骤
		if(!in_array($info['type'], array('login','login_out'))){
			
	        $mkuser = session('appuser');

	        //检查用户是否登录，登录通行证验证
	        if(!$mkuser || $mkuser['isLoged'] != md5(md5('passed'))){
				
				$header = $this->get_all_headers();
				$token = $header['token'];

				$client = new \HproseHttpClient(C('AUTONURL').'/PrintSysLogin');
				
				//检查登录状态
				$is_login = $client->_is_login($token);

				//检查登录状态
				if(!$is_login || $is_login['status'] != '200' || (time()-intval($is_login['time_out'])) > intval(C('Print_Sys_Set.time_out'))){
					$result = array('state' => 'noLogin', 'msg'=>'未登陆或登录超时', 'lng'=>'login_timeout');
			        echo json_encode($result);exit;
				}else{

					//刷新超时时间
					$client->hold_login($token);

			        $author = array(
						'uid'      => $is_login['id'],			//登入的id值
						'isLoged'  => md5(md5('passed')),
			        );
			        $this->tokenID = $is_login['user_id'];
			        session('appuser',$author); //session赋值
				}
	        }

	        $client = new \HproseHttpClient(C('AUTONURL').'/OrderPrint');

		}else{
			$client = new \HproseHttpClient(C('AUTONURL').'/PrintSysLogin');
        }

        $this->client = $client;
	}

	/**
	 * 公共入口，根据调用函数名自动调用对应的函数进行操作
	 * @return [this->type] [调用的函数方法名]
	 * @return [this->data] [调用函数需要的数据]
	 * @return [type] [description]
	 */
	function console(){

		if(!method_exists($this, $this->type)){
			$result = array('state'=>'no','msg'=>$this->type.'函数不存在', 'lng'=>'function_not_exist');
			echo json_encode($result);exit;
		}

		call_user_func_array(array($this, $this->type), array($this->data));
	}

//============================== 登录 ==================================
	//登录
	public function login($info){

		//验证字段是否为空
		if(trim($info['uname']) == '' || trim($info['ucode']) == ''){
			$result = array('state'=>'no','msg'=>'未正确提交相关资料', 'lng'=>'no_info');
			echo json_encode($result);exit;
		}

		//用户名和密码
		$UserName = trim($info['uname']);
		$UserPwd  = trim($info['ucode']);

		$client = $this->client;
		
		$user = $client->_loginning($UserName, $UserPwd, 'username');//Api数据校验

        if($user['state'] == 'no'){

			echo json_encode($user);exit;

        }else{

        	//验证key
			if(!$this->ckey($info['key'],$user['username'],$user['pwd'])){
				$result = array('state' => 'no', 'msg'=>'key验证失败', 'lng'=>'verify_failed');
				echo json_encode($result);exit;
			};

	        $author = array(
				'uid'      => $user['id'],			//登入的id值
				'username' => $user['username'],		//登入的用户名
				'isLoged'  => md5(md5('passed')),
	        );
	        
	        session('appuser',$author); //session赋值

	        // 登录成功	
			$token = $this->set_token();
			$data_u['token'] 		= $token;
			$data_u['time_out'] 	= time();
			$data_u['status'] 		= 200; 			//token设为正常状态 20170707

			$data_a['user_id'] 		= $user['id'];

			//保存token相关信息
			$check_print_user = $client->check_print_user($data_a, $data_u, $user, C('Print_Sys_Set.time_out'));

			//如果token已经存在，且尚未过期，则不能登录
			if($check_print_user == 'already_logined'){
				$result = array('state' => 'no', 'msg' => '您已通过其他终端登录，请退出后再登陆', 'lng'=>'already_logined');
	        	echo json_encode($result);exit;
			}

			if($check_print_user < 0){
				$result = array('state' => 'no', 'msg'=>'登录异常请重新登录', 'lng'=>'login_again');
	        	echo json_encode($result);exit;
			}

			$return_s = array(
				'uid'        => $user['id'],
				'user_name'  => $user['username'],
				'user_type'  => $user['type'], //用户所属注册类型
				'balance'    => $user['amount'], //账户余额
				'sess_id'    => session_id(),
				'token'      => $token,
				'web_config' => C('Web_Config'), //各线路的价格等配置信息
			);

	        $result = array('state' => 'yes', 'msg'=>'验证成功','appuser'=>$return_s, 'lng'=>'verify_success');
	        echo json_encode($result);exit;

        }

	}

//======================= 打印 =====================

	//查询未打印的订单总数，订单信息，订单相关的商品信息
	public function index($info){

		$user_id = $this->tokenID;//session('appuser.uid');

		$ePage   = ($info['ePage']) ? trim($info['ePage']) : 10;//每页显示的数量
		$p       = ($info['p']) ? trim($info['p']) : 1;//当前显示页数
		$keyword = ($info['keyword']) ? trim($info['keyword']) : '';//搜索关键字

		$client = $this->client;

        $where = array();
		$where['user_id']     = array('eq',$user_id);
		$where['print_state'] = array(array('eq',0),array('eq',10),'or'); //打印中，未打印 都列出来

		if(!empty($keyword)){
			if(is_numeric($keyword)){
				//暂时只能用收件人手机号或者凭证号进行搜索
				if(strlen($keyword) == 11 && preg_match("/13[123569]{1}\d{8}|15[1235689]\d{8}|188\d{8}/", $keyword)){
					$where['reTel'] = array('eq',$keyword);
				}else{
					$where['random_code'] = array('eq',$keyword);
				}
			}else{
				// $where['receiver'] = array('like','%'.$keyword.'%');
				$result = array('state'=>'no','msg'=>'查无数据', 'lng'=>'no_data');
				echo json_encode($result);exit;
			}
			
		}

        $count = $client->_count($where);

        //查询结果没有数据
        if($count == 0){
			$result = array('state'=>'no','msg'=>'查无数据', 'lng'=>'no_data', 'user_id'=>$user_id);
			echo json_encode($result);exit;
        }

        $pages = (ceil(intval($count) / intval($ePage)) == 0) ? 1 : ceil(intval($count) / intval($ePage));// 总页数

        //验证请求的页码是否超出总页数
        if($p > $pages){
        	$result = array('state'=>'no','msg'=>'页码不存在', 'lng'=>'page_not_exist');
			echo json_encode($result);exit;
        }

        $list = $client->_list($where, $p, $ePage);

        $result = array('num' => $count, 'ePage'=>$ePage, 'p'=>$p, 'pages'=>$pages, 'data'=>$list, 'keyword'=>$keyword);

        echo json_encode($result);

	}

	//获取打印资料
	public function getInfo($info){

		$id = ($info['id']) ? trim($info['id']) : '';

		if($id == ''){
			$result = array('state'=>'no','msg'=>'缺少必要参数', 'lng'=>'lack_paramer');
			echo json_encode($result);exit;
		}

		$client = $this->client;

		$info = $client->_info($id, C('RMB_Free_Duty'), C('US_TO_RMB_RATE'));

		if($info === false){
			
			$result = array('state'=>'no','msg'=>'查无数据', 'lng'=>'no_data');
			echo json_encode($result);exit;
		}
		// dump($info);
		echo json_encode($info);
	}

	//页面返回相关资料（含称重重量）
	public function step_one($info){

		$id     = $info['id'];		//订单ID
		$weight = $info['weight'];	//称重重量
		$time   = $info['time'];	//称重时间

		if($id == '' || $weight == '' || $time == ''){
			$result = array('state'=>'no','msg'=>'缺少必要参数', 'lng'=>'lack_paramer');
			echo json_encode($result);exit;
		}

		$client = $this->client;

		$res = $client->_step_one($id, $weight, $time, C('Web_Config'), C('RMB_Free_Duty'), C('US_TO_RMB_RATE'));

		if($res['state'] == 'yes'){
			$data = array('id'=>$id, 'weight' => $weight, 'time'=>$time, 'freight'=>$res['cost'], 'tax'=>$res['tax'], 'weigh_config'=>$res['weigh_config']);
			//保存称重资料且计费成功后，返回
			$result = array('state'=>'yes', 'rdata'=>$data, 'msg'=>'计费成功', 'lng'=>'charge_success');
			echo json_encode($result);
		}else{
			echo json_encode($res);
		}

	}

	//接收 扣费 指令，执行订单的支付并扣费
	public function step_two($info){
		$id      = $info['id'];		//订单ID
		$user_id = $this->tokenID;//session('appuser.uid'); // 用户ID

		if($id == ''){
			$result = array('state'=>'no','msg'=>'缺少必要参数', 'lng'=>'lack_paramer');
			echo json_encode($result);exit;
		}

		$client = $this->client;

		$res = $client->_step_two($id, $user_id);

		if($res['state'] == 'yes'){

			$data = $res['redata'];//支付单号
			$data = array('id'=>$id, 'user_id' => $user_id);
			$data['payno']   = $res['redata']['payno'];//支付单号
			$data['paytime'] = $res['redata']['paytime'];//支付时间
			$data['paykind'] = $res['redata']['paykind'];//支付方式
			$data['balance'] = $res['redata']['balance'];//支付方式

			//扣费成功后，返回
			$backArr = array('state'=>'yes', 'rdata'=>$data, 'msg'=>'支付成功', 'lng'=>'pay_success');
			echo json_encode($backArr);
		}else{
			echo json_encode($res);
		}
	}

	//打印成功后，保存打印状态
	public function step_three($info){

		$id     = $info['id'];		//订单ID
		$status = $info['status'];	//打印状态
		$time   = $info['time'];	//打印时间
		$MKNO   = $info['MKNO'];	//MKNO
		$STNO   = $info['STNO'];	//STNO

		if($id == '' || $status == '' || $time == '' || $MKNO == '' || $STNO == ''){
			$result = array('state'=>'no','msg'=>'缺少必要参数', 'lng'=>'lack_paramer');
			echo json_encode($result);exit;
		}

		$client = $this->client;

		$res = $client->_step_three($id, $status, $time, $MKNO, $STNO);

		echo json_encode($res);
	}

	//账户登出
	public function login_out($info){
		//验证字段是否为空
		if(trim($info['uname']) == '' || trim($info['dictate']) == ''){
			$result = array('state'=>'no','msg'=>'未正确提交相关资料', 'lng'=>'no_info');
			echo json_encode($result);exit;
		}

		if($info['dictate'] != md5('user_want_login_out')){
			$result = array('state'=>'no','msg'=>'指令验证失败', 'lng'=>'no_info');
			echo json_encode($result);exit;
		}

		$client = $this->client;

        $header = $this->get_all_headers();

		$res = $client->_login_out($header['token']);

		echo json_encode($res);exit;
	}

	// 获取账户余额
	public function get_user_balance($info){
		$user_id = $this->tokenID; // 用户ID

		if($user_id == ''){
			$result = array('state'=>'no','msg'=>'缺少必要参数', 'lng'=>'lack_paramer');
			echo json_encode($result);exit;
		}

		$client = $this->client;

		$res = $client->_get_user_balance($user_id);

		if($res !== false){
			//扣费成功后，返回
			$backArr = array('state'=>'yes', 'balance'=>$res, 'msg'=>'成功获取余额', 'lng'=>'get_balance_success');
			echo json_encode($backArr);
		}else{
			$backArr = array('state'=>'no', 'msg'=>'获取余额失败', 'lng'=>'get_balance_falied');
			echo json_encode($backArr);
		}
	}
//========================= 自定义函数 ======================
	//验证密匙key
	private function ckey($str,$uname,$ucode){
		$_md5 = md5(base64_encode($uname.$ucode.C('Print_Sys_Set.MkWl2Key')));
		return $str == $_md5;
	}

 	// 生成token
	private function set_token(){	
		$str = md5(uniqid(md5(microtime(true)),true));  //生成一个不会重复的字符串
        $str = sha1($str);  //加密
        return $str;
	}

	/**
	 * 获取自定义的header数据
	 */
	private function get_all_headers(){

	    // 忽略获取的header数据
	    $ignore = array('host','accept','content-length','content-type');

	    $headers = array();

	    foreach($_SERVER as $key=>$value){
	        if(substr($key, 0, 5)==='HTTP_'){
	            $key = substr($key, 5);
	            $key = str_replace('_', ' ', $key);
	            $key = str_replace(' ', '-', $key);
	            $key = strtolower($key);

	            if(!in_array($key, $ignore)){
	                $headers[$key] = $value;
	            }
	        }
	    }

	    return $headers;
	}

}