<?php
/**
 * 自助打印终端---扫码登录  Api
 * 功能：
 * 创建时间：2017-08-14
 * 创建人：jie
 */
namespace AUApi\Controller;
use Think\Controller\HproseController;
class PublicloginController extends HproseController{

	public function _login($no, $wx){

		// 非微信扫码登录的，每次扫码都是一次新的登录，当次登录成功后，就会删除此次记录的
		if($wx == ''){

			//新增登录准备的记录
			$data_u = array();
			$data_u['no_id'] = $no;
			$data_u['status'] = 10; //准备登录的状态  等待用户输入账户和密码
			$data_u['cretime'] = time();

			$res = M('UserSoft2wechat')->add($data_u);

			//成功生成准备登录的记录
			if($res){
				return array('state'=>'enter_username_and_pwd', 'msg'=>'输入账户和密码', 'lng'=>'enter_username_and_pwd');
			}else{
				return array('state'=>'false', 'msg'=>'数据错误', 'lng'=>'data_is_wrong');
			}

		}else{

			$check = M('UserSoft2wechat')->where(array('wx_id'=>$wx))->find();

			if(!$check){
				//新增登录准备的记录
				$data_u = array();
				$data_u['no_id'] = $no;
				$data_u['wx_id'] = $wx;
				$data_u['status'] = 10; //准备登录的状态  等待用户输入账户和密码
				$data_u['cretime'] = time();

				$res = M('UserSoft2wechat')->add($data_u);

				//成功生成准备登录的记录
				if($res){
					return array('state'=>'enter_username_and_pwd', 'msg'=>'输入账户和密码', 'lng'=>'enter_username_and_pwd');
				}else{
					return array('state'=>'false', 'msg'=>'数据错误', 'lng'=>'data_is_wrong');
				}

			}else{

				$bd = array();
				$bd['cretime'] = time();
				
				if($check['status'] == '20' || $check['status'] == '15'){
					
					$bd['status'] = 15;
					$bd['no_id'] = $no;

					M('UserSoft2wechat')->where(array('wx_id'=>$wx))->save($bd); //15 等待点击“登录”按钮

					$data = M('UserList')->where(array('id'=>$check['user_id']))->find();
					return array('state'=>'at_once_login', 'data'=>$data, 'msg'=>'点击确认直接登录', 'lng'=>'at_once_login');

				}else if($check['status'] == '10'){
					$bd['no_id'] = $no;
					M('UserSoft2wechat')->where(array('wx_id'=>$wx))->save($bd); //10 等待用户输入账户和密码
					return array('state'=>'enter_username_and_pwd', 'msg'=>'输入账户和密码', 'lng'=>'enter_username_and_pwd');
				}
			}

			// if($check['status'] != '20'){
			// 	return array('state'=>'0', 'msg'=>'登录状态已过期');
			// }


		}
	}

	public function _save($no, $wx, $username, $pwd, $intime, $wait_time, $type, $time_out='600'){

		$map = array();
		if($type != 'at_once_login'){
			$map['no_id'] = array('eq',$no);
		}else{
			$map['wx_id'] = array('eq',$wx);
		}

		$check = M('UserSoft2wechat')->where($map)->find();

		//检验登录准备记录是否存在
		if($check){

			//检验cretime与intime之间的差别是否大于wait_time*0.95 的时限
			if(intval($intime) - intval($check['cretime']) > intval($wait_time) * 0.95){
				return array('state'=>'no', 'msg'=>'登录操作超时，请重新扫描登录', 'lng'=>'timeout_to_login', 'en_msg'=>'Login operation timed out, please scan again to login');
			}

			$user = M('UserList')->where(array('username'=>$username))->find();

			//检验账户是否存在
			if(!$user){
				return array('state'=>'no', 'msg'=>'账户不存在', 'lng'=>'user_not_exist', 'en_msg'=>'The account doesn’t exist');
			}else if($user['pwd'] !== $pwd){//检验密码正确性
				return array('state'=>'no', 'msg'=>'密码不正确', 'lng'=>'pwd_not_right', 'en_msg'=>'The password is not correct');
			}else{

				//检查该账户是否已经登录
		        $user_app = M('AppUserPrint')->where(array('user_id'=>$user['id']))->find();

		        //如果token已经存在，且尚未过期，则不能登录
		        if($user_app){
		            if((time()-intval($user_app['time_out'])) <= intval($time_out)){
		                return array('state' => 'no', 'msg' => '您已通过其他终端登录', 'lng'=>'already_logined', 'en_msg'=>'You’ve logged in through other terminal');
		            }
		        }

				$data = array();
				$data['status']  = 20;
				$data['user_id'] = $user['id'];
				$data['wx_id']   = ($wx == '') ? '0' : $wx;
				$data['no_id']   = $no;
				$data['intime']  = $intime;

				//更改登录状态为 成功登录
				M('UserSoft2wechat')->where($map)->save($data);
				return array('state'=>'yes', 'msg'=>'验证通过', 'lng'=>'passed', 'en_msg'=>'Verification passed');
			}


		}else{
			return array('state'=>'no', 'msg'=>'请重新扫描登录', 'lng'=>'scan_again', 'en_msg'=>'Please scan again to login');
		}
	}

	// 软件端查询状态并返回
	public function _get_state($no){
		$soft = M('UserSoft2wechat')->where(array('no_id'=>$no))->find();
		
		if(!$soft){
			return array('status'=>0);
		}

		$user = array();

		if($soft['status'] == 20){
			$user = M('UserList')->where(array('id'=>$soft['user_id']))->find();
		}

		return array('status'=>$soft['status'], 'user'=>$user);
	}
}