<?php
/**
 * 宁波商品报备
 * 创建日期：2016-08-26
 * 修改日期：2017-01-17
 */
	include('config.php');
	include('function.php');
	header('Content-type:text/html;charset=UTF-8');	//设置输出格式

	// 先把数据表中的100条赋予一个标识码(用时间戳)，以便马上锁定这批需要处理的数据不会跟其他同时运行的程序冲突
	$locked_sql = "UPDATE MIS_Goods_Record SET uuid='$uuid' WHERE (Status <> '2' OR GjStatus <> '0') AND uuid < '$oneHour' ORDER BY Id ASC LIMIT $limit";
	// echo $locked_sql;die;
	$pdo->query($locked_sql);

	// 然后，把已经被这次运行的 $uuid 标识过的数据都找出来
	$find_sql = "select ProductId,DsSku from MIS_Goods_Record where uuid = '$uuid'";
	$find = $pdo->query($find_sql);

	// die;

	if($find->rowCount() > 0){

		$su_get  = 0;	//返回的T 为成功的个数
		$fal_get = 0;	//返回的F 为成功的个数
		$s       = 0;	//成功更新个数
		$f       = 0;	//更新失败的个数

		$list = $find->fetchAll(PDO::FETCH_ASSOC);

		foreach($list as $key=>$nb){

			// 传入ProductId、DsSku以便生成XML报文
			$xmlstr[$key] = createXML($nb['ProductId'], $nb['DsSku']);

			$timestamp = date('Y-m-d H:i:s');	// 当前时间
			$he        = $userid.$pwd.$timestamp;	//拼接
			$sign      = md5($he);	//拼接后再MD5加密

			//需要发送的数据
			$form[$key] = array(
				'xmlstr'    => $xmlstr[$key],
				'msgtype'   => $msgtype,
				'customs'   => $customs,
				'userid'    => $userid,
				'timestamp' => $timestamp,
				'sign'      => $sign,
			);

			$res[$key] = sendXML($url, $form[$key]);	// 发送请求，备案商品查询(根据货号查询)

			// print_r($res);
			$result[$key] = json_decode(json_encode((array) simplexml_load_string($res[$key])), true);// 返回的XML报文转为数组
			// print_r($result[$key]);
			// die;
			// var_dump($result[$key]);
			// echo '<br />';
			// echo '<br />';
			// 查询请求操作成功 T
			if(isset($result[$key]['Header']['Result']) && $result[$key]['Header']['Result'] == 'T'){

				$data = $result[$key]['Body'];//返回的物流信息

				if(isset($data['ProductId'])){

					$ProductId = $data['ProductId'];

					// 用于更新数据表的时候需要的字段(无值的时候是需要默认为空)
					$pr_arr = array('GoodsName','GoodsEnName','TariffNo','HsCode','Property','Brand','OriginPlace','Unit','Guse','Gcomposition','Gfunction','Detail','DsSkuCode','Comments','WarehouseName');
					// 用于更新数据表的时候需要的字段(无值的时候是需要默认为0)
					$nu_arr = array('Tax','Status','GjStatus','GjLockFlag','BizType','Tariff','AddedValueTax','ConsumptionDuty');

					$set = '';
					foreach($data as $pey=>$it){
						if(in_array($pey,$pr_arr) || in_array($pey,$nu_arr)){
							$$pey = ((isset($it) ? count($it) : 0) == 0) ? ((in_array($pey,$nu_arr)) ? '0' : '') : htmlspecialchars($it);
							$set .= $pey."='".$$pey."',";
						}
					}

					$set = rtrim($set, ',');//清除最右侧的英文逗号
					
					// $GoodsName       = ((isset($data['GoodsName']) ? count(($data['GoodsName'])) : 0) == 0) ? '' : $data['GoodsName'];
					// $GoodsEnName     = ((isset($data['GoodsEnName']) ? count(($data['GoodsEnName'])) : 0) == 0) ? '' : $data['GoodsEnName'];
					// $TariffNo        = ((isset($data['TariffNo']) ? count(($data['TariffNo'])) : 0) == 0) ? '' : $data['TariffNo'];
					// $HsCode          = ((isset($data['HsCode']) ? count(($data['HsCode'])) : 0) == 0) ? '' : $data['HsCode'];
					// $Property        = ((isset($data['Property']) ? count(($data['Property'])) : 0) == 0) ? '' : $data['Property'];
					// $Brand           = ((isset($data['Brand']) ? count(($data['Brand'])) : 0) == 0) ? '' : $data['Brand'];
					// $OriginPlace     = ((isset($data['OriginPlace']) ? count(($data['OriginPlace'])) : 0) == 0) ? '' : $data['OriginPlace'];
					// $Unit            = ((isset($data['Unit']) ? count(($data['Unit'])) : 0) == 0) ? '' : $data['Unit'];
					// $Tax             = ((isset($data['Tax']) ? count(($data['Tax'])) : 0) == 0) ? 0 : $data['Tax'];
					// $Status          = ((isset($data['Status']) ? count(($data['Status'])) : 0) == 0) ? 0 : $data['Status'];
					// $GjStatus        = ((isset($data['GjStatus']) ? count(($data['GjStatus'])) : 0) == 0) ? 0 : $data['GjStatus'];
					// $GjLockFlag      = ((isset($data['GjLockFlag']) ? count(($data['GjLockFlag'])) : 0) == 0) ? 0 : $data['GjLockFlag'];
					// $Guse            = ((isset($data['Guse']) ? count(($data['Guse'])) : 0) == 0) ? '' : $data['Guse'];
					// $Gcomposition    = ((isset($data['Gcomposition']) ? count(($data['Gcomposition'])) : 0) == 0) ? '' : $data['Gcomposition'];
					// $Gfunction       = ((isset($data['Gfunction']) ? count(($data['Gfunction'])) : 0) == 0) ? '' : $data['Gfunction'];
					// $Detail          = ((isset($data['Detail']) ? count(($data['Detail'])) : 0) == 0) ? '' : $data['Detail'];
					// $DsSkuCode       = ((isset($data['DsSkuCode']) ? count(($data['DsSkuCode'])) : 0) == 0) ? '' : $data['DsSkuCode'];
					// $Comments        = ((isset($data['Comments']) ? count(($data['Comments'])) : 0) == 0) ? '' : $data['Comments'];
					// $WarehouseName   = ((isset($data['WarehouseName']) ? count(($data['WarehouseName'])) : 0) == 0) ? '' : $data['WarehouseName'];
					// $BizType         = ((isset($data['BizType']) ? count(($data['BizType'])) : 0) == 0) ? 0 : $data['BizType'];
					// $Tariff          = ((isset($data['Tariff']) ? count(($data['Tariff'])) : 0) == 0) ? 0 : $data['Tariff'];
					// $AddedValueTax   = ((isset($data['AddedValueTax']) ? count(($data['AddedValueTax'])) : 0) == 0) ? 0 : $data['AddedValueTax'];
					// $ConsumptionDuty = ((isset($data['ConsumptionDuty']) ? count(($data['ConsumptionDuty'])) : 0) == 0) ? 0 : $data['ConsumptionDuty'];

					// 更新数据
					$save_sql = "UPDATE MIS_Goods_Record SET ".$set." WHERE ProductId = '$ProductId'";
					// $save_sql = "UPDATE MIS_Goods_Record SET GoodsName='$GoodsName', GoodsEnName='$GoodsEnName', TariffNo='$TariffNo', HsCode='$HsCode', Property='$Property', Brand='$Brand', OriginPlace='$OriginPlace', Unit='$Unit', Tax='$Tax', Status='$Status', GjStatus='$GjStatus', GjLockFlag='$GjLockFlag', Guse='$Guse', Gcomposition='$Gcomposition', Gfunction='$Gfunction', Detail='$Detail', DsSkuCode='$DsSkuCode', Comments='$Comments', WarehouseName='$WarehouseName', BizType='$BizType', Tariff='$Tariff', AddedValueTax='$AddedValueTax', ConsumptionDuty='$ConsumptionDuty' WHERE ProductId = '$ProductId'";

					// echo $save_sql;
					// $save = $pdo->query($save_sql);

					if($pdo->exec($save_sql) !== false){
	                    $s++;//成功更新数据的个数
	                }else{
	                	$f++;//更新失败的个数
	                }

	                $su_get++;//返回的T 为成功的个数

				}else{
					$fal_get++;//返回的F 为失败的个数
				}

			}else{// F 暂不处理

				$fal_get++;//返回的F 为失败的个数
				
				if(isset($result[$key]['Header']['ResultMsg']) && $result[$key]['Header']['ResultMsg'] == '该货号不存在'){
					// 暂不处理
				}
			}

		}

		echo '商品报备查询结果：<br />共发送'.$limit.'个，成功：'.$su_get.'个，失败：'.$fal_get.'个<br />数据更新成功：'.$s.'个，更新失败：'.$f.'个';
	
	}else{
		echo '商品报备查询已全部完成';
	}