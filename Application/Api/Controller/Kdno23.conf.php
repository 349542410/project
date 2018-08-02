<?php
$config = array(
	'cuscode'        => 'long@allpyra.com',	//账户
	'sitecode'       => 'B8CC311DEF2648B691BC8494A5360A30',	//密钥
	'pmsLoginAction' => "http://test.export.our-stone.com/export-openApi",	// 测试地址

	// 'pmsLoginAction' => "http://export.our-stone.com",	// 正式地址
	'xmlsave'        => UPFILEBASE.'/Upfile/kdno23_logs/',//生成xml保存到文件
	'exports_switch' => true,	//是否生成一个txt文件
	// 'percent'     => 0.8, //商品价格转换比例
	// 'rmb_rate'    => 7, //人民币转美元 汇率比例
);
$Order_config = array(
	'shipperCountryCode'       => 'US',//国家地区代码,不能填写中文汉字   Y
	'shipperCity'              => 'Fremont',//收件人城市   Y
	'clearanceDestinationCode' => 'GUANGZHOU',//清关目的地	Y
	'logisticsCompany'         => 'ems',//快递公司  Y
	'orderProxyFlag'           => 'N',//代理订单推送标志  Y
	
	'totalTax'                 => '0',//实际总税费  N
	'postAmount'               => '0',//实际运费  N

);