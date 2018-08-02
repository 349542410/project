<?php
	$config = require('../../config.php');
	//成功
	$arr1 = array(
		'result'=>'true',
		'returnCode'=>'200',
		'message'=>'成功',
	);
	$str_s = json_encode($arr1);
	//失败
	$arr2 = array(
			'result'=>'false',
			'returnCode'=>'500',
			'message'=>'失败',
		);
	$str_f = json_encode($arr2);
	$serurl 		=  $config['API_URL'] . "/Application/PHP/Ex_common_files/server.php";
	$serurl1 	=  $config['API_URL'] . "/Application/PHP/Ex_common_files/server2.php";