<?php
/*
	版本号：V2.0 顺丰
	创建人：Jie
	创建日期：2016-10-10
	修改日期：2016-10-11
	用途：计算机自动去查询顺丰物流并获取物流信息且保存
 */
	$config = require('../../../config.php');
	require_once('../db.php');//数据库连接
	require_once('h_config.php');//配置信息
	require_once('r_function.php'); //获取xml并转数组
	// require_once('function.php');//公用函数
	// require_once('./ex_config.php');//远程地址
	// require_once("./../../hprose_php5/HproseHttpClient.php");
	header('Content-type:text/html;charset=UTF-8');	//设置输出格式

	/* Jie 20160921 */
	// 线程名称
	$node = isset($_GET['node']) ? $_GET['node'] : 'sfus';

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
	$find_sql = "SELECT $no,id FROM mk_tran_list WHERE trankd = '$TranKd' AND IL_state <> '1003' AND id > $maxlid AND $no <> '' ORDER BY id ASC LIMIT $limit";

	$find = $pdo->query($find_sql);

	if($find->rowCount() > 0){

		$nu_list = $find->fetchAll(PDO::FETCH_ASSOC);

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
		// print_r($arr);die;
		// $nu_list =  array('STNO'=>'080000860854');//旧版测试用
		// $arr =  array('0'=>'080000819333','1'=>'080000819402','2'=>'080000819306');
		// print_r($arr);die;

		// $tracking_number = implode(',', $arr);

		// $nu_list =  array('0'=>array('STNO'=>'080000820051'));//新 测试用
		// $nu_list =  array('0'=>array('STNO'=>'080000820051'),'1'=>array('STNO'=>'080001114498'),'2'=>array('STNO'=>'080000819360'));
		// $nu_list =  array('0'=>array('STNO'=>'080000820051'),'1'=>array('STNO'=>'080001114498'));

//============= 测试 End  ======== 
		$et = 0;//计算成功获取物流信息的总数
		$msg = '';

	    //生成日志
	    $xmlsave = $config['UPFILEBASE'].'/sfus_log/';
	    $file_name = date('Y-m-d').'-rount.txt';    //文件名

	    $content = "\r\n\r\n======== 发送数据 ".date('H:i:s')." =========\r\n\r\n".json_encode($nu_list,JSON_UNESCAPED_UNICODE);

	    if(!is_dir($xmlsave)) mkdir($xmlsave, 0777, true);//第三个参数设置true 设置递归模式自动创建必需的目录文件夹

	    file_put_contents($xmlsave.$file_name, $content,FILE_APPEND);
	    //生成日志 end

		// start 用于以后区分SF和其他物流公司
		foreach($nu_list as $kk=>$item){

			$tracking_number = $item[$no];

			//调用文件中的函数处理
			$res[$kk] = getArr($tracking_number, $debug=0);//$debug 调试编码，不同数值可用于检查不同位置的数据输出

			if($res[$kk]['do'] == 'yes'){
				$et++;
			}else{
				$msg .= '单号：'.$item[$no].'，msg：'.$res[$kk]['title'].'；';
			}
			
		}
		//end
		// echo '<pre>';
		// print_r($res);
		// echo $et++;

	    //生成日志
	    $xmlsave = $config['UPFILEBASE'].'/sfus_log/';
	    $file_name = date('Y-m-d').'-rount.txt';    //文件名

	    $content = "\r\n\r\n======== 处理结果 ".date('H:i:s')." =========\r\n\r\n".$msg;

	    if(!is_dir($xmlsave)) mkdir($xmlsave, 0777, true);//第三个参数设置true 设置递归模式自动创建必需的目录文件夹

	    file_put_contents($xmlsave.$file_name, $content,FILE_APPEND);
	    //生成日志 end

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