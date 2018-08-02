<?php
return array(

//	'environment' => 'test',            // 测试环境
     'environment' => 'production',   // 真实环境

	'private_key' => 'meiquick.2017.com',//字符串加密解密密匙

	'URL_MODEL' 		=> 2,
	'URL_HTML_SUFFIX'	=>'',
	/*'TMPL_PARSE_STRING' => array(
		'__CSS__'    => WEB_JCM.'/Ace/css',			//css路径	20161025 伦 
		'__JS__'     => WEB_JCM.'/Ace/js',			//js 路径	20161025 伦
		'__IMG__'    => WEB_JCM.'/Ace/img', 			//img 路径	20161025 伦
		'__AVA__'    => WEB_JCM.'/Ace/avatars',		// avatars 路径	20161025 伦
		'__ICONS__'  => WEB_JCM.'/Ace/icons',		// avatars 路径	20161025 伦
		'__PUBLIC__' => WEB_JCM.'/Ace',				// 20161025 伦
		'__MEMBER__' => WU_JCM.'/Member',			//官网会员登录 js,css,img路径  2015-10-15	20161025 伦
		'__PCWX__'   => WEB_JCM.'/Tokenpcwx',		//微信登录 js,css,img路径  2016-04-05	20161025 伦
		'__APPWX__'  => WU_JCM.'/wx',		//手机版官网会员登录 js,css,img路径  20170608 jie
	),*/		
	'TMPL_PARSE_STRING' => array(
		'__CSS__'    => WU_JCM.'/Ace/css',			//css路径	20161025 伦 
		'__JS__'     => WU_JCM.'/Ace/js',			//js 路径	20161025 伦
		'__IMG__'    => WU_JCM.'/Ace/img', 			//img 路径	20161025 伦
		'__AVA__'    => WU_JCM.'/Ace/avatars',		// avatars 路径	20161025 伦
		'__ICONS__'  => WU_JCM.'/Ace/icons',		// avatars 路径	20161025 伦
		'__PUBLIC__' => WU_JCM.'/Ace',				// 20161025 伦
		'__MEMBER__' => WU_JCM.'/Member',			//官网会员登录 js,css,img路径  2015-10-15	20161025 伦
		'__PCWX__'   => WU_JCM.'/Tokenpcwx',		//微信登录 js,css,img路径  2016-04-05	20161025 伦
		'__APPWX__'  => WU_JCM.'/wx',		//手机版官网会员登录 js,css,img路径  20170608 jie
	),	
	//'HOME_NAME'		=>'Web',
	'PERSON_PIC' 	=> '/Uploads/Person/',			// 设置文件上传的保存路径（不要把根路径写进去）20161025 伦 上传路劲更改 D:/sys/www/upfiles/a/Web
	'COMPANY_PIC'	=> '/Uploads/Company/',			//20161025 伦 上传路劲更改 D:/sys/www/upfiles/a/Web

	//表单令牌 配置
	'TOKEN_ON'      =>    true,  				// 是否开启令牌验证 默认关闭
	'TOKEN_NAME'    =>    '__hash__',    		// 令牌验证的表单隐藏字段名称，默认为__hash__
	'TOKEN_TYPE'    =>    'md5',  				//令牌哈希验证规则 默认为MD5
	'TOKEN_RESET'   =>    true,  				//令牌验证出错后是否重置令牌 默认为true

	'LANG_SWITCH_ON' 		=> true,   				// 开启语言包功能
	'LANG_AUTO_DETECT' 		=> true, 				// 自动侦测语言 开启多语言功能后有效
	'LANG_LIST'        		=> 'zh-cn,en-us,zh-tw', // 允许切换的语言列表 用逗号分隔
	'VAR_LANGUAGE'     		=> 'l', 				// 默认语言切换变量
	'DEFAULT_LANG'		    => 'en-us',				// 默认语言，开启了自动侦测语言的情况下，此功能无效

	//默认分页显示数量
	'EPAGE' => 10,
	//Order货品声明的行数 20170322 甘
	'OROW' => 6,

	'page_print_count' => 3,

	

	//证件类型
	'ID_TYPE' => array(
		'ID'      => 'identity_card', //身份证
		// 'PASPORT' => 'passport', //护照
	),
    // 微信端访问不同模板
    'DEFAULT_V_LAYER'    => strpos(isset($_SERVER['HTTP_USER_AGENT'])?$_SERVER['HTTP_USER_AGENT']:'', 'MicroMessenger') ? 'ViewWx' :'View',
);
