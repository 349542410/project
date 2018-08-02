<?php
namespace Admin\Controller;
use Think\Controller;
class AdminAuthController extends AdminbaseController{
	public $client;
	public $writes;
	
	function __construct(){
		parent::__construct();
		vendor('Hprose.HproseHttpClient');
        $this->client = new \HproseHttpClient(C('RAPIURL').'/AdminAuth');		//读取、查询操作
        //$this->writes = new \HproseHttpClient(C('WAPIURL').'/AdminCollectPoint');		//添加，修改 
        
	}

	
	/*==============员工 	start=======================*/
	//员工列表
	public function index_authNav(){
		//$a = $this->top_nav;
		
		$data['epage'] = C('EPAGE');
		$data['p'] = I('get.p');
		//取得用户信息
		$res = $this->client->userlist($data);
		$count = $res['count'];
		$page=new \Think\Page($count,$data['epage']);
		//%FIRST% 表示第一页的链接显示 
		//%UP_PAGE% 表示上一页的链接显示 
		//%LINK_PAGE% 表示分页的链接显示 
		//%DOWN_PAGE% 表示下一页的链接显示 
		//%END% 表示最后一页的链接显示 
		
		$page->setConfig('theme','%FIRST% %UP_PAGE% %LINK_PAGE% %DOWN_PAGE% %END% %HEADER%');
		$page -> setConfig('prev', '上一页');
		$page -> setConfig('next','下一页');
		$page -> setConfig('last','末页');
		$page -> setConfig('first','首页');
		
		$show = $page->show();			
		//$top_nav = $this->top_nav;
		
		//$three_nav = $this->three_nav;
		//$this->assign('top_nav', $top_nav);
		//$this->assign('three_nav', $three_nav);
		//print_r($res['list']);
		//exit;
		$this->assign('data', $res['list']);
		$this->assign('page', $show);
		
    	//$list = M('admin_user')->field('user_id, user_name, email, add_time, last_login')->order('user_id DESC')->select();
    	//echo $qqq = M('admin_user')->getLastSql();
    	//exit;
    	
    	
	    //foreach ($list AS $key=>$val)
	    //{
	    //    $list[$key]['add_time']     = date('Y-m-d H:i:s', $val['add_time'] += 8*3600);
	    //    $list[$key]['last_login']   = date('Y-m-d H:i:s', $val['last_login'] += 8*3600);
	    //}
	   
	    //$this->assign('data',$list);
	    $three_view = $this->filesname;
	    //if('no' != $three_view  && !empty($three_view)){
		    $this->assign('three_nav', $this->three_nav);
		    $this->assign('ModulesName', $this->ModulesName);
			//$content = $this->fetch('Public:'.$three_view);
		    //$this->meun = $content;
	    //}
	    $this->display('user_index');
	}

	//用户修改
	public function useredit(){
		$data['id'] = I('get.uid');
		if(empty($data['id'])){
			$this->error('该用户不存在！');
			exit;
		}

		$user = $this->client->useredit($data);

		$group_id = empty(I('group_id')) ? empty($user['res']['group_id']) ? '' : $user['res']['group_id'] : I('group_id');
		$this->assign('group_id', $group_id);
		if(!empty($group_id)){
			$group_type = I('group_type');
	    	//角色id
	    	$group['id']=$where['id']=$group_id;
	    	//角色名称
	    	$group['name']= iconv("gb2312","utf-8",$_GET["name"]);

	    	$res = $this->client->permission($where);

	    	if(empty($user['res']['userRule'])){
	    		$group_type = 1;
	    	}
	    	if(empty($group_type)){
				$ruleid = explode(',', $user['res']['userRule']);
	    	}else{
	    		$ruleid = explode(',', $res['ruleID']['rules']);
	    	}
	    	
	    	$this->ruleid = $ruleid;
	    	$this->group=$group;
	    	$this->result=$res['result'];
	    	$this->ruleID=$res['ruleID'];		
		}


		$this->assign('data', $user['data']);
		$this->assign('res', $user['res']);
		$this->display();
	}
	
