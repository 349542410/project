<?php
namespace Api\Controller;
use Think\Controller\HproseController;
class AdminAuthController extends HproseController{

	/*===============用户信息处理	start======================*/
	/**
	 * 取得用户信息
	 * Enter description here ...
	 */
	public function userlist($data){
    	
       	$count = M('manager_list')->count();
        //return $count;
        
//		$page=new \Think\Page($count,$data['epage']);
//		//%FIRST% 表示第一页的链接显示 
//		//%UP_PAGE% 表示上一页的链接显示 
//		//%LINK_PAGE% 表示分页的链接显示 
//		//%DOWN_PAGE% 表示下一页的链接显示 
//		//%END% 表示最后一页的链接显示 
//		
//		$page->setConfig('theme','%FIRST% %UP_PAGE% %LINK_PAGE% %DOWN_PAGE% %END% %HEADER%');
//		$page -> setConfig('prev', '上一页');
//		$page -> setConfig('next','下一页');
//		$page -> setConfig('last','末页');
//		$page -> setConfig('first','首页');
//		
//		$show = $page->show();		
		//$list = M('manager_list')->field('id, name, tname, email, phone')->where('status = 1')->limit($page->firstRow.','.$page->listRows)->select();
		$list = M('manager_list')->field('id, name, tname, email, phone, status')->page($data['p'],$data['epage'])->select();
		
		
    	return array('count'=>$count, 'list'=>$list);
		
	}
	/**
	 * 用户权限
	 * Enter description here ...
	 * @param $data
	 */
	public function useredit($data){
		$res_a = M('auth_group')->where('status=1')->field('id,title')->select();
		//return $res_a;
		$res_b = M('manager_list')->field('au.id, au.name, ga.group_id, ga.userRule')->alias('au')->join('left join mk_auth_group_access AS ga ON au.id = ga.uid')->where('au.id = '.$data['id'].'')->find();
		$res['data'] = $res_a;
		$res['res'] = $res_b;
		
		return $res;
		
	}
	
