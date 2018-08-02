<?php
/*
$config = array(
	'partnerName'     => 'meijie',	//合作商名称
	'version'         => 'meijie',//'1.0.0.1',	//API版本号
	'pmsLoginAction'  => "http://47.52.23.6:8090/api",	// 网络服务请求地址
	'format'          => 'xml',	//要发送内容的数据格式， 目前支持xml， 默认值为xml
	'checkword'       => 'SGc0wFl0ZP',	//IBS系统为客户分配的密钥
	// 'lang'            => 'zh-CN',	//语言 官方已默认为en
	'xmlsave'         => 'D:/sys/logs/kdno17_logs/',//生成xml保存到文件
	'exports_switch'  => true,	//是否生成一个txt文件
);
$Order_config = array(
	'BranchCode'            => '1008',			//物流公司海外仓编号
	'LogisticsProviderCode' => 'meijie',		//线路编号（如果指定LogisticsServiceCode该字段为必选）
	'LogisticsServiceCode'  => 'meijie',		//线路服务编号
	'MemberId'              => '1098',			//会员ID
	'SenderProvince'        => 'California',	//发件人 省
	'SenderCity'            => 'Berkeley',		//发件人 市
	'SenderCountryCode'     => 'US',			//发件人国家代码
	'ReceiverCountryCode'   => 'CN',			//收件人国家代码
);*/

$config = array(
	'partnerName'     => 'meijie',	//合作商名称
	'version'         => 'meijie',//'1.0.0.1',	//API版本号
	'pmsLoginAction'  => "http://52.53.199.43:8090/api",	// 网络服务请求地址
	'format'          => 'xml',	//要发送内容的数据格式， 目前支持xml， 默认值为xml
	'checkword'       => 'CANTA5Pxqk',	//IBS系统为客户分配的密钥
	// 'lang'            => 'zh-CN',	//语言 官方已默认为en
	'xmlsave'         => UPFILEBASE.'/Upfile/kdno17_logs/',//生成xml保存到文件
	'exports_switch'  => true,	//是否生成一个txt文件
);

$Order_config = array(
	'BranchCode'            => 'meijie1006',			//物流公司海外仓编号
	'operatorID'            => '1112',			
	'LogisticsProviderCode' => 'meijie',		//线路编号（如果指定LogisticsServiceCode该字段为必选）
	'LogisticsServiceCode'  => 'meijietmail',		//线路服务编号
	'MemberId'              => '10080',			//会员ID
	'SenderProvince'        => 'California',	//发件人 省
	'SenderCity'            => 'Berkeley',		//发件人 市
	'SenderCountryCode'     => 'US',			//发件人国家代码
	'ReceiverCountryCode'   => 'CN',			//收件人国家代码
);