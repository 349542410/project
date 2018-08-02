<?php
namespace Api\Controller;
use AUApi\Controller\KdnoConfig\Kdno17;
use Think\Controller;
	/*
	create by Man 160922 
	需使用在mk_api中
	作用：与ERP打印MK单时，直接称重与发中转
	功能：
		A.接收 ERP的中转号进行必要分析
		B.接收 ERP重量与中转号，进行面单入库，与中转的操作
	*/
class MkilErpController extends Controller{
	public function index()
	{
		echo 'hello world';
	}
	/*
	A.对中转号进行必要分析，
	荣POST来中转号信息
	{XXX(通常),"toMKIL":[{"TransitNo":""}]}
	杰收到后，按以下流程分析中转号($TransitNo)的准确性(使用tp.api.MkilErpController.class.php编写，参考GetcodeController)
	1 . 是否存在于transit_no中(transit_no.no=$TransitNo && transit_no.status=0)
	2 . 当前时间必须小于transit_no.date + 20小时
	返回LOGS {"TransitNo":"原样返回","TransitId":transit_no.tcid}
	如果有误 则返回LOGS{"TransitNo":"原样返回","TransitId":0;"LOGSTR":"错误说明"}
	*/
	public function TransitNo()
	{
		 $jn = new \Org\MK\JSON;
		 $js = $jn->get(); //数组

		 //var_dump($js);die();

		if(!is_array($js)){
			//返回错误
			$backArr = array('TransitNo'=>$TransitNo,'TransitId'=>'0','LOGSTR'=>'数据格式有误');
			//$LOGS = json_encode($backArr);
			//echo $LOGS;
			echo $jn->respons($js['KD'],$js['CID'],array($backArr));
			return;
		}

		$toMKIL = $js['toMKIL']; //这个是发来的中转号数组
		$TransitNo = $toMKIL[0]['TransitNo'];


		$check = M('TransitNo')->field('tcid,date')->where(array('no'=>$TransitNo,'status'=>'0'))->find();

		if($check){

			// 暂时取消 20小时 的使用限制 jie 20180503  试行一段时间，如果（中转操作，返仓，清关）操作均可正常运作，可考虑继续。
/*			$Tdate = strtotime($check['date']) + 72000;

			if(time() < $Tdate){
				$backArr = array('TransitNo'=>$TransitNo,'TransitId'=>$check['tcid']);
			}else{
				$backArr = array('TransitNo'=>$TransitNo,'TransitId'=>'0','LOGSTR'=>'该中转号已超出可使用时间');
			}*/
			$backArr = array('TransitNo'=>$TransitNo,'TransitId'=>$check['tcid']);

		}else{
			$backArr = array('TransitNo'=>$TransitNo,'TransitId'=>'0','LOGSTR'=>'中转号不存在,或已完成使用');
		}

		// $LOGS = json_encode($backArr);
		//$this->ajaxReturn($backArr);
		echo $jn->respons($js['KD'],$js['CID'],array($backArr));
		exit();
	}