	/**
	 * 用户添加角色
	 * Enter description here ...
	 * @param unknown_type $data
	 */
	public function usergroup($data){
		//检验是否存在  存在则修改  不存在则添加
		$res = M('auth_group_access')->where('uid = '.$data['uid'].' ')->find();
		if($res){
			$red = M('auth_group_access')->where('uid = '.$data['uid'].'')->save($data);
		}else{
			$red = M('auth_group_access')->add($data);
			
		}
		return $red;
	}
	
	
	/**
	 * 管理员揽收点列表
	 * Enter description here ...
	 */
	public  function user_point($data){
	
		//取得揽收点信息
		//$point = M('collect_point')->field('id, point_name')->select();
		
		
		
		//取得当前揽收点信息
		//$group = M('auth_group_access')->where('uid = '.$data['adid'].' ')->find();
		$group = M('manager_list')->field('au.id, au.name, ga.group_id, ga.point_id')->alias('au')->join('left join mk_auth_group_access AS ga ON au.id = ga.uid')->where('au.id = '.$data['id'].'')->find();
		$w['uid'] = $data['aduid'];
		$adpoint = M('auth_group_access')->field('point_id')->where($w)->find();
		$adpoint_id = trim($adpoint['point_id'], ',');
		$adpoint_id = trim($adpoint_id, '，');
		$ad_count = strpos($adpoint_id, ',');
		//return '23423';
		if($ad_count){
			$where['id'] = array('in', $adpoint_id);
		}else{
			
			$where['id'] = $adpoint_id;
		}
		if('ALL' == $where['id'] || in_array($group['group_id'], $data['administrator'])){
			$where = '';
			$res['point_id_all'] = 'ALL';
		}
		//return $where;
		$point = M('collect_point')->field('id, point_name')->where($where)->select();	
		
		//return $group;
//		if(empty($group['point_id'])) {
//			$w['uid'] = $data['aduid'];
//			$adpoint = M('auth_group_access')->field('point_id')->where($w)->find();
//			if(!empty($adpoint)){
//				$group['point_id'] = $adpoint['point_id'];
//			}else{
//				$res['point'] = '';
//				$res['group'] = $group;
//				return $res;				
//			}
//		}

//		$w['uid'] = $data['aduid'];
//		$adpoint = M('auth_group_access')->field('point_id')->where($w)->find();
//		$adpoint_id = trim($adpoint['point_id'], ',');
//		$adpoint_id = trim($adpoint_id, '，');
//		$ad_count = strpos($adpoint_id, ',');
//		//return '23423';
//		if($ad_count){
//			$where['id'] = array('in', $adpoint_id);
//		}else{
//			
//			$where['id'] = $adpoint_id;
//		}
//		if('ALL' == $where['id']){
//			$where = '';
//			$res['point_id_all'] = 'ALL';
//		}

		//return $where;
		//$point = M('collect_point')->field('id, point_name')->where($where)->select();			
/*		if(!is_null($group['point_id'])){
			//return  'sfgsdf';
			$point_id = trim($group['point_id'], ',');
			$point_id = trim($point_id, '，');
			$count = strpos($point_id, ',');
//			if(in_array($group['group_id'], $data['administrator'])){
//				return $data['administrator'];
//			}else{
//				
//				return $data['administrator'];
//			}
			if($count && !in_array($group['group_id'], $data['administrator'])){
				$where['id'] = array('in', $point_id);
			}else{
				if('ALL' == $group['point_id'] || in_array($group['group_id'], $data['administrator'])){  //当为拥有全部线路或者为超级管理员时  查询所有揽收点    $group['point_id'] = 'ALL' 时 为拥有全部揽收点
					$where = '';
					//if('ALL' == $group['point_id']){
					//	$group['point_id'] = 0;	
					//}
					$res['point_id_all'] = 'ALL';
				}else{
					$where['id'] = $point_id;
				}
				//$where['id'] = $point_id;
			}
			//return $where;
			$point = M('collect_point')->field('id, point_name')->where($where)->select();
			//return $point;
			//$res['point'] = $point_c;
		}else{
			$w['uid'] = $data['aduid'];
			$adpoint = M('auth_group_access')->field('point_id')->where($w)->find();
			$adpoint_id = trim($adpoint['point_id'], ',');
			$adpoint_id = trim($adpoint_id, '，');
			$ad_count = strpos($adpoint_id, ',');
			//return '23423';
			if($ad_count){
				$where['id'] = array('in', $adpoint_id);
			}else{
				
				$where['id'] = $adpoint_id;
			}
			if('ALL' == $where['id']){
				$where = '';
				$res['point_id_all'] = 'ALL';
			}
			//return $where;
			$point = M('collect_point')->field('id, point_name')->where($where)->select();	
		}
*/		
		$res['point'] = $point;
		$res['group'] = $group;
		return $res;
	}
	
	
	
