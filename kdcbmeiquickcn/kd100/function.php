<?php
/* 公共函数 */
	function Tjson($msg,$status,$state,$data=array(),$noechoyn=false){
		$backStr = array(
			'message' => $msg,
			'status'  => $status,
			'state'   => $state,
			'data'    => $data,
		);
		if($noechoyn == true){
			return json_encode($backStr);
		}else{
			echo json_encode($backStr);
		}
		
	}

	function Txml($msg,$nu,$com,$status,$state,$data_array=array(),$noechoyn=false){
		$xml = "<?xml version=\"1.0\" encoding=\"utf-8\"?>\n";
		$xml .= "<xml>\n";
		$xml .= "<message>".$msg."</message>\n";
		$xml .= "<nu>".$nu."</nu>\n";
		$xml .= "<com>".$com."</com>\n";
		$xml .= "<status>".$status."</status>\n";

		if(count($data_array) > 0){
			// 循环创建XML单项
			foreach ($data_array as $data) {
				$xml .= create_item($data['context'], $data['time']);
			}
		}
		 
		$xml .= "<state>".$state."</state>\n";
		$xml .= "</xml>\n";
		
		if($noechoyn == true){
			return $xml;
		}else{
			echo $xml;
		}
	}
 
	//  创建XML单项
	function create_item($context, $time){
	    $item = "<data>\n";
	    $item .= "<context>" . $time . "</context>\n";
	    $item .= "<time>" . $context . "</time>\n";
	    $item .= "</data>\n";
	 
	    return $item;
	}

	/**
	 * status计算方式
	 * @param  [type] $stateArr [description]
	 * @return [type]           [description]
	 */
	function tran_state($stateArr){
		if ($stateArr == 12) {
			$states = '1';
		}else if($stateArr != 12 && $stateArr < 1000){
			$states = '0';
		}else if($stateArr >= 1000){
			$states = intval($stateArr) - 1000;
		}
		return $states;
	}

	//测试用打印输出
	function dump($data){
		echo '<pre>';
		print_r($data);
		echo '</pre>';
		return $data;
	}

	function curl_post($url, $post) {
	    $options = array(  
	        CURLOPT_RETURNTRANSFER => true,  
	        CURLOPT_HEADER         => false,  
	        CURLOPT_POST           => true,  
	        CURLOPT_POSTFIELDS     => $post,  
	    );  
	  
	    $ch = curl_init($url);  
	    curl_setopt_array($ch, $options);  
	    $result = curl_exec($ch);  
	    curl_close($ch);  
	    return $result;  
	}

//============================= 停用 Jie 20160118 ======================================
	// //获取IL_state，判断state的值
	// function get_state($nu){
	// 	global $pdo;
	// 	// 查询该单号的IL_state
	// 	$sql = "select IL_state from mk_tran_list where `MKNO` = '$nu' AND `IL_state` > 11 limit 1";
	// 	$res = $pdo->prepare($sql);
	// 	$res->execute();
	// 	$num = $res->rowCount();
	// 	//查询的数据总数为0时返回false
	// 	if($num == 0){
	// 		return false;exit;
	// 	}

	// 	foreach($res as $row){
	// 		$stateArr = $row['IL_state'];
	// 	}
		
	// 	//state赋值
	// 	$state = tran_state($stateArr);

	// 	return $state;
	// }