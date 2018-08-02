<?php
header('Content-type:text/html;charset=utf-8');

	//模拟返回的json数据
	$Gjson = '{"message":"","nu":"221014000103","companytype":"shentong","ischeck":"1","com":"shentong","updatetime":"2015-05-19 11:39:56","status":"200","condition":"F00","codenumber":"221014000103","data":[{"time":"2015-04-28 15:46:48","location":"","context":"已签收,签收人是前台签收","ftime":"2015-04-28 15:46:48"},{"time":"2015-04-28 08:47:46","location":"","context":"广东番禺大岗 的派件员 东涌点李文峰 正在派件","ftime":"2015-04-28 08:47:46"},{"time":"2015-04-28 06:34:51","location":"","context":"快件已到达广东番禺大岗","ftime":"2015-04-28 06:34:51"},{"time":"2015-04-27 07:36:52","location":"","context":"由广东番禺中转部 发往 广东番禺大岗","ftime":"2015-04-27 07:36:52"},{"time":"2015-04-27 07:20:41","location":"","context":"快件已到达广东番禺中转部","ftime":"2015-04-27 07:20:41"},{"time":"2015-04-27 03:59:37","location":"","context":"由广东广州公司 发往 广东番禺中转部","ftime":"2015-04-27 03:59:37"},{"time":"2015-04-26 22:10:47","location":"","context":"由广东深圳罗湖中转部 发往 广东广州中转部","ftime":"2015-04-26 22:10:47"},{"time":"2015-04-26 22:10:47","location":"","context":"广东深圳罗湖中转部 正在进行 装袋 扫描","ftime":"2015-04-26 22:10:47"},{"time":"2015-04-26 18:22:02","location":"","context":"广东深圳罗湖桑达分部 的收件员 李武已收件","ftime":"2015-04-26 18:22:02"}],"state":"3"}';
	$j = '{"status":"abort","message":"3天查询无记录","lastResult":'.$Gjson.'}';	//自定义json
	
	//如果是合法的json
	if(is_json($j)){
		$res = analyJson($j);	//解释json为数组形式
		TJson($res);	//处理方法
	}else{
		$arr = array(
				'result'=>'false',
				'returnCode'=>'500',
				'message'=>'失败',
			);
		echo json_encode($arr);
	}

	/**
	 * 快递100回调数据处理
	 * @param [type] $res [回调的json解释后的数组]
	 */
	function TJson($res){

		include 'config.php';

		$lastResult = $res['lastResult'];

		if($lastResult && count($lastResult['data']) > 0){	//如果$lastResult存在且$lastResult['data']不为空
			switch($res['status']){
				case 'polling':		//监控中
					get_data($lastResult,'');
				break;

				case 'updateall':		//重新推送
					get_data($lastResult,'');
				break;

				case 'shutdown':		//结束
					get_data($lastResult,'shutdown');
				break;

				case 'abort':		//中止
					get_data($lastResult,'abort');
				break;
			}
		}else if($lastResult && count($lastResult['data']) < 1){	//如果$lastResult存在且$lastResult['data']为空
			switch($res['status']){

				case 'shutdown':

					$nd = '订单：'.$lastResult['nu'].' 已完成';	//补填内容信息
					lose_data($res,$lastResult,'shutdown',$nd);

				break;

				case 'abort':

					if(strlen($res['message']) > 0){
						$nd = $res['message'];
					}else if(strlen($res['message']) < 1 && strlen($lastResult['message']) > 0){
						$nd = $lastResult['message'];
					}else if(strlen($res['message']) < 1 && strlen($lastResult['message']) < 1){
						$nd = '订单：'.$lastResult['nu'].' 疑难，待审核';
					}

					lose_data($res,$lastResult,'abort',$nd);
				break;
			}
		}else{
			echo $str_f;exit;
		}

	}

	/**
	 * $lastResult['data']不为空
	 * @param  [type] $lastResult [description]
	 * @param  [type] $status     [状态]
	 * @return [type]             [description]
	 */
	function get_data($lastResult,$status){
			include 'config.php';
			$data = array_reverse($lastResult['data']);		//将原数组中的元素顺序翻转，创建新的数组并返回
			// $data = array_reverse($data);
			// dump($data);die;
			$sql="select * from mk_stnolist where `STNO` = '$lastResult[nu]'"; //找到相对应的MKNO，
			
			$query=mysql_query($sql);

			if(mysql_num_rows($query) == '1'){
				$mk = mysql_fetch_assoc($query);	//查询结果转为数组

				$count = count($data);	//计算物流信息总数
				$rs = 0;
				mysql_query('START TRANSACTION');	//开启一个事务

				// 如果存在此MKNO，且此MKNO的状态为20
				if($mk['MKNO'] && $mk['status'] == '20'){
					foreach($data as $item){

						/*查询此行数据是否已经存入数据表*/
						if($status != 'abort'){
							// MKIL   先查询 mk_il_logs 是否已经存在某条数据
							$check_mkil="select * from mk_il_logs where `MKNO` = '$mk[MKNO]' and `content` = '$item[context]' and `create_time` = '$item[ftime]'";

							$res_mkil = mysql_query($check_mkil);// or die ("SQL: {$check_mkil}<br>Error:".mysql_error());

							//如果不存在此条信息，则保存
							if(mysql_num_rows($res_mkil) == '0'){
								// 将相关资料直接将记录增加到mk_il_log中
								$sql_mkil = "INSERT INTO mk_il_logs (MKNO,content,create_time,status) VALUES ('$mk[MKNO]', '$item[context]', '$item[ftime]', '')";

								$res1 = mysql_query($sql_mkil);
							}
						}

						// ERP 先查询 MIS_mk_logs 是否已经存在某条数据
						$check_erp="select * from MIS_mk_logs where `MKNO` = '$mk[MKNO]' and `context` = '$item[context]' and `ftime` = '$item[ftime]'";

						$res_erp = mysql_query($check_erp);

						//如果不存在此条信息，则保存
						if(mysql_num_rows($res_erp) == '0'){
							$sql_erp = "INSERT INTO MIS_mk_logs (MKNO,context,ftime,status) VALUES ('$mk[MKNO]', '$item[context]', '$item[ftime]', '$lastResult[state]')";

							$res2 = mysql_query($sql_erp);
						}
						/* End */

						if($status != 'abort'){		//即$status = polling/shutdown/updateall
							//如果数据已经存在,则不保存也 不更新该条信息到数据表
							if(mysql_num_rows($res_mkil) == '1' && mysql_num_rows($res_erp) == '1'){
								$rs++;
							//如果数据插入成功
							}else if($res1 && $res2){
								$rs++;
							}
						}else{	//即$status = abort
							//如果数据已经存在,则不保存也 不更新该条信息到数据表
							if(mysql_num_rows($res_erp) == '1'){
								$rs++;
							//如果数据插入成功
							}else if($res2){
								$rs++;
							}
						}


					}
				}else{
					return false;
				}

				//数据保存正常且完整正确是，返回成功给 快递100 ，以停止继续接收该相关信息
				if($count == $rs){

					mysql_query("COMMIT");		//事务确认

					UpState($status,$mk);	//更新mk_logs中的相应记录state

					echo $str_s;exit;
						
				}else{
					mysql_query("ROLLBACK");	//事务回滚
					echo $str_f;exit;
				}
			}else{
				echo '没有与此对应的美快单号';
			}
	}

	/**
	 * $lastResult['data']为空
	 * @param  [type] $res        [description]
	 * @param  [type] $lastResult [description]
	 * @param  [type] $status     [状态]
	 * @return [type]             [description]
	 */
	function lose_data($res,$lastResult,$status,$nd){
		include 'config.php';

		$sql="select * from mk_stnolist where `STNO` = '$lastResult[nu]'"; //找到相对应的MKNO，
		
		$query=mysql_query($sql);

		if(mysql_num_rows($query) == '1'){
			
			$mk = mysql_fetch_assoc($query);			//查询结果转为数组
			$tt = trim($lastResult['updatetime']);		//补填时间
			$rs = 0;
			mysql_query('START TRANSACTION');	//开启一个事务
			
			// 如果存在此MKNO，且此MKNO的状态为20
			if($mk['MKNO'] && $mk['status'] == '20'){

				/*查询此行数据是否已经存入数据表*/
				if($status != 'abort'){
					// MKIL   先查询是否已经存在某条数据
					$check_mkil="select * from mk_il_logs where `MKNO` = '$mk[MKNO]' and `create_time` = '$tt'";

					$res_mkil = mysql_query($check_mkil);

					//如果不存在此条信息，则保存
					if(mysql_num_rows($res_mkil) == '0'){
						// 将相关资料直接将记录增加到mk_il_log中
						$sql_mkil = "INSERT INTO mk_il_logs (MKNO,content,create_time,status) VALUES ('$mk[MKNO]', '$nd', '$tt', '')";

						$res1 = mysql_query($sql_mkil);
					}
				}

				// ERP 先查询是否已经存在某条数据
				$check_erp="select * from MIS_mk_logs where `MKNO` = '$mk[MKNO]' and `ftime` = '$tt'";

				$res_erp = mysql_query($check_erp);

				//如果不存在此条信息，则保存
				if(mysql_num_rows($res_erp) == '0'){
					$sql_erp = "INSERT INTO MIS_mk_logs (MKNO,context,ftime,status) VALUES ('$mk[MKNO]', '$nd', '$tt', '$lastResult[state]')";

					$res2 = mysql_query($sql_erp);
				}
				/* End */

				if($status != 'abort'){		//即$status = shutdown
					//如果数据已经存在,则不保存也 不更新该条信息到数据表
					if(mysql_num_rows($res_mkil) == '1' && mysql_num_rows($res_erp) == '1'){
						$rs = 1;
					//如果数据插入成功
					}else if($res1 && $res2){
						$rs = 1;
					}
				}else{	//即$status = abort
					//如果数据已经存在,则不保存也 不更新该条信息到数据表
					if(mysql_num_rows($res_erp) == '1'){
						$rs = 1;
					//如果数据插入成功
					}else if($res2){
						$rs = 1;
					}
				}
			}else{
				return fasle;
			}

			//数据保存正常且完整正确时，返回成功给 快递100 ，以停止继续接收该相关信息
			if($rs == '1'){

				mysql_query("COMMIT");		//事务确认

				UpState($status,$mk);	//更新mk_logs中的相应记录state

				echo $str_s;exit;
					
			}else{
				mysql_query("ROLLBACK");	//事务回滚
				echo $str_f;exit;
			}

		}else{
			echo '没有与此对应的美快单号';
		}
	}

	/**
	 * 更新mk_logs中的相应记录state
	 * @param [type] $status [状态]
	 * @param [type] $mk     [美快单号信息]
	 */
	function UpState($status,$mk){
		//若$status = 'shutdown'，将mk_logs中的相应记录state更改为400 (前提为state=200)
		if($status == 'shutdown'){
			$sql="select * from mk_logs where `MKNO` = $mk[MKNO]"; //找到相对应的MKNO，
			$query=mysql_query($sql);
			$it = mysql_fetch_assoc($query);
			if($it['state'] == '200'){
				$sql = "update mk_logs set `state` = '400' where `id` = '$it[id]'";
				mysql_query($sql);
			}
		}

		//若$status = 'abort'，将mk_logs中的相应记录state更改为404 (前提为state=200)
		if($status == 'abort'){
			$sql="select * from mk_logs where `MKNO` = $mk[MKNO]"; //找到相对应的MKNO，
			$query=mysql_query($sql);
			$it = mysql_fetch_assoc($query);
			if($it['state'] == '200'){
				$sql = "update mk_logs set `state` = '404' where `id` = '$it[id]'";
				mysql_query($sql);
			}
		}
	}
