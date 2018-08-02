<?php
/**
 * 3大发送功能(短信、KD100、ERP)的集合体  测试时候用
 */
namespace Api\Controller;
use Think\Controller;
class LogsController extends Controller{

	public function index(){

		$this->display();
	}

//==================== 发送到ERP222  已暂停使用  =========================

	/**
	 * 发送mk_logs数据表的数据到erp
	 *
	 * Jie 2015-09-14 旧版发送功能，已暂停使用
	 *
	 * 版本日期 2015-08-03
	 */
	public function sendERP222(){
		$CID = I('get.CID','1');
		ini_set('memory_limit','4088M');
		ini_set('max_execution_time', 0);

		$num = C('LOGS_SET.LIMIT')?C('LOGS_SET.LIMIT'):100;		//操作数量限制
		
		//检测mk_log_notes 中是否有时间操作记录
		$notes = M('LogsNotes')->where(array('state'=>'200'))->order('id desc')->find();	//取最大的时间
		$where['t.CID'] = array('eq',$CID);
		//如果有记录
		if($notes){
			$where['l.id'] = array('neq',$notes['lid']);		 //Man 保留，否则每次都会有一个记录，不
			$where['l.optime'] = array('egt',$notes['optime']);	 //大于等于这个记录的时间,Man需使用大于和等于，因同一时候可能会有多条记录Limit可能会漏掉一些
			//依照 $where 条件再加上按照时间从小到大的顺序排列查找
			// 特殊情况：同一个时间中有多个数据符合但是由于limit限制，所以取出来的时候是按照“随机”原则取的
			$logs = M('Logs l')->field('l.*')->join('LEFT JOIN mk_tran_list t ON t.MKNO=l.MKNO')->where($where)->order('l.optime asc')->limit($num)->select();

		}else{	//如果没有，则直接从mk_logs表中按照时间从小到大的顺序找出*条记录进行操作
			
			// 获取limit(*)条数据中的最大时间值  备用
			// $Model = M();
			// $maxtime = $Model->query("select max(optime) from ( SELECT optime FROM mk_logs ORDER BY optime asc limit $num ) as a");
			// Man 150813 Left join mk_tran_list原因是 从该表中 选取 CID 作为查询条件
			$logs = M('Logs l')->field('l.*')->join('LEFT JOIN mk_tran_list t ON t.MKNO=l.MKNO')->where($where)->order('l.optime asc')->limit($num)->select();
		}

		//dump($logs);
		$count = count($logs);
		//如果已经没有数据符合
		if($count < 1){
			echo 'none'.date('Y-m-d H:i:s'); exit;
		}
		/*
		$arr = array();
		foreach($logs as $k=>$item){
			$arr[$k]['MKNO'] = $item['MKNO'];
			$arr[$k]['state'] = $item['state'];
			$arr[$k]['ftime'] = $item['optime'];
			$cona = array(
					'c8' => '生成面单',
					'c12' => '已揽收',
					'c20' => '已中转',
					'c60' => '打印快递单',
					'c100' => '再中转',
					'c200' => '已发快递',
				);
			$arr[$k]['context'] = isset($cona['c'.$item['state']])?$cona['c'.$item['state']]:'NULL';
		}*/
		$arr = array();
		foreach($logs as $k=>$item){
			$arr1 			= array();
			$arr1['MKNO'] 	= $item['MKNO'];
			$arr1['state'] 	= $item['state'];
			$arr1['ftime'] 	= $item['optime'];
			$cona = array(
					'c8' 	=> '生成面单',
					'c12' 	=> '已揽收',
					'c20' 	=> '已中转',
					'c60' 	=> '打印快递单',
					'c100' 	=> '再中转',
					'c200' 	=> '已发快递',
			);
			$arr1['pno']	 = ($item['state']==20) ? $item['mStr1'] : ''; //20150813 Man 返回中转批号
			$arr1['context'] = isset($cona['c'.$item['state']])?$cona['c'.$item['state']]:'NULL';
			array_push($arr, $arr1);
		}
		// dump($arr);die;
		$url 	= C('LOGS_SET.URL');  //post url
		$schema = "json";
		$param 	= json_encode($arr);  
		// echo $param;
		$post_data = "schema=".$schema."&pf=mkil&param=".$param;	//组合

		/* Jie 2015-09-14 
		// //通过curl函数发送
		// $ch = curl_init();
		// 	curl_setopt($ch, CURLOPT_POST, 1);
		// 	curl_setopt($ch, CURLOPT_HEADER, 0);
		// 	curl_setopt($ch, CURLOPT_URL,$url);
		// 	curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);

		// 	//当CURLOPT_RETURNTRANSFER设置为1时，如果成功只将结果返回，不自动输出返回的内容。如果失败返回FALSE；
		// 	//若不使用这个选项：如果成功只返回TRUE，自动输出返回的内容。如果失败返回FALSE
		// 	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

		// 	$result = curl_exec($ch);
		 	curl_close($ch); */
		
		// Jie 2015-09-14
		$result = posturl($url,$post_data);

		$res = trim(json_decode($result,true));		//返回的结果

		//dump($res);
		$data['lid'] 	= $logs[$count-1]['id'];
		$data['optime'] = $logs[$count-1]['optime'];
		$data['state'] 	= $res;
		M('LogsNotes')->add($data);		//保存记录
		echo 'Done'.date('Y-m-d H:i:s').' NUM:'.count($logs);
	}


/*--------------------------------
功能:		HTTP接口 发送短信类
说明:		http://api.sms.cn/mt/?uid=用户账号&pwd=MD5位32密码&mobile=号码&mobileids=号码编号&content=内容
状态:
	100 发送成功
	101 验证失败
	102 短信不足
	103 操作失败
	104 非法字符
	105 内容过多
	106 号码过多
	107 频率过快
	108 号码内容空
	109 账号冻结
	110 禁止频繁单条发送
	112 号码不正确
	120 系统升级
--------------------------------*/

