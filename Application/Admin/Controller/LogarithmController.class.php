<?php
/**
 * 批号对数
 */
namespace Admin\Controller;
use Think\Controller;
header("Content-type: text/html; charset=utf-8");
class LogarithmController extends AdminbaseController{

    function _initialize() {
        parent::_initialize();
		$client = new \HproseHttpClient(C('RAPIURL').'/Logarithm');		//读取、查询操作
		$this->client = $client;	//全局变量
    }

	/**
	 * 中转跟踪
	 * @param  boolean $request [外部请求 其他控制器调用的凭证]
	 * @param  boolean $type    [外部请求 其他控制器调用的時候传入的中转线路类型tc.id]
	 * @return [type]           [description]
	 */
	public function index($request=false, $type=''){

		$keyword    = trim(I('get.keyword'));
		$searchtype = I('get.searchtype');
		$tcid       = (I('get.tcid')) ? trim(I('get.tcid')) : '';//20161116 jie   增加 TrandKd = 中转线路.id 的查询
		$starttime  = intval(I('starttime'));
		$endtime    = intval(I('get.endtime'));

        //按查询类型搜索
        if(!empty($keyword) && !empty($searchtype)){
            $map['tn.'.$searchtype] = array('like','%'.$keyword.'%');
        }

        // 20180307 jie   根据分配的线路权限获取线路相关的订单等信息
        $PublicLineData = new \Admin\Controller\PublicLineDataController();

        // 如果$request为true， 则为外部控制器的请求操作，把标签A覆盖为外部传入的数据 20170103 jie
        if($request == true){

        	$PublicLineData->line_id = $type;

        	$tcids = $PublicLineData->intersect();//一维数组

        	if($type == C('Transit_Type.MKBc3_Transit')){
        		// $type = explode(",",$type);
        		$map['tn.tcid'] = array('in',$tcids);
        	}else{
        		$tcids = implode(',',$tcids);
        		$map['tn.tcid'] = array('eq',$tcids);
        	}
        	// 只有当$type=C('Transit_Type.GdEms_Transit')的值的时候才执行此判断  20170315 Jie
        	if($type == C('Transit_Type.GdEms_Transit')) $map['tn.send_report'] = array('eq','1');// 只列出已经执行了“发货通知”的批次号
        
        }else{
        	/* 20180307 jie 根据分配的线路权限获取线路相关的订单等信息 */
            $PublicLineData->line_id = true;

            $tcids = $PublicLineData->intersect();//一维数组
            // 如果不是 全部线路权限，则根据实际分配的线路权限，读取线路信息
            if($tcids !== true){
            	$map['tn.tcid'] = array('in',$tcids);
            }
            /* 20180307 jie 根据分配的线路权限获取线路相关的订单等信息 */
        }

        // 20180307 jie   根据分配的线路权限获取线路相关的订单等信息
        $center_list = $PublicLineData->get_lines();
        $this->assign('center_list',$center_list);


        //如果是搜索请求，则优先按照搜索条件筛选数据
        if($tcid != ''){
        	$map['tn.tcid'] = array('eq',$tcid);//标签A
        }

		//按时间段搜索
		if(!empty($starttime) && !empty($endtime)){
			$starttime = date('Y-m-d H:i:s',$starttime);
			$endtime   = date('Y-m-d H:i:s',$endtime);
			$map['tn.date'] = array('between',$starttime.",".$endtime);

		}else if(!empty($starttime) && $endtime){
			$endtime = date('Y-m-d H:i:s',$endtime);
			$map['tn.date'] = array('elt',$endtime);

		}else if($starttime && !empty($endtime)){
			$starttime = date('Y-m-d H:i:s',$starttime);
			$map['tn.date'] = array('egt',$starttime);
		}

		$client = $this->client;
		$list = $client->_index($map, $request);

		if($request == true){
			return $list;
		}
        //dump($list['list']);exit();
		$this->assign('list',$list['list']);
		$this->assign('center_list',$center_list);
		$this->assign($_GET);
		$this->display();
	}

	/**
	 * 刷新数量的统计（左）
	 * @return [type] [description]
	 */
	public function reflash(){
		if(IS_AJAX){
			// sleep(1);
			$id = I('post.id');
			$client = $this->client;
			$result = $client->fresh_count($id);
			$this->ajaxReturn($result);
		}
	}

