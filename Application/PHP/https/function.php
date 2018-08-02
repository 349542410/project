<?php
	/**
	 * curl函数发送数据到ERP
	 * @param  [type] $url       [接收数据的api]
	 * @param  [type] $vars      [提交的数据]
	 * @param  [type] $second    [要求程序必须在$second秒内完成,负责到$second秒后放到后台执行]
	 * @return [type]            [description]
	 */
	function curl_post_ssl($url, $vars, $second=30,$aHeader=array())
	{
	    $ch = curl_init();
	    // curl_setopt($ch,CURLOPT_VERBOSE,1);//debug模式
	    // curl_setopt($ch,CURLOPT_TIMEOUT,$second);//超时
	    curl_setopt($ch, CURLOPT_HEADER, 0); //定义是否显示状态头 1：显示 ； 0：不显示
	    curl_setopt($ch,CURLOPT_RETURNTRANSFER, 1);//定义是否直接输出返回流，即把返回的请求结果赋予给$info
	    curl_setopt($ch,CURLOPT_URL,$url);//定义表单提交地址
	    curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,0);// 跳过证书检查
	    curl_setopt($ch,CURLOPT_SSL_VERIFYHOST,0);// 检查证书中是否设置域名,0不验证
	    // curl_setopt($ch,CURLOPT_SSLCERTTYPE,'key');//证书类型
	    // curl_setopt($ch,CURLOPT_SSLCERT,'/keys/publickey.key');//client.crt文件路径
	    // curl_setopt($ch,CURLOPT_SSLCERTPASSWD,'1234');//client证书密码
	    // curl_setopt($ch,CURLOPT_SSLKEYTYPE,'key');//私钥类型
	    // curl_setopt($ch,CURLOPT_SSLKEY,'/keys/privatekey.key');

	    if( count($aHeader) >= 1 ){
	        curl_setopt($ch, CURLOPT_HTTPHEADER, $aHeader);//定义请求类型
	    }

	    curl_setopt($ch,CURLOPT_POST, 1);//定义提交类型 1：POST ；0：GET
	    curl_setopt($ch,CURLOPT_POSTFIELDS,$vars);//定义提交的数据
	    $data = curl_exec($ch);//执行
	    curl_close($ch);//关闭

	    if($data){
	        return $data;
	    }
	    else{
	        return false;
	    }
	}