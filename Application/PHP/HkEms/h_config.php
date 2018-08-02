<?php
    require_once '../config.php';
	// 香港E特快的 跟踪包裹 配置
	$no             = 'STNO';			// STNO：香港E特快运单号; MKNO：客户订单号(即我方的美快单号)
	$TranKd         = 5;				// 5：表示香港E特快
	$limit          = 1; 				// 批量交易个数(即查询数据表的数据条数)
	$limitHour      = 36000;			// 程序操作时限(10个小时)
	$out_load       = '../../Api/Controller';
	$serurl         = LOGISTICS_DOMAIN."/Ex_common_files/common_server.php";	//必要参数
