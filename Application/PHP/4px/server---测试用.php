<?php
	require_once('./../../hprose_php5/HproseHttpServer.php');

	function save($arr){
		include('config.php');

		$nu = $arr['lastResult']['nu'];	//美快单号
		$data = $arr['lastResult']['data'];	//物流信息
        $count  = count($data);  //计算总数

		$sql = "select CID from mk_tran_list where `MKNO` = '$nu' LIMIT 1";
		$first = $pdo->query($sql);
		// $first->execute();
		// $sear_count = $first->rowCount();

		if($first->rowCount() > 0){
			$mk = $first->fetch(PDO::FETCH_ASSOC);
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
            $check_mkil[$key] = "SELECT * FROM mk_il_logs WHERE `MKNO` = '$nu' AND `content` = '$item[TrackingContent]' AND `create_time` = '$item[OccurDatetime]' AND `status` = '$item[BusinessLinkCode]'";

            $res_mkil[$key] = $pdo->query($check_mkil[$key]);

            $count_item[$key] = $res_mkil[$key]->fetchColumn();	//获取查询结果总数
            //如果不存在此条信息，则保存
            if($count_item[$key] == 0){
                // 将相关资料直接将记录增加到mk_il_log中,150911添加CID
                $sql_mkil[$key] = "INSERT INTO mk_il_logs (MKNO,content,create_time,status,CID) VALUES ('$nu', '$item[TrackingContent]', '$item[OccurDatetime]',$item[BusinessLinkCode],$mkcid)";

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

        	$tlsql  = "UPDATE mk_tran_list SET IL_state='$IL_state', ex_time='$ex_time', ex_context='$ex_context' WHERE MKNO='$nu' LIMIT 1";

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