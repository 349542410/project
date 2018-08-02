<?php
	include('config.php');
	include('order_function.php');

	if(!isset($noechoyn)){
		$sign      = trim($_POST['sign']);
		$gcustomer = trim($_POST['customer']);
		$param     = trim($_POST['param']);
		$param_arr = json_decode($param,true);
		$company   = trim($param_arr['company']);
	}

	$noechoyn = isset($noechoyn) ? $noechoyn : false;

	// print_r($param.$key);
	// print_r(md5($param.$key));
	
	// 如果不是通过order_cli.php请求的，则执行以下验证
	if($noechoyn == false){
		/* 开始验证 */
		if($sign != md5($param.$key)){
			$bstr = Tjson('false','503','验证签名失败',$noechoyn);
			return;
		}

		if($gcustomer != $customer){
			$bstr = Tjson('false','500','请求格式错误',$noechoyn);
			return;
		}

		if($company != $mgcom){
			$bstr = Tjson('false','500','请求格式错误',$noechoyn);
			return;
		}
	}

	//code 是否存在(通过tran_list.MKNO)
	$ssql = "select * from mk_tran_list where `MKNO` = '$param_arr[code]' limit 1";
	$search = $pdo->prepare($ssql);
	$search->execute();
	$num = $search->rowCount();
	if($num == 0){
		$bstr = Tjson('false','504','单号错误',$noechoyn);
		return;
    }

	//param里的四行均需填写 否则返回400
	foreach($param_arr as $item){
		if(trim($item) == ''){
			$bstr = Tjson('false','400','数据不完整',$noechoyn);
			return;
		}
	}
	/* 验证结束 */

	/* 保存之前查询是否已经存在此MKNO */
	$csql = "select * from mk_tran_share where `MKNO` = '$param_arr[code]' AND `customer` = '$gcustomer' limit 1";
	$check = $pdo->prepare($csql);
	$check->execute();

	//如果不存在则执行新增
	$time = time();
	$nowdate = date('Y-m-d H:i:s',$time);
	$Strsql = '';

	if($check->rowCount() == 0){
		/* 执行数据新增保存 */
		$Strsql = "INSERT INTO mk_tran_share (MKNO,company,customer,callback,status,cretime,lastime) VALUES ('$param_arr[code]', '$param_arr[company]', '$gcustomer', '$param_arr[callback]',0,'$nowdate','$time')";
    }else if($param_arr['operator'] == 'repush'){
    	//通过id来进行更新
    	$Strsql = "UPDATE mk_tran_share SET company='$param_arr[company]', callback='$param_arr[callback]', status=0, lastime='$time' WHERE MKNO='$param_arr[code]' AND `customer` = '$gcustomer' LIMIT 1";
    }

    if($Strsql != ''){
    	// echo $Strsql;
    	// die;
		$save = $pdo->exec($Strsql);
		// echo '影响行数：'.$save;
		if($save !== false){

			if($check->rowCount() == 0){
				$bstr = Tjson('true','200','成功',$noechoyn);
				return;
			}else if($param_arr['operator'] == 'repush'){
				$bstr = Tjson('true','502','重复订阅',$noechoyn);
				return;
			}
			
		}else{
			$bstr = Tjson('false','501','服务器错误',$noechoyn);
			return;
		}
    
    }else{
    	$bstr = Tjson('false','502','重复订阅',$noechoyn);
		return;
    }