<?php
	/*
		20161222 与广东邮政对接
	*/
	$config = array(
		'clientId'        => 'K21000119',	// 登录名
		'partnerId'      => '123456',	// 登录密码
		
		'OrderModeUrl'   => "http://58.32.246.71:8000/CommonOrderModeBPlusServlet.action",// B+模式获取电子面单号接口  请求地址 必需
		
		'exports_switch' => false,	//是否生成一个txt文件
		'xmlsave'        => UPFILEBASE.'/Upfile/kdno8/',//生成xml保存到文件
	);

	//默认的订单信息配置
	$Order_config = array(
		'logisticProviderID' => 'YTO',	// 物流公司ID   Y
		'serviceType'        => '0',	//服务类型(1-上门揽收, 2-次日达 4-次晨达 8-当日达,0-自己联系)。默认为0   Y
		'orderType'          => '1',	//订单类型(0-COD,1-普通订单,2-便携式订单3-退货单)   Y
	);