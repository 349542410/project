<?php
	/*
	create by Man 161012 用于读取一个顺丰单的物流信息
	*/
	include('h_config.php');
	// include('r_function.php');
	include('function.php');
	require_once('ex_config.php');
	header('Content-type:text/html;charset=UTF-8');	//设置输出格式
	if(!isset($_GET['no'])){
		die('no');
	}
	$tracking_number = $_GET['no'];

	$cXml = createXML($customerCode, $tracking_type, $tracking_number, $lang);
	$data = base64_encode($cXml);//xml报文加密

	$validateStr = base64_encode(md5(utf8_encode($cXml).$checkword, false));

	$client = new \SoapClient ($pmsLoginAction);
	$result = $client->sfexpressService(array('data'=>$data,'validateStr'=>$validateStr,'customerCode'=>$customerCode));//查询，返回的是一个结构体
	
	$reArr = get_object_vars($result);

	// 把顺丰发过来的xml报文中包含的&进行转义
	$reArr['Return'] = str_replace("&", "&amp;", $reArr['Return']);

	$reArr = xml_array($reArr);// 返回的XML报文转为数组

	$reArr = $reArr['Return'];

	var_dump($reArr);