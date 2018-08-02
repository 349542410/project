<?php
	// 创建xml报文
	function createXML($productId, $dsSku){

		//货号和电商sku，两个字段只能其中一个为空										
		if(trim($productId) != '') $dsSku = '';

		$xml = '<?xml version="1.0" encoding="UTF-8"?>
				<Message>
					<Header>
						<CustomsCode>4423962963</CustomsCode>
						<OrgName>广州美快软件开发有限公司</OrgName>
						<ProductId>'.$productId.'</ProductId>
						<DsSku>'.$dsSku.'</DsSku>
					</Header>
				</Message>';

		return $xml;
	}

	// curl post发送
    function sendXML($url, $xmlData){
		
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