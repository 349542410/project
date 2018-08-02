<?php
namespace Org\MK;
class CQEMS {
	public function get()
	{
		# code...
		/*<?xml version="1.0" encoding="UTF-8"?>*/
		$url = 'http://os.ems.com.cn:8081/zkweb/bigaccount/getBigAccountDataAction.do?';
		$xml = '<XMLInfo><sysAccount>50010600832577</sysAccount><passWord>e10adc3949ba59abbe56e057f20f883e</passWord><appKey></appKey><businessType>1</businessType><billNoAmount>2</billNoAmount></XMLInfo>';
		$data = 'method=getBillNumBySys&xml='.urlencode(base64_encode($xml));
		

		$posturl = $url .$data;
		//$res 	= $this->httpget($posturl);
		
		/*post
		echo base64_decode($res);
		$res 	= $this->httppost($url,$data);*/

		$res 	= base64_decode($res);
		$res 	= '<?xml version="1.0" encoding="utf-8"?><response><result>1</result><errorDesc>无错误信息</errorDesc><errorCode>E000</errorCode><assignIds><assignId><billno>1131422053006</billno></assignId><assignId><billno>1131422054306</billno></assignId></assignIds></response>';
		$res 	= json_decode(json_encode((array) simplexml_load_string($res)), true);
		if(array_key_exists('result', $res)){
			if($res['result']==0){
				return array('code'=>0,'error'=>$res['result']);
			}elseif($res['result']==1 && array_key_exists('assignIds', $res)){
				return array('code'=>1,'ids'=>$res['assignIds']['assignId']);
			}
		}
		return array('code'=>0,'error'=>'');
	}
	private function httpget($url){
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		$output = curl_exec($ch);
		curl_close($ch);
		return $output;
	}
	private function httppost($url,$post_data)
	{
		$ch 		= curl_init();
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
			return trim($output);
		}else{
			return 0;
		}
	}
	//邮政发来物流轨迹
	public function getcontrail()
	{
		# code...
	}
}