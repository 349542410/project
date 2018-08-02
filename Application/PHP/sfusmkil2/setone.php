<?php
/*
	版本号：V2.0
	创建人：Jie
	创建日期：2016-10-10
	修改日期：2016-10-11
	用途：用 网址的形式 直接访问此文件，如http://test3.megao.hk/sfus/setone.php?no=080000820051
 */

	include_once('h_config.php');//配置信息
	include_once('r_function.php');//获取xml并转数组

	if(!isset($_GET['no'])){
		die('no');
	}

	$no = trim($_GET['no']);

	$debug = (isset($_GET['debug'])) ? trim($_GET['debug']) : 0;

	$res = getArr($no, $debug);//$debug=1 调试编码，不同数值可用于检查不同位置的数据输出

	print_r($res);