<?php

	require_once('./../../hprose_php5/HproseHttpServer.php');

	function save($arr, $id, $type=''){
		// require_once('connect.php');
		require_once('../db.php');//数据库连接
		require_once('function.php');
			
		$arr = three_to_one($arr);

		// 时间格式转换为YY-MM-DD HH:ii:ss
		$arr['returnTime'] = preg_replace('{^(\d{4})(\d{2})(\d{2})(\d{2})(\d{2})(\d{2})(.*?)$}u', '$1-$2-$3 $4:$5:$6', $arr['returnTime']);

		$sql = "SELECT * FROM mk_tran_list_receipt where `guid` = '$arr[guid]' AND `requestType` = '$type'";

		$check = $pdo->query($sql);

		$count = $check->rowCount();

		$k_data = '';
		$s_data = '';

		// 不存在，则新增
		if($count == 0){
			$arr['lid']         = $id;
			$arr['requestType'] = $type;

			foreach($arr as $key=>$item){
				$k_data .= $key.",";
				$s_data .= "'".$item."',";
			}

			$k_data = rtrim($k_data,',');//清除最右侧的英文逗号
			$s_data = rtrim($s_data,',');//清除最右侧的英文逗号
			// $k_data = implode(array_keys($arr),',');
			// $s_data = implode(array_values($arr),',');
			$d_sql = 'INSERT INTO mk_tran_list_receipt ('.$k_data.') VALUES ('.$s_data.')';
		}else{

			$cinfo = $check->fetch(PDO::FETCH_ASSOC);

			foreach($arr as $key=>$item){
				$s_data .= $key."='".$item."',";
			}

			$s_data = rtrim($s_data, ',');//清除最右侧的英文逗号

			$d_sql = 'UPDATE mk_tran_list_receipt SET '.$s_data." WHERE id = '$cinfo[id]'";

		}

		// 新增/更新成功
		if($pdo->exec($d_sql) !== false){
			return true;
		}else{
			return false;
		}
	}

	$server = new HproseHttpServer();
	$server->setErrorTypes(E_ALL);
	$server->setDebugEnabled();
	$server->addFunction('save');
	$server->start();