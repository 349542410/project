<?php
/**
 * 发送mk_logs数据表的数据到erp   副本
 */
namespace Api\Controller;
use Think\Controller;
class LogsController extends Controller{

	public function index(){

		$this->display();
	}

	public function send(){
		ini_set('memory_limit','4088M');
		ini_set('max_execution_time', 0);

		$num = C('LOGS_SET.LIMIT');		//操作数量限制
		//检测mk_log_notes 中是否有时间操作记录
		$notes = M('LogsNotes')->where(array('state'=>'200'))->order('optime desc')->find();	//取最大的时间
		$where['CID'] = array('eq','2');
		//如果有记录
		if($notes){
			$where['id'] = array('neq',$notes['lid']);
			$where['optime'] = array('egt',$notes['optime']);
			//依照这个时间来查找*条数据(按照时间从小到大的顺序排列查找)
			// 特殊情况：同一个时间中有多个数据符合但是由于limit限制，所以取出来的时候是按照“随机”原则取的
			$logs = M('Logs')->where($where)->order('optime asc')->limit($num)->select();

		}else{	//如果没有，则直接从mk_logs表中按照时间从小到大的顺序找出*条记录进行操作
			
			//获取limit(*)条数据中的最大时间值  备用
			// $Model = M();
			// $maxtime = $Model->query("select max(optime) from ( SELECT optime FROM mk_logs ORDER BY optime asc limit $num ) as a");

			$logs = M('Logs')->where($where)->order('optime asc')->limit($num)->select();
		}

		$count = count($logs);
		//如果已经没有数据符合
		if($count < 1){
			echo 'none';
			exit;
		}
		
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

			// switch($item['state']){
			// 	case '8':
			// 	$arr[$k]['context'] = '生成面单';
			// 	break;

			// 	case '12':
			// 	$arr[$k]['context'] = '已揽收';
			// 	$cont = C('MESSAGE.WMD');        //发送内容
			// 	$this->sendSMS($item['MKNO'],$item['mStr1'],$item['state'],$cont);	//调用发送方法

			// 	break;

			// 	case '20':
			// 	$arr[$k]['context'] = '已中转';
			// 	break;

			// 	case '60':
			// 	$arr[$k]['context'] = '打印快递单';
			// 	break;

			// 	case '100':
			// 	$arr[$k]['context'] = '再中转';
			// 	break;

			// 	case '200':
			// 	$arr[$k]['context'] = '已发快递';
			// 	$cont = C('MESSAGE.CMD');        //发送内容
			// 	$this->sendSMS($item['MKNO'],$item['mStr1'],$item['state'],$cont,$item['transit'],$item['tranNum']);	//调用发送方法
			// 	break;
			// }
		}
		dump($arr);
		$url = C('LOGS_SET.URL');  //post url
		$schema = "json";
		$param 	= json_encode($arr);  
		// echo $param;
		$post_data = "schema=".$schema."&pf=mkil&param=".$param;	//组合

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
			$res = trim(json_decode($result,true));		//返回的结果

