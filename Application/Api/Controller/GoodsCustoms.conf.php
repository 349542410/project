<?php
	/*
		20161222 与海关商品报备对接
		商品报备
	*/
	// 统一用的xml报文头部
	$Head = array(
		'MessageID'    => '',//报文编号 (MessageType)+”_”+发送方代码（Sender） + YYYYMMDDHHMMSS(日期时间) +XXXXX(五顺序流水号)
		'MessageType'  => '',//报文类型
		'Sender'       => 'TEST17',//报文发送者标识
		'Receiver'     => 'KJPUBLICPT',//报文接收人标识
		'SendTime'     => date('YmdHis'),//date('YmdHis'),//发送时间
		'FunctionCode' => '',//业务类型   单向海关申报填 CUS;单向国检申报填 CIQ;同时发送时填写“BOTH”
		'SignerInfo'   => '',//签名信息  可空
		'Version'      => '3.0',//版本号
	);

	//订单报文 订单资料配置
	$Elec_order = array(
		'freight'                => '0',		//运费  默认0     20160913 Jie
		'rate'                   => '0.119',	//税率	20160913 Jie	
		'OrderStatus'            => '0',	//电子订单状态 : 0-订单确认,1-订单完成,2-订单取消 Y
		'PayStatus'              => '0',	//支付 : 0-已付款,1-未付款	Y
		'OtherPayment'           => '0',	//抵付金额  优惠减免金额，无则填“0”
		'OtherPayNotes'          => '',	//抵付说明抵付情况说明。如果填写抵付金额时，此项必填。
		'RecipientCountry'       => '142',	//收货人所在国
		'RecipientProvincesCode' => '440402',	//收货人收货人行政区代码  进口需要填收货人所在行政区域代码 出口可空
		'CIQGoodsNo'             => '1600894441',	//检验检疫商品备案编号
	);

	//订单报文 头部
	$OrderHead = array(
		'DeclEntNo'          => 'CO0000000033',	//申报企业编号	Y
		'DeclEntName'        => '广州美快软件开发有限公司',	//申报企业名称	Y
		'EBEntNo'            => 'CO0000000033',	//电商企业编号   Y
		'EBEntName'          => '广州美快软件开发有限公司',	//电商企业名称   N
		'EBPEntNo'           => 'CO0000000033',	//电商平台企业编号	Y
		'InternetDomainName' => 'www.meiquick.com',	//电商平台互联网域名	Y
		'EBPEntName'         => '广州美快软件开发有限公司',	//电商平台企业名称	N
		'DeclTime'           => date('YmdHis'),	//申报时间		Y
		'OpType'             => 'A',	//操作方式	A-新增；M-变更；D-删除	Y
		'IeFlag'             => 'I',	//进出口标示	I-进口商品订单；E-出口商品订单	Y
		'CustomsCode'        => '5208',	//主管海关代码		Y
		'CIQOrgCode'         => '442100',	//检验检疫机构代码		Y
	);

	//商品报备报文 头部
	$GoodsRegHead = array(
		'DeclEntNo'    => 'CO0000000033', //申报企业编号
		'DeclEntName'  => '广州美快软件开发有限公司', //申报企业名称
		'EBEntNo'      => 'CO0000000033', //电商企业编号
		'EBEntName'    => '广州美快软件开发有限公司', //电商企业名称
		'OpType'       => 'A', //操作方式:A-新增；M-修改；D-取消备案；
		'CustomsCode'  => '5130', //主管海关代码
		'CIQOrgCode'   => '442100', //检验检疫机构代码
		'EBPEntNo'     => 'CO0000000033', //电商平台企业编号
		'EBPEntName'   => '广州美快软件开发有限公司', //电商平台名称
		'CurrCode'     => '142', //电商平台名称
		'BusinessType' => '3', //跨境业务类型:1-特殊监管区域BBC保税进口；2-保税仓库BBC保税进口；3-BC直购进口；
		'DeclTime'     => '20160426163203', //申请备案时间
		'InputDate'    => '20160426163203', //录入日期
		'IeFlag'       => 'I', //进出境标志  I-进，E-出
		'Notes'        => '商品备案测试专用01', //备注   N
	);