<?php
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
	$serurl = "http://kd.mkil.megao.cn:8301/server.php";