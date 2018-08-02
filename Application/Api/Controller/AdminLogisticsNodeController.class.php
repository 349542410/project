<?php
/**
 * 美快优选3(中通)
 * 功能包括： 各个节点推送，补录菜鸟单号，补录航空号
 * 
 */
namespace Api\Controller;
use Think\Controller\HproseController;
use AUApi\Controller\KdnoConfig\Kdno17;
class AdminLogisticsNodeController extends HproseController{

	// 查询 中通单号 剩余数量
	public function NumberOfOrders(){
//		if(is_dir(C('Kdno_Path'))){
//
//			$EMS = new Kdno17();
//
//			return $EMS->RemainingNumberOfOrders();
//		}else{
//			return false;
//		}

        $EMS = new Kdno17();

        return $EMS->RemainingNumberOfOrders();
	}

	// 刷新 中通号剩余数量
	public function _reloadNums(){
		$nums = $this->NumberOfOrders(); // 查询 中通单号 剩余数量

		if($nums === false) $nums = '查询失败';

		return $nums;
	}
	
    /**
     * 节点推送  批次号列表 视图
     * @param  [type] $map [description]
     * @return [type]      [description]
     */
    public function _index($map, $ids){

		$list = M('TransitNo tn')->field('tn.id,tn.no,tn.date,tn.airdatetime,tn.airno,tn.status,tc.name,tn.tcid')
								 ->join('LEFT JOIN mk_transit_center tc ON tn.tcid = tc.id')
								 ->where($map)->order('tn.date asc')->select();

		foreach($list as $key=>$item){
			$res = $this->each_count($item['id']);
			$list[$key]['all']  = $res['all']; //总数
			$list[$key]['not']  = $res['not']; //未发送
			$list[$key]['done'] = $res['done']; //已发送
			$list[$key]['sort'] = $res['sort']; //推送阶段
			$list[$key]['lp']   = $res['lp']; //菜鸟单号总数
		}
		

		$center_list = $this->_center_list($ids);

		$nums = $this->NumberOfOrders(); // 查询 中通单号 剩余数量

		return array('list'=>$list,'center_list'=>$center_list, 'rest_num'=>$nums);
    }

    //根据ID集查询线路名
    public function _center_list($ids){
    	$where = array();
    	$where['id'] = array('in',$ids);

    	$center_list =  M('TransitCenter')->field('id,name')->where($where)->select();
    	return $center_list;
    }

	/**
	 * 当前节点推送状态 查询  
     * @param  [type] $id [tran_list.id]
	 * @return [type] [description]
	 */
    public function each_count($id){
    	$where = array();
		$where['t.noid'] = array('eq',$id);

		// 该批次号的菜鸟单号总数
		$lp = M('ZtTransferLp')->where(array('noid'=>$id))->count();

		$check = M('NodePushLogs')->field('sort, status')->where(array('noid'=>$id))->order('id desc')->find();

		//如果查无记录，则默认 第一阶段 未完成
		if(!$check){
			$check['sort'] = 0;
		}

		$all = M('TranList t')->join('LEFT JOIN mk_tran_list_state e ON e.lid = t.id')->where($where)->count();	//批次号对应的总数

		$where['e.node_push_state'] = array(array('lt', $check['sort']*100), array('exp','is NULL'), 'or');
		$not = M('TranList t')->join('LEFT JOIN mk_tran_list_state e ON e.lid = t.id')->where($where)->count();	//未推送

		$where['e.node_push_state'] = array('egt', $check['sort']*100);
		$done = M('TranList t')->join('LEFT JOIN mk_tran_list_state e ON e.lid = t.id')->where($where)->count();	//推送成功

		return array('all'=>$all, 'not'=>$not, 'done'=>$done, 'sort'=>$check['sort'], 'lp'=>$lp);

    }

