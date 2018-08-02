<?php
	// curl post发送
    function http_post($url, $sendData, $timeout){

		$ch = curl_init();
		// 定义content-type为xml
		// $header[] = "Content-type: text/xml";
    	
    	// 推送json的时候，需要定义CURLOPT_HTTPHEADER
    	$header = array(
		    'Content-Type: application/json',
		    'Content-Length: ' . strlen($sendData)
		);
		curl_setopt($ch, CURLOPT_URL, $url); //定义表单提交地址
		curl_setopt($ch, CURLOPT_POST, 1);   //定义提交类型 1：POST ；0：GET
		curl_setopt($ch, CURLOPT_HEADER, 0); //定义是否显示状态头 1：显示 ； 0：不显示
		curl_setopt($ch, CURLOPT_TIMEOUT,$timeout); //超时 秒
		curl_setopt($ch, CURLOPT_HTTPHEADER, $header);//定义请求类型
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);//定义是否直接输出返回流，即把返回的请求结果赋予给$info
		curl_setopt($ch, CURLOPT_POSTFIELDS, $sendData); //定义提交的数据，这里是XML文件
    	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // 跳过证书检查
		$info = curl_exec($ch);//执行
		curl_close($ch);//关闭
		return $info;
    }