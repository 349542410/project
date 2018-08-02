<?php
	// define('UPLOAD_PATH',"D:/sys/www/upfiles/a/Web");
//	define('NewUrl',"http://mkuser.megao.hk:83/");
	return array(
		'RAPIURL'           => API_URL,	//读取行为的url
		'WAPIURL'           => API_URL,	//操作行为的url
		'AUTONURL'          => AUAPI_URL, //自助终端打印API网址
		'WAPURL' 					=> WAP_URL,
		'WebUrl'			=> WEB_URL,
		// 'WebUrl'			=> 'http://user.loc.mk:891/',
		'pay_do'		 	=> $GLOBALS['globalConfig']['PAY_DO'], // 支付宝支付成功后自动跳转地址
		// 微信登录
		'PCWXURL' 			=> 'http://mkweb.megao.hk?token=', 	// 生成二维码的网址
		'wxcallback'		=> 'Tokenpcwx/callback',	 		// 回调的网址

		//自助打印终端配置项
		'Print_Sys_Set'  =>  array(
			'time_out'    => '300',//超时时间
			'wait_time'   => '120',//输入密码等待时间
			'private_key' => 'www.meiquick.com',//字符串加密解密密匙
			'MkWl2Key'    => 'megao',//打印用的密匙
		),

		/*邮件设置*/
		'EMAIL_SET'=>array( 
			// 'HOST'       =>"smtp.exmail.qq.com", 			//SMTP服务器	请注意发送邮件的邮箱是否开启了SMTP功能，未开启则无法使用第三方发送功能
            'EMAIL_USER' =>'m.all-purpose@megao.cn', 	    //发件人邮箱地址
            'EMAIL_PWD'  =>'Purpose123', 					//发件人邮箱密码
            'HOST'       =>"smtp.exmail.qq.com", 		//SMTP服务器	请注意发送邮件的邮箱是否开启了SMTP功能，未开启则无法使用第三方发送功能
            'PORT'       => 465, 							//邮件发送端口
//            'EMAIL_USER' =>'dev@meiquick.com', 	      	//发件人邮箱地址
//            'EMAIL_PWD'  =>'MKil12345678', 					//发件人邮箱密码
            'TITLE'      =>'密码重置',					//邮件标题
            'FROMNAME'   =>'美快物流',					//发件人姓名
            'MAXTIME'    => 30,							//时限(分钟)
		),

		'RMB_Free_Duty'  => $GLOBALS['globalConfig']['RMB_Free_Duty'], //人民币免税金额额度
		
		// 支付方式 0未激活, 1已激活可用
		'Pay_Kind' => array(
			'wechat'      => '0',//微信    充值用
			'alipay'      => '1',//支付宝  充值用
			'balance'     => '1',//余额	   订单支付用
			'cash'        => '1',//现金    充值用
			'credit_card' => '1',//信用卡  充值用
			'other'       => '1',//其他    充值用
			'paypal'      => '1',//paypal    充值用
		),

		//'qrcode_url' => 'http://www.megaoshop.com/testcode/html/image.php?filetype=PNG&dpi=72&scale=3&rotation=0&font_family=0&font_size=8&text={**}&thickness=30&start=NULL&code=BCGcode128',
		'qrcode_url' => 'http://qcode.mk.cc/testcode/html/image.php?filetype=PNG&dpi=72&scale=3&rotation=0&font_family=0&font_size=8&text={**}&thickness=30&start=NULL&code=BCGcode128',

		//短网址
		'ID_URL' 	=> WEB_URL.'/Idcollection/',
		'ID_URL_M' 	=> WAP_URL.'/Idcollection/',
		//补填身份证信息地址
		'supplement_info_url' => WEB_URL.'/Profile/index/',

		//发送短信给用户补充上传身份证图片
		'send_user' => '%s，您的包裹将从美国发出，为免延误配送，请尽快到 %s 上传身份证',

		'tmp_download_path' => $_SERVER['DOCUMENT_ROOT'].'/File',

		'TXT_NOTE' => API_ABS_FILE, //支付日志 储存路径


		'UPLOADS_ID_IMG'   =>'/UPLOADS_ID_IMG/',	//身份证图片上传的保存路径
//		'Redis'				=> array(
//			'Host'	=> '127.0.0.1',
//			'Port'	=> 6379,
//			'Auth'	=> 'mkil111',
//		),

		// 'OPEN_REDIS' => true,		//是否开启redis

        'FILTER_COURIER_NAME' => '顺丰|中通|圆通|申通|韵达|百世|德邦|优速|天天快递|汇通',
        'US_TO_RMB_RATE' => getConfig('app.pay_exchange_rate', $GLOBALS['globalConfig']['US_TO_RMB_RATE']), //美元和人民币汇率
	);