	//用户添加角色
	public function usergroup(){
	    $data['uid']=I('post.id');
    	$data['group_id']=I('group_id');
		$arr=I('rule');
		if(empty($data['uid']) || empty($data['group_id'])){
			$rew['status'] = 0;
    		$rew['data']['strstr'] = '参数错误';
    		$this->ajaxReturn($rew);
	    	exit; 
		}
    	//$data['userRule']=implode(',',$arr);
    	//$data['id'] = I("groupID"); 
    	//print_r($data);
    	//exit;
    	$arr = trim($arr, ',');
    	$arr = trim($arr, ' ');
    	$arr = trim($arr, '，');
    	$data['userRule'] = $arr;  	
    	
    	$res = $this->client->usergroup($data);
		
    	if($res){
     		$rew['status'] = 1;
    		$rew['data']['strstr'] = '保存成功！';
    		$rew['data']['url'] = U('AdminAuth/index_authNav');
    		$this->ajaxReturn($rew);
	    	exit;   
 		}else{
     		$rew['status'] = 0;
    		$rew['data']['strstr'] = '修改失败！';
    		$this->ajaxReturn($rew);
	    	exit;   
 			
 		} 	
//    	if($res){
//    		$this->success('保存成功！',U('AdminAuth/index_authNav'));
//    		//$this->redirect('AdminAuth/index_authNav', 2, '保存成功');
//    	}else{
//    		$this->error('保存失败！');
//    		//$this->redirect('AdminAuth/useredit', 2, '保存失败');
//    	}    	
//		if($res){
//    		$result = array('state'=>'yes', 'msg'=>'保存成功');
//    	}else{
//    		$result = array('state'=>'no', 'msg'=>'保存失败');
//    	}			
//		
//		$this->ajaxReturn($result);	
	
	}
	//用户删除
	public function userdelete(){
		$uid = I('get.id');
		if(!empty($uid)){
			//$res = M('admin_user')->where('user_id = '.$uid.'')->delete();
			$where['id'] = $uid;
			$res = $this->client->userdelete($where);
			if($res){
	     		$rew['status'] = 1;
	    		$rew['data']['strstr'] = '删除成功！';
	    		//$rew['data']['url'] =U('AdminAuth/index_authNav_authDisplay');
	    		$this->ajaxReturn($rew);
		    	exit;   			
			}else{
	     		$rew['status'] = 0;
	    		$rew['data']['strstr'] = '删除失败！';
	    		//$rew['data']['url'] =U('AdminAuth/index_authNav_authDisplay');
	    		$this->ajaxReturn($rew);
		    	exit; 			
			}
			
		}else{
	     		$rew['status'] = 0;
	    		$rew['data']['strstr'] = '不存在该用户';
	    		//$rew['data']['url'] =U('AdminAuth/index_authNav_authDisplay');
	    		$this->ajaxReturn($rew);
		    	exit; 		
		}
	}

	
	/**
	 * 管理员揽收点列表
	 * Enter description here ...
	 */
	public  function user_point(){
		//$adid = session('admin')['adid'] ? session('adid') : 1;	//修改权限后  adid 要修改
        //$admin = $value['admin']['adid'];
        //echo $adid;
		//exit;
		$adid = I('get.uid');
		if(empty($adid)){
			$this->error('该管理员不存在！');
			exit;
		}
		//取得揽收点信息 /当前揽收点信息
		$data['id'] = $adid; 		
		$data['aduid'] = session('admin')['adid'];
		$data['administrator'] = C('ADMINISTRATOR');
		
		$res = $this->client->user_point($data);
		//print_r($res);
		//exit;
		//当前管理员揽收点
		if(isset($res['point_id_all'])){
			$this->assign('point_all', $res['point_id_all']);
		}
		
		$point = $res['point'];
		$group = $res['group'];
		$user_point = explode(',', $group['point_id']);
		//print_r($group);
		//exit;
		$this->assign('point', $point);
		$this->assign('res', $group);
		$this->assign('user_point', $user_point);
		$this->display();
	}
	
	
	
	/**
	 * 管理员揽收点保存
	 * Enter description here ...
	 */
	public function pointhaddent(){
		$uid = I('post.id');
		$point = I('post.rule');
		if(empty($uid) || empty($point)){
			$result = array('state'=>'no', 'msg'=>'参数错误');
			$this->ajaxReturn($result);	
			exit;
		}
		
		if($point[0] == 0){
			$point_id = 'ALL';
		}else{
			$point_id = implode(',', $point);
			$point_id = trim($point_id, ',');
		}
		$data['uid'] = $uid;
		$data['point_id'] = $point_id;
		
		$res = $this->client->pointhaddent($data);
		if(!empty($res['errorstr'])){
			$result = array('state'=>'no', 'msg'=>$res['errorstr']);
			$this->ajaxReturn($result);
			exit;
		}		
//		if($res){
//			$this->success('更新成功！', U('AdminAuth/index_authNav'));
//		}else{
//			$this->error('更新失败！');
//		}
//		
		if($res){
    		$result = array('state'=>'yes', 'msg'=>'更新成功');
    	}else{
    		$result = array('state'=>'no', 'msg'=>'更新失败');
    	}			
		
		$this->ajaxReturn($result);	
	
	}
	
	/**
	 * 员工分配中转路线
	 * Enter description here ...
	 */
	public function transit(){
		$adid = I('get.uid');
		if(empty($adid)){
			$this->error('该管理员不存在！');
			exit;
		}
		//取得揽收点信息 /当前揽收点信息
		$data['id'] = $adid;
		$data['aduid'] = session('admin')['adid'];
		$data['administrator'] = C('ADMINISTRATOR');
				
		
		$res = $this->client->transit($data);

		//当前管理员揽收点
		$point = $res['transit'];
		$group = $res['group'];
		$user_point = explode(',', $group['transit_id']);
		if(isset($res['transit_id_all'])){
			$this->assign('transit_all', $res['transit_id_all']);
		}	
		$this->assign('point', $point);
		$this->assign('res', $group);
		$this->assign('user_point', $user_point);
		$this->display('user_transit');		
	}
	

