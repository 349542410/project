<?php
/**
 * 自助打印终端---提供会员在ERP软件端登录，操作订单打印
 * 用途：非 登录/退出 的请求，需要验证登录状态
 */
namespace MkAuto\Controller;
use Think\Controller;
class PrintSysBaseController extends Controller{

	protected $terminal_code; 	// 终端编号
	protected $token;			// token

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

		$arr = json_decode(urldecode(base64_decode($json)),true);

		$this->type = $arr['type'];
		$this->data = $arr['data'];

		vendor('Hprose.HproseHttpClient');

		// 非 登录/退出 的请求，才执行以下步骤
		if(!in_array($arr['type'], array('login','login_out'))){
			
	        $mkuser = session('appuser');

	        //检查用户是否登录，登录通行证验证
	        if(!$mkuser || $mkuser['isLoged'] != md5(md5('passed'))){
				
				$header              = get_all_headers();
				$token               = $header['token'];
				$this->terminal_code = strtoupper($header['terminal-code']);  // 终端编号 2017-10-27   全部字符转大写

				$this->token = $token;

				$client = new \HproseHttpClient(C('RAPIURL').'/PrintSysLogin');
				
				//检查登录状态
				$is_login = $client->_is_login($token);

				//检查登录状态
				if(!$is_login || $is_login['status'] != '200' || (time()-intval($is_login['time_out'])) > intval(C('Print_Sys_Set.time_out'))){
					$result = array('state' => 'noLogin', 'msg'=>'未登陆或登录超时', 'lng'=>'login_timeout');
			        $Language->get_lang($result);exit;
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

	        $client = new \HproseHttpClient(C('RAPIURL').'/OrderPrint');

		}else{
			$client = new \HproseHttpClient(C('RAPIURL').'/PrintSysLogin');
        }

        $this->client = $client;
	}

}