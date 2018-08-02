<?php
/**
 * 申通号管理
 */
namespace Admin\Controller;
use Think\Controller;
header('Content-Type:text/html; charset=utf-8');
class ExpressController extends AdminbaseController{

    function _initialize() {
        parent::_initialize();
        $client = new \HproseHttpClient(C('RAPIURL').'/Express');		//读取、查询操作
        $this->client = $client;		//全局变量

    }

	public function index(){

		$keyword    = trim(I('get.keyword'));
		$searchtype = I('get.searchtype');
		$status     = I('get.status');
		$starttime  = intval(I('get.starttime'));
		$endtime    = intval(I('get.endtime'));
		
		$p = (I('p')) ? trim(I('p')) : '1';
		$ePage = (I('get.ePage')) ? trim(I('get.ePage')) : C('EPAGE');
		$this->assign($_GET);
		$this->assign('ePage',$ePage);
		
		if(!empty($keyword) && !empty($searchtype)){
			$where["s.".$searchtype]=array('like','%'.$keyword.'%');
		}

		if($starttime && $endtime){
			$where['usetime'] = array('between',array($starttime,$endtime));
		}else if(!$starttime && $endtime){
			$where['usetime'] = array('elt',$endtime);
		}else if($starttime && !$endtime){
			$where['usetime'] = array('egt',$starttime);
		}

        if($status != ''){
            $where['s.status'] = $status;
        }

        $client = $this->client;

		$res = $client->count($where,$p,$ePage);	//统计总数
		$count = $res['count'];
		$list  = $res['list'];
		$warn  = $res['warn'];

		$page = new \Think\Page($count,$ePage); // 实例化分页类 传入总记录数和每页显示的记录数(20)

	$page->setConfig('prev', "上一页");//上一页
	$page->setConfig('next', '下一页');//下一页
	$page->setConfig('first', '首页');//第一页
	$page->setConfig('last', "末页");//最后一页
	$page->setConfig ( 'theme', '%FIRST% %UP_PAGE% %LINK_PAGE% %DOWN_PAGE% %END% %HEADER%' );
    		
		$show = $page->show(); // 分页显示输出
		$this->assign('page',$show);// 赋值分页输出

		$warn_num = C('WARNING')?C('WARNING'):1000;	//设置提示数量

		$this->assign('warn',$warn);
		$this->assign('warn_num',$warn_num);
		$this->assign('als',$res['als']);
		$this->assign('list',$list);
		$this->assign('id',$list['id']);

		$this->display();
	}

	/**
	 * 生成申通号 视图
	 */
	public function createView(){

		$this->assign('v','10');
		$this->display();
	}