	/**
	 * 管理员揽收点保存
	 * Enter description here ...
	 */
	public function transithaddent(){
		$uid = I('post.id');
		$point = I('post.rule');
		if(empty($uid) || empty($point)){
			$result = array('state'=>'no', 'msg'=>'参数错误');
			$this->ajaxReturn($result);	
			exit;
		}
		$point_id = implode(',', $point);
		$point_id = trim($point_id, ',');
		$data['uid'] = $uid;
		if($point[0] == 0){
			$point_id = 'ALL';
		}
		$data['transit_id'] = $point_id;
		
		$res = $this->client->transithaddent($data);
		
		if(!empty($res['errorstr'])){
			//$this->success($res['errorstr'], U('AdminAuth/index_authNav'));
			//exit;
			$result = array('state'=>'no', 'msg'=>$res['errorstr']);
			$this->ajaxReturn($result);
			exit;
		}
		
//		if($res){
//			$this->success('更新成功！', U('AdminAuth/index_authNav'));
//		}else{
//			$this->error('更新失败！');
//		}
		if($res){
    		$result = array('state'=>'yes', 'msg'=>'更新成功');
    	}else{
    		$result = array('state'=>'no', 'msg'=>'更新失败');
    	}			
		
		$this->ajaxReturn($result);	
		
		
	}	
	
	
	/*=============员工		end=========================*/
	/*=============权限组模块名	start======================*/
	//权限组模块名
	public function index_authNav_modules(){
		
		$data['epage'] = C('EPAGE');
		$data['p'] = I('get.p');
		$res = $this->client->modules($data);
		//print_r($res);
		//$count = $res['count'];
		//$page=new \Think\Page($count,$data['epage']);
		//%FIRST% 表示第一页的链接显示 
		//%UP_PAGE% 表示上一页的链接显示 
		//%LINK_PAGE% 表示分页的链接显示 
		//%DOWN_PAGE% 表示下一页的链接显示 
		//%END% 表示最后一页的链接显示 
		
		//$page->setConfig('theme','%FIRST% %UP_PAGE% %LINK_PAGE% %DOWN_PAGE% %END% %HEADER%');
		//$show=$page->show();
		
		//$this->assign('page',$show);
		
		$this->assign('list', $res['list']);

		
		$three_view = $this->filesname;
		
	    //if('no' != $three_view  && !empty($three_view)){
		    $this->assign('three_nav', $this->three_nav);
		    $this->assign('ModulesName', $this->ModulesName);
			//$content = $this->fetch('Public:'.$three_view);
		    //$this->meun = $content;
	    //}		
		
		//$this->assign('page', $res['show']);
		//$mlist = M('auth_modules')->select();
		//$this->assign('list',$mlist);
		$this->display('modules');
	}
	
