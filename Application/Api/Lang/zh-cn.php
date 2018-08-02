<?php
return array(
	'lng'		 => 'zh-cn',
	/* 控制器 */
	'NotExist'    => '查无此项',
	'IsEmpty'     => '内容为空',
	'IsWrong'     => 'MD5码不正确',
	'WrongType'   => '数据类型错误',
	'Done'        => '完成',
	'l_empty' => '不能为空',
	'l_max_len' => '长度超过限制',
	'CLIENT_LIST'  	=>		'客户列表',
	'error_meg'    	=>		'数据错误，请重新传输！',
	'auto_Indent'  	=>		'自定义订单',
	'and'          	=>		'和',
	'existed'      	=>		'严重错误：必须检查是否重复发货！',
	'succeed'      	=>		'保存成功',
	'insert_error' 	=>		'订单信息有错，数据保存失败，请重新再试',
	'SHIPIN'     	=>		'包裹已到{$STNM},等待处理中',
	'SHIPOUT'    	=>		'{$STNM}已打印{$EXPNM}面单:{$EXPNO},发往下一个中转仓',

	'Line_Stop'		=> '您选择的线路不存在',
	'Need_sfID'		=> '该线路要求填写身份证号',

	//'SHIPSEND'		=>		'{$STNM}已打印{$EXPNM}面单:{$EXPNO}', //20150729
	//'SHIPSEND'		=>		'已离开{$STNM}，发往{$EXPNM}',
	//'SHIPSEND'		=>		'已离开{$STNM}，发往{$EXPNM}，面单号:{$EXPNO}',
	'SHIPSEND'		=>		'已离开{$STNM}，发往国内，面单号:{$EXPNO}', //171017

	'ErrorBack'		=> 		'不符合取消中转条件',
	'OkBack'		=> 		'仓位已满，返回{$STNM}',
	'BackSave'		=> 		'取消成功！',
	'SHIPOVER'		=>		'已从{$STNM}发出',
	'OPERATED'		=>		'已操作，无法重复操作',

	//'SORT'			=>		'包裹由{$STNM}揽收,正在进行分拣', //20150729
	'SORT'			=>		'{$STNM} 已揽收',

	'NOWEIGH'		=>		'未称重的包裹不能进行此操作',
	'NOEXISTE'		=>		'单号{$MKNO}不存在，请检查',
	'WEIGH'			=>		'更新重量成功',
	'WEIGHFALSE'	=>		'称重重复',
	'ONORDER'		=>		'面单不存在！',
	'NothisTime'	=> 		'请按流程操作',

	//'TranContext'   => 		'已出库，并由{$NM}承运,单号{$NO}', 	// Man 20150504 20150729
	//'TranContext'   => 		'已离开{$STNM}，发往美快国际物流香港中转中心',
	'TranContext'   	=> '已离开{$STNM}，发往{$STTO}',  //Man 160114 增加到达中心名称
	'TransitlinesError' => '中转线路不相同，请校对',
	'TransitNoDone'		=> '{$NO}已完成，请用新的批号中转',
	'TransitError'   	=> '线路资料有误！',
	'TransitNoInfoError'=> '客户资料未完整，请暂停中转',
	//160114要增加同一批次号里，手机号码，身份证号码，收件人地址分别不能重新
	'TransitSameError'	=> '请下批再发，同一批次里不能存在相同的',
	'WaitClientInfo'	=> '身份证明资料未完整，请您尽快补录资料，以便尽快发货',

	'Stoped'		=> 		'该件已停止转运，请退回发货人',		// Man 20150706,抽单处理


	'SYSERROR0'		=> '没收到JSON,JSON内容为空,错误的JSON格式',
	'SYSERROR2'		=> 'MD5码不正确',
	'SYSERROR3'		=> '数据类型不对。json.kd不对',
);