	/**
	 * [sendSMS description]
	 * @param  [type] $mkno  [美快单号]
	 * @param  [type] $tname [仓库名字]
	 * @param  [type] $type  [发送类型]
	 * @param  [type] $cont  [发送内容]	
	 * @param  string $EXPNM [中转物流公司]
	 * @param  string $tranNum [中转物流公司相应的单号]
	 * @param  string $ctime [定时发送设置的时间]
	 * @param  string $mid   [未知]
	 * @return [type]        [description]
	 */
	public function sendSMS(){
		// $CID = I('CID','1');
		$num = C('MESSAGE.LIMIT')?C('MESSAGE.LIMIT'):5;		//操作数量限制

		$oneDay = date('Y-m-d H:i:s',time()-86400);	//当前时间的一天前

		//检测mk_mkil_sms 中是否有时间操作记录
		//$where2['sms_code'] = array('eq','100');	//等于100 即发送成功的,//man150817不管发送是否成功都略过
		$where2['sms_time'] = array('gt',$oneDay);	//距离当前时间1天之内

		//按照时间从大到小且发送状态等于100即成功的 取第一个
		$firstCheck = M('MkilSms')->where($where2)->order('sms_time desc')->find();

		//如果mk_mkil_sms 中搜索得到符合的数据，则执行
		if($firstCheck){

			$map['l.optime'] = array('egt',$firstCheck['sms_time']);	 //大于等于这个记录的时间
			$map['l.id']     = array('neq',$firstCheck['lid']);		//要不等于mk_mkil_sms中该最大时间的数据的lid

		}else{	//如果mk_mkil_sms 中没有符合发送状态为100的数据，则执行

			/* Jie 20150817 由于应用的设计思路改变，此验证已与$firstCheck 重复操作，故注释屏蔽
			// $where3['sms_code'] = array('neq','100');	//不等于100 即发送不成功的
			$where3['sms_time'] = array('gt',$oneDay);		//距离当前时间1天之内
			$secondCheck = M('MkilSms')->where($where3)->order('sms_time desc')->find(); //asc按照时间从小到大 取第一个 //Man 150817 改为DESC 取最新一个时间

			if($secondCheck){
				$map['l.optime'] = array('egt',$secondCheck['sms_time']);	 //大于等于这个记录的时间
				$map['l.id']     = array('neq',$secondCheck['lid']);		//要不等于mk_mkil_sms中该最大时间的数据的lid
			
			}else{	//两次的检查处理都没得出数据时，则执行以下
			*/	
				// Man 20150817
				$map['l.optime'] = array('gt',$oneDay);	//距离当前时间1天之内
				
			// }
					
		}
		
		//统一读取要发送的数据
		$map['l.state']  = array(array('eq','12'),array('eq','200'),'or');	//取state等于12或者200的
		$map['t.TranKd'] = array('eq','1');		//中转方式 1申通
		// $map['s.id']  = array('exp','is NULL');
		//$map['t.CID']    = array('eq',$CID);	//所属公司，导入美快系统的公司资料，日后可能会关联公司名在短信中,Man150817
		$logs = M('Logs l')->field('l.MKNO,l.mStr1,l.state,l.transit,l.tranNum,l.id,t.reTel,t.receiver,l.optime')
				// ->join('LEFT JOIN mk_mkil_sms s ON s.sms_mkno=l.MKNO') // Man不使用此表
				->join('LEFT JOIN mk_tran_list t ON t.MKNO=l.MKNO')
				->where($map)->order('l.optime asc')->limit($num)->select();	//按mk_logs.optime时间从小到大排序  取第一条数据

		// $logs = array(
		// 		array(
		// 			'MKNO'=>'MK881000666US',
		// 			'mStr1'=>'美快国际物流北加洲仓',
		// 			'state'=>'12',
		// 			'transit'=>'国泰航空',
		// 			'tranNum'=>'555888888',
		// 			'id'=>'998',
		// 			'reTel'=>'13535013712',
		// 			'receiver'=>'何生',
		// 			'optime'=>'2015-08-17 01:01:01'
		// 			),
		// 		array(
		// 			'MKNO'=>'MK881000666US',
		// 			'mStr1'=>'美快国际物流香港中转中心',
		// 			'state'=>'200',
		// 			'transit'=>'申通',
		// 			'tranNum'=>'555866668',
		// 			'id'=>'999',
		// 			'reTel'=>'86-15360088803',
		// 			'receiver'=>'何生',
		// 			'optime'=>'2015-08-17 05:01:01',
		// 			),				
		// 	);
		//如果没有数据，则终止并退出
		if(count($logs) < 1){
			echo 'none'.date('Y-m-d H:i:s');exit;
		}

		$i = 0;
		foreach($logs as $item){
			$mkno     = $item['MKNO'];			//美快单号
			$tname    = $item['mStr1'];		//仓库名
			$state    = $item['state'];			//发送类型
			$EXPNM    = $item['transit'];		//中转物流公司
			$tranNum  = $item['tranNum'];	//中转物流公司的单号
			$mobile   = trim($item['reTel']);           //收件人手机号码
			$receiver = trim($item['receiver']);        //收件人姓名	

			$cback 	  = curl($item['MKNO']);		//获取短信用的url

	        //sprintf() 函数把格式化的字符串写入一个变量中
	        if($state == '12'){
	        	$cont = C('MESSAGE.WMD');        //获取发送内容格式
	        	$type = '已揽件';
	            $cont = sprintf($cont,$tname,$mkno,$receiver,$cback['url']);	//拼接发送内容
	        }else if($state == '200'){
	        	$cont = C('MESSAGE.CMD');        //获取发送内容格式
	        	$type = '已发快递';
	            $cont = sprintf($cont,$tname,$EXPNM,$tranNum,$cback['url'],$receiver);	//拼接发送内容
	        }

	        $isMob = "/^1[3|4|5|7|8]\d{9}$/";
			if(!preg_match($isMob,$mobile)){	//验证手机号码格式

				$code = '112';
				$msg  = '号码不正确(MK)';

			}else{
		        //消息编号，该参数用于发送短信收取状态报告用，格式为消息编号+逗号；与接收号码一一对应，可以重复出现多次。
		        //这里只用一个编号即可，手机号加上微秒，应该是唯一的了吧。
		        $mobileids = intval($mobile).microtime();
		        //要发送的内容
		        $content = urlencode($cont);

				$url = C('MESSAGE.HTTP');  //短信接口
				$uid = C('MESSAGE.UID');   //用户账号
				$pwd = C('MESSAGE.PWD');   //密码

				$data = array
					(
					'uid'		=> $uid,					//用户账号
					'pwd'		=> md5($pwd.$uid),			//MD5位32密码,密码和用户名拼接字符
					'mobile'	=> $mobile,				//号码
					'content'	=> $content,			//内容
					'mobileids'	=> $mobileids,
					'time'		=> '',					//定时发送
					'encode'	=> 'utf8',				//编码格式，看自己的项目需求了 这里是utf8的	
					);

				$res = $this->postSMS($url,$data);			//POST方式提交
				// $res = "sms&stat=100&message=发送成功"; 	//测试使用，正式使用时要注销

		        //以下为测试是否发送成功！
		        $code = substr($res,9,3);
		        //匹配对应的识别码
				$arr = array(
						'c100' => '发送成功',
						'c101' => '验证失败',
						'c102' => '短信不足',
						'c103' => '操作失败',
						'c104' => '非法字符',
						'c105' => '内容过多',
						'c106' => '号码过多',
						'c107' => '频率过快',
						'c108' => '号码内容空',
						'c109' => '账号冻结',
						'c110' => '禁止频繁单条发送',
						'c112' => '号码不正确',
						'c120' => '系统升级',
					);

				$msg = isset($arr['c'.$code])?$arr['c'.$code]:'未知错误，请联系客服';
		        
			}

			//数据保存
	        $dat['lid']           = $item['id'];     //mk_logs.id
	        $dat['sms_type']      = $type;     //类型
	        $dat['sms_warehouse'] = $tname;    //仓库
	        $dat['sms_phone']     = $mobile;   //手机号码
	        $dat['sms_time']      = $item['optime']; //发送时间
	        $dat['sms_mkno']      = $mkno; //美快单号
	        $dat['sms_content']   = $cont; //发送内容
	        $dat['sms_code']      = $code; //短信标识码
	        $dat['sms_msg']       = $msg;  //短信反馈信息

        	M('MkilSms')->add($dat);	//发送记录保存到数据表

        	$i++;

		}
		echo 'Done'.date('Y-m-d H:i:s').' NUM:'.$i."(总：".count($logs).")";
	}

