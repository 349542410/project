<?php
	/**
	 * 文件中文名： 调用香港E特快的类，获取到的物流信息作一定的处理后再执行物流的数据保存
	 * 2017-01-10 Jie
	 * 处理：接收返回的xml之后，处理成数组形式作返回
	 * @param  [type] $tracking_number [运单号]
	 * @param  [type] $debug           [调试编码，不同数值可用于检查不同位置的数据输出]
	 * @param  [type] $EMS             [调用类---香港E特快的]
	 * @param  [type] $serurl          [用于保存物流信息的地址]
	 * @param  [type] $config          [香港E特快的必需的配置信息]
	 * @return [type]                  [description]
	 */
	function getArr($tracking_number, $debug=0, $EMS, $serurl, $config, $type=''){
		require("./../../hprose_php5/HproseHttpClient.php");
		// echo '<pre>';
		$result = $EMS->trackOrder($tracking_number, $config);
		$j_arr = json_decode($result, true);
		
		if($type != ''){
			return $j_arr;exit;
		}
		// print_r($j_arr);die;

		if($j_arr['Status'] == 'success'){
			// print_r($j_arr);die;

			$reArr = $j_arr['Result'][0];

			// 第一步筛选 是否有物流信息更新
			if(!isset($reArr['CurrentStatusCode']) || !isset($reArr['DestTraces'])){
				return array('do'=>'no', 'title'=>'该运单号尚未更新物流信息');      //返回信息
			}

			// 第二步筛选 是否有物流信息更新
			if(count($reArr['DestTraces']) == 0){
				return array('do'=>'no', 'title'=>'暂无物流信息更新');      //返回信息
			}
			
			$IL_state = MKIL_State($reArr['CurrentStatusCode']);
			// print_r($IL_state);
			$list = $reArr['DestTraces'];
			// print_r($list);

			$new_a = array();
			//将香港E特快的物流信息转化为美快物流信息
			foreach($list as $key => $row){

				if($reArr['CurrentStatusCode'] == 'delivered'){
					$new_a[$key]['BusinessLinkCode'] = $IL_state;
					$new_a[$key]['TrackingContent']  = "【".$row['Location']."】".$row['Action'];
					$new_a[$key]['OccurDatetime']    = $row['DealTime'];

					// 额外保存本公司专有信息
					$new_a[$key+1]['BusinessLinkCode'] = '1003';

					//Man161020
					//$new_a[$key+1]['TrackingContent']  = "【".$row2['accept_address']."】已签收,感谢使用美快国际物流,期待再次为您服务";
					$new_a[$key+1]['TrackingContent']  = "感谢使用美快国际物流，期待再次为您服务！";

					$new_a[$key+1]['OccurDatetime']    = date('Y-m-d H:i:s');
				}else{
					$new_a[$key]['BusinessLinkCode'] = $IL_state;
					$new_a[$key]['TrackingContent']  = "【".$row['Location']."】".$row['Action'];
					$new_a[$key]['OccurDatetime']    = $row['DealTime'];
				}

			}
			$new_a = array_reverse($new_a);//返回翻转顺序的数组
			// print_r($new_a);

			//将loginfo生成 ./Express100/common_server.php 可接受的格式，使用 HproseHttp进行保存
			$arr = array();
			$arr['status']                  = '';
			$arr['billstatus']              = 'check';
			$arr['message']                 = '';
			$arr['lastResult']['message']   = 'ok';
			$arr['lastResult']['nu']        = $reArr['TrackingNo'];	//tran_list.STNO
			$arr['lastResult']['ischeck']   = '1';
			$arr['lastResult']['condition'] = '';
			$arr['lastResult']['com']       = '';
			$arr['lastResult']['status']    = '';
			$arr['lastResult']['state']     = '';
			$arr['lastResult']['data']      = $new_a;
// print_r($arr);die;
			$client = new HproseHttpClient($serurl);
		    $res = $client->save($arr, 'STNO', 'EMS');	//直接传入数组形式的数据
// print_r($res);die;
		    return $res;

		}else{

			$msg = (trim($j_arr['ErrorMessage']) != '') ? $j_arr['ErrorMessage'] : '获取信息失败';
			return array('do'=>'no', 'title'=>$msg);      //返回信息
		}

	}

	// 根据香港E特快的物流状态转换成美快物流状态
	function MKIL_State($str){
		if($str == 'despatch'){

			$IL_state = 1005;	//快递派件

		}else if($str == 'delivered'){

			$IL_state = 1003;	//快递签收

		}else if($str == 'submitted'){

			$IL_state = 1001;	//快递揽件

		}else if($str == 'returned'){

			$IL_state = 1002;	//快递疑难

		}else if(in_array($str, array('created','exported','inscanned','clearance','intransit'))){

			$IL_state = 1000;	//在途

		}else{
			$IL_state = 1000;	//在途
		}

		return $IL_state;
	}