	/**
	 * 管理员揽收点保存
	 * Enter description here ...
	 */
	public function pointhaddent($data){
		$row = M('auth_group_access')->where('uid = '.$data['uid'].' ')->find();
		if(empty($row)){
			$rek['status'] = false;
			$rek['errorstr'] = '请给该员工设置角色';
			return $rek;
		}		
		$res = M('auth_group_access')->where('uid = '.$data['uid'].' ')->save($data);
		return $res;
	}	
	

	
	/**
	 * 员工中转路线列表
	 * Enter description here ...
	 */
	public  function transit($data){
		
		//取得中转路线信息
		//$transit = M('transit_center')->field('id, name')->where('status = 1')->select();
		
		
		
		//取得当前揽收点信息
		//$group = M('auth_group_access')->where('uid = '.$data['adid'].' ')->find();
		$group = M('manager_list')->field('au.id, au.name, ga.transit_id, ga.group_id')->alias('au')->join('left join mk_auth_group_access AS ga ON au.id = ga.uid')->where('au.id = '.$data['id'].'')->find();
		
		$w['uid'] = $data['aduid'];
		$adpoint = M('auth_group_access')->field('transit_id')->where($w)->find();
		$adpoint_id = trim($adpoint['transit_id'], ',');
		$adpoint_id = trim($adpoint_id, '，');
		$ad_count = strpos($adpoint_id, ',');
		//return '23423';
		if($ad_count){
			$where['id'] = array('in', $adpoint_id);
		}else{
			
			$where['id'] = $adpoint_id;
		}
		if('ALL' == $where['id'] || in_array($group['group_id'], $data['administrator'])){
			//$where = '';
			$res['transit_id_all'] = 'ALL';
			unset($where['id']);
		}
		$where['status'] = 1;


		//return $where;
		$transit = M('transit_center')->field('id, name')->where($where)->select();
				
/*		
		if(!is_null($group['transit_id'])){
			//return  'sfgsdf';
			$transit_id = trim($group['transit_id'], ',');
			$transit_id = trim($transit_id, '，');
			$count = strpos($transit_id, ',');
//			if(in_array($group['group_id'], $data['administrator'])){
//				return $data['administrator'];
//			}else{
//				
//				return $data['administrator'];
//			}
			if($count && !in_array($group['group_id'], $data['administrator'])){
				$where['id'] = array('in', $transit_id);
			}else{
				if('ALL' == $group['transit_id'] || in_array($group['group_id'], $data['administrator'])){  //当为拥有全部线路或者为超级管理员时  查询所有揽收点    $group['point_id'] = 'ALL' 时 为拥有全部揽收点
					$where = '';
					//if('ALL' == $group['point_id']){
					//	$group['point_id'] = 0;	
					//}
					$res['transit_id_all'] = 'ALL';
				}else{
					$where['id'] = $transit_id;
				}
				//$where['id'] = $point_id;
			}
			$where['status'] = 1;
			//return $where;
			$transit = M('transit_center')->field('id, name')->where($where)->select();
			//return $point;
			//$res['point'] = $point_c;
		}else{
			$w['uid'] = $data['aduid'];
			$adpoint = M('auth_group_access')->field('transit_id')->where($w)->find();
			$adpoint_id = trim($adpoint['transit_id'], ',');
			$adpoint_id = trim($adpoint_id, '，');
			$ad_count = strpos($adpoint_id, ',');
			//return '23423';
			if($ad_count){
				$where['id'] = array('in', $adpoint_id);
			}else{
				
				$where['id'] = $adpoint_id;
			}
			if('ALL' == $where['id'] || in_array($group['group_id'], $data['administrator'])){
				$where = '';
				$res['transit_id_all'] = 'ALL';
			}
			$where['status'] = 1;
			//return $where;
			$transit = M('transit_center')->field('id, name')->where($where)->select();	
		}
*/		
		$res['transit'] = $transit;
		$res['group'] = $group;
		return $res;
	}	
	
	/**
	 * 管理员中转路线保存
	 * Enter description here ...
	 */
	public function transithaddent($data){
		$row = M('auth_group_access')->where('uid = '.$data['uid'].' ')->find();
		if(empty($row)){
			$rek['status'] = false;
			$rek['errorstr'] = '请给该员工设置角色';
			return $rek;
		}
		$res = M('auth_group_access')->where('uid = '.$data['uid'].' ')->save($data);
		return $res;
	}		
	
	/**
	 * 员工删除
	 * Enter description here ...
	 */
	public function userdelete($data){
		//删除员工权限组
		$w['uid'] = $data['id'];
		M('auth_group_access')->where($w)->delete();
		$res = M('manager_list')->where($data)->delete();
		return $res;
	
	}
	
	
	
	/*===============用户信息处理	end======================*/
	
