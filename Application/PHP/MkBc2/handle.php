<?php
/**
 * 2017-04-19 jie
 * 用于美快后台系统查询物流信息
 * 单独查询某个单号的 物流信息
 */
	//请求设置
	set_time_limit(0);
	header('Content-type:text/html;charset=UTF-8');

	require_once('mkbc2.class.php'); //加载类
	require_once('function.php'); 	//加载公共函数
	require_once('h_config.php'); 	//加载数据保存的路径
	require_once('deal.php');		//获取得到 物流信息 后的处理方法

	$bc2 = new \bc2();

	if(!isset($_POST['no'])){
		die('no');
	}

	$tracking_number = trim($_POST['no']); //运单号
	$part            = isset($_POST['kind'])? trim($_POST['kind']) : false;//false=查询全部物流信息(默认)，true=查最新的一条物流状态
	// $debug           = (isset($_POST['debug'])) ? trim($_POST['debug']) : 0;
	
	// $tracking_number   = '9973508689200';
	$res = getArr($tracking_number, $serurl, $bc2, $part);//$debug 调试编码，查物流

	echo json_encode($res);//用json形式返回便于 请求方 能够“获取”到返回结果实体形态