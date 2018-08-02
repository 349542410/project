<?php
/*
	版本号：V2.0
	创建人：Jie
	创建日期：2016-10-10
	修改日期：2016-10-11
	用途：给后台Admin主动获取顺丰物流信息并保存
 */
	header('Content-type:text/html;charset=UTF-8');//设置输出格式
	include_once('h_config.php');//配置信息
	include_once('r_function.php');//获取xml并转数组

	if(!isset($_POST['no'])){
		die('no');
	}

	$tracking_number = trim($_POST['no']);
	$debug           = (isset($_POST['debug'])) ? trim($_POST['debug']) : 0;
	
	$res = getArr($tracking_number, $debug);//$debug=1 调试编码，不同数值可用于检查不同位置的数据输出
	echo json_encode($res);//用json形式返回便于 请求方 能够“获取”到返回结果实体形态