	// 统计整批批次号总重量
	public function count_weight(){
		if(IS_AJAX){
			$id = I('post.id');
			$client = $this->client;
			$result = $client->_count_weight($id);
			$this->ajaxReturn($result);
		}
	}

	/**
	 * 快递跟踪
	 * @param  boolean $request [外部请求 其他控制器调用的凭证]
	 * @param  boolean $type    [外部请求 其他控制器调用的時候传入的中转线路类型tc.id]
	 * @return [type]           [description]
	 */
	public function pro_two($request=false, $type=''){

		$keyword    = trim(I('get.keyword'));
		$searchtype = I('get.searchtype');
		$tcid       = (I('get.tcid')) ? trim(I('get.tcid')) : '';//20161116 jie   增加 TrandKd = 中转线路.id 的查询
		$starttime  = intval(I('starttime'));
		$endtime    = intval(I('get.endtime'));

		$sto = (I('get.sto')) ? trim(I('get.sto')) : 0;//用于判断是否已经点击查询按钮，当点击之后才显示数据 20161228 jie
		
        //按查询类型搜索
        if(!empty($keyword) && !empty($searchtype)){
            $map['tn.'.$searchtype] = array('like','%'.$keyword.'%');
        }

        // 20180307 jie   根据分配的线路权限获取线路相关的订单等信息
        $PublicLineData = new \Admin\Controller\PublicLineDataController();

        // 如果$request为true， 则为外部控制器的请求操作，把标签A覆盖为外部传入的数据 20170103 jie
        if($request == true){
        	$PublicLineData->line_id = $type;

        	$tcids = $PublicLineData->intersect();//一维数组

        	if($type == C('Transit_Type.MKBc3_Transit')){
        		// $type = explode(",",$type);
        		$map['tn.tcid'] = array('in',$tcids);
        	}else{
        		$tcids = implode(',',$tcids);
        		$map['tn.tcid'] = array('eq',$tcids);
        	}
        	// 只有当$type=C('Transit_Type.GdEms_Transit')的值的时候才执行此判断  20170315 Jie
        	if($type == C('Transit_Type.GdEms_Transit')) $map['tn.send_report'] = array('eq','1');// 只列出已经执行了“发货通知”的批次号
        
        }else{
        	/* 20180307 jie  根据分配的线路权限获取线路相关的订单等信息  */
            // $PublicLineData = new \Admin\Controller\PublicLineDataController();
            $PublicLineData->line_id = true;

            $tcids = $PublicLineData->intersect();//一维数组
            // 如果不是 全部线路权限，则根据实际分配的线路权限，读取线路信息
            if($tcids !== true){
            	$map['tn.tcid'] = array('in',$tcids);
            }
            // $center_list = $PublicLineData->get_lines();
            /* 20180307 jie   根据分配的线路权限获取线路相关的订单等信息   */
        }
        // 20180307 jie
        $center_list = $PublicLineData->get_lines();
        $this->assign('center_list',$center_list);

        // 如果是搜索条件中的某个线路，则优先使用此查询条件
        if($tcid != ''){
        	$map['tn.tcid'] = array('eq',$tcid);
        }

		//按时间段搜索
		if(!empty($starttime) && !empty($endtime)){
			$starttime = date('Y-m-d H:i:s',$starttime);
			$endtime   = date('Y-m-d H:i:s',$endtime);
			$map['tn.airdatetime'] = array('between',$starttime.",".$endtime);

		}else if(!empty($starttime) && $endtime){
			$endtime = date('Y-m-d H:i:s',$endtime);
			$map['tn.airdatetime'] = array('elt',$endtime);

		}else if($starttime && !empty($endtime)){
			$starttime = date('Y-m-d H:i:s',$starttime);
			$map['tn.airdatetime'] = array('egt',$starttime);
		}

		$client = $this->client;
		$list = $client->pro_two($map, U('Logarithm/showList'), $sto, $request);
		
		if($request == true){
			return $list;
		}
		$this->assign('list',$list['list']);
		$this->assign($_GET);
		$this->display();
	}

	/**
	 * 刷新数量的统计（右）
	 * @return [type] [description]
	 */
	public function toflash(){

		if(IS_AJAX){
			// sleep(1);
			$id   = I('post.id');
			$tcid = I('post.tcid');

			$client = $this->client;
			$result = $client->fresh_other($id, $type='', U('Logarithm/showList'), 0, $tcid); // 20170204 jie 修复
			$this->ajaxReturn($result);
		}
	}

