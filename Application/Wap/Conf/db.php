<?php
return array(
	'RAPIURL'           =>API_URL,	//读取行为的url
	'UPLOADS_ID_IMG'   =>'/UPLOADS_ID_IMG/',	//身份证图片上传的保存路径
	'supplement_info_url' => '/Index/record/',

    'URL_ROUTER_ON'   => true, 
    'URL_ROUTE_RULES'=>array(
        '/^([a-z]{1}\d{6})$/' => 'Index/confirm?mkno=:1',
    ),
);