	//权限组模块名添加
	public function modules_add(){
		$res = $this->client->modules_add();
		$id = I('id');
		if(empty($id)){
			$id = 0;
		} 
		$this->assign('id', $id);
		$this->assign('cateList', $res);
		$this->display('modules_add');
		
	}
	//处理模块组提交
	public function modules_handle(){
		$data['ModulesName'] = I('post.name');
		$data['pid'] = empty(I('post.modules_id')) ? 0 : I('post.modules_id');
		if(empty($data['ModulesName'])){
			$rew['status'] = false;
    		$rew['data']['strstr'] = '参数错误';
    		$this->ajaxReturn($rew);
	    	exit; 
		}
		$id = I('post.id');
		if(empty($id)){
			
			
			$res = $this->client->modules_handle($data);
//			if($res){
//				$this->success('添加成功,<a href="'.U('AdminAuth/modules_add').'" >继续添加</a>',U('AdminAuth/index_authNav_authDisplay'),3);
//			}else{
//				$this->error('添加失败！');
//			}
			if($res){
	     		$rew['status'] = 1;
	    		$rew['data']['strstr'] = '添加成功！';
	    		//$rew['data']['url'] =U('AdminAuth/index_authNav_authDisplay');
	    		$this->ajaxReturn($rew);
		    	exit;   
	 		}else{
	     		$rew['status'] = 0;
	    		$rew['data']['strstr'] = '添加失败！';
	    		$this->ajaxReturn($rew);
		    	exit;   
	 			
	 		}
		}else{
			$data['id'] = $id;
			$default_url = I('post.default_url');
			//echo $default_url;
			//exit;
			if(!empty($default_url)){
				$data['default_url'] = 1;	//默认URL
				$data['rule_id'] = $default_url;
			}
			//print_r($data);
			//exit;
			$res = $this->client->modules_handle($data);
			//print_r($res);
			//exit;
			if(!empty($res['errorstr'])){
				//$this->error($res['errorstr']);
				//exit;
				$rew['status'] = 0;
	    		$rew['data']['strstr'] = $res['errorstr'];
	    		$this->ajaxReturn($rew);
		    	exit;   
			}
//			if($res){
//				$this->success('修改成功！', U('AdminAuth/index_authNav_authDisplay'),1);
//			}else{
//				$this->error('修改失败！');
//			}
			if($res){
	     		$rew['status'] = 1;
	    		$rew['data']['strstr'] = '修改成功！';
	    		//$rew['data']['url'] =U('AdminAuth/index_authNav_authDisplay');
	    		$this->ajaxReturn($rew);
		    	exit;   
	 		}else{
	     		$rew['status'] = 0;
	    		$rew['data']['strstr'] = '修改失败！';
	    		$this->ajaxReturn($rew);
		    	exit;   
	 			
	 		}
		}
	}
	//编辑模块组
	public function modulesEdit(){
		$id['id'] = I('get.id');
		$data = $this->client->modulesEdit($id);
		$cateList = $data['res'];
		$pid = I('pid');
		if(empty($pid)){
			$map['pid_one'] 	= $id['id'];
			$map['pid_two'] 	= array('exp', 'is null');
			$map['pid_three'] = array('exp', 'is null');
			
		}else{
			$where['pid_two'] = $id['id'];
			$where['pid_three'] = array(array('exp', 'is null')); 
			$map['_complex'] = $where;
			$map['_logic'] = 'OR';
			$map['pid_three'] = $id['id'];
			//$navs = $this->client->navs($where);
						
		}
		$navs = $this->client->navs($map);
		if(!empty($navs)){
			$this->assign('navs', $navs);
		}
		
		$totype = 'edit';
		$this->assign('totype', $totype);
		$this->assign('id', $pid);
		$this->assign('cateList',$cateList);
		$this->assign('data', $data['data']);
		$this->display('modules_add');
	}
	//删除模块组名
	public function modulesDelete(){
		$data['id'] = I('get.id');
		if(empty($data['id'])){
			$rew['status'] = 0;
    		$rew['data']['strstr'] = '参数错误';
    		$this->ajaxReturn($rew);
	    	exit;   
 			
		}
		$res = $this->client->modulesDelete($data);
		
		if(!empty($res['errorstr'])){
			//$this->error($res['errorstr']);
			$rew['status'] = 0;
    		$rew['data']['strstr'] = $res['errorstr'];
    		$this->ajaxReturn($rew);
	    	exit;   
		}
		
//		if($res > 0){
//			$this->success('删除成功！',U('AdminAuth/index_authNav_modules'),1);
//		}else{
//			$this->error('删除失败！');
//		}
		if($res){
     		$rew['status'] = 1;
    		$rew['data']['strstr'] = '删除成功！';
    		$rew['data']['url'] =U('AdminAuth/index_authNav_authDisplay');
    		$this->ajaxReturn($rew);
	    	exit;   
 		}else{
     		$rew['status'] = 0;
    		$rew['data']['strstr'] = '删除失败！';
    		$this->ajaxReturn($rew);
	    	exit;   
 			
 		}		
		
	}
	
	/*=============权限组模块名 	end======================*/
	
	/*=============权限添加	start=====================*/
	//权限添加
	public function auth_add(){
		
		$modlist = $this->client->auth_add();
		$data['checked'] = I('mid');
		$this->assign('data', $data);
		//$modlist = M('auth_modules')->select();
		$this->assign('modlist', $modlist);
		$this->display('auth_add');
	}
	
