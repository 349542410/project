<?php
$baseconf = array(
	/*
	'customerCode'    => 'OSMS_1',	//客户编码
	'checkword'       => 'fc34c561a34f',	//IBS系统为客户分配的密钥
	'pmsLoginAction'  => "http://osms.sit.sf-express.com:2080/osms/services/OrderWebService?wsdl",	// 网络服务请求地址
	'imgUploadAction' => "http://osms.sit.sf-express.com:2080/osms/hessian/uploadIdentityService",	// 证件图片上传请求地址
	*/
	'customerCode'    => 'OSMS_1168',	//客户编码
	'checkword'       => '828c146eb09d4686',	//IBS系统为客户分配的密钥
	'pmsLoginAction'  => "http://osms.sf-express.com/osms/services/OrderWebService?wsdl",	// 网络服务请求地址	
	'imgUploadAction' => "http://osms.sf-express.com/osms/hessian/uploadIdentityService",	// 证件图片上传请求地址

	'lang'            => 'zh-CN',	//语言 官方已默认为en
	'xmlsave'         => UPFILEBASE.'/Upfile/kdno12_logs/',//生成xml保存到文件
	'exports_switch'  => true,	//是否生成一个txt文件
	'freight'         => 0,	//运费  默认100     20160913 Jie
	'rate'            => 0,	//税率	20160913 Jie
);
$orderconf = array(
	// 'is_gen_bill_no'  => '1',			//默认值1
	// 'j_custid'        => '0010002117',	//寄件方编号
	// 'j_email'         => 'dev@megao.cn',	//寄件方Email
	// 'j_company'       => '',        		//寄件方公司名称
	// 'j_area_code'     => '',			//寄件方联系区号
	'j_country'       => 'US',			//寄件方国籍   默认值US

	// 'j_province'      => '',			// N 寄件方省份    V1.20
	// 'j_city'          => '',			// N 寄件方城市   V1.20
	// 'j_county'        => '',			// N 寄件人所在县/区，必须是标准的县/区称谓，示例：“福田区”。  V1.20
	
	'j_contact'       => '',        	//寄件方联系人
	'j_tel'           => '',			//寄件方联系电话
	'j_address'       => '',			//寄件方详细地址
	'j_post_code'     => '',			//寄件方邮编
	//'custid'          => '0010002117',	//月结卡号
	'custid'          => '0015000031',	//月结卡号
	// 'buyers_nickname' => 'Megao',	//寄件人名称 美购商城
	'tax_set_accounts'   => '0015000031',	//税金结算账号
);