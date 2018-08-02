<?php
	include('config.php');

	$gid   = trim($_GET['id']);
	$com   = trim($_GET['com']);
	$nu    = trim($_GET['nu']);
	$show  = trim($_GET['show']) ? trim($_GET['show']) : trim($standard['show']);	//返回类型：json或者xml
	$muti  = trim($_GET['muti']) == 0 ? trim($standard['muti']) : trim($_GET['muti']);	//如果接收到等于0，则返回最新的一条信息
	$order = trim($_GET['order']) ? trim($_GET['order']) : trim($standard['order']);	////默认按时间由新到旧排列

	$state = get_state($nu);	//获取IL_state

	if($gid != $id || $com != $mkcom || strlen($gid) != 16){

		$msg = 'false';

		if($show == 0){
			$status = '0';
			echo Tjson($msg,$status,$state);
		}else{
			$status = '物流单暂无结果';
			echo Txml($msg,$nu,$com,$status,$state);
		}
		exit;

	}else{

		$sql = $muti == '0' ? "select MKNO,content,create_time from mk_il_logs where `MKNO` = '$nu' order by `create_time` desc limit 1" : "select MKNO,content,create_time from mk_il_logs where `MKNO` = '$nu' order by `create_time` $order limit $muti";
		$mk = array();
		$query = mysql_query($sql);

		//接口出现异常
        if(!$query){

			$msg = 'false';
			
			if($show == 0){
				$status = '2';
				echo Tjson($msg,$status,$state);
			}else{
				$status = '接口出现异常';
				echo Txml($msg,$nu,$com,$status,$state);
			}
            exit;

        }else{
        	$msg = 'ok';
        	while($row = mysql_fetch_array($query)){
				$mk[] = $row;
				echo '<pre>';
				// print_r($row);
			}

			$data = array();
			foreach($mk as $k=>$item){
				$data[$k]['time'] = $item['create_time'];
				$data[$k]['context'] = $item['content'];
			}

			if($show == 0){
				$status = '1';
				echo Tjson($msg,$status,$state,$data);
			}else{
				$status = '查询成功';
				echo Txml($msg,$nu,$com,$status,$state,$data);
			}
			exit;
        }

	}

	//获取IL_state，判断state的值
	function get_state($nu){
		// 查询该单号的IL_state
		$sql      = "select IL_state from mk_tran_list where `MKNO` = '$nu' limit 1";
		$query    = mysql_query($sql);
		$stateArr = mysql_fetch_array($query);

		//state
		if ($stateArr['IL_state'] == 12) {
			$state = '1';
		}else if($stateArr['IL_state'] != 12 && $stateArr['IL_state'] < 1000){
			$state = '0';
		}else if($stateArr['IL_state'] >1000){
			$state = '-1000';
		}

		return $state;
	}


	function Tjson($msg,$status,$state,$data=array()){
		$backStr = array(
			'message' => $msg,
			'status' => $status,
			'state' => $state,
			'data' => $data,
		);
		return json_encode($backStr);
	}

	function Txml($msg,$nu,$com,$status,$state,$data_array=array()){
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
		return $xml;
	}
 
	//  创建XML单项
	function create_item($context, $time){
	    $item = "<data>\n";
	    $item .= "<context>" . $time . "</context>\n";
	    $item .= "<time>" . $context . "</time>\n";
	    $item .= "</data>\n";
	 
	    return $item;
	}