	//处理权限添加
	public function authAddHandle(){
		
		$data['name']=I('ruleName');
		$data['title']=I('ruleTitle');
        //过滤方法必须为空,否则验证时会出错
		$data['condition']=I('post.condition','','');
		$data['status']=I('status');
		$data['mid']=I('modules');
		$data['sort'] = I('sort');
		$data['request_type'] = I('post.request_type');
		$id = I('post.id');
		if(empty($data['mid'])){
//			$this->error('请选择所属分类组');
//			exit;
     		$rew['status'] = 0;
    		$rew['data']['strstr'] = '请选择所属分类组';
    		$this->ajaxReturn($rew);
	    	exit;  
		}
		
		if(!empty($id)){
			$data['id'] = $id;
			
			$res = $this->client->authAddHandle($data);
			//print_r($res);
			//exit;
//			if($res){
//				$this->success('权限修改成功！',U('AdminAuth/index_authNav_authDisplay'));
//			}else{
//				$this->error('权限修改失败！');
//			}
			
			if($res){
	     		$rew['status'] = 1;
	    		$rew['data']['strstr'] = '权限修改成功！';
	    		//$rew['data']['url'] =U('AdminAuth/index_authNav_authDisplay');
	    		$this->ajaxReturn($rew);
		    	exit;   
	 		}else{
	     		$rew['status'] = 0;
	    		$rew['data']['strstr'] = '权限修改失败！';
	    		$this->ajaxReturn($rew);
		    	exit;   
	 			
	 		}	
			
			
		}else{
			
			$res = $this->client->authAddHandle($data);
			
//			if($res){
//				$this->success('权限添加成功！', U('AdminAuth/index_authNav_authDisplay'));
//			}else{
//				$this->error('权限添加失败！');
//			}
			if($res){
	     		$rew['status'] = 1;
	    		$rew['data']['strstr'] = '权限添加成功！';
	    		//$rew['data']['url'] =U('AdminAuth/index_authNav_authDisplay');
	    		$this->ajaxReturn($rew);
		    	exit;   
	 		}else{
	     		$rew['status'] = 0;
	    		$rew['data']['strstr'] = '权限添加失败！';
	    		$this->ajaxReturn($rew);
		    	exit;   
	 			
	 		}	
			
			
			
		}
	}
	//权限列表
	public function index_authNav_authList(){
		
		$data['epage'] = C('EPAGE');
		$data['p'] = I('get.p');
		$data['id'] = I('get.id'); 
		//print_r($data);
		//exit;
		$res = $this->client->authList($data);
		//print_r($res);
		//exit;
		$count = $res['count'];
		$page=new \Think\Page($count,$data['epage']);
		//%FIRST% 表示第一页的链接显示 
		//%UP_PAGE% 表示上一页的链接显示 
		//%LINK_PAGE% 表示分页的链接显示 
		//%DOWN_PAGE% 表示下一页的链接显示 
		//%END% 表示最后一页的链接显示 
		$page->setConfig('prev', "上一页");//上一页
		$page->setConfig('next', '下一页');//下一页
		$page->setConfig('first', '首页');//第一页
		$page->setConfig('last', "末页");//最后一页
//		$page->setConfig ( 'theme', '%FIRST% %UP_PAGE% %LINK_PAGE% %DOWN_PAGE% %END% %HEADER%' );
//    				
		$page->setConfig('theme','%FIRST% %UP_PAGE% %LINK_PAGE% %DOWN_PAGE% %END% %HEADER%');
		$show=$page->show();

		$modules = $this->client->authmodules();
		foreach ($res['data'] as $key => $value){
			if(!empty($value['pid_two'])){
				$res['data'][$key]['pid_two_name'] = $modules[$value['pid_two']];
			}
			if(!empty($value['pid_three'])){
				$res['data'][$key]['pid_three_name'] = $modules[$value['pid_three']];
			}
		}
				
		
		
		
		
		$rek['id'] = '';
		$role_nav = $this->client->role_nav($rek);
		$power['add_category'] = 'on';
		//$power['line'] = I('get.line');
		//$power['id'] = I('get.id');
		if(!empty(I('get.line'))){
			$rew['id'] = I('get.line');
			$role_nav_two = $this->client->role_nav($rew);
			$this->assign('role_nav_two', $role_nav_two);
		}
		$this->assign('role_nav', $role_nav);
		$this->assign('page',$show);
		//$this->assign('num',$res['num']);
		$this->assign('data',$res['data']);
		
		$three_view = $this->filesname;
		//if('no' != $three_view && !empty($three_view)){
		    $this->assign('three_nav', $this->three_nav);
		    $this->assign('ModulesName', $this->ModulesName);
			//$content = $this->fetch('Public:'.$three_view);
		    //$this->meun = $content;
	    //}	
		
		
		$this->display('authList');
	}
	
	
	public function role_nav(){
		$data['id'] = I('get.line');
		$role_nav = $this->client->role_nav($rek);
		
		$role_nav_two = $this->client->role_nav($data);
		if(empty($role_nav_two)){
			$this->redirect('AdminAuth/index_authNav_authList', array('id' => $data['id'], 'line' => $data['id']));
			exit;
		}
		$power['add_category'] = 'on';
		$this->assign('role_nav', $role_nav);
		$this->assign('role_nav_two', $role_nav_two);
		$this->assign('power', $power);
		$three_view = $this->filesname;
		//if('no' != $three_view && !empty($three_view)){
		    $this->assign('three_nav', $this->three_nav);
		    $this->assign('ModulesName', $this->ModulesName);
			//$content = $this->fetch('Public:'.$three_view);
		    //$this->meun = $content;
	    //}	
		$this->display('authList');
	}
	
	
	
	//权限添加修改
	public function authAddEdit(){
		$id = I('get.id');
		$data['id'] = $id; 
		$data = $this->client->authAddEdit($data);
		$data['auth']['checked'] = !empty($data['auth']['pid_three']) ? $data['auth']['pid_three'] : !empty($data['auth']['pid_two']) ?  $data['auth']['pid_two'] : $data['auth']['pid_one'];
		//print_r($data['auth']);
		//exit;
		$this->assign('data', $data['auth']);
		$this->assign('modlist', $data['modlist']);
		$this->display('auth_add');
	}
	
