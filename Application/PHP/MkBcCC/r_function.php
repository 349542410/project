<?php
	/**
	 * 文件中文名： 把xml转数组，并把顺丰物流信息作一定的处理后再执行物流的数据保存
	 * 20161102 Jie
	 * 处理：接收返回的xml之后，处理成数组形式作返回
	 * @param  [type] $tracking_number [运单号]
	 * @param  [type] $debug           [调试编码，不同数值可用于检查不同位置的数据输出]
	 * @return [type]                  [description]
	 */
	function getArr($tracking_number, $debug=0){

		require_once('getXML.php');//发送请求获取顺丰物流xml报文
		require_once('function.php');//公用函数
		require('ex_config.php');//远程地址
		require("./../../hprose_php5/HproseHttpClient.php");
		header('Content-type:text/html;charset=UTF-8');	//设置输出格式

		// 20161103 Jie
		$result = get($tracking_number, $debug);//$debug  调试编码，不同数值可用于检查不同位置的数据输出

/*		$cXml = createXML($customerCode, $tracking_type, $tracking_number, $lang);
		$data = base64_encode($cXml);//xml报文加密

		$validateStr = base64_encode(md5(utf8_encode($cXml).$checkword, false));

		$client = new SoapClient ($pmsLoginAction);
		
		$result = $client->sfexpressService(array('data'=>$data,'validateStr'=>$validateStr,'customerCode'=>$customerCode));//查询，返回的是一个结构体*/

		// return $result;
		$reArr = get_object_vars($result);
		// return $reArr;
		// 把顺丰发过来的xml报文中包含的&进行转义
		$reArr['Return'] = str_replace("&", "&amp;", $reArr['Return']);

		// 调试编码
		if($debug == 2){
			echo "过滤xml报文中包含的&：";
			print_r($reArr);die;
		}

		$reArr = xml_array($reArr);// 返回的XML报文转为数组
		// 调试编码
		if($debug == 5){
			echo "经过function.php处理完后：";
			print_r($reArr);die;
		}
		$reArr = $reArr['Return'];

		//=================//
		if(isset($reArr['Head']) && $reArr['Head'] == 'OK'){

			$WaybillRoute = $reArr['Body']['RouteResponse'];
			$Route = $reArr['Body']['RouteResponse']['Route'];

			$new_a = array();

			// 只执行一条物流数据查询的时候
			if(count($Route) == 1){

				$list = $Route['@attributes'];

				$new_a[0]['BusinessLinkCode'] = MKIL_State($list['opcode']);
				$new_a[0]['TrackingContent']  = "【".$list['accept_address']."】".$list['remark'];
				$new_a[0]['OccurDatetime']    = $list['accept_time'];

			}else{//以下内容为传递多条物流数据的时候
				
				$Route = array_reverse($Route);//返回翻转顺序的数组
				$list  = $Route;
				$ru    = false;//判断用
				//物流信息数组三维转二维数组
				foreach($list as $key => $row){

				    foreach($row as $key2 => $row2){

				        if(isset($row2['remark'])){

							if($row2['opcode'] == '80'){
								$new_a[$key]['BusinessLinkCode'] = MKIL_State($row2['opcode']);
								$new_a[$key]['TrackingContent']  = "【".$row2['accept_address']."】".$row2['remark'];
								$new_a[$key]['OccurDatetime']    = $row2['accept_time'];

								// 额外保存本公司专有信息
								$new_a[$key+1]['BusinessLinkCode'] = '1003';

								//Man161020
								//$new_a[$key+1]['TrackingContent']  = "【".$row2['accept_address']."】已签收,感谢使用美快国际物流,期待再次为您服务";
								$new_a[$key+1]['TrackingContent']  = "感谢使用美快国际物流，期待再次为您服务！";

								$new_a[$key+1]['OccurDatetime']    = date('Y-m-d H:i:s');
								$ru = true;
							}
							if($row2['opcode'] == '8000'){
								if($ru === true){
									break;
								}else{
									$new_a[$key]['BusinessLinkCode'] = '1003';
									$new_a[$key]['TrackingContent']  = "【".$row2['accept_address']."】已签收,感谢使用美快国际物流,期待再次为您服务";
									$new_a[$key]['OccurDatetime']    = $row2['accept_time'];
								}
							}else{
								$new_a[$key]['BusinessLinkCode'] = MKIL_State($row2['opcode']);
								$new_a[$key]['TrackingContent']  = "【".$row2['accept_address']."】".$row2['remark'];
								$new_a[$key]['OccurDatetime']    = $row2['accept_time'];
							}

				        }

				    }
				}
				$new_a = array_reverse($new_a);//返回翻转顺序的数组
			}

			// 调试编码
			if($debug == 3){
				echo "顺丰物流信息数组处理后：";
				print_r($new_a);die;
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

			// 调试编码
			if($debug == 4){
				echo "组成数组后：";
				print_r($arr);die;
			}

			$client = new HproseHttpClient($serurl);

		    $res = $client->save($arr, 'STNO', 'SF');	//直接传入数组形式的数据

		    return $res;

		}else{
			return array('do'=>'no', 'title'=>$reArr['ERROR']);      //返回信息
		}

	}