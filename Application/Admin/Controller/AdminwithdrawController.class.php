<?php
namespace Admin\Controller;
use Think\Controller;
class AdminwithdrawController extends AdminbaseController{
	public $client;
	public $writes;
	
	function __construct(){
		parent::__construct();
		vendor('Hprose.HproseHttpClient');
        $this->client = new \HproseHttpClient(C('RAPIURL').'/Adminwithdraw');		//读取、查询操作
        //$this->writes = new \HproseHttpClient(C('WAPIURL').'/AdminCollectPoint');		//添加，修改 
        
	}
	
	/**
	 * 提现列表
	 * Enter description here ...
	 */
	
	public function withdraw_list(){
		$data['epage'] = C('EPAGE');
		$data['p'] = I('get.p');
		$status = I('get.type');
		switch ($status){
			case 'audited':
				$data['status'] = 0;
				break;
			case 'confirmed':
			  	$data['status'] = 1;
			  	break;
			case 'completed':
				$data['status'] = 2;
				break;
			case 'cancel':
				$data['status'] = 3;
				break;
			default:
			 
		}
		//print_r($data);
		//exit;
		//取得用户信息
		$res = $this->client->withdraw_list($data);
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
		//$top_nav = $this->top_nav;
		
		//$three_nav = $this->three_nav;
		//$this->assign('top_nav', $top_nav);
		//$this->assign('three_nav', $three_nav);

		foreach ($res['list'] as $key => $val){
			$list[$key] = $val;
			$list[$key]['status_name'] = C('WITHD_STATUS')[$val['examine_status']];
		}
		
		$this->assign('list', $list);
		$this->assign('page', $show);
		
		
		
		$this->display('index');
	}
	
