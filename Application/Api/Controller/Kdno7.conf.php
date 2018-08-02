<?php
	/*
		20161222 与广东邮政对接
	*/
	$config = array(
		'userName'       => 'emsdmds',	// 登录账号
		'passWord'       => 'A123456',	// 登录密码
		'key'            => '805b6443960544773aeb2e5b48bea43d',	// 校验码
		//'pmsLoginAction' => "http://dmtestservice.ekjshop.com/KJB2CService.svc?wsdl",// 请求地址 必需
		'pmsLoginAction' => "http://dmservice.ekjshop.com/KJB2CService.svc?wsdl",// 请求地址 必需
		'OtherUrl'       => "http://dmtestservice.ekjshop.com/DMService.svc?wsdl",// 请求地址 面单打印/EMS物流追踪 专用
		
		'exports_switch' => false,	//是否生成一个txt文件
		'xmlsave'        => '../../../File/kdno7/',//生成xml保存到文件
		'rate'           => '0.119',	//税率	20170329 Jie
		'freight'        => '0',	//运费  默认100     20160913 Jie
	);

	//默认的订单信息配置
	$Order_config = array(
		'SenderCountry'     => '香港',		//发件人国家二字编码
		'SenderCity'        => '香港',		//发件人城市
		'SenderStreet'      => '40559 Encyclopedia,Fremont,CA',		//发件人地址
		'CustomCode'        => '5145',		//申报关区代码  必填、4位代码值(默认:5145)
		
		'Tax'               => '0',			//税费 必填、订单对应产生的税费(可填0)
		'OrderCurrency'     => '人民币',	//核算币制  必填、核算币制中文名(订单内取用的统一币制)
		'Freight'           => '0',			//运费 必填、可填0
		'Insurance'         => '0',			//保费 必填、可填0
		'DeductionAmount'   => '0',			//抵扣金额  必填 可为0
		'DeductionNote'     => '无',		//抵扣金额说明  必填 抵扣金额为0时，填”无”
		'OtherCharge'       => '0',			//其他费用  必填 可为0
		'PayerIdType'       => '01',		//订购人证件类型  必填 01身份证02护照04其它
		
		//推送支付信息 需要的资料
		'PaymentEntNo'      => '',			//支付企业编号  可空 广州关:支付企业海关编号 总署:单一窗口编码
		'PayEnterpriseCode' => '42342',			//支付企业代码  必填、支付企业在申报关口的备案号
		'PayCurrency'       => '人民币',		//支付币制名称  必填、支付单的核算币制中文名称，要和订单信息里核算币制一致,一般为"人民币"

		// 打印面单需要的资料
		'CustomerCode'       => '',		//大客户代码 varchar(30) 电商在KJY里一般的大客户代码
		'ApplySeaPort'       => '',		//进口口岸 varchar(30) 申报海关口岸(中文名称)

	);