	/**
	 * 某个详情
	 * @return [type] [description]
	 */
	public function info(){
		$id = I('id');
		
		$client = $this->client;
		$result = $client->info($id);

		$this->assign('info',$result[0]);
		$this->assign('msg',$result[1]);
		$this->display();
	}

	public function showList(){

		$p = I('get.p')?I('get.p'):1;	//当前页数，如果没有则默认显示第一页

		$Istate = I('get.IL_state')?I('get.IL_state'):"";
		$stype  = I('get.stype')?I('get.stype'):"";
		$noid   = I('get.noid')?I('get.noid'):"";

		$client = $this->client;
		$res = $client->showList($noid,$stype,$Istate,$p);

        $this->ilstarr = C('ilstarr');

        //$ilstarri中的0-9 是与 $ilstarr 中的key对应
        $this->ilstarri = C('ilstarri');

		$this->assign('list',$res['0']);	//数据列表
		// dump($res['0']);die;
		$page = new \Think\Page($res['1'],30); // 实例化分页类 传入总记录数和每页显示的记录数(20)
		$page->setConfig('prev', "上一页");//上一页
		$page->setConfig('next', '下一页');//下一页
		$page->setConfig('first', '首页');//第一页
		$page->setConfig('last', "末页");//最后一页
		$page->setConfig ( 'theme', '%FIRST% %UP_PAGE% %LINK_PAGE% %DOWN_PAGE% %END% %HEADER%' );
    		
		$show = $page->show(); // 分页显示输出
		$this->assign('page',$show);// 赋值分页输出
		$this->assign('mstr',$res['2']);// 
		$this->assign('record_list',$res['3']);// 
		
		$sel_list = C('LOGARTHM_SELECT');
		ksort($sel_list);
		$this->assign('sel_list',$sel_list);	//20160316 Jie

		$this->assign('tcid',trim(I('get.tcid'))); //20170119 jie
		$limit = $page->firstRow.','.$page->listRows;

		$this->display();
	}

	/**
	 * 快递跟踪 -- 手动完成某个订单 20160316 Jie
	 * @return [type] [description]
	 */
	public function toPost(){

		if(IS_AJAX){
			// $data = array();
			// $this->ajaxReturn($data);die;
			$id            = I('post.id');				// tran_list.id
			$status_select = I('post.status_select');	// select标签的val
			$status_custom = I('post.status_custom');	// 自定义原因
			$befinished    = I('post.finished');	// 完成此单 checkbox

			$value    = S('admin');
			$tname    = $value['adtname'];		//当前登陆的管理员
			$username = $this->change_code($tname);	//根据中文、字母或数字、字母和数字组合3种情况截取
			// $username = mb_substr($username, 0, 1, 'utf-8');

			//如果都为空
			if($status_select == '' && $status_custom == ''){
				$backArr = array('status'=>404, 'msg'=>'请选择原因！');
				$this->ajaxReturn($backArr);
			}

			//如果选择了自定义输入但是input的value为空
			if($status_select == '2000' && $status_custom == ''){
				$backArr = array('status'=>404, 'msg'=>'请填写原因！');
				$this->ajaxReturn($backArr);
			}

			// 必须勾选 “完成此单” 才能操作 已完成 这个事务
			if($status_select == '2010' && $befinished != 'on'){
				$backArr = array('status'=>404, 'msg'=>'必须勾选【完成此单】才能进行此操作！');
				$this->ajaxReturn($backArr);
			}

			$arr_message = C('LOGARTHM_SELECT');
			$client   = $this->client;
			$res  = $client->_toPost($id,$status_select,$status_custom,$befinished,$username,$arr_message,$tname);
			$this->ajaxReturn($res);
			
/*			$info = M('TranList')->field('noid,MKNO,CID,IL_state')->where(array('id'=>$id))->find();

			if($info['IL_state'] == '1003'){
				$backArr = array('status'=>0, 'msg'=>'该单已被操作完成');
				$this->ajaxReturn($backArr);
			}

			//如果自定义原因为空，则取select标签的val
			if($status_custom == ''){
				
				$data['content']     = $arr_message[$status_select].'('.$username.'**)';
				$data['status']      = 2000 + intval($status_select);

			}else{//如果自定义原因为空，则取$status_custom的值

				$data['content']     = $status_custom.'('.$username.'**)';	//自定义原因
				$data['status']      = 2000;	//自定义的原因默认为0，所以相加得2000
			}

			$data['MKNO']        = $info['MKNO'];
			$data['noid']        = $info['noid'];
			$data['create_time'] = date('Y-m-d H:i:s');
			$data['CID']         = $info['CID'];
			$res = M('IlLogs')->add($data);
			if($res){
				M('TranList')->where(array('id'=>$id))->setField('IL_state','1003');	//将IL_state 更新为 已签收状态(1003)
				$backArr = array('status'=>1, 'msg'=>'成功');
				$this->ajaxReturn($backArr);
			}else{
				$backArr = array('status'=>0, 'msg'=>'失败');
				$this->ajaxReturn($backArr);
			}*/

		}
	}

