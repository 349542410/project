<?php
	header("Content-type: text/html; charset=utf-8");
	error_reporting(E_ALL);//错误等级设置
	date_default_timezone_set('PRC');
	ini_set('memory_limit','4088M');	//内存容量
	ini_set('max_execution_time', 0);	//超时时间

//	$dsn    = 'mysql:dbname=mkil;host=127.0.0.1';
//	$user   = 'mkiluser';	//数据库用户名
//	$passwd = 'mk12345678';	//数据库密码
//
//	try{
//		$pdo = new PDO($dsn, $user, $passwd);
//		$pdo->query('set names utf8');//设置字符集
//	}catch(PDOException $e){
//		echo '数据库连接失败'.$e->getMessage();die;
//	}
    require_once('../db.php');//数据库连接


	// 数据表数据 查询条件 配置
	$uuid    = time();	//标识码(时间戳) 访问此文件的时候马上生成
	$oneHour = time()-3540;	//当前时间的59分钟前

	// 物流配置信息
	$limit     = 10; // 一次性锁定多少个数据
	$userid    = 'megaoshop';
	$pwd       = '203e11cd-947a-4c61-add5-c9a78f61e897';
	$customs   = '3109';
	$msgtype   = 'cnec_jh_getgoods';
	$url       = "https://api.kjb2c.com/dsapi/dsapi.do";
?>