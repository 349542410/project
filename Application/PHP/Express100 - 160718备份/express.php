<?php
/**
 * 客户端
 */
	header("Content-type: text/html; charset=utf-8");

	//模拟返回的json数据
	// $Gjson = '{"message":"","nu":"221014000145","companytype":"shentong","ischeck":"1","com":"shentong","updatetime":"2015-05-19 11:39:56","status":"200","condition":"F00","codenumber":"221014000145","data":[{"time":"2015-04-28 15:46:48","location":"","context":"已签收,签收人是前台签收","ftime":"2015-04-28 15:46:48"},{"time":"2015-04-28 08:47:46","location":"","context":"广东番禺大岗 的派件员 东涌点李文峰 正在派件","ftime":"2015-04-28 08:47:46"}],"state":"3"}';
	// $j = '{"status":"shutdown","message":"3天查询无记录","lastResult":'.$Gjson.'}';	//自定义json
	

	// $j = '{"status":"shutdown","billstatus":"check","message":"","lastResult":{"message":"ok","nu":"221014000145","ischeck":"1","condition":"F00","com":"shentong","status":"200","state":"3","data":[{"time":"2015-07-25 11:20:53","ftime":"2015-07-25 11:20:53","context":"已签收,签收人是门卫签收"},{"time":"2015-07-25 08:32:30","ftime":"2015-07-25 08:32:30","context":"山东潍坊诸城公司 的派件员 诸城邱永生 正在派件"},{"time":"2015-07-25 08:17:06","ftime":"2015-07-25 08:17:06","context":"快件已到达山东潍坊诸城公司"},{"time":"2015-07-25 08:16:52","ftime":"2015-07-25 08:16:52","context":"快件已到达山东潍坊诸城公司"},{"time":"2015-07-25 00:11:24","ftime":"2015-07-25 00:11:24","context":"由山东潍坊公司 发往 山东潍坊诸城公司"},{"time":"2015-07-25 00:10:19","ftime":"2015-07-25 00:10:19","context":"快件已到达山东潍坊公司"},{"time":"2015-07-24 18:44:02","ftime":"2015-07-24 18:44:02","context":"由山东潍坊中转部 发往 山东潍坊公司"},{"time":"2015-07-23 00:50:49","ftime":"2015-07-23 00:50:49","context":"由广东深圳罗湖中转部 发往 山东潍坊中转部"},{"time":"2015-07-23 00:50:49","ftime":"2015-07-23 00:50:49","context":"广东深圳罗湖中转部 正在进行 装袋 扫描"},{"time":"2015-07-23 00:48:36","ftime":"2015-07-23 00:48:36","context":"由广东深圳罗湖中转部 发往 山东潍坊中转部"},{"time":"2015-07-23 00:32:52","ftime":"2015-07-23 00:32:52","context":"由广东深圳罗湖中转部 发往 山东潍坊中转部"},{"time":"2015-07-23 00:26:55","ftime":"2015-07-23 00:26:55","context":"快件已到达广东深圳罗湖中转部"},{"time":"2015-07-22 13:46:27","ftime":"2015-07-22 13:46:27","context":"由海外深圳分拨中心 发往 广东深圳公司"},{"time":"2015-07-22 13:44:29","ftime":"2015-07-22 13:44:29","context":"快件已到达海外深圳分拨中心"},{"time":"2015-07-20 22:01:23","ftime":"2015-07-20 22:01:23","context":"由香港公司 发往 海外深圳分拨中心"},{"time":"2015-07-22 13:44:29","ftime":"2015-05-22 13:44:29","context":"测试此条快件信息是否会被保存"}]}}';

	//模拟结束

	$j = isset($_POST['param'])?$_POST['param']:null;
	$j = trim($j);

	//echo 'RS:<br/>';

	

//====================================== 一下为正式处理行为 ======================================	
	//如果是合法的json
	if($j=='' || $j == null || (!is_json($j))){
		$arr = array(
			'result'=>'false',
			'returnCode'=>'700',
			'message'=>'数据形式错误',
		);
		echo json_encode($arr);
		exit;
	}
	$res  = $j;
	//$res = analyJson($j);	//解释json为数组形式
	//TJson($res);	//处理方法

	//print_r($res);
	//exit;

	/**
	 * 客户端 快递100回调数据处理    由于是模拟接收json所用，此函数启用时删除，只需要里面的代码使用即可
	 * @param [type] $res [回调的json解释后的数组]
	 */
	//function TJson($res){
	    //require_once("./../../hprose2/Hprose.php");
	    
		include('ex_config.php');
		require_once("./../../hprose_php5/HproseHttpClient.php");

		//echo $serurl;exit;
	    $client = new HproseHttpClient($serurl);
       //echo 'eeee';exit;
        $msg = $client->info($res);	//直接传入json形式的数据
        //echo 'eeee';exit;
        //var_dump($msg);//exit;
        if($msg['title'] == 'abort' && $msg['do'] == 'yes'){

        	echo $str_s;exit;

        }else if($msg['title'] != 'abort' && $msg['do'] == 'yes'){

        	echo $str_s;exit;

        }else if($msg['title'] != 'abort' && $msg['do'] == 'no'){

        	echo $str_f;exit;

        }

	//}


//=============================== 检验用 ==============================================

	//判断数据是合法的json数据:
	function is_json($string) {
		json_decode($string);
		return (json_last_error() == JSON_ERROR_NONE);	//json_last_error()函数返回数据编解码过程中发生的错误
	}
?>