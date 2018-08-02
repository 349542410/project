<?php
global $globalConfig;
return array(
    'SHOW_PAGE_TRACE'=>true,

    'LOAD_EXT_CONFIG' => 'db',

    //数据库配置信息
    'DB_TYPE'   => 'mysql', // 数据库类型
    'DB_HOST'   => 'localhost', // 服务器地址
    'DB_NAME'   => 'mkil', // 数据库名
    'DB_USER'   => 'root', // 用户名
    'DB_PWD'    => 'root', // 密码
    'DB_PORT'   => 3306, // 端口
    'DB_PREFIX' => 'mk_', // 数据库表前缀
    'DB_CHARSET'=> 'utf8', // 字符集
    'DB_DEBUG'  =>  TRUE, // 数据库调试模式 开启后可以记录SQL日志 3.2.3新增

    'DEFAULT_MODULE' => '',
    'MODULE_ALLOW_LIST' => array('Admin','Api','WebUser','Web','AUApi','MkAuto','Wap','WxUser'),

    'APP_SUB_DOMAIN_DEPLOY'   =>    1, // 开启子域名或者IP配置
    'APP_SUB_DOMAIN_RULES'    =>  $globalConfig['APP_SUB_DOMAIN_RULES'] ,

    // Redis 配置
    'Redis'				=> array(
        'Host'	=> '127.0.0.1',
        'Port'	=> 6379,
        'Auth'	=> 'mkil111',
    ),
    'COS' => array(
        'app_id' => '',
        'secret_id' => '',
        'secret_key' => '',
        'region' => 'gz',       // bucket所属地域：华北 'tj' 华东 'sh' 华南 'gz',详情请参考API文档
        'timeout' => 60,        // 请求超时时间
        'bucket' => '',
    ),
    //腾讯云身份证识别
    'OCR' => array(
        'app_id' => '',
        'secret_id' => '',
        'secret_key' => '',
        'bucket' => '',
    ),

    //阿里云身份证识别
    'ALIOCR' => array(
        'appcode'    => '8b7ec2512e0944a48d89f46b31250840',
        'URL'         => 'https://dm-51.data.aliyun.com/rest/160601/ocr/ocr_idcard.json',
    ),
    //阿里云身份证名字 + 身份证号码识别
    'ALIIDCARD' => array(
        'appcode'    => '8b7ec2512e0944a48d89f46b31250840',
        'URL'         => 'http://idcard.market.alicloudapi.com/lianzhuo/idcard',
    ),

    //Redis Session配置
    'SESSION_AUTO_START'=>  true,// 是否自动开启Session
    'SESSION_TYPE'=>  'Redis',//session类型
    'SESSION_PERSISTENT'    =>  1,//是否长连接(对于php来说0和1都一样)
    'SESSION_CACHE_TIME'=>  1,//连接超时时间(秒)
    'SESSION_EXPIRE'=>  0,//session有效期(单位:秒) 0表示永久缓存
    'SESSION_PREFIX'=>  'sess_',//session前缀
    'SESSION_REDIS_HOST'=>  '127.0.0.1,192.168.1.225', //127.0.0.1,192.168.1.225 分布式Redis,默认第一个为主服务器
    'SESSION_REDIS_PORT'=>  '6379',       //端口,如果相同只填一个,用英文逗号分隔
    'SESSION_REDIS_AUTH'    =>  '123456789',    //Redis auth认证(密钥中不能有逗号),如果相同只填一个,用英文逗号分隔 );

);