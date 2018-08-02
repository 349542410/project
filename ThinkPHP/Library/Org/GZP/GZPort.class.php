<?php
/*/ +----------------------------------------------------------------------
// +----------------------------------------------------------------------
// | Author: Man 2017-03-09
// | 对接广州单一窗口
	使用方法：
	$xl 	= new \Org\GZP\GZPort();
	$rs 	= $xl->send('原业备报文xml字串','报文类型KJ881101','保存到文件的前缀MK888102311');
// +----------------------------------------------------------------------*/

namespace Org\GZP;

class GZPort{


	protected $prikey;
	protected $signedXML;
	protected $para 		= array();
	protected $sendUrl;

	protected $dirbase;
	protected $logs;
	protected $saveto;

	protected $sender;
	protected $MessageID;
	protected $SendTime;

	public function __construct()
	{
		require_once('GZPort.conf.php');
		$this->para 		= array(
			'clientid'		=> $config['clientid'],
			'key'			=> $config['key'],
			'messageType'	=> '',
			'messageText'	=> '',
		);
		$this->dirbase 		= dirname(__FILE__).'/';
		$this->sendUrl		= $config['sendUrl'];
		$this->sender 		= $config['sender'];
		$this->logs			= false;
		if($config['logs'] && $config['saveto']){
			$this->saveto	= $config['saveto'];
			$this->logs		= true;
		}
		
	}

	private function getKey($keyfile='key/privatekey.key',$keytype='key')
	{

		if(!file_exists($keyfile)){
			return false;
		}
		$private_key = file_get_contents($keyfile);
		
		if($keytype=='key'){
			$private_key= base64_encode($private_key);
		}
	    $private_key	= str_replace("-----BEGIN RSA PRIVATE KEY-----","",$private_key);
		$private_key	= str_replace("-----END RSA PRIVATE KEY-----","",$private_key);
		$private_key	= str_replace("\n","",$private_key);
		$private_key	= "-----BEGIN RSA PRIVATE KEY-----".PHP_EOL .wordwrap($private_key, 64, "\n", true). PHP_EOL."-----END RSA PRIVATE KEY-----";
		$this->prikey 	= $private_key;
		return true;
	}

	private function sign($xmldom)
	{
		//
		if(empty($xmldom)) return 0;

		
		if(empty($this->prikey)){
			$keyname = $this->dirbase.'key/privatekey.key';
			if(!$this->getKey($keyname)){
				return 1;
			}
		}

		$xmlDocument 	= new \DOMDocument();
		$xmlDocument->loadXML($xmldom); //use load(file) 经常无法读取

		$xmlTool 		= new \Org\GZP\FR3D\XmlDSig\Adapter\XmlseclibsAdapter();
		$xmlTool->setPrivateKey($this->prikey);
		$xmlTool->addTransform(\Org\GZP\FR3D\XmlDSig\Adapter\XmlseclibsAdapter::ENVELOPED);
		$xmlTool->sign($xmlDocument);

		//var_dump($xmlDocument);
		//$xmlDocument->save('test_'.time().'.xml');
		$this->signedXML = $xmlDocument->saveXML();
		$this->para['messageText'] = $this->signedXML;

	}
	private function genxml($xmldom,$messageType)
	{
		$xml 		= file_get_contents($this->dirbase.'BaseXML/'.$messageType.'.xml');
		list($usec, $sec) = explode(" ", microtime());
		$SendTime	= date('YmdHis',$sec);
		$MessageID 	= $messageType.'_'.$this->sender.'_'.$SendTime.round($usec*1000000);
		$DATA 		= base64_encode($xmldom);
		$xmlstr 	= str_replace(
			array('{%MessageID%}','{%SendTime%}','{%DATA%}'), 
			array($MessageID,$SendTime,$DATA), $xml);

		$this->MessageID 	= $MessageID;
		$this->SendTime		= $SendTime;
		return $xmlstr;
	}
	//$filepre 用于保存文件的前缀
	public function send($xmldom=null,$messageType,$filepre='')
	{
		$res = array(
			"Code" 	=> 0,
			"Err"	=> 'post出错',
		);
		
		if(empty($this->signedXML) || !empty($xmldom)){
			$this->sign($this->genxml($xmldom,$messageType));
		}
		if(empty($this->signedXML)){
			return $res;
		}
		$this->para['messageType'] = $messageType;


		$http 	= new \Org\MK\HTTP();
		$rs 	= $http->post($this->sendUrl,$this->para);

		if($rs===false){
			$rsdata 	=  $res;
		}else{
			$rsdata = array(
				'Code'		=> 1,
				'Err'		=> '',
				'MessageID'	=> $this->MessageID,
				'SendTime'	=> $this->SendTime,
				'Data'		=> json_decode($rs,true),
			);
		}
		
		if($this->logs){
			$rsstr = var_export($rsdata,true)."\t\r".$xmldom;
			file_put_contents(
				$this->saveto.$filepre.'_'.$this->MessageID.'.xml',
				$rsstr
			);
		}
		return $rsdata;
	}
	public function get()
	{
		# code...
	}
}