<?php
namespace Api\Controller;
use Think\Controller\HproseController;
class AdminAnnouncementController extends HproseController{

	/**
	 * 通告列表
	 * Enter description here ...
	 */
	public function indexs($data){
		//return $data;
       	$count = M('announcement_title')->where($data)->count();
		//return $count;
       	//$list = M('manager_list')->field('id, name, tname, email, phone')->where('status = 1')->limit($page->firstRow.','.$page->listRows)->select();
		$list = M('announcement_title')->where($data)->page($data['p'],$data['epage'])->select();
		
		
    	return array('count'=>$count, 'list'=>$list);
	}

	/**
	 * 查看通告内容
	 * Enter description here ...
	 */
	public function info($data){
		$res =  M('announcement_title')->alias('ant')->field('ant.*, anc.content')->join('left join mk_announcement_content as anc ON ant.id = anc.ant_id')->where('ant.id = '.$data['id'].' ')->find();
		return $res;
	}
	
	
	/**
	 * 通告信息提交处理
	 */
	public function addhandle($data){
		
		$rek['content'] = $data['comment'];
		if(!empty($data['id'])){
			$id = $data['id'];
			$rek['ant_id'] = $id;
			$res = M('announcement_title')->data($data)->where('id = '.$id.' ')->save();
			$cont_id = M('announcement_content')->field('id')->where('ant_id = '.$id.' ')->find();
			if($cont_id){			
				$rew = M('announcement_content')->data($rek)->where('ant_id = '.$id.' ')->save();
			}else{
				
				$rew = M('announcement_content')->data($rek)->add();
			}
			if($res || $rew){
				$rea['status'] = true;
				$rea['strstr'] = '通告修改成功！';
				return $rea;
			}else{
				$rea['status'] = false;
				$rea['errorstr'] = '通告修改失败！';
				return $rea;
			}
			
		}else{
			$res = M('announcement_title')->data($data)->add();
			if($res){
				$rek['ant_id'] = $res;
				$rew = M('announcement_content')->data($rek)->add();
				if($rew){
					$rea['status'] = true;
					$rea['strstr'] = '通告添加成功！';
					return $rea;
				}else{
					$rea['status'] = false;
					$rea['errorstr'] = '通告内容添加失败!';
					return $rea;
				}
			}else{
				$rea['status'] = false;
				$rea['errorstr'] = '通告添加失败!';
				return $rea;
			}
		}
	}
	
	
	
	/**
	 * 通告内容删除
	 * Enter description here ...
	 */
	public function delete($data){
		//开启事务
		$cash_logs = M('announcement_title');
		$cash_logs->startTrans();
		$res = $cash_logs->where('id = '.$data['id'].' ')->delete();
		if($res){
			$rek = M('announcement_content')->where('ant_id = '.$data['id'].' ')->delete();
			if($rek){
				$cash_logs->commit();//成功则提交
				//$rea['status'] = true;
				//$rea['strstr'] = '通告内容删除成功!';
				$rea = true;
				return $rea;
			}else{
				$cash_logs->rollback();//不成功，则回滚
				//$rea['status'] = false;
				//$rea['errorstr'] = '通告内容删除失败!';
				$rea = false;
				return $rea;
			}
			
		}else{
			$cash_logs->rollback();//不成功，则回滚
			//$rea['status'] = false;
			//$rea['errorstr'] = '通告内容删除失败!';
			$rea = false;
			return $rea;
		}
		
	}
	
	
	

}