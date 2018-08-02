<?php
	
	/**
	 * 每次只执行一个单号的清关信息获取操作
	 * @param  [type] $tracking_number [单号]
	 * @param  [type] $EMS             [description]
	 * @param  [type] $config          [配置信息]
	 * @param  [type] $pdo             [description]
	 * @param  string $type            [判断是否只单独查询，不做任何处理]
	 * @return [type]                  [description]
	 */
	function getData($tracking_number, $EMS, $config, $pdo, $TranKd, $type=''){
		// print_r($tracking_number);die;
		// echo '<pre>';
		$result = $EMS->getTaxStatus($tracking_number, $config);
		$j_arr = json_decode($result, true);
		if($type != ''){
			return $j_arr;exit;
		}
		// print_r($j_arr);//查询清关返回的结果
		// die;

		if($j_arr['Status'] == 'success'){
			$reArr = $j_arr['Result'];
			// print_r($reArr);
			// $reArr = array_reverse($reArr);
			// print_r($reArr);die;

			$res = save($reArr,$pdo,$TranKd);

			if($res == true){
				return array('do'=>'yes', 'title'=>'成功获取并保存数据');      //返回信息
			}else{

				// 即使部分保存不成功，下次再执行申报单清关请求的时候，已经保存的数据不会再保存，所以可以不使用事务方式处理
				return array('do'=>'no', 'title'=>'获取数据'.count($reArr).'条，成功保存'.$res.'条');      //返回信息
			}

		}else{

			return array('do'=>'no', 'title'=>$j_arr['ErrorMessage']);      //返回信息
		}
	}

	/**
	 * 保存清关描述信息到 mk_trainer_logs
	 * 注意：即使部分清关信息保存不成功，但下次再执行该单的申报单清关状态查询的时候，已经保存的数据不会再保存，而只会保存尚未有的数据，所以可以不使用事务方式处理
	 * @param  [type] $item [数据数组]
	 * @return [type]       [description]
	 */
	function save($reArr,$pdo,$TranKd){
		// echo '<pre>';

		$i = 0;//统计成功保存数据的个数
		foreach($reArr as $key=>$item){
			//判断时间格式是否需要重设时间格式
			if(is_array($item['createDate'])){
				// print_r($item['createDate']);
				$time = $item['createDate'];
				$ctime = $time['year'];

				$ctime .= "-".sprintf('%02s',(intval($time['month'])+1));
				$ctime .= "-".sprintf('%02s',$time['dayOfMonth']);
				$ctime .= " ".sprintf('%02s',$time['hourOfDay']);
				$ctime .= ":".sprintf('%02s',$time['minute']);
				$ctime .= ":".sprintf('%02s',$time['second']);

				// print_r($ctime);echo '<br>';
				$stime = strtotime($ctime);
				// print_r($stime);echo '<br>';
				
				$de_time = 60*60*8;
				$stime -= $de_time;
				$stime = date('Y-m-d H:i:s',$stime);
				// print_r($stime);echo '<br>';
			}else{

				$stime = $item['createDate'];
			}

			// 20170204 jie
			// 用于更新数据表的时候需要的字段(无值的时候是需要默认为空)
			$pr_arr = array('shipmentNumber','taxNameForCn','LogisticsName');
			// 用于更新数据表的时候需要的字段(无值的时候是需要默认为0)
			$nu_arr = array('taxStatus');
			// 把香港E特快的字段(左)转换成数据表需要的字段名称(右) 20170205 jie
			$cop_arr = array('shipmentNumber'=>'LogisticsNo','taxNameForCn'=>'Result','taxStatus'=>'Status');

			$check_trainer = "SELECT * FROM mk_trainer WHERE `LogisticsNo` = '$item[shipmentNumber]'";

			$res_mkil = $pdo->query($check_trainer);

			$set = '';
			$val = '';

			// 检查是否已经存在此申报单的资料
			if($res_mkil->rowCount() > 0){
				$cinfo = $res_mkil->fetch(PDO::FETCH_ASSOC);

				foreach($item as $pey=>$it){
					if(in_array($pey,$pr_arr) || in_array($pey,$nu_arr)){
						$$pey = ((isset($it) ? count($it) : 0) == 0) ? ((in_array($pey,$nu_arr)) ? '0' : '') : htmlspecialchars($it);
						$set .= $cop_arr[$pey]."='".$$pey."',"; // 20170205 jie
					}
				}

				// $set .= "MftNo='',OrderNo='',CheckFlg='',CheckMsg='',PaySource='',StartTime=0,EndTime=0";
				$set = rtrim($set, ',');// 清除最右侧的英文逗号

				$add_sql = "UPDATE mk_trainer SET ".$set." WHERE id = '$cinfo[id]'";

			}else{//不存在此申报单，则查询新数据(插入数据)

				foreach($item as $pey=>$it){
					if(in_array($pey,$pr_arr) || in_array($pey,$nu_arr)){
						$$pey = ((isset($it) ? count($it) : 0) == 0) ? ((in_array($pey,$nu_arr)) ? '0' : '') : htmlspecialchars($it);
						$set .= $cop_arr[$pey].",";
						$val .= "'".$$pey."',";
					}
				}

				$set .= 'CreateTime,LogisticsName,TranKd';
				$val .= "'".$stime."','香港E特快',".$TranKd;// 记录当前查询的页数和结束时间
				
				// 以下字段默认为空或者0，因为香港E特快没有这些数据返回				
				$set .= ',MftNo,OrderNo,CheckFlg,CheckMsg,PaySource,StartTime,EndTime';
				$val .= ",'','','','','',0,0";// 20160205  jie

				$set = rtrim($set, ',');// 清除最右侧的英文逗号
				$val = rtrim($val, ',');// 清除最右侧的英文逗号

				$add_sql = "INSERT INTO mk_trainer (".$set.") VALUES (".$val.")";

			}
			// 20170204 End

			// print_r($set);echo '<br>';
			// print_r($val);echo '<br>';
			// print_r($add_sql);
			// die;

			$check_logs = "SELECT * FROM mk_trainer_logs WHERE `LogisticsNo` = '$item[shipmentNumber]' AND `Status` = '$item[taxStatus]' AND `CreateTime` = '$stime'";

			$res_logs = $pdo->query($check_logs);

			// 已存在记录
			if($res_logs->rowCount() > 0){
				$linfo = $res_logs->fetch(PDO::FETCH_ASSOC);
				$add_logs = "UPDATE mk_trainer_logs SET Status='$item[taxStatus]', content='$item[taxNameForCn]', TaxTotal='$item[taxTotal]' WHERE id = '$linfo[id]'";

			}else{//未有记录，则新增
				$add_logs = "INSERT INTO mk_trainer_logs (LogisticsNo,Status,content,CreateTime,TaxTotal) VALUES ('$item[shipmentNumber]', '$item[taxStatus]', '$item[taxNameForCn]', '$stime', '$item[taxTotal]')";
			}

			if($pdo->exec($add_sql) !== false && $pdo->exec($add_logs) !== false){
				$i++;
			}
		}

		if($i == count($reArr)){
			return true;
		}else{
			return $i;
		}

	}