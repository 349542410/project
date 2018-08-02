<?php
	include('config.php');
	include('function.php');
	
	//检测mk_tran_share 中是否有操作记录，即最大的readid值
	$max = "select max(readid) as rid from mk_tran_share"; //取最大的id
	$max_res = $pdo->query($max);
	$maxreadid = $max_res->fetchAll(PDO::FETCH_ASSOC);
	// dump($res_state[0]['IL_state']);die;
	$maxrid = $maxreadid[0]['rid'];	//最后执行发送的mk_il_logs.id的值

	$ssql = "select id,MKNO,callback,status,readid,readtime from mk_tran_share where `status` < 10 order by `cretime` asc limit $limit";
	// echo $ssql;
	$search = $pdo->prepare($ssql);
	$search->execute();
	$sear_count = $search->rowCount();
	// echo $sear_count;
	if($sear_count > 0){
		foreach($search as $k=>$item){

			// 如果没有1003，只要time()-.readtime>30天的 => tran_share.status=14 ,$watchStatus='abort'
			if((time() - $item['readtime']) > 2592000){
				$watchStatus = 'abort';
				// $status = 14;
			}else{//搜索其中一条记录的 il_logs.stauts=1003 => tran_share.status=10,$watchStatus(返回时使用)='stop'
				$checkSql = "select * from mk_il_logs where `MKNO` = '$item[MKNO]' AND `status` = '1003' AND `id` > $maxrid  order by `create_time` desc";

				$check = $pdo->prepare($checkSql);
				$check->execute();
				$check_count = $check->rowCount();
				//只要其中一条记录的 il_logs.stauts=1003
				if($check_count > 0){
					$watchStatus = 'stop';
					$status = 10;
				}
			}

			$operation = $item['readid'] > 0 ? 'append' : 'override';

			//status=0 需发送该MKNO的所有物流信息
			if($item['status'] == 0){

				$sql = "select * from mk_il_logs where `MKNO` = '$item[MKNO]' order by `id` asc";

			}else if($item['status'] == 1){//status=1 只发送最新的物流信息(按readid区分，即从大于mk_tran_share.max(readid)开始)

				$sql = "select * from mk_il_logs where `MKNO` = '$item[MKNO]' AND `id` > $maxrid order by `create_time` desc limit 1";

			}

			$detail = array();
			$One = $pdo->prepare($sql);
			$One->execute();
			if($One->rowCount() > 0){
				foreach($One as $key=>$son){
					$detail[$key]['id']       = $son['id'];
					$detail[$key]['context']  = $son['content'];
					$detail[$key]['time']     = $son['create_time'];
					$detail[$key]['location'] = '';
				}

				//取该单的所有物流信息中最大的id值即就是最新的物流信息状态
				$sta_sql = "select status From mk_il_logs where id = (select max(id) from mk_il_logs where `MKNO` = '$item[MKNO]')";
				// dump($sta_sql);

				$sta_res = $pdo->query($sta_sql);
				$staArr = $sta_res->fetchAll(PDO::FETCH_ASSOC);
				// dump($res_state[0]['IL_state']);die;
				$sta = $staArr[0]['status'];	//最后执行发送的mk_il_logs.id的值
				$state = tran_state($sta);

				$arr = array(
					'watchStatus' => $watchStatus,
					'operation'   => $operation,
					'status'      => $state,//取il_logs.status按api的计算方式
					'company'     => $mgcom,
					'code'        => $item['MKNO'],
					'callback'    => $item['callback'],
					'detail'      => $detail,
				);
				// dump($arr);
				$param = json_encode($arr);
				// dump($param);

				$post_data = "param=".$param."&sign=".md5($param.$key)."&company=".$mgcom;	//组合
				
				//执行发送json
				$res = curl_post($send_url, $post_data);

				$basckArr = json_decode($res,true);

				if($basckArr['returnCode'] == 300){

					$Strsql = "UPDATE mk_tran_share SET status=14 WHERE id='$item[id]' LIMIT 1";

				}else if($basckArr['returnCode'] == 400){

					$Strsql = "UPDATE mk_tran_share SET status=0, readid=0, readtime=0 WHERE id='$item[id]' LIMIT 1";

				}else if($basckArr['returnCode'] == 200){

					$time   = time();
					$status = isset($status) ? $status : 1 ;
					$Strsql = "UPDATE mk_tran_share SET status=$status, readid='$son[id]', readtime='$time' WHERE id='$item[id]' LIMIT 1";
				}
				// dump($Strsql);
				$pdo->exec($Strsql);	//执行sql
			
			}else{//如果没有相关的物流信息则退出当前这个，执行下一个
				// echo "none";
				continue;
			}

		}


    }else{//查询结果0，没有任何数据需要发送
    	echo 'none';
    }