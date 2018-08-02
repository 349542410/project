<?php
/*Hprose 专用模块*/
	// include_once('config.php');
	$config = require('../../config.php');
	include_once('function.php');
	//$serurl = "http://kd100.mkil.meiquick.cn:8333/to/api_server.php";	// Hprose调用的服务器端地址
	$serurl =  $config['API_URL'] . "/Application/PHP/tokd100/api_server.php";	// Hprose调用的服务器端地址

	/*api.php*/
	$mkcom 	= 'kaola';
	$id 	= 'EMura5xzTbHsq2Qk';

	// 用于匹配美快单号的正则
	$mkno_rule = '/^MK[0-9A-Z]{11}$/';