//=============================== 检验用 ==============================================

	/*
	 * function:二维数组按指定的键值排序
	 */
	// function array_sort($arr,$keys,$type='asc'){
	// 	$keysvalue = $new_array = array();
	// 	foreach ($arr as $k=>$v){
	// 		$keysvalue[$k] = $v[$keys];
	// 	}
	// 	if($type == 'asc'){
	// 		asort($keysvalue);
	// 	}else{
	// 		arsort($keysvalue);
	// 	}
	// 	reset($keysvalue);
	// 	foreach ($keysvalue as $k=>$v){
	// 		$new_array[] = $arr[$k];//$new_array[$k] = $arr[$k];加$k下标就是原来的，不加下标就重新排了；
	// 	}
	// 	return $new_array;
	// }

	//判断数据是合法的json数据:
	function is_json($string) {
		json_decode($string);
		return (json_last_error() == JSON_ERROR_NONE);	//json_last_error()函数返回数据编解码过程中发生的错误
	}

	/**
	* 解析json串
	* @param type $json_str
	* @return type
	*/
	function analyJson($json_str) {
		$json_str = str_replace('\\', '', $json_str);
		$out_arr = array();
		preg_match('/{.*}/', $json_str, $out_arr);
		if (!empty($out_arr)) {
			$result = json_decode($out_arr[0], TRUE);
		} else {
			return FALSE;
		}

		return $result;
	}

	//测试用打印输出
	function dump($data){
		echo '<pre>';
		print_r($data);
		echo '</pre>';
		return $data;
	}

?>