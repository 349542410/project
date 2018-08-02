<?php
$_rounter = array(
	'URL_MODEL'         	=> '2',
	'APP_SUB_DOMAIN_DEPLOY'   =>    1, 
	'APP_SUB_DOMAIN_RULES'    =>    array(

		'api.man.megao.hk:81'		=> 'Api',
		'pm.app.megao.hk:81'		=> 'Projectmanagement',
		'pm.megao.hk:81'			=> 'Projectmanagement',
		'mgvip.app.megao.hk:81'		=> 'Advert',
		'mgvip.megao.hk:81'			=> 'Advert',
		'pay.megao.hk:81'			=> 'Advert',
		'mgcrm.app.megao.hk:81'		=> 'Crm',
		'mgcrm.megao.hk:81'			=> 'Crm',
		'weixin.megao.hk:81'		=> 'Crm',
		'mkweb.app.megao.hk:81'		=> 'Web',
		'mkweb.megao.hk:81'			=> 'Web',
		'mkuser.app.megao.hk:81'	=> 'WebAdmin',
		'mkuser.megao.hk:81'		=> 'WebAdmin',
		'mkwap.megao.hk:81'		=> 'Wap',


		'kaola.app.megao.hk:82'		=> 'KaoLa',
		'kaola.megao.hk:82'			=> 'KaoLa',
		'admpl.app.megao.hk:82'		=> 'admpl',
		'admpl.megao.hk:82'			=> 'admpl',
		'ecadmpl.app.megao.hk:82'	=> 'ecadmpl',
		'ecadmpl.megao.hk:82'		=> 'ecadmpl',
		'kefu.app.megao.hk:82'		=> 'Kefu',
		'kefu.megao.hk:82'			=> 'Kefu',
		'payment.app.megao.hk:82'	=> 'Payment',
		'payment.megao.hk:82'		=> 'Payment',
		'goods.app.megao.hk:82'		=> 'Goods',
		'goods.megao.hk:82'			=> 'Goods',

		'customs.app.megao.hk:82'	=> 'Customs',


		'mkadmin.app.megao.hk:83'	=> 'Admin',
		'mkadmin.megao.hk:83'		=> 'Admin',
		'mkapi.app.megao.hk:83'		=> 'Api',
		'mkapi.megao.hk:83'			=> 'Api',
		'erpapi.app.megao.hk:83'	=> 'Api',   //ERP调用Api的网址，隔离方便config不同设置 Man161110		
		'erpapi.megao.hk:83'		=> 'Api',   //ERP调用Api的网址，隔离方便config不同设置 Man161110	
		'pic.megao.hk:83'			=> 'Pic',	//掇影部的上传空间
		'mkuser.megao.hk:83'		=> 'WebUser',
		'mkuserwx.megao.hk:83'		=> 'WebUser',
		'pay.megao.hk:83'			=> 'WebUser',


		'app6.megao.hk:86'			=> 'Home', 

		'mkuser.app.megao.hk:86'	=> 'WebUser',
		'mkuser.megao.hk:86'		=> 'WebUser',
		'api.megao.hk:86'			=> 'Api',		

		'app.megao.hk:887'			=> 'Home', 


		'api.app.megao.hk:888'		=> 'Api', //与ERP打MK单对接 	
		'home.megao.hk:888'			=> 'Home',
		'home.app.megao.hk:888'		=> 'Home',

		//也可以使用'api.man'，但这样就会将所有端口 相同的域名指向Api
		//'*'			=>'Home',
	),
	'URL_ROUTER_ON'   		=> true, 
	'URL_ROUTE_RULES'=>array(
		':id\d'     		=> 'Index/:1',
		'/:id\d'    		=> 'Index/:1',
		"/^s(\d+)$/"	=> 'Exshop/',
		"/^s(\d+)\/(\d+)$/"	=> 'Exshop/',
		"/^s(\d+)\/(\d+)\/(\d+)$/"	=> 'Exshop/',
		"/^I(\d+)$/"				=> 'Idcollection/',
		"m"							=> 'Idcollection/',
		"g"							=> 'Idcollection/',		
	),   
	'LOAD_EXT_CONFIG' 		=> 'db', //读取conf/db.php
);