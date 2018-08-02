<?php
/**
 * 预加载 客户端
 */
namespace Admin\Controller;
use Think\Controller;
use Think\Log;

class AdminbaseController extends Controller {

	public function _initialize(){

		//验证登陆,没有登陆则跳转到登陆页面
//		$admin = array(
//				'adid' => 49,
//		);
//		session('admin', $admin);
		if(!session('admin')['adid'] && !isset($_POST['authuid'])){
//				$allow = array('idcard');
//				if(!in_array(ACTION_NAME, $allow)){
//					$this->redirect('Login/index');
//				}else{
//					$admin = array(
//							'adid' => 49,
//					);
//					session('admin', $admin);				
//				}

            // 清除session
            session('admin',null);
            $this->redirect('Login/index');
		}
		
		//权限验证
		$row = $this->authCheck("/".CONTROLLER_NAME."/".ACTION_NAME,session('admin')['adid']);

		$status = $row['status'];
		//print_r($row);
		//exit;
		$this->top_nav   = $row['nav'];
		$this->two_nav   = $row['two_nav'];   //二级导航默认
		$this->three_level = $row['three_level'];		//三级导航默认
		$this->ModulesName = $row['three_nav']['ModulesName'];
		$this->filesname = $row['three_nav']['filesname'];
		unset($row['three_nav']['ModulesName']);
		unset($row['three_nav']['filesname']);
		$this->three_nav = $row['three_nav'];
		$this->username = $row['username'];
		//print_r($this->three_nav);
		//exit;
		if(!$status){
			
		//if(!$this->authCheck("/".CONTROLLER_NAME."/".ACTION_NAME,session('admin')['adid'])){
			//$allow = array('index','checkLogin','pic','verify', 'logout');
			//$allow = array('logout');
			//跳过权限验证的方法
			$allow = array('verify_c', 'Login_out', 'Login_in');
			//跳过权限验证的控制器的方法名
			if("/".CONTROLLER_NAME."/".ACTION_NAME == '/Index/index'){
				$skip_auth = 1;
			}
			
			//$userInfo = session('username');
			//ACTION_NAME 当前操作名  (常量参考)
			  if(!in_array(ACTION_NAME, $allow)){
			  	//$this->error(L('NotAuth_h')); 
				if(!isset($skip_auth)){
			  	//if(IS_AJAX){
				  	if(isset($_SERVER["HTTP_X_REQUESTED_WITH"]) && strtolower($_SERVER["HTTP_X_REQUESTED_WITH"])=="xmlhttprequest"){
				  		//查询返回类型
						$where['name'] = '/'.CONTROLLER_NAME."/".ACTION_NAME;
						vendor('Hprose.HproseHttpClient');
						$client = new \HproseHttpClient(C('RAPIURL').'/AdminAuthvalidate');		//读取、查询操作
					    //print_r($where);
					    //exit;
						$rule_auth = $client->rule_auth($where);
					    //unset($rule_auth['request_type']);
						//print_r($rule_auth);
					    //exit;
					    if($rule_auth['request_type'] == 0 || !isset($rule_auth['request_type'])){
					    	//$result = array('state'=>'no', 'msg'=>'没有该操作权限');
							$result['state']  	= 'no';
							//$result['status'] 	= '0';
							$result['msg']    	= '没有该操作权限';
							$result['status'] 	= false;
							$result['errorstr'] = '没有该操作权限';
							$result['strstr'] 	= '没有该操作权限';
							$result['auth_prompt'] = 'yes';
							$this->ajaxReturn($result);
							exit;
					    }else{
					    	$cont = $this->fetch('Public:prompt');
					    	//$cont = '您没有操作权限';
					    	echo $cont;
					    	exit;
					    }
						
					}else{
						$this->error('您没有该操作的权限');
						exit;
					}
			  	}
				
			  }
			
		}

		//获取用户线路揽收点权限
		$this->point_transit = $this->pointAll();
	}

	
	
/**
  * 权限验证
  * @param rule string|array  需要验证的规则列表,支持逗号分隔的权限规则或索引数组
  * @param uid  int           认证用户的id
  * @param string mode        执行check的模式
  * @param relation string    如果为 'or' 表示满足任一条规则即通过验证;如果为 'and'则表示需满足所有规则才能通过验证
  * @return boolean           通过验证返回true;失败返回false
*/
public function authCheck($rule,$uid,$type=1, $mode='url', $relation='or'){
	vendor('Hprose.HproseHttpClient');
	$client = new \HproseHttpClient(C('RAPIURL').'/AdminAuthvalidate');		//读取、查询操作

    //组建数据
    $data['type'] = 'groups';
    $data['data']['uid'] = $uid;

    $groups = $client->validate($data);
    //print_r($groups);
    //exit;
    $rew['uid'] = $uid;
    $username = $client->username($rew);
    $res['username'] = $username['name'];
    //print_r($groups);
    //exit;
    $datas['groups'] = $groups[0]['rules'];
	if(empty($datas['groups'])){
		if(in_array($groups[0]['group_id'], C('ADMINISTRATOR'))){
			$datas['groups'] = '';
		}else{
			$status = false;
			$res['status'] = $status;
			
			return $res;
		}
	}else{
		//超级权限
		if(in_array($groups[0]['group_id'], C('ADMINISTRATOR'))){
			$datas['groups'] = '';
		}
	}
	//$datas['groups'] = '';
	//根据权限组查询导航条内容
	$nav = $client->groups_nav($datas);
	
	//print_r($nav);
	//exit;
	
	//根据url 与权限组查询出三级导航列表
	$datas['rule'] = $rule;
	//print_r($datas);
	//exit;
	$three_nav_all = $client->rule_nav($datas);
	//print_r($three_nav);
	//exit;
	$three_nav = $three_nav_all['navs'];
	$two_nav = isset($three_nav_all['nav_three']) ? $three_nav_all['nav_three'] : array();
	
	if(empty($three_nav)){
		$status = false;
	}
	//print_r($three_nav);
	//exit;
	
	//超级管理员跳过验证
	//$auth=new \Think\Auth();
	
	//获取当前uid所在的角色组id
	//$groups=$auth->getGroups($uid);
	//判断是否为超级管理员
	if(in_array($groups[0]['group_id'], C('ADMINISTRATOR'))){
		$status =  true;
	}else{
		//组建数据
		$data['type'] = 'check'; 
		$data['data']['rule'] 		= $rule;
		$data['data']['uid'] 		= $uid;
		$data['data']['type'] 		= $type;
		$data['data']['mode'] 		= $mode; 
		$data['data']['relation'] 	= $relation;
		$data['data']['groups'] 	= $groups[0]['rules'];;
		
		
		$check = $client->validate($data);
		//return $auth->check($rule,$uid,$type,$mode,$relation) ? true:false;
		$status =  $check ? true:false;
	}
	//三级默认选中
	$tl['rule'] = $rule;
	$level = $client->three_level($tl);
	//print_r($level);
	//exit;
	
	$res['nav'] = $nav;
	$res['two_nav'] = $two_nav; //二级导航默认
	$res['three_level'] = $level;
	$res['three_nav'] = $three_nav;
	$res['status'] = $status;
	//print_r($res);
	//exit;
	return $res;
}

