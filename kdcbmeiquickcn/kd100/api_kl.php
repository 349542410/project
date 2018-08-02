<?php
/**
 * 客户端
 */
	header("Content-type: text/html; charset=utf-8");
    $config = require('../../config.php');
    define('API_URL', $config['API_URL']);
	include('kl_config.php');
	require_once("../../Application/hprose_php5/HproseHttpClient.php");

	$gid   = isset($_GET['id']) ? trim($_GET['id']) :'';
	$com   = isset($_GET['com']) ? trim($_GET['com']) : '';
	$nu    = isset($_GET['nu']) ? trim($_GET['nu']) : '';
	$show  = isset($_GET['show']) ? trim($_GET['show']) : 0;	//返回类型：json或者xml
	$muti  = isset($_GET['muti']) ? trim($_GET['muti']) : 1;	//如果接收到等于0，则返回最新的一条信息
	$order = isset($_GET['order']) ? trim($_GET['order']) : 'desc';	//默认按时间由新到旧排列

	/*//测试用
	$gid = 'y4nK9CdnOgAOil1E';
	$com = 'MKIL';
	$nu = 'MK8810001690US';
	$show = '0';
	$muti = '0';
	$order = 'desc';
	*/
	$ismkno = preg_match($mkno_rule,$nu);
	if($gid != $id || $com != $mkcom || strlen($gid) != 16 || !$ismkno){
		$msg = 'false';
		$status = '0';
		$state = 0;
		if($show == 0){
			$bstr = Tjson($msg,$status,$state,array());
		}else{
			$bstr = Txml($msg,$nu,$com,$status,$state,array());
		}
		return;
	}

	$client = new HproseHttpClient($serurl);
	// echo $serurl;exit;
    // $msg = $client->info($gid,$com,$nu,$show,$muti,$order);
    $msg = $client->info($gid,$com,$nu,$show,$muti,$order);
    echo $msg;