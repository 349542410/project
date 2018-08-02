<?php
namespace Admin\Controller;
use Think\Controller;
class AdminTerminalController extends AdminbaseController{
	public $client;
	//public $writes;
	
	function _initialize(){
		parent::_initialize();
		//vendor('Hprose.HproseHttpClient');
        $this->client = new \HproseHttpClient(C('RAPIURL').'/AdminTerminal');		//读取、查询操作
        //$this->writes = new \HproseHttpClient(C('WAPIURL').'/AdminCollectPoint');		//添加，修改 
       
	}
	
	/**
	 * 终端号列表
	 * Enter description here ...
	 */
	public function terminal_list(){
		
		$data['epage'] = C('EPAGE');
		$data['p'] = I('get.p');
		//$map['a'] =array('like',array('%thinkphp%','%tp'),'OR');
		$computer = I('get.computer');
		$terminal = I('get.terminal');
		$point_id = I('get.point_id');
		$status   = I('get.status');
		$number   = I('get.number');
		if(!empty($computer)){
			$computer = trim($computer, ' ');
			$computer = trim($computer, '，');
			$computer = trim($computer, ',');
			$data['stl.computer_name'] = array('like', array('%'.$computer.'%'));
		}
		if(!empty($terminal)){
			$terminal = trim($terminal, ' ');
			$terminal = trim($terminal, '，');
			$terminal = trim($terminal, ',');
			
			$data['stl.terminal_name'] = array('like', array('%'.$terminal.'%'));
		}
		if(!empty($point_id)){
			$data['stl.point_id'] = $point_id;
		}
		if(!empty($status) && $status != 'no'){
			$status = $status == 1 ? 0 : 1;
			$data['stl.status'] = $status;
		}
		if(!empty($number)){
			$data['epage'] = $number;
		}
		
		
		$res = $this->client->terminal_list($data);
		//echo ADMIN_FILE;
		//exit;
		
		$list = $res['list'];
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
		
		$rew['uid'] = session('admin')['adid'];
		$point = $this->pointAll($type = 2);
		if(!empty($point['point_id']) && $point['point_id'] != 'NONE'){
			$where['point_id'] = $point['point_id'];
			$point = $this->client->terminal_point($where); 	
			$this->assign('point_list', $point);
		}
		//foreach ($list AS $key => $val){
		//	$list[$key]['status_name'] = C('ID_CARD_STATUS')[ $val['status']];
		//}
		//$point_list = $this->client->point();
		//$this->assign('point_list', $point_list);

		$this->assign('page',$show);// 赋值分页输出
		$this->assign('list',$list);
		$this->display();
	
	}
	
	
	/**
	 * 终端号添加
	 * Enter description here ...
	 */
	public function terminal_add(){
		
		$point_list = $this->client->terminal_add();
		//print_r($point_list);
		//exit;
		$this->assign('point_list', $point_list);
		$this->display();	
	
	}
	
	/**
	 * 终端号添加保存处理
	 * Enter description here ...
	 */
	public function terminal_upp(){
		
		$data['computer_name'] 		= I('post.computer_name');
		$data['terminal_name'] 		= I('post.terminal_name');
		$data['point_id'] 	  	 	= I('post.point_id');
		$data['status'] 	   		= I('post.status');
		$data['sys_time'] 			= date('Y-m-d H:i:s', time());
		$data['modify_admin_id'] 	= session('admin')['adid'];
		$id = I('post.id');
		if(!empty($id)){
			$data['id']		= $id;
			$res = $this->client->terminal_upp($data);
//			if($res){
//				$this->success('终端号修改成功！', U('AdminTerminal/terminal_list'));
//			}else{
//				$this->error('终端号修改失败！');
//			}
			
			if($res){
    			$result = array('state'=>'yes', 'msg'=>'修改成功');
	    	}else{
	    		$result = array('state'=>'no', 'msg'=>'修改失败');
	    	}			
			
			$this->ajaxReturn($result);			
			
		}else{
			$data['create_time']		= date('Y-m-d H:i:s', time());
			$res = $this->client->terminal_upp($data);
//			if($res){
//				$this->success('终端号添加成功！', U('AdminTerminal/terminal_list'));
//			}else{
//				$this->error('终端号添加失败！');
//			}
			if($res){
    			$result = array('state'=>'yes', 'msg'=>'添加成功');
	    	}else{
	    		$result = array('state'=>'no', 'msg'=>'添加失败');
	    	}			
			
			$this->ajaxReturn($result);	
			
		}
	}
	
	
	/**
	 * 终端号编辑
	 * Enter description here ...
	 */
	public function terminal_edit(){
		$id = I('get.id');
		if(empty($id)){
			//$this->error('终端号不存在');
			//exit;
			$result = array('state'=>'no', 'msg'=>'终端号不存在');
			$this->ajaxReturn($result);
			exit;
		}
		$data['id'] = $id;
		$res = $this->client->terminal_edit($data);
		
		$point_list = $this->client->terminal_add();
		$this->assign('res', $res);
		$this->assign('edit', 1);
		$this->assign('point_list', $point_list);
		$this->display('terminal_add');
	}
	
	
	/**
	 * 终端号删除
	 * Enter description here ...
	 */
	public function terminal_delete(){
		$id = I('get.id');
		if(empty($id)){
			//$this->error('终端号不存在！');
			$rew['status'] = 0;
    		$rew['data']['strstr'] = '终端号不存在！';
    		//$rew['data']['url'] = U('AdminTerminal/terminal_list');
    		$this->ajaxReturn($rew);
	    	exit;
		}
		$data['id'] = $id;
		$res = $this->client->terminal_delete($data);
		
		if($res['errorstr']){
			//$this->error($res['errorstr']);
			$rew['status'] = 0;
    		$rew['data']['strstr'] = $res['errorstr'];
    		//$rew['data']['url'] = U('AdminTerminal/terminal_list');
    		$this->ajaxReturn($rew);
	    	exit;
		}else{
			if($res){
				//$this->success('删除成功！', U('AdminTerminal/terminal_list'));
				$rew['status'] = 1;
	    		$rew['data']['strstr'] = '删除成功！';
	    		$rew['data']['url'] = U('AdminTerminal/terminal_list');
	    		$this->ajaxReturn($rew);
		    	exit;
			}else{
				//$this->error('删除失败！');
				$rew['status'] = 0;
	    		$rew['data']['strstr'] = '删除失败！';
	    		//$rew['data']['url'] = U('AdminTerminal/terminal_list');
	    		$this->ajaxReturn($rew);
		    	exit;
				
			}
		}
		
	}
	
}