	/*===============权限组模块名处理	start======================*/
	/**
	 * 取得权限组模块名
	 * Enter description here ...
	 * @param $data
	 */
	public function modules($data){
		
       	$list = D('auth_modules')->Authsmodules();
//		$page=new \Think\Page($count,$data['epage']);
//		//%FIRST% 表示第一页的链接显示 
//		//%UP_PAGE% 表示上一页的链接显示 
//		//%LINK_PAGE% 表示分页的链接显示 
//		//%DOWN_PAGE% 表示下一页的链接显示 
//		//%END% 表示最后一页的链接显示 
//		
//		$page->setConfig('theme','%FIRST% %UP_PAGE% %LINK_PAGE% %DOWN_PAGE% %END% %HEADER%');
//		$page -> setConfig('prev', '上一页');
//		$page -> setConfig('next','下一页');
//		$page -> setConfig('last','末页');
//		$page -> setConfig('first','首页');
//		
//		$show = $page->show();		
		//$list = M('auth_modules')->limit($page->firstRow.','.$page->listRows)->select();
		//$list = M('auth_modules')->page($data['p'],$data['epage'])->select();
		
		
    	return array('list'=>$list);		
	}
	
	public function modules_add(){
		$res = D('auth_modules')->Authsmodules();
		//$rek = $this->list_to_tree($res);
		//return $rek;
		return $res;
	}

	
	
	/**
	 * 无限极分类
	 * Enter description here ...
	 * @param unknown_type $list
	 * @param unknown_type $pk
	 * @param unknown_type $pid
	 * @param unknown_type $child
	 * @param unknown_type $root
	 */
	
//	public function list_to_tree($list, $pk='id', $pid = 'pid', $child = '_child', $root = 0) {
//	  // 创建Tree
//	  $tree = array();
//	  if(is_array($list)) {
//	    // 创建基于主键的数组引用
//	    $refer = array();
//	    foreach ($list as $key => $data) {
//	      $refer[$data[$pk]] =& $list[$key];
//	    }
//	    foreach ($list as $key => $data) {
//	      // 判断是否存在parent
//	      $parentId = $data[$pid];
//	      if ($root == $parentId) {
//	        $tree[] =& $list[$key];
//	      }else{
//	        if (isset($refer[$parentId])) {
//	          $parent =& $refer[$parentId];
//	          $parent[$child][] =& $list[$key];
//	        }
//	      }
//	    }
//	  }
//	  return $tree;
//	}
	
	
	
	/**
	 * 权限组模块名添加修改处理
	 * Enter description here ...
	 */
	public function modules_handle($data){
		if(!empty($data['id'])){
			$id = $data['id'];
		}
		
		//检验当前父类是否可以添加修改       
		//1.查找当前父类是否有下一级子类  有  可以添加   没有   检验规则表是否有当前父类规则   有则删除规则再添加   没有就可以添加
		$sub = M('auth_modules')->field('id')->where('pid = '.$data['pid'].' ')->find();
		
		if(empty($sub)){
			$sub_one = M('auth_rule')->field('id')->where('pid_one = '.$data['pid'].'')->find();
			//return $sub_one;
			if(!empty($sub_one)){
				$res['status'] = false;
				$res['errorstr'] = '当前父类存在规则，请删除后再添加';
				return $res;
			}
		}
		
		
		if(!isset($id)){
			$res = M('auth_modules')->data($data)->add();
			return $res;
		}else{
			if(!empty($data['rule_id'])){
				//检验默认url唯一值 有则取消默认
				$row = M('auth_rule')->field('pid_one, pid_two, pid_three')->where('id = '.$data['rule_id'].' ')->find();
				$k['pid_one'] 	= $row['pid_one'];
				$k['pid_two'] 	= empty($row['pid_two']) ? array('exp', 'is null') : $row['pid_two'];
				$k['pid_three'] = empty($row['pid_three']) ? array('exp', 'is null') : $row['pid_three'];
				$v['default_url'] = 0;
				M('auth_rule')->data($v)->where($k)->save();
				//设置默认URL
				$rek['id'] = $data['rule_id'];
				$rek['default_url'] = $data['default_url'];
				$rew = M('auth_rule')->data($rek)->where('id = '.$data['rule_id'].' ')->save();
				unset($data['rule_id']);
				unset($data['default_url']);
				
			}
			//return $res;
			$res = M('auth_modules')->data($data)->where('id = '.$id.'')->save();
			$res = empty($res) ? empty($rew) ? 0 : $rew : $res;
			return $res;	
		
		}
	
	}
	
