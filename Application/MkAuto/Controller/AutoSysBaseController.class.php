<?php
/**
 * 自助、揽收（新版，二合一）
 * 用途：非 登录/退出 的请求，需要验证登录状态
 */
namespace MkAuto\Controller;
use Think\Controller;
class AutoSysBaseController extends Controller{

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
        $client = new \HproseHttpClient(C('RAPIURL').'/AutoReceive');
        $client -> setTimeout(1200000);//设置 HproseHttpClient 超时时间
		$this->client = $client;
		
		// 非 登录 的请求，才执行以下步骤
		if(!in_array($info['type'], array('login','login_out'))){

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
}