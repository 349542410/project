<?php
    require_once ('../config.php');
	// 美快BC优选2 跟踪包裹 配置
	$no             = 'STNO';			// STNO：香港E特快运单号; MKNO：客户订单号(即我方的美快单号)
	$TranKd         = 9;				// 9：表示美快BC优选2
	$limit          = 1; 				// 批量交易个数(即查询数据表的数据条数)
	$limitHour      = 36000;			// 程序操作时限(10个小时)
	$serurl         = LOGISTICS_DOMAIN."/Ex_common_files/common_server.php";	//必要参数
	// $out_load       = '../../Api/Controller';