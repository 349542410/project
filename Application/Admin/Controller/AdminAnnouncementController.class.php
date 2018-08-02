<?php
namespace Admin\Controller;
use Think\Controller;
class AdminAnnouncementController extends AdminbaseController{
	public $client;
	public $writes;
	
	function __construct(){
		parent::__construct();
		vendor('Hprose.HproseHttpClient');
        $this->client = new \HproseHttpClient(C('RAPIURL').'/AdminAnnouncement');		//读取、查询操作
        //$this->writes = new \HproseHttpClient(C('WAPIURL').'/AdminCollectPoint');		//添加，修改 
        
	}
	
	/**
	 * 通告列表
	 * Enter description here ...
	 */
	public function index(){
		if(I('get.keyword')){
			$data['bulletin_title'] = array('like', '%'.I('get.keyword').'%');
		}
		if(I('get.start_date')){
			$stime = I('get.start_date');
			//获取当前时区
			$time_zood = date_default_timezone_get();
			//将当前时区转成0时区
			date_default_timezone_set('UTC');
			$start_time = empty($stime) ? 0 : strtotime($stime);
			$data['start_time'] = array('egt', $start_time);
			date_default_timezone_set($time_zood);		
		}
		if(I('get.end_date')){
			$etime = I('get.end_date');
			//获取当前时区
			$time_zood = date_default_timezone_get();
			//将当前时区转成0时区
			date_default_timezone_set('UTC');
			$end_time = empty($etime) ? 0 : strtotime($etime);
			$data['end_time'] = array('elt', $end_time);
			date_default_timezone_set($time_zood);		
		}
		if(I('get.lang')){
			$data['lang'] = I('get.lang');
		}
		$leng = strlen(I('get.feeback'));
		if($leng > 0){
			$data['feeback'] = I('get.feeback');
		} 
		//print_r($data);
		//exit;
		$data['epage'] = C('EPAGE');
		$data['p'] = I('get.p');
		
		//取得用户信息
		$res = $this->client->indexs($data);
		//print_r($res);
		//exit;
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
		$ret = $res['list'];
		//获取当前时区
		$time_zood = date_default_timezone_get();
		//将当前时区转成0时区
		date_default_timezone_set('UTC');		
		foreach ($ret as $key => $val){
			$view = explode(',', $val['view_to']);
			//print_r($view);
			//exit;
			foreach ($view as $k => $v){
				$view_str .= C('VIEW_TO')[$v] . ' ';
			}
			$ret[$key]['view_str'] = $view_str;
			$ret[$key]['start_time'] = empty($val['start_time']) ? 0 : date('Y-m-d', $val['start_time']);
			$ret[$key]['end_time']   = empty($val['end_time']) ? 0 : date('Y-m-d', $val['end_time']);
			$ret[$key]['lang_name']  = C('LANG_CAT')[$val['lang']];
			$view_str = '';
		}
		date_default_timezone_set($time_zood);
		$this->assign('data', $ret);
		$this->assign('page', $show);

	    $three_view = $this->filesname;
	    $this->assign('three_nav', $this->three_nav);
	    $this->assign('ModulesName', $this->ModulesName);
		//$view_to  = C('VIEW_TO');
		$lang = C('LANG_CAT');
	    //$this->assign('view_to', $view_to);
	    $this->assign('lang', $lang);
		$this->display('index_on');
	}	
	
	/**
	 * 通告添加
	 */
	public function add(){
		
		$view_to  = C('VIEW_TO');
		$lang = C('LANG_CAT');
	    $this->assign('view_to', $view_to);
	    $this->assign('lang', $lang);
		$three_view = $this->filesname;
	    $this->assign('three_nav', $this->three_nav);
	    $this->assign('ModulesName', $this->ModulesName);
	   
		$this->display();
	}
	