	/**
	 * 编辑模块名
	 * Enter description here ...
	 */
	public function modulesEdit($data){
		
		$data = M('auth_modules')->where($data)->find();
		
		$res = D('auth_modules')->Authsmodules();
		$rek['data'] = $data;
		$rek['res'] = $res;
		return $rek;
	}
	/**
	 * 删除模块名
	 * Enter description here ...
	 */
	public function modulesDelete($data){
		$id = $data['id'];
		//检验是否可以删除
		$row = M('auth_modules')->where('pid ='.$id.' ')->find();
		if(!empty($row)){
			$res['status'] = false;
			$res['errorstr'] = '该类存在下级分类，请先删除下级分类';
			return $res;
		}
		
		//检验是否存在规则中
		$where['pid_one'] 	= $id;
		$where['pid_two'] 	= $id;
		$where['pid_three'] = $id;
		$where['_logic'] 	= 'OR';
		$rek = M('auth_rule')->where($where)->find();
		if(!empty($rek)){
			$res['status'] = false;
			$res['errorstr'] = '该类存在规则，请先删除分类规则';
			return $res;
		}
		//return 'grfgds'; 
		
		$res = M('auth_modules')->where('id = '.$id.'')->delete();
		return $res;
	}
	
	/*===============权限组模块名处理	end======================*/
	
	
	/*===============权限添加	start====================*/
	
	/**
	 * 权限添加-取得权限组模块
	 * Enter description here ...
	 */
	public function auth_add(){
		
		 $res = D('auth_modules')->Authsmodules();
		 $rek['list'] = $res;
		 
		 return $res;	
	}
	
	/**
	 * 处理权限添加
	 * Enter description here ...
	 */
	public function authAddHandle($data){
		$pids = M('auth_modules')->field('id, pid')->where('id = '.$data['mid'].' ')->find();
		if(empty($pids['pid'])){
			$data['pid_one'] = $data['mid'];
			unset($data['mid']);
		}else{
			$pid_one = M('auth_modules')->field('id, pid')->where('id = '.$pids['pid'].' ')->find();
			
			if(empty($pid_one['pid'])){
				$data['pid_one'] = $pid_one['id'];
				$data['pid_two'] = $pids['id'];
				//$data['pid_three'] = ;
			}else{
				$pid_two = M('auth_modules')->field('id, pid')->where('id = '.$pid_one['pid'].' ')->find();
				if(empty($pid_two['pid'])){
					$data['pid_one'] = $pid_two['id'];
					$data['pid_two'] = $pid_one['id'];
					$data['pid_three'] = $data['mid'];
					unset($data['mid']);
				}else{
					$res['status'] = false;
					$res['errorstr'] = '当前权限分类只支持三级分类，请重新添加';
					return $res;
				}
			}
		}
		
		if(empty($data['id'])){
			$res = M('auth_rule')->add($data);
			return $res;
		}else{
			$res = M('auth_rule')->where('id = '.$data['id'].' ')->save($data);
			return $res;
		}
	}
	
	
	/**
	 * 权限列表
	 * Enter description here ...
	 */
	public function authList($data){
		$m = D('RuleView');
		if(!empty($data['id'])){
			$where['pid_one']  	= $data['id'];
			$where['pid_two']  	= $data['id'];
			$where['pid_three']	= $data['id'];
			$where['_logic'] 	= 'OR';
			//$where['_complex'] = $map;

		}else{
			$where = '';
		}		
		
		$count = $m->where($where)->count();
	
//		$page=new \Think\Page($count,$data['epage']);
//		//%FIRST% 表示第一页的链接显示 
//		//%UP_PAGE% 表示上一页的链接显示 
//		//%LINK_PAGE% 表示分页的链接显示 
//		//%DOWN_PAGE% 表示下一页的链接显示 
//		//%END% 表示最后一页的链接显示 
//		
//		$page->setConfig('theme','%FIRST% %UP_PAGE% %LINK_PAGE% %DOWN_PAGE% %END% %HEADER%');
//		$show=$page->show();
		//$data=$m->limit($page->firstRow.','.$page->listRows)->order('id desc')->select();
		//$data=M('auth_rule')->alias('ar')->field('ar.id, ar.name, ar.title, ar.type, ar.status, ar.pid_one, ar.pid_two, ar.pid_three, am.ModulesName')->join('left join mk_auth_modules as am ON ar.pid_one = am.id')->select();

		$data=$m->page($data['p'],$data['epage'])->order('id desc')->where($where)->order('sort desc')->select();

		//$data=$m->select();
		//$num = $page->firstRow;
		//$res['num']  = $num;
		//$res['show'] = $show;
		$res['data'] = $data;
		$res['count'] = $count;
		return $res;
	}
	
