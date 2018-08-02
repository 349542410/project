<?php
// +----------------------------------------------------------------------
// +----------------------------------------------------------------------
// | Author: Man 2016-06-15
// +----------------------------------------------------------------------

namespace Org\MK;

class MKSYS {
	public function get($url,$data=null)
	{
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url.($data?$data:''));
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		$output = curl_exec($ch);
		curl_close($ch);
		return $output;
	}
	//$post_data可以是数据也可以是 &=
	public function post($url,$data)
	{
		$ch 		= curl_init();
		$post_data 	= (is_array($data))?http_build_query($data):$data;
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
			return 'Error:'.$httpStatusCode;
		}
	}
}