	/**
	 * 通告信息提交处理
	 */
	public function addhandle(){
		$data = I('post.');
		//$res['view_to'] = implode(',', $data['view_to']);
		$res['view_to'] = trim($data['view_to'], ',');
		$res['view_to'] = trim($res['view_to'], '，');
		$res['view_to'] = trim($res['view_to'], ' '); 
		$res['feeback'] = $data['feeback'];
		$res['lang'] 	= $data['lang'];
		$res['bulletin_title'] = $data['bulletin_title'];
		
		//获取当前时区
		$time_zood = date_default_timezone_get();
		//将当前时区转成0时区
		date_default_timezone_set('UTC');
		$res['start_time'] = empty($data['start_date']) ? 0 : strtotime($data['start_date']);
		$res['end_time']   = empty($data['end_date']) ? 0 : strtotime($data['end_date']);
		$res['comment']    = $data['editor'];
		//$res['content']  = I('post.editor', '', 'false');
		if(!empty($data['id'])){
			$res['id'] = $data['id'];
		}
		
		$rek = $this->client->addhandle($res);
		date_default_timezone_set($time_zood);
		//print_r($rek);
		//exit;
 		if($rek['status']){
 			//$this->error($rek['errorstr'], U('AdminAnnouncement/index'));
 			//exit;
     		$rew['status'] = 1;
    		$rew['data']['strstr'] = $rek['strstr'];
    		$rew['data']['url'] = U('AdminAnnouncement/index');
    		$this->ajaxReturn($rew);
	    	exit;   
 		}else{
     		$rew['status'] = 0;
    		$rew['data']['strstr'] = $rek['errorstr'];
    		$rew['data']['url'] = U('AdminAnnouncement/index');
    		$this->ajaxReturn($rew);
	    	exit;   
 			
 		}
		//exit; 
		//print_r($data);
		//exit;
		//$view_to = 
	}
	
	/**
	 * 查看通告内容
	 * Enter description here ...
	 */
	public function info(){
		$id = I('get.id');
		if(empty($id)){
			$this->error('不存在该通告内容');
			exit;
		}
		$data['id'] = $id;
		$res = $this->client->info($data);
		$res['html_content'] = htmlspecialchars_decode($res['content']);
		//echo $res['html_content'];
		//exit;
		$view = explode(',', $res['view_to']);
		$view_to  = C('VIEW_TO');
		//获取当前时区
		$time_zood = date_default_timezone_get();
		//将当前时区转成0时区
		date_default_timezone_set('UTC');
		$lang = C('LANG_CAT');
		$res['start_time'] = empty($res['start_time']) ? 0 : date('Y-m-d H:i', $res['start_time']);
		$res['end_time']   = empty($res['end_time']) ? 0 : date('Y-m-d H:i', $res['end_time']);
		//print_r($res);
		//exit;
		date_default_timezone_set($time_zood);
	    $this->assign('view', $view);
		$this->assign('view_to', $view_to);
	    $this->assign('lang', $lang);
		$this->assign('res', $res);
		$this->display();
	}
	
	
	/**
	 * 通告内容删除
	 * Enter description here ...
	 */
	public function delete(){
		$data['id'] = I('get.id');
		$res = $this->client->delete($data);
		if($res){
			$rew['status'] = 1;
    		$rew['data']['strstr'] = '通告内容删除成功';
		}else{
			$rew['status'] = 0;
    		$rew['data']['strstr'] = '通告内容删除失败';
		}
    	$rew['data']['url'] = U('AdminAnnouncement/index');
    	$this->ajaxReturn($rew);
    	exit;
		//print_r($rew);
		//exit;
	}
	
	/**
	 * 编辑器图片上传
	 * Enter description here ...
	 */
	public function pic(){
			$files = C("UPLOAD_DIR").C('COMMENT_NAME');
			//echo C('ADMIN_URL');
			//exit;
			//up-file01032793826514844
			//up-file8793429144354882
			if(!file_exists($files))
			{
			     mkdir ($files,0777,true);
			}
			$uploadClass = new \Think\Upload();
			$uploadClass->maxSize=C('UPLOAD_SIZE');
			$uploadClass->exts=C('UPLOAD_TYPE'); 
			$uploadClass->rootPath=$files;
	      	$info = $uploadClass->upload();
		    //print_r($info);
		    //exit;
	      	if(!$info) {// 上传错误提示错误信息        
		        $this->error($uploadClass->getError());
		       //$this->error('上传失败');    
		    }else{// 上传成功        
		    	foreach ($info as $key => $val){
		    		$img = C('ADMIN_URL') . C('COMMENT_NAME') . $val['savepath'].$val['savename'];
					
		    	}
		    	//echo $img;
		    	//exit;
				//$img = C('UPLOAD_NAME').'/'. $info['files']['savepath'].$info['files']['savename'];
				$res['error'] = 0;
				$res['data'][] = $img;
				//$arr = json_encode($res);
		    	
				$this->ajaxReturn($res, 'JSON');
		    	
		    }
	
	} 
	
	
	
	
	
}	