    /**
     * 节点推送
     * @param  [type] $noid          [批次号ID]
     * @param  [type] $ZT_Node_Point [定义 中通 节点推送的阶段]
     * @param  [type] $timeout       [节点之间的推送时间间隔]
     * @param  [type] $operatorID    [操作人ID]
     * @return [type]                [description]
     */
    public function _node_push($noid, $ZT_Node_Point, $timeout, $operatorID){

        ini_set('memory_limit','500M');
        set_time_limit(0);

		$check_push = $this->toNodeLogs($noid, 'first', $ZT_Node_Point, $timeout, $operatorID);//立即生成推送节点的批次号记录

		// 非数组，则返回的是错误指令
		if(!is_array($check_push)){
			if($check_push === false){
				return array('status'=>'400','msg'=>"操作失败，请检查参数是否正确");
			}else if(is_numeric($check_push)){
				return array('status'=>'400','msg'=>"时间限制，大约在 ".date('Y-m-d H:i:s',$check_push)." 后才能推送");
			}
		}

		// 推送阶段已经全部完成
		if($check_push['push_step'] == 'end') return array('status'=>'400','msg'=>"已完成节点所有阶段的推送");

		// 如果airno为空，且是某个特定阶段，是不能进行推送的，需提示操作员
		if(in_array($check_push['push_state'], array('Arrival','Departure'))){

			$airno = M('TransitNo')->where(array('id'=>$noid))->getField('air_flight_no');//批次号 的 航空单号（中通用的字段）

			if(trim($airno) == ''){
				return array('status'=>'500','msg'=>"该批次号尚未有航空单号，请进行补填",'url'=>U('LogisticsNode/add',array('noid'=>$noid)));
			}
			
		}else{
			$airno = ''; //默认空
		}

/*		// 暂时不做任何用途
		// continue 表示该批次号当前节点阶段的推送尚未完成，因此继续推送 mk_tran_list_state.node_push_state=0 的数据
		if($check_push['push_step'] == 'continue'){
			$map['n.node_push_state'] = array(array('elt', $check_push['sort']*100),array('exp', 'is NULL'),'or');

		}else if($check_push['push_step'] == 'new'){//从第一阶段开始，即新的批次号，以前未推送过

			$map['n.node_push_state'] = array('exp', 'is NULL');
		}else{
			$map['n.node_push_state'] = array('elt', $check_push['sort']*100);
		}*/

		// continue 表示该批次号当前节点阶段的推送尚未完成，因此继续推送 mk_tran_list_state.node_push_state=0 的数据
		if($check_push['push_step'] == 'continue'){
			$map['n.node_push_state'] = array(array('neq', $check_push['sort']*100),array('exp', 'is NULL'),'or');
		}


		$map['t.noid'] = array('eq', $noid);//按批次号查询

/*		暂时取消推送到菜鸟系统  20171110 jie
		if($check_push['sort'] <= '1'){
			$list = M('TranList t')->field('t.id,t.MKNO,t.STNO,t.IL_state,n.node_push_state,z.LPNO')->join('left join mk_tran_list_state n on n.lid = t.id')->join('left join mk_zt_transfer_lp z on z.STNO = t.STNO')->where($map)->select();
		}else{

    		$list = M('TranList t')->field('t.id,t.MKNO,t.STNO,t.IL_state,n.node_push_state')->join('left join mk_tran_list_state n on n.lid = t.id')->where($map)->select();
		}*/
		
		$list = M('TranList t')->field('t.id,t.MKNO,t.STNO,t.IL_state,n.node_push_state')->join('left join mk_tran_list_state n on n.lid = t.id')->where($map)->select();
		// return $list;

		//检查批次号中是否有数据
		if(count($list) == 0){
			return array('status'=>'400','msg'=>"没有数据需要推送");
		}

		$count   = count($list);
		$suc_num = 0;//节点推送成功的总数
		$lp_num  = 0;//缺少 菜鸟单号的总数
		$lp_no   = '';//缺少 菜鸟单号的 STNO
		$il_state = 0; //IL_state < 1001  的订单总数，状态>=1001的订单，是不需要进行 节点推送
		$other_no = 0; //其它批次号转入的订单

    	$Kdno = new Kdno17();

		foreach($list as $item){

			if($item['node_push_state'] != '' && $item['node_push_state'] >= $check_push['sort']*100){
				$other_no++;
				$suc_num++;
				continue;
			}
			// 当快件的状态是 >= 1001 的时候，不需要推送节点信息
			// 暂时取消状态的检查限制
			// if($item['IL_state'] < 1001){

/*				暂时取消推送到菜鸟系统  20171110 jie
				// 第一阶段的推送 是改为推送到菜鸟物流
				if($check_push['sort'] <= '1'){

					// 如果菜鸟单号是空的，则记录总数+1
					if($item['LPNO'] == ''){
						$lp_num++;
						$lp_no .= $item['STNO'].',';
						$push_result = false;
					}else{
						//把中通和菜鸟单号进行推送
						$push_result = $this->updateCustomerOrderNumber($Kdno, $item['STNO'], $item['LPNO'], $item);
					}

				}else{//第二阶段开始，是推送给中通的

					//推送 节点信息 到 中通
					$push_result = $this->toPush($Kdno, $item['STNO'], $check_push['push_state'], $airno, $item);
				}*/
				
		    	$push_arr = array(
		    		'STNO'       => $item['STNO'],
		    		'push_state' => $check_push['push_state'],
		    		'airno'      => $airno,
		    		'data'       => $item,
		    	);
				//推送 节点信息 到 中通
				$push_result = $this->toPush($Kdno, $push_arr);

				if($push_result == true){
					$this->saveState($item['id'], $check_push['sort']);//推送成功的订单，node_push_state记录为200，表示节点成功推送
					$suc_num++;
				}
			// }else{
			// 	$il_state++;
			// }
			usleep(20000);
		}

		$this->toNodeLogs($check_push['id'], $check_push['push_step'], $ZT_Node_Point, $timeout, $operatorID, $suc_num, $count);//再次更新该批次号的节点推送记录信息

/*		暂时取消推送到菜鸟系统  20171110 jie
		// 总结语句
		if($check_push['sort'] <= '1'){
			$lp_no = trim($lp_no, ',');
			return $backArr = array('status'=>'1', 'msg'=>'操作完成，推送总数：'.$count.'。成功推送：'.$suc_num."(失败：".($count - $suc_num).")。缺少菜鸟单号的运单号有：".$lp_num."个（".$lp_no."）");
		}else{

			return $backArr = array('status'=>'1', 'msg'=>'操作完成，推送总数：'.$count.'。成功推送：'.$suc_num."(失败：".($count - $suc_num).")");
		}*/
		
		return $backArr = array('status'=>'200', 'msg'=>'操作完成，推送总数：'.$count.'。成功：'.$suc_num."(其它批次号并入且已推： ".$other_no.")。失败：".($count - $suc_num));
    }