	/**
	 * 获取权限列表二级三级分类名称
	 * Enter description here ...
	 */
//	public function authmodules($data){
//		$mame = M('auth_modules')->where($data)->select();
//		
//		return $mame;
//	}
	public function authmodules(){
		$mame = M('auth_modules')->select();
		foreach ($mame as $key => $val){
			$res[$val['id']] = $val['ModulesName'];
		}
		return $res;
	}
	
	
	/**
	 * 获取权限显示分类级别
	 * Enter description here ...
	 */
	public function authDisplay($data){
		$name = M('auth_modules')->where('pid = '.$data['pid'].' ')->select();
		if (isset($data['name']) || isset($data['title'])){
			$m = D('RuleView');
			if(!empty($data['name'])){
				$where['name'] = $data['name'];
			}
			if(!empty($data['title'])){
				$where['title'] = $data['title'];
			}
			$data = $m->where($where)->order('sort desc')->select();
			return $data;
		}
		return $name;
	}
	
	/**
	 * 获取导航
	 * Enter description here ...
	 */
	public function top_nav($data){
		$res = $this->authnav($data['id']);
		return $res;
	}
	
	public function authnav($id, $returnArray = array()){
	
			$res = M('auth_modules')->where('id = '.$id.' ')->find();
			if(!empty($res['pid'])){
				$returnArray[] = $res;
				$returnArray = $this->authnav($res['pid'], $returnArray);
				$pid = $res['pid'];
			}else{
				$returnArray[] = $res;
				//return $returnArray;
				
			}
			return $returnArray;
		
	}
	
	/**
	 * 查询是否需要导航
	 * 
	 */
	public function navs($data){
		$res = M('auth_rule')->where($data)->select();
		//$res = M()->getlastsql();
		return $res;
	}
	
	
	/**
	 * 权限修改
	 * Enter description here ...
	 * @param unknown_type $data
	 */
	public function authAddEdit($data){
		
		$res['auth'] = M('auth_rule')->where('id = '.$data['id'].'')->find();
		$res['modlist'] = D('auth_modules')->Authsmodules();
		return $res;
		
	}
	
	/**
	 * 删除权限
	 * Enter description here ...
	 * @param unknown_type $data
	 */
	public function authAddDel($data){
		
		$res = M('auth_rule')->where('id = '.$data['id'].'')->delete();
		return $res;
	}
	
	
	/*===============权限添加	end====================*/
	
	
	
	/*=============角色管理	start====================*/
	/**
	 * 角色列表
	 * Enter description here ...
	 */
	public function role($data){
		
		$m=M('auth_group');
		$count=$m->count();
//		$page=new \Think\Page($count,$data['epage']);
//		$page->setConfig('theme','%FIRST% %UP_PAGE% %LINK_PAGE% %DOWN_PAGE% %END% %HEADER%');
//		$show=$page->show();
//		$data=$m->limit($page->firstRow.','.$page->listRows)->order('id desc')->select();		
//		
//		$res['show'] = $show;
		$data=$m->page($data['p'],$data['epage'])->order('id desc')->select();
		
		$res['data'] = $data;
		$res['count'] = $count;
		return $res;
	}
	
	

