<?php
/**
 * 原始版本号：V1.0
 * 更新版本号：V1.1
 * sfus、4px、HkEms、GdEms等物流信息处理 服务器端 20160823
 * 20161103 Jie  增加函数toTimeZone(美国夏令时和冬令时跟中国时区之间的时间转换)
 */
	require_once('../../hprose_php5/HproseHttpServer.php');

	/**
	 * [save description]
	 * @param  [type]  $arr     [物流信息数组]
	 * @param  string  $sno     [查询单号，默认MKNO]
	 * @param  string  $toor    [用于区别顺丰与其他快递，若为'SF'，则需要进行顺丰专用的方法proof_time()]
	 * @return [type]           [description]
	 */
	function save($arr, $sno='MKNO',$toor=''){// 20160818 Jie

		$lastResult = $arr['lastResult'];
		$data       = $lastResult['data'];
		$nu         = $lastResult['nu'];	//单号
		$count      = count($data); 		//计算总数
		$status     = $arr['status'];   	//状态
		$state      = isset($lastResult['state']) ? $lastResult['state'] : 0;    //物流的派送状态

		$sql = "select MKNO,CID from mk_tran_list where $sno = '$nu' LIMIT 1";// 20160818 Jie

		return logistics($nu, $data, $count, $status, $state, $sql, $sno, $toor);// 20160818 Jie
	}

	/**
	 * 通用型处理方法 20160818 Jie V1.1
	 * @param  [type] $nu     [MKNO 或 STNO]
	 * @param  [type] $data   [物流信息数组]
	 * @param  [type] $count  [物流信息数组统计总数]
	 * @param  [type] $status [状态]
	 * @param  [type] $state  [物流的派送状态]
	 * @param  [type] $sql    [sql语句]
	 * @param  [type] $sno    [查询单号（MKNO或STNO），默认MKNO]
	 * @param  [type] $toor   [用于区别顺丰与其他快递，若为'SF'，则需要进行顺丰专用的方法proof_time()]
	 * @return [type]         [description]
	 */
	function logistics($nu, $data, $count, $status, $state, $sql, $sno, $toor){
        require_once ('../db.php');

        $first = $pdo->query($sql);
        if($first->rowCount() > 0){
            $mk    = $first->fetch(PDO::FETCH_ASSOC);
            $mkcid =  isset($mk['CID']) ? $mk['CID'] : 0;
        }else{
            // $mkcid = 0;
            return array('do'=>'no', 'title'=>'单号不存在');
        }

        if($toor == 'SF'){
            // return $data;
            $data = proof_time($mk['MKNO'],$data);
            // return $data;
            $count = count($data);
            // 当时count($data) = 0 的时候，物流信息已经全部更新完
            if(count($data) < 1){
                return array('do'=>'yes', 'title'=>'该单号的物流信息已经是最新');      //返回信息
            }
        }

        $res1   = 0;	//成功保存数
        $res2   = 0;	//测试用，数据比较  执行保存次数
        $pdo->beginTransaction();	//开启事务

        for($key=$count-1;$key>-1; $key--){
            $item = $data[$key];

			// MKIL   先查询 mk_il_logs 是否已经存在某条数据
			$check_mkil = "SELECT * FROM mk_il_logs WHERE `MKNO` = '$mk[MKNO]' AND `content` = '$item[TrackingContent]' AND `status` = '$item[BusinessLinkCode]'";
			// $check_mkil[$key] = "SELECT * FROM mk_il_logs WHERE `MKNO` = '$mk[MKNO]' AND `create_time` = '$item[OccurDatetime]' AND `status` = '$item[BusinessLinkCode]'";

			$res_mkil[$key] = $pdo->query($check_mkil);

			$count_item[$key] = $res_mkil[$key]->fetchColumn();	//获取查询结果总数
			//如果不存在此条信息，则保存
			if($count_item[$key] == 0){
				// 将相关资料直接将记录增加到mk_il_log中,150911添加CID
				if($toor == 'SF'){

					// 20161011 //此处只用于针对顺丰，由于在原顺丰物流信息最后添加了一条我方公司的物流信息，所以需要处理
					$check_mk = "SELECT * FROM mk_il_logs WHERE `MKNO` = '$mk[MKNO]' AND `content` = '$item[TrackingContent]' AND `status` = '$item[BusinessLinkCode]'";
					$res_mk = $pdo->query($check_mk);// 20161011

					// 20161011
					if($res_mk->fetchColumn() == 0){

						// 20160923
						$sql_mkil = "INSERT INTO mk_il_logs (MKNO,content,create_time,status,CID,rount_time) VALUES ('$mk[MKNO]', '$item[TrackingContent]', '$item[OccurDatetime]', $item[BusinessLinkCode], $mkcid, '$item[SFTime]')";
					}else{
						$sql_mkil = '';// 20161011
					}

				}else{
					$sql_mkil = "INSERT INTO mk_il_logs (MKNO,content,create_time,status,CID,rount_time) VALUES ('$mk[MKNO]', '$item[TrackingContent]', '$item[OccurDatetime]', $item[BusinessLinkCode], $mkcid, '$item[OccurDatetime]')";
				}

				if($sql_mkil != ''){//20161011
					//执行保存成功则+1 20160923
					if($pdo->exec($sql_mkil) !== false){
						$res1++;
					}
				}else{//20161011 Jie
					$res1++;//此处只用于针对顺丰，由于在原顺丰物流信息最好添加了一条我方公司的物流信息，所以需要处理
				}

				$res2++;
				
			}else if($res_mkil[$key]){	//已经存在也+1
				$res1++;
			}

		}

		if(count($data) == $res1){

			$IL_state   = $data['0']['BusinessLinkCode'];
			$ex_time    = $data['0']['OccurDatetime'];
			$ex_context = $data['0']['TrackingContent'];

			$tlsql  = "UPDATE mk_tran_list SET IL_state='$IL_state', ex_time='$ex_time', ex_context='$ex_context' WHERE $sno='$nu' LIMIT 1";

			if($pdo->exec($tlsql) !== false){
				$pdo->commit();      //事务确认
				return array('do'=>'yes', 'title'=>'操作成功','成功保存数'=>$res1,'执行保存次数'=>$res2);     //返回信息
			}else{
				$pdo->rollback();    //事务回滚
				return array('do'=>'no', 'title'=>'tranlist数据更新操作失败，事务回滚','成功保存数'=>$res1,'执行保存次数'=>$res2);      //返回信息
			}
			
		}else{
			$pdo->rollback();    //事务回滚
			return array('do'=>'no', 'title'=>'物流信息保存操作失败，事务回滚','成功保存数'=>$res1,'执行保存次数'=>$res2);      //返回信息
		}
	}

	// 顺丰专用 物流时间统一转为中国时间  20161010 Jie
	function proof_time($MKNO,$arr){
		include('../db.php');
		$info = array();
		$arr  = array_reverse($arr);//返回翻转顺序的数组

		$find_sql = "SELECT status,create_time,rount_time FROM mk_il_logs WHERE MKNO = '$MKNO' ORDER BY id DESC LIMIT 1";
		$find = $pdo->query($find_sql);

		$info = $find->fetch(PDO::FETCH_ASSOC);

		foreach($arr as $key=>$item){

			if($find->rowCount() > 0){
				
				// 20161102 Jie 顺丰有可能会称重(12)之后就直接揽件
				if($info['status'] >= 12){

					$info['rount_time'] = ($info['rount_time'] == '') ? $info['create_time'] : $info['rount_time'];

					// 夏令时+15h   非夏令时+16h
					// 20161103 Jie 根据美国夏令时和冬令时的转变而自动转换与中国北京时间的时间差
					$add_time = strtotime(toTimeZone($item['OccurDatetime']));

					//判断+15h或+16h之后是否会大于服务器当前时间
					$now_time = time();

					$add_time = ($add_time <= $now_time) ? date('Y-m-d H:i:s',$add_time) : date('Y-m-d H:i:s',$now_time);
					$arr[$key]['SFTime'] = $item['OccurDatetime'];

					// 数据表最新物流的原始物流时间(rount_time) >= 顺丰物流时间
					if(strtotime($info['rount_time']) >= strtotime($item['OccurDatetime'])){
						// $arr[$key]['SFTime'] = $item['OccurDatetime'];

						if($info['status'] < 1000){
							$arr[$key]['OccurDatetime'] = $add_time;
							$info['rount_time']  = $item['OccurDatetime'];
							$info['create_time'] = $add_time;
						}else{
							unset($arr[$key]);
						}

					}else{// 顺丰物流时间 > 数据表最新物流的原始物流时间(rount_time) (条件1)

						// 满足 条件1 的前提下，顺丰物流时间 <= 数据表最新物流的物流创建时间(create_time)
						if(strtotime($info['create_time']) > strtotime($item['OccurDatetime'])){

							$arr[$key]['OccurDatetime'] = $add_time;

							// 替换最新的物流信息
							$info['rount_time']  = $item['OccurDatetime'];
							$info['create_time'] = $add_time;

						}else{// 满足 条件1 的前提下，顺丰物流时间 > 数据表最新物流的物流创建时间(create_time)

							// 替换最新的物流信息 // 顺丰的物流信息时间不需要+16h
							$info['create_time'] = $item['OccurDatetime'];
							$info['rount_time']  = $item['OccurDatetime'];
						}
						// $arr[$key]['SFTime'] = $item['OccurDatetime'];
					}
					$info['status'] = $item['BusinessLinkCode'];
				}

			}else{
				// $info['create_time'] = $item['OccurDatetime'];
			}

		}

		return array_reverse($arr);//返回翻转顺序的数组
	}

	/*
	 * 时区转换  20161103 Jie
	 * America/Los_Angeles  [美国/洛杉矶]
	 * PRC   				[中华人民共和国]
	 * 每年三月的第二个礼拜天凌晨两点夏令时开始，于十一月的第一个礼拜天凌晨两点结束，全美国都是如此。洛杉矶所在的太平洋时区在夏令时阶段比北京晚15小时，冬
	 * 令时阶段晚16小时。
	 */
	function toTimeZone($src, $from_tz = 'America/Los_Angeles', $to_tz = 'PRC', $fm = 'Y-m-d H:i:s') {
	    $datetime = new DateTime($src, new DateTimeZone($from_tz));
	    $datetime->setTimezone(new DateTimeZone($to_tz));
	    return $datetime->format($fm);
	}

	$server = new HproseHttpServer();
	$server->setErrorTypes(E_ALL);
	$server->setDebugEnabled();
	$server->addFunction('save');
	$server->start();