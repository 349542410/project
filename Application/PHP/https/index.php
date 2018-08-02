<?php
	header('Content-type:text/html;charset=utf-8');
	include_once('function.php');

	if($_POST){

		$message  = trim($_POST['message']);
		$areatext = htmlspecialchars(trim($_POST['area']));

		$msg = '&messageType='.$message;
		// $msg .= '&messageText='.$areatext;
		// $url = "http://58.63.50.170:18080/cbt/client/declare/sendMessage.action?clientid=CO0000000033&key=12345678".$msg;
		$url = "http://58.63.50.170:18080/cbt/client/declare/sendMessage.action?clientid=KJ00001&key=TEST123".$msg; //测试网址

		// print_r($url);die;
		$data = array();
		// $data['messageType'] = $message;
		$data['messageText'] = base64_encode($areatext);
		// print_r($data);die;
		$res = curl_post_ssl($url,$data);
		print($res);
	}