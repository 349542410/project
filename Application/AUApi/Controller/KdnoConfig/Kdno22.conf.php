<?php
$config = array(
	'cuscode'        => 'MKGJWL',//$GLOBALS['globalConfig']['KDNO_CONFIG']['22']['cuscode'],	//仓库标识
	'sitecode'       => 'MJCO01',//$GLOBALS['globalConfig']['KDNO_CONFIG']['22']['sitecode'],	//户代码
	'key'            => 'fc2658f96e4c4bfd98a31efaa6ef4606',//$GLOBALS['globalConfig']['KDNO_CONFIG']['22']['key'],	//客户秘钥
	'Version'        => '1.0',	//语言 官方已默认为en
	'pmsLoginAction' => 'http://api.esd.topideal.com/bs/s_interface.aspx',//$GLOBALS['globalConfig']['KDNO_CONFIG']['22']['pmsLoginAction'],	// 地址
	'xmlsave'        => UPFILEBASE.'/Upfile/kdno22_logs/',//生成xml保存到文件
	'exports_switch' => true,	//是否生成一个txt文件
	// 'percent'     => 0.8, //商品价格转换比例
	// 'rmb_rate'    => 7, //人民币转美元 汇率比例
);
$Order_config = array(
	'sender_country'     => '美国',//收件人国家 Y
	'receiver_country'   => '中国',//收件人国家 Y
	'buyer_idType'       => '1',//值为：1=身份证，0=其他    Y

	'productCode'        => 'GZ-ZTO-JC',//产品代码，分配物流公司单号使用  GZ-STO-JC:申通广州机场网点单号  	Y
	'declareType'        => 'DG-BC',//(可选)申报类型 如：GZ-NS-CC：广州南沙CCGZ-HP-BC：广州黄埔   Y
	'goodsPriceCurrency' => '142',//订单货款金额币制，BC必填，默认142：人民币  Y
	'freight'            => '0',//订单运费，无运费=0  Y
	'freightCurrency'    => '142',//运费币制，BC必填，默认142：人民币  Y
	'insuredFee'         => '0',//订单保费，无保费=0  Y
	'insuredCurrency'    => '142',//保费币制，BC必填，默认142：人民币  Y
	'taxFcy'             => '0',//订单税费，无税费=0  Y
	'taxCurrency'        => '142',//税费币制，BC必填，默认142：人民币  Y
	'discount'           => '0',//优惠减免金额，无优惠=0  Y
	'discountCurrency'   => '142',//优惠减免金额币制，BC必填，默认142：人民币  Y

	'payCurrency'        => 'CNY',//支付币制-货币单位：CNY    Y
	'changeSingle'       => '0',//换单标志，0：不需要、1：需要    Y
	'tradeModels'        => '1',//贸易模式1：跨境模式 （默认）2：一般贸易    Y
	'orderBy'            => 'asc',//物流信息排序，asc,desc不区分大小写；Asc:按时间升级显示；Desc：按时间降序显示  Y

);