	/**
	 * 快递跟踪 -- 手动完成多个订单 20160511 Jie
	 * @return [type] [description]
	 */
	public function anymore(){

		if(IS_AJAX){

			$ids           = I('post.ids','');				// tran_list.id
			$status_select = I('post.status_select');	// select标签的val
			$status_custom = I('post.status_custom');	// 自定义原因
			$befinished    = I('post.finished');	// 完成此单 checkbox

			$value    = session('admin');
			$tname    = $value['adtname'];		//当前登陆的管理员
			$username = $this->change_code($tname);	//根据中文、字母或数字、字母和数字组合3种情况截取
			// $username = mb_substr($username, 0, 1, 'utf-8');

			//如果都为空
			if($status_select == '' && $status_custom == ''){
				$backArr = array('status'=>404, 'msg'=>'请选择原因！');
				$this->ajaxReturn($backArr);
			}

			//如果选择了自定义输入但是input的value为空
			if($status_select == '2000' && $status_custom == ''){
				$backArr = array('status'=>404, 'msg'=>'请填写原因！');
				$this->ajaxReturn($backArr);
			}

			if($ids == ''){
				$backArr = array('status'=>404, 'msg'=>'请选择至少一个面单进行操作！');
				$this->ajaxReturn($backArr);
			}

			// 必须勾选 “完成选中的面单” 才能操作 已完成 这个事务
			if($status_select == '2010' && $befinished != 'on'){
				$backArr = array('status'=>404, 'msg'=>'必须勾选【完成选中的面单】才能进行此操作！');
				$this->ajaxReturn($backArr);
			}

			$ids_arr = explode(",",$ids);	//转换成数组
			// die;
			$arr_message = C('LOGARTHM_SELECT');
			$client   = $this->client;
			$res  = $client->_anyMore($ids_arr,$status_select,$status_custom,$befinished,$username,$arr_message,$tname,true);
			$this->ajaxReturn($res);

		}
	}
	/**
	 * 导出 视图
	 * @return [type] [description]
	 */
	public function export(){
		$arr = C('Export_Type');
		$this->assign('selectList',$arr);
		$this->display();
	}

