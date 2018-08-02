<?php
	require_once('config.php');
	require_once('../db.php');//数据库连接
	// require_once('connect.php');
	require_once 'customs.php';
	require_once 'solution.php';
	require_once 'function.php';
	require('./ex_config.php');//远程地址
	require("./../../hprose_php5/HproseHttpClient.php");

	$customs = new \Customs();
	$client = new \HproseHttpClient($serurl);

    const USERNAME='4423962963';//海关代码
    const APIURL='http://221.224.206.244:8081/KJPOSTWEB/Data.aspx';
    const SHOPNAME='广州美快软件开发有限公司';

	/* Jie 20160921 */
	// 线程名称
	$node = isset($_GET['node']) ? $_GET['node'] : 'ORDER_INFO';

	//主动获取顺丰的物流信息
	$time = time();	//标识码(时间戳) 访问此文件的时候马上生成

	// 查询倒序后的最新的一条数据
	$maxlid_sql = "SELECT id,lid,ctime,state FROM mk_tran_list_notes WHERE node = '$node' ORDER BY id DESC LIMIT 1";

	$max = $pdo->query($maxlid_sql);

	$maxinfo = $max->fetch(PDO::FETCH_ASSOC);

	$maxinfo['ctime'] = ($maxinfo['ctime'] == '') ? 0 : $maxinfo['ctime'];

	$tenHour = $time - $maxinfo['ctime'];	//当前时间的10小时前

	// 超过规定时限(10小时) 或者mk_tran_list_notes.max(id).state = 200，则会重新执行物流信息查询
	if($tenHour > $limitHour || $maxinfo['state'] == '200'){
		
		// 如果查询没有任何结果
		if($maxinfo === false){
			$maxlid = $maxinfo['lid'];
		}else{
			$maxlid = 0;
		}
		$ctime = $time;//超过10小时时限或者state=200，需要用 新的时间戳 标记 以示新一轮开始
	}else{
		$maxlid = $maxinfo['lid'];
	}
	/* 线程 End 20160921 */

	$t_sql = "SELECT * FROM mk_tran_list WHERE id > $maxlid ORDER BY id ASC LIMIT $limit";

	$tlist = $pdo->query($t_sql);

	if($tlist->rowCount() > 0){
		$nu_list = $tlist->fetchAll(PDO::FETCH_ASSOC);

		$maxId = $nu_list[count($nu_list)-1]['id'];

		/* Jie 20160921 */
		if($max->rowCount() > 0){
			if($maxinfo['state'] == '200' || $tenHour > $limitHour){
				$note_sql = "UPDATE mk_tran_list_notes SET lid='$maxId', ctime='$ctime', state='0' WHERE id = '$maxinfo[id]'";
			}else{
				$note_sql = "UPDATE mk_tran_list_notes SET lid='$maxId' WHERE id='$maxinfo[id]'";
			}
		}else{
			$note_sql = "INSERT INTO mk_tran_list_notes (lid,ctime,node) VALUES ('$maxId', '$ctime','$node')";
		}

		// $pdo->query($note_sql);

		echo '<pre>';
		// print_r($nu_list);

		$et = 0;//计算成功获取物流信息的总数
		$msg = '';

		// start 用于以后区分SF和其他物流公司
		foreach($nu_list as $kk=>$item){

			$o_sql = "SELECT * FROM mk_tran_order WHERE lid = '$item[id]'";

			$olist = $pdo->query($o_sql);

			if($olist->rowCount() > 0){
				$order = $olist->fetchAll(PDO::FETCH_ASSOC);

				// print_r($order);

				$item['extend_order_goods'] = $order;
				// print_r($item);
				$res[$kk] = getArr($item, $node, $customs, $client);
			}else{
				echo '<br/>';
				echo 'Notice：订单中没有商品';
			}
			
		}





/*		$endtime = time();
		$Ttime = $endtime - $time;

		// $et  计算成功获取并保存物流信息的总数
		if($et == 0){
			$backXML = '<Response service="RoutePushService"><Head>ERR</Head><ERROR code="4001">系统发生数据错误或运行时异常；耗时：'.$Ttime.'秒；反馈信息：'.$msg.'</ERROR></Response>';
			
		}else{
			$backXML = '<Response service="RoutePushService"><Head>OK</Head>请求发送总数：'.$limit.'个，实际查询数据：'.count($nu_list).'个；成功保存物流信息：'.$et.'个；耗时：'.$Ttime.'秒</Response>';
		}
		echo $backXML;
*/



	}else{//当搜索数据表已经没有得到合适数据的时候，就把最大的id的状态标记为200

		/* Jie 20170317 */
		if($max->rowCount() > 0){
			
			$note_sql = "UPDATE mk_tran_list_notes SET state='200' WHERE id = '$maxinfo[id]'";
			
		}else{// 如果mk_tran_list查询没有数据，而且mk_tran_list_notes也没有对应的数据

			$note_sql = "INSERT INTO mk_tran_list_notes (lid,ctime,node) VALUES ('0', '$ctime','$node')";
		}

		$pdo->query($note_sql);
		/* End 20170317 */
	}