	/**
	 * 生成申通号 方法
	 */
	public function Add(){

		if(!IS_POST){
			die('非法操作');
		}

		$left  = trim(I('post.fixed'));		//固定开始值
		$start = trim(I('post.start'));		//开始
		$end   = trim(I('post.end'));			//结束
		$long  = trim(I('post.long'));		//总长度

		// $left  = 'ST9900';//I('post.fixed');	//固定开始值
		// $start = '200';//I('post.start');		//开始
		// $end   = '216';//I('post.end');			//结束
		// $long  = '12';//I('post.long');			//总长度

		if(intval($end) < intval($start)){
			$result = array('state' => 'no','msg' => '请按照左小右大的形式进行填写');
			$this->ajaxReturn($result);
			// $this->error('请按照左小右大的形式进行填写');
		}
		$rest = intval($long)-strlen($left);   	//计算出剩余需要拼接的长度

		$count = (intval($end)-intval($start))+1;		//计算出需要生成的总数

		$client = $this->client;
		$result = $client->newAdd($count,$left,$start,$rest);

		if($result == $count){
			$result = array('state' => 'yes','msg' => '添加成功！总共添加了'.$result.'个号码');
		}else{
			$res = $count - $result;
			$result = array('state' => 'yes','msg' => '添加成功，总共添加了'.$result.'个号码，有'.$res.'个已存在');
		}
		$this->ajaxReturn($result);

	}

//======================================== csv文件保存到服务器端 ====================================
	/**
	 * 生成csv文件到服务器端
	 */
    //数据处理
    public function CreateCSV(){

		if(!IS_POST){
			die('非法操作');
		}

		// ini_set('memory_limit','4088M');
		// ini_set('max_execution_time', '0');
		$username = session('admin.adname');

        $client = $this->client;

		$tips = $client->getCSV($username);

		if($tips['status'] == '404'){
			$this->ajaxReturn($tips);
			exit;

		}else{

			$str = $tips['str'];
			$n   = $tips['i'] - 1;

	        $exportexcel  = new \Libm\MKILExcel\MkilExportMarket;
			$filename = 'STNO-'.date('YmdHis')."(".$n.")";				//导出的文件名，无需后缀
			$fileurl  = ADMIN_ABS_FILE.'/ST_Export/'.$username.'/'.$filename;	//20170220 jie
			$exportexcel->SaveName   = $fileurl;	//包含路径+文件名;
			$exportexcel->Title      = $tips['title'];		//单元格表头
			$exportexcel->Data       = $str;		//导出数据数组
			$exportexcel->Format     = 'csv';   	// 导出类型：csv, 2003表示excel2003, 2007表示excel2007
			$exportexcel->Model_Type = '1';   	// 是否进行省略操作
			$exportexcel->Sort       = false;   	// 是否带序号
			$exportexcel->export();  				// 返回true,false

			if($exportexcel->export() !== false){
				$client->reflash($filename,$addId = $tips['addId'],$i = $tips['i']);	//更新export_record
				
		    	$receiver = session('admin.ademail');//"mkil2015@163.com";	//收件人邮箱地址  此处为管理员自己的邮箱

		    	// $warn = $client->getWarn();		//余下的电子单数量
		    	$warn = $tips['warn'];

		    	$warning = '';
		    	$warn_num = C('WARNING')?C('WARNING'):1000;	//设置提醒数量

		    	if($warn < $warn_num){
		    		$warning = "<br /><font style='color:red;padding-left:30px;'>注意：电子单仅剩余 <span style='font-size:18px;'>".number_format($warn)."</span> 张，请<span style='font-size:18px;font-weight:bold;'> 尽快 </span>及时补充，亦恳请申通见字后将新的电子单号发送至 dev@megao.cn 多谢！</font>";
		    	}else{
		    		$warning = "<br /><font style='padding-left:30px;'>电子单剩余 ".number_format($warn)." 张</font>";
		    	}

		    	$content = '<div style="font-size:15px;">'.$username.' 您好，<br>'.'<span style="padding-left:30px;">'.$tips['ctime'].' 您进行了申通号资料的导出，请查看以下附件。</span>'.$warning.'</div>
		    	<br /><br /><div style="text-align:right;"><p>如有疑问请与管理员联系：dev@megao.cn</p><p>广州美快软件开发有限公司</p></div>';

				$filename .= '.csv';
				$fileurl  .= '.csv';

				if(!is_file($fileurl)) $this->ajaxReturn(array('state'=>'0','msg'=>'导出文件不存在'));

		    	// 调试的时候禁止发送邮件，防止邮箱账号被封 20161109 jie
		    	$switch = C('Test.sendemail') ? C('Test.sendemail') : false;
		    	if($switch === true){
		    		// 测试用
					$backArr = array('status'=>'1','msg'=>"文件已发送到[已屏蔽短信发送]");
					$this->ajaxReturn($backArr);die;
		    	}else{

		    		// 非调试形式， 正常执行功能
		    		$this->MkilMail($filename,$receiver,$content,$fileurl);
		    	}
			}else{
				$backArr = array('state'=>'0','msg'=>'数据导出失败');
				$this->ajaxReturn($backArr);
			}
			
		}
		

    }

//=============================
    public function st_csv(){
		ini_set('max_execution_time', 0);
		ini_set('memory_limit','4088M');
    	$noid = I('no');
        $client = $this->client;

		$tips = $client->_st_csv($noid);
		// dump($tips);die;
		$str = $tips['str'];
		$n   = $tips['i'] - 1;

	    $exportexcel  = new \Libm\MKILExcel\MkilExportMarket;
		$filename = 'STNO-'.date('YmdHis')."(".$n.")";				//导出的文件名，无需后缀
		$fileurl  = ADMIN_ABS_FILE.'/ST_Export/'.$username.'/'.$filename;	//20170220 jie
		$exportexcel->SaveName   = $fileurl;	//包含路径+文件名;
		$exportexcel->Title      = $tips['title'];		//单元格表头
		$exportexcel->Data       = $str;		//导出数据数组
		$exportexcel->Format     = '2007';   	// 导出类型：csv, 2003表示excel2003, 2007表示excel2007
		$exportexcel->Model_Type = '1';   	// 是否进行省略操作
		$exportexcel->Sort       = false;   	// 是否带序号
		$exportexcel->OutPut 	 = true;
		$exportexcel->export();  				// 返回true,false
    }

//=============================== End ===========================