	/**
	 * 导出 方法
	 * @return [type] [description]
	 */
	public function export_file(){
		if(IS_POST){
			//查询
			$noid = I('id');
			$type = I('type');

			/* 20160123 */
			$value    = session('admin');
			$receiver = $value['ademail'];	//"mkil2015@163.com";	//收件人邮箱地址  此处为管理员自己的邮箱
			$username = $value['adtname'];	//当前登录的管理员
			$extime   = date('Y-m-d H:i:s');
			// $list = M('TranList l')
			// ->field('0 as f0,l.id as f1,l.MKNO as f2,l.STNO as f3,o.detail as f4,o.catname as f5,o.price as f6,o.number as f7,(o.number * o.price) as f8,l.weight as f9,round((l.weight / 2.2046),4) as f10,l.receiver as f11,l.sfid as f12,l.reAddr as f13,l.reTel as f14,l.postcode as f15,l.city as f16')
			// ->join('RIGHT JOIN mk_tran_order o ON o.lid = l.id')
			// ->where(array('noid'=>$noid))->select();
			// //获取导出csv的模板类型，1为申通csv模式，2为顺丰csv模式
			// $tranline = M('TransitNo n')->field('c.csv_type,c.email')->join('LEFT JOIN mk_transit_center c ON n.tcid = c.id')->where(array('n.id'=>$noid))->find();

			$client   = $this->client;
			$getlist  = $client->_export_file($noid,$type);

			$switch = C('Test.sendemail') ? C('Test.sendemail') : false;

			// 根据不同的transit_center.id判断对应的物流类型
			$transit_type = C('Transit_Type');
			$transit_type = array_flip($transit_type);

			/* 20160126 */
			//返回exist则表示最近一天已经执行过导出，且最近没有数据总数的变动
			if($getlist[1] == 'exist'){
				
				$fileurl  = $getlist[0];
				$filename = basename($fileurl);
				$tranline = $getlist[2];
				$content  = '<div style="font-size:15px;">'.$username.' 您好，<br>'.'<span style="padding-left:30px;">'.$extime.' 您进行了批号为【'.$tranline['no'].'】的资料导出，请查看以下附件。</span></div>
				<br /><br /><div style="text-align:right;"><p>如有疑问请与管理员联系：dev@megao.cn</p><p>广州美快软件开发有限公司</p></div>';

		    	// 调试的时候禁止发送邮件，防止邮箱账号被封 20161109 jie
		    	if($switch === true){
		    		// 测试用
					$tips = array('status'=>'1','msg'=>"文件已发送到[今日已执行过导出](测试用，已关闭邮件发送)");
					$this->ajaxReturn($tips);die;
		    	}else{
		    		// 非调试形式， 正常执行功能
		    		$this->MkilMail($filename,$receiver,$content,$fileurl,$username,$tranline['email']);	//发送邮件
		    	}
				
				exit;
			}

			$list     = $getlist[0];
			$tranline = $getlist[1];

			// 设置为文本无科学计数
			foreach($list as $key=>$item){
				foreach($item as $k=>$it){
					if(in_array($k, array('f3','f12','f14','f17','f18'))){
						$list[$key][$k] = "\t".$it;	// 符合规则的，则在字段前面添加"\t"
					}
				}
			}

			//设置csv表头
        	// $title = '序号,美快单号,顺丰单号,物品名称,类别,申报单价,物品数量,申报实价,实际重量(LB),实际重量(KG),收货人,身份证号码,收货地址,收货人电话,邮政编码,城市,no_1,no_2,发件人';
        	
        	// 20170220 jie  去除---身份证号、收货地址、收件人电话、邮政编码
        	$title = '序号,美快单号,顺丰单号,物品名称,类别,申报单价,物品数量,申报实价,实际重量(LB),实际重量(KG),收货人,城市,no_1,no_2,发件人';

        	//省略包含的字段的值，f0是默认需要省缺的
	        $clist = array('f0','f1','f2','f3','f11','f12','f13','f14','f15');

	        $fpd = $transit_type[$tranline['id']]; // 20160912 jie 文件名前缀

	        $exportexcel  = new \Libm\MKILExcel\MkilExportMarket;
	        
			$filename = $fpd."-".date('YmdHis');				//导出的文件名
			$fileurl  = ADMIN_ABS_FILE.'/'.$fpd.'/'.$filename;	//20170220 jie
			$exportexcel->SaveName   = $fileurl;	//包含路径+文件名;
			$exportexcel->Title      = $title;		//单元格表头
			$exportexcel->Data       = $list;		//导出数据数组
			$exportexcel->Format     = $type;//'2007';   	// 导出类型：csv, 2003表示excel2003, 2007表示excel2007
			$exportexcel->Clear_List = $clist;   	// 需执行清空字段数组
			$exportexcel->Model_Type = $tranline['csv_type'];//'2';   	// 是否进行省略操作
			$exportexcel->export();  				// 返回true,false

			if($exportexcel->export() !== false){

				$content  = '<div style="font-size:15px;">'.$username.' 您好，<br>'.'<span style="padding-left:30px;">'.$extime.' 您进行了批号为【'.$tranline['no'].'】的资料导出，请查看以下附件。</span></div>
				<br /><br /><div style="text-align:right;"><p>如有疑问请与管理员联系：dev@megao.cn</p><p>广州美快软件开发有限公司</p></div>';

	    		if($exportexcel->Format == 'csv'){
					$filename .= '.csv';
					$fileurl  .= '.csv';
	    		}else if($exportexcel->Format == '2003'){
					$filename .= '.xls';
					$fileurl  .= '.xls';
	    		}else if($exportexcel->Format == '2007'){
					$filename .= '.xlsx';
					$fileurl  .= '.xlsx';
	    		}

	    		if(!is_file($fileurl)) $this->ajaxReturn(array('status'=>'0','msg'=>'导出文件不存在'));
	    		
	    		$client->export_notes_add($fileurl,$extime,count($list),$type);	//保存导出记录到mk_export_notes

		    	// 调试的时候禁止发送邮件，防止邮箱账号被封 20161109 jie
		    	if($switch === true){
		    		// 测试用
					$tips = array('status'=>'1','msg'=>"文件已发送到[首次导出](测试用，已关闭邮件发送功能)");
					$this->ajaxReturn($tips);die;
		    	}else{

		    		// 非调试形式， 正常执行功能
		    		$this->MkilMail($filename,$receiver,$content,$fileurl);	//发送邮件
		    	}

				// $backArr = array('state'=>'1','msg'=>'done');
				// $this->ajaxReturn($backArr);
			}else{
				$backArr = array('status'=>'0','msg'=>'数据导出失败');
				$this->ajaxReturn($backArr);
			}
		}
	}

