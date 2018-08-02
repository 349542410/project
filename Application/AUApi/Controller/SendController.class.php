<?php
/**
 * 短信发送功能 服务器端(称重，中转单发用)
 */
namespace AUApi\Controller;
use Think\Controller\HproseController;
class SendController extends HproseController{
    protected $crossDomain =    true;
    protected $P3P         =    true;
    protected $get         =    true;
    protected $debug       =    true;

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
	 * @param  string $ctime [定时发送设置的时间]
	 * @param  string $mid   [未知]
	 * @return [type]        [description]
	 */
	public function sendSMS($mkno,$tname,$type,$cont,$EXPNM='',$ctime='',$mid=''){

		$url = C('MESSAGE.HTTP');  //短信接口
		$uid = C('MESSAGE.UID');   //用户账号
		$pwd = C('MESSAGE.PWD');   //密码

		$reInfo = $this->getInfo($mkno);	//查找单号的收件人信息

        $mobile  = trim($reInfo['reTel']);           //收件人手机号码
        $receiver = trim($reInfo['receiver']);        //收件人姓名

        //sprintf() 函数把格式化的字符串写入一个变量中
        if($type == 'Weighing'){

            $cont = sprintf($cont,$tname,$mkno,$receiver);	//拼接发送内容
        }else{
        	$stno = $reInfo['STNO'];	//申通号
            $cont = sprintf($cont,$tname,$EXPNM,$stno);	//拼接发送内容
        }

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
			'encode'=>'utf8',				//编码格式，看自己的项目需求了 我的是utf8的	
			);
		$res = $this->postSMS($url,$data);			//POST方式提交

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
        $dat['sms_time']      = date('Y-m-d H:i:s',time()); //发生时间
        $dat['sms_mkno']      = $mkno; //美快单号
        $dat['sms_content']   = $cont; //发送内容
        $dat['sms_code']      = $code; //短信标识码
        $dat['sms_msg']       = $msg;  //短信反馈信息
        $res = $this->add($dat);
        if($res){
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

	/**
	 * 发送记录保存到数据表
	 */
	public function add($dat){
		M('MkilSms')->add($dat);
	}

	/**
	 * 查找单号的收件人信息
	 * @param  [type] $mkno [description]
	 * @return [type]       [description]
	 */
	public function getInfo($mkno){
		//$reInfo = M('TranList')->where(array('MKNO'=>$mkno))->find();
		$reInfo = M('TranList')->field('reTel,receiver,STNO')->where(array('MKNO'=>$mkno))->find();
		return $reInfo;
	}

	//========================= Transfer ============================

	/**
	 * 打包中转 获取物流公司信息
	 * @param  [type] $id    [description]
	 * @param  [type] $cname [description]
	 * @return [type]        [description]
	 */
	public function transInfo($id){
		$info = M('ExpressCompany')->field('short_name')->where(array('id'=>$id))->find();
		$ra   = array('no'=>0,'id'=>0,'name'=>'');
		if($info){
			$ra   = array('no'=>$id,'id'=>$id,'name'=>$info['short_name']);
		}
		return $ra;
	}
}