    /**
     * 20160114 Jie 改用此函数进行发送邮件
     * 邮件发送  请注意发送邮件的邮箱是否开启了SMTP功能，未开启则无法使用第三方发送功能
     * @param  [type] $title    [邮件标题]
     * @param  [type] $receiver [收件人]
     * @param  [type] $content  [发送的内容]
     * @param  [type] $fileurl  [上传文件的文件路径，文件名需要补充相应的后缀]
     * @return [type]           [description]
     */
    
    public function MkilMail($title,$receiver,$content,$fileurl){
    	try{

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

            $DataNote               = new \Libm\DataNotes\DataNote();
            $DataNote->RequestData  = $args;
            $DataNote->ResponseData = $result;
            $DataNote->save_dir     = ADMIN_ABS_FILE.'/ST_Export_CSV_Eamil/';
            $DataNote->file_name    = 'Export_Record.txt';
            $DataNote->save();

	  //   	/*写法一*/
			// require_once 'MkilMailer.class.php'; //载入PHPMailer类

			// $EMAIL_SET = C('EMAIL_SET2');
			// $mailTo    = array(array($receiver,$username));		//收件人
			// array_push($mailTo, array($EMAIL_SET['EMAIL_COPE_TO'],$EMAIL_SET['EMAIL_COPE_NAME'])); //添加多个收件人（地址，昵称）

			// $EMAIL_SET['Subject']       = $title;
			// $EMAIL_SET['Content']       = $content;
			// $EMAIL_SET['AddAddress']    = $mailTo;
			// $EMAIL_SET['AddAttachment'] = array(array($fileurl, $title));

			// $mail = new \MkilMailer($EMAIL_SET); //实例化

			//发送
			if($result['success'] === false) {
				$tips = array('status'=>'0','msg'=>"Mailer Error: " . $result['error']);
				$this->ajaxReturn($tips);

			} else {
				$tips = array('status'=>'1','msg'=>"文件已发送到您的邮箱，请注意查收");
				$this->ajaxReturn($tips);
				//echo "邮件已发送！";
			}

    	}catch(\Exception $e){
            $DataNote               = new \Libm\DataNotes\DataNote();
            $DataNote->RequestData  = $args;
            $DataNote->ResponseData = $e->getMessage();
            $DataNote->save_dir     = ADMIN_ABS_FILE.'/ST_Export_Record/';
            $DataNote->file_name    = 'Export_Record.txt';
            $DataNote->save();
    	}
    }
//======================== 导入申通号 ===========================

    public function importView(){
    	$this->display();
    }

