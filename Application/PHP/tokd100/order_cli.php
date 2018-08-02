<?php
/**
 * 客户端
 */
	header("Content-type: text/html; charset=utf-8");
	include('order_config.php');
	include('order_function.php');
	require_once("../../hprose_php5/HproseHttpClient.php");

	$sign      = trim($_POST['sign']);
	$gcustomer = trim($_POST['customer']);
	$param     = trim($_POST['param']);
	$param_arr = json_decode($param,true);
	$company   = trim($param_arr['company']);

	/* 开始验证 */
	if($sign != md5($param.$key)){
		$bstr = Tjson('false','503','验证签名失败');
		return;
	}

	if($gcustomer != $customer){
		$bstr = Tjson('false','500','请求格式错误');
		return;
	}

	if($company != $mgcom){
		$bstr = Tjson('false','500','请求格式错误');
		return;
	}

	$client = new HproseHttpClient($serurl);
	// echo $serurl;exit;
    // $msg = $client->info($gid,$com,$nu,$show,$muti,$order);
    $msg = $client->info($sign,$gcustomer,$param,$param_arr,$company);
    echo $msg;