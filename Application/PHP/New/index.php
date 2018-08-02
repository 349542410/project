<?php
/**
 * 物流信息推送
 * @var string
 */
	include('config.php');
	include('httppost.php');

	$sql = "SELECT MKNO FROM mk_il_logs WHERE `state` = 0 GROUP BY MKNO LIMIT 10";

	$row = get_all($sql);
	$count = count($row);
	if($count > 0){
		$i = 0;
		// $sql = "select * from mk_il_logs where `MKNO` = $row";
		foreach($row as $item){
			// dump($item);
			$sql = "SELECT create_time as ftime,content as context FROM mk_il_logs WHERE `MKNO` = '$item[MKNO]'";
			$one = get_all($sql);
			// dump($one);
		//组建数组
		$arr = array(
		    'status' => '',
		    'message' => 'ok',
		    'lastResult' => Array(
	            'message' => '',
	            'nu' => $item['MKNO'],
	            'companytype' => 'Megao',
	            'ischeck' => '1',
	            'com' => 'Megao',
	            'updatetime' => date('Y-m-d H:i:s',time()),
	            'status' => '200',
	            'condition' => 'F00',
	            'codenumber' => $item['MKNO'],
	            'data' => $one,
	    		'state' => '3',
			),
		);
		$str = json_encode($arr);
		// dump($str);
		$res = curl_post('http://test3.megao.hk/New/getPost.php',$str);
		echo $res;



		die;
		$i++;

		}
		// echo $i;
	}

?>