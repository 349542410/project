<?php
/*模拟接收*/
	// include('config.php');
	require_once('../db.php');//数据库连接
	if($_POST){
		$arr = array(
			"result"=>"true",
			"returnCode"=>"200",
			"message"=>"成功",
		);
		echo $str = json_encode($arr);
		
		// $res = curl_post("http://test3.megao.hk/tokd100/send.php", $str);
		// echo $res;
	}else{
		die('没有数据接收到');
	}
