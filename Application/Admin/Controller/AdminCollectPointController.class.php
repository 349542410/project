<?php
namespace Admin\Controller;
use Think\Controller;
class AdminCollectPointController extends AdminbaseController{
	public $client;
	//public $writes;
	
	function _initialize(){
		parent::_initialize();

        $this->client = new \HproseHttpClient(C('RAPIURL').'/AdminCollectPoint');		//读取、查询操作
        //$this->writes = new \HproseHttpClient(C('WAPIURL').'/AdminCollectPoint');		//添加，修改
		
        //$this->writes = new \HproseHttpClient(C('WAPIURL').'/AdminCollectPoint');		//添加，修改 
       

	}

	/**
	 * 揽收点列表
	 * Enter description here ...
	 */
	
	public function index(){
		$data['epage'] = C('EPAGE');
		$data['uid'] = session('admin')['adid'];
		$data['p'] = I('get.p');
		$res = $this->client->count($data);
		
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
		
		if($res['errorstr']){
			$this->error($res['errorstr'], U('Ecompany/index'),3);
			exit;
		}
		
		$this->assign('page',$show);// 赋值分页输出
		$this->assign('list',$res['list']);
		
		$this->display();
	}
	
	
	/**
	 * 揽收点添加
	 * Enter description here ...
	 */
	public function info(){
		
		//取得管理员名称
		$res = $this->client->adminlist();
		$adminlist = $res['list'];
		$time_zone = $res['time_zone'];
		//print_r($time_zone);
		//exit;
		$this->assign('time_zone', $time_zone);
		$this->assign('adminlist', $adminlist);
		$this->display();
	}
	
	/**
	 * 揽收点数据写入数据库
	 * Enter description here ...
	 */
	
	public function add(){
		
		$data['point_name'] 	= I('post.point_name');
		$data['point_name_mkil']= I('post.point_name_mkil');
		if(I('post.point_admin_id')){
			$data['point_admin_id'] = I('post.point_admin_id');
		}
		$data['point_address'] 	= I('post.point_address');
		$data['remarks'] 		= I('post.remarks');
		$data['point_zone']		= I('post.point_zone');
		$data['last_modify_time']	= date('Y-m-d H:i:s', time());
		
		//$value = session();	//修改权限后  adid 要修改
        //$admin = $value['admin']['adid'];
        $admin = session('admin')['adid'];
		$data['last_modify_by']	= $admin;
		$id = I('post.id');
        if(!empty($id)){
			$data['id'] = $id;
		}
        //print_r($data);
        //exit;
        $res = $this->client->pointadd($data);
        //print_r($res);
        //exit;
//        if($res){
//        	$this->error('添加修改成功！', U('AdminCollectPoint/index'), 3);
//        }else{
//        	$this->error('添加修改失败！');
//        }

			if($res){
    			$result = array('state'=>'yes', 'msg'=>'添加修改成功');
	    	}else{
	    		$result = array('state'=>'no', 'msg'=>'添加修改失败');
	    	}			
			
			$this->ajaxReturn($result);        
        
	}
	
	/**
	 * 揽收点修改
	 * Enter description here ...
	 */
	public function edit(){
		$id = I('get.pid');	
		if(empty($id)){
			//$this->error('请选择修改揽收点', U('AdminCollectPoint/index'), 3);
			//exit;
			$result = array('state'=>'no', 'msg'=>'请选择修改揽收点');
			$this->ajaxReturn($result);
			exit;
		}
		$data['id'] = $id;
		$data['uid'] = session('admin')['adid'];
		if(empty($data['uid'])){
//			$this->error('请重新登录', U('Login/index'));
//			exit;
			$result = array('state'=>'no', 'msg'=>'请重新登录');
			$this->ajaxReturn($result);
			exit;
		}
		$info = $this->client->edit($data);
		if(!empty($info['errorstr'])){
//			$this->error($info['errorstr']);
//			exit;
			$result = array('state'=>'no', 'msg'=>$info['errorstr']);
			$this->ajaxReturn($result);
			exit;
		}
		$res = $this->client->adminlist();
		$userlist = $res['list'];
		$time_zone = $res['time_zone'];
		$this->assign('time_zone', $time_zone);
		$this->assign('res', $info);
		$this->assign('adminlist', $userlist);
		$this->assign('edit', 1);
		$this->display('info');
	}
	