	//权限添加删除
	public function authAddDel(){
		$data['id'] = I('get.id');
		if(empty($data['id'])){
			$rew['status'] = 0;
    		$rew['data']['strstr'] = '删除规则不存在';
    		//$rew['data']['url'] = U('AdminAuth/index_authNav_role');
    		$this->ajaxReturn($rew);
	    	exit;   
 			
		}
		$res = $this->client->authAddDel($data);
//		if($res){
//			$this->success('权限删除成功！', U('AdminAuth/index_authNav_authDisplay'));
//		}else{
//			$this->error('权限删除失败！');
//		}
		if($res){
	     		$rew['status'] = 1;
	    		$rew['data']['strstr'] = '权限删除成功！';
	    		//$rew['data']['url'] = U('AdminAuth/index_authNav_role');
	    		$this->ajaxReturn($rew);
		    	exit;   
	 		}else{
	     		$rew['status'] = 0;
	    		$rew['data']['strstr'] = '权限删除失败！';
	    		//$rew['data']['url'] = U('AdminAuth/index_authNav_role');
	    		$this->ajaxReturn($rew);
		    	exit;   
	 			
	 	}			
				
		
		
	}
	
	//权限显示顶级
	public function index_authNav_authDisplay(){
		$data['pid'] = 0;
		
		//新增查询功能180502
		$rule_name = I('get.rule_name');
		if(!empty($rule_name)){
			$rule_name = trim($rule_name, ' ');
			$data['title'] = array('like','%'.$rule_name.'%'); 
		}
		$rule_url = I('get.rule_url');
		if(!empty($rule_url)){
			$data['name'] = $rule_url;
		}
		
		//print_r($data);
		//exit;
		$nav_one = $this->client->authDisplay($data);
		
		//exit;
		//检验是否需要设置默认导航
//		foreach ($nav_one as $key => $val){
//			$arr[] = $val['id'];
//		}
//		$where['pid_one'] = array('in', $arr);
//		$where['pid_two'] = array('exp', 'is null'); 
//		$where['pid_three'] = array('exp', 'is null'); 
//		$navs = $this->client->navs($where);
//		foreach ($navs as $k =>$v){
//			$n[$v['pid_one']] = 1;	//判断是否可以设置默认URL
//		}
//		$this->assign('n', $n);
		$this->assign('list', $nav_one);
		$three_view = $this->filesname;
		//if('no' != $three_view && !empty($three_view)){
			
		    $this->assign('three_nav', $this->three_nav);
		    $this->assign('ModulesName', $this->ModulesName);
			//$content = $this->fetch('Public:'.$three_view);
		    //$this->meun = $content;
	    //}		
	    if(!empty($rule_name) || !empty($rule_url)){
			$this->assign('data', $nav_one);
			$this->display('authNav_two_list');		    
	    }else{
	    	$this->display('authDisplay');
	    }
		//$this->display('authDisplay');
	}
	//权限管理二级三级显示
	public function authNav_two(){
		$id = I('id');
		$data['pid'] = $id;
		$nav_two = $this->client->authDisplay($data);
		$rek['id'] = $id;
		$top_navs = $this->client->top_nav($rek);
		$top_navs = array_reverse($top_navs);
		
//		print_r($top_navs);
//		exit;


		usort($top_nav, function($a, $b){
			return ($a['pid'] > $b['pid']) ? 1 : -1; 	
		});
		$this->assign('top_navs', $top_navs);
		
		//$top_nav = rsort($top_nav);
		//print_r($top_nav);
		//exit;
//		if(!empty($nav_two)){
//			//检验是否需要设置默认导航
//			foreach ($nav_two as $key => $val){
//				$arr[] = $val['id'];
//			}
//			//print_r($arr);
//			//exit;
//			$where['pid_two'] = array('in', $arr);
//			$where['pid_three'] = array(array('exp', 'is null')); 
//			$map['_complex'] = $where;
//			$map['_logic'] = 'OR';
//			$map['pid_three'] = array('in', $arr);
//			//$navs = $this->client->navs($where);
//			$navs = $this->client->navs($map);
//			
//			foreach ($navs as $k =>$v){
//				$n[$v['pid_two']] = 1;	//判断是否可以设置默认URL
//				$ns[$v['pid_three']] = 1;	//判断是否可以设置默认URL
//			}
//
//			$this->assign('n', $n);
//			$this->assign('ns', $ns);
//		}else{
//			$ns[$id] = 1;
//
//		}
		//如果 $nav_two 为空  则证明是在最底级分类  查询规则
		if(empty($nav_two)){
			$rules['id'] = $id;
			$rules['epage'] = C('EPAGE');
			$rules['p'] = I('get.p');
			$rule = $this->client->authList($rules);
			
			$modules = $this->client->authmodules();
			foreach ($rule['data'] as $key => $value){
				if(!empty($value['pid_two'])){
					$rule['data'][$key]['pid_two_name'] = $modules[$value['pid_two']];
				}
				if(!empty($value['pid_three'])){
					$rule['data'][$key]['pid_three_name'] = $modules[$value['pid_three']];
				}
			}
			if(empty($rule['data'])){
				$judgment = '1';
				
			}else{
				$judgment = '2';
			} 
			
		}else{
			$judgment = '3';
		}
		$a = $this->top_nav;
		
		//$three_view = $this->filesname;
		//print_r( $this->top_nav);
		//exit;
		//if('no' != $three_view && !empty($three_view)){
		    $this->assign('three_nav', $this->three_nav);
		    $this->assign('ModulesName', $this->ModulesName);
			//$content = $this->fetch('Public:'.$three_view);
		  //  $this->meun = $content;
	    //}				
		
		
		$this->assign('id', $id);
		$this->assign('judgment', $judgment);
		if(!empty($nav_two)){
			$this->assign('list', $nav_two);
			$this->display('authNav_two');
		}
		
		if(isset($rule)){
		
			$this->assign('data', $rule['data']);
			$this->display('authNav_two_list');		
		}
		
		
		
	}
	