    /**
     * 导入CSV
     * @return [type] [description]
     */
    public function import_csv(){
   //  	$result = array('status'=>'0','msg'=>K(ADMIN_ABS_FILE));
			// $this->ajaxReturn($result);exit;

		ini_set('memory_limit','4088M');
		ini_set('max_execution_time', 0);

    	G('begin');

/*		功能使用一段时间后没问题的话，这段代码可以删除 20180109 jie
		$upload           = new \Think\Upload();// 实例化上传类
		$upload->maxSize  = 1048576*50 ;// 设置附件上传大小
		$upload->exts     = array('csv','xlsx');// 设置附件上传类型
		$upload->rootPath = K(ADMIN_ABS_FILE); //设置文件上传保存的根路径
		$upload->savePath = C('UPLOADS_STNO_CSV'); // 设置文件上传的保存路径（相对于根路径）
		$upload->autoSub  = true; //自动子目录保存文件
		$upload->subName  = array('date','Ymd');
		$upload->saveName = array('uniqid',mt_rand()); //设置上传文件名

		$info = $upload->upload();

		// MkilImportMarket这个类需要读取这个参数
		$info['file']['tmp_name'] = K(ADMIN_ABS_FILE . $info['file']['savepath'] . $info['file']['savename']);
		// dump($info);die;
		if(!$info) {// 上传错误提示错误信息
			// $this->error($upload->getError());
			$result = array('status'=>'0','msg'=>$upload->getError());
			$this->ajaxReturn($result);exit;
		}

		$filename = $info;
		// $filename = $_FILES['file']['tmp_name'];
		import('Vendor.MKILExcel.MkilImportMarket');//上线  使用的时候使用此加载
    	$importexcel = new \MkilImportMarket($filename);
		$arr = $importexcel->import();

		//如果返回的数组是有status字段信息的,则$arr为错误信息,不是处理好的数据数组
		if(isset($arr['status'])){
			$this->ajaxReturn($arr);exit;
		}*/

		// 20180109 jie
		$importexcel = new \Libm\MKILExcel\MkilImportMarket;
		$importexcel->inputFileName  = $_FILES['file']['tmp_name'];
		$arr = $importexcel->import();
		// dump($arr);die;
		//如果返回的是false
		if($arr === false){
			$this->ajaxReturn(array('status'=>'0','msg'=>$importexcel->getError()));exit;
		}
		// 20180109 jie end
    	
    	$client = $this->client;
    	$result = $client->_import_csv($arr);
    	// fclose($handle); //关闭指针
    	G('end');
    	$result['msg'] .= '，耗时：'.G('begin','end').'s';	//耗时时间显示
    	$this->ajaxReturn($result);
    	// echo G('begin','end').'s';
    	// dump($result);
    }

    /**
     * 读取为数组的形式 20160901 Jie 已不使用
     * @param  [type] $handle [description]
     * @return [type]         [description]
     */
	protected function input_csv($handle) {
		$out = array ();
		$n = 0;
		while ($data = fgetcsv($handle, 10000)) {
			$num = count($data);
			for ($i = 0; $i < $num; $i++) {
				$out[$n][$i] = $data[$i];
			}
			$n++;
		}
		return $out;
	}
//=============================== 发送到快递100 ================================

    public function toKD(){
		if(!IS_POST){
            $tips = array('status'=>'500','msg'=>"非法操作");
            $this->ajaxReturn($tips);
		}

		ini_set('memory_limit','4088M');
		ini_set('max_execution_time', 0);

		$value = session('admin');
		$username = $value['adname'];

		$where['status']      = array('eq','20');			//mk_stnolist 20 为已使用
		$where['kd100status'] = array('eq','0');		//mk_stnolist 0 即为仍未发送到快递100
		$where['l.STNO']      = array('exp','is not NULL');	//mk_tran_list 中的 申通号不为NULL
		$where['l.TranKd']    = array('eq','1');			//mk_tran_list 中的 中转方式为申通
		$where['l.IL_state']  = array('eq','200');		//mk_tran_list 中的 物流状态为200  已完成

        $client = $this->client;

		$tips = $client->KD100($where,$username);

		$this->ajaxReturn($tips);

    }


//=============================== End ==============================


    function pro(){
    	if($_SERVER['HTTP_HOST'] != 'admin.gwj.bd'){
    		die('非法操作');
    	}

		$where['status'] = array('eq','20');	//20 为已使用
		$where['cuid']   = array('exp','is not NULL');		//标识码 不为NULL
		$where['l.STNO'] = array('exp','is not NULL');		//mk_tran_list 中的 申通号不为NULL

		$value = session('admin');
		$username = $value['adname'];

		$cuid = $this->cuid();			//获取一个标识码

		$st = M('stnolist s')->field('s.id,s.cuid')->join('LEFT JOIN mk_tran_list l ON s.STNO = l.STNO')->where($where)->select();

		foreach($st as $v){
			$data['cuid'] = NULL;
			M('stnolist')->where(array('id'=>$v['id']))->save($data);
		}
		
		echo count($st).'条数据已还原';
		dump($st);

    }


//================================= 导出csv文件供本地下载  Jie 2015-08-28 以下不作操作使用 ======================================

