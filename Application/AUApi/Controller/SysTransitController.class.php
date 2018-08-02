<?php
/**
 * 中转操作（新揽收系统专用）
 * 功能：对订单进行揽收的实际操作
 * 创建时间：2018-03-21
 * 创建人：jie
 */
namespace AUApi\Controller;
use Think\Controller;
class SysTransitController extends Controller{
	public function index($js){

		$toMKIL = $js['toMKIL'][0]; //这个是发来的中转号数组

		$TransitNo    = $toMKIL['TransitNo'];
		$MKNO         = $toMKIL['MKNO'];
		$request_type = (isset($toMKIL['request_type'])) ? $toMKIL['request_type'] : ''; // 20171206 jie 用于erp请求的时候，跳过终端号的检查

		$checkMKNO = M('TranList')->where(array('MKNO'=>$MKNO))->find();

		//检查是否存在此美快单号
		if(!$checkMKNO){
			return array('TransitNo'=>$TransitNo, 'MKNO'=>$MKNO, 'Code'=>'0', "LOGSTR"=>"MKNO单号不存在");
		}

		$terminal_code = $toMKIL['terminal_code']; // 揽收终端号  20171030 jie
		$operatorId    = $toMKIL['operatorId'];    // 操作人ID   20171030 jie
		$operatorName  = $toMKIL['operatorName'];    // 操作人真实姓名   20171030 jie

		$T_Model = M();   //实例化
		$T_Model->startTrans();//开启事务

		$ct_time = date('Y-m-d H:i:s',(time()-rand(20,300)));

		$center = array();
		/*将更改tran_list的重量，增加logs称重记录，增加il_logs中place的收货记录*/
		// 查询中转中心管理的对应信息
		if($TransitNo != 'MKIL'){
			$center = M('TransitCenter')->field('transit,toname')->where(array('id'=>$checkNo['tcid']))->find();
		}else{
			$center['transit'] = 'MKIL';// 2017-08-11 jie
		}

		$logs_data['CID']     = $js['CID'];//$checkNo['tcid'];
		$logs_data['tranid']  = 0; //称重的时候默认0
		$logs_data['transit'] = $center['transit'];
		$logs_data['tranNum'] = 'MKILWeigh';
		$logs_data['mStr1']   = $toMKIL['place'];
		$logs_data['MKNO']    = $MKNO;
		$logs_data['weight']  = $toMKIL['Weight'];
		$logs_data['state']   = 12;//中转

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
		$il_data['status']      = 12;//中转
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
			$t_data['weight']     = $toMKIL['Weight'];
			$t_data['IL_state']   = 12;//称重
			$t_data['ex_time']    = $ct_time;
			$t_data['ex_context'] = '美快国际物流'.$toMKIL['place'].' 已揽收';
			$tlist = M('TranList')->where(array('MKNO'=>$MKNO))->save($t_data);
		}

		if($tlist !== false && $logs !== false && $ilLogs !== false){

			// 不是erp打单那一类型的请求，而是揽收系统的请求，则进去以下操作  20171206 jie
			if($request_type == ''){

				/* 20171030 新增 */
				// 根据终端编号，查询此终端编号信息
				$check_terminal = $this->check_terminal($terminal_code);// 20171030
				
				if($check_terminal['state'] == 'no'){
					$T_Model->rollback();//事务有错回滚
					return array('TransitNo'=>$TransitNo,'MKNO'=>$MKNO,'Code'=>'0', "LOGSTR"=>$check_terminal['msg']);
				}else{
					$Terminal = $check_terminal['Terminal'];

					$t_map['MKNO'] = $MKNO;

					//会员订单
					$check_tlist = M('TranUlist')->where($t_map)->find();

					if(!$check_tlist){
						$T_Model->rollback();//事务有错回滚
						return array('TransitNo'=>$TransitNo,'MKNO'=>$MKNO,'Code'=>'0', "LOGSTR"=>"该会员的订单信息不存在");
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
						$T_Model->rollback();//事务有错回滚
						return array('TransitNo'=>$TransitNo,'MKNO'=>$MKNO,'Code'=>'0', "LOGSTR"=>"无法找到对应的数据进行更新");
					}
				}
				/* 20171030 新增 end */
			}

			if($ilLogs == 0){

				$T_Model->rollback();//事务回滚
				$backArr = array('TransitNo'=>$TransitNo,'MKNO'=>$MKNO,'Code'=>'2', "LOGSTR"=>"称重重复");
			}else{

				// 属于 中通 线路的订单  20171127 jie
				if($checkMKNO['TranKd'] == '17'){
					$this->toPush($checkMKNO['STNO'], 'Verified', '', $checkMKNO);// 推送“审单”节点 给中通（如果是中通的订单）
				}

				$T_Model->commit();//提交事务成功
				$backArr = array('TransitNo'=>$TransitNo,'MKNO'=>$MKNO,'Code'=>'1', "LOGSTR"=>"保存称重数据成功");
			}

		}else{
			$T_Model->rollback();//事务回滚
			$backArr = array('TransitNo'=>$TransitNo,'MKNO'=>$MKNO,'Code'=>'0', "LOGSTR"=>"未能保存称重数据，操作失败");
			
		}

		return $backArr;

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

}