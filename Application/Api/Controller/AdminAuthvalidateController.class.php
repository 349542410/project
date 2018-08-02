<?php
namespace Api\Controller;
use Think\Controller\HproseController;
class AdminAuthvalidateController extends HproseController{
	
	
	public function validate($data){
		
		$auth=new \Think\Auth();
		
		if('groups' == $data['type']){
			//超级管理员跳过验证
			
			$uid = $data['data']['uid'];
			
			//获取当前uid所在的角色组id
			//$groups = $auth->getGroups($uid);
			$wu['uid'] = $uid;
			$g = M('auth_group_access')->where($wu)->find();
			$groups[0]['rules'] = $g['userRule'];
			$groups[0]['group_id'] = $g['group_id'];
			return $groups;
			
		}elseif('check' == $data['type']){
			
			$rule 		= $data['data']['rule'];
			$uid 		= $data['data']['uid'];
			$type 		= $data['data']['type'];
			$mode 		= $data['data']['mode'];
			$relation 	= $data['data']['relation'];
			$group_id 	= $data['data']['groups']; 
			//return $data;
			//
			//$check = $auth->check($rule,$uid,$type,$mode,$relation);
			
			$rule = M('auth_rule')->field('id')->where('name = "'.$rule.'" AND status = 1 ')->find();
			//return $rule;
			if(empty($rule)){
				$check = false;
				return $check; 
			}
			$group = explode(',', $group_id);
			if(in_array($rule['id'], $group)){
				$check = true;
			}else {
				$check = false;
			}
			return $check;
		}	
		
	}
	
	
	//根据权限组获取权限
	public function groups_nav($data){
		$rule = $data['groups'];
		if(empty($rule)){
			$where['am.pid'] = 0;
		}else{
			$where['ar.id'] = array('in', $rule);
		
		}
		$where['ar.status'] = 1;
		//$where['am.pid'] = 0;
		//return $where;
		
		//$pid_one = M('auth_rule')->alias('ar')->field('id, pid_one')->where($where)->select();
		//return $pid_one;
		//$pidid = array();
		//foreach($pid_one as $vak){
		//	if(!in_array($vak['pid_one'], $pidid)){
		//		array_push($pidid, $vak['pid_one']);
		//	}
		//}
		//return $pidid;
		//$where['am.id'] = array('in', $pidid);
		//顶级导航
		$top_nav = M('auth_rule')->alias('ar')->where($where)->join('left join mk_auth_modules as am ON ar.pid_one = am.id')->order('sort desc')->order('sort desc')->select();
		//return $top_nav;
		foreach ($top_nav as $tv){
			//顶级导航名称与url
			//if()
			$nav[$tv['ModulesName']]['name'] = $tv['ModulesName'];
			if(empty($tv['pid_two']) && empty($tv['pid_three'])){
				if(1 == $tv['default_url']){
					$nav[$tv['ModulesName']]['url'] = $tv['name'];
					//默认导航
					$nav_default = explode('/',$tv['name']);
					$nav_d[] = $nav_default[1];
					$nav[$tv['ModulesName']]['nav'] = $nav_d;
					$nav_d = array();
				}
			}
			//二级导航名称与url
			unset($where['am.pid']);
			//unset($where['am.id']);
			$where['pid_one'] = $tv['pid_one'];
			$where['pid_two'] = array('exp', 'is not null');
			
			$two_nav =  M('auth_rule')->alias('ar')->where($where)->join('left join mk_auth_modules as am ON ar.pid_two = am.id')->order('sort desc')->select();
			foreach ($two_nav as $tw){
				
				
				//if(empty($tv['pid_three'])){
					if(1 == $tw['default_url']){
						$nav[$tv['ModulesName']]['two'][$tw['ModulesName']]['name'] = $tw['ModulesName'];
						$nav[$tv['ModulesName']]['two'][$tw['ModulesName']]['url'] = $tw['name'];
						//默认导航
						$nav_default = explode('/',$tw['name']);
						$nav_d[] = $nav_default[1];
						$nav[$tv['ModulesName']]['two'][$tw['ModulesName']]['nav'] = $nav_default[1];
						//$nav_k .= $nav_d;  
					}
				//}
				//三级导航名称与url
				//$three_nav =  M('auth_rule')->alias('ar')->where($where)->join('left join mk_auth_modules as am ON ar.pin_t = am.id')->select();
				//foreach ($three_nav as $th){
					
				//}
			}
			
			
			if(!empty($nav_d)){
				$nav[$tv['ModulesName']]['nav'] = array_unique($nav_d);
				$nav_d = array();
			}
			//公共URL 不用显示
			if(empty($nav[$tv['ModulesName']]['two']) &&  empty($nav[$tv['ModulesName']]['url'])){
				unset($nav[$tv['ModulesName']]);
			}
		}
		return $nav;
		
	}
	
	
	//三级导航权限菜单
	public function rule_nav($data){
		//根据url 出顶级 二级分类
		$rule = M('auth_rule')->alias('ar')->where('name = "'.$data['rule'].'" AND status = 1 ')->join('left join mk_auth_modules as am ON ar.pid_two = am.id')->find();
		$navs['ModulesName'] = $rule['ModulesName'];
		//return $rule;
		//查询满足条件的列表
		if(!empty($data['groups'])){
			$where['ar.id'] 	 = array('in', $data['groups']);
		}
		$where['ar.pid_one'] = $rule['pid_one'];
		$where['ar.pid_two'] = empty($rule['pid_two']) ? array('exp', 'is null') : $rule['pid_two'];
		//return $where;
		$nav = M('auth_rule')->alias('ar')->join('left join mk_auth_modules as am ON ar.pid_three = am.id ')->where($where)->order('sort desc')->select();
		//return $nav;
		//return $nav;
//		$v['name'] = 'Index/index_one'; 
//		$r = strpos($v['name'], 'index')
//		return $r;
//		//组建数据
		if(!empty($nav)){
			foreach ($nav as $v){
				//$number = strpos($v['name'], 'index');
				//return $number;
				//exit;
				//if($number > 0){
				if(1 == $v['default_url']){
						$navs[$v['ModulesName']]['name'] = $v['ModulesName'];	//列表名称
						$navs[$v['ModulesName']]['url'] = $v['name'];	//列表URL
						//默认导航
						$nav_default = explode('/',$v['name']);
						$nav_d[] = $nav_default[1];
						//$navs[$v['ModulesName']]['two_nav'] = $nav_d;
						$navs[$v['ModulesName']]['nav'] = $nav_default[1];
						$navs[$v['ModulesName']]['action'] = $nav_default[2];
//						$files = substr($v['name'], $number);
//						$p = explode('_',$files);
//						if(in_array('no', $p)){
//							$navs['filesname'] = 'no';	//加载列表文件名称
//						}else{
//							$navs['filesname'] = $p[1];	//加载列表文件名称
//							
//						}
						
						
				}	
			}
			if (!isset($navs)){
				$navs = array();
			}else{
				
			}
		}else{
			$navs = array();
		}
		if (!empty($nav_d)){
			$nav_d = array_unique($nav_d);
			$res['nav_three'] = $nav_d;
			$res['navs'] = $navs;
		}else{
			$res['navs'] = $navs;
		}		
		return $res;
	}
	
	
	/**
	 * 三级导航默认选中
	 * Enter description here ...
	 */
	public function three_level($data){
		$where['name'] = $data['rule'];
		$rew = M('auth_rule')->field('pid_one, pid_two, pid_three')->where($where)->find();
		if($rew){
			$rew = array_filter($rew);
			if($rew){
				$res = M('auth_rule')->field('name')->where($rew)->select();
				foreach ($res as $key => $val){
					$row[] = $val['name'];
				}
			}else{
				$row[] = array();
			}
		}else{
			$row = array();
		}
		
		return $row;
		
	}
	
	
	/**
	 * 获取用户线路
	 * Enter description here ...
	 */
	public function pointAll($data){
		$res = M('auth_group_access')->field('uid, point_id, transit_id')->where('uid = '.$data['uid'].' ')->find();
		return  $res;
	} 
	
