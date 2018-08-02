<?php
/**
 * mk_logs数据表 短信发送
 * 20160914 jie 身份证号码  原sfid字段改为使用 idno
 */
namespace Api\Controller;
use Think\Controller;
class SendSmsController extends Controller{
	public function _initialize(){
		//echo 'bbbbb';
		$allowhost = C('MESSAGE.ALLOWHOST');
		$ser_name = $_SERVER['SERVER_NAME'];
		// dump($ser_name);
		if($allowhost != $ser_name){
			echo 'network error';die();
		}
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
		G('begin');
		// $CID = I('CID','1');
		$num = C('MESSAGE.LIMIT')?C('MESSAGE.LIMIT'):5;		//操作数量限制

		$oneDay = date('Y-m-d H:i:s',time()-86400);	//当前时间的一天前

		//检测mk_mkil_sms 中是否有时间操作记录
		//$where2['sms_code'] = array('eq','100');	//等于100 即发送成功的,//man150817不管发送是否成功都略过
		$where2['sms_time'] = array('gt',$oneDay);	//距离当前时间1天之内

/*		//按照时间从大到小且发送状态等于100即成功的 取第一个
		$firstCheck = M('MkilSms')->where($where2)->order('sms_time desc')->find();*/

		//按照lid从大到小且发送状态等于100即成功的 取第一个
		$firstCheck = M('MkilSms')->where($where2)->order('lid desc')->find();
		//var_dump($firstCheck);
		//如果mk_mkil_sms 中搜索得到符合的数据，则执行
		if($firstCheck){
			//160521Man改为直接经l.id为准
			//$map['l.optime'] = array('egt',$firstCheck['sms_time']);	 //大于等于这个记录的时间
			//$map['l.id']     = array('neq',$firstCheck['lid']);		//要不等于mk_mkil_sms中该最大时间的数据的lid
			$map['l.id']     = array('gt',$firstCheck['lid']);

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
		//$map['l.state']  = array(array('eq',12),array('eq',200),'or');	//取state等于12或者200的
		$map['l.state']  = array('eq',12);	//Man170518 取state等于12
		// $map['t.TranKd'] = array('eq','1');		//中转方式 1申通
		// $map['s.id']  = array('exp','is NULL');
		//$map['t.CID']    = array('eq',$CID);	//所属公司，导入美快系统的公司资料，日后可能会关联公司名在短信中,Man150817
		$logs = M('Logs l')->field('l.MKNO,l.mStr1,l.state,l.transit,l.tranNum,l.id,t.reTel,t.receiver,l.optime,t.sfid')
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
			G('end');
			echo 'none, 耗时：'.G('begin','end').'s';exit;
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
			$sfid     = trim($item['idno']);        //身份证号码  20160106 Jie     20160914 jie 改为 idno

			$cback 	  = curl($item['MKNO']);		//获取短信用的url

	        //sprintf() 函数把格式化的字符串写入一个变量中
	        if($state == '12'){
	        	//20160106 Jie 
	        	if(!empty($sfid)){	//如果不为空

	        		//验证是否等于8 或者 格式不正确时
		        	if($sfid == '8' || certificate($sfid) === false){
		        		unset($cback);	//重置
		        		$cback = curl($item['MKNO'],'za');		//重新获取短信用的url
		        		$cont = C('MESSAGE.WMD2');        //获取发送内容格式
		        	}else{
		        		$cont = C('MESSAGE.WMD');        //获取发送内容格式
		        	}
		        	//End 20160106

	        	}else{	//sfid为空的时候不用理会
	        		$cont = C('MESSAGE.WMD');        //获取发送内容格式
	        	}

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

				//$res 	= '';
				$res = $this->postSMS($url,$data);			//POST方式提交
				// $res = "sms&stat=100&message=发送成功"; 	//测试使用，正式使用时要注销

		        //以下为检查是否发送成功！
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
			/*160521Man test
			echo '<pre>';
			var_dump($data);
			echo '</pre>';
			continue;
			*/
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
		G('end');
		echo 'Done, 耗时：'.G('begin','end').'s, Nums: '.$i."(总：".count($logs).")";
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
	
}