<?php
$config = array(
	'AppToken'       => 'Pv2g6gfqoh0=',	//系统为客户分配的密钥
	'Version'        => '1.0.0.1',	//语言 官方已默认为en
	'pmsLoginAction' => "http://220.249.191.230:60104/api/External/OrderMarking",	// 请求地址
	'xmlsave'        => UPFILEBASE.'/Upfile/kdno20/',//生成xml保存到文件
	'exports_switch' => true,	//是否生成一个txt文件
	'percent'        => 0.8, //商品价格转换比例
	'rmb_rate'       => 7, //人民币转美元 汇率比例
	'CurrencyCode'   => 'USD', //人民币转美元 汇率比例
	'hide_or_not'    => true, //决定某些字段是否显示/隐藏
);
$Order_config = array(
	//发件人信息
	'ShipperCounty'   => 'Berkeley',//区/县 Y
	'ShipperCity'     => 'Berkeley',//城市 Y
	'ShipperProvince' => 'California',//省份 Y
	'ShipperCountry'  => 'US',//国家 Y
	
	//其他必要信息
	'LogisticsProductCode' => 'SP0002',//物流产品编码	Y
	'Freight'              => 0,//运费 无则填 0	Y
	'InsuredFee'           => 0,//保价费 无则填 0	Y
	'OrderType'            => 0,//订单类型  必填;0:直购 2:保税;默认：0	Y


);