		// dump($res);
		$data['lid'] = $logs[$count-1]['id'];
		$data['optime'] = $logs[$count-1]['optime'];
		$data['state'] = $res;
		M('LogsNotes')->add($data);		//保存记录

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
	public function sendSMS($mkno,$tname,$type,$cont,$EXPNM='',$tranNum='',$ctime='',$mid=''){
		$where['CID'] = array('eq','2');
		$logs = M('Logs')->where($where)->order('optime asc')->limit(1)->select();

		$url = C('MESSAGE.HTTP');  //短信接口
		$uid = C('MESSAGE.UID');   //用户账号
		$pwd = C('MESSAGE.PWD');   //密码
		
		$reInfo = M('TranList')->field('reTel,receiver,STNO')->where(array('MKNO'=>$mkno))->find();	//查找单号的收件人信息

        $mobile  = trim($reInfo['reTel']);           //收件人手机号码
        $receiver = trim($reInfo['receiver']);        //收件人姓名

        //sprintf() 函数把格式化的字符串写入一个变量中
        if($type == '12'){
        	$type == '已揽件';
            $cont = sprintf($cont,$tname,$mkno,$receiver);	//拼接发送内容
        }else{
        	$type == '已发快递';
            $cont = sprintf($cont,$tname,$EXPNM,$tranNum);	//拼接发送内容
        }

        $mobile = str_replace("86-" ,'' ,$mobile);
        //匹配手机号码格式
        $g = "/^1[34578]\d{9}$/"; 
        if(preg_match($g, $mobile) != true){
            return false;
        }

        //消息编号，该参数用于发送短信收取状态报告用，格式为消息编号+逗号；与接收号码一一对应，可以重复出现多次。
        //这里只用一个编号即可，手机号加上微秒，应该是唯一的了吧。
        $mobileids = intval($mobile).microtime();
        //要发送的内容
        $content = urlencode($cont);


		$data = array
			(
			'uid'=>$uid,					//用户账号
			'pwd'=>md5($pwd.$uid),			//MD5位32密码,密码和用户名拼接字符
			'mobile'=>$mobile,				//号码
			'content'=>$content,			//内容
			'mobileids'=>$mobileids,
			'time'=>$ctime,					//定时发送
			'encode'=>'utf8',				//编码格式，看自己的项目需求了 这里是utf8的	
			);
		// $res = $this->postSMS($url,$data);			//POST方式提交
			$res = "sms&stat=100&message=发送成功";
        //以下为测试是否发送成功！
        $code = substr($res,9,11);

        switch($code){
            //100为发送成功
            case 100:
            //如果成功就，这里只是测试样式，可根据自己的需求进行调节
            $msg = '短信发送成功，请注意查收短信';
            break;

            case 101:
            $msg = '验证失败';
            break;

            case 102:
            $msg = '短信不足，请充值';
            break;

            case 103:
            $msg = '操作失败';
            break;

            case 104:
            $msg = '非法字符';
            break;

            case 105:
            $msg = '内容过多';
            break;

            case 106:
            $msg = '号码过多';
            break;

            case 107:
            $msg = '频率过快';
            break;

            case 108:
            $msg = '号码内容空';
            break;

            case 109:
            $msg = '账号冻结';
            break;

            case 110:
            $msg = '禁止频繁单条发送';
            break;

            case 112:
            $msg = '号码不正确';
            break;

            case 120:
            $msg = '系统升级';
            break;

            default:
            $code = 0;
            $msg = '未知错误，请联系客服';
        }

        $dat['sms_type']      = $type;     //类型
        $dat['sms_warehouse'] = $tname;    //仓库
        $dat['sms_phone']     = $mobile;   //手机号码
        $dat['sms_time']      = date('Y-m-d H:i:s',time()); //发送时间
        $dat['sms_mkno']      = $mkno; //美快单号
        $dat['sms_content']   = $cont; //发送内容
        $dat['sms_code']      = $code; //短信标识码
        $dat['sms_msg']       = $msg;  //短信反馈信息
        $result = M('MkilSms')->add($dat);	//发送记录保存到数据表
        if($result){
        	return true;
        }else{
        	return false;
        }
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

		ini_set('memory_limit','4088M');
		ini_set('max_execution_time', 0);

		// $value = session('admin');
		// $username = $value['adname'];

		$where['status']      = array('eq','20');			//20 为已使用
		$where['kd100status'] = array('eq','0');		//0 即为仍未发送到快递100
		$where['l.STNO']      = array('exp','is not NULL');	//mk_tran_list 中的 申通号不为NULL
		$where['l.TranKd']    = array('eq','1');			//mk_tran_list 中的 中转方式为申通
		$where['l.IL_state']  = array('eq','200');		//mk_tran_list 中的 物流状态为200  已完成

		//查找状态为已完成但尚未发送的申通号
		$getdtcount = 300; //每次读取的记录数量
		$st = M('Stnolist s')->field('s.id,s.STNO')->join('LEFT JOIN mk_tran_list l ON s.STNO = l.STNO')->where($where)->order('l.optime')->limit($getdtcount)->select();
		$sti = count($st);
		if($sti < 1){
			// return $tips = array('status'=>'404','msg'=>"没有数据需要发送");
			echo 'none';exit;
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

    }


}