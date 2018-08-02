<?php
return array(
	//'配置项'=>'配置值'
	 'SHOW_PAGE_TRACE'   =>false,// 显示页面Trace信息

	//ThinkPHP3.2.3开始，规范起见，默认的数据库驱动类设置了 字段名强制转换为小写，如果你的数据表字段名采用大小写混合方式的话，需要在配置文件中增加如下设置：
	'DB_PARAMS'    =>    array(\PDO::ATTR_CASE => \PDO::CASE_NATURAL),//

    //后缀
    'URL_HTML_SUFFIX'   => '',
    'URL_CASE_INSENSITIVE'  =>  false,
    'ERROR_PAGE' 		=> '/Public/error.html',
    //'Kdno_Path'=>'D:/wwwroot/public_html/mkil/public_html/mkApi/Api/Controller/Kdno',
    'FILTER_COURIER_NAME' => '顺丰|中通|申通|圆通|韵达|百世|德邦|优速|天天快递|汇通',


    //发送数据到快递100 参数配置
    'KD100'	=> array(
        'KD100KEY'		=> 'yGeTTjxt4884',
        'CALLBACKURL'	=> API_URL.'/kdcbmeiquickcn/kd100/express1.php',
        'POSTURL'		=> 'http://www.kuaidi100.com/poll',
        'KD_NUM_LITMIT' => 88,	//发送快递100 数量限制
    ),
    'LANG_SWITCH_ON' 		=> true,   				// 开启语言包功能
    'LANG_AUTO_DETECT' 		=> true, 				// 自动侦测语言 开启多语言功能后有效
    'LANG_LIST'        		=> 'zh-cn,en-us,zh-tw', // 允许切换的语言列表 用逗号分隔
    'VAR_LANGUAGE'     		=> 'l', 				// 默认语言切换变量
    'DEFAULT_LANG'		    => 'zh-cn',				// 默认语言，开启了自动侦测语言的情况下，此功能无效
);