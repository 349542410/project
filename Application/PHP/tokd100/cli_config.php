<?php
/*Hprose 专用模块*/
	// include_once('config.php');
	include_once('function.php');
	require_once '../config.php';
	$serurl = LOGISTICS_DOMAIN."/tokd100/api_server.php";	// Hprose调用的服务器端地址

	/*api.php*/
	$mkcom 	= 'MKIL';
	$id 	= 'y4nK9CdnOgAOil1E';

	// 用于匹配美快单号的正则
	$mkno_rule = '/^MK[0-9A-Z]{11}$/';