	/**
	 * 调用邮件发送
	 * @param [type] $title    [邮件标题]
	 * @param [type] $receiver [收件人]
	 * @param [type] $content  [邮件内容]
	 * @param [type] $fileurl  [附件路径]
	 * @param [type] $username [收件人的昵称]
	 * @param [type] $another_email [另一个收件人邮箱]
	 */
    public function MkilMail($title,$receiver,$content,$fileurl){
    	$EMAIL_SET = C('EMAIL_SET2');

		$args = array(
            'to' => array(
                $receiver,     //收件人地址，可填写多个
            ),
            'CC' => array(
                $EMAIL_SET['EMAIL_COPE_TO'],        //抄送，可选
            ),
            // 'BCC' => array(
            //                             //密送，可选
            // ),
			'FromName'   => $EMAIL_SET['EMAIL_COPE_NAME'],    //发件人姓名，可选
			'title'      => $title,
			'content'    => $content,
			'type'       => 'html',
			'attachment' => array(
                $fileurl,      //附件的路径，可选
                // dirname(__FILE__) . '/attachment/002.txt',
            ),
        );

        $phpmail = new \Lib11\PHPMailer\PHPMailerTools();
        $result = $phpmail->sendMail($args);

		//发送
		if($result['success'] === false) {
			$tips = array('status'=>'0','msg'=>"Mailer Error: " . $result['error']);
			$this->ajaxReturn($tips);

		} else {
			$tips = array('status'=>'1','msg'=>"文件已发送到您的邮箱，请注意查收");
			$this->ajaxReturn($tips);
			//echo "邮件已发送！";
		}

		// require_once 'MkilMailer.class.php'; //载入PHPMailer类

		// $EMAIL_SET = C('EMAIL_SET2');
		// $mailTo    = array(array($receiver,$username));		//收件人
		// array_push($mailTo, array($another_email,'')); 		//添加多个收件人（地址，昵称）

		// $EMAIL_SET['Subject']       = $title;
		// $EMAIL_SET['Content']       = $content;
		// $EMAIL_SET['AddAddress']    = $mailTo;
		// $EMAIL_SET['AddAttachment'] = array(array($fileurl,$title));

		// $mail = new \MkilMailer($EMAIL_SET); //实例化

		// //发送
		// if(!$mail->Send()) {
		// 	$another = new \MkilMailer($EMAIL_SET); //实例化
		// 	$another->anotherSend();

		// 	if($another->anotherSend()) {
		// 		$backArr = array('status'=>'1','msg'=>"Mailer Msg: 已用备用账号发送邮件");
		// 		$this->ajaxReturn($backArr);
		// 	}else{
		// 		$backArr = array('status'=>'0','msg'=>"Mailer Msg: 邮件发送失败");
		// 		$this->ajaxReturn($backArr);
		// 	}

		// } else {

		// 	$backArr = array('status'=>'1','msg'=>"文件已发送到您的邮箱，请注意查收");
		// 	$this->ajaxReturn($backArr);
		// 	//echo "邮件已发送！";
		// }
    }

    /**
     * 根据中文、字母或数字、字母和数字组合3种情况截取
     * @param  [type] $str [description]
     * @return [type]      [description]
     */
    public function change_code($str){
    	//如果是纯字母、纯数字、字母+数字组合
	    if (preg_match('/^[0-9a-zA-Z]*$/',$str)) {
	        $str1 = substr_replace($str,'**',2);
	    } else {	//如果是纯中文
	        $str1 = mb_substr($str,0,1,'utf-8').'**';
	    }
	    return $str1;
    }

