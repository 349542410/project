<?php
namespace Api\Controller;
use Think\Controller\HproseController;
class AdminCollectPointController extends HproseController{
	
	/**
	 * 查总数
	 * @param  [type] $where [description]
	 * @return [type]        [description]
	 */
    public function count($data){
    	
    	//$point = M('auth_group_access')->field('point_id')->where('uid = '.$data['uid'].' ')->find();
    	//if('' == $point['point_id']){
    	//	$datas['status'] = false;
    	//	$datas['errorstr'] = '该管理员没有揽收点管理';
    	//	return $datas;
    	//}elseif ('0' == $point['point_id']){
       	$count = M('collect_point')->count();
        	//return $count;
    	//}else {
    	//	$point_str = trim($point['point_id'], ',');
    	//	$point_id = explode(',', $point_str);
    	//	$count = count($point_id);
    	//}
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
		//if('0' == $point['point_id']){
			//$list = M('collect_point')->alias('cp')->field('cp.*, ml.name AS admin_name, tz.name_zone, tz.id AS tzid')->join('left join mk_manager_list AS ml ON cp.point_admin_id = ml.id ')->join('left join mk_time_zone AS tz ON cp.point_zone = tz.id')->limit($page->firstRow.','.$page->listRows)->select();
		//}else{
		//	$list = M('collect_point')->alias('cp')->field('cp.*, ml.name AS admin_name, tz.name_zone, tz.id AS tzid')->join('left join mk_manager_list AS ml ON cp.point_admin_id = ml.id ')->join('left join mk_time_zone AS tz ON cp.point_zone = tz.id')->where('cp.id in('.$point_str.') ')->limit($page->firstRow.','.$page->listRows)->select();
		//}
		$list = M('collect_point')->alias('cp')->field('cp.*, ml.name AS admin_name, tz.name_zone, tz.id AS tzid')->join('left join mk_manager_list AS ml ON cp.point_admin_id = ml.id ')->join('left join mk_time_zone AS tz ON cp.point_zone = tz.id')->page($data['p'],$data['epage'])->select();
		
			
			
			
    	return array('count'=>$count, 'list'=>$list);
    }
    
    
    /**
     * 获取管理员数据
     * Enter description here ...
     */
    public function adminlist(){
    	
    	$time_zone = M('time_zone')->select();
    	$list =  M('manager_list')->field('id, name')->where('status = 1')->select();
    	$res['time_zone'] = $time_zone;
    	$res['list'] = $list;
    	 
    	return $res;
    	
    }
    
    
    /**
     * 数据添加数据库
     * $data['id'] 揽收点ID
     * @param $data
     */
    public function pointadd($data){
    	
    	//取得操作员名称
    	$last_modify_by =  M('manager_list')->field('name')->where('id = '.$data['last_modify_by'].' ')->find();
    	
    	if(empty($last_modify_by)){
    		return false;
    	}
    	$data['last_modify_by'] = $last_modify_by['name'];
    	if(!empty($data['id'])){
    		$res = M('collect_point')->where('id = '.$data['id'].' ')->save($data);
    	}else{
    		$res = M('collect_point')->add($data);
    	}
    	if($res){
    		return true;
    	}else{
    		return false;
    	}
    }
    
    /**
     * 获取揽收点信息
     * $data['id'] 揽收点ID
     */
    public function edit($data){
    	$id = $data['id'];
    	//检验揽收点是否存在用户中
    	//$point = M('auth_group_access')->field('point_id')->where('uid = '.$data['uid'].' ')->find();
    	//$point_id = trim($point['point_id'], ',');
    	//$point_id = explode(',', $point_id);
    	//if(!in_array($id, $point_id)){
    	//	$res['errorstr'] = '该管理员没有操作该揽收点权限';
    	//	return $res;
    	//}
    	$res = M('collect_point')->where('id ='. $id)->find();
    	return $res;
    
    }
    
    
    /**
     * 揽收点信息删除
     * $data['id'] 揽收点ID
     */
    public function pointdelete($data){
    	//检验揽收点是否在使用（财务   揽收  出库）在使用就不能删除
    	
    	
    	//检验是否有删除权限
//        $point = M('auth_group_access')->field('point_id')->where('uid = '.$data['uid'].' ')->find();
//        if('' == $point['point_id']){
//        	$err['errorstr'] = '该管理员没有操作该揽收点权限';
//        	return $err;
//        }
//        if(!empty($point) && '0' != $point['point_id']){
//        	$point_id = trim($point['point_id'], ',');
//	    	$point_id = explode(',', $point_id);
//	    	if(!in_array($data['id'], $point_id)){
//	    		$res['errorstr'] = '该管理员没有操作该揽收点权限';
//	    		return $res;
//	    	}
//	    	$key = array_search($data['id'], $point_id);
//	    	unset($point_id[$key]);
//	    	$rek['point_id'] = implode(',', $point_id);
//	    	M('auth_group_access')->where('uid = '.$data['id'].' ')->save($rek);
//        }
    	//删除揽收点
    	$res = M('collect_point')->where('id = '.$data['id'].' ')->delete();
    	if($res){
    		$err['status'] = true;
    		$err['errorstr'] = '揽收点删除成功！';
    		return $err;
    		
    	}else{
    		$err['status'] = false;
    		$err['errorstr'] = '揽收点删除失败！';
    		return $err;
    		
    	}
    	
    }
    
}   