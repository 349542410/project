<?php
/**
 * 客户端
 * 20161031 Jie 用于保存快递100发送过来的 申通/EMS 的物流信息
 * 20161031 Jie 增加对EMS物流信息的保存
 */
	header("Content-type: text/html; charset=utf-8");

    $config = require('../../config.php');
    define('API_URL', $config['API_URL']);
	//模拟返回的json数据
	// $Gjson = '{"message":"","nu":"221014000145","companytype":"shentong","ischeck":"1","com":"shentong","updatetime":"2015-05-19 11:39:56","status":"200","condition":"F00","codenumber":"221014000145","data":[{"time":"2015-04-28 15:46:48","location":"","context":"已签收,签收人是前台签收","ftime":"2015-04-28 15:46:48"},{"time":"2015-04-28 08:47:46","location":"","context":"广东番禺大岗 的派件员 东涌点李文峰 正在派件","ftime":"2015-04-28 08:47:46"}],"state":"3"}';
	// $j = '{"status":"shutdown","message":"3天查询无记录","lastResult":'.$Gjson.'}';	//自定义json

	// $j = '{"status":"polling","billstatus":"check","message":"","lastResult":{"message":"ok","nu":"221014000145","ischeck":"1","condition":"F00","com":"shentong","status":"200","state":"0","data":[{"time":"2015-07-25 08:32:30","ftime":"2015-07-25 08:32:30","context":"申通国际香港分公司 的收件员 孙春柳 已收件"},{"time":"2015-07-20 22:01:23","ftime":"2015-07-20 22:01:23","context":"由【申通国际香港分公司】发往【海外深圳分拨中心】"}]}}';

	// $j = '{"status":"polling","billstatus":"check","message":"","lastResult":{"message":"ok","nu":"221014000145","ischeck":"1","condition":"F00","com":"shentong","status":"200","state":"0","data":[{"time":"2016-10-19 14:11:37","ftime":"2016-10-19 14:11:37","context":"已签收,签收人是: 转EMS1133969112997"},{"time":"2015-07-25 08:32:30","ftime":"2015-07-25 08:32:30","context":"山东潍坊诸城公司 的派件员 诸城邱永生 正在派件"},{"time":"2015-07-25 08:17:06","ftime":"2015-07-25 08:17:06","context":"快件已到达山东潍坊诸城公司"},{"time":"2015-07-25 08:16:52","ftime":"2015-07-25 08:16:52","context":"快件已到达山东潍坊诸城公司"},{"time":"2015-07-25 00:11:24","ftime":"2015-07-25 00:11:24","context":"由山东潍坊公司 发往 山东潍坊诸城公司"},{"time":"2015-07-25 00:10:19","ftime":"2015-07-25 00:10:19","context":"快件已到达山东潍坊公司"},{"time":"2015-07-24 18:44:02","ftime":"2015-07-24 18:44:02","context":"由山东潍坊中转部 发往 山东潍坊公司"},{"time":"2015-07-23 00:50:49","ftime":"2015-07-23 00:50:49","context":"由 申通国际香港分公司 发往 海外深圳分拨中心"},{"time":"2015-07-23 00:50:49","ftime":"2015-07-23 00:50:49","context":"广东深圳罗湖中转部 正在进行 装袋 扫描"},{"time":"2015-07-23 00:48:36","ftime":"2015-07-23 00:48:36","context":"由广东深圳罗湖中转部 发往 山东潍坊中转部"},{"time":"2015-07-23 00:32:52","ftime":"2015-07-23 00:32:52","context":"由广东深圳罗湖中转部 发往 山东潍坊中转部"},{"time":"2015-07-23 00:26:55","ftime":"2015-07-23 00:26:55","context":"【申通国际香港分公司】的收件员【孙春柳】已收件"},{"time":"2015-07-22 13:46:27","ftime":"2015-07-22 13:46:27","context":"由海外深圳分拨中心 发往 广东深圳公司"},{"time":"2015-07-22 13:44:29","ftime":"2015-07-22 13:44:29","context":"申通国际快件已到达海外深圳分拨中心"},{"time":"2015-07-20 22:01:23","ftime":"2015-07-20 22:01:23","context":"由【申通国际香港分公司】发往【海外深圳分拨中心】"},{"time":"2015-07-22 13:44:29","ftime":"2015-05-22 13:44:29","context":"测试此条快件信息是否会被保存"}]}}';

	//模拟返回申通物流信息
	// $j = '{"status":"polling","billstatus":"check","message":"","lastResult":{"message":"ok","nu":"221014000145","ischeck":"1","condition":"F00","com":"shentong","status":"200","state":"0","data":[{"time":"2016-10-19 14:11:37","ftime":"2016-10-19 14:11:37","context":"已签收,签收人是: 转EMS1133969112997"},{"time":"2016-09-21 15:02:18","ftime":"2016-09-21 15:02:18","context":"由 申通国际香港分公司 发往 海外深圳分拨中心"},{"time":"2016-09-21 14:52:47","ftime":"2016-09-21 14:52:47","context":"申通国际香港分公司 的收件员 孙春柳 已收件"},{"time":"2016-09-19 14:23:14","ftime":"2016-09-19 14:23:14","context":"已离开美快国际物流香港中转中心，发往申通，面单号:5530024402464"},{"time":"2016-09-16 02:04:57","ftime":"2016-09-16 02:04:57","context":"已离开美快国际物流美国北加州仓，发往美快国际物流香港中转中心"},{"time":"2016-09-16 02:04:56","ftime":"2016-09-16 02:04:56","context":"美快国际物流美国北加州仓 已揽收"}]}}';

	//模拟返回EMS物流信息
	// $j = '{"status":"polling","billstatus":"check","message":"","lastResult":{"message":"ok","nu":"1133969112997","ischeck":"1","condition":"F00","com":"shentong","status":"200","state":"3","data":[{"time":"2016-10-19 12:25:32","ftime":"2016-10-19 12:25:32","context":"【深圳市】 投递并签收，签收人：他人收 电联放e栈"},{"time":"2016-10-19 07:57:30","ftime":"2016-10-19 07:57:30","context":"【深圳市】 深圳市南油速递营销部安排投递，预计13:00:00前投递（投递员姓名：陈海东15012593691;联系电话：15012593691）"},{"time":"2016-10-19 05:30:00","ftime":"2016-10-19 05:30:00","context":"【深圳市】 到达 深圳陆运转运中心 处理中心"},{"time":"2016-10-19 03:16:00","ftime":"2016-10-19 03:16:00","context":"【中山市】 离开中山市 发往深圳市（经转）"},{"time":"2016-10-19 02:36:00","ftime":"2016-10-19 02:36:00","context":"【中山市】 到达中山三角处理中心处理中心（经转）"},{"time":"2016-10-18 16:42:08","ftime":"2016-10-18 16:42:08","context":"【湛江市】 离开湛江市 发往中山市"},{"time":"2016-10-18 15:47:00","ftime":"2016-10-18 15:47:00","context":"【湛江市】 湛江进出境快件监管中心已收件（揽投员姓名：欧天敬,联系电话:0759-2189001）"}]}}';

	// $mkno = 'MK881000198US';
	//模拟结束

	$j = isset($_POST['param']) ? $_POST['param'] : null;
	$j = trim($j);
	
	// 20161027 Jie
	$mkno = isset($_GET['mkno']) ? trim($_GET['mkno']) : '';//'MK881000198US';

