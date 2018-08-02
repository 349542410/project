<?php
namespace Admin\Controller;
use Think\Controller;
class AdminZoneController extends AdminbaseController{
	public $client;
	//public $writes;
	
	function _initialize(){
		parent::_initialize();
        $this->client = new \HproseHttpClient(C('RAPIURL').'/AdminZone');		//读取、查询操作
        //$this->writes = new \HproseHttpClient(C('WAPIURL').'/AdminCollectPoint');		//添加，修改 
       
	}
	
	
	
	/**
	 * 时区列表
	 * Enter description here ...
	 */
	public  function index(){
		$data['epage'] = C('EPAGE');
		$data['p'] = I('get.p');
		
 		$res = $this->client->indexs($data);
		
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
		$this->assign('page', $show);
		$this->assign('data', $res['list']);
		$this->display();
	}
	
	
	/**
	 * 时区添加
	 * Enter description here ...
	 */
	public function add(){
	
		$this->display();
	}
	
	
	/**
	 * 时区添加/修改处理
	 * Enter description here ...
	 */
	public function addhandle(){
		
		$data['name_zone'] = I('post.name_zone');
		$data['rule_zone'] = I('post.rule_zone');
		//$data['time_zone_number'] = empty(I('post.time_zone_number')) ? 0 : I('post.time_zone_number');
		//$data['sort'] = I('sort');
		
		if(empty($data['name_zone']) || empty($data['rule_zone'])){
			$result = array('state'=>'no', 'msg'=>'参数错误');
			$this->ajaxReturn($result);
			exit;
		}
		
//		if(empty($data['time_zone_number'])){
//			$result = array('state'=>'no', 'msg'=>'时区数不能为空');
//			$this->ajaxReturn($result);
//			exit;
//		}
		
		
		$id = I('post.id');
		if(!empty($id)){
			$data['id'] = $id;
			$res = $this->client->addhandle($data);
			
		    if($res){
    			$result = array('state'=>'yes', 'msg'=>'修改成功');
	    	}else{
	    		$result = array('state'=>'no', 'msg'=>'修改失败');
	    	}
			$this->ajaxReturn($result);
//			if($res){
//				$this->success('修改时区成功', U('AdminZone/index'), 3);
//			}else{
//				$this->error('修改时区失败！');
//			}
		}else{
			
			$res = $this->client->addhandle($data);
			
			if($res){
    			$result = array('state'=>'yes', 'msg'=>'添加成功');
	    	}else{
	    		$result = array('state'=>'no', 'msg'=>'添加失败');
	    	}			
			
			$this->ajaxReturn($result);
//			if($res){
//				$this->success('添加时区成功!', U('AdminZone/index'), 3);
//			}else{
//				$this->error('添加时区失败！');
//			
//			}
		}
		
	}
	
	/**
	 * 时区编辑
	 * Enter description here ...
	 */
	public function edit(){
		$data['id'] = I('get.id');
		if(empty($data['id'])){
			//$this->error('请选择修改时区', U('AdminZone/index'), 3);
			//exit;
			$result = array('state'=>'no', 'msg'=>'请选择修改时区');
			$this->ajaxReturn($result);
			exit;
		}
		$res = $this->client->edit($data);
		$this->assign('data', $res);
		$this->display('add');
	}
	
	/**
	 * 时区删除
	 * Enter description here ...
	 */
	public function zonedel(){
		$data['id'] = I('get.id');
		if(empty($data['id'])){
			//$this->error('请选择删除时区', U('AdminZone/index'), 3);
			//exit;
			$rew['status'] = 0;
    		$rew['data']['strstr'] = '请选择删除时区';
    		//$rew['data']['url'] = U('AdminZone/index');
    		$this->ajaxReturn($rew);
	    	exit;			
		}		
		//print_r($data);
		//exit;
		$res = $this->client->zonedel($data);
		
		if($res['status']){
			//$this->success($res['errorstr'], U('AdminZone/index'), 3);
			$rew['status'] = 1;
    		$rew['data']['strstr'] = $res['errorstr'];
    		$rew['data']['url'] = U('AdminZone/index');
    		$this->ajaxReturn($rew);
	    	exit;  
			
		}else{
			//$this->error($res['srrorstr']);
			$rew['status'] = 0;
    		$rew['data']['strstr'] = $res['errorstr'];
    		//$rew['data']['url'] = U('AdminZone/index');
    		$this->ajaxReturn($rew);
	    	exit;
		}
	}
	
	
	
}