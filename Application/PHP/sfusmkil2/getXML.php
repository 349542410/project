<?php
	
	/**
	 * [发送请求获取顺丰物流xml报文  20161003 Jie]
	 * @param  [type]  $tracking_number [运单号]
	 * @param  integer $debug           [调试编码，不同数值可用于检查不同位置的数据输出]
	 * @return [type]                   [description]
	 */
	function get($tracking_number, $debug=0){
		require('h_config.php');//配置信息
		// require_once('function.php');//公用函数 已被加载，不再重复加载
		header('Content-type:text/html;charset=UTF-8');	//设置输出格式

		$cXml = createXML($customerCode, $tracking_type, $tracking_number, $lang);
		$data = base64_encode($cXml);//xml报文加密

		$validateStr = base64_encode(md5(utf8_encode($cXml).$checkword, false));

		$client = new SoapClient ($pmsLoginAction);
		
		$result = $client->sfexpressService(array('data'=>$data,'validateStr'=>$validateStr,'customerCode'=>$customerCode));//查询，返回的是一个结构体
		
		// 调试编码
		if($debug == 1){
			echo '获得原始顺丰物流信息数据：';
			print_r($result);die;
		}else{
			return $result;
		}
	}