//====================================== 一下为正式处理行为 ======================================	
	//如果是合法的json
	if($j=='' || $j == null || (!is_json($j))){
		$arr = array(
			'result'     =>'false',
			'returnCode' =>'700',
			'message'    =>'数据形式错误',
		);
		echo json_encode($arr);
		exit;
	}
	$res  = $j;
//	$res = analyJson($j);	//解释json为数组形式
//	TJson($res);	//处理方法
//	 echo '<prev>';
//	 print_r($res);
//	 exit;

	/**
	 * 客户端 快递100回调数据处理    由于是模拟接收json所用，此函数启用时删除，只需要里面的代码使用即可
	 * @param [type] $res [回调的json解释后的数组]
	 */
    //生成日志
    $xmlsave = UPFILEBASE.'/kd100_log/';
    $file_name = date('Y-m-d').'-express1.txt';    //文件名

    $content = "======== 回调结果 =========\r\n\r\n".$res. "\r\n\r\nMK_NO:".$mkno;

    if(!is_dir($xmlsave)) mkdir($xmlsave, 0777, true);//第三个参数设置true 设置递归模式自动创建必需的目录文件夹

    file_put_contents($xmlsave.$file_name, $content,FILE_APPEND);
    //生成日志 end

    require('ex_config.php');
	require("../../Application/hprose_php5/HproseHttpClient.php");

    $client = new HproseHttpClient($serurl1);

    $res = json_decode($res,true);
if($res['message'] == '3天查询无记录'){
    echo $str_s;exit;
}

    // 当$mkno为空的时候，即为申通物流信息的处理
    if(isset($mkno) && $mkno != ''){

    	// 检验MKNO的格式是否正确 20161031 Jie
		$ismkno    = preg_match('/^MK[0-9A-Z]{11}$/',$mkno);

		if($ismkno){
    		$msg = $client->save($res,true,$mkno);	//直接传入数组形式的数据  ture表示是EMS
		}else{
			$arr = array(
				'result'     =>'false',
				'returnCode' =>'701',
				'message'    =>'MKNO格式错误',
			);
			echo json_encode($arr);
			exit;
		}

    }else{
    	$msg = $client->save($res);	//直接传入数组形式的数据 申通快递
    }
    // print_r($msg);
    // exit;
    
    if($msg['title'] == 'abort' && $msg['do'] == 'yes'){

    	echo $str_s;exit;

    }else if($msg['title'] != 'abort' && $msg['do'] == 'yes'){

    	echo $str_s;exit;

    }else if($msg['title'] != 'abort' && $msg['do'] == 'no'){

    	echo $str_f;exit;

    }


//=============================== 检验用 ==============================================

	//判断数据是合法的json数据:
	function is_json($string) {
		json_decode($string);
		return (json_last_error() == JSON_ERROR_NONE);	//json_last_error()函数返回数据编解码过程中发生的错误
	}
?>