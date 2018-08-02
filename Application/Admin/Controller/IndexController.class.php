<?php
namespace Admin\Controller;
use Think\Controller;
class IndexController extends AdminbaseController {
	public $client;
	function _initialize(){
		parent::_initialize();
        $this->client = new \HproseHttpClient(C('RAPIURL').'/Index');		//读取、查询操作
        //$this->writes = new \HproseHttpClient(C('WAPIURL').'/AdminCollectPoint');		//添加，修改 
       
	}	
	public function index(){
		//require_once '/Common/indextime.php';
		//时区时间
		$time_zone = $this->client->time_zone();
		$time_type = date_default_timezone_get();
		foreach ($time_zone as $key => $val){
			date_default_timezone_set($val['rule_zone']);
			$time_zone[$key]['time_numbs'] = date('Y-m-d H:i:s', time());
			$time_zone[$key]['ymd'] = date('Y', time()) . '年' . date('m', time()) . '月' . date('d', time()) . '日';
			$time_zone[$key]['his'] = date('H', time()) . ':' . date('i', time()) . ':' . date('s', time()) ;
			$time_zone[$key]['xq'] = "星期" . mb_substr( "日一二三四五六",date("w"),1,"utf-8" );
			date_default_timezone_set($time_type);
			$time_zone[$key]['sjc'] = strtotime($time_zone[$key]['time_numbs']) * 1000; //时间戳
			$sjc[$key] = $time_zone[$key]['sjc'];
		}
		//print_r($sjc);
		//exit;
		//$sjc = 1;
		$sjc = json_encode($sjc);
		date_default_timezone_set($time_type);
		$this->assign('time_zone', $time_zone);
		$this->assign('sjc', $sjc);
		//$time_zone_html = $this->fetch('Index:time_zone');
		//通告内容
		$announcement = $this->client->announcement();
		//print_r($announcement);
		//exit;
		foreach ($announcement as $key => $val){
			$announcement[$key]['times'] = date('Y-m-d', $val['start_time']);
		}
		
		$this->assign('announcement', $announcement);
		
		
		//获取个人信息
		$uid = session('admin')['adid'];
		if(empty($uid)){
			$this->error('用户未登录，请重新登陆', U('/Login/index'));
			exit;
		}
		$au['id'] = $uid;
		$user = $this->client->admin_user($au);
		$this->assign('user', $user);
		$this->display();
	}

	
	//查看通告详细
	public function announ_info(){
		$where['id'] = I('get.id');
		$res = $this->client->announ_info($where);
		$res['times'] = date('Y-m-d', $res['start_time']);
		
		$this->assign('data', $res);
		$this->display();
	
	}
	
	//修改密码
	public function pwdedit(){
		$uid = session('admin')['adid'];
		if(empty($uid)){
			$this->error('用户未登录，请重新登陆', U('/Login/index'));
			exit;
		}		
		$au['id'] = $uid;
		$user = $this->client->admin_user($au);
		
		$this->assign('user', $user);
		$this->display();
		
	}
	
	//处理修改密码
	public function pwdhandle(){
		$id = I('post.id');
		$pwd = I('post.pwd');
		$pwd_one = I('post.pwd_one');
		$pwd_two = I('post.two');

		if(empty($id)){
			$rew['status'] = 0;
    		$rew['data']['strstr'] = '用户不存在';
    		//$rew['data']['url'] = U('AdminAuth/index_authNav');
    		$this->ajaxReturn($rew);
	    	exit;   
		}

		if(empty($pwd)){
			$rew['status'] = 0;
    		$rew['data']['strstr'] = '请输入密码';
    		//$rew['data']['url'] = U('AdminAuth/index_authNav');
    		$this->ajaxReturn($rew);
	    	exit;   
		}		
		
		if(empty($pwd_one != $pwd_two)){
			$rew['status'] = 0;
    		$rew['data']['strstr'] = '确认密码不正确';
    		//$rew['data']['url'] = U('AdminAuth/index_authNav');
    		$this->ajaxReturn($rew);
	    	exit;   
		}		
				
		$where['id'] = $id;
		$where['pwd'] = $pwd;
		$where['pwd_one'] = $pwd_one;
		
		$rew = $this->client->pwdhandle($where);
		
		if($rew['status']){
			$rew['data']['url'] = U('/Login/Login_out');
		}
		$this->ajaxReturn($rew);
	    exit;   
	
	}
	

	
	public function test(){
        $upload = new \Think\Upload();// 实例化上传类
        $upload->maxSize   =     3145728 ;// 设置附件上传大小
        $upload->exts      =     array('jpg', 'gif', 'png', 'jpeg');// 设置附件上传类型
        $upload->rootPath  =     ADMIN_ABS_FILE; // 设置附件上传根目录
        $upload->savePath  =     ''; // 设置附件上传（子）目录
        // 上传文件
        $info   =   $upload->upload();
        if(!$info) {// 上传错误提示错误信息
            print_r($upload->getError());
        }else{// 上传成功
            echo '上传成功！';
        }
    }
	
	
}