	/**
	 * 文件导出到客户端 .csv
	 */
	
    //数据处理
    public function ExportData(){
	// ini_set('memory_limit','4088M');
	// ini_set('max_execution_time', '100');

		$where['status'] = array('eq','20');	//20 为已使用
		$where['cuid']   = array('eq','');		//标识码 为空的 即未导出
		$where['l.STNO'] = array('neq','');		//mk_tran_list 中的 申通号不为空

		$value = session('admin');
		$username = $value['adname'];

		$cuid = $this->cuid();			//获取一个标识码
		$data1['user_name'] = $username;
		$data1['time']      = date('Y-m-d H:i:s',time());
		$data1['cuid']      = $cuid;
		$addId = M('export_record')->add($data1);			//保存信息到表  并获取当次新增主键id的值

		// $st = M('stnolist s')->field('s.id,s.cuid')->join('LEFT JOIN mk_tran_list l ON s.STNO = l.STNO')->where($where)->select();

		foreach($st as $v){
			$data2['cuid'] = $cuid;
			M('stnolist')->where(array('id'=>$v['id']))->save($data2);	//更新标识码
		}

		$where['cuid'] = array('eq',$cuid);		//标识码 为当前已生成的唯一标识

    	// $list = M('stnolist s')->field('s.id,o.lid,l.STNO,l.MKNO,o.detail,sum(o.number) as num,l.sender,l.sendTel,l.sendAddr,l.receiver,l.reTel,l.province,l.city,l.reAddr,l.postcode,l.weight,l.premium')->join('LEFT JOIN mk_tran_list l ON s.STNO = l.STNO')->join('LEFT JOIN mk_tran_order o ON l.id = o.lid')->group('l.STNO,o.detail')->where($where)->select();

        $str = "序号,订单号,申通号,物品名称,数量,寄件方,联系人,电话,寄件方地址,收货人,电话,省份,城市,区,收货地址,邮编,重量(C('SHOW_WEIGHT_UNIT')),保价金额￥,申报金额￥\n";
        $str = iconv('utf-8','GBK',$str);

        $i = 1;	//序号
        foreach($list as $item){
			$MKNO     = $item['MKNO'];//iconv('UTF-8','GB2312',$item['MKNO']);		//订单号
			$STNO     = $item['STNO'];//iconv('UTF-8','GB2312',$item['STNO']);		//申通号
			$detail   = iconv('UTF-8','GBK',$item['detail']);					//物品名称
			$num      = $item['num'];//iconv('UTF-8','GB2312',$item['num']);		//数量
			$sender   = iconv('UTF-8','GBK',$item['sender']);					//寄件方
			$KD       = iconv('UTF-8','GBK','申通快递');							//寄件方
			$sendTel  = $item['sendTel'];//iconv('UTF-8','GB2312',$item['sendTel']);	//寄件方电话
			$sendAddr = iconv('UTF-8','GBK',$item['sendAddr']);					//寄件方地址
			$receiver = iconv('UTF-8','GBK',$item['receiver']);					//收件人
			$reTel    = $item['reTel'];//iconv('UTF-8','GB2312',$item['reTel']);	//收件人电话
			$province = iconv('UTF-8','GBK',$item['province']);					//省份
			$city     = iconv('UTF-8','GBK',$item['city']);						//城市

        	$arr = explode(" ",$item['reAddr']);	//从收件地址中获取 地区 信息

			$area     = iconv('UTF-8','GBK',$arr[2]);							//区
			$reAddr   = iconv('UTF-8','GBK',$item['reAddr']);					//收货地址
			$postcode = $item['postcode'];//iconv('UTF-8','GB2312',$item['postcode']);	//收货邮编
			$weight   = $item['weight'];//iconv('UTF-8','gbk',$item['kg']);			//重量
			$premium  = $item['premium'];//iconv('UTF-8','GB2312',$item['premium']);	//保价金额

        	$str .= $i.",".$MKNO.",".$STNO.",".$detail.",".$num.",".$KD.",".$sender.",".$sendTel.",".$sendAddr.",".$receiver.",".$reTel.",".$province.",".$city.",".$area.",".$reAddr.",".$postcode.",".$weight.",".$premium.",".'860'."\n";
        	$i++;
        	
        }

        $filename = date('YmdHis').'.csv';
        $this->export_csv($filename,$str,$addId,$i);
    }

