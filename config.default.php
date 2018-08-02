<?php
return [

    'APP_SUB_DOMAIN_RULES'=> array(
        'admin'         =>'Admin',
        'admin.test'    =>'Admin',
        'admin.dev'     =>'Admin',
        'web'           =>'Web',
        'web.test'      =>'Web',
        'web.dev'       =>'Web',
        'member'        =>'WebUser',
        'member.test'   =>'WebUser',
        'member.dev'    =>'WebUser',
        'api'           =>'Api',
        'api.test'      =>'Api',
        'api.dev'       =>'Api',
        'wap'           =>'Wap',
        'wap.test'      =>'Wap',
        'wap.dev'       =>'Wap',
        'mkauto'        =>'MkAuto',
        'mkauto.test'   =>'MkAuto',
        'mkauto.dev'    =>'MkAuto',
        'auapi'         =>'AUApi',
        'auapi.test'    =>'AUApi',
        'auapi.dev'     =>'AUApi',
    ),

    'RESURL' => 'http://res.mk.cc',  // js/css路径地址 指向res
    'FILEURL' => 'http://file.mk.cc', // 上传文件路径 指向file
    'UPFILEBASE'=> $_SERVER['DOCUMENT_ROOT'].'/File', // 上传文件硬盘路径

    'KDNOPATH'=> $_SERVER['DOCUMENT_ROOT'].'/Application/Api/Controller', // Kdno类加载路径

    'API_URL' => 'http://api.mk.cc', // API模块
    'MEMBER_URL' => 'http://member.mk.cc', // 会员后台模块
    'ADMIN_URL' => 'http://admin.mk.cc', // 会员后台
    'WEB_URL' => 'http://web.mk.cc',  // pc官网模块
    'WAP_URL' => 'http://wap.mk.cc', // wap模块
    'MKAUTO_URL' => 'http://mkauto.mk.cc', // mk自助终端模块
    'AUAPI_URL' => 'http://auapi.mk.cc', // AUApi模块

    // Admin 模块
    'Admin' => [
        'EPAGE' => 20,
    ],

    // Api 模块
    'Api' => [

    ],

    // AUApi 模块
    'AUApi' => [

    ],

    // MkAuto 模块
    'MkAuto' => [

    ],

    // Wap 模块
    'Wap' => [

    ],

    // Web 模块
    'Web' => [

    ],

    // WebUser 模块
    'WebUser' => [

    ],

    'Logistics' => [
        'domain'  => 'http://www.logistic.com', //获取物流信息指向application\PHP
    ],
    'PAY_DO' => 'http://user.test.meiquick.com/',  // 支付宝回调地址
    //超级管理员id,拥有全部权限,只要用户uid在这个角色组里的,就跳出认证.可以设置多个值,如array('1','2','3')
    'ADMINISTRATOR'=>array('1'),

    //支付宝
    'partner'			=> '2088421416276353',
    'app_id' 	=> '2016072801676437',
    'private_key'		=> 'MIICXgIBAAKBgQDb9VNqfUzasN4toNWE7fY26ZvvZO7K1Og5nJLFHxxaqgWNrxXNFV2atQAN3yjS5DxNQoQdtGyjkt+/QDnB+V/5DNPCVeSgE2DnM8t0lncxbQHHah8Atm3tJ+ijtjwAh7rdVAYygZDizXJ1YOX7xFAyrJWMZTvC8OoMdwzxcolxWQIDAQABAoGAdVkIy8NVgUbjAczQnT6nINy5CJr8mtHDoxjZZLkYU3ZpyBEkvGktqx/ti3kHOpvxX/agrYhYfVwaatpE9iuo+yR5p1NeSugGTITS/aCHrBPA3bqciiJxtEzaEUE1SlB/hjRhAzuNy5A5WsUOELBwmSIK78zIK8beSAx/zt/4/EECQQD1XjJNlMyZwLxWglPyjbUA38URQD1jcxMA1R6ECRN8xy7y7DoGYt/ylVEbG/APb2EsVa5EVacKg9aFL2WcgEzNAkEA5X1EQkJs1eaobOJ8qhUIIHoYK4RRhXFqjKgMxavEyfqwuRYMie7YUKaHPvWNyEHG6K/3QlDbYx5gghOz9hO2vQJBAMxbSrwQvSMlQfcvDqnKWkFDHceTYE2Ozvn3hjXjtUZMQo7yLhWZjfllYSqZ5yOD2UPqjHy/daMtUKKWaiOhO9UCQQDSGb1EbEv4CRRpm3FGxbqLATzfmmSIJy3FWJVY48lmoXzp9qXEIkcoj02C9oy3qoDQx0k4DY7NUCJK9H7t616BAkEAtXF0y18eUigiS4+1Wjt3nC4B1xeMFxJdBVX/2vvi4HHvVOkRcu89LVeENNA0s6Lr+zfnbEYk5dFPtLr1ZB7KfA==',
    'alipay_public_key' 	=> 'MIGfMA0GCSqGSIb3DQEBAQUAA4GNADCBiQKBgQCnxj/9qwVfgoUh/y2W89L6BkRAFljhNhgPdyPuBV64bfQNN1PjbCzkIM6qRdKBoLPXmKKMiFYnkd6rAoprih3/PrQEB/VsW8OoM8fxn67UDYuyBTqA23MML9q1+ilIZwBC2AQ2UBVOrFXfFl75p6/B5KsiNG9zpgmLCUYuLkxpLQIDAQAB',

    'WX_NOTIFY_URL' => 'http://user.test.meiquick.com/WxCallBack/callback',  // 终端微信支付异步回调地址

    //此2项配置，更改的时候，需要通知 物流会员后台 的同事进行同时修改
    'US_TO_RMB_RATE' => '7', //7, //美元和人民币汇率
    'RMB_Free_Duty'  => '50', //人民币免税金额额度

    // 物流线路对接的配置合集
    'KDNO_CONFIG' => array(
        '21'=>array(
            'SecretKey'      => 'APvYM8Mt5Xg1QYvker67VplTPQRx28Qt/XPdY9D7TUhaO3vgFWQ71CRZ/sLZYrn97w==',    //系统为客户分配的密钥
            'AccessToken'    => 'ANfX2gSY14IPwZ8B/cmOfaGxfeW3PMdhCXKMjfrBUinUCJn/9nEDSlupkgnp1lZhDw==',    //系统为客户分配的密钥
            'APPKEY'         => 'c829241a-5a8d-467b-b3e3-85cbc20d8317',    //系统为客户分配的密钥
            'pmsLoginAction' => 'http://bill.open.xlobo.com/api/router/rest',    //系统为客户分配的密钥
        ),
        '22'=>array(
            'cuscode'        => 'MKGJWL',   //仓库标识
            'sitecode'       => 'MJCO01',    //户代码
            'key'            => 'fc2658f96e4c4bfd98a31efaa6ef4606', //客户秘钥
            'pmsLoginAction' => 'http://api.esd.topideal.com/bs/s_interface.aspx',    //地址
        ),
    ),

    // 发送数据到ERP 线上服务器  http://service.erp.megao.cn:9033/Other/GetMultMKState.ashx
    'LOGS_SET_URL'=>'http://service.erp.megao.cn:9033/Other/GetMultMKState.ashx',

    'PAY_TEST_USER_ID'  => '', //测试用户支付id配置,多个用户用英文逗号分隔开：1,2,8
    'MKAUTO_AUTH_REDIRECT' => 'http://wap.dev.meiquick.com/Index/mkAutoRedirect', // 终端微信登录授权回调地址

    //短信发送账号设置
    'MESSAGE' => array(
        //申请的短信接口平台
        'HTTP'  => 'http://api.sms.cn/mtutf8/',
        //申请时候的用户账号
        'UID'   => 'a2565855778',
        //申请时候的用户密码 
        'PWD'   => 'Megao347168',
        //'WMD' => "%3\$s您好,您购买的商品已被%1\$s揽收,单号为%2\$s %4\$s 【美快．com】",
        //'WMD'     => "%3\$s您购买的商品已由%1\$s揽收,单号%2\$s,物流: %4\$s 【美快】",
        //'CMD' => "你购买的商品已从%1\$s发出,由%2\$s承运,单号为%3\$s %4\$s 【美快．com】",
        //'CMD'     => "%5\$s您购买的商品已离开%1\$s,由%2\$s(%3\$s)发出,物流: %4\$s 【美快】",

        'WMD'   => "%3\$s您好,您购买的商品已由%1\$s揽收,详情点击 %4\$s 【美快】",
        'WMD2'  => "%3\$s您好,您购买的商品已由%1\$s揽收,请登录 %4\$s 补充完整资料【美快】", //Man160105需补充资料
        'CMD'   => "%5\$s您好,您的商品已离开%1\$s,由%2\$s发出,详情点击 %4\$s 【美快】",

        'LIMIT' => 100,     //每次发送短信的数量限制
        'CREATE_URL' => "m.meiquick.com/",  //生成一个url地址放在短信中发送
        'ALLOWHOST'  => 'mkapi.megao.hk:83',
    ),
];