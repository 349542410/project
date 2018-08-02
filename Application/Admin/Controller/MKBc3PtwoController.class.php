<?php
/**
 * 美快优选3(湛江EMS)
 * 功能包括： 湛江快递单号管理
 * 
 */
namespace Admin\Controller;
use Think\Controller;
header('Content-Type:text/html; charset=utf-8');
class MKBc3PtwoController extends AdminbaseController{

    function _initialize() {
        parent::_initialize();
        $client = new \HproseHttpClient(C('RAPIURL').'/MKBc3Ptwo');		//读取、查询操作
        $client -> setTimeout(1200000);//设置 HproseHttpClient 超时时间
        $this->client = $client;		//全局变量

    }

    //快递号管理 视图
	public function zj_no(){

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
		$page = new \Think\Page($count,$ePage); // 实例化分页类 传入总记录数和每页显示的记录数(20)

	$page->setConfig('prev', "上一页");//上一页
	$page->setConfig('next', '下一页');//下一页
	$page->setConfig('first', '首页');//第一页
	$page->setConfig('last', "末页");//最后一页
	$page->setConfig ( 'theme', '%FIRST% %UP_PAGE% %LINK_PAGE% %DOWN_PAGE% %END% %HEADER%' );
    		
		$show = $page->show(); // 分页显示输出
		$this->assign('page',$show);// 赋值分页输出

		$warn = $res['warn'];//$client->getWarn();		//余下的电子单数量
		$warn_num = C('WARNING')?C('WARNING'):1000;	//设置提示数量

		$this->assign('warn',$warn);
		$this->assign('warn_num',$warn_num);
		$this->assign('als',$info['als']);
		$this->assign('list',$list);
		$this->assign('id',$list['id']);

		$this->display();
	}

	/**
	 * 新增快递号 视图
	 */
	public function createView(){

		$this->assign('v','10');
		$this->display();
	}

	/**
	 * 新增快递号 方法
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
	 * 导出csv   生成csv文件到服务器端
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
			$filename = 'MkBc3-'.date('YmdHis')."(".$n.")";				//导出的文件名，无需后缀
			$fileurl  = ADMIN_ABS_FILE.'/MkBc3_Export/'.$username.'/'.$filename;	//20170220 jie
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
		    		$warning = "<br /><font style='color:red;padding-left:30px;'>注意：电子单仅剩余 <span style='font-size:18px;'>".number_format($warn)."</span> 张，请<span style='font-size:18px;font-weight:bold;'> 尽快 </span>及时补充，亦恳请见字后将新的电子单号发送至 dev@megao.cn 多谢！</font>";
		    	}else{
		    		$warning = "<br /><font style='padding-left:30px;'>电子单剩余 ".number_format($warn)." 张</font>";
		    	}

		    	$content = '<div style="font-size:15px;">'.$username.' 您好，<br>'.'<span style="padding-left:30px;">'.$tips['ctime'].' 您进行了资料的导出，请查看以下附件。</span>'.$warning.'</div>
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

//=============================== End ===========================

    /**
     * 20160114 Jie 改用此函数进行发送邮件
     * 邮件发送  请注意发送邮件的邮箱是否开启了SMTP功能，未开启则无法使用第三方发送功能
     * @param  [type] $title    [邮件标题]
     * @param  [type] $receiver [收件人]
     * @param  [type] $content  [发送的内容]
     * @param  [type] $fileurl  [上传文件的文件路径]
     * @return [type]           [description]
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

  //   	/*写法一*/
		// require_once 'MkilMailer.class.php'; //载入PHPMailer类

		// $EMAIL_SET = C('EMAIL_SET2');
		// $mailTo    = array(array($receiver,$username));		//收件人
		// array_push($mailTo, array($EMAIL_SET['EMAIL_COPE_TO'],$EMAIL_SET['EMAIL_COPE_NAME'])); //添加多个收件人（地址，昵称）

		// $EMAIL_SET['Subject']       = $title;
		// $EMAIL_SET['Content']       = $content;
		// $EMAIL_SET['AddAddress']    = $mailTo;
		// $EMAIL_SET['AddAttachment'] = array(array($fileurl,$title));

		// $mail = new \MkilMailer($EMAIL_SET); //实例化

		// //发送
		// if(!$mail->Send()) {
		// 	$tips = array('status'=>'0','msg'=>"Mailer Error: " . $mail->ErrorInfo);
		// 	$this->ajaxReturn($tips);

		// } else {
		// 	$tips = array('status'=>'1','msg'=>"文件已发送到您的邮箱，请注意查收");
		// 	$this->ajaxReturn($tips);
		// 	//echo "邮件已发送！";
		// }
    }
//======================== 导入快递号 ===========================

    public function import_no(){

    	$this->display();
    }

    /**
     * 导入CSV
     * @return [type] [description]
     */
    public function import_csv(){
        
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

    /**
     * 清关 --- 资料导出(导出成功后直接提成用户下载)
     * 导出文件，是用于手动形式报关，处理结果跟“发送”形式的报关一致
     * @return [type] [description]
     */
    public function toExport(){
    	$noid= I('id');
		$client   = $this->client;
		$res  = $client->_export_file($noid);

		$tcid      = $res['tcid'];
		$getlist   = $res['list'];
		$title     = $res['title'];

		$nos = array();//STNO数组集

		// 设置为文本无科学计数
		foreach($getlist as $key=>$item){

			$nos[$key] = $item['f19']; //STNO数组集
			// unset($getlist[$key]['f20']); //STNO数组集 获取得到STNO之后，清除此项

	        /* 去除详细地址中的省市区和空格 */
	        // $getlist[$key]['f6'] = str_replace($getlist[$key]['f10'],'',$getlist[$key]['f6']);
	        // $getlist[$key]['f6'] = str_replace($getlist[$key]['f9'],'',$getlist[$key]['f6']);
	        // $getlist[$key]['f6'] = str_replace($getlist[$key]['f8'],'',$getlist[$key]['f6']);
	        // $getlist[$key]['f6'] = trim(str_replace(' ','',$getlist[$key]['f6']));

			//20180502注释
			// foreach($item as $k=>$it){
			// 	// if(in_array($k, array('f2','f17','f23','f24','f28','f29'))){
			// 		$getlist[$key][$k] = "\t".$it;	// 符合规则的，则在字段前面添加"\t"
			// 	// }
			// }
		}

		$nos = array_filter($nos);//移除数组中的空值，并返回结果为数组
		$nos = array_unique($nos);//移除数组中的重复的值，并返回结果为数组
		
		$client->saveState($nos, $tcid,session('admin.adtname'));

		// $title = "原始单号,证件号码,总费用,订单时间,收货人,收货地址,收货人电话,省,市,县,邮编,备注,店铺货号,数量,单价,支付单号,订单号,订购人姓名,STNO,重量(lb),商品名称,海关商品报备编码,条形码,行邮税则号,货品名称,货品重量,时间,自定义单号1,自定义单号2,品牌,货币类型,计量单位,规格型号,原厂地国别,备注";

		$fpd = 'MkBc3'; // 20160912 jie 文件名前缀
		
		$filename = $fpd."-".date('YmdHis');				//导出的文件名
		// $fileurl  = K(C('CSVURL').'/'.$fpd.'/'.$filename);	//20170220 jie

		$exportexcel  = new \Libm\MKILExcel\ExcelOperation;//上线  使用的时候使用此加载

		$headArr = explode(',',$title);
		$exportexcel ->push($filename,$headArr,$getlist);
    }

}