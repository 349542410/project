<?php
namespace Api\Controller;
use Think\Controller\HproseController;
class AdminwithdrawController extends HproseController{
	/**
	 * 会员提现列表
	 * Enter description here ...
	 * @param unknown_type $data
	 */
	public function withdraw_list($data){
		if(isset($data['status'])){
			$count = M('withdraw_cash')->alias('wc')->join('left join mk_withdraw_cash_logs AS wcl ON wc.id = wcl.wc_id')->where('wcl.examine_status = '.$data['status'].' ')->count();
			$list = M('withdraw_cash')->alias('wc')->field('wc.*, ul.username, ul.amount AS useramount, wcl.examine_status')->join('mk_user_list AS ul ON wc.user_id = ul.id')->join('mk_withdraw_cash_logs AS wcl ON wc.id = wcl.wc_id')->where('wcl.examine_status = '.$data['status'].' ')->page($data['p'],$data['epage'])->order('create_time desc')->select();
		
		}else{
       		$count = M('withdraw_cash')->count();
       		$list = M('withdraw_cash')->alias('wc')->field('wc.*, ul.username, ul.amount AS useramount, wcl.examine_status')->join('mk_user_list AS ul ON wc.user_id = ul.id')->join('mk_withdraw_cash_logs AS wcl ON wc.id = wcl.wc_id')->page($data['p'],$data['epage'])->order('create_time desc')->select();
		}
		//$list = M('manager_list')->field('id, name, tname, email, phone')->where('status = 1')->limit($page->firstRow.','.$page->listRows)->select();
		
		
		
    	return array('count'=>$count, 'list'=>$list);	
	
	}

	/**
	 * 会员提现详细
	 * Enter description here ...
	 * @param $data
	 */
	public function withdraw_info($data){
		//return $data;
		//取得会员提现信息
		$row = M('withdraw_cash')->alias('wc')->field('wc.*, ul.username, ul.amount AS useramount, wcl.*')->join('mk_user_list AS ul ON wc.user_id = ul.id')->join('mk_withdraw_cash_logs AS wcl ON wc.id = wcl.wc_id')->where('wc.id = '.$data['id'].' ')->find();
		//取得审核人名字
		if($row['examine_user']){
			$w['id'] = $row['examine_user'];
			$admin_user = M('manager_list')->field('name')->where($w)->find();
			$row['admin_user'] = $admin_user['name'];
		}
		
		
		//充值记录信息
		$recharge_count = M('recharge_record')->where('UID = '.$row['user_id'].' ')->count();
//		if(!empty($data['page_type'])){
			//$rp = ($data['page_type'] == 'rech') ? $data['p'] : $data['page_two'];
//		}else{
//			$rp = empty($data['p']) ? 0 : $data['p'];
//		}	
//		
//		return $rp
		$recharge_list = M('recharge_record')->alias('rech')->field('rech.*, ul.username, ml.name AS adminname')->where('UID = '.$row['user_id'].' ')
						->join('mk_user_list AS ul ON rech.UID = ul.id')
						->join('left join mk_manager_recharge_list AS mrl ON rech.payno')
						->join('left join mk_manager_list AS ml ON mrl.user_id = ml.id')
						->page($data['p'],$data['epage'])->order('id desc')->select();
		//消费记录信息
		$consumption_count = M('wlorder_record')->where('UID = '.$row['user_id'].' ')->count();
		//$cp = ($data['page_type'] == 'cons') ? $data['p'] : $data['page_two'];
		
		$consumption_list = M('wlorder_record')->alias('cons')->field('cons.*, ul.username')->where('UID = '.$row['user_id'].' ')->join('mk_user_list AS ul ON cons.UID = ul.id')->page($data['p'],$data['epage'])->order('id desc')->select();
		
		$res['cash'] = $row;
		$res['rech']['count'] = $recharge_count;
		$res['rech']['list']  = $recharge_list;
		$res['cons']['count'] = $consumption_count;
		$res['cons']['list']  = $consumption_list;
		return $res;
	}
	
	
	public function withdraw_complete($data){
		$w['wc.user_id'] = $data['user_id'];
		$count = M('withdraw_cash')->alias('wc')->where($w)->count();
       	$list = M('withdraw_cash')->alias('wc')->field('wc.*, ul.username, ul.amount AS useramount, wcl.examine_status, wcl.*')->join('mk_user_list AS ul ON wc.user_id = ul.id')->join('mk_withdraw_cash_logs AS wcl ON wc.id = wcl.wc_id')->where($w)->page($data['p'],$data['epage'])->order('create_time desc')->select();
		return array('count'=>$count, 'list'=>$list);
	
	}
	
	
	
	
	
