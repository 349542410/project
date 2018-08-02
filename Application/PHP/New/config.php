<?php
	header('Content-type:text/html;charset=utf-8');//声明编码
	error_reporting(E_ALL);//错误等级设置
	date_default_timezone_set('PRC');//时区设置   PRC  中国

//	$conn = @ mysql_connect("localhost", "mkiluser", "mk12345678") or die("数据库链接错误");
//
//	//设置编码
//	mysql_query("set names 'utf-8'");
//	//选择数据表
//	mysql_select_db("mkil", $conn);
    require_once('../db.php');//数据库连接

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

	/**
	 * @param string $sql sql语句
	 * @return array 返回一个二维数组
	 */
	function get_all($sql){
		global $conn;
		$res=mysql_query($sql,$conn);
		$data=array();
		if($res && mysql_num_rows($res)>0){
			while($arr=mysql_fetch_assoc($res)){
				$data[]=$arr;
			}
		}
		return $data;
	}

	/**
	 * @param string $sql sql语句
	 * @return array 返回一个一维数组
	 */
	function get_one($sql){
		global $conn;
		$res=mysql_query($sql,$conn);
		$data=array();
		if($res && mysql_num_rows($res)>0){
			$data=mysql_fetch_assoc($res);
		}
		return $data;
	}

	//测试用打印输出
	function dump($data){
		echo '<pre>';
		print_r($data);
		echo '</pre>';
		return $data;
	}
?>