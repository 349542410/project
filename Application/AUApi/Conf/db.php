<?php
global $globalConfig;

return array(

    //后缀
    'URL_HTML_SUFFIX'   	=> '',
    'URL_MODEL'				=> 2,
//		'SESSION_AUTO_START'    => true,
//		'SESSION_EXPIRE'		=> 10,
//		'SESSION_OPTIONS'		=> array('expire'=>'10'),

    //短信发送账号设置
    'MESSAGE' => $globalConfig['MESSAGE'],

    //新自助、揽收，报错记录日志文件
    'AutoSys_Set' => array(
        'Error_Notes'      => LOG_URL.'/', //新自助、揽收，报错记录日志的位置
        'AutoReceiveLogs'  => 'AutoReceiveLogs.txt',//揽收日志文件名前缀
        'AutoPrintSysLogs' => 'AutoPrintSysLogs.txt',//自助日志文件名前缀
    ),
    'Allow_Error_Value'=> '0.01', // 揽收系统重新计费的时候，金额的比较的允许误差值

    //顺丰资料
    'SF'					=> array(
        'head' 		=> 'BSPdevelop',
        'checkword' => 'j8DzkIFgmlomPt0aLuwU',
        'host' 		=> 'http://218.17.248.244:11080',
        'info'		=> array(
            'j_company'     => '美购商城',
            'j_contact'     => '魏先生',
            'j_tel'         => '18622185790',
            'j_mobile'      => '18622185790',
            'j_country'     => '中国',
            'j_province'    => '天津',
            'j_city'        => '天津市',
            'j_county'      => '东丽区',
            'j_address'     => '滨海机场TCS仓库 美快国际天津中转仓',
            'custid'        => '',  //44012586顺丰月结卡号
        )
    ),
    'KDNOPATH'				=> 'D:www/root/conct/api/'. 'Application/AUApi/Controller/KdnoConfig/Kdno.php',
    'US_TO_RMB_RATE' => getConfig('app.pay_exchange_rate', $GLOBALS['globalConfig']['US_TO_RMB_RATE']), //美元和人民币汇率
);