	public function role_nav($data =array()){
		if(empty($data['id'])){
			$id = 0;
		}else{
			$id = $data['id'];
		}
		$res = M('auth_modules')->where('pid = '.$id.' ')->select();
		return $res;
	}
	
	
	
	/**
	 * 处理角色添加/修改
	 * Enter description here ...
	 */
	public function roleAddHandle($data){
		//return $data;
		if(!empty($data['id'])){
			$res = M('auth_group')->data($data)->where('id = '.$data['id'].'')->save();
			return $res;
		}else{
			//return $data;
			$res = M('auth_group')->add($data);
			return $res;
		}
		
	}
	
	/**
	 * 角色名修改
	 * Enter description here ...
	 */
	public function roleEdit($data){
		
		$res = M('auth_group')->where('id = '.$data['id'].'')->find();
		return  $res;
		
	}
	
	/**
	 *角色删除 
	 * Enter description here ...
	 */
	public function roleDel($data){
		$res = M('auth_group')->where('id = '.$data['id'].'')->delete();
		return $res;
	}
	
	/**
	 * 权限设置
	 * Enter description here ...
	 * @param unknown_type $data
	 */
//	public function permission($data){
//	
//    	//获取所有规则id
//    	$ruleID = M('auth_group')->field('rules')->where($data)->select();
//    	
//    	$rule=D("RuleView");
//    	$mid=$rule->group('pid_one')->select();
//    	return $mid;
//    	foreach ($mid as $v) {
//    		$map['pid_one']=array('in',$v['pid_one']);
//    		//$map['status']='1';    		
//    		$result[$v['ModulesName']]=$rule->where($map)->select();
//    	}
//		
//    	$res['ruleID'] = $ruleID;
//    	$res['result'] = $result;
//		return $res;
//	}
	
