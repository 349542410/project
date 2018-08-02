<?php
//服务器上可能会变动的参数都写在这里，
//此文件里面的配置 不可上传 到服务器  
return array(

	'DB_PARAMS'             =>    array(\PDO::ATTR_CASE => \PDO::CASE_NATURAL),
	'RAPIURL' =>API_URL,	//读取行为的url
	'WAPIURL' =>API_URL,	//操作行为的url

	//发送数据到快递100 参数配置
	'KD'=>array(
		'KD100KEY'=>'2b6d3et5s65u98a',
		'CALLBACKURL'=>'http://get.here.cn/',
		'POSTURL'=>'http://192.168.1.13/kd100/',
	),

	/*邮件设置 文件导出后执行邮件发送*/ 
	'EMAIL_SET2'=>array(
        'EMAIL_COPE_TO'=>'dev@meiquick.com',    //抄送邮箱
        'EMAIL_COPE_NAME'=>'meiquickdev',          //抄送人显示名字
	),

	//会员资料上传的图片路径设置
	'PIC_AUTH' => WU_FILE,
	
	// 测试的时候用 20161109 Jie
    'Test' => array(
        'sendemail' => false,// true的时候关闭发送邮件功能
    ),

    // 20161114 jie
    // define('UPLOAD_PATH',"D:\sys\www\upfiles\c\Admin"),// 项目路径 不以/结尾
	'UPLOADS'          =>'/Uploads/',	//申通物流管理--申通转EMS文件上传的保存路径
	'UPLOADS_STNO_CSV' =>'/Uploads_STNO_CSV/',	//导入申通号文件上传的保存路径
	'UPLOADS_ID_IMG'   =>'/UPLOADS_ID_IMG/',	//身份证图片上传的保存路径

	// 允许物流 节点推送 的线路ID配置
	'Logistics_Node_Set' => array(
		'MKBc3_Transit' => '17',//20171020 Jie 美快优选3线路对应的id
	),

	// 用于显示后台的重量单位
	'SHOW_WEIGHT_UNIT' => 'lb',
	
	//超级管理员id,拥有全部权限,只要用户uid在这个角色组里的,就跳出认证.可以设置多个值,如array('1','2','3')
	'ADMINISTRATOR'=>$GLOBALS['globalConfig']['ADMINISTRATOR'],
	
	//权限允许与禁止访问系统的权限群组ID与个人用户ID 
	'ALLOW_PROHIBIT_AUTH' => array(
		'0'		=> array(),					//禁止权限组访问ID集合
		'1'		=> array(),					//禁止个人访问ID集合
    	'2'		=> array(),					//允许个人访问ID集合
    	'3'		=> array(),					//允许权限组访问ID集合
	),

	/*===============================上传图片服务器配置	start==================================*/
	
	'BUCKET_LIST'	=> 'text',   //Bucket列表下存储图片的文件夹名称-------存放原图
	'BUCKET_THUM'	=> 'text_thum',   //Bucket列表下存储图片的文件夹名称-------存放缩略图
	
	'BUCKET_URL'	=> '',   //Bucket列表下存储图片的文件夹名称
	
	'IDCARD_ADD_STATUS' => true,    //是否开启已处理身份证--身份证添加	无设置则不开启     设置 true 为开启
	'IDCARD_COS'        => '',      //是否开启服务器COS存储合成图片 不开启保存本地服务器   默认不开启  设置true 为开启
	
	/*===============================上传图片服务器配置	start==================================*/
    // 20161019 Jie 后台主动获取顺丰物流信息 默认配置
    'GetLogis_Config' => array(
        // 配置
        'customerCode'   => 'OSMS_215',         // 客户编码 必需
        'checkword'      => '350e4437b96d', // IBS系统为客户分配的密钥 必需
        'pmsLoginAction' => "http://osms.sf-express.com/osms/services/OrderWebService?wsdl",    // 请求地址 必需
        'lang'           => 'zh_CN',            // 语言
        'tracking_type'  => '1',                // 1.根据顺丰运单号查询; 2.根据客户订单号查询; 3.在IBS查询，不区分运单号和订单号
        'no'             => 'STNO',         // STNO：顺丰运单号; MKNO：客户订单号(即我方的美快单号)
        'TranKd'         => 5,              // 4：表示顺丰
        'sUrl'           => API_URL."/Application/PHP/Ex_common_files/common_server.php",   // 跨文件操作数据保存
    ),

    //发送数据到快递100 参数配置
    'KD100'	=> array(
        'KD100KEY'		=> 'yGeTTjxt4884',
        'CALLBACKURL'	=> API_URL.'/kdcbmeiquickcn/kd100/express1.php',
        'POSTURL'		=> 'http://www.kuaidi100.com/poll',
        'KD_NUM_LITMIT' => 88,	//发送快递100 数量限制
    ),


    // 20161114 jie
    define('UPLOAD_PATH',"C:/temp/phpupload"),// 项目路径 不以/结尾
    // 手动获取顺丰物流信息的请求地址
    'Get_SF_Logis' =>API_URL . "/Application/PHP/sfusmkil2/handle.php",// sfus/handle.php 必须
    /*'ST_Transit' => '1',//20161219 Jie 申通物流线路对应的id
    'SF_Transit' => '5',//20161219 Jie 顺丰物流线路对应的id
    'HkEms_Transit' => '6',//20170106 Jie 香港邮政线路对应的id
    'GdEms_Transit' => '7',//20170106 Jie 广东邮政线路对应的id
    */
    // 手动获取 香港E特快 物流信息的请求地址 170119
    'Get_HkEms_Logis' => API_URL . "/Application/PHP/HkEms/handle.php",// HkEms/handle.php 必须  香港E特快

    'Get_MkBc2_Logis' => API_URL . "/Application/PHP/MkBc2/handle.php",// MkBc2/handle.php 必须  美快BC优选2 20170419 jie
    'Get_MkBcCC_Logis' => API_URL . "/Application/PHP/MkBcCC/handle.php",// 美快优选CC 20170712 jie
    // 手动获取 顺丰/香港E特快/美快BC优选2 物流信息的请求地址
    'Get_Logis' => array(
        'url' => API_URL . "/Application/PHP/%1\$s/handle.php", //地址
        'ids' => array(
            '2'  => 'sfus',// sfus/handle.php 必须  顺丰   完整地址例子：http://test3.megao.hk:83/php83/sfus/handle.php
            '5'  => 'sfusmkil2',// sfus/handle.php 必须  顺丰   完整地址例子：http://test3.megao.hk:83/php83/sfus/handle.php
            '6'  => 'HkEms',// HkEms/handle.php 必须  香港E特快
            '9'  => 'MkBc2',// MkBc2/handle.php 必须  美快BC优选2
            '10'  => 'MkBc2',// MkBc2/handle.php 必须  美快BC优选2
            '12' => 'MkBcCC',// MkBcCC/handle.php 必须  美快优选CC
            '17' => 'zhongtong',// zhongtong/handle.php 必须  中通
        ),
    ),
    // 2017-04-28 jie
    'Transit_Type' => array(
        'ST_Transit'    => '1',//20161219 Jie 申通物流线路对应的id
        'SF_Transit'    => '5',//20161219 Jie 顺丰物流线路对应的id
        'HkEms_Transit' => '6',//20170106 Jie 香港邮政线路对应的id
        'GdEms_Transit' => '7',//20170106 Jie 广东邮政线路对应的id
        'MKBc2_Transit' => '10',//20170413 Jie 美快BC优选2线路对应的id
        //'MKBc3_Transit' => '11,13,14,15,16,17,18,10',//20170610 Jie 美快优选3线路对应的id
        'MKBc3_Transit' => '11,17,18,10,19,20,22,23',//20170610 Jie 美快优选3线路对应的id
        'MKBcCC_Transit' => '12',//20170712 Jie 美快优选CC线路对应的id
    ),
    // MKBc2Controller的基本配置
    'MKBc2_config'=> array(
        'ecCompanyId'    => 'MEIJIE',   //电商标识
        'partnered'      => 'Pz75VDDqjkz8',    //数字签名
        'url'            => 'http://211.156.198.65:6869/MEIJIE/HttpService',   //发送地址
        'prov'           => 'USA',  //发件人所在省
        'city'           => 'San Francisco',//'三潘市',  //发件人所在市县（区），市区中间用“,”分隔；注意有些市下面是没有区
        'exports_switch' => true,   //是否生成一个txt文件
        'xmlsave'        => API_ABS_FILE.'/FJEMS/',//生成xml保存到文件

    ),

    //揽收软件用的密匙
    'MkWl2Key' => 'megao',
    //美快揽收系统配置项
    'MkReceive_Set' => array(
        'time_out' => '18000', //300分钟
        'MkWl2Key' => 'megao', //揽收软件用的密匙
    ),

    //权限管理设置 	end

    'AutoSys_Set' => array(
        'Error_Notes'      => UPFILEBASE . '/MkAutoTesting/', //新自助、揽收，报错记录日志的位置
        'AutoReceiveLogs'  => 'Receive/',//揽收日志文件名
        'AutoPrintSysLogs' => 'PrintSys/',//自助日志文件名
    ),

    'US_TO_RMB_RATE' => getConfig('app.pay_exchange_rate', $GLOBALS['globalConfig']['US_TO_RMB_RATE']), //美元和人民币汇率
);