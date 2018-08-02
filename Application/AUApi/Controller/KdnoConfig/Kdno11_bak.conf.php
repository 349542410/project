<?php
$config = array(
	'partnerName'     => 'test01',	//合作商名称
	'checkword'       => '123456',	//系统为客户分配的密钥
	'pmsLoginAction'  => "http://eexpress-ws.linexsolutions.com/eExpressClientWebService.asmx?wsdl",	// 请求地址
	// 'lang'            => 'zh-CN',	//语言 官方已默认为en
	'xmlsave'         => UPFILEBASE.'/Upfile/kdno11_logs/',//生成xml保存到文件
	'exports_switch'  => true,	//是否生成一个txt文件
);
$Order_config = array(
	'ReceiverCountryCode' => 'CN',		//收件人所属国家，默认值（CN）   Y
	'Pieces'              => '1',		//
	'dValue'              => '300',		//申报价值   Y
	'duty_paid'           => 'Y',		//税金已付（Y/N，若不填写默认“N”）   Y
);