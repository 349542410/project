<?php
/*
	create by Man  20161118
	$HTTP = new \Org\MK\HTTP();
	$HTTP->post('网址','参数数组');
	$HTTP->get(''网址，不含?和参数","参数数组");
*/
namespace Org\MK;
class HTTP {
	public function post($url,$data_array,$timeout=30,$rekd=0,$sslversion=3)
	{
		$ch 		= curl_init();
		curl_setopt($ch, CURLOPT_URL,$url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_TIMEOUT, $timeout); 

		if(substr($url,0,5)=='https'){
			//因为版本问题出现以下错误
			//14077438:SSL routines:SSL23_GET_SERVER_HELLO:tlsv1 alert internal error
			//设置为对方版本3后，正常使用
			if($sslversion>0) //180226
				curl_setopt($ch, CURLOPT_SSLVERSION,3);

			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, False); // 跳过证书检查
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST,False); // 从证书中检查SSL加密算法是否存在 
		}
		//指定post数据
		curl_setopt($ch, CURLOPT_POST, true);
		//添加变量
		$postpara 	= (is_array($data_array))?http_build_query($data_array):$data_array;
		curl_setopt($ch, CURLOPT_POSTFIELDS, $postpara);
		if(!is_array($data_array)){
			curl_setopt($ch, CURLOPT_HTTPHEADER, [
	            'Content-Type: application/json',
	            'Content-Length: '.strlen($postpara)
	        ]);
		}

		$output 		= curl_exec($ch);
		$httpStatusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		$httpErr 		= curl_error($ch);
		curl_close($ch);
		$restr = '';
		if($rekd==0){
			if($httpStatusCode==200){
				$restr = trim($output);
			}else{
//				echo 'Error:'.$httpErr;
				$restr = false;
			}
		}
		return $restr;
	}
	public function posts($url,$data_array,$timeout=30,$sslversion=3)
	{
		$ch 		= curl_init();
		curl_setopt($ch, CURLOPT_URL,$url);		
		
		if($sslversion>0)
			curl_setopt($ch, CURLOPT_SSLVERSION,$sslversion);

		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // 跳过证书检查
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false); // 从证书中检查SSL加密算法是否存在 
		curl_setopt($ch, CURLOPT_TIMEOUT, $timeout); 
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		//指定post数据
		curl_setopt($ch, CURLOPT_POST, true);
		//添加变量
		$postpara 	= (is_array($data_array))?http_build_query($data_array):$data_array;
		curl_setopt($ch, CURLOPT_POSTFIELDS,$postpara);
		if(!is_array($data_array)){
			curl_setopt($ch, CURLOPT_HTTPHEADER, [
	            'Content-Type: application/json',
	            'Content-Length: '.strlen($postpara)
	        ]);
		}
		$output 		= curl_exec($ch);
		$httpStatusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		curl_close($ch);
		if($httpStatusCode==200){
			return trim($output);
		}else{
			echo curl_error($ch);
			return false;
		}
	}
	/*
		get 中的url不能包含参数
	*/
	public function get($url,$data_array)
	{
		$ch = curl_init();
		$url_with_data = $url .'?' . http_build_query($data_array);
		curl_setopt($ch, CURLOPT_URL, $url_with_data);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		$output = curl_exec($ch);
		$httpStatusCode 	= curl_getinfo($ch, CURLINFO_HTTP_CODE);
		curl_close($ch);
		if($httpStatusCode==200){
			return trim($output);
		}else{
			return false;
		}
	}
}