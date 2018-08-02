<?php
	header("Content-type: text/html; charset=utf-8");
	$conn = @ mysql_connect("127.0.0.1", "mkiluser", "mk12345678") or die("数据库链接错误");
	mysql_select_db("mkil", $conn);
	mysql_query("set names utf8");
	
	//成功
	$arr1 = array(
		'result'=>'true',
		'returnCode'=>'200',
		'message'=>'成功',
	);
	$str_s = json_encode($arr1);
	//失败
	$arr2 = array(
			'result'=>'false',
			'returnCode'=>'500',
			'message'=>'失败',
		);
	$str_f = json_encode($arr2);
		
	//本地不发送至ERP服务器
	$canbackErp = false;
?>