	/**
	 * [postSMS description]
	 * @param  [type] $url  [短信接口平台]
	 * @param  string $data [数据]
	 * @return [type]       [description]
	 */
	public function postSMS($url,$data=''){
		// return $res = "sms&stat=100&message=发送成功";

		$port="";
		$post="";
		$row = parse_url($url);
		$host = $row['host'];
		$port = isset($row['port'])?$row['port']:80;
		$file = $row['path'];
		while (list($k,$v) = each($data))
		{
			$post .= rawurlencode($k)."=".rawurlencode($v)."&";	//转URL标准码
		}
		$post = substr( $post , 0 , -1 );
		$len = strlen($post);
		$fp = @fsockopen( $host ,$port, $errno, $errstr, 10);

		if (!$fp) {
			return "$errstr ($errno)\n";
		} else {
			$receive = '';
			$out = "POST $file HTTP/1.1\r\n";
			$out .= "Host: $host\r\n";
			$out .= "Content-type: application/x-www-form-urlencoded\r\n";
			$out .= "Connection: Close\r\n";
			$out .= "Content-Length: $len\r\n\r\n";
			$out .= $post;
			fwrite($fp, $out);
			while (!feof($fp)) {
				$receive .= fgets($fp, 128);
			}
			fclose($fp);
			$receive = explode("\r\n\r\n",$receive);
			unset($receive[0]);
			return implode("",$receive);
		}
	}


//=================================== 发送到快递100 ===================================

