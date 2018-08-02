<?php
namespace Api\Controller;
use Think\Controller\HproseController;
class IndexController extends HproseController{
	
	//取得时区
	public function time_zone(){
		$res = M('time_zone')->order('id asc')->select();
		return $res;
	}

	//取得通告
	public function announcement(){
		//$where['']
		//$where['_string'] = "(find_in_set('1',view_to))";
        //构造时间条件
        // date_default_timezone_set('UTC');
        $curr_time = time();
        $where['_string'] = ('  start_time < ' . $curr_time . ' and (end_time > ' . $curr_time . ' or end_time=0)');

		$res = M('announcement_title')->field('id, bulletin_title, start_time')->where($where)->select();	
		//$res = M('announcement_title')->getLastSql();
		return $res;
	}
	
	
	//获取通告内容
	public function announ_info($where){
		$w['at.id'] = $where['id'];
		$res = M('announcement_title')->alias('at')->join('left join mk_announcement_content as ac ON at.id = ac.ant_id')->where($w)->find();
		return $res;
		
	}
	
	//获取用户信息
	public function admin_user($where){
		$res = M('manager_list')->where($where)->find();
		
		return $res;
		
		
	}
	
	//处理密码
	public function pwdhandle($data){
		//检验用户
		$w['id'] = $data['id'];
		//$w['pwd'] = md5($data['pwd']);
		//return $w;
		$row = M('manager_list')->where($w)->find();
		//return $row;
		$pws = $data['pwd'];
		if(empty($row) || $row['pwd'] != md5($pws)){
			$rew['status'] = 0;
    		$rew['data']['strstr'] = '密码输入错误';
    		return $rew; 
		}else{
			$wh['id'] = $data['id'];
			$wh['pwd'] = md5($data['pwd_one']);
			unset($w['pwd']);
			$res = M('manager_list')->where($w)->save($wh);
			if($res){
				$rew['status'] = 1;
	    		$rew['data']['strstr'] = '密码修改成功,重新登陆';
	    		//$rew['data']['url'] = U('/Login/Login_out');
	    		return $rew; 			
			}else{
				$rew['status'] = 0;
	    		$rew['data']['strstr'] = '密码修改失败';
	    		//$rew['data']['url'] = U('AdminAuth/index_authNav');
	    		return $rew;				
			
			}
			
		
		}
	}
	
	
}