	/**
	 * 获取用户名称
	 * Enter description here ...
	 */
	public function username($data){
		if(isset($data['uid'])){
			$where['id'] = $data['uid'];
		}
		if(isset($data['name'])){
			$where['name'] = $data['name'];
		}
		if(isset($where['password'])){
			$where['password'] = $data['password'];
		}
		$res = M('manager_list')->field('id, name')->where($where)->find();
		return $res;
	}	
	/**
	 * 查询当前访问url 返回类型
	 * Enter description here ...
	 * @param unknown_type $data
	 */
	public function rule_auth($data){
		//return $data;
		$res = M('auth_rule')->where($data)->find();
		return $res;
	}
	
	/**
	 * 登陆权限API
	 * Enter description here ...
	 */
	public function longinAuth($data){
		if(!empty($data['name'])){
			$where['name'] = $data['name'];
		}else{
			$rew['status'] = false;
			$rew['errorstr'] = '用户名不能为空!';
			return $rew;
			
		}
		if(!empty($data['password'])){
			$where['password'] = $data['password'];
		}else{
			$rew['status'] = false;
			$rew['errorstr'] = '用户名密码不能为空!';
			return $rew;
			
		}
		//查询用户名
		$res = M('manager_list')->field('id, name')->where($where)->find();
		if(!empty($res)){
			$agcwhere['uid'] = $res['id'];
			$row = M('auth_group_access')->field('id, group_id, userRule')->where($agcwhere)->find();
			if(!empty($row)){
				//是否为超级管理员
				if(in_array($row['group_id'], $data['auth'])){
					$rwhere['status'] = 1;
					$auth = M('auth_rule')->field('name')->where($rwhere)->select();
					$rew['username'] = $res;
					$rew['auth'] = $auth;
					$rew['status'] = true;
					return $rew;					
				}
				
				if(!empty($row['userRule'])){
					//获取访问权限
					$rule = trim($row['userRule'], ' ');
					$rule = trim($rule, ',');
					
					$rwhere['id'] = array('in', $rule);
					$rwhere['status'] = 1;
					$auth = M('auth_rule')->field('name')->where($rwhere)->select();
					$rew['username'] = $res;
					$rew['auth'] = $auth;
					$rew['status'] = true;
					return $rew;
					
				}elseif (!empty($row['group_id'])){
					$agwhere['id'] = $row['group_id'];
					$rule = M('auth_group')->field('rules')->where($agwhere)->find();
					$rule_id = trim($row['rules'], ' ');
					$rule_id = trim($rule_id, ',');
					
					$rwhere['id'] = array('in', $rule_id);
					$rwhere['status'] = 1;
					$auth = M('auth_rule')->field('name')->where($rwhere)->select();
					$rew['username'] = $res;
					$rew['auth'] = $auth;
					$rew['status'] = true;
					return $rew;					
				}else{
					$rew['status'] = false;
					$rew['errorstr'] = '该用户未赋予任何权限';
					return $rew;
				}
			}else{
				$rew['status'] = false;
				$rew['errorstr'] = '该用户未赋予任何权限';
				return $rew;
			}
			
			
		}else{
			$rew['status'] = false;
			$rew['errorstr'] = '该账号不存在！';
			return $rew;
		}	
	
	}
	
	
	
}
	