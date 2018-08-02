<?php

	header("Content-type: text/html; charset=utf-8");
	error_reporting(E_ALL);//错误等级设置
	date_default_timezone_set('PRC');
	ini_set('memory_limit','4088M');
	ini_set('max_execution_time', 0);

	// $dsn    = 'mysql:dbname=mkil;host=127.0.0.1';
	// $user   = 'mkiluser';	//数据库用户名
	// $passwd = 'mk12345678';	//数据库密码

	// try{
	// 	$pdo = new PDO($dsn, $user, $passwd);
	// 	$pdo->query('set names utf8');//设置字符集
	// }catch(PDOException $e){
	// 	echo '数据库连接失败'.$e->getMessage();die;
	// }

	require_once('../db.php');//数据库连接

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