	/**
	 * 会员提现详细
	 * Enter description here ...
	 */
	public function withdraw_info(){
		$id = I('get.id');
		if(empty($id)){
			$this->error('不存在该提现记录');
			exit;
		}
		$page_type = I('get.page_type');
		$page_two = I('get.page_two');
		$data['id'] = $id;
		$data['epage'] = C('Pay_EAGE');
		$data['p'] = I('get.p');
		if(!empty($page_type)){
			$data['page_type'] = $page_type;
			$data['page_two'] = $page_two;
		}else{
			$data['page_type'] = '';
			$data['page_two'] = 0;
		}
		$res = $this->client->withdraw_info($data);
		
		if(!empty($page_type) && $page_type == 'rech'){
			$_GET['p'] = $page_two;
		}
		
		$count = $res['rech']['count'];
		$page=new \Think\Page($count,$data['epage']);
		//%FIRST% 表示第一页的链接显示 
		//%UP_PAGE% 表示上一页的链接显示 
		//%LINK_PAGE% 表示分页的链接显示 
		//%DOWN_PAGE% 表示下一页的链接显示 
		//%END% 表示最后一页的链接显示 
		
		$page->setConfig('theme','%FIRST% %UP_PAGE% %LINK_PAGE% %DOWN_PAGE% %END% %HEADER%');
		$page ->setConfig('prev', '上一页');
		$page ->setConfig('next','下一页');
		$page ->setConfig('last','末页');
		$page ->setConfig('first','首页');
		$show_rech = $page->show();
		
		if(!empty($page_type) && $page_type == 'cons'){
			$_GET['p'] = $page_two;
		}
		$count = $res['cons']['count'];
		$page=new \Think\Page($count,$data['epage']);
		//%FIRST% 表示第一页的链接显示 
		//%UP_PAGE% 表示上一页的链接显示 
		//%LINK_PAGE% 表示分页的链接显示 
		//%DOWN_PAGE% 表示下一页的链接显示 
		//%END% 表示最后一页的链接显示
		
		$page->setConfig('theme','%FIRST% %UP_PAGE% %LINK_PAGE% %DOWN_PAGE% %END% %HEADER%');
		$page ->setConfig('prev', '上一页');
		$page ->setConfig('next','下一页');
		$page ->setConfig('last','末页');
		$page ->setConfig('first','首页');
		$show_cons = $page->show();
		
		
		foreach ($res['rech']['list'] as $key => $val){
			$res['rech']['list'][$key]['paykind_name'] = C('Pay_Kind')[$val['paykind']];
		}
		
		foreach ($res['cons']['list'] as $key => $val){
			$res['cons']['list'][$key]['status_name'] = C('PAY_STAT')[$val['pay_state']];
		}
		$Pay_Kind_alias = C('Pay_Kind_alias');
		foreach($Pay_Kind_alias AS $key => $val){
			$pay[$key] = C('Pay_Kind')[$val];
		}
		
		$res['cash']['mode_name'] = C('PAY_MODE')[$res['cash']['mode']];
		$res['cash']['examine_status_name'] = C('WITHD_STATUS')[$res['cash']['examine_status']];
		
		$res['cash']['cash_mode_name'] = C('Pay_Kind')[C('Pay_Kind_alias')[$res['cash']['cash_mode']]];
		
		//已完成显示
		if($res['cash']['examine_status'] == 2 || $res['cash']['examine_status'] == 3 ){
			$wh_cash['user_id'] = $res['cash']['user_id'];
			$wh_cash['epage'] = C('Pay_EAGE');
			$wh_cash['p'] = I('get.p');
			$complete = $this->client->withdraw_complete($wh_cash);
			
			$count = $complete['count'];
			$complete_list = $complete['list'];
			$page=new \Think\Page($count,$wh_cash['epage']);
			//%FIRST% 表示第一页的链接显示 
			//%UP_PAGE% 表示上一页的链接显示 
			//%LINK_PAGE% 表示分页的链接显示 
			//%DOWN_PAGE% 表示下一页的链接显示 
			//%END% 表示最后一页的链接显示
			
			$page->setConfig('theme','%FIRST% %UP_PAGE% %LINK_PAGE% %DOWN_PAGE% %END% %HEADER%');
			$page ->setConfig('prev', '上一页');
			$page ->setConfig('next','下一页');
			$page ->setConfig('last','末页');
			$page ->setConfig('first','首页');
			$show_complete = $page->show();	
			//print_r($complete_list);
			//exit;
			foreach ($complete_list as $key => $value){
				$complete_list[$key]['examine_status_name'] =  C('WITHD_STATUS')[$value['examine_status']];
				//$complete_list[$key]['mode_name'] =  C('PAY_MODE')[$value['mode']];
				//$complete_list[$key]['mode_name'] =  C('PAY_MODE')[$value['mode']];
				$complete_list[$key]['cash_mode_name'] = C('Pay_Kind')[C('Pay_Kind_alias')[$value['cash_mode']]];
		
			}
			//print_r($complete_list);
			//exit;
			$this->assign('show_complete', $show_complete);
			$this->assign('complete', $complete_list);
			
			
		}
		
		//print_r($complete_list);
		//exit;
		
		$this->assign('row', $res['cash']);
		$this->assign('rech', $res['rech']['list']);
		$this->assign('cons', $res['cons']['list']);
		$this->assign('show_rech', $show_rech);
		$this->assign('show_cons', $show_cons);
		$this->assign('paykind', $pay);
		if ($res['cash']['examine_status'] == 2 || $res['cash']['examine_status'] == 3 ){
			$this->display('withdraw_complete');
		}else{
			$this->display();
		}
	}
	