	/*
	B.接收 ERP重量与中转号，进行面单入库，与中转的操作
	荣POST来重量与中转号
	{XXX(通常),"toMKIL":[{"MKNO":"美快单号","TransitNo":"","Weight":"","place":"仓库名称"}]}
	杰收到后
	1.tran_list.MKNO是否存在。
	2.TransitNo按上一个方式进行分析
	3.将更改tran_list的重量，增加logs称重记录，增加il_logs中place的收货记录
	4.logs,il_logs增加中转记录
	---------
	返回：
	当称重保存成功时,不论中转是否成功 都返回成功
	只要称重成功的，才成功中转操作
	返回LOGS {"TransitNo":"原样返回","MKNO":"原样返回","Code":"1成功,0为失败,2为称重重复","LOGSTR":"内容"}

	// 2017-08-11 jie
		由于需要加入一个新的系统（美快物流揽收系统）去使用此功能，所以，此功能需要作出相应修改；
		当 TransitNo = MKIL  的时候，就是表示属于此系统的操作方法
	*/
	public function Transit()
	{
		 $jn = new \Org\MK\JSON;
		 $js = $jn->get(); //数组

/*		// 测试用的数据
		$jn = '{"KD":"toMKIL","CID":"2","SID":"20","CMD5":"fc60e6bdcc5311037ca33c8c4137e007","STM":"20161011080139","LAN":"zh-cn","toMKIL":[{"MKNO":"MK881000469US","TransitNo":"TSF086","Weight":"0.5","place":"美国仓(北加州)"}]}';
		$js = json_decode($jn,true);*/

		if(!is_array($js)){
			//返回错误
			$backArr = array('TransitNo'=>$TransitNo,'MKNO'=>$MKNO,'Code'=>'0','LOGSTR'=>'数据格式有误');
			echo $jn->respons($js['KD'],$js['CID'],array($backArr));
			return;
		}

		$toMKIL = $js['toMKIL'][0]; //这个是发来的中转号数组

		$TransitNo    = $toMKIL['TransitNo'];
		$MKNO         = $toMKIL['MKNO'];
		$request_type = (isset($toMKIL['request_type'])) ? $toMKIL['request_type'] : ''; // 20171206 jie 用于erp请求的时候，跳过终端号的检查

		$checkMKNO = M('TranList')->where(array('MKNO'=>$MKNO))->find();

		//检查是否存在此美快单号
		if(!$checkMKNO){
			$backArr = array('TransitNo'=>$TransitNo,'MKNO'=>$MKNO,'Code'=>'0',"LOGSTR"=>"MKNO单号不存在");
			echo $jn->respons($js['KD'],$js['CID'],array($backArr));
			return;
		}

		// 2017-08-11 jie 美快物流揽收系统的识别码为 MKIL，当 TransitNo = 'MKIL' 的时候，无需执行 A判断
		if($TransitNo == 'MKIL'){
			$checkNo = true;

			$terminal_code = $toMKIL['terminal_code']; // 揽收终端号  20171030 jie
			$operatorId    = $toMKIL['operatorId'];    // 操作人ID   20171030 jie
			$operatorName  = $toMKIL['operatorName'];    // 操作人真实姓名   20171030 jie
		}else{
			// 判断中转号是否存在    A判断
			$checkNo = M('TransitNo')->field('id,tcid,date')->where(array('no'=>$TransitNo,'status'=>0))->find();
		}

		//检查是否存在此中转单号
		if($checkNo){

			// 2017-08-11 jie
			if($TransitNo == 'MKIL'){
				$Tdate = time() + 7200;
			}else{
				$Tdate = strtotime($checkNo['date']) + 72000; //20小时
			}

			// 该中转号的创建时间 + 20h ，大于现在的时间的话

			//if(time() < $Tdate){

				$Model = M();   //实例化
				$Model->startTrans();//开启事务

				$ct_time = date('Y-m-d H:i:s');

				$center = array();
				// 将更改tran_list的重量，增加logs称重记录，增加il_logs中place的收货记录
				// 查询中转中心管理的对应信息
				if($TransitNo != 'MKIL') $center = M('TransitCenter')->field('transit,toname')->where(array('id'=>$checkNo['tcid']))->find();

				// 2017-08-11 jie
				if($TransitNo == 'MKIL') $center['transit'] = 'MKIL';

				// dump($center);die;
				$logs_data['CID']     = $js['CID'];//$checkNo['tcid'];
				$logs_data['tranid']  = 0; //称重的时候默认0
				$logs_data['transit'] = $center['transit'];
				$logs_data['tranNum'] = 'MKILWeigh';
				$logs_data['mStr1']   = $toMKIL['place'];
				$logs_data['MKNO']    = $MKNO;
				$logs_data['weight']  = $toMKIL['Weight'];
				$logs_data['state']   = 12;//称重

				// 检查mk_logs是否已经存在此条数据
				$check_logs = M('Logs')->where(array('CID'=>$js['CID'],'tranid'=>'0','transit'=>$center['transit'],'tranNum'=>'MKILWeigh','mStr1'=>$toMKIL['place'],'MKNO'=>$MKNO,'weight'=>$toMKIL['Weight'],'state'=>'12'))->select();

				// 已存在，则不作任何操作，标记为0
				if($check_logs){
					$logs = 0;
				}else{// 不存在则新增
					$logs = M('Logs')->add($logs_data);
				}

				$il_data['MKNO']        = $MKNO;
				$il_data['content']     = '美快国际物流'.$toMKIL['place'].' 已揽收';
				$il_data['create_time'] = $ct_time;
				$il_data['status']      = 12;//称重
				$il_data['noid']        = 0; //称重的时候默认0
				$il_data['CID']         = $js['CID'];//$checkNo['tcid'];
				$il_data['rount_time']  = date('Y-m-d H:i:s');

				// 检查是否已经存在
				$check_il = M('IlLogs')->where(array('MKNO'=>$MKNO,'content'=>'美快国际物流'.$toMKIL['place'].' 已揽收','status'=>'12','noid'=>'0','CID'=>$js['CID']))->find();

				// 已存在，则不作任何操作，标记为0
				if($check_il){
					$ilLogs = 0;
				}else{// 不存在则新增
					$ilLogs = M('IlLogs')->add($il_data);

					// 20161104 Jie 放入这个位置，同步物流信息更新的时候才会运行，以防跟il_logs的信息不一致
					// $t_data['noid']       = $checkNo['id']; //称重的时候不需要更新这个
					$t_data['weight']     = $toMKIL['Weight'];
					$t_data['IL_state']   = 12;//称重
					$t_data['ex_time']    = $ct_time;
					$t_data['ex_context'] = '美快国际物流'.$toMKIL['place'].' 已揽收';
					$tlist = M('TranList')->where(array('MKNO'=>$MKNO))->save($t_data);
					// $tlist = M('TranList')->where(array('MKNO'=>$MKNO))->setField('weight',$toMKIL['Weight']);
				}

				// 2017-08-11 jie  如果TransitNo="MKIL" 则只生成揽收记录
				if($TransitNo == 'MKIL'){
					if($tlist !== false && $logs !== false && $ilLogs !== false){

						// 不是erp打单那一类型的请求，而是揽收系统的请求，则进去以下操作  20171206 jie
						if($request_type == ''){

							/* 20171030 新增 */
							// 根据终端编号，查询此终端编号信息
							$check_terminal = $this->check_terminal($terminal_code);// 20171030

							if($check_terminal['state'] == 'no'){
								$Model->rollback();//事务有错回滚
								$backArr = array('TransitNo'=>$TransitNo,'MKNO'=>$MKNO,'Code'=>'0', "LOGSTR"=>$check_terminal['msg']);
								echo $jn->respons($js['KD'],$js['CID'],array($backArr));
								return;
							}else{
								$Terminal = $check_terminal['Terminal'];

								$t_map['MKNO'] = $MKNO;

								//会员订单
								$check_tlist = M('TranUlist')->where($t_map)->find();

								if(!$check_tlist){
									$Model->rollback();//事务有错回滚
									$backArr = array('TransitNo'=>$TransitNo,'MKNO'=>$MKNO,'Code'=>'0', "LOGSTR"=>"该会员的订单信息不存在");
									echo $jn->respons($js['KD'],$js['CID'],array($backArr));
									return;
								}

								// 查询条件
								$many_map['member_id'] = array('eq', $check_tlist['user_id']);
								$many_map['order_id']  = array('eq', $check_tlist['id']);
								$many_map['tran_id']   = array('eq', $checkMKNO['id']);

								// 保存数据
								$many_data['manager_id']           = $operatorId; // 揽收人ID(员工ID)
								$many_data['manager_tname']        = $operatorName; // 揽收人真实姓名
								$many_data['point_id']             = $Terminal['point_id']; // 揽收点ID
								$many_data['terminal_of_point_id'] = $Terminal['id']; // 揽收点终端号ID

								// 检查该会员是否在终端操作（打印）过此订单
								$check_relation = M('PrintRelationOrder')->where($many_map)->find();

								// 已存在，则更新
								if($check_relation){
									$save_many = M('PrintRelationOrder')->where(array('id'=>$check_relation['id']))->save($many_data);
								}else{
									$Model->rollback();//事务有错回滚
									$backArr = array('TransitNo'=>$TransitNo,'MKNO'=>$MKNO,'Code'=>'0', "LOGSTR"=>"无法找到对应的数据进行更新");
									echo $jn->respons($js['KD'],$js['CID'],array($backArr));
									return;
								}
							}
							/* 20171030 新增 end */
						}

						if($ilLogs == 0){

							$Model->rollback();//事务回滚
							$backArr = array('TransitNo'=>$TransitNo,'MKNO'=>$MKNO,'Code'=>'2', "LOGSTR"=>"称重重复");
						}else{

							// 属于 中通 线路的订单  20171127 jie
							if($checkMKNO['TranKd'] == '17'){
								$this->toPush($checkMKNO['STNO'], 'Verified', '', $checkMKNO);// 推送“审单”节点 给中通（如果是中通的订单）
							}

							$Model->commit();//提交事务成功
							$backArr = array('TransitNo'=>$TransitNo,'MKNO'=>$MKNO,'Code'=>'1', "LOGSTR"=>"保存称重数据成功");
						}

					}else{
						$Model->rollback();//事务回滚
						$backArr = array('TransitNo'=>$TransitNo,'MKNO'=>$MKNO,'Code'=>'0', "LOGSTR"=>"未能保存称重数据，操作失败");

					}

					echo $jn->respons($js['KD'],$js['CID'],array($backArr));
					exit;
				}

				$otc = false;// 标记中转的所有操作是否都完成
				if($tlist !== false && $logs !== false && $ilLogs !== false){

					// 属于 中通 线路的订单  20171127 jie
					if($checkMKNO['TranKd'] == '17'){
						$this->toPush($checkMKNO['STNO'], 'Verified', '', $checkMKNO);// 推送“审单”节点 给中通（如果是中通的订单）
					}

					//称重的所有操作执行成功后继续往下操作
					$Model->commit();//称重的所有操作都成功，提交事务成功，完成这个事务

					$Model2 = M();   //实例化
					$Model2->startTrans();//开启下一个事务，用于中转

					// 检查是否已经存在
					$check_logs = M('Logs')->where(array('CID'=>$js['CID'],'tranid'=>$checkNo['id'],'transit'=>$center['transit'],'tranNum'=>$checkMKNO['STNO'],'mStr1'=>$toMKIL['TransitNo'],'MKNO'=>$MKNO,'weight'=>'0','state'=>'20'))->select();

					// 不存在则新增
					if(!$check_logs){
						$logs_data['tranid']  = $checkNo['id']; //中转的时候显示与之对应的
						$logs_data['tranNum'] = $checkMKNO['STNO'];
						$logs_data['mStr1']   = $toMKIL['TransitNo'];
						$logs_data['state']   = 20;//中转 20161104
						$logs_data['weight']  = 0;//20161104
						$logs_transit = M('Logs')->add($logs_data);
					}

					// 检查是否已经存在
					$check_il = M('IlLogs')->where(array('MKNO'=>$MKNO,'content'=>'已离开美快国际物流'.$toMKIL['place'].'，发往 '.$center['toname'],'status'=>'20','noid'=>$checkNo['id'],'CID'=>$js['CID']))->find();

					// 不存在则新增
					if(!$check_il){

						$t_time = date('Y-m-d H:i:s');

						$il_data['noid']        = $checkNo['id']; //中转的时候显示与之对应的
						$il_data['content']     = '已离开美快国际物流'.$toMKIL['place'].'，发往 '.$center['toname'];
						$il_data['create_time'] = $t_time;
						$il_data['status']      = 20;//中转

						$ilLogs_transit = M('IlLogs')->add($il_data);

						// 更新mk_tran_list的最新物流信息为中转
						// 20161104 Jie 放入这个位置，同步物流信息更新的时候才会运行，以防跟il_logs的信息不一致
						$t_data2['noid']       = $checkNo['id']; //中转的时候显示与之对应的
						$t_data2['IL_state']   = 20;//中转
						$t_data2['ex_time']    = $t_time;
						$t_data2['ex_context'] = '已离开美快国际物流'.$toMKIL['place'].'，发往 '.$center['toname'];
						$tlist_transit = M('TranList')->where(array('MKNO'=>$MKNO))->save($t_data2);
					}

					if($tlist_transit !== false && $logs_transit !== false && $ilLogs_transit !== false){

						$Model2->commit();//提交事务成功
						$otc = true;// 标记中转的所有操作是否都完成
					}else{
						$Model2->rollback();//事务有错回滚
					}

				}else{
					// 20161104 Jie 称重操作中任意一处操作失败，则终止并抛出错误
					$Model->rollback();//事务有错回滚
					//抛出错误并终止
					$backArr = array('TransitNo'=>$TransitNo,'MKNO'=>$MKNO,'Code'=>'0', "LOGSTR"=>"未能保存称重数据，操作失败");
					echo $jn->respons($js['KD'],$js['CID'],array($backArr));
					// dump($backArr);
					exit;
				}

				if($ilLogs == '0'){
					if($otc === true){
						$nox = '；已成功执行保存中转数据(此为额外提示信息)';
					}else{
						$nox = '；未能执行中转数据保存(此为额外提示信息)';
					}
					$dptime = strtotime($check_il['rount_time']);
					// echo $dptime;
					// $dptime = gmstrftime('%H:%M:%S',$dptime);
					$dptime = $this->formatDate($dptime);
					$other = '，'.$dptime.'已执行过称重操作'.$nox;
					$rcode = 2;//2为称重重复
				}else{
					$rcode = 1;
				}

				$backArr = array('TransitNo'=>$TransitNo,'MKNO'=>$MKNO,'Code'=>$rcode,"LOGSTR"=>"称重成功".$other);

			//}else{
				// $backArr = array('TransitNo'=>$TransitNo, 'MKNO'=>$MKNO, 'Code'=>'0', 'LOGSTR'=>'该中转号已超出可使用时间(TransitNo.date)');

			//}

			// dump($backArr);
			echo $jn->respons($js['KD'],$js['CID'],array($backArr));
		}else{
			$backArr = array('TransitNo'=>$TransitNo,'MKNO'=>$MKNO,'Code'=>'0',"LOGSTR"=>"该中转单号不存在");
			echo $jn->respons($js['KD'],$js['CID'],array($backArr));
			// return;
		}

        exit();
	}

