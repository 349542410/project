<?php
	header("Content-type: text/html; charset=utf-8");
	error_reporting(E_ALL);//错误等级设置
	date_default_timezone_set('PRC');
	ini_set('memory_limit','4088M');	//内存容量
	ini_set('max_execution_time', 0);	//超时时间

//	$dsn    = 'mysql:dbname=mkil;host=10.66.135.76:33060';
//	$user   = 'mkiluser';	//数据库用户名
//	$passwd = '3hNU^172v#hvH6F+';	//数据库密码
//
//	try{
//		$pdo = new PDO($dsn, $user, $passwd);
//		$pdo->query('set names utf8');//设置字符集
//	}catch(PDOException $e){
//		echo '数据库连接失败'.$e->getMessage();die;
//	}
    require_once('../db.php');//数据库连接

	// 配置
	$customerCode   = 'OSMS_215';			// 客户编码 必需
	$checkword      = '350e4437b96d';	// IBS系统为客户分配的密钥 必需
	$pmsLoginAction = 'http://osms.sf-express.com/osms/services/OrderWebService?wsdl';	// 请求地址 必需
	$lang           = 'zh_CN';			// 语言
	$tracking_type  = '1';				// 1.根据顺丰运单号查询; 2.根据客户订单号查询; 3.在IBS查询，不区分运单号和订单号
	$no             = 'STNO';			// STNO：顺丰运单号; MKNO：客户订单号(即我方的美快单号)
	$TranKd         = 5;				// 4：表示顺丰
	$limit          = 10; 				// 批量交易个数(即查询数据表的数据条数)
	$limitHour      = 36000;			// 程序操作时限(10个小时)