	public function permission($data){

	    //获取所有规则id
    	$ruleID = M('auth_group')->field('rules')->where($data)->find();
		//return $ruleID;
    	//$modules = D('auth_modules')->rules();

		$modules = $this->role_nav();

		$result = [];

		foreach ($modules as $v){
			//检验顶级分类是否存在规则
			$wcate['status'] = 1;
			$wcate['pid_one'] = $v['id'];
			//$wcate['pid_one'] = 13;
			$wcate['pid_two'] = array('exp', 'is null');
			$wcate['name'] = array('neq', '#');
			//return $wcate;
			$rule = M('auth_rule')->where($wcate)->select();
			//return $rule;
			$result[$v['ModulesName']]['modules'] = $v;		//分类信息
			$result[$v['ModulesName']]['rule']    = $rule;	//分类信息规则
			//array_merge()  array_intersect()
			//$result[$v['ModulesName']]['subclass'];		//下级信息
			//return $result;
			//默认值使用
			if(!empty($rule)){
				foreach ($rule as $key => $val){
					$default_ModulesName[] = $val['id'];
				}
				$result[$v['ModulesName']]['default'] = $default_ModulesName;
			}
			//return $result;
			//检验二级分类是否存在规则
			$modules_two = $this->role_nav($v);
			//return $modules_two;
			if(!empty($modules_two)){
				foreach ($modules_two as $val){
					$wcate_two['status'] = 1;
					$wcate_two['pid_one'] = $v['id'];
					$wcate_two['pid_two'] = $val['id'];
					$wcate_two['pid_three'] = array('exp', 'is null');
					$wcate_two['name'] = array('neq', '#');
					$rule_two = M('auth_rule')->where($wcate_two)->select();
					$result[$v['ModulesName']]['subclass'][$val['ModulesName']]['modules'] = $val;
					$result[$v['ModulesName']]['subclass'][$val['ModulesName']]['rule'] = $rule_two;
					//$result[$v['ModulesName']]['subclass'][$val['ModulesName']]['subclass'];
					//默认值使用
					
					if(!empty($rule_two)){
						foreach ($rule_two as $k => $vk){
							$default_subclass[] = $vk['id'];
						}
						$result[$v['ModulesName']]['subclass'][$val['ModulesName']]['default'] = $default_subclass;
						if(isset($default_ModulesName)){
							$default_ModulesName = array_unique(array_merge($default_subclass, $default_ModulesName));
						}else{
							$default_ModulesName = $default_subclass;
						}
						$result[$v['ModulesName']]['default'] = $default_ModulesName;
					}
					
					
//					//检验三级分类是否存在规则
					$modules_three = $this->role_nav($val);
					if (!empty($modules_three)){
						foreach ($modules_three as $value){
							$wcate_three['status'] = 1;
							$wcate_three['pid_one'] = $v['id'];
							$wcate_three['pid_two'] = $val['id'];
							$wcate_three['pid_three'] = $value['id'];
							$wcate_three['name'] = array('neq', '#');
							$rule_three = M('auth_rule')->where($wcate_three)->select();
							$result[$v['ModulesName']]['subclass'][$val['ModulesName']]['subclass'][$value['ModulesName']]['modules'] = $value;
							$result[$v['ModulesName']]['subclass'][$val['ModulesName']]['subclass'][$value['ModulesName']]['rule'] = $rule_three;
							//$result[$v['ModulesName']]['subclass'][$val['ModulesName']]['rule'];
							//默认值使用
							
							if(!empty($rule_three)){
								foreach ($rule_three as $ky => $vt){
									$default_three[] = $vt['id'];
								}
								
								$result[$v['ModulesName']]['subclass'][$val['ModulesName']]['subclass'][$value['ModulesName']]['default'] = $default_three;
								//return $default_three;
								
								if(isset($default_ModulesName)){
									$default_ModulesName = array_unique(array_merge($default_three, $default_ModulesName));
									
								}else{
									$default_ModulesName = $default_three;
								}
								//return $default_ModulesName;
								$result[$v['ModulesName']]['default'] = $default_ModulesName;
								if(isset($default_subclass)){
									$default_subclass =  array_unique(array_merge($default_subclass, $default_three));
								}else{
									$default_subclass =  $default_three;
								}
								$result[$v['ModulesName']]['subclass'][$val['ModulesName']]['default'] = $default_subclass;
								
							}
							unset($default_three);	
						}
						
					}
					unset($default_subclass);
				}
			}
			
			
			unset($default_ModulesName);
			
		}
		$modules['result'] = $result;
		$modules['ruleID'] = $ruleID;
		return $modules;
	}
	
	
	
	/**
	 * 更新权限设置
	 * Enter description here ...
	 */
	public function authUpdate($data){
		$where['id'] = $data['id']; 
		$num = M('auth_group')->where($where)->save($data);
		return $num;
	}
	
	
	/**
	 * 成员管理
	 * Enter description here ...
	 * @param unknown_type $data
	 */
	public function member($data){
		$rdate = M('auth_group')->field('r.id, r.title, au.id as uid, au.name')->alias('r')->join(array('left join mk_auth_group_access AS aga ON r.id = aga.group_id', 'left join mk_manager_list AS au ON aga.uid = au.id'))->where('r.id = '.$data['id'].'')->select();
		return  $rdate;
	}
	
	/**
	 * 成员编辑页
	 * Enter description here ...
	 */
	public function memberEdit(){
		$rdata = M('auth_group')->field('id, title')->select();
		return $rdata;
	}
	/**
	 * 成员编辑页处理提交
	 * Enter description here ...
	 * @param unknown_type $data
	 */
	public function memberHandle($data){
		$res = M('auth_group_access')->data($data)->where('uid = '.$data['uid'].'')->save();
		return $res;
	}
	
	/**
	 * 成员删除
	 * Enter description here ...
	 * @param unknown_type $where
	 */
	public function memberDel($where){
		$res = M('auth_group_access')->where($where)->delete();
		return $res;
	}
	
	
	/*=============角色管理	end====================*/

}