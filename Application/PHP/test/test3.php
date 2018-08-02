<?php
	header("Content-type: text/html; charset=utf-8");
	error_reporting(E_ALL);//错误等级设置
	date_default_timezone_set('PRC');
	ini_set('memory_limit','4088M');	//内存容量
	ini_set('max_execution_time', 0);	//超时时间

//	$dsn    = 'mysql:dbname=mkil;host=127.0.0.1';
//	$user   = 'mkiluser';	//数据库用户名
//	$passwd = 'mk12345678';	//数据库密码
//
//	try{
//		$pdo = new PDO($dsn, $user, $passwd);
//		$pdo->query('set names utf8');//设置字符集
//	}catch(PDOException $e){
//		echo '数据库连接失败'.$e->getMessage();die;
//	}

    require_once('../db.php');//数据库连接

	$MKNO = 'MK81000063US';
	$arr = array(
	    '0' => Array
	        (
	            'BusinessLinkCode' => '1001',
	            'TrackingContent' => '【宁波】快件已到达宁波中转中心',
	            'OccurDatetime' => '2016-09-22 08:46:09',
	        ),
	    '1' => Array
	        (
	            'BusinessLinkCode' => '1000',
	            'TrackingContent' => '【三藩市】快件在【美国三藩市营运中心】已装车，准备发往下一站',
	            'OccurDatetime' => '2016-09-20 20:43:22',
	        ),
	    '2' => Array
	        (
	            'BusinessLinkCode' => '1001',
	            'TrackingContent' => '【三藩市】顺丰速运 已收取快件',
	            'OccurDatetime' => '2016-09-14 13:46:09',
	        ),
	);
	$arr = array(
	    '0' => Array
	        (
	            'BusinessLinkCode' => '1001',
	            'TrackingContent' => '【宁波】快件到达 【宁波鄞州关务组】',
	            'OccurDatetime' => '2016-09-26 14:39:24',
	        ),
	    '1' => Array
	        (
	            'BusinessLinkCode' => '1000',
	            'TrackingContent' => '【三藩市】快件在【美国三藩市营运中心】已装车，准备发往下一站',
	            'OccurDatetime' => '2016-09-20 06:43:22',
	        ),
	    '2' => Array
	        (
	            'BusinessLinkCode' => '1001',
	            'TrackingContent' => '【三藩市】顺丰速运 已收取快件',
	            'OccurDatetime' => '2016-09-19 15:10:51',
	        ),
	);	
	echo '<pre>';
	// print_r($arr);
	// echo count($arr);
	$arr = array_reverse($arr);
	echo '原';
	print_r($arr);
// die;

	// function proof_time($MKNO,$arr){
		$info = array();

		$find_sql = "SELECT status,create_time,rount_time FROM mk_il_logs WHERE MKNO = '$MKNO' ORDER BY id DESC LIMIT 1";
		$find = $pdo->query($find_sql);

		$info = $find->fetch(PDO::FETCH_ASSOC);
		// print_r($info);

		foreach($arr as $key=>$item){
			print_r($info);
			if($find->rowCount() > 0){

				if($info['status'] >= 20){

					$info['rount_time'] = ($info['rount_time'] == '') ? $info['create_time'] : $info['rount_time'];
					// 数据表最新物流的原始物流时间(rount_time) >= 顺丰物流时间
					if(strtotime($info['rount_time']) >= strtotime($item['OccurDatetime'])){
						$arr[$key]['SFTime'] = $item['OccurDatetime'];
							// 顺丰的物流信息时间要+16h
							$add_time = intval(strtotime($item['OccurDatetime']))+57600;//+16h
							//判断+16h后是否会大于服务器当前时间
							$now_time = time();

							$add_time = ($add_time <= $now_time) ? date('Y-m-d H:i:s',$add_time) : date('Y-m-d H:i:s',$now_time);
							// print_r($add_time);
							if($info['status'] < 1000){
								$arr[$key]['OccurDatetime'] = $add_time;
								$info['rount_time']  = $item['OccurDatetime'];
								$info['create_time'] = $add_time;
							}else{
								$arr[$key]['OccurDatetime'] = $item['OccurDatetime'];
							}
						// if($info['status'] >= 1000) unset($arr[$key]);
						// 数据表中最新的物流信息的原始物流时间 >= 顺丰的物流信息时间 的时候，说明数据表中已经存在当前此条物流信息，则继续下一轮的物流信息处理
						// continue;

					}else{// 顺丰物流时间 > 数据表最新物流的原始物流时间(rount_time) (条件1)

						// 满足 条件1 的前提下，顺丰物流时间 <= 数据表最新物流的物流创建时间(create_time)
						if(strtotime($info['create_time']) > strtotime($item['OccurDatetime'])){

							// 顺丰的物流信息时间要+16h
							$add_time = intval(strtotime($item['OccurDatetime']))+57600;//+16h
							//判断+16h后是否会大于服务器当前时间
							$now_time = time();

							$add_time = ($add_time <= $now_time) ? date('Y-m-d H:i:s',$add_time) : date('Y-m-d H:i:s',$now_time);
							// print_r($add_time);
							$arr[$key]['OccurDatetime'] = $add_time;

							// 替换最新的物流信息
							$info['rount_time']  = $item['OccurDatetime'];
							$info['create_time'] = $add_time;

						}else{// 满足 条件1 的前提下，顺丰物流时间 > 数据表最新物流的物流创建时间(create_time)

							// 顺丰的物流信息时间不需要+16h
							// 替换最新的物流信息
							$info['rount_time']  = $item['OccurDatetime'];
							$info['create_time'] = $item['OccurDatetime'];

						}

						$arr[$key]['SFTime'] = $item['OccurDatetime'];
						// continue;
					}
					$info['status'] = $item['BusinessLinkCode'];
					// $info['rount_time']  = $item['OccurDatetime'];
				}

			}else{
				$info['create_time'] = $item['OccurDatetime'];
			}

			// $info['status']      = $item['BusinessLinkCode'];
			// $info['rount_time']  = $item['OccurDatetime'];
		}
// $arr = array_reverse($arr);
echo '结果';
print_r($arr);
	// }