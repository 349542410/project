<?php
$config = array(
	'SecretKey'      => $GLOBALS['globalConfig']['KDNO_CONFIG']['21']['SecretKey'],	//系统为客户分配的密钥
	'AccessToken'    => $GLOBALS['globalConfig']['KDNO_CONFIG']['21']['AccessToken'],	//系统为客户分配的密钥
	'APPKEY'         => $GLOBALS['globalConfig']['KDNO_CONFIG']['21']['APPKEY'],	//系统为客户分配的密钥
	'Version'        => '1.0',	//语言 官方已默认为en
	// 'sign'           => 'MD5',	//语言 官方已默认为en

	'pmsLoginAction' => $GLOBALS['globalConfig']['KDNO_CONFIG']['21']['pmsLoginAction'],	// 请求地址
	'xmlsave'        => UPFILEBASE.'/Upfile/kdno21/',//生成xml保存到文件
	'exports_switch' => true,	//是否生成一个txt文件
	// 'percent'        => 0.8, //商品价格转换比例
	// 'rmb_rate'       => 7, //人民币转美元 汇率比例
);
$Order_config = array(
	//发件人信息
	'SendCountryName' => '美国',//国家 Y
	'ChannelName'     => '美街',//渠道名称 Y
	'LogisticId'     => '1',//货站ID Y
	'LineTypeId'     => '3',//线路类型Id   1-个人快件 3-电商快件 9-奶粉专线  Y
	

);