    public function toKD100(){
    	// $CID = I('CID','1');
    	$getdtcount = C('KD100.KD_NUM_LITMIT')?C('KD100.KD_NUM_LITMIT'):150; //每次读取的记录数量

		ini_set('memory_limit','4088M');
		ini_set('max_execution_time', 0);
 	
 		$maxtime = date('Y-m-d H:i:s',time()-86400);	//计算一天前
 		
		$where['s.status']      = array('eq','20');		//20 为已使用
		$where['s.kd100status'] = array('eq','0');		//0 即为仍未发送到快递100
		$where['l.tranNum']     = array('exp','is not NULL');	//mk_logs 中的 申通号不为NULL
		$where['l.state']       = array('eq','200');		//mk_logs 中的 物流状态为200  已完成
		$where['t.TranKd']      = array('eq','1');			//mk_tran_list 中的 中转方式为申通
		//$where['l.transit']     = array('eq','申通');		//mk_logs 中的 中转方式为申通
		$where['l.optime'] = array('lt',$maxtime);		//mk_logs 中的操作时间；能够执行发送的是当前时间的前一天的所有数据
		//查找状态为已完成但尚未发送的申通号
		
		$st = M('Stnolist s')->field('s.id,s.STNO')
			->join('LEFT JOIN mk_logs l ON l.MKNO = s.MKNO')
			->join('LEFT JOIN mk_tran_list t ON t.MKNO=s.MKNO')
			->where($where)->order('l.optime')->limit($getdtcount)->select();

		/*
		$where['s.status']      = array('eq','20');			//20 为已使用
		$where['s.kd100status'] = array('eq','0');		//0 即为仍未发送到快递100
		$where['l.STNO']      = array('exp','is not NULL');	//mk_tran_list 中的 申通号不为NULL
		$where['l.TranKd']    = array('eq','1');			//mk_tran_list 中的 中转方式为申通
		$where['l.IL_state']  = array('eq','200');		//mk_tran_list 中的 物流状态为200  已完成

		//查找状态为已完成但尚未发送的申通号
		$st = M('Stnolist s')->field('s.id,s.STNO')
				->join('LEFT JOIN mk_tran_list l ON s.STNO = l.STNO')
				->where($where)->order('l.optime')->limit($getdtcount)->select();
		*/
		// dump($st);
		// die;
		$sti = count($st);
		if($sti < 1){
			// return $tips = array('status'=>'404','msg'=>"没有数据需要发送");
			echo 'none'.date('Y-m-d H:i:s');exit;
		}

		//获取config中的快递100配置信息
		$KD100 		= C('KD100');
		$kd100key 	= $KD100['KD100KEY'];
		$cbackurl 	= $KD100['CALLBACKURL'];
		$url      	= $KD100['POSTURL'];		

		$i = 0;
		$schema = "json";
		$cburl 	= array(
			'callbackurl'=>$cbackurl
		);
		$pars 	= array(
			"company"	=> "shentong",
			"number"	=> "",
			"from"		=> "",
			"to"		=> "",
			"key"		=> $kd100key,
			"parameters"=> $cburl,
		);
		foreach($st as $item){
			$pars['number'] 	= $item['STNO'];
			$param 	= json_encode($pars);
			//return $param;
			$post_data = "schema=".$schema."&param=".$param;	//组合

			//通过curl函数发送
			$ch = curl_init();
				curl_setopt($ch, CURLOPT_POST, 1);
				curl_setopt($ch, CURLOPT_HEADER, 0);
				curl_setopt($ch, CURLOPT_URL,$url);
				curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);

				//当CURLOPT_RETURNTRANSFER设置为1时，如果成功只将结果返回，不自动输出返回的内容。如果失败返回FALSE；
				//若不使用这个选项：如果成功只返回TRUE，自动输出返回的内容。如果失败返回FALSE
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

				$result = curl_exec($ch);
				curl_close($ch);
				$res = json_decode($result,true);

			//200为成功，重复发送的则会返回501，501也表示已经成功，保存的时候改为200后保存
			if($res['returnCode'] == '200' || $res['returnCode'] == '501'){

				//mk_stnolist
				$data_KD['kd100status'] 	= 200;	// 状态标注为200表示已发送
				M('Stnolist')->where(array('id'=>$item['id']))->save($data_KD);	// 更新快递100状态

				//mk_send_record
				$data_Record['username'] 	= 'admin';//$username;
				$data_Record['STNO'] 		= $item['STNO'];
				M('SendRecord')->add($data_Record);	// 保存操作记录
				$i++;
			}
		}
		echo 'Done'.date('Y-m-d H:i:s').' NUM:'.$i."(总：".$sti.")";

    }