	/**
	 * 提现详细
	 * Enter description here ...
	 */
	public function withdraw_hadd(){
//		$id = I('post.id');
//		$examine_status = I('post.examine_status');
//		$node = I('post.node');
		$data = I('post.');
		//print_r($data);
		//exit;	
		//审核
		if(!empty($data['examine']) && $data['examine'] == 1){
			if(empty($data['note'])){
				//$this->error('请填写操作说明');
				//exit;
	     		$rew['status'] = 0;
	    		$rew['data']['strstr'] = '请填写操作说明';
	    		//$rew['data']['url'] = U('AdminAuth/member',array('id' =>$id));
	    		$this->ajaxReturn($rew);
		    	exit;   
				
				
			}			
			$row['id'] = $data['id'];
			$row['examine_user'] = session('admin')['adid'];
			$row['examine_time'] = date('Y-m-d : H:i:s', time());
			$row['note'] = $data['note'];
			$row['status'] = 'examine';
			//print_r($row);
			//exit;
			$res = $this->client->withdraw_hadd($row);
			if($res['status']){
				//$this->error($res['strstr'], U('Adminwithdraw/withdraw_info', array('id' => $row['id'])));
				//exit;
	     		$rew['status'] = 1;
	    		$rew['data']['strstr'] = $res['strstr'];
	    		$rew['data']['url'] = U('Adminwithdraw/withdraw_info', array('id' => $row['id']));
	    		$this->ajaxReturn($rew);
		    	exit; 				
				
			}else{
				//$this->error($res['errorstr']);
				//exit;
	     		$rew['status'] = 0;
	    		$rew['data']['strstr'] = $res['errorstr'];
	    		//$rew['data']['url'] = U('Adminwithdraw/withdraw_info', array('id' => $row['id']));
	    		$this->ajaxReturn($rew);
		    	exit; 	
				
			}
				
		}
		//确认
		else if(!empty($data['confirm']) && 1 == $data['confirm']){
			
			if(empty($data['online_amount']) || $data['online_amount'] <= 0 ){
				//$this->error('请输入在线转账金额');
				//exit;
	     		$rew['status'] = 0;
	    		$rew['data']['strstr'] = '请输入在线转账金额';
	    		//$rew['data']['url'] = U('AdminAuth/member',array('id' =>$id));
	    		$this->ajaxReturn($rew);
		    	exit;   				
				
				
			}
			if(empty($data['cash_service']) || $data['cash_service'] < 0 ){
				//$this->error('请输入在线转账手续费');
				//exit;
	     		$rew['status'] = 0;
	    		$rew['data']['strstr'] = '请输入在线转账手续费';
	    		//$rew['data']['url'] = U('AdminAuth/member',array('id' =>$id));
	    		$this->ajaxReturn($rew);
		    	exit;   				
			}
			if(empty($data['cash_mode'])){
				//$this->error('请选择退款方式');
				//exit;
	     		$rew['status'] = 0;
	    		$rew['data']['strstr'] = '请选择退款方式';
	    		//$rew['data']['url'] = U('AdminAuth/member',array('id' =>$id));
	    		$this->ajaxReturn($rew);
		    	exit;   								
				
			}
			if(empty($data['note'])){
				//$this->error('请填写操作说明');
				//exit;
	     		$rew['status'] = 0;
	    		$rew['data']['strstr'] = '请填写操作说明';
	    		//$rew['data']['url'] = U('AdminAuth/member',array('id' =>$id));
	    		$this->ajaxReturn($rew);
		    	exit;   								
				
				
			}
			$row['id'] = $data['id'];
			$row['cash_service'] = $data['cash_service'];
			$row['online_amount'] = $data['online_amount'];
			$row['cash_mode'] = $data['cash_mode'];
			$row['examine_user'] = session('admin')['adid'];
			$row['examine_time'] =  date('Y-m-d : H:i:s', time());
			$row['cash_mode'] = $data['cash_mode'];
			
			$row['note']	= $data['note'];
			$row['status'] = 'confirm';
//			print_r($row);
//			exit;
			$res = $this->client->withdraw_hadd($row);
			//print_r($res);
			//exit;
			if($res['status']){
				//$this->error($res['strstr'], U('Adminwithdraw/withdraw_info', array('id' => $row['id'])));
				//exit;
	     		$rew['status'] = 1;
	    		$rew['data']['strstr'] = $res['strstr'];
	    		$rew['data']['url'] = U('Adminwithdraw/withdraw_info', array('id' => $row['id']));
	    		$this->ajaxReturn($rew);
		    	exit;  				
				
			}else{
				//$this->error($res['errorstr']);
				//exit;
	     		$rew['status'] = 0;
	    		$rew['data']['strstr'] = $res['errorstr'];
	    		//$rew['data']['url'] = U('Adminwithdraw/withdraw_info', array('id' => $row['id']));
	    		$this->ajaxReturn($rew);
		    	exit; 				
				
			}
							
			
			
		}
		//取消
		else if(!empty($data['cancel']) && 1 == $data['cancel']){
			//print_r($data);
			//exit;
			if(empty($data['note'])){
				//$this->error('请填写操作说明');
				///exit;
	     		$rew['status'] = 0;
	    		$rew['data']['strstr'] = '请填写操作说明';
	    		//$rew['data']['url'] = U('Adminwithdraw/withdraw_info', array('id' => $row['id']));
	    		$this->ajaxReturn($rew);
		    	exit;
				
			}
			$row['id'] = $data['id'];
			$row['note']  = $data['note'];
			$row['examine_user'] = session('admin')['adid'];
			$row['examine_time'] = date('Y-m-d : H:i:s', time());
			$row['status'] = 'cancel';
			//print_r($row);
			//exit;
			$res = $this->client->withdraw_hadd($row);
			
			if($res['status']){
				//$this->error($res['strstr'], U('Adminwithdraw/withdraw_info', array('id' => $row['id'])));
				//exit;
	     		$rew['status'] = 1;
	    		$rew['data']['strstr'] = $res['strstr'];
	    		$rew['data']['url'] = U('Adminwithdraw/withdraw_info', array('id' => $row['id']));
	    		$this->ajaxReturn($rew);
		    	exit;
				
			}else{
				//$this->error($res['errorstr']);
				//exit;
				$rew['status'] = 0;
	    		$rew['data']['strstr'] = $res['errorstr'];
	    		$rew['data']['url'] = U('Adminwithdraw/withdraw_info', array('id' => $row['id']));
	    		$this->ajaxReturn($rew);
		    	exit;
				
			}
			
			
			
		}
	}
	
