<?php

	//获取得到 物流信息 后的处理方法
	function getArr($tracking_number, $serurl, $bc2, $part=false){
		require("./../../hprose_php5/HproseHttpClient.php");

		$res = $bc2->transit($tracking_number, $part);

		$res = object_array($res); //对象转换数组
// echo '<pre>';
// var_dump($res);die;
		//完整的物流跟踪信息
		if($part == false){
			$list = (isset($res['out']['Mail'])) ? $res['out']['Mail'] : array();

		}else{//最新的一条物流信息

			$list = (isset($res['out'])) ? $res['out'] : array();
		}

		//有物流信息返回
		if(count($list) > 0){

			$state    = $list[count($list)-1]['actionInfoOut'];
			$mailCode = $list[count($list)-1]['mailCode'];

			// print_r($state);
			$IL_state = MKIL_State($state);
			// echo $IL_state;

			$new_a = array();
			//将香港E特快的物流信息转化为美快物流信息
			foreach($list as $key => $row){

				if($IL_state == '1003'){
					$new_a[$key]['BusinessLinkCode'] = $IL_state;
					$new_a[$key]['TrackingContent']  = $row['relationOfficeDesc'];
					$new_a[$key]['OccurDatetime']    = trans_time($row['actionDateTime']);

					// 额外保存本公司专有信息
					$new_a[$key+1]['BusinessLinkCode'] = '1003';
					$new_a[$key+1]['TrackingContent']  = "感谢使用美快国际物流，期待再次为您服务！";
					$new_a[$key+1]['OccurDatetime']    = date('Y-m-d H:i:s');
				}else{
					$new_a[$key]['BusinessLinkCode'] = $IL_state;
					$new_a[$key]['TrackingContent']  = $row['relationOfficeDesc'];
					$new_a[$key]['OccurDatetime']    = trans_time($row['actionDateTime']);
				}

			}

			// print_r($new_a);die;

			$new_a = array_reverse($new_a);//返回翻转顺序的数组

			//将loginfo生成 ./Express100/common_server.php 可接受的格式，使用 HproseHttp进行保存
			$arr = array();
			$arr['status']                  = '';
			$arr['billstatus']              = 'check';
			$arr['message']                 = '';
			$arr['lastResult']['message']   = 'ok';
			$arr['lastResult']['nu']        = $mailCode;	//tran_list.STNO
			$arr['lastResult']['ischeck']   = '1';
			$arr['lastResult']['condition'] = '';
			$arr['lastResult']['com']       = '';
			$arr['lastResult']['status']    = '';
			$arr['lastResult']['state']     = '';
			$arr['lastResult']['data']      = $new_a;

			// print_r($arr);die;
			$client = new HproseHttpClient($serurl);
		    $res = $client->save($arr, 'STNO', 'MkBc2');	//直接传入数组形式的数据
		    return $res;
		    
		}else{
			return $backArr = array('do'=>'no', 'title'=>'该运单号尚未更新物流信息');      //返回信息
		}


	}

	// 根据 美快BC优选2 的物流状态转换成美快物流状态
	function MKIL_State($str){
		if(preg_match("/已妥投/", $str)  || preg_match("/已签收/", $str)){

			$IL_state = 1003;	//快递签收

		}else if(preg_match("/正在投递/", $str)){

			$IL_state = 1005;	//快递派件

		}else if(preg_match("/收寄/", $str)){

			$IL_state = 1001;	//快递揽件

		}else{

			$IL_state = 1000;	//在途
		}

		return $IL_state;
	}

	// 美快BC优选2 返回的物流信息的时间中，全部默认+8小时的，如：2017-04-17T09:56:13+08:00
	function trans_time($time){
		date_default_timezone_set('PRC');
		// return date('Y-m-d H:i:s',strtotime($time)+8*60*60);
		return date('Y-m-d H:i:s',strtotime($time)); //不需要+8小时
	}