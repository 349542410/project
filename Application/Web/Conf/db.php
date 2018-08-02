<?php
define('UPLOAD_PATH',"/mg/www/vip/public_html/meiquick_files");
return array(
	'RAPIURL'           =>API_URL,
	'WAPIURL'           =>API_URL,
	'APIURL'   			=> API_URL,	//上传身份照片的url20180112
	// 20170309 修改伦
	"__MKUSER__" => MEMBER_URL, //会员登录网址
	'UPLOADS_ID_IMG'   =>'/sfzpic/',	//身份证图片上传的保存路径 20180112
	/*邮件设置*/ 
  'EMAIL_SET'=>array( 
		'HOST'       =>"smtp.exmail.qq.com", 			//SMTP服务器请注意发送邮件的邮箱是否开启了SMTP功能，未开启则无法使用第三方发送功能
		'PORT'       =>25, 						//邮件发送端口
		'EMAIL_USER' =>'m.all-purpose@megao.cn', 	      //发件人邮箱地址
		'EMAIL_PWD'  =>'Purpose123', 			//发件人邮箱密码
		'TITLE'      =>'密码重置',		//邮件标题
		'FROMNAME'   =>'美快物流',		//发件人姓名
		'MAXTIME'    =>30,		//时限(分钟)
	),
  	// 微信登录
	'PCWXURL' 		=> 'http://mkweb.megao.hk?token=', // 生成二维码的网
	'wxcallback'	=> 'Tokenpcwx/callback',	 // 心跳的网址
	'supplement_info_url' => WEB_URL.'/Profile/index/', //短网址跳转的网址
);
