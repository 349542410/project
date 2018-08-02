<?php
/**
 * 角色管理 客户端
 */
namespace Admin\Controller;
use Think\Controller;
class RoleController extends AdminbaseController{
	
    function _initialize() {
        parent::_initialize();
		$client = new \HproseHttpClient(C('RAPIURL').'/Role');		//读取、查询操作
		$this->client = $client;	//全局变量
    }
    
	public function index(){

		$client = $this->client;
		$list   = $client->index();		

        $this->assign('list',$list);

		$this->display();
	}

	/**
	 * 添加 视图
	 */
	public function add(){
        
		$pwrg = C('pwrg');
		$pwr = C('pwr');

		$this->assign('pwrg',$pwrg);
		$this->assign('pwr',$pwr);

		$this->display();
	}

	/**
	 * 添加 方法
	 * @return [type] [description]
	 */
	public function insert(){
		if(!IS_POST){
			die('非法操作');
		}

		if($_POST){
			$data['role_name']        = trim(I('post.name'));		//用户组名称
			$data['status']           = I('post.status');			//是否激活
			$data['content']          = trim(I('post.content'));	//描述

			$gr = C('pwr');
			foreach($gr as $v){
				$dat[$v['1']] = I($v['3']);
			}
			$str = serialize($dat);		//序列化

			$data['power'] = $str;

	        $client = $this->client;
	        $result = $client->add($data);			

	        $this->ajaxReturn($result);

		}

	}

	/**
	 * 编辑 视图
	 */
	public function edit(){

		if($_GET){
			$id = I('get.id');

			$client = $this->client;
			$info   = $client->edit($id);					
			$info['power'] = unserialize($info['power']);

			$pwrg = C('pwrg');
			$pwr = C('pwr');

			$this->assign('pwrg',$pwrg);
			$this->assign('pwr',$pwr);
	        $this->assign('info',$info);

			$this->display();
		}		
	}

	/**
	 * 数据更新 方法
	 * @return [type] [description]
	 */
	public function update(){

		if(!IS_POST){
			die('非法操作');
		}

		if($_POST){
			$id = I('post.id');
			$data['role_name']        = trim(I('post.name'));		//用户组名称
			$data['status']           = I('post.status');			//是否激活
			$data['content']          = trim(I('post.content'));	//描述

			$gr = C('pwr');
			foreach($gr as $v){
				$dat[$v['1']] = I($v['3']);
			}
			$str = serialize($dat);		//序列化

			$data['power'] = $str;
			
	        $client = $this->client;
	        $result = $client->update($id,$data);			

	        $this->ajaxReturn($result);

		}		
	}

	/**
	 * 单个删除 方法
	 * @return [type] [description]
	 */
	public function delete(){

		if(IS_AJAX){
			$id = I('post.id');

	        $client = $this->client;
			$result = $client->delete($id);

			$this->ajaxReturn($result);

		}else{
			die('非法操作');
		}
	}

	// public function group(){
	// 	$g = C('pwrg');
	// 	$gr = C('pwr');


	// 	foreach($gr as $v){
	// 		$data[$v['1']] = I($v['3']);
	// 	}
	// 	$str = serialize($data);
	// 	dump($str);
	// 	$dat['role_name']        = trim(I('post.name'));		//用户组名称
	// 	$dat['power'] = $str;
	// 	if(M('role_list')->add($dat)){
	// 		echo 'yes';
	// 	}
	// }

	// public function read(){
	// 	$res = M('role_list')->where(array('role_name'=>'超级管理员'))->find();
	// 	$arr = unserialize($res['power']);
	// 	dump($arr);
	// }
}