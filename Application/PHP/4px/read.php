<?php
	header("Content-type: text/html; charset=utf-8");
	require_once('./conf.php');
	require_once('./ex_config.php');
	require_once('./function.php');
	require_once("./../../hprose_php5/HproseHttpClient.php");

	$loginfo = getloginfo($mkno='');
	// var_dump($loginfo);

	// echo '<pre>';
	// print_r($loginfo);
	// echo '</pre>';

	/*  文档编写要求
	//将loginfo生成 ./Express100/server.php 可接受的格式，使用 HproseHttp进行保存
	//需将BusinessLinkCode转换为我们的物流状态
	（到达香港仓，香港仓已发快递，快递状态(揽收，在途，疑难(如DD开头等)，签收(如OK开头)，退回,派件等+
	清关中,
	错件(SH开头等,),
	海关问题件(HC开头等)，
	延迟（TD开头等）,
	其它算作在途
	对照物流后台管理）)
	增加的状态可以从1010开始定义
	*/

	//将BusinessLinkCode转换为我们的物流状态
	$TrackingList = $loginfo['TrackingList']['TrackingList'];
	foreach($TrackingList as $key=>$item){
		$TrackingList[$key]['BusinessLinkCode'] = MKIL_State($item['BusinessLinkCode']);
	}
	
	//将loginfo生成 ./Express100/server.php 可接受的格式，使用 HproseHttp进行保存
	$arr = array();
	$arr['status']                  = '';
	$arr['billstatus']              = 'check';
	$arr['message']                 = '';
	$arr['lastResult']['message']   = 'ok';
	$arr['lastResult']['nu']        = $loginfo['DeliveryCodeNo'];
	$arr['lastResult']['ischeck']   = '1';
	$arr['lastResult']['condition'] = '';
	$arr['lastResult']['com']       = '';
	$arr['lastResult']['status']    = '';
	$arr['lastResult']['state']     = '';
	$arr['lastResult']['data']      = $TrackingList;

	// echo '<pre>';
	// print_r($arr);
	// echo '</pre>';

	$client = new HproseHttpClient($serurl);

    $res = $client->save($arr);	//直接传入数组形式的数据
	echo '<pre>';
	print_r($res);
	echo '</pre>';