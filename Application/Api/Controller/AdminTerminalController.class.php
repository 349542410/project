<?php
namespace Api\Controller;
use Think\Controller\HproseController;
class AdminTerminalController extends HproseController{

	/**
	 * 终端号列表
	 * Enter description here ...
	 */
	public function terminal_list($data){
    	//$point = M('user_addressee')->field('name, id_card_front_small, id_card_back_small, sys_time, cre_num')->where('status = 0 or status = 2 ')->select();
    	$epage = $data['epage'];
    	$p = $data['p'];
    	unset($data['epage']);
    	unset($data['p']);
    	
       	$count = M('self_terminal_list')->alias('stl')->where($data)->count();
    
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

		//$list = M('self_terminal_list')->alias('stl')->field('stl.*, cp.point_name')->join('left join mk_collect_point AS cp ON stl.point_id = cp.id ')->limit($page->firstRow.','.$page->listRows)->order('sys_time desc')->select();
    	$list = M('self_terminal_list')->alias('stl')->field('stl.*, cp.point_name')->join('left join mk_collect_point AS cp ON stl.point_id = cp.id ')->where($data)->page($p,$epage)->order('sys_time desc')->select();
    	
		
    	return array('count'=>$count, 'list'=>$list);
	}
	
	/**
	 * 终端号添加
	 * Enter description here ...
	 */	
	public function terminal_add(){
		$res = M('collect_point')->field('id, point_name')->select();
		return $res;
	}


	
	/**
	 * 终端号添加保存处理
	 * Enter description here ...
	 */
	public function terminal_upp($data){
		if(!empty($data['id'])){
			$res = M('self_terminal_list')->where('id = '.$data['id'].' ')->save($data);
		}else{
			$res = M('self_terminal_list')->add($data);
		
		}
		return $res;
	
	
	}
		
	
	/**
	 * 终端号编辑
	 * Enter description here ...
	 */
	public function terminal_edit($data){
	
		$res = M('self_terminal_list')->where('id = '.$data['id'].' ')->find(); 
		return $res;
	}

	
	
	/**
	 * 终端号删除
	 * Enter description here ...
	 */
	public function terminal_delete($data){
		//检验终端号是否在使用
		$res = M('print_relation_order ')->field('id')->where('terminal_of_point_id = '.$data['id'].' or terminal_id = '.$data['id'].' ')->find();
		if(!empty($res)){
			$row['status'] = false;
			$row['errorstr'] = '该终端号已被使用，不能删除!';
			return $row;
		}
		
		$rek = M('self_terminal_list')->where('id = '.$data['id'].' ')->delete();
		return $rek;
	
	}
	
	/**
	 * 获取揽收点
	 * Enter description here ...
	 */
	public function terminal_point($data){
		if($data['point_id'] == 'ALL'){
			$where = '';	
		}else{
			$where['id'] = array('in', $data['point_id']);
		}
		$res = M('collect_point')->field('id, point_name')->where($where)->select();
		
		return $res;
	}
	
}
	