	/*=============权限添加 	end=====================*/
	
	/*=============角色管理	start====================*/
	
	//角色列表
	public function index_authNav_role(){
		
		$data['epage'] = C('EPAGE');
		$data['p'] = I('get.p');
		$res = $this->client->role($data);
		$count = $res['count'];
		$page=new \Think\Page($count,$data['epage']);
		$page->setConfig('theme','%FIRST% %UP_PAGE% %LINK_PAGE% %DOWN_PAGE% %END% %HEADER%');
		$show=$page->show();
		$res['show'] = $show;		
		
		$this->assign('page',$res['show']);
		//$this->assign('num',$res['num']);
		$this->assign('data',$res['data']);
		
		$three_view = $this->filesname;
		//if('no' != $three_view && !empty($three_view)){
		    $this->assign('three_nav', $this->three_nav);
		    $this->assign('ModulesName', $this->ModulesName);
			//$content = $this->fetch('Public:'.$three_view);
		    //$this->meun = $content;
	    //}			
		$this->display('role');
	}

	
	//角色添加
	public function roleAdd(){
		
		$this->display();
	}
	//处理角色名添加
	public function roleAddHandle(){
	    $data['title']=I('groupName');
    	$data['describe']=I('describe');
    	if(empty($data['title']) || empty($data['describe'])){
    		$rew['status'] = 0;
    		$rew['data']['strstr'] = '参数错误';
    		//$rew['data']['url'] = U('AdminAuth/index_authNav_role');
    		$this->ajaxReturn($rew);
	    	exit; 
    	}
    	$data['status']=I('status');
    	$id = I('post.id');
    	
    	
    	
    	//$m=M("auth_group");
    	if(!empty($id)){
    		$data['id'] = $id;
    		$res = $this->client->roleAddHandle($data);
    		
//    		if($res){
//    			
//    			$this->success('修改成功！',U('AdminAuth/index_authNav_role'));
//    		}else{
//    			$this->error('修改失败！');
//    		}
	    	if($res){
	     		$rew['status'] = 1;
	    		$rew['data']['strstr'] = '修改成功！';
	    		$rew['data']['url'] = U('AdminAuth/index_authNav_role');
	    		$this->ajaxReturn($rew);
		    	exit;   
	 		}else{
	     		$rew['status'] = 0;
	    		$rew['data']['strstr'] = '修改失败！';
	    		$rew['data']['url'] = U('AdminAuth/index_authNav_role');
	    		$this->ajaxReturn($rew);
		    	exit;   
	 			
	 		}
    		
    		
    	}else{
    		//print_r($data);
    		//exit;
    		$res = $this->client->roleAddHandle($data);
    		
//	    	if($res){
//	    		$this->success('添加成功！', U('AdminAuth/index_authNav_role'));
//	    	}else{
//	    		$this->error('添加失败！');
//	    	}
	    	if($res){
	     		$rew['status'] = 1;
	    		$rew['data']['strstr'] = '添加成功！';
	    		$rew['data']['url'] = U('AdminAuth/index_authNav_role');
	    		$this->ajaxReturn($rew);
		    	exit;   
	 		}else{
	     		$rew['status'] = 0;
	    		$rew['data']['strstr'] = '添加失败！';
	    		$rew['data']['url'] = U('AdminAuth/index_authNav_role');
	    		$this->ajaxReturn($rew);
		    	exit; 
	 		}
    			    	
	    	
	    	
    	}
    	
    	
	}
	//角色名修改
	public function roleEdit(){
		$data['id'] = I('get.id');
		
		$data = $this->client->roleEdit($data);
		$this->assign('data', $data);
		$this->display('roleAdd');
	}
	//角色删除
	public function roleDel(){
		$data['id'] = I('get.id');
		if(empty($data['id'])){
			$rew['status'] = 0;
    		$rew['data']['strstr'] = '参数错误';
    		$this->ajaxReturn($rew);
	    	exit;
		}
		$res = $this->client->roleDel($data);
		
	    if($res){
     		$rew['status'] = 1;
    		$rew['data']['strstr'] = '删除成功！';
    		$this->ajaxReturn($rew);
	    	exit;   
 		}else{
     		$rew['status'] = 0;
    		$rew['data']['strstr'] = '删除失败！';
    		$this->ajaxReturn($rew);
	    	exit; 
 		}
	}
	//权限设置
	public function permission(){
    	//角色id
    	$group['id']=$where['id']=I('get.id');
    	//角色名称

    	$group['name']= (string)iconv("gb2312","utf-8",$_GET["name"]);
    	$res = $this->client->permission($where);

		$ruleid = explode(',', $res['ruleID']['rules']);

    	$this->ruleid = $ruleid;
    	$this->group=$group;
    	$this->result=$res['result'];
    	$this->ruleID=$res['ruleID'];

		$three_view = $this->filesname;
		//if('no' != $three_view && !empty($three_view)){
		    $this->assign('three_nav', $this->three_nav);
		    $this->assign('ModulesName', $this->ModulesName);
			//$content = $this->fetch('Public:'.$three_view);
		    //$this->meun = $content;
	    //}	
	        	
    	$this->display();
	}
	//更新权限设置
	public function authUpdate(){
		$arr=I('rule');
		$arr = trim($arr, ',');
		$arr = trim($arr, ' ');
		$arr = trim($arr, '，');
		
    	//$where['id']=I("groupID");
    	//$data['rules']=implode(',',$arr);
    	$data['rules'] = $arr; 
		$data['id'] = I("groupID");
    	$num = $this->client->authUpdate($data);
    	//更新,返回影响行数
    	//$num=M('auth_group')->where($where)->save($data);
    	
//    	if($num){
//    		$this->success('权限设置成功！',U('AdminAuth/index_authNav_role'),3);
//    	}else{
//    		$this->error('权限设置失败！');
//    	}
		if($num){
     		$rew['status'] = 1;
    		$rew['data']['strstr'] = '权限设置成功！';
    		$rew['data']['url'] = U('AdminAuth/index_authNav_role');
    		$this->ajaxReturn($rew);
	    	exit;   
 		}else{
     		$rew['status'] = 0;
    		$rew['data']['strstr'] = '权限设置失败！';
    		$this->ajaxReturn($rew);
	    	exit; 
 		}    	
    	
    	
	}
	//成员管理
	public function member(){
		$data['id'] = I('get.id');
		$rdata = $this->client->member($data);
		$three_view = $this->filesname;
		//if('no' != $three_view && !empty($three_view)){
		    $this->assign('three_nav', $this->three_nav);
		    $this->assign('ModulesName', $this->ModulesName);
			//$content = $this->fetch('Public:'.$three_view);
		    //$this->meun = $content;
	    //}	
	    //print_r($rdata);
	    //exit;
		$this->assign('rdate', $rdata);
		$this->display();
	}
	//成员编辑页
	public function memberEdit(){
		$uid = I('get.uid');
		$rid = I('get.id');
		$user_name = iconv('gb2312', 'utf-8', $_GET['user_name']);
		
		$rdata = $this->client->memberEdit();
		$this->assign('rdata', $rdata);
		$this->assign('user_name', $user_name);
		$this->assign('uid', $uid);
		$this->assign('rid', $rid);
		$this->display();
	}
	//成员删除
	public function memberDel(){
		$uid = I('get.uid');
		$id = I('get.id');
		$where['uid'] = $uid;
		$where['group_id'] = $id;
		if (empty($where['uid']) || empty($where['group_id'])){
			$rew['status'] = 0;
    		$rew['data']['strstr'] = '参数错误';
    		$this->ajaxReturn($rew);
	    	exit;
		}
		//print_r($where);
		//exit;
		$res = $this->client->memberDel($where);
//		if($res > 0){
//			$this->success('用户角色删除成功！',  U('AdminAuth/member',array('id' =>$id)),3);
//		}else{
//			$this->error('用户角色删除失败！');
//		}
		
		if($res){
     		$rew['status'] = 1;
    		$rew['data']['strstr'] = '用户角色删除成功！';
    		$rew['data']['url'] = U('AdminAuth/member',array('id' =>$id));
    		$this->ajaxReturn($rew);
	    	exit;   
 		}else{
     		$rew['status'] = 0;
    		$rew['data']['strstr'] = '用户角色删除失败！';
    		$this->ajaxReturn($rew);
	    	exit; 
 		}
		
	}
	//成员编辑提交处理
	public function memberHandle(){
		$data['group_id'] = I('post.groupName');
		$data['uid'] = I('post.user_id');
		$res = $this->client->memberHandle($data);
//		if($res){
//			$this->success('用户角色修改成功！', U('AdminAuth/member',array('id' => $data['group_id'])),3);
//		}else{
//			$this->error('用户角色修改失败！');
//		}
	    if($res){
     		$rew['status'] = 1;
    		$rew['data']['strstr'] = '用户角色修改成功！';
    		$rew['data']['url'] = U('AdminAuth/member',array('id' => $data['group_id']));
    		$this->ajaxReturn($rew);
	    	exit;   
 		}else{
     		$rew['status'] = 0;
    		$rew['data']['strstr'] = '用户角色修改失败！';
    		$this->ajaxReturn($rew);
	    	exit; 
 		}
	}
	/*=============角色管理	end====================*/
	
	
	
	public function index_one(){
	
		$this->display();
	}
	
	
	
	
	
	
}