	/**
	 * 会员提现审核操作
	 * Enter description here ...
	 * @param unknown_type $data
	 */
	public function withdraw_hadd($data){
		//return $data;
		$row = M('withdraw_cash')->alias('wc')->field('wc.*, ul.username, ul.amount AS useramount, wcl.*')->join('mk_user_list AS ul ON wc.user_id = ul.id')->join('mk_withdraw_cash_logs AS wcl ON wc.id = wcl.wc_id')->where('wc.id = '.$data['id'].' ')->find();
		//return $row;
		//if($row['useramount'] <= 0){
		//	$error['status'] = false;
		//	$error['errorstr'] = '账号即时余额为0';
		//	return $error;
		//}
		//审核
		if('examine' == $data['status']){
			if($row['examine_status'] != 0){
				$error['status'] = false;
				$error['errorstr'] = '操作状态出错';
				return $error;
			}
			if($row['freeze_amount'] >= $row['request_amount']){
				//修改提款状态
				$data['examine_status'] = 1;
				$data['actual_amount'] = $row['request_amount'];
				$wc_id = $data['id']; 
				unset($data['status']);
				unset($data['id']);
				$res = M('withdraw_cash_logs')->data($data)->where('wc_id = '.$wc_id.' ')->save();
				if($res){
					$error['status'] = true;
					$error['strstr'] = '操作成功';
					return $error;
				}else{
					$error['status'] = false;
					$error['errorstr'] = '操作失败';
					return $error;
				}
				
			}
		//确认退款
		}else if('confirm' == $data['status']){
			if($row['examine_status'] != 1){
				$error['status'] = false;
				$error['errorstr'] = '操作状态出错';
				return $error;
			}
			$useramount = $row['useramount'] > 0 ? true : false;
			$online_amount = $data['online_amount'] > 0 ? true : false;
			$cash_service = $data['cash_service'] >= 0 ? true : false;
			$freeze_amount = $row['freeze_amount'] >= $row['request_amount'] ? true : false;
			$request_amount = $data['online_amount'] + $data['cash_service'];
			$request = ($row['request_amount'] == $request_amount) ? 	true : false;			
			if($useramount && $online_amount && $cash_service && $freeze_amount && $request){
				//开启事务
				$cash_logs = M('withdraw_cash_logs');
				$cash_logs->startTrans();
				$data['actial_amount'] = $row['request_amount'];
				$data['examine_status'] = 2;
				$wc_id = $data['id'];
				unset($data['id']);
				unset($data['status']);
				$res = $cash_logs->data($data)->where('wc_id = '.$wc_id.'')->save();
				if($res){
					$cash_logs->commit();//成功则提交
					$error['status'] = true;
					$error['strstr'] = '操作成功';
					return $error;
				}else{
					$cash_logs->rollback();//不成功，则回滚
					$error['status'] = false;
					$error['errorstr'] = '操作失败';
					return $error;
					
				}
			}else{
				$error['status'] = false;
				$error['errorstr'] = '请重新审核申请金额或在线支付金额';
				return $error;
			}
			
 		}elseif ('cancel' == $data['status']){
 			//return $data;
			 $arry = array(2, 3); //2为状态已确认	3为状态已取消
			 if(in_array($row['examine_status'], $arry)){
				$error['status'] = false;
				$error['errorstr'] = '操作状态出错';
				return $error;			 	
			 }
			 $user_list = M('user_list');
			 $user_list->startTrans();
			 $ruser = $user_list->where('id = '.$row['user_id'].' ')->setInc('amount',$row['freeze_amount']);
			 if($ruser){
			 	//修改操作记录
			 	$cash_logs = M('withdraw_cash_logs');
			 	$cash_logs->startTrans();
				$data['actial_amount'] = $row['request_amount'];
				$data['examine_status'] = 3;
				$wc_id = $data['id'];
				unset($data['id']);
				unset($data['status']);
				$res = $cash_logs->data($data)->where('wc_id = '.$wc_id.'')->save();			 	
			 	if($res){
					$cash_logs->commit();//成功则提交
					$user_list->commit();//成功则提交
					$error['status'] = true;
					$error['strstr'] = '操作成功';
					return $error;		 		
			 	}else{
					$cash_logs->rollback();//不成功，则回滚
					$user_list->rollback();//不成功，则回滚
					$error['status'] = false;
					$error['errorstr'] = '操作失败';
					return $error;			 	
			 	}
			 	
			 }else{
				$user_list->rollback();//不成功，则回滚
				$error['status'] = false;
				$error['errorstr'] = '修改会员金额失败';
				return $error;		 
			 }
 		}
		
	}
	
