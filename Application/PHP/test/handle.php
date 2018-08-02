<?php
	// header("content-type:text/html;charset=utf-8"); 
	// echo '<pre>';
	// print_r($_POST);

	// echo '<br />';
	// echo '===file upload info:';
	// echo '<br />';

	// print_r($_FILES);
	$backArr = array('returnCode'=>'200', 'msg'=>'推送失败');
	echo json_encode($backArr);//用json形式返回便于 请求方 能够“获取”到返回结果实体形态

	// $file_path = $_FILES['file']['tmp_name'];

	// if(file_exists($file_path)){
	// 	$fp = fopen($file_path,"r");
	// 	$str = "";
	// 	$buffer = 1024;//每次读取 1024 字节
	// 	while(!feof($fp)){//循环读取，直至读取完整个文件
	// 	$str .= fread($fp,$buffer);
	// 	} 
	// 	$str = str_replace("\r\n","<br />",$str);
	// 	echo $str;
	// }

	// if(file_exists($file_path)){
	// 	$str = file_get_contents($file_path);//将整个文件内容读入到一个字符串中
	// 	$str = str_replace("\r\n","<br />",$str);
	// 	echo $str;
	// }