    //以下方法是补录提单信息功能的  20180625xieyiyi

    //获取提单列表
    public function lading_list()
    {
        //线路id
        $line = I('get.line','');

        //获取所有可用线路
        $center = $this->client->_center_list();

        $ePage = (I('get.ePage')) ? trim(I('get.ePage')) : C('EPAGE');
        $p     = (I('p')) ? trim(I('p')) : '1';

        $where['TranKd'] = $line;
        $res = $this->client->ladingList($where,$p,$ePage);

        $count = $res['count'];
        $list  = $res['list'];

        $page = new \Think\Page($count,$ePage); // 实例化分页类 传入总记录数和每页显示的记录数(20)

        $page->setConfig('prev', "上一页");//上一页
        $page->setConfig('next', '下一页');//下一页
        $page->setConfig('first', '首页');//第一页
        $page->setConfig('last', "末页");//最后一页
        $page->setConfig ( 'theme','%FIRST% %UP_PAGE% %LINK_PAGE% %DOWN_PAGE% %END% %HEADER%');

        $show = $page->show(); // 分页显示输出

        $this->assign('page',$show);// 赋值分页输出
        $this->assign('list',$list);
        $this->assign('ePage',$ePage);
        $this->assign('center',$center);
        $this->assign('line',$line);
        $this->display();
    }

    //添加提单号
    public function lading_add()
    {
        if(IS_POST){
            $lading_no = I('post.lading_no');//提单号
            $transport_no = I('post.transport_no');//航班号
            $gross_weight = I('post.grossweight');//毛重
            $net_weight = I('post.netweight');//净重
            $number = I('post.number');//订单总量
            $price = I('post.price');
            $take_off_time = I('post.taketime');//起飞时间
            $arrive_time = I('post.arrivetime');//抵达时间
            $note = I('post.remark');//备注
            $nos_arr = I('post.nos');//选择的批次号id数组
            $line = I('post.line');//线路id

            $zzState = I('post.zzState',0);//0不推送  1准备推送
            if($zzState == 1){
                $lad_data['zzState'] = 1;
            }else{
                $lad_data['zzState'] = 0;
            }

            if(empty($nos_arr)){
                $this->ajaxReturn(array('state'=>'no','msg'=>'至少选择一个批次号'));
            }

            $tranKd = $this->TranKd($line);
            if($tranKd === false){
                $this->ajaxReturn(array('state'=>'no','msg'=>'线路不存在或者被禁用'));
            }

            //验证
            $verify = $this->lading_verify($lading_no,$transport_no,$gross_weight,$net_weight,$number,$price,$take_off_time,$arrive_time);
            if($verify !== true){
                return $this->ajaxReturn($verify);
            }

            //判断提单号是否重复
            if(! $this->client->lading_nos($lading_no)){
                $this->ajaxReturn(array('state'=>'no','msg'=>'提单号重复'));
            }

            $lad_data['lading_no'] = $lading_no;
            $lad_data['take_off_time'] = $take_off_time;
            $lad_data['arrive_time'] = $arrive_time;
            $lad_data['gross_weight'] = $gross_weight;
            $lad_data['net_weight'] = $net_weight;
            $lad_data['number']  = $number;
            $lad_data['price'] = $price;
            $lad_data['transport_no'] = $transport_no;
            $lad_data['TranKd'] = $line;

            $log_data['note'] = $note;
            $log_data['operator_id'] = session('admin')['adid'];

            $res = $this->client->lading_add($lad_data,$log_data,$nos_arr);
            $this->ajaxReturn($res);
        }else{
            $line = I('get.line');
            if(empty($line)){
                exit('请选择线路');
            }
            //获取所有符合条件的批次号
            $nos = $this->client->transitNos($line);
            $this->assign('nos',$nos);
            $this->assign('line',$line);
            $this->display();
        }
    }