//////////////////////////////////////////////////////
    //测试用
    public function cre_url(){
		$logs = array(
					'MKNO'=>'MK881000666US',
					'mStr1'=>'美快国际物流北加洲仓',
					'state'=>'12',
					'transit'=>'国泰航空',
					'tranNum'=>'555888888',
					'id'=>'998',
					'reTel'=>'13535013712',
					'receiver'=>'利德斯',
					'optime'=>'2015-08-17 01:01:01'
					);

			$mkno     = $logs['MKNO'];			//美快单号
			$tname    = $logs['mStr1'];		//仓库名
			$state    = $logs['state'];			//发送类型
			$EXPNM    = $logs['transit'];		//中转物流公司
			$tranNum  = $logs['tranNum'];	//中转物流公司的单号
			$mobile   = trim($logs['reTel']);           //收件人手机号码
			$receiver = trim($logs['receiver']);        //收件人姓名	

			$cback 	  = curl($logs['MKNO']);		//获取短信用的url

		$cont = C('MESSAGE.CMD');
		$cont = sprintf($cont,$tname,$EXPNM,$tranNum,$cback['url'],$receiver);	//拼接发送内容
		echo $cont;
    }

//==================== 发送到ERP =========================

	/**
	 * 发送mk_il_logs数据表的数据到erp
	 *
	 * 关联：mk_il_log_notes
	 *
	 * Jie 2015-09-14 最新版本的发送功能，目前使用中
	 *
	 * 版本日期 2015-09-14
	 */
	public function sendERP(){
		$CID = I('get.CID','1');
		ini_set('memory_limit','4088M');
		ini_set('max_execution_time', 0);

		$num = C('LOGS_SET.LIMIT')?C('LOGS_SET.LIMIT'):100;		//操作数量限制
		
		//检测mk_il_log_notes 中是否有操作记录
		$maxlid = M('IlLogsNotes')->where(array('state'=>'200','il_CID'=>$CID))->max('lid'); //order('id desc')->getField('lid');//find();	//取最大的id
		// dump($maxlid);
		if(!$maxlid) $maxlid = 0;
		$where['i.CID'] = array('eq',$CID);

		//如果有记录
		if($maxlid > 0){
			$where['i.id'] = array('gt',$maxlid);
		}

		$logs = M('IlLogs i')->field('i.*,l.mStr1,l.state as logs_state')->join('LEFT JOIN mk_logs l ON i.MKNO=l.MKNO AND l.state=i.status')->where($where)->order('id asc')->limit($num)->select();

		// dump($logs);
		$count = count($logs);
		//如果已经没有数据符合
		if($count < 1){
			echo 'None'.date('Y-m-d H:i:s'); exit;
		}

		$arr = array();
		foreach($logs as $k=>$item){
			$arr1            = array();
			$arr1['MKNO']    = $item['MKNO'];
			$arr1['state']   = $item['state'];
			$arr1['ftime']   = $item['create_time'];
			$arr1['pno']     = ($item['logs_state']==20) ? $item['mStr1'] : ''; //20150813 Man 返回中转批号
			$arr1['context'] = $item['content'];

			array_push($arr, $arr1);
		}
		// dump($arr);
		// die;
		$url 	= C('LOGS_SET.URL');  //post url
		$schema = "json";
		$param 	= json_encode($arr);  

		$post_data = "schema=".$schema."&pf=mkil&param=".$param;	//组合

		// //通过curl函数发送
		// $ch = curl_init();
		// 	curl_setopt($ch, CURLOPT_POST, 1);
		// 	curl_setopt($ch, CURLOPT_HEADER, 0);
		// 	curl_setopt($ch, CURLOPT_URL,$url);
		// 	curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);

		// 	//当CURLOPT_RETURNTRANSFER设置为1时，如果成功只将结果返回，不自动输出返回的内容。如果失败返回FALSE；
		// 	//若不使用这个选项：如果成功只返回TRUE，自动输出返回的内容。如果失败返回FALSE
		// 	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

		// 	$result = curl_exec($ch);
		// 	curl_close($ch);
		
		$result = posturl($url,$post_data);

		$res = trim(json_decode($result,true));		//返回的结果

		$data['lid'] 	= $logs[$count-1]['id'];
		$data['il_CID'] = $logs[$count-1]['CID'];
		$data['state'] 	= $res;
		M('IlLogsNotes')->add($data);		//保存记录
		echo 'Done'.date('Y-m-d H:i:s').' NUM:'.count($logs);
	}

}