    // 根据运单号 推送节点 给中通
    public function toPush($Kdno, $push_arr){
    	return $Kdno->SubmitTracking($push_arr);
    }

    // 把中通和菜鸟单号进行推送
    public function updateCustomerOrderNumber($Kdno, $STNO, $LPNO, $data){
    	return $Kdno->updateCustomerOrderNumber($STNO, $LPNO, $data);
    }

    //保存订单自身的节点推送状态信息
    public function saveState($lid, $node_push_state){
    	$node_push_state = intval($node_push_state) * 100;
    	$check = M('TranListState')->where(array('lid'=>$lid))->find();
    	if($check){
    		M('TranListState')->where(array('id'=>$check['id']))->setField('node_push_state',$node_push_state);
    	}else{
    		$data = array();
			$data['lid']             = $lid;
			$data['node_push_state'] = $node_push_state;
			M('TranListState')->add($data);
    	}
    }

	/**
	 * [toNodeLogs 节点推送记录保存 按批次号]
	 * @param  [type] $noid          [批次号ID或mk_node_push_logs.id]
	 * @param  [type] $step          [first, continue]
	 * @param  [type] $ZT_Node_Point [db.php定义的节点数组]
	 * @param  [type] $timeout       [db.php定义的节点推送时间间隔数组，first的时候才使用]
	 * @param  [type] $operatorID    [操作人ID]
	 * @param  string $suc_num       [推送成功的订单总数]
	 * @param  string $count         [批次号中的订单总数]
	 * @return [type]                [description]
	 */
	public function toNodeLogs($noid, $step, $ZT_Node_Point, $timeout, $operatorID, $suc_num='', $count=''){
		// 第一件事是先把数据保存到数据表
		if($step == 'first'){

			//检查是否已经有推送记录，查最新的一条
			$check = M('NodePushLogs')->where(array('noid'=>$noid))->order('id desc')->find();

			if($check && $check['status'] == '200'){

				$point = array_search($check['push_state'], $ZT_Node_Point);//获取最新推送记录中的节点编码的 键名

				if(array_key_exists($point, $timeout)){

					$time_limit = intval($timeout[$point]) * 60;//时间间隔精确到秒

					$time_differ = time()-strtotime($check['push_time']);//计算现在时间与上一次推送的时间之间的间隔

					//如果允许推送的 时间间隔$time_differ 未达到 指定值$timeout
					if($time_differ < $time_limit){

						$time_out = strtotime($check['push_time']) + $time_limit;
						return $time_out;
						// return $timeout[$point];//'time_limit_';
					}
				}

			}

			/* 这里只是判断推送节点正处于哪一阶段 */
			// 如果记录已经存在，且，推送结果仍未0，表示批次号中的订单该次节点推送尚未完全推送成功
			if($check && $check['status'] == '0'){
				$push_state = $check['push_state'];
				// $push_time  = $check['push_time'];
				$push_step  = 'continue';
			}else if($check && $check['status'] == '200'){
				// 如果记录已经存在，且，推送结果为200，表示批次号中的订单该次节点推送全部推送成功
				
				$last_point = array_search($check['push_state'],$ZT_Node_Point);//获取最新推送记录中的节点编码的 键名

				// 根据键名判断下一个节点阶段是否存在，即下一个推送状态
				if(array_key_exists($last_point+1,$ZT_Node_Point)){
					$push_state = $ZT_Node_Point[$last_point+1];//根据数组的键名获取数组中下一阶段节点的键值，即下一个推送状态
					$push_step  = 'next';
					$sort       = $check['sort']+1; //下一节点阶段
				}else{// 节点推送阶段已全部执行完毕
					$push_state = '';
					$push_step  = 'end';
					return array('push_state'=>$push_state, 'push_step'=>$push_step, 'id'=>'');
				}
				
			}else{
				$push_state = $ZT_Node_Point[0]; //从第一阶段开始推送节点
				$sort       = '1'; //从第一阶段开始推送节点
				$push_step  = 'new';
			}
			/* 这里只是判断推送节点正处于哪一阶段 */

			// 推送记录已存在的则不再保存
			if($push_step == 'continue'){
				return array('push_state'=>$push_state, 'push_step'=>$push_step, 'id'=>$check['id'], 'sort'=>$check['sort']);
			}else{
				$data['noid']       = $noid;
				$data['push_state'] = $push_state;
				$data['status']     = 0;
				$data['operatorID'] = $operatorID;
				$data['sort']       = $sort;
				$res = M('NodePushLogs')->add($data);		//保存记录

				if($res){
					return array('push_state'=>$push_state, 'push_step'=>$push_step, 'id'=>$res, 'sort'=>$sort);
				}else{
					return false;
				}
			}

		}else{

			$data_d = array();

			// if($step != 'continue') $data_d['push_total'] = $count;//非continue 新一节点阶段的推送的时候，需要保存批次号的订单总数，作记录参考
			$data_d['push_total'] = $count;//该批次号该阶段推送的订单总数，作记录参考

			//当查询的总数 == 成功推送的总数的时候，则可以认定为全部成功
			if($suc_num == $count){
				$data_d['status']     = '200';
				$data_d['operatorID'] = $operatorID;
				$res = M('NodePushLogs')->where(array('id'=>$noid))->save($data_d);		//保存记录
				if($res !== false){
					return true;
				}else{
					return false;
				}
			}else{
				return false;
			}
		}

	}

    /**
     * 补录航空号 方法
     */
	public function _add_method($noid, $airno){

		$check = M('TransitNo')->where(array('id'=>$noid))->find();

		if(!$check){
			return array('state'=>'no', 'msg'=>'该批次号不存在');
		}

		$res = M('TransitNo')->where(array('id'=>$noid))->setField('air_flight_no', $airno);

		if($res !== false){
			return array('state'=>'yes', 'msg'=>'航空号【'.$airno.'】录入成功');
		}else{
			return array('state'=>'no', 'msg'=>'操作失败');
		}
	}

}