    //编辑提单号
    public function lading_edit()
    {
        if(IS_POST){
            $lading_no = I('post.lading_no');//提单号
            $transport_no = I('post.transport_no');//航班号
            $gross_weight = I('post.grossweight');//毛重
            $net_weight = I('post.netweight');//净重
            $number = I('post.number');//订单总量
            $price = I('post.price');
            $take_off_time = I('post.taketime');//起飞时间
            $arrive_time = I('post.arrivetime');//抵达时间
            $note = I('post.remark');//备注
            $nos_arr = I('post.nos');//选择的批次号id数组
            $id = I('post.id');//提单号id
            $line = I('post.line');//线路id

            $zzState = I('post.zzState',0);//0不推送  1准备推送
            if($zzState == 1){
                $lad_data['zzState'] = 1;
            }else{
                $lad_data['zzState'] = 0;
            }

            if(empty($nos_arr)){
                $this->ajaxReturn(array('state'=>'no','msg'=>'至少选择一个批次号'));
            }

            $tranKd = $this->TranKd($line);
            if($tranKd === false){
                $this->ajaxReturn(array('state'=>'no','msg'=>'线路不存在或者被禁用'));
            }

            //验证
            $verify = $this->lading_verify($lading_no,$transport_no,$gross_weight,$net_weight,$number,$price,$take_off_time,$arrive_time);
            if($verify !== true){
                return $this->ajaxReturn($verify);
            }

            //判断提单号是否重复
            if(! $this->client->lading_nos($lading_no,$id)){
                $this->ajaxReturn(array('state'=>'no','msg'=>'提单号重复'));
            }

            $lad_data['lading_no'] = $lading_no;
            $lad_data['take_off_time'] = $take_off_time;
            $lad_data['arrive_time'] = $arrive_time;
            $lad_data['gross_weight'] = $gross_weight;
            $lad_data['net_weight'] = $net_weight;
            $lad_data['number']  = $number;
            $lad_data['price'] = $price;
            $lad_data['transport_no'] = $transport_no;

            $log_data['note'] = $note;
            $log_data['operator_id'] = session('admin')['adid'];

            $res = $this->client->lading_edit($id,$lad_data,$log_data,$nos_arr);
            $this->ajaxReturn($res);

        }else{
            $line = I('get.line');
            if(empty($line)){
                exit('请选择线路');
            }

            $id = I('get.id');
            $where['id'] = $id;
            $where['TranKd'] = $line;
            $info = $this->client->lading_info($where);
            if($info === false){
                exit('查找数据失败');
            }

            //获取所有符合条件的批次号
            $nos = $this->client->transitNos($line,$id);
            $this->assign('nos',$nos);
            $this->assign('info',$info);
            $this->assign('line',$line);
            $this->display();
        }
    }

    //查看提单信息
    public function lading_info()
    {
        $line = I('get.line');
        if(empty($line)){
            exit('请选择线路');
        }

        $id = I('get.id');
        $where['id'] = $id;
        $where['TranKd'] = $line;
        $info = $this->client->lading_info($where);
        if($info === false){
            exit('查找数据失败');
        }

        $this->assign('info',$info);
        $this->assign('line',$line);
        $this->display();
    }

    //提单验证
    public function lading_verify($lading_no,$transport_no,$gross_weight,$net_weight,$number,$price,$take_off_time,$arrive_time)
    {
        if(! preg_match("/^[A-Za-z0-9]{1,30}$/",$lading_no)){
            return array('state'=>'no','msg'=>'提单号由1~30位的数字与字母组成');
        }

        if(! preg_match("/^[A-Za-z0-9]{1,30}$/",$transport_no)){
            return array('state'=>'no','msg'=>'航班号由1~30位的数字与字母组成');
        }

        if(! preg_match("/^[1-9]\d{0,9}(\.\d{0,3})?$/",$gross_weight)){
            return array('state'=>'no','msg'=>'毛重格式不正确：最多10位整数，3位小数');
        }

        if(! preg_match("/^[1-9]\d{0,9}(\.\d{0,3})?$/",$net_weight)){
            return array('state'=>'no','msg'=>'净重格式不正确：最多10位整数，3位小数');
        }

        if(! preg_match("/^[1-9]\d{0,5}$/",$number)){
            return array('state'=>'no','msg'=>'总量为1~6位长度的整数');
        }

        if(! preg_match("/^[1-9]\d{0,9}(\.\d{0,2})?$/",$price)){
            return array('state'=>'no','msg'=>'总额格式不正确：最多10位整数，2位小数');
        }

        $start = strtotime($take_off_time);
        $end   = strtotime($arrive_time);
        if($start >= $end){
            return array('state'=>'no','msg'=>'起飞时间不能晚于等于抵达时间');
        }

        return true;
    }

    //判断线路是否可用
    public function TranKd($line)
    {
        $info = $this->client->TranKd($line);
        return $info;
    }
}