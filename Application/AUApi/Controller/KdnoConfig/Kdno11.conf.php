<?php
$config = array(
	'CustomerCode'   => '10006',	//合作商名称
	'Key'            => 'C75C1568ED804B8F915D61213FC192C1',	//系统为客户分配的密钥
	'pmsLoginAction' => "http://47.52.200.227:8012/CustomerOrderWebService.asmx?wsdl",	// 请求地址
	// 'lang'        => 'zh-CN',	//语言 官方已默认为en
	'xmlsave'        => UPFILEBASE.'/Upfile/kdno11_logs/',//生成xml保存到文件
	'exports_switch' => true,	//是否生成一个txt文件
	'LabelType'      => 0,	//需要下载的格式类型  0，Zpl; 1，Pdf
);
$Order_config = array(
	'dValue'              => '0',		//申报价值   Y
	'duty_paid'           => 'Y',		//税金已付（Y/N，若不填写默认“N”）   Y=DDP N=DDU
);