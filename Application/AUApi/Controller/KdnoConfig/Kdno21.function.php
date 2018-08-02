<?php
	// curl post发送
    function http_post($url, $sendData, $timeout=0){

		$ch = curl_init();
    	
    	// 推送json的时候，需要定义CURLOPT_HTTPHEADER
    	$header = array(
		    'Content-Type: application/x-www-form-urlencoded; charset=GBK',// 定义content-type为json
		    // 'Content-Length: ' . strlen($sendData)
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

	//屏蔽电话号码中间的四位数字
	function hidtel($phone){
	    $IsWhat = preg_match("/^1[0-9]{10}$/",$phone); //手机电话
	    if($IsWhat != 1)
	    {
	        $phone = trim($phone);
	        $phone = explode("-",$phone);
	        return $phone[0]."***".$phone[2];
	    }
	    else
	    {
	        return  preg_replace('/(1[358]{1}[0-9])[0-9]{4}([0-9]{4})/i','$1****$2',$phone);
	    }
	}

	/**
	 * 只保留字符串首尾字符，隐藏中间用*代替（两个字符时只显示第一个）
	 * @param string $user_name 姓名
	 * @return string 格式化后的姓名
	 */
	function substr_cut($user_name){
		if (preg_match("/^([\x{4e00}-\x{9fa5}]+)$/u", $user_name)) {
    	
			$strlen   = mb_strlen($user_name, 'utf-8');
			$firstStr = mb_substr($user_name, 0, 1, 'utf-8');
			$lastStr  = '';//mb_substr($user_name, -1, 1, 'utf-8');
		    return $strlen == 2 ? $firstStr . str_repeat('*', mb_strlen($user_name, 'utf-8') - 1) : $firstStr . str_repeat("**", $strlen - 2) . $lastStr;
    	}else{
    		$user_name = trim($user_name);
    		$site = strpos($user_name,' ');
    		$firstStr = explode(' ', $user_name);
    		return $firstStr[0] . str_repeat('*', strlen($user_name) - $site);
    	}
	}