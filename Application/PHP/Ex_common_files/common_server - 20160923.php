<?php
/**
 * sfus、4px等物流信息处理 服务器端 20160823 V1.0
 */
	require_once('./../../hprose_php5/HproseHttpServer.php');

	/**
	 * [save description]
	 * @param  [type]  $arr     [物流信息数组]
	 * @param  string  $sno     [查询单号，默认MKNO]
	 * @return [type]           [description]
	 */
	function save($arr, $sno='MKNO'){// 20160818 Jie

		$lastResult = $arr['lastResult'];
		$data       = $lastResult['data'];
		$nu         = $lastResult['nu'];	//单号
		$count      = count($data); //计算总数
		$status     = $arr['status'];   //状态
		$state      = isset($lastResult['state']) ? $lastResult['state'] : 0;    //物流的派送状态

		$sql = "select MKNO,CID from mk_tran_list where $sno = '$nu' LIMIT 1";// 20160818 Jie

		// $first = $pdo->query($sql);

		$result = logistics($nu,$data,$count,$status,$state,$sql,$sno);// 20160818 Jie

		return $result;

	}

	/**
	 * 通用型处理方法 20160818 Jie V1.1
	 * @param  [type] $nu     [description]
	 * @param  [type] $data   [description]
	 * @param  [type] $count  [description]
	 * @param  [type] $status [description]
	 * @param  [type] $state  [description]
	 * @param  [type] $sql    [description]
	 * @return [type]         [description]
	 */
	function logistics($nu,$data,$count,$status,$state,$sql,$sno){
		include('config.php');

		$first = $pdo->query($sql);

		if($first->rowCount() > 0){
			$mk    = $first->fetch(PDO::FETCH_ASSOC);
			$mkcid =  isset($mk['CID']) ? $mk['CID'] : 0;
		}else{
			// $mkcid = 0;
			return array('do'=>'no', 'title'=>'单号不存在');
		}
		// return $mkcid;die;

		$res1   = 0;
		$res2   = 0;	//测试用，数据比较
		$pdo->beginTransaction();	//开启事务

		// foreach($data as $key=>$item){
		for($key=$count-1;$key>-1; $key--){
			$item = $data[$key];
			// MKIL   先查询 mk_il_logs 是否已经存在某条数据
			$check_mkil[$key] = "SELECT * FROM mk_il_logs WHERE `MKNO` = '$mk[MKNO]' AND `content` = '$item[TrackingContent]' AND `create_time` = '$item[OccurDatetime]' AND `status` = '$item[BusinessLinkCode]'";
			// $check_mkil[$key] = "SELECT * FROM mk_il_logs WHERE `MKNO` = '$mk[MKNO]' AND `create_time` = '$item[OccurDatetime]' AND `status` = '$item[BusinessLinkCode]'";

			$res_mkil[$key] = $pdo->query($check_mkil[$key]);

			$count_item[$key] = $res_mkil[$key]->fetchColumn();	//获取查询结果总数
			//如果不存在此条信息，则保存
			if($count_item[$key] == 0){
				// 将相关资料直接将记录增加到mk_il_log中,150911添加CID
				$sql_mkil[$key] = "INSERT INTO mk_il_logs (MKNO,content,create_time,status,CID) VALUES ('$mk[MKNO]', '$item[TrackingContent]', '$item[OccurDatetime]',$item[BusinessLinkCode],$mkcid)";

				//执行保存成功则+1
				if($pdo->exec($sql_mkil[$key]) !== false){
					$res1++;
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
				return $msg = array('do'=>'yes', 'title'=>'操作成功','n1'=>$res1,'n2'=>$res2);     //返回信息
			}else{
				$pdo->rollback();    //事务回滚
				return $msg = array('do'=>'no', 'title'=>'操作失败，事务回滚','n1'=>$res1,'n2'=>$res2);      //返回信息
			}
			
		}else{
			$pdo->rollback();    //事务回滚
			return $msg = array('do'=>'no', 'title'=>'操作失败，事务回滚','n1'=>$res1,'n2'=>$res2);      //返回信息
		}
	}

	$server = new HproseHttpServer();
	$server->setErrorTypes(E_ALL);
	$server->setDebugEnabled();
	$server->addFunction('save');
	$server->start();