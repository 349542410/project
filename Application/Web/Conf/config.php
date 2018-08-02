<?php
return array(
	'URL_MODEL' => 2,
	'URL_HTML_SUFFIX'=>'',

	'TMPL_PARSE_STRING' => array(
		'__CSS__' 	=> WEB_JCM.'/css',//css路径	20161025 伦 
		'__JS__' 	=> WEB_JCM.'/js',//js 路径	20161025 伦
		'__IMG__' 	=> WEB_JCM.'/img', //img 路径	20161025 伦
		'__AVA__'	=> WEB_JCM.'/avatars',// avatars 路径	20161025 伦
		'__ICONS__'	=> WEB_JCM.'/icons',// avatars 路径	20161025 伦
		'__PUBLIC__'=> WEB_JCM,		// 20161025 伦
		'__MEMBER__'=> WU_JCM.'/Member',			//官网会员登录 js,css,img路径  2015-10-15	20161025 伦
	),	
	'HOME_NAME'		=>'WEB',
	'PERSON_PIC' 	=> '/Uploads/Person/',	// 设置文件上传的保存路径（不要把根路径写进去）20161025 伦 上传路劲更改 D:/sys/www/upfiles/a/Web
	'COMPANY_PIC'	=> '/Uploads/Company/',	//20161025 伦 上传路劲更改 D:/sys/www/upfiles/a/Web

	//表单令牌 配置
	// 'TOKEN_ON'      =>    true,  // 是否开启令牌验证 默认关闭
	// 'TOKEN_NAME'    =>    '__hash__',    // 令牌验证的表单隐藏字段名称，默认为__hash__
	// 'TOKEN_TYPE'    =>    'md5',  //令牌哈希验证规则 默认为MD5
	// 'TOKEN_RESET'   =>    true,  //令牌验证出错后是否重置令牌 默认为true

	'LANG_SWITCH_ON' 		=> true,   	// 开启语言包功能
	'LANG_AUTO_DETECT' 		=> true, 	// 自动侦测语言 开启多语言功能后有效
	'LANG_LIST'        		=> 'zh-cn,en-us,zh-tw', // 允许切换的语言列表 用逗号分隔
	'VAR_LANGUAGE'     		=> 'l', 	// 默认语言切换变量
	'DEFAULT_LANG'		    => 'en-us',

    'RAPIURL'           =>API_URL,	//读取行为的url

    'CUSTOMER_SERVICE'  => 'https://webchat.7moor.com/wapchat.html?accessId=0fcfddd0-84b8-11e8-9e40-2b39a9550803&fromUrl=meiquick-web',
	//默认分页显示数量
	'EPAGE' => 10,
);