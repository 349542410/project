<?php

	/** 生成xml格式 广东邮政对接专用 */
	function arrayToXml($arr){
		$xml = "";
		
		foreach ($arr as $key=>$val){
			if(is_numeric($key)){
				$xml .= "<item>".arrayToXml($val)."</item>";
			}else{
				if(is_array($val)){
					$xml.="<".$key.">".arrayToXml($val)."</".$key.">";
				}else{
					$xml.="<".$key.">".$val."</".$key.">";
				}
			}
		}

		return $xml;
	}

	// curl post发送
    function sendData($url, $data){

		$ch = curl_init();
		// $header[] = "Content-type: text/xml";//定义content-type为xml
		curl_setopt($ch, CURLOPT_URL, $url); //定义表单提交地址
		curl_setopt($ch, CURLOPT_POST, 1);   //定义提交类型 1：POST ；0：GET
		curl_setopt($ch, CURLOPT_HEADER, 0); //定义是否显示状态头 1：显示 ； 0：不显示
		// curl_setopt($ch, CURLOPT_TIMEOUT,10); //超时6秒
		// curl_setopt($ch, CURLOPT_HTTPHEADER, $header);//定义请求类型
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);//定义是否直接输出返回流，即把返回的请求结果赋予给$info
		curl_setopt($ch, CURLOPT_POSTFIELDS, $data); //定义提交的数据，这里是XML文件
    		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // 跳过证书检查
		$info = curl_exec($ch);//执行
		curl_close($ch);//关闭
		return $info;
    }