<?php
namespace Org\MK;
class Customs {
	private $jsarray;
	//private $url 		= 'http://u3.megao.hk/Api/Test/testrev';
	private $url 		= 'http://113.204.136.28/KJClientReceiver/Data.aspx?';
	private $myCode 	= '4423962963';
	private $custsendid 	= 'CQITC';
	var $MessageType 	= '';
	var $MessageId	= '';
	var $ActionType 	= 1;
	var $MessageTime 	= '';
	var $MessageBody 	= '';
	var $ErrorStr 		= '';

	function __construct()
	{

	}
	
	//获取海关post来的回执
	public function get()
	{
		if(!isset($_POST["data"])) return 0;
		$data 		= trim($_POST["data"]);
    		if($data=='') return 0;
    		$data 		= base64_decode($data);
    		$jsa		= json_decode(json_encode((array) simplexml_load_string($data)), true);
    		if(!is_array($jsa)) return false;
    		if(!@$header = $jsa['MessageHead']) return false;
    		if(!$header['SenderId']==$this->custsendid) return false;
    		if(!$header['ReceiverId']==$this->myCode) return false;
    		if(!@$type = $header['MessageType']) return false;
    		if(!@$body=$jsa['MessageBody']['DTCFlow'][$type]) return false;
    		//返回错误时没有MESSAGE_TYPE，也没有SUCCESS
    		if(!array_key_exists('MESSAGE_TYPE', $body)){
    			$body['MESSAGE_TYPE']	= $type;
    		}
    		$this->jsarray	= $body;
    		//分析返回的资料是否正常
    		return $this->jsarray;
	}
	private function check()
	{
		if($this->MessageType==''){
			$ErrorStr .='MessageType 必须指定，请参照相关说明';
			return false;
		}
		if($this->MessageId==''){
			$ErrorStr .='MessageId 必须指定，请使用logs.id';
			return false;
		}
		if($this->MessageTime==''){
			$ErrorStr .='MessageTime 必须指定，请使用logs时间并使用 date("Y-m-d\TH:i:s")格式';
			return false;
		}
		if($this->MessageBody==''){
			$ErrorStr .='MessageBody (即相关的XML内容)必须指定';
			return false;
		}
		$this->MessageType = strtoupper($this->MessageType);
		return true;
	}

	//将资料post到海关2016-05-18
	//返回数组array(code,success),当code=0时为网络故障,当code=1表示发送成功，但返回是否成功要参考success是否为1
	public function post()
	{
		if(!$this->check())return false;
		$post_data 	= 'data='.$this->createxml();
		//echo $post_data;exit;
		$ch 		= curl_init();
		$url 		= $this->url;
		//echo $this->url;
		curl_setopt($ch, CURLOPT_URL,$url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		//指定post数据
		curl_setopt($ch, CURLOPT_POST, true);
		//添加变量
		curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
		$output 		= curl_exec($ch);
		$httpStatusCode 	= curl_getinfo($ch, CURLINFO_HTTP_CODE);

		curl_close($ch);

		if($httpStatusCode==200){
			$output 		= trim($output);
			$res 			= (strtolower(trim($output))=='true')?1:0;
			return array('code'=>1,'success'=>$res);
		}else{
			return array('code'=>0,'success'=>'Error:'.$httpStatusCode);
		}
	}
	/*
		$mtime 为 date('Y-m-dTH:i:s')格式，使用保存到logs中的时间
		$mid 使用保存到logs中的id
	*/
	private function createxml()
	{
		$sid 	= $this->myCode;
		$spw 	= 'df60dd03f9434b541ac814f2f0ac76cd';
		$snm 	= '广州美快软件开发有限公司';	
		$type 	= $this->MessageType;
		$mid 	= $this->MessageId;
		$mtime = $this->MessageTime;
		$xml 	 = $this->MessageBody;
		$mid 	= str_replace(array('_','-'),'m',strtolower($type)).'-'.$this->getId().'-'.$mid;
		$xml2 = "<DTC_Message><MessageHead>
				<MessageType>{$type}</MessageType>
				<MessageId>{$mid}</MessageId>
				<ActionType>1</ActionType>
				<MessageTime>{$mtime}</MessageTime>
				<SenderId>{$sid }</SenderId>
				<ReceiverId>CQITC</ReceiverId>
				<UserNo>{$sid }</UserNo>
				<Password>{$spw}</Password>
				</MessageHead>
				<MessageBody><DTCFlow><{$type}>$xml</{$type}></DTCFlow></MessageBody>
			</DTC_Message>";
			$xml2 = str_replace(array("\n","\r","	"),'',$xml2);
			$xml2 = str_replace('%SID%',$sid,$xml2);
			$xml2 = str_replace('%SNM%',$snm,$xml2);
			//echo $xml2;
			$xml2 = base64_encode($xml2);
			return urlencode($xml2);
	}
	private function getId()
	{
		$str = microtime();
		$str = str_replace(' ','-',$str);
		$str = str_replace('.','',$str);
		return $str;
	}
	//将结果post到客户的ERP中
	public function putdata($url,$data)
	{
		$post_data 	= 'data='.$data;
		$ch 		= curl_init();
		//echo $data;
		curl_setopt($ch, CURLOPT_URL,$url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
		$output 		= curl_exec($ch);
		$httpStatusCode 	= curl_getinfo($ch, CURLINFO_HTTP_CODE);

		curl_close($ch);

		if($httpStatusCode==200){
			$output 		= trim($output); echo $output;
			$res 			= (strtolower(trim($output))=='true')?1:$output;
			return array('code'=>1,'success'=>$res);
		}else{
			return array('code'=>0,'success'=>'Error:'.$httpStatusCode);
		}
	}
}