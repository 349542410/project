<?php
/**
 * 2017-01-19 jie
 * 只用于查询
 * 单独查询某个单号的清关状态/物流信息
 */
	require_once('../db.php');
	require_once('h_config.php');//配置信息
	require_once('c_function.php');//专属方法
	require_once('function.php'); //获取xml并转数组
	$kd6    = $out_load.'\Kdno6.class.php';
	$kdfile = $out_load.'\Kdno6.conf.php';
	require_once($kd6);//加载类
	require_once($kdfile);//载入配置信息

	$EMS = new \Kdno();

	if(!isset($_GET['no'])) die('请输入单号');
	if(trim($_GET['no']) == '') die('单号不能为空');

	$tracking_number = $_GET['no'];

	$type = (isset($_GET['type'])) ? trim($_GET['type']) : '1';

	if($type == '2'){
		//调用文件中的函数处理
		$res = getArr($tracking_number, $debug=0, $EMS, $serurl, $config, $type='outside');//$debug 调试编码，查物流
	}else{
		//调用文件中的函数处理
		$res = getData($tracking_number, $EMS, $config, $pdo, $TranKd, $type='outside');//查询清关状态
	}

	echo '<pre>';
	print_r($res);
	echo '</pre>';