	/**
	 * 检查 终端号 是否存在、可用，保存相关信息  20171030
	 * @param  [type] $terminal_code [终端编号]
	 * @return [type]                [description]
	 */
	public function check_terminal($terminal_code){
		// 根据终端编号，查询此终端编号信息
		$Terminal = M('SelfTerminalList')->where(array('terminal_name'=>$terminal_code))->find();
		if(!$Terminal){
			return array('state'=>'no', 'msg'=>'该终端编号不存在');
		}else{

			//终端机尚未激活
			if($Terminal['status'] == '0'){
				return array('state'=>'no', 'msg'=>'该终端编号尚未激活');
			}else{
				return array('state'=>'yes', 'Terminal'=>$Terminal);
			}
		}
	}

	/**
	 * 用于显示 操作时间间隔有多长
	 * @param  [type] $sTime [description]
	 * @return [type]        [description]
	 */
	function formatDate($sTime) {
		//sTime=源时间，cTime=当前时间，dTime=时间差
		$cTime  = time();
		$dTime  = $cTime - $sTime;
		$dDay   = intval(date("Ymd",$cTime)) - intval(date("Ymd",$sTime));
		$dYear  = intval(date("Y",$cTime)) - intval(date("Y",$sTime));
		if( $dTime < 60 ){
			$dTime =  $dTime."秒前";
		}else if( $dTime < 3600 ){
			$dTime =  intval($dTime/60)."分钟前";
		}else if( $dTime >= 3600 && $dDay == 0  ){
			$dTime =  "今天".date("H:i",$sTime);
		}else if($dYear==0){
			$dTime =  date("m-d H:i",$sTime);
		}else{
			$dTime =  date("Y-m-d H:i",$sTime);
		}
		return $dTime;
	}

    // 根据运单号 (审单)推送节点 给中通
    public function toPush($STNO, $push_state, $airno='', $data){
    	$arr = array(
    		'STNO'       => $STNO,
    		'push_state' => $push_state,
    		'airno'      => $airno,
    		'data'       => $data,
    	);
    	$Kdno = new Kdno17();
    	return $Kdno->SubmitTracking($arr);
    }
}