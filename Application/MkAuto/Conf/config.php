<?php
return array(

	'private_key' => 'meiquick.2017.com',//字符串加密解密密匙

	'URL_MODEL' 		=> 2,
	'URL_HTML_SUFFIX'	=>'',
	
	'TMPL_PARSE_STRING' => array(
		// 自助终端 微信登录配置
		'__CSS__'    => WU_JCM.'/Ace/css',			//css路径	20161025 伦 
		'__JS__'     => WU_JCM.'/Ace/js',			//js 路径	20161025 伦
		'__ICONS__'  => WU_JCM.'/Ace/icons',		//avatars 路径	20161025 伦
		'__APPWX__'  => WU_JCM.'/wx',				//js,css,img路径  20170608 jie

		// // PDA 配置
  //   	'__PUBLIC__' => '/asset/mkil',
  //   	'__SOFT__'   => '/App3/tpl/Mkil/Public', // 更改默认的/Public 替换规则
	),

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

	// 终端机所安装的软件类型（print:打印软件，receive：揽收软件）
	'Request_Type' => array('print', 'receive'),

	// 快件状态与说明
    'ilstarr' => array(
		array('8','未揽收'),			//0
		array('12','已揽收未中转'),		//1
		array('16','已中转因故返仓'),	//2
		array('20','已中转未到港'),		//3
		array('200','到港并发快递'),	//4
		array('1001','快递揽件'),		//5
		array('1000','快递在途'),		//6
		array('1005','快递派件中'),		//7
		array('1002','快递疑难'),		//8
		array('1003','快递签收'),		//9
		array('1004','退回'),			//10
		array('1006','拒收退回'),		//11
		array('1012','快递延迟'),		//12   Jie 20161013
		array('1400','清关中'),			//13   Jie 20161209
		array('1410','已出关'),			//14   Jie 20161209
	),
);		