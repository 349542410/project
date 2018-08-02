<?php
/**
 * 2017-01-19 jie   暂不可用
 * 
 * 
 */
	header('Content-type:text/html;charset=UTF-8');//设置输出格式
	require_once('h_config.php');//配置信息
	// require_once('function.php'); //获取xml并转数组
	$kd6    = $out_load.'\GoodsCustoms.class.php';
	// $kdfile = $out_load.'\Kdno6.conf.php';
	require_once($kd6);//加载类
	// require_once($kdfile);//载入配置信息

	$EMS = new \Kdno();

	if(!isset($_POST['arr'])){
		die('no');
	}

	$list = $_POST['arr'];

	//调用文件中的函数处理
	$res = $EMS->isGoods($list, true);

	echo json_encode($res);//用json形式返回便于 请求方 能够“获取”到返回结果实体形态