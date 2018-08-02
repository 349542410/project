<?php
	header('Content-type:text/json;charset=UTF-8');	//设置输出格式
	require_once('./ex_config.php');
	require_once('./function.php');
	require_once("./../../hprose_php5/HproseHttpClient.php");
	/*
		编写方案
	
	Man 2016-08-03 15:30
	将收到以下XML
	<Request service="RoutePushService" lang="zh-CN">
	<Body>
		<WaybillRoute
		id="10049361064087"
		mailno="444003079772"
		orderid="TE201500106"
		acceptTime="2015-01-04 17:42:00"
		acceptAddress="深圳"
		remark="上门收件"
		opCode="50"/>

		<WaybillRoute
		id="10049361064087"
		mailno="444003079772"
		orderid="TE201500106"
		acceptTime="2015-01-05 17:42:00"
		acceptAddress="上海"
		remark="在途"
		opCode="60"/>		
	</Body>
	</Request>
	其中 WaybillRoute 最多10个(测试时分两种情况进行，一：只有一条；二：多于一条),opCode另有文件说明
	id可以不管
	mailno为快递单号即tran_list.STNO
	要求  Express100/server.php 可接受的格式，使用 HproseHttp进行保存

	=========================
	处理成功后输出 ：
	<Response service="RoutePushService"><Head>OK</Head></Response>
	失败输出
	<Response service="RoutePushService"><Head>ERR</Head><ERROR code="4001">系统发生数据错误或运行时异常</ERROR></Response>
	编写方案 End */

	/* 测试用 */
	// $xml = '<Request service="RoutePushService" lang="zh-CN">
	// <Body>
	// 	<WaybillRoute
	// 	id="10049361064087"
	// 	mailno="221014000275"
	// 	orderid="TE201500106"
	// 	acceptTime="2015-01-04 17:42:00"
	// 	acceptAddress="深圳"
	// 	remark="上门收件"
	// 	opCode="50"/>
	// 	<WaybillRoute
	// 	id="10049361064087"
	// 	mailno="221014000275"
	// 	orderid="TE201500106"
	// 	acceptTime="2015-01-05 17:42:00"
	// 	acceptAddress="上海"
	// 	remark="在途"
	// 	opCode="60"/>
	// </Body>
	// </Request>';

	$xml = '<Request service="RoutePushService" lang="zh-CN">
	<Body>
		<WaybillRoute
		id="10049361064087"
		mailno="221014000271"
		orderid="TE201500106"
		acceptTime="2017-01-04 17:42:00"
		acceptAddress="深圳"
		remark="快递已被签收，收件人：XXX"
		opCode="80"/>
	</Body>
	</Request>';
	/* 测试用 End */

	// var_dump($xml);
	$getArr = object_array($xml);
	// echo '<pre>';
	// print_r($getArr);
	// echo '</pre>';

	// $lang = $getArr['@attributes']['lang'];	//语言
	$WaybillRoute = $getArr['Body']['WaybillRoute'];

	// var_dump(count($WaybillRoute));
	$new_a = array();
	if(count($WaybillRoute) == 1){

		$list = $WaybillRoute['@attributes'];

		$new_a[0]['BusinessLinkCode'] = MKIL_State($list['opCode']);
		$new_a[0]['TrackingContent']  = $list['remark'];
		$new_a[0]['OccurDatetime']    = $list['acceptTime'];
		// print_r($list);

		//将loginfo生成 ./Express100/server.php 可接受的格式，使用 HproseHttp进行保存
		$arr = array();
		$arr['status']                  = '';
		$arr['billstatus']              = 'check';
		$arr['message']                 = '';
		$arr['lastResult']['message']   = 'ok';
		$arr['lastResult']['nu']        = $list['mailno'];	//tran_list.STNO
		$arr['lastResult']['ischeck']   = '1';
		$arr['lastResult']['condition'] = '';
		$arr['lastResult']['com']       = '';
		$arr['lastResult']['status']    = '';
		$arr['lastResult']['state']     = '';

		unset($list['id']);
		unset($list['mailno']);
		unset($list['orderid']);

		$arr['lastResult']['data']      = $new_a;
		// var_dump($arr);

	}else{

		$list = $WaybillRoute;

		//物流信息数组三维转二维数组
		foreach($list as $key => $row){
		    foreach($row as $key2 => $row2){
		        $new_a[$key] = $row2;
		        $new_a[$key]['BusinessLinkCode'] = MKIL_State($row2['opCode']);
		        $new_a[$key]['TrackingContent'] = $row2['remark'];
		        $new_a[$key]['OccurDatetime'] = $row2['acceptTime'];
				unset($new_a[$key]['id']);
				unset($new_a[$key]['mailno']);
				unset($new_a[$key]['orderid']);
		    }
		}

		// print_r($new_a);

		//将loginfo生成 ./Express100/server.php 可接受的格式，使用 HproseHttp进行保存
		$arr = array();
		$arr['status']                  = '';
		$arr['billstatus']              = 'check';
		$arr['message']                 = '';
		$arr['lastResult']['message']   = 'ok';
		$arr['lastResult']['nu']        = $list[0]['@attributes']['mailno'];	//tran_list.STNO
		$arr['lastResult']['ischeck']   = '1';
		$arr['lastResult']['condition'] = '';
		$arr['lastResult']['com']       = '';
		$arr['lastResult']['status']    = '';
		$arr['lastResult']['state']     = '';
		$arr['lastResult']['data']      = $new_a;

	}
	
	// print_r($arr);die;

	$client = new HproseHttpClient($serurl);

    $res = $client->save($arr, 'STNO');	//直接传入数组形式的数据
	echo '<pre>';
	print_r($res);
	echo '</pre>';

	if($res['do'] == 'yes'){
		$backXML = '<Response service="RoutePushService"><Head>OK</Head></Response>';
	}else{
		$backXML = '<Response service="RoutePushService"><Head>ERR</Head><ERROR code="4001">系统发生数据错误或运行时异常</ERROR></Response>';
	}

	echo $backXML;