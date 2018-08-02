<?php
$config = array(
	'Username'       => 'meiquick',	//合作商名称
	'Password'       => '6d89fbc4b1e9',	//系统为客户分配的密钥
	'Version'        => '1.0',	//语言 官方已默认为en
	'pmsLoginAction' => "https://chongqing-api.11183.hk/packageService.svc?wsdl",	// 请求地址
	'xmlsave'        => UPFILEBASE . '/kdno19/',//生成xml保存到文件
	'exports_switch' => true,	//是否生成一个txt文件
	'percent'        => 0.8, //商品价格转换比例
	'rmb_rate'       => 7, //人民币转美元 汇率比例
	'CurrencyCode'   => 'USD', //人民币转美元 汇率比例
	'hide_or_not'    => true, //决定某些字段是否显示/隐藏
);
$Order_config = array(
	//发件人信息
	'FromArea'               => 'Berkeley',//区/县 Y
	'FromCity'               => 'Berkeley',//城市 Y
	'FromEmail'              => '2565855778@qq.com',//邮箱 Y
	'FromProvince'           => 'California',//省份 Y
	
	//其他必要信息
	'ChannelCode'            => 'CH0026',//渠道 Y
	'CustomerIdentity'       => 'BNGEWM',//客户标识 Y
	'TrackingCenterCode'     => 'HZ011',//货站编码 Y
	'HasPrepaid'             => 1,//是否代缴关税 Y
	'HasReplaceUploadIdCard' => 1,//是否代传身份证 Y
	'InsureStatus'           => 1,//是否投保 Y
	'Length'                 => 10,//长 Y
	'Width'                  => 10,//宽 Y
	'Height'                 => 10,//高 Y
	'Origin'                 => 'US',//原产地 Y
	'TariffNumber'           => '01019900',//行邮税号 至少填写一个，以TariffNumber为准	Y
);
$OrderesBoxed_config = array(
	'NumberType' => 2,//号码类型，1-客户订单号,2-EMS 跟踪号  Y
	'Length'     => 10,//长 CM  Y
	'Width'      => 10,//宽 CM  Y
	'Height'     => 10,//高 CM  Y
	'Weight'     => 10,//重量 KG   Y
);