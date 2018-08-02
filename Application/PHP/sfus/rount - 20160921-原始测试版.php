<?php
	// include('r_config.php');
	include('r2_config.php');
	include('r_function.php');
	include('function.php');
	require_once('./ex_config.php');
	require_once("./../../hprose_php5/HproseHttpClient.php");
	header('Content-type:text/json;charset=UTF-8');	//设置输出格式

	//主动获取顺丰的物流信息
	//查tran_list.trankd=? IL_State<>1003的 MK单进行物流信息获取
	$find_sql = "SELECT $no FROM mk_tran_list WHERE IL_state <> '1003' AND trankd = '$TranKd' ORDER BY id ASC LIMIT $limit";
	// echo $find_sql;die;
	$find = $pdo->query($find_sql);
	if($find->rowCount() > 0){

		$list = $find->fetchAll(PDO::FETCH_ASSOC);

		// echo '<pre>';

		$arr = array();
		foreach($list as $item){
			$arr[] = $item[$no];
		}
		$arr =  array('0'=>'070023478143');
		// print_r($arr);die;
		$tracking_number = implode(',', $arr);

		//生成xml报文
		$cXml = createXML($customerCode, $tracking_type, $tracking_number, $lang);
		// print_r($cXml);

		$data = base64_encode($cXml);//xml报文加密

		$validateStr = base64_encode(md5($cXml.$checkword, false));
		// $validateStr = base64_encode(md5(utf8_encode($cXml).$checkword, false));

		$client = new \SoapClient ($pmsLoginAction);
		$result = $client->sfexpressService(array('data'=>$data,'validateStr'=>$validateStr,'customerCode'=>$customerCode));//查询，返回的是一个结构体
		
		// print_r($result);
		// die;
		$reArr = xml_array($result);// 返回的XML报文转为数组
		// print_r($reArr);die;
		$reArr = $reArr['Return'];
		// $reArr = object_array($reArr['Return']);// 返回的XML报文转为数组
		print_r($reArr);
		// die;

		if($reArr['Head'] == 'OK'){

			$WaybillRoute = $reArr['Body']['RouteResponse'];
			$Route = $reArr['Body']['RouteResponse']['Route'];

			$new_a = array();

			// 只执行一条数据查询的时候
			if(count($Route) == 1){

				$list = $Route['@attributes'];

				$new_a[0]['BusinessLinkCode'] = MKIL_State($list['opcode']);
				$new_a[0]['TrackingContent']  = $list['remark'];
				$new_a[0]['OccurDatetime']    = $list['accept_time'];
				// print_r($list);

				//将loginfo生成 ./Express100/server.php 可接受的格式，使用 HproseHttp进行保存
				$arr = array();
				$arr['status']                  = '';
				$arr['billstatus']              = 'check';
				$arr['message']                 = '';
				$arr['lastResult']['message']   = 'ok';
				$arr['lastResult']['nu']        = $WaybillRoute['@attributes']['mailno'];	//tran_list.STNO
				$arr['lastResult']['ischeck']   = '1';
				$arr['lastResult']['condition'] = '';
				$arr['lastResult']['com']       = '';
				$arr['lastResult']['status']    = '';
				$arr['lastResult']['state']     = '';
				$arr['lastResult']['data']      = $new_a;
				// var_dump($arr);

			}else{//以下内容为传递多个单号的时候使用，暂不可使用 20160830 Jie

				$list = $WaybillRoute;

				//物流信息数组三维转二维数组
				foreach($list as $key => $row){
				    foreach($row as $key2 => $row2){
				        $new_a[$key] = $row2;
				        $new_a[$key]['BusinessLinkCode'] = MKIL_State($row2['opCode']);
				        $new_a[$key]['TrackingContent'] = $row2['remark'];
				        $new_a[$key]['OccurDatetime'] = $row2['acceptTime'];

				    }
				}

				// print_r($new_a);

				//将loginfo生成 ./Express100/server.php 可接受的格式，使用 HproseHttp进行保存
				$arr = array();
				$arr['status']                  = '';
				$arr['billstatus']              = 'check';
				$arr['message']                 = '';
				$arr['lastResult']['message']   = 'ok';
				$arr['lastResult']['nu']        = $list[0]['@attributes']['mailno'];	//tran_list.STNO
				$arr['lastResult']['ischeck']   = '1';
				$arr['lastResult']['condition'] = '';
				$arr['lastResult']['com']       = '';
				$arr['lastResult']['status']    = '';
				$arr['lastResult']['state']     = '';
				$arr['lastResult']['data']      = $new_a;

			}

			// print_r($arr);die;
			$client = new HproseHttpClient($serurl);

		    $res = $client->save($arr, 'STNO');	//直接传入数组形式的数据
			// echo '<pre>';
			// print_r($res);
			// echo '</pre>';

			if($res['do'] == 'yes'){
				$backXML = '<Response service="RoutePushService"><Head>OK</Head></Response>';
			}else{
				$backXML = '<Response service="RoutePushService"><Head>ERR</Head><ERROR code="4001">系统发生数据错误或运行时异常</ERROR></Response>';
			}

			echo $backXML;



		}
	}