    /**
     * 客户端输出
     * @param  [type] $filename [文件名]
     * @param  [type] $data     [数据]
     * @param  [type] $addId    [当次新增主键id的值]
     * @param  [type] $i        [数据数量]
     */
    public function export_csv($filename,$data,$addId,$i) {
        header("Content-type:text/csv");
        header("Content-Disposition:attachment;filename=".$filename);
        header('Cache-Control:must-revalidate,post-check=0,pre-check=0');
        header('Expires:0');
        header('Pragma:public');
        echo $data;
        //更新导出状态
        // $dat['filename'] = $filename;	//csv文件名
        // $dat['nums'] = $i-1;			//数据数量
        // $dat['export_status'] = 10;		//导出状态更改为 10 表示导出完成
        // M('export_record')->where(array('id'=>$addId))->save($dat);   //需要时打开
        exit;
    }

	/**
	 * 标识码生成方法
	 * @return [type] [description]
	 */
	function cuid(){
	    if (function_exists('com_create_guid')){
	        return com_create_guid();
	    }else{
	        mt_srand((double)microtime()*10000);//optional for php 4.2.0 and up.
	        $charid = strtoupper(md5(uniqid(rand(), true)));
	        $hyphen = chr(45);// "-"
	        $uuid = //chr(123)// "{"
	                substr($charid, 0, 8).$hyphen
	                .substr($charid, 8, 4).$hyphen
	                .substr($charid,12, 4).$hyphen
	                .substr($charid,16, 4).$hyphen
	                .substr($charid,20,12);
	                //.chr(125);// "}"
	        return $uuid;
	    }
	}

//====================== End ==========================

/*    public function files(){
		$hostdir= $_SERVER['DOCUMENT_ROOT'].'/File/app81/Admin/ST_Export/admin/';
		//获取本文件目录的文件夹地址
		$filesnames = scandir($hostdir);
		//获取也就是扫描文件夹内的文件及文件夹名存入数组 $filesnames
		// print_r ($_SERVER['DOCUMENT_ROOT']);die;
		// print_r ($hostdir);die;
		foreach ($filesnames as $name) {
			if(!is_dir($name)){
				$url="http://file.gwj.bd/app81/Admin/ST_Export/admin/".$name;
				$aurl= "<a href=\"".$url."\">".$name."</a>";
				echo $aurl . date ( "Y-m-d H:i:s", filemtime ( $name ) ) . "<br/>";
			}
		}
    }*/

    /**
     * [dir_size 申通导出历史文件下载]
     * @return [type] [description]
     */
	public function dir_size() {
		$dir = ADMIN_ABS_FILE.'/ST_Export/'.session('admin.adname').'/';
		// echo $dir;die;
		$url = ADMIN_FILE."/ST_Export/".session('admin.adname')."/";
	    $dh = opendir ( $dir ); // 打开目录，返回一个目录流
	    $return = array ();
	    $i = 0;
	    while ( $file = readdir ( $dh ) ) { // 循环读取目录下的文件
	        if ($file != '.' and $file != '..') {
	            $path = $dir . '/' . $file; // 设置目录，用于含有子目录的情况
	            if (is_dir ( $path )) {

	            } elseif (is_file ( $path )) {
	                $filetime [] = date ( "Y-m-d H:i:s", filemtime ( $path ) ); // 获取文件最近修改日期
	                $return [$i]['name'] = $file;
	                $return [$i]['url'] = $url . '/' . $file;
	                $return [$i]['time'] = date ( "Y-m-d H:i:s", filemtime ( $path ) );
	            }
	        }
	        $i++;
	    }
	    closedir ( $dh ); // 关闭目录流
	    array_multisort($filetime,SORT_DESC,SORT_STRING, $return);//按时间排序
	    // dump($return); // 返回文件
	    $this->assign('list', $return);
	    $this->display();
	}
}