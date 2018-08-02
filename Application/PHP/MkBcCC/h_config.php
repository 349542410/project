<?php
	// 美快优选CC(即顺丰)物流的推送配置
	$customerCode   = 'OSMS_215';			// 客户编码 必需
	$checkword      = '350e4437b96d';	// IBS系统为客户分配的密钥 必需
	$pmsLoginAction = 'http://osms.sf-express.com/osms/services/OrderWebService?wsdl';	// 请求地址 必需
	$lang           = 'zh_CN';			// 语言
	$tracking_type  = '1';				// 1.根据顺丰运单号查询; 2.根据客户订单号查询; 3.在IBS查询，不区分运单号和订单号
	$no             = 'STNO';			// STNO：顺丰运单号; MKNO：客户订单号(即我方的美快单号)
	$TranKd         = 12;				// 12：表示美快优选CC
	$limit          = 20; 				// 批量交易个数(即查询数据表的数据条数)
	$limitHour      = 36000;			// 程序操作时限(10个小时)