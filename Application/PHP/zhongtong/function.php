<?php
	/**
	 * 文件中文名： 调用香港E特快的类，获取到的物流信息作一定的处理后再执行物流的数据保存
	 * 2017-01-10 Jie
	 * 处理：接收返回的xml之后，处理成数组形式作返回
	 * @param  [type] $tracking_number [运单号]
	 * @param  [type] $debug           [调试编码，不同数值可用于检查不同位置的数据输出]
	 * @param  [type] $serurl          [用于保存物流信息的地址]
	 * @param  [type] $config          [香港E特快的必需的配置信息]
	 * @param  [type] $type            [是否直接返回物流信息，不保存物流数据]
	 * @return [type]                  [description]
	 */
	function getArr($tracking_number, $debug=0, $serurl, $config, $type=''){
		require("./../../hprose_php5/HproseHttpClient.php");
		// echo '<pre>';
		$result = GetOrderTracking($tracking_number, $config);
		$j_arr = json_decode($result, true);
		
		if($type != ''){
			return $j_arr;exit;
		}

		// echo '<pre>';
		// print_r($j_arr);
		// die;

		if($j_arr['Success'] == 'true'){
			// print_r($j_arr);die;

			$reArr = $j_arr['Data'];
			// print_r($reArr);die;
			// 第一步筛选 是否有物流信息更新
			if(!isset($reArr['TrackingEventList']) || !isset($reArr['TrackingEventList']['TrackingEvent'])){
				return array('do'=>'no', 'title'=>'该运单号尚未更新物流信息');      //返回信息
			}

			// 第二步筛选 是否有物流信息更新
			if(count($reArr['TrackingEventList']['TrackingEvent']) == 0){
				return array('do'=>'no', 'title'=>'暂无物流信息更新');      //返回信息
			}
			
			// print_r($IL_state);
			$list = (isset($reArr['TrackingEventList']['TrackingEvent'])) ? $reArr['TrackingEventList']['TrackingEvent'] : array();
			// print_r($list);

			if(count($list) > 0){
				$new_a = array();
				//将香港E特快的物流信息转化为美快物流信息
				foreach($list as $key => $row){

					$IL_state = MKIL_State($row['Message']);
					// print_r($IL_state);die;

					if($IL_state == '1003'){
						$new_a[$key]['BusinessLinkCode'] = $IL_state;
						$new_a[$key]['TrackingContent']  = $row['Message'];
						$new_a[$key]['OccurDatetime']    = $row['Time'];

						// 额外保存本公司专有信息
						$new_a[$key+1]['BusinessLinkCode'] = '1003';
						$new_a[$key+1]['TrackingContent']  = "感谢使用美快国际物流，期待再次为您服务！";
						$new_a[$key+1]['OccurDatetime']    = date('Y-m-d H:i:s');
					}else{
						$new_a[$key]['BusinessLinkCode'] = $IL_state;
						$new_a[$key]['TrackingContent']  = $row['Message'];
						$new_a[$key]['OccurDatetime']    = $row['Time'];
					}

				}
				$new_a = array_reverse($new_a);//返回翻转顺序的数组
				// print_r($new_a);die;

				//将loginfo生成 ./Express100/common_server.php 可接受的格式，使用 HproseHttp进行保存
				$arr = array();
				$arr['status']                  = '';
				$arr['billstatus']              = 'check';
				$arr['message']                 = '';
				$arr['lastResult']['message']   = 'ok';
				$arr['lastResult']['nu']        = $tracking_number;	//tran_list.STNO
				$arr['lastResult']['ischeck']   = '1';
				$arr['lastResult']['condition'] = '';
				$arr['lastResult']['com']       = '';
				$arr['lastResult']['status']    = '';
				$arr['lastResult']['state']     = '';
				$arr['lastResult']['data']      = $new_a;
				// print_r($arr);die;
				$client = new HproseHttpClient($serurl);
			    $res = $client->save($arr, 'STNO', 'zhongtong');	//直接传入数组形式的数据
				// print_r($res);die;
			    return $res;
			}else{
				return $backArr = array('do'=>'no', 'title'=>'该运单号尚未更新物流信息');      //返回信息
			}
		}else{

			$msg = (trim($j_arr['ReasonDesc']) != '') ? $j_arr['ReasonDesc'] : '获取信息失败';
			return array('do'=>'no', 'title'=>$msg);      //返回信息
		}

	}

	// 获取跟踪信息
	function GetOrderTracking($STNO, $config){
		
		$model = array(
			'GetOrderTracking' => array(
				'ExpressOrderNumber' => $STNO,
			),
		);

		$xml = arrayToXml($model);//转换成xml报文

// echo '<pre>';		
// echo $STNO;
// print_r($xml);
// die;
		$up_to_low = strtolower($xml);//通知内容（xml/json）全部转化为小写
		$with_key = $up_to_low.$config['checkword'];//加密钥：上一步得到的字符串追加密钥
		$with_md5 = md5($with_key);//将上一步得到的字符串进行MD5

		$sendData = array();
		$sendData['content']     = $xml;//要发送的XML内容
		$sendData['cryptograph'] = $with_md5;//数据验证密文
		$sendData['partnerName'] = $config['partnerName'];//合作商名称
		$sendData['version']     = '1.0';//API版本号
		$sendData['messageType'] = 'GetOrderTracking';//发送的消息类型
		$sendData['format']      = $config['format'];//要发送内容的数据格式，目前支持xml，默认值为xml

		$xmlstring = sendXML($config['pmsLoginAction'],$sendData);
		
		// // 模拟
		// $xmlstring = '<Response>
		// 			  <Success>true</Success>
		// 			  <Reason></Reason>
		// 			  <ReasonDesc></ReasonDesc>
		// 			  <Data>
		// 			    <TrackingEventList>
		// 			      <TrackingEvent>
		// 			        <Time>2014-07-29 19:33:51</Time>
		// 			        <Message>波特兰公司 货物已扫描签收入库</Message>
		// 			      </TrackingEvent>
		// 			      <TrackingEvent>
		// 			        <Time>2014-07-30 11:42:11</Time>
		// 			        <Message>货物已扫描装箱 准备出库</Message>
		// 			      </TrackingEvent>
		// 			      <TrackingEvent>
		// 			        <Time>2017-11-10 17:35:51</Time>
		// 			        <Message>[嘉兴市]海宁长安 的 -LA(0573-87466200、0573-87466300)已收件 网点收件扫描-Parcel scanned by site</Message>
		// 			      </TrackingEvent>
		// 			      <TrackingEvent>
		// 			        <Time>2017-11-13 09:15:36</Time>
		// 			        <Message>[遵义市]遵义龙坑镇 的 (0851-27225254) 向泽进(18984917231) 正在派件 网点派件扫描-Physical delivery scheduled.</Message>
		// 			      </TrackingEvent>
		// 			      <TrackingEvent>
		// 			        <Time>2017-11-13 18:35:42</Time>
		// 			        <Message>[遵义市]遵义龙坑镇(0851-27225254)的派件已签收,感谢您使用中通快递，期待再次为您服务!Delivered </Message>
		// 			      </TrackingEvent>
		// 			    </TrackingEventList>
		// 			  </Data>
		// 			</Response>';

		$xmlsave = $config['xmlsave'];
		if(!is_dir($xmlsave)) mkdir($xmlsave, 0777, true);//第三个参数设置true 设置递归模式自动创建必需的目录文件夹

		$file_name = 'Kdno17_GetOrderTracking_'.$STNO.'.txt';	//文件名

		$content = "=================== ".date('Y-m-d H:i:s')." ===================\r\n\r\n-------- Request --------\r\n\r\n".$xml."\r\n\r\n-------- Response --------\r\n\r\n".$xmlstring."\r\n\r\n";

		if(is_file($file_name)){

			file_put_contents($xmlsave.$file_name, $content);
		}else{
			file_put_contents($xmlsave.$file_name, $content, FILE_APPEND);
		}
		
		return json_encode((array) simplexml_load_string($xmlstring));
	}

	// curl post发送
    function sendXML($url, $xmlData){
		$data['data'] = $xmlData;
		$ch = curl_init();
		// $header[] = "Content-type: text/xml";//定义content-type为xml
		curl_setopt($ch, CURLOPT_URL, $url); //定义表单提交地址
		curl_setopt($ch, CURLOPT_POST, 1);   //定义提交类型 1：POST ；0：GET
		curl_setopt($ch, CURLOPT_HEADER, 0); //定义是否显示状态头 1：显示 ； 0：不显示
		// curl_setopt($ch, CURLOPT_TIMEOUT,10); //超时6秒
		// curl_setopt($ch, CURLOPT_HTTPHEADER, $header);//定义请求类型
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);//定义是否直接输出返回流，即把返回的请求结果赋予给$info
		curl_setopt($ch, CURLOPT_POSTFIELDS, $xmlData); //定义提交的数据，这里是XML文件
    	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // 跳过证书检查
		$info = curl_exec($ch);//执行
		curl_close($ch);//关闭
		return $info;
    }

	/** 生成xml格式 中通对接专用 */
	function arrayToXml($arr){
		header('Content-type:text/html;charset=UTF-8');	//设置输出格式
		$xml = "";
		
		foreach ($arr as $key=>$val){
			if(is_numeric($key)){
				$xml .= "<Cargo>".arrayToXml($val)."</Cargo>";
			}else{
				if(is_array($val)){
					$xml.="<".$key.">".arrayToXml($val)."</".$key.">";
				}else{
					$xml.="<".$key.">".$val."</".$key.">";
				}
			}
		}

		return $xml;
	}

	// 根据中通的物流状态转换成美快物流状态
	function MKIL_State($str){
		if(preg_match("/已妥投/", $str)  || preg_match("/已签收/", $str)){

			$IL_state = 1003;	//快递签收

		}else if(preg_match("/正在投递/", $str)  || preg_match("/正在派件/", $str)){

			$IL_state = 1005;	//快递派件

		}else if(preg_match("/已收件/", $str)){

			$IL_state = 1001;	//快递揽件

		}else{

			$IL_state = 1000;	//在途
		}

		return $IL_state;
	}