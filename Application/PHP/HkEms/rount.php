<?php
/*
	版本号：V1.0 香港E特快
	创建人：Jie
	创建日期：2017-01-10
	修改日期：2017-01-10
	用途：自动去查询并获取 物流信息 且保存
	指导文档：香港E特快Userapi_1.2.pdf
	数据表：mk_tran_list  mk_tran_list_notes  mk_il_logs
	编写方案：
 */
	require_once('../db.php');//数据库连接
	require_once('h_config.php');//配置信息
	require_once('function.php'); //获取xml并转数组
	$kd6    = $out_load.'\Kdno6.class.php';
	$kdfile = $out_load.'\Kdno6.conf.php';
	require_once($kd6);//加载类
	require_once($kdfile);//载入配置信息

	$EMS = new \Kdno();

	echo "\r\n".date('Y-m-d H:i:s')."\r\n";

	/* Jie 20160921 */
	// 线程名称
	$node = isset($_GET['node']) ? $_GET['node'] : 'Hk_ems';//默认 Hk_ems 香港E特快

	//主动获取顺丰的物流信息
	$time = time();	//标识码(时间戳) 访问此文件的时候马上生成

	// 查询倒序后的最新的一条数据
	$maxlid_sql = "select id,lid,ctime,state from mk_tran_list_notes where node = '$node' order by id desc limit 1";

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

	//查tran_list.trankd=? IL_State<>1003的 MK单进行物流信息获取
	$find_sql = "SELECT $no,id FROM mk_tran_list WHERE TranKd = '$TranKd' AND IL_state <> '1003' AND id > $maxlid AND $no <> '' ORDER BY id ASC LIMIT $limit";
// print_r($find_sql);die;
	$find = $pdo->query($find_sql);

	if($find->rowCount() > 0){

		$nu_list = $find->fetchAll(PDO::FETCH_ASSOC);
		// echo '<pre>';
		// print_r($nu_list);
		// die;

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

		$pdo->query($note_sql);
		// die;
		/* End 20160921 */

//============= 测试   ========

		// $nu_list =  array('0'=>array('STNO'=>'EK211220759HK'));//新 测试用
		// $nu_list =  array('0'=>array('STNO'=>'080000820051'),'1'=>array('STNO'=>'080001114498'),'2'=>array('STNO'=>'080000819360'));

		// print_r($nu_list);
		// die;
//============= 测试 End  ======== 
		$et = 0;//计算成功获取物流信息的总数
		$msg = '';

		// start 用于以后区分SF和其他物流公司
		foreach($nu_list as $kk=>$item){

			$tracking_number = $item[$no];

			//调用文件中的函数处理
			$res[$kk] = getArr($tracking_number, $debug=0, $EMS, $serurl, $config);//$debug 调试编码，不同数值可用于检查不同位置的数据输出

			if($res[$kk]['do'] == 'yes'){
				$et++;
			}else{
				$msg .= '单号11：'.$item[$no].'，msg：'.$res[$kk]['title'].'；';
			}
			
		}
		//end
		// echo '<pre>';
		// print_r($res);
		// echo $et++;

		$endtime = time();
		$Ttime = $endtime - $time;
		
		// $et  计算成功获取并保存物流信息的总数
		if($et == 0){
			$backXML = '<Response service="RoutePushService"><Head>ERR</Head><ERROR code="4001">系统发生数据错误或运行时异常；耗时：'.$Ttime.'秒；反馈信息：'.$msg.'</ERROR></Response>';
			
		}else{
			$backXML = '<Response service="RoutePushService"><Head>OK</Head>请求发送总数：'.$limit.'个，实际查询数据：'.count($nu_list).'个；成功保存物流信息：'.$et.'个；耗时：'.$Ttime.'秒</Response>';
		}
		echo $backXML;

	}else{//当搜索数据表已经没有得到合适数据的时候，就把最大的id的状态标记为200

		/* Jie 20170317 */
		if($max->rowCount() > 0){
			
			$note_sql = "UPDATE mk_tran_list_notes SET state='200' WHERE id = '$maxinfo[id]'";
			
		}else{// 如果mk_tran_list查询没有数据，而且mk_tran_list_notes也没有对应的数据

			$note_sql = "INSERT INTO mk_tran_list_notes (lid,ctime,node) VALUES ('0', '$ctime','$node')";
		}

		$pdo->query($note_sql);
		echo date('Y-m-d H:i:s').' 运行完成';
		/* End 20170317 */
	}