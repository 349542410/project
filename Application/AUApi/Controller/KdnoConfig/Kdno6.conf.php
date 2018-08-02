<?php
	/*
		20161222 与香港E特快下单对接
	*/
	$config = array(
		'ApiToken'    => '4bbc0757c855708898405b7fdf3afacf',	// 口令环
		'ShipwayCode' => 'EEXPRESS',	// 订单上传的默认设置
		'Currency'    => 'RMB',	// 申报币种
		'CustomsType' => 'G',	// 海关申报类型
		// 'Url'         => "http://test3.megao.hk/test/server.php/type/",// 请求地址 必需
		'Url'         => "http://www.chinacourierhk.com/openapi/user/1.2/",// 请求地址 必需
		'exports_switch' => true,	//是否生成一个txt文件
		'xmlsave'        => UPFILEBASE.'/Upfile/kdno6_logs/',//生成xml保存到文件		
	);

	//默认的订单信息配置
	$Order_config = array(
		'SenderCountryCode'   => 'HK',		//发件人国家二字编码
		'SenderState'         => 'HK',		//发件人省份
		'SenderCity'          => 'HK',		//发件人城市
		'SenderStreet'        => 'HK',		//发件人地址
		'DutyPaid'            => '1',       //是否做关税预付
		'IsCarTransportation' => '1',		//是否全程陆运
		'ReceiverCountryCode' => 'CN',		//收件人国家二字编码，中国为 CN
	);

	//默认的商品信息配置
	$printOrder = array(
		'LabelFormat'  => 'Label_100x100',	//打印格式
		'OutPutFormat' => 'pdf',	// 输出格式。pdf 返回 pdf 文件流; html 返回 html 格式代码
		'Base64Encode' => 'true',	// 返回的文件流是否做 Base64 编码。输出格式是 pdf 时必须填写
	);