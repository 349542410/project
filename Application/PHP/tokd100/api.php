<?php
	// include('config.php');
	require_once('../db.php');//数据库连接
	include('function.php');
	// global $pdo;

	if(!isset($noechoyn)){
		$gid   = isset($_GET['id']) ? trim($_GET['id']) :'';
		$com   = isset($_GET['com']) ? trim($_GET['com']) : '';
		$nu    = isset($_GET['nu']) ? trim($_GET['nu']) : '';
		$show  = isset($_GET['show']) ? trim($_GET['show']) : '';//trim($standard['show']);	//返回类型：json或者xml
		$muti  = isset($_GET['muti']) ? trim($_GET['muti']) : '';//trim($standard['muti']);	//如果接收到等于0，则返回最新的一条信息
		$order = isset($_GET['order']) ? trim($_GET['order']) : '';//trim($standard['order']);	//默认按时间由新到旧排列
	}

	$noechoyn = isset($noechoyn) ? $noechoyn : false;

	// 如果不是通过api_cli.php请求的，则执行以下验证
	if($noechoyn == false){
		//验证单号格式
		$ismkno = preg_match($mkno_rule,$nu);
		if($gid != $id || $com != $mkcom || strlen($gid) != 16 || !$ismkno){
			$msg = 'false';
			$status = '0';
			$state = 0;
			if($show == 0){
				$bstr = Tjson($msg,$status,$state,array(),$noechoyn);
			}else{
				$bstr = Txml($msg,$nu,$com,$status,$state,array(),$noechoyn);
			}
			return;
		}
	}
	
	//如果$muti=0，则只查询最新一条物流信息，否则查询该单全部物流信息
	$sql = $muti == '0' ? "select MKNO,content,create_time from mk_il_logs where `MKNO` = '$nu' order by `create_time` desc limit 1" : "select MKNO,content,create_time from mk_il_logs where `MKNO` = '$nu' order by `create_time` $order";

	//取该单的所有物流信息中最大的id值即就是最新的物流信息状态
	$sta_sql = "select status From mk_il_logs where id = (select max(id) from mk_il_logs where `MKNO` = '$nu')";
	// dump($sta_sql);

	$sta_res = $pdo->query($sta_sql);
	$staArr = $sta_res->fetchAll(PDO::FETCH_ASSOC);
	// dump($staArr[0]['status']);die;
	$sta = $staArr[0]['status'];	//最后执行发送的mk_il_logs.id的值
	$state = tran_state($sta);

	$mk = $pdo->prepare($sql);//查询数据库
	$mk->execute();
	$num = $mk->rowCount();	///计算总数
	//接口出现异常
    if($num == 0){
		$msg = 'false';
		$status = '0';
		if($show == 0){
			$bstr = Tjson($msg,$status,$state,array(),$noechoyn);
		}else{
			$bstr = Txml($msg,$nu,$com,$status,$state,array(),$noechoyn);
		}
        return;

    }else{
    	$msg = 'ok';
    	$status = '1';
		$data = array();
		foreach($mk as $k=>$item){
			$data[$k]['time'] = $item['create_time'];
			$data[$k]['context'] = $item['content'];
		}

		if($show == 0){
			$bstr = Tjson($msg,$status,$state,$data,$noechoyn);
		}else{
			$bstr = Txml($msg,$nu,$com,$status,$state,$data,$noechoyn);
		}
		return;
    }