	/**
	 * 充值记录  无刷新分页
	 * Enter description here ...
	 */
	public function rech(){
		$id = I('get.id');
		if(empty($id)){
			$this->error('不存在该提现记录');
			exit;
		}
		
		$data['id'] = $id;
		$data['epage'] = C('Pay_EAGE');
		$data['p'] = I('get.p');
	
		$res = $this->client->rech($data);
		
		
		$count = $res['rech']['count'];
		$page=new \Think\Page($count,$data['epage']);
		//%FIRST% 表示第一页的链接显示 
		//%UP_PAGE% 表示上一页的链接显示 
		//%LINK_PAGE% 表示分页的链接显示 
		//%DOWN_PAGE% 表示下一页的链接显示 
		//%END% 表示最后一页的链接显示 
		
		$page->setConfig('theme','%FIRST% %UP_PAGE% %LINK_PAGE% %DOWN_PAGE% %END% %HEADER%');
		$page ->setConfig('prev', '上一页');
		$page ->setConfig('next','下一页');
		$page ->setConfig('last','末页');
		$page ->setConfig('first','首页');
		$show_rech = $page->show();

		
		foreach ($res['rech']['list'] as $key => $val){
			$res['rech']['list'][$key]['paykind_name'] = C('Pay_Kind')[$val['paykind']];
		}
		$res['rech']['show_rech'] = $show_rech;
		
		$this->ajaxReturn($res);
	    //exit; 
	}
	
	
	
	
	public function cons(){
		$id = I('get.id');
		if(empty($id)){
			$this->error('不存在该提现记录');
			exit;
		}
		$page_type = I('get.page_type');
		$page_two = I('get.page_two');
		$data['id'] = $id;
		$data['epage'] = C('Pay_EAGE');
		$data['p'] = I('get.p');
		if(!empty($page_type)){
			$data['page_type'] = $page_type;
			$data['page_two'] = $page_two;
		}else{
			$data['page_type'] = '';
			$data['page_two'] = 0;
		}
		$res = $this->client->cons($data);
		
		$count = $res['cons']['count'];
		$page=new \Think\Page($count,$data['epage']);
		//%FIRST% 表示第一页的链接显示 
		//%UP_PAGE% 表示上一页的链接显示 
		//%LINK_PAGE% 表示分页的链接显示 
		//%DOWN_PAGE% 表示下一页的链接显示 
		//%END% 表示最后一页的链接显示
		
		$page->setConfig('theme','%FIRST% %UP_PAGE% %LINK_PAGE% %DOWN_PAGE% %END% %HEADER%');
		$page ->setConfig('prev', '上一页');
		$page ->setConfig('next','下一页');
		$page ->setConfig('last','末页');
		$page ->setConfig('first','首页');
		$show_cons = $page->show();

		foreach ($res['cons']['list'] as $key => $val){
			$res['cons']['list'][$key]['status_name'] = C('PAY_STAT')[$val['pay_state']];
		}
		
		$res['cons']['show_cons'] = $show_cons;
		$this->ajaxReturn($res);
	}
	
	
	
}