<?php
	return array(
		'RAPIURL'           => AUAPI_URL,	//读取行为的url
		'WAPIURL'           => AUAPI_URL,	//操作行为的url

		// 'pay_do'		 	=> 'http://pay.megao.hk:83/', // 支付宝支付成功后自动跳转地址

		// PDA专属配置
		'PDA_URL'  => API_URL,	//POST url
		'jconf' => array(
			'appID' => '65412888',
			'Key'   => 'c260d0ed79c711e4b8ae382c4a62e14e',
        ),
		// PDA专属配置

		// 微信登录
		// 'PCWXURL' 			=> 'http://mkweb.megao.hk?token=', 	// 生成二维码的网址
		// 'wxcallback'		=> 'Tokenpcwx/callback',	 		// 回调的网址

		//自助打印终端配置项
		'Print_Sys_Set'  =>  array(
			'time_out'    => 300,//超时时间 秒 (5分钟)
			'wait_time'   => '120',//输入密码等待时间 秒
			'private_key' => 'www.meiquick.com',//字符串加密解密密匙
			'MkWl2Key'    => 'megao',//打印用的密匙
		),

		//美快揽收系统配置项
		'MkReceive_Set' => array(
			'time_out' => 1800, //30分钟
			'MkWl2Key' => 'megao', //揽收软件用的密匙
		),

		//称重
		'WMD' => array(
			'WeightSwitch' => 'off',
			'CONTENT' => "%3\$s您好,您购买的商品已进入%1\$s,单号为%2\$s.【美快国际物流】",
		),
		//转发快递
		'CMD' => array(
			'CommunicateSwitch' => 'off',
			'CONTENT' => "您购买的商品已从%1\$s发出,由%2\$s承运,单号为%3\$s.【美快国际物流】",
		),
	
		/*邮件设置*/
		// 'EMAIL_SET'=>array( 
		// 	'HOST'       =>"smtp.exmail.qq.com", 		//SMTP服务器	请注意发送邮件的邮箱是否开启了SMTP功能，未开启则无法使用第三方发送功能
		// 	'PORT'       =>465, 							//邮件发送端口
		// 	'EMAIL_USER' =>'m.all-purpose@megao.cn', 	      	//发件人邮箱地址
		// 	'EMAIL_PWD'  =>'Purpose123', 					//发件人邮箱密码
		// 	'TITLE'      =>'re_Your_title',//'密码重置',					//邮件标题
		// 	'FROMNAME'   =>'re_Meikuai',//'美快物流',					//发件人姓名
		// 	'MAXTIME'    =>30,							//时限(分钟)
		// ),

		//此2项配置，更改的时候，需要通知 物流会员后台 的同事进行同时修改
		'RMB_Free_Duty'  => $GLOBALS['globalConfig']['RMB_Free_Duty'], //人民币免税金额额度
        'PAY_TEST_USER_ID'  => $GLOBALS['globalConfig']['PAY_TEST_USER_ID'], //测试用户id配置
        'US_TO_RMB_RATE' => getConfig('app.pay_exchange_rate', $GLOBALS['globalConfig']['US_TO_RMB_RATE']), //美元和人民币汇率
        
		// // 支付方式 0未激活, 1已激活可用
		// 'Pay_Kind' => array(
		// 	'wechat'      => '0',//微信    充值用
		// 	'alipay'      => '1',//支付宝  充值用
		// 	'balance'     => '1',//余额	   订单支付用
		// 	'cash'        => '1',//现金    充值用
		// 	'credit_card' => '1',//信用卡  充值用
		// 	'other'       => '1',//其他    充值用
		// 	'paypal'      => '1',//paypal    充值用
		// ),

		// 'TXT_NOTE' => API_ABS_FILE, //支付日志 储存路径

		// 会员后台的充值/下单计费标准  20170509 jie
		// 此项配置，更改的时候，需要通知 物流会员后台 的同事进行同时修改
		// 'Web_Config' => array(
		// 	//各线路的ID对应各自的配置
		// 	'1' => array(
		// 		'Discount'  => '0',	//折扣比例
		// 		'Charge'    => '0',	//服务费
		// 		'Weight'    => '2',	//首重重量  磅
		// 		'Price'     => '16.00',	//首重价格
		// 		'Unit'      => '0.1',	//续重计费单位
		// 		'UnitPrice' => '0.8',		//续重每单位金额
		// 	),
		// 	'2' => array(
		// 		'Discount'  => '0',	//折扣比例
		// 		'Charge'    => '0',	//服务费
		// 		'Weight'    => '1',	//首重重量
		// 		'Price'     => '5.00',	//首重价格
		// 		'Unit'      => '0.1',	//续重计费单位
		// 		'UnitPrice' => '0.5',		//续重每单位金额
		// 	),
		// 	'3' => array(
		// 		'Discount'  => '0',	//折扣比例
		// 		'Charge'    => '0',	//服务费
		// 		'Weight'    => '2',	//首重重量
		// 		'Price'     => '19.00',	//首重价格
		// 		'Unit'      => '0.1',	//续重计费单位
		// 		'UnitPrice' => '0.6',		//续重每单位金额
		// 	),
		// 	'5' => array(
		// 		'Discount'  => '0',	//折扣比例
		// 		'Charge'    => '0',	//服务费
		// 		'Weight'    => '1',	//首重重量
		// 		'Price'     => '5.00',	//首重价格
		// 		'Unit'      => '0.1',	//续重计费单位
		// 		'UnitPrice' => '0.5',		//续重每单位金额
		// 	),
		// 	'11' => array(
		// 		'Discount'  => '0',	//折扣比例
		// 		'Charge'    => '0',	//服务费
		// 		'Weight'    => '1',	//首重重量
		// 		'Price'     => '5.00',	//首重价格
		// 		'Unit'      => '0.1',	//续重计费单位
		// 		'UnitPrice' => '0.5',		//续重每单位金额
		// 	),
		// 	'12' => array(
		// 		'Discount'  => '0',	//折扣比例
		// 		'Charge'    => '0',	//服务费
		// 		'Weight'    => '1',	//首重重量
		// 		'Price'     => '10.00',	//首重价格
		// 		'Unit'      => '0.1',	//续重计费单位
		// 		'UnitPrice' => '0.6',		//续重每单位金额
		// 	),
		// 	'14' => array(
		// 		'Discount'  => '0',	//折扣比例
		// 		'Charge'    => '0',	//服务费
		// 		'Weight'    => '1',	//首重重量
		// 		'Price'     => '10.00',	//首重价格
		// 		'Unit'      => '0.1',	//续重计费单位
		// 		'UnitPrice' => '0.6',		//续重每单位金额
		// 	),
		// 	'15' => array(
		// 		'Discount'  => '0',	//折扣比例
		// 		'Charge'    => '0',	//服务费
		// 		'Weight'    => '1',	//首重重量
		// 		'Price'     => '10.00',	//首重价格
		// 		'Unit'      => '0.1',	//续重计费单位
		// 		'UnitPrice' => '0.6',		//续重每单位金额
		// 	),
		// 	'16' => array(
		// 		'Discount'  => '0',	//折扣比例
		// 		'Charge'    => '0',	//服务费
		// 		'Weight'    => '1',	//首重重量
		// 		'Price'     => '10.00',	//首重价格
		// 		'Unit'      => '0.1',	//续重计费单位
		// 		'UnitPrice' => '0.6',		//续重每单位金额
		// 	),
		// ),

		// 'UPLOADS_ID_IMG'   =>'/UPLOADS_ID_IMG/',	//身份证图片上传的保存路径
		
		// // PDA要用
		// 'DB_TYPE'           	=> 'mysql', // 数据库类型
		// 'DB_DSN'            	=> 'mysql:host=localhost;dbname=mkil;charset=utf8',
		// 'DB_USER'           	=> 'mkiluser',
		// 'DB_PWD'            	=> 'mk12345678', // 密码
		// 'DB_PREFIX'         	=> 'mk_', // 数据库表前缀
		// //后缀
		// 'URL_HTML_SUFFIX'   	=> '',
		// 'URL_MODEL'				=> 2,
		// 'SESSION_AUTO_START'    => true,
		// 'SESSION_EXPIRE'		=> 10,

        'MKAUTO_AUTH_REDIRECT' => isset($GLOBALS['globalConfig']['MKAUTO_AUTH_REDIRECT']) ? $GLOBALS['globalConfig']['MKAUTO_AUTH_REDIRECT'] : 'http://wap.dev.meiquick.com/Index/mkAutoRedirect'
	);