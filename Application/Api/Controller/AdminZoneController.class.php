<?php
namespace Api\Controller;
use Think\Controller\HproseController;
class AdminZoneController extends HproseController{
	
	
	/**
	 * 时区列表
	 * Enter description here ...
	 */
	public  function indexs($data){
		$count = M('time_zone')->count();
		//return $count;
		$list =  M('time_zone')->select();
		$res['list'] = $list;
		$res['count'] = $count;
		
		return $res;	
	}
	
	/**
	 * 时区添加/修改处理
	 * Enter description here ...
	 */
	public function addhandle($data){
		if (!empty($data['id'])){
			$res = M('time_zone')->where('id = '.$data['id'].' ')->save($data);
			return $res;
		}else{
			$res = M('time_zone')->add($data);
			return $res;
		
		}
	}
	
	/**
	 * 时区编辑
	 * Enter description here ...
	 */
	public function edit($data){
		$res = M('time_zone')->where('id = '.$data['id'].' ')->find();
		return $res;
		
	}
	
	/**
	 * 时区删除
	 * Enter description here ...
	 */
	public function zonedel($data){
		
		//检验揽收点是否存在该时区
		$row = M('collect_point')->field('id, point_name, point_zone')->where('point_zone = '.$data['id'].' ')->find();
		
		if(!empty($row)){
			$res['status'] = false;
			$res['errorstr'] = ' '.$row['point_name'].' 揽收点正在使用该时区！ ';
			
			return $res;
		}
		$row = M('time_zone')->where('id = '.$data['id'].' ')->delete();
		if($row){
			$res['status'] = true;
			$res['errorstr'] = '时区删除成功！';
			return $res;
		}else{
			$res['status'] = false;
			$res['errorstr'] = '时区删除失败!';
			
			return $res;
		}
	
	}
	

}