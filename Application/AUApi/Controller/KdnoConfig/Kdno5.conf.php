<?php
$baseconf = array(
	'customerCode'   => 'OSMS_215',	//客户编码
	'checkword'      => '350e4437b96d',	//IBS系统为客户分配的密钥
	'pmsLoginAction' => "http://osms.sf-express.com/osms/services/OrderWebService?wsdl",	//网络服务请求地址
	//'lang'           => 'zh-CN',	//语言 官方已默认为en
	'lang'           => 'zhCN',	//语言 官方已默认为en
	'xmlsave'        => UPFILEBASE.'/Upfile/kdno5_logs/',//生成xml保存到文件
	'exports_switch' => true,	//是否生成一个txt文件
	'freight'        => 100,	//运费  默认100     20160913 Jie
	'rate'           => 0.112,	//税率	20160913 Jie	
);
$orderconf = array(
	'is_gen_bill_no'  => '1',			//默认值1
	'j_custid'        => '0010006085',	//寄件方编号
	'j_email'         => 'jiangjiewen@megao.cn',	//寄件方Email
	'j_company'       => '',        		//寄件方公司名称
	'j_area_code'     => '',			//寄件方联系区号
	'j_country'       => 'US',		//寄件方国籍   默认值US
	
	'j_contact'       => '',        		//寄件方联系人
	'j_tel'           => '',			//寄件方联系电话
	'j_address'       => '',			//寄件方详细地址
	'j_post_code'     => '',			//寄件方邮编
	'custid'          => '0010006085',	//月结卡号
	'buyers_nickname' => 'Megaoshop',	//寄件人名称 美购商城
);