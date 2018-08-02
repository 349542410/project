<?php
/**
 * 2017-01-19 jie
 * 只用于查询
 * 单独查询某个单号的清关状态/物流信息
 */
	header('Content-type:text/html;charset=UTF-8');//设置输出格式
	require_once('h_config.php');//配置信息
	require_once('function.php'); //获取xml并转数组
	$kd6    = $out_load.'\Kdno11.class.php';
	$kdfile = $out_load.'\Kdno11.conf.php';
	require_once($kd6);//加载类
	require_once($kdfile);//载入配置信息

	$EMS = new \Kdno();

	if(!isset($_POST['no'])){
		die('no');
	}

	$tracking_number = trim($_POST['no']);
	$debug           = (isset($_POST['debug'])) ? trim($_POST['debug']) : 0;

	//调用文件中的函数处理
	$res = getArr($tracking_number, $debug=0, $EMS, $serurl, $config);//$debug 调试编码，查物流

	echo json_encode($res);//用json形式返回便于 请求方 能够“获取”到返回结果实体形态