	/**
	 * 获取线路数组
	 * $type = 1  为数组返回   $type = 2 为字符串返回    $type = 3 为 sql 条件返回
	 * Enter description here ...
	 */
	public function pointAll($type = 1){
		vendor('Hprose.HproseHttpClient');
		$client = new \HproseHttpClient(C('RAPIURL').'/AdminAuthvalidate');		//读取、查询操作
		$data['uid'] = session('admin')['adid'];
		//每60秒更新一次session['point_transit']
//		if(!isset($_SESSION['last_access'])||(time()-$_SESSION['last_access'])>60){
//			$_SESSION['last_access'] = time();
//			$rek = array();
//		}else{
//			$rek = session('point_transit');
//			$rek = json_decode($rek,true);
//			
//		}
		
		$rek = session('point_transit');
		$rek = json_decode($rek,true);
		if(empty($rek[$data['uid']]['point_id']) || empty($rek[$data['uid']]['transit_id'])){
			$res = $client->pointAll($data);
//			if(is_null($res['point_id'])){
//				$res['point_id'] = '';
//			}
//			if(is_null($res['transit_id'])){
//				$res['transit_id'] = '';
//			}
			
			$rek[$res['uid']]['point_id'] = $res['point_id'];
			$rek[$res['uid']]['transit_id'] = $res['transit_id'];
			
			$point_transit = json_encode($rek);
			session('point_transit', $point_transit);
			$rew[$res['uid']]['point_id'] = $res['point_id'];
			$rew[$res['uid']]['transit_id'] = $res['transit_id'];
		}else{
			$rew[$data['uid']]['point_id'] = $rek[$data['uid']]['point_id'];
			$rew[$data['uid']]['transit_id'] = $rek[$data['uid']]['transit_id'];
		}
		
		
		//$type = 1  为数组返回   $type = 2 为字符串返回    $type = 3 为 sql 条件返回
		if($type == 1){
			if($rew[$data['uid']]['point_id'] === 'ALL' && !is_null($rew[$data['uid']]['point_id'])){
				$row['point_id'] = 'ALL';
			}else{
				 if(is_null($rew[$data['uid']]['point_id']) || empty($rew[$data['uid']]['point_id'])){
					$row['point_id'] = 'NONE';
				}else{
					$row['point_id'] = explode(',', $rew[$data['uid']]['point_id']);
				}
				
			}
			if($rew[$data['uid']]['transit_id'] === 'ALL' && !is_null($rew[$data['uid']]['point_id'])){
				$row['transit_id'] = 'ALL';
			}else{
				if(is_null($rew[$data['uid']]['transit_id']) || empty($rew[$data['uid']]['transit_id'])){
					$row['transit_id'] = 'NONE';
				}else{
					$row['transit_id'] = explode(',', $rew[$data['uid']]['transit_id']);
				}
				
			}
			
			//$row['point_id'] = explode(',', $rew[$data['uid']]['point_id']);
			//$row['transit_id'] = explode(',', $rew[$data['uid']]['transit_id']);
		}else if($type == 2){
			$rew[$data['uid']]['point_id'] = trim($rew[$data['uid']]['point_id'], ' ');
			$rew[$data['uid']]['transit_id'] = trim($rew[$data['uid']]['transit_id'], ' ');
			if(strlen($rew[$data['uid']]['transit_id']) == 0 || empty($rew[$data['uid']]['transit_id'])){
				$rew[$data['uid']]['transit_id'] = 'NONE';
			}
			if(strlen($rew[$data['uid']]['point_id']) == 0 || empty($rew[$data['uid']]['point_id'])){
				$rew[$data['uid']]['point_id'] = 'NONE';
			}
			//var_dump($rew);
			//exit;
			$row['point_id'] = $rew[$data['uid']]['point_id'];
			$row['transit_id'] = $rew[$data['uid']]['transit_id'];
		
		}elseif ($type == 3){
			$point_id = explode(',', $rew[$data['uid']]['point_id']);
			$transit_id = explode(',', $rew[$data['uid']]['transit_id']);
			$count_p = count($point_id);
			$count_t = count($transit_id);
			
			if($count_p > 1){
				foreach ($point_id as $key => $val){
					$str_point .= "'" . $val . "'" . ',';
				}
				$str_point = trim($str_point, ',');
				$row['point_id'] = 'in('.$str_point.')'; 
				
			}else{
				if(strlen($rew[$data['uid']]['point_id']) == 0  || empty($rew[$data['uid']]['point_id'])){
					$rew[$data['uid']]['point_id'] = 'NONE';
				}
				$row['point_id'] = $rew[$data['uid']]['point_id'];
				
			}
			if($count_t > 1){
				foreach ($transit_id as $key => $val){
					$str_transit .= "'" . $val . "'" . ','; 
				}
				$str_transit = trim($str_transit, ',');
				$row['transit_id'] = 'in('.$str_transit.')'; 
				
			}else{
				if(strlen($rew[$data['uid']]['transit_id']) == 0 || empty($rew[$data['uid']]['transit_id'])){
					$rew[$data['uid']]['transit_id'] = 'NONE';
				}
				$row['transit_id'] = $rew[$data['uid']]['transit_id'];
			}
			
		}
		
		return $row;
	}


	


	


}