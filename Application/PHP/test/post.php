<?php
	// curl post 发送文件
	$path = $_SERVER['DOCUMENT_ROOT'];

	// echo $path;die;
	$file = $path.'\man.png';
	$file = curl_file_create($file);
	$ch = curl_init();

	$post_data = array(
	    'loginfield' => 'username',
	    'username' => 'ybb',
	    'password' => '123456',
		'file' => $file,
	);

	curl_setopt($ch, CURLOPT_HEADER, false);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, false);
	//启用时会发送一个常规的POST请求，类型为：application/x-www-form-urlencoded，就像表单提交的一样。
	curl_setopt($ch, CURLOPT_POST, true);  
	curl_setopt($ch,CURLOPT_BINARYTRANSFER,true);
	curl_setopt($ch, CURLOPT_POSTFIELDS,$post_data);
	curl_setopt($ch, CURLOPT_URL, 'http://test3.megao.hk/test/handle.php');
	$info= curl_exec($ch);//执行
	curl_close($ch);//关闭URL请求
	
	// print_r($info);
	if($info){
		echo '发送成功';
	}