<?php
/**
 * 美快后台登录验证  服务器端
 */
namespace Api\Controller;
use Think\Controller\HproseController;
class AdLoginController extends HproseController{    
    protected $crossDomain =    true;
    protected $P3P         =    true;
    protected $get         =    true;
    protected $debug       =    true;

    /**
     * 登陆方法
     * @param  [type] $map [查找条件]
     */
    public function is_login($map,$pwd){
        $res = M('ManagerList')->where($map)->find();
        if(empty($res) || $res['status'] == '0'){
            return array('state' => 'no', 'msg' => '账户不存在或被禁用');
        }

        if($res['pwd'] != md5($pwd)){
            return array('state' => 'no', 'msg' => '密码错误');
        }

        $data['login_time'] = time();

        M('ManagerList')->where($map)->save($data);

		//hua 20180417 获取用户权限组
		if($res){
			$w['uid'] = $res['id'];
			$rek = M('auth_group_access')->field('group_id')->where($w)->find();
			if($rek){
				$res['auth_group'] = $rek['group_id'];
			}
		}
        return array('state' => 'yes', 'msg' => '验证成功', 'res' => $res);
    }

    /**
     * 获取管理员所拥有的权限
     */
    public function power($admin){
        //$group = M('ManagerList')->join('LEFT JOIN mk_role_list ON mk_role_list.id=mk_manager_list.groupid')->where(array('mk_manager_list.id'=>$admin['adid']))->find();
        //$group['power'] = unserialize($group['power']); //反序列化
        //return $group;
		//获取管理员所拥有的权限
		$w['uid'] = $admin['adid'];
		$group = M('auth_group_access')->field('userRule, group_id, uid')->where($w)->find();
		if(empty($group)){
			$rek['status'] = false;
			$rek['errorstr'] = '该用户未赋予任何权限！';
			return $rek;
		}
		
		//检验是否禁用或允许访问权限组或者个人
		$apauth = $admin['apauth'];
		if(!empty(array_filter($apauth))){
			$prohibit_group 	= $apauth['0'];
			$prohibit_personal 	= $apauth['1'];
			$allow_personal 	= $apauth['2'];
			$allow_group 		= $apauth['3'];
			if(!empty($prohibit_group) && in_array($group['group_id'], $prohibit_group)){
				$rek['status'] = false;
				$rek['errorstr'] = '该用户权限组禁止访问！';
				return $rek;
			}
			if(!empty($prohibit_personal) && in_array($group['uid'], $prohibit_personal)){
				$rek['status'] = false;
				$rek['errorstr'] = '该用户禁止访问！';
				return $rek;
			}
			if(!empty($allow_personal) && !in_array($group['uid'], $allow_personal)){
				$rek['status'] = false;
				$rek['errorstr'] = '该用户禁止访问！';
				return $rek;
			}
			if(!empty($allow_group) && !in_array($group['group_id'], $allow_group)){
				$rek['status'] = false;
				$rek['errorstr'] = '该用户权限组禁止访问！';
				return $rek;
			}
			
			
				
		}
		
		
		//判断是否为超级管理员
//		$auth = $admin['auth'];
//		if(in_array($group['group_id'], $auth)){
//			//如果是超级管理员 默认跳转Index/index
//			$rule[0]['name'] = U('/AdminAuth/index_authNav_role');  //本地82用的
//			//$rule[0]['name'] = U('/Index/index');  //本地83或线上用的
//			
//		}else{
//			//存在个人权限则取   $group['userRule']  否则取权限组权限 
//			if(!empty($group['userRule'])){
//				$where['id'] = array('in', $group['userRule']);
//				$where['status'] = 1;
//				$where['default_url'] = 1;
//				$rule = M('auth_rule')->field('id, name, sort')->where($where)->order('sort desc')->select();
//			}else{
//				$rule = array();
//			}
//
//		
//		}

		
		
		$rule[0]['name'] = U('/Index/index');  //本地83或线上用的
		return $rule;
    }
}