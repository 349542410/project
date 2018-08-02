<?php
	// include('r_config.php');
	include('r2_config.php');
	include('r_function.php');
	include('function.php');
	require_once('./ex_config.php');
	require_once("./../../hprose_php5/HproseHttpClient.php");
	header('Content-type:text/json;charset=UTF-8');	//设置输出格式

	/* Jie 20160921 */
	// 线程名称
	$node = isset($_GET['node']) ? $_GET['node'] : 1;
	// echo $node;die;
	//主动获取顺丰的物流信息
	$time = time();	//标识码(时间戳) 访问此文件的时候马上生成

	// 查询倒序后的最新的一条数据
	$maxlid_sql = "select id,lid,ctime,state from mk_tran_list_notes where node = '$node' order by id desc limit 1";
	$max = $pdo->query($maxlid_sql);

	$maxinfo = $max->fetch(PDO::FETCH_ASSOC);
	// print_r($maxinfo);

	$maxinfo['ctime'] = ($maxinfo['ctime'] == '') ? 0 : $maxinfo['ctime'];

	$tenHour = $time - $maxinfo['ctime'];	//当前时间的10小时前
	// print_r($tenHour);die;

	if($tenHour < $limitHour){
		if($maxinfo != ''){
			$ctime = $maxinfo['ctime'];
		}else{
			$ctime = $time;
		}
	}else{
		$ctime = $time;
	}

	// print_r($ctime);

	// 超过规定时限(10小时) 或者mk_tran_list_notes.max(id).state = 200，则会重新执行物流信息查询
	if($tenHour > $limitHour || $maxinfo['state'] == '200'){
		if($maxinfo == ''){
			$maxlid = $maxinfo['lid'];
		}else{
			$maxlid = 0;
		}
	}else{
		if($maxinfo == ''){
			$maxlid = 0;
		}else{
			$maxlid = $maxinfo['lid'];
		}
	}
		
	// echo '    ';
	// print_r($maxlid);
	// die;
	/* End 20160921 */

	//查tran_list.trankd=? IL_State<>1003的 MK单进行物流信息获取
	$find_sql = "SELECT $no,id FROM mk_tran_list WHERE IL_state <> '1003' AND trankd = '$TranKd' AND id > '$maxlid' AND $no <> '' ORDER BY id ASC LIMIT $limit";
	// echo $find_sql;die;
	$find = $pdo->query($find_sql);

	if($find->rowCount() > 0){

		$nu_list = $find->fetchAll(PDO::FETCH_ASSOC);
		// print_r($nu_list);die;
		// echo '<pre>';
		$maxId = $nu_list[count($nu_list)-1]['id'];
		// print_r($maxId);

		/* Jie 20160921 */
		$note_sql = "INSERT INTO mk_tran_list_notes (lid,ctime,node) VALUES ('$maxId', '$ctime','$node')";
		// echo $note_sql;die;
		$pdo->query($note_sql);
		/* End 20160921 */

		//筛选出运单号
		// $arr = array();
		// foreach($list as $item){
		// 	$arr[] = $item[$no];
		// }
//============= 测试   ========
		// print_r($arr);die;
		// $arr =  array('0'=>'080000820051');//测试用
		// 
		// $arr =  array('0'=>'080000819333','1'=>'080000819402','2'=>'080000819306');
		// print_r($arr);die;
//============= 测试 End  ======== 
		// $tracking_number = implode(',', $arr);

		// $nu_list =  array('0'=>array('STNO'=>'080000820015'));
		$nu_list =  array('0'=>array('STNO'=>'080000819360'),'1'=>array('STNO'=>'080000819290'),'2'=>array('STNO'=>'080000819439'));

		$et = 0;//计算成功获取物流信息的总数
		$msg = '';
		foreach($nu_list as $kk=>$item){
			// print_r($item[$no]);die;
			$tracking_number = $item[$no];
			//生成xml报文
			$cXml = createXML($customerCode, $tracking_type, $tracking_number, $lang);
			// print_r($cXml);

			$data = base64_encode($cXml);//xml报文加密

			// $validateStr = base64_encode(md5($cXml.$checkword, false));
			$validateStr = base64_encode(md5(utf8_encode($cXml).$checkword, false));

			$client = new SoapClient ($pmsLoginAction);
			$result = $client->sfexpressService(array('data'=>$data,'validateStr'=>$validateStr,'customerCode'=>$customerCode));//查询，返回的是一个结构体
			
			print_r($result);
			$reArr = get_object_vars($result);
			// print_r($reArr);

			// 把顺丰发过来的xml报文中包含的&进行转义
			$reArr['Return'] = str_replace("&", "&amp;", $reArr['Return']);
			// print_r($reArr);
			// die;
			$reArr = xml_array($reArr);// 返回的XML报文转为数组

			$reArr = $reArr['Return'];
			// print_r($reArr);
			// die;

			if(isset($reArr['Head']) && $reArr['Head'] == 'OK'){

				$WaybillRoute = $reArr['Body']['RouteResponse'];
				$Route = $reArr['Body']['RouteResponse']['Route'];

				/* 20160929 当物流状态出现8000的时候，清除之后(包括8000)的物流信息 
				 * 本人签收的时候，快递状态为80之后会出现opcode=8000; 当快递被代收的之后也是出现opcode=8000
				*/
				$Route  = array_reverse($Route);//返回翻转顺序的数组
				$st = '';
				foreach($Route as $ki => $rt){
					if($rt['@attributes']['opcode'] == '8000'){
						$st = $ki;
					}

					if($st != ''){
						unset($Route[$ki]);
					}
				}
				$Route  = array_reverse($Route);//返回翻转顺序的数组
				/* End */

				$new_a = array();

				// 只执行一条物流数据查询的时候
				if(count($Route) == 1){

					$list = $Route['@attributes'];

					$new_a[0]['BusinessLinkCode'] = MKIL_State($list['opcode']);
					$new_a[0]['TrackingContent']  = "【".$list['accept_address']."】".$list['remark'];
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

				}else{//以下内容为传递多条物流数据的时候

					$list = $Route;

					//物流信息数组三维转二维数组
					foreach($list as $key => $row){
					    foreach($row as $key2 => $row2){
					    	// print_r($row2['opcode']);
					        // $new_a[$key] = $row2;
					        if(isset($row2['remark'])){
								$new_a[$key]['BusinessLinkCode'] = MKIL_State($row2['opcode']);
								$new_a[$key]['TrackingContent']  = "【".$row2['accept_address']."】".$row2['remark'];
								$new_a[$key]['OccurDatetime']    = $row2['accept_time'];
					        }

					    }
					}

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

				}

				// print_r($arr);die;
				$client = new HproseHttpClient($serurl);

			    $res = $client->save($arr, 'STNO', 'SF');	//直接传入数组形式的数据
				// echo '<pre>';
				// print_r($res);
				// echo '</pre>';
				// die;
			    
				if($res['do'] == 'yes'){
					$et++;
					// $backXML = '<Response service="RoutePushService"><Head>OK</Head></Response>';
				}else{
					$msg .= '单号：'.$item[$no].'，msg：'.$res['title'].'；';
					// $backXML = '<Response service="RoutePushService"><Head>ERR</Head><ERROR code="4001">系统发生数据错误或运行时异常</ERROR></Response>';
				}

				// echo $backXML;


			}
		}

		$endtime = time();
		$Ttime = $endtime - $time;
		if($et == 0){
			$backXML = '<Response service="RoutePushService"><Head>ERR</Head><ERROR code="4001">系统发生数据错误或运行时异常；耗时：'.$Ttime.'秒；反馈信息：'.$msg.'</ERROR></Response>';
			
		}else{
			$backXML = '<Response service="RoutePushService"><Head>OK</Head>请求发送总数：'.$limit.'个，实际查询数据：'.count($nu_list).'个；成功获取物流信息：'.$et.'个；耗时：'.$Ttime.'秒</Response>';
		}
		echo $backXML;
	}else{//当搜索数据表已经没有得到合适数据的时候，就把最大的id的状态标记为200
		/* Jie 20160921 */
		$note_sql = "UPDATE mk_tran_list_notes SET state='200' WHERE id = '$maxinfo[id]'";
		// echo $note_sql;die;
		$pdo->query($note_sql);
		/* End 20160921 */
	}