	/**
	 * 揽收到信息删除
	 * Enter description here ...
	 */
	public function pointdelete(){
		$id = I('get.pid');
		if(empty($id)){
			//$this->error('揽收点信息不存在！');
			$rew['status'] = 0;
	    	$rew['data']['strstr'] = '揽收点信息不存在！';
	    	$this->ajaxReturn($rew);
	    	exit;
			
		}
		$data['id'] = $id;
		$data['uid'] = session('admin')['adid'];
		if(empty($data['uid'])){
			//$this->error('请重新登录', U('Login/index'));
			//exit;
			$rew['status'] = 0;
	    	$rew['data']['strstr'] = '请重新登录';
	    	$rew['data']['url'] = U('Login/index');
	    	$this->ajaxReturn($rew);
	    	exit;
			
		}		
		
		$res = $this->client->pointdelete($data);
		if(!empty($res['errorstr'])){
			//$this->error($res['errosrstr']);
			//exit;
			$rew['status'] = 0;
	    	$rew['data']['strstr'] = $res['errorstr'];
	    	//$rew['data']['url'] = U('AdminCollectPoint/index');
	    	$this->ajaxReturn($rew);
	    	exit;
		}
		
		$rew['status'] = 1;
    	$rew['data']['strstr'] = $res['errorstr'];
    	$rew['data']['url'] = U('AdminCollectPoint/index');
    	$this->ajaxReturn($rew);
    	exit;
		//print_r($res);
		//exit;
		//$this->error(''.$res['errorstr'].'', U('AdminCollectPoint/index'), 3);
	}
	
	/**
	 * 
	 * Enter description here ...
	 * @param $a 默认时区	
	 * @param $b 传入时区
	 * @param $times 默认时区时间
	 */
	public function contimezone($a, $b, $times){
		date_default_timezone_set($a);
		$time_t_a = date('d', time());
		$time_h_a = date('H', time());
		//$time_i_a = date('i', time());
		
		date_default_timezone_set($b);
		$time_t_b = date('d', time());
		$time_h_b = date('H',time());
		//$time_i_b = date('i', time()); 
		//$t = ($time_t_a - $time_t_b) * 3600 * 24;
		date_default_timezone_set($a); //还原原来时区
		$b = floatval($time_h_a) - floatval($time_h_b);
		
		$c = strtotime($times);
		
		$h = date('H', $c);
		$i = date('i', $c);
		$s = date('s', $c);
		$m = date('m', $c);
		$d = date('d', $c);
		$y = date('Y', $c);
		$chazhi = $h - $b;
		if($chazhi < 0){
			$d = $d - 1;
			$h = $chazhi + 24;	
		}
		elseif($chazhi > 24){
			$d = $d +1;
			$h = $chazhi - 24;
		}else{
			$h = $chazhi;
		}
		$dtime = $y.'-'.$m.'-'.$d.' '.$h.':'.$i.':'.$s;   
		//$dtime = date ("Y-m-d H:i:s" , mktime(gmdate('H', $c)- $b,gmdate('i', $c),gmdate('s', $c),gmdate('m', $c),gmdate('d', $c),gmdate('Y', $c)));
		//$d = $c + $b * 3600 + $t;
		
		//$dtime = date("Y-m-d H:i:s", $d);
		return $dtime;
	}
	
	
	
//	public function ceshi(){
//		//$ec = R('AdminTerminal/terminal_list');+
//		//echo $ec;
//		//$this->display('AdminTerminal/terminal_list');
//		$this->redirect(U('AdminTerminal/terminal_list',array('ids' => '12')));
//		
//	}
	
	
}