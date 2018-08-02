<?php
	/* //美国顺丰测试
	$sfconfig = array(
		'head' 		=> 'OSMS_1',
		'checkword' => 'fc34c561a34f ', 
		'host' 		=> 'http://osms.sit.sf-express.com:2080/osms/services/OrderWebService',
	);*/
	//天津顺丰
	$sfconfig = array(
		'head' 		=> '0224702759',
		'checkword' => 'fsgjxfDPPCo7IXmU6Hpx3tOVz49DVRfX', 
		'host' 		=> 'http://bsp-oisp.sf-express.com/bsp-oisp/sfexpressService',
	);
	
	//这个无法测试了
	$sfconfig2 = array(
		'head' 		=> '0224702759',
		'checkword' => 'fsgjxfDPPCo7IXmU6Hpx3tOVz49DVRfX', 
		'host' 		=> 'http://218.17.248.244:11080/bsp-oisp/sfexpressService', //域名与path,测试与正式版不同
	);