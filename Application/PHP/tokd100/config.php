<?php
/* 公共配置模块 */
	require_once('../db.php');//数据库连接
	header("Content-type: text/html; charset=utf-8");
	//error_reporting(E_ALL);//错误等级设置
	date_default_timezone_set('PRC');
	ini_set('memory_limit','100M');
	ini_set('max_execution_time', 0);
	// mysql链接数据库
	// $conn = @ mysql_connect("127.0.0.1", "mkiluser", "mk12345678") or die("数据库链接错误");
	// mysql_select_db("mkil", $conn);
	// mysql_query("set names utf8");

	// $dsn    = 'mysql:dbname=mkil;host=127.0.0.1';
	// $user   = 'mkiluser';//数据库用户名
	// $passwd = 'mk12345678';//数据库密码

	// try{
	// 	$pdo = new PDO($dsn, $user, $passwd);
	// 	$pdo->query('set names utf8');//设置字符集
	// }catch(PDOException $e){
	// 	echo '数据库连接失败'.$e->getMessage();die;
	// }

	

	/*默认配置*/
	$standard = array(
		'show'  => '0',	//返回类型 0默认返回json格式
		'muti'  => '1',	//返回信息数量	0只返回最新一行信息，>0的时候取全部数据
		'order' => 'desc',	//默认按时间由新到旧排列
	);

	/*api.php*/
	$mkcom 	= 'MKIL';
	$id 	= 'y4nK9CdnOgAOil1E';

	/*order.php*/
	$mgcom    = 'MKIL';
	$key      = 'dQRCFqtxzpxqbICY';
	$customer = "kuaidi100";

	/*send.php*/
	$limit = 100;	//默认查询条数限制
	$send_url = "http://test3.megao.hk/tokd100/callback.php";	// 发送出去的目标地址

	// 用于匹配美快单号的正则
	$mkno_rule = '/^MK[0-9A-Z]{11}$/';