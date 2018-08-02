<?php
global $globalConfig;

return array(
    //'配置项'             	=>'配置值'
    // 'DEFAULT_MODULE'    	=>    'Wxpc',  // 默认模块
    // 'TMPL_FILE_DEPR' => '_',

    //后缀
    'URL_HTML_SUFFIX'   	=> '',
    'URL_MODEL'				=> 2,
    'SESSION_AUTO_START'    => true,
    'SESSION_EXPIRE'		=> 10,
    'SESSION_OPTIONS'		=> array('expire'=>'1000'),
    'SHOW_PAGE_TRACE'   	=> ($_SERVER['SERVER_NAME']!=='erpapi.app.megao.hk'),// 显示页面Trace信息


    'LOGS_SET' => array(
        'LIMIT' 		=> 100,	//操作数量限制
        //'URL' 			=> 'http://s.mk.vip8801.com/Other/GetMultMKState.ashx',	//发送地址
        //'URL' 			=> 'http://service.erp.megao.cn:9033/Other/GetMultMKState.ashx',	//发送地址   服务器使用
        'URL' => $GLOBALS['globalConfig']['LOGS_SET_URL'],	//发送地址  本地测试使用

    ),

    //配置同步erp物流信息域名
    'MESSAGE' => $globalConfig['MESSAGE'],
    'US_TO_RMB_RATE' => getConfig('app.pay_exchange_rate', $GLOBALS['globalConfig']['US_TO_RMB_RATE']), //美元和人民币汇率
    
);