	/**
	 * 充值记录   无刷新分页
	 * Enter description here ...
	 * @param unknown_type $data
	 */
	public  function rech($data){
		//取得会员提现信息
		$row = M('withdraw_cash')->alias('wc')->field('wc.*, ul.username, ul.amount AS useramount, wcl.*')->join('mk_user_list AS ul ON wc.user_id = ul.id')->join('mk_withdraw_cash_logs AS wcl ON wc.id = wcl.wc_id')->where('wc.id = '.$data['id'].' ')->find();
				
		$recharge_count = M('recharge_record')->where('UID = '.$row['user_id'].' ')->count();
//		if(!empty($data['page_type'])){
			//$rp = ($data['page_type'] == 'rech') ? $data['p'] : $data['page_two'];
//		}else{
//			$rp = empty($data['p']) ? 0 : $data['p'];
//		}	
//		
//		return $rp
		$recharge_list = M('recharge_record')->alias('rech')->field('rech.*, ul.username, ml.name AS adminname')->where('UID = '.$row['user_id'].' ')
						->join('mk_user_list AS ul ON rech.UID = ul.id')
						->join('left join mk_manager_recharge_list AS mrl ON rech.payno')
						->join('left join mk_manager_list AS ml ON mrl.user_id = ml.id')
						->page($data['p'],$data['epage'])->order('id desc')->select();	
	
		//$res['cash'] = $row;
		$res['rech']['count'] = $recharge_count;
		$res['rech']['list']  = $recharge_list;
		return $res;
	
	}
	
	
	public function cons($data){
		//取得会员提现信息
		$row = M('withdraw_cash')->alias('wc')->field('wc.*, ul.username, ul.amount AS useramount, wcl.*')->join('mk_user_list AS ul ON wc.user_id = ul.id')->join('mk_withdraw_cash_logs AS wcl ON wc.id = wcl.wc_id')->where('wc.id = '.$data['id'].' ')->find();
		//消费记录信息
		$consumption_count = M('wlorder_record')->where('UID = '.$row['user_id'].' ')->count();
		//$cp = ($data['page_type'] == 'cons') ? $data['p'] : $data['page_two'];
		
		$consumption_list = M('wlorder_record')->alias('cons')->field('cons.*, ul.username')->where('UID = '.$row['user_id'].' ')->join('mk_user_list AS ul ON cons.UID = ul.id')->page($data['p'],$data['epage'])->order('id desc')->select();
		$res['cons']['count'] = $consumption_count;
		$res['cons']['list']  = $consumption_list;
		return $res;			
	
	}
	
	
}