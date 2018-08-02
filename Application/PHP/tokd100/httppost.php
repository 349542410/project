<?php
	require_once '../config.php';
	/*模拟发送*/
	function curl_post($url, $post) {
	    $options = array(  
	        CURLOPT_RETURNTRANSFER => true,  
	        CURLOPT_HEADER         => false,  
	        CURLOPT_POST           => true,  
	        CURLOPT_POSTFIELDS     => $post,  
	    );  
	  
	    $ch = curl_init($url);  
	    curl_setopt_array($ch, $options);  
	    $result = curl_exec($ch);  
	    curl_close($ch);  
	    return $result;  
	}

	$param = array(
		'company' => 'MKIL',	// 说明：需与我们的$mgcom相同
		'code' => 'MK881000169US',	// 这个是tran_list.MKNO，
		'operator' => 'repush',	// 操作:order表示订阅，repush表示请求快递公司发起一次全量重推
		'callback' => 'http://www.hehe.com/openit',
	);
	$par = json_encode($param);
	$data = array(
		'sign' => '27b9cb6313ef1b423646ebb5be256897',
		'customer' => 'kuaidi100',
		'param' => $par,
	);

	$res = curl_post(LOGISTICS_DOMAIN."/tokd100/order_cli.php", $data);  

	echo $res;
	// var_dump($res);