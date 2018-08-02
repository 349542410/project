<?php
/**
 * 自助打印终端---提供会员在ERP软件端（无需会员登录）
 * 包含：操作订单打印流程、获取线路价格配置
 */
namespace AUApi\Controller;
use Think\Controller\HproseController;
class TestController {

	/**
	 * 获取打印资料  涉及到税金的计算
	 * @param  [type] $no                [Q单号]
	 * @param  [type] $RMB_Free_Duty     [人民币免税金额额度]
	 * @param  [type] $US_TO_RMB_RATE    [美元和人民币汇率]
	 * @return [type]         [description]
	 */
	public function _info(){
		try{

			$map['random_code'] = array('eq',$no); //凭证号
			$map['order_no']    = array('eq',$no);// Q开头的单号
			$map['_logic']      = 'or';
			$where['_complex']  = $map;

			//订单信息
			$info = M('TranUlist')->where($map)->find();

			//查无数据
			if(!$info) return array('state'=>'no', 'msg'=>'查无数据', 'lng'=>'no_data');

			//查询该线路信息
			$center = M('TransitCenter')->where(array('id'=>$info['TranKd']))->find();

	        /* 20180112  检查身份证号码和身份证照片  jie   暂定是打单后才验证，因此这里暂时关闭*/
	/*		$info['input_idno']         = $center['input_idno'];
			$info['member_sfpic_state'] = $center['member_sfpic_state'];

	        $CheckIdInfo = new \AUApi\Controller\CheckIdInfoController();

	        $check_res = $CheckIdInfo->check_id($info);

	        if ($check_res !== true) return $check_res;*/
	        /* end 20180112  检查身份证号码和身份证照片  jie */

	        /* 税金计算 */
	        $SysTaxCount = new \AUApi\Controller\SysTaxCountController();

	        $sys_tax_arr = array();
			$sys_tax_arr['center']         = $center;
			$sys_tax_arr['info']           = $info;
			$sys_tax_arr['RMB_Free_Duty']  = $RMB_Free_Duty;
			$sys_tax_arr['US_TO_RMB_RATE'] = $US_TO_RMB_RATE;
	        $SysTax = $SysTaxCount->index($sys_tax_arr);

			$tax       = $SysTax['tax'];//税金
			$goods     = $SysTax['goods'];//订单商品资料
			$extra_fee = $SysTax['extra_fee'];//附加费
	        /* 税金计算 */

			//订单相关的商品信息，并入到订单信息的goods里面
			$info['goods']         = $goods;//商品列表
			$info['tax']           = $tax;//总税金
			$info['lng_line_name'] = $center['lngname'];//线路名的多语言关键字  20180201 jie
			$info['extra_fee']     = $extra_fee;//附加费

	        $SystemCharging = new \AUApi\Controller\SysFreightCountController();

	        $Web_Config = $SystemCharging->_get_lines_configure($info);//线路价格配置与会员线路优惠

	        if(isset($Web_Config['state']) && $Web_Config['state'] == 'no'){
	            return $Web_Config;
	        }

	        $info['Web_Config'] = $Web_Config;
	        
			return $info;

        }catch (\Exception $e){
            
            $DataNote               = new \Libm\DataNotes\DataNote();
            $DataNote->RequestData  = $no;
            $DataNote->ResponseData = $e->getMessage();
            $DataNote->save_dir     = C('AutoSys_Set.Error_Notes');
            $DataNote->file_name    = C('AutoSys_Set.AutoPrintSysLogs');
            $DataNote->save();
        }
	}

	/**
	 * 页面返回相关资料（保存称重等资料） 涉及到税金的计算
	 * @param  [type] $id               [订单ID]
	 * @param  [type] $weight           [称重重量]
	 * @param  [type] $time             [称重时间]
	 * @param  [type] $operator_id      [操作人id]
	 * @param  [type] $RMB_Free_Duty    [人民币免税金额额度]
	 * @param  [type] $US_TO_RMB_RATE   [美元和人民币汇率]
	 * @return [type]                   [description]
	 */
	public function _step_one($id, $weight, $time, $operator_id, $RMB_Free_Duty, $US_TO_RMB_RATE){

		try{

			$info = M('TranUlist')->where(array('id'=>$id))->find();

			if(!$info){
				return array('state'=>'no', 'msg'=>'订单不存在', 'lng'=>'order_not_exist');
			}

			$list = M('TranList')->where(array('MKNO'=>$info['MKNO']))->find();
			// 已揽收的订单不可再打印
			if($list && $list['IL_state'] >= '12'){
				return array('state'=>'no', 'msg'=>'订单已揽收，不可操作', 'lng'=>'order_already_collected');
			}

			// 检查订单是否已经支付
			if($info['pay_state'] == '1'){
				$rdata = array('id'=>$info['id'], 'weight'=>$info['weight'], 'time'=>$info['weigh_time'], 'ctime'=>$info['ctime'], 'cost'=>$info['cost'], 'freight'=>$info['freight'], 'real_charge'=>$info['charge']);
				//保存称重资料且计费成功后，返回
				return array('state'=>'paid','msg'=>'订单已支付，可直接打印', 'lng'=>'order_already_paid', 'rdata'=>$rdata);
			}

			$Model = M();   //实例化
	        $Model->startTrans();//开启事务

			// 将状态为 未打印 的订单 锁定状态为打印中
			if($info['print_state'] == 0){
				// 立即锁定订单的打印状态为打印中
				M('TranUlist')->where(array('id'=>$id))->setField('print_state',10);
			}

	        /* 公共计费公式 */
	        $SystemCharging = new \AUApi\Controller\SysFreightCountController();
	        $sys_arr = array();
	        $sys_arr['weight']  = $weight;
	        $sys_arr['TranKd']  = $info['TranKd'];
	        $sys_arr['user_id'] = $info['user_id'];
	        $SystemCharge = $SystemCharging->index($sys_arr);

	        $freight        = $SystemCharge['freight'];//计重运费
	        $cost           = $SystemCharge['cost'];//实际总消费（不包含税金）
	        $original_price = $SystemCharge['original_price'];//原始消费金额
	        $discount       = $SystemCharge['discount'];//总优惠金额
	        $real_charge    = $SystemCharge['real_charge'];//实收服务费
	        /* 公共计费公式 */

			$center = M('TransitCenter')->where(array('id'=>$info['TranKd']))->find();
			
	        /* 税金计算 */
	        $SysTaxCount = new \AUApi\Controller\SysTaxCountController();

	        $sys_tax_arr = array();
			$sys_tax_arr['center']         = $center;
			$sys_tax_arr['info']           = $info;
			$sys_tax_arr['RMB_Free_Duty']  = $RMB_Free_Duty;
			$sys_tax_arr['US_TO_RMB_RATE'] = $US_TO_RMB_RATE;
	        $SysTax = $SysTaxCount->index($sys_tax_arr);

			$tax       = $SysTax['tax'];//税金
			$goods     = $SysTax['goods'];//订单商品资料
			$extra_fee = $SysTax['extra_fee'];//附加费
	        /* 税金计算 */

			$cost           = sprintf("%.2f", ($tax + $cost)); // 总消费金额（包含税金）
			$original_price = sprintf("%.2f", ($tax + $original_price)); // 未计算所有折扣优惠的 消费金额（包含税金）
	        
			$data = array();
			$data['weigh_time']      = $time;//称重时间
			$data['weight']          = $weight;//称重实际重量
			$data['freight']         = $freight;//总运费
			$data['discount_amount'] = $discount;//折扣优惠金额   原价 - 实际消费金额
			$data['charge']          = $real_charge;//实收服务费
			$data['tax']             = $tax;//统计税金  所有商品加载一起的税金，美元
			$data['original_price']  = $original_price;//消费金额 原价
			$data['cost']            = $cost;//实际消费金额

			$t_data = array();
			$t_data['freight']         = $freight;//总运费
			$t_data['discount_amount'] = $discount;//折扣优惠金额   原价 - 实际消费金额
			$t_data['fee']             = $real_charge;//实收服务费
			$t_data['tax']             = $tax;//统计税金  所有商品加载一起的税金，美元
			$t_data['original_price']  = $original_price;//消费金额 原价
			$t_data['cost']            = $cost;//实际消费金额

			//保存称重资料且计费成功后
			$save_order = M('TranUlist')->where(array('id'=>$id))->save($data);//更新部分字段
	//1
			// 检查该内部订单号的消费记录 是否已经存在
			$check_record = M('WlorderRecord')->where(array('order_no'=>$info['order_no']))->find();

			// 消费记录已经存在，则更新数据
			if($check_record){
				$save_record = M('WlorderRecord')->where(array('order_no'=>$info['order_no']))->save($t_data);
			}else{
				$t_data['UID']              = $info['user_id'];
				$t_data['paykind']          = $info['paykind'];
				$t_data['order_no']         = $info['order_no'];
				$t_data['ordertime']        = $info['ordertime'];
				$t_data['user_balance_usa'] = '0';
				$t_data['original_price']   = '0';
				
				$save_record = M('WlorderRecord')->add($t_data);
			}
	//2
			if($save_order !== false && $save_record !== false){

				// 检查记录
				$check_logs = M('ULogs')->where(array('order_no'=>$info['order_no'],'state'=>'3001'))->find();

				//记录不存在，则新增，这个记录只需要保存第一次成功称重的记录
				if(!$check_logs){
					$logs = array();
					$logs['order_no']    = $info['order_no']; //内部订单号
		        	$logs['content']     = '您的订单已经称重完毕，请等待系统确认';
		        	$logs['create_time'] = $time;  //称重时间
		        	$logs['state']       = '3001';
		        	$logs['operator_id'] = $operator_id;
		        	M('ULogs')->add($logs);//保存订单操作记录
				}

				$Model->commit();//提交事务成功

				$back_data = array('id'=>$id, 'weight' => $weight, 'time'=>$time, 'cost'=>$cost, 'tax'=>$tax, 'original_price'=>$original_price, 'RMB_Free_Duty'=>$RMB_Free_Duty, 'US_TO_RMB_RATE'=>$US_TO_RMB_RATE, 'extra_fee'=>$extra_fee, 'freight'=>$freight, 'real_charge'=>$real_charge, 'discount'=>$discount);

				return array('state'=>'yes', 'rdata'=>$back_data, 'msg'=>'计费成功', 'lng'=>'charge_success');

			}else{
				$Model->rollback();//事务有错回滚

				return array('state'=>'no', 'msg'=>'保存称重数据失败','lng'=>'failed_to_save_weight');
			}

        }catch (\Exception $e){
            
            $DataNote               = new \Libm\DataNotes\DataNote();
            $DataNote->RequestData  = $info['order_no'];
            $DataNote->ResponseData = $e->getMessage();
            $DataNote->save_dir     = C('AutoSys_Set.Error_Notes');
            $DataNote->file_name    = C('AutoSys_Set.AutoPrintSysLogs');
            $DataNote->save();
        }
	}

	/**
	 * [订单支付]
	 * @param  [type] $id            [订单ID]
	 * @param  [type] $operator_id   [操作人id]
	 * @param  [type] $terminal_code [终端编号 20171030]
	 * @return [type]                [description]
	 */
	public function _step_two($id, $operator_id, $terminal_code){

		try{

			$info = M('TranUlist')->where(array('id'=>$id))->find();  //根据此内部订单号和账户ID找出订单信息
			if(!$info){
				return array('state'=>'no','msg'=>'订单不存在', 'lng'=>'order_not_exist');
			}

			$user = M('UserList')->where(array('id'=>$info['user_id']))->find();
			if(!$user){
				return array('state'=>'no','msg'=>'账户不存在', 'lng'=>'user_not_exist');
			}

			/* 已经扣费成功的，则直接跳过 step_two ，进入step_three */
			//拦截位置  注意：这里，如果订单是已支付，则可以提供再次打印订单的，但不会重复扣费
			if($info['pay_state'] == '1'){
	        	//支付已经支付了，将支付单号等必要信息返回给打印系统
	        	$redata = array();
				$redata['id']          = $id;
				$redata['operator_id'] = $operator_id;
				$redata['paykind']     = $info['paykind'];
				$redata['payno']       = $info['payno'];
				$redata['paytime']     = $info['paytime'];
				$redata['balance']     = sprintf("%.2f", $user['amount']);
				return array('state'=>'paid','msg'=>'订单已支付，请直接打印', 'rdata'=>$redata, 'lng'=>'order_already_paid');
			}
			/* end 已经扣费成功的，则直接跳过 step_two ，进入step_three */

			$user_amount = $user['amount']; //账户余额

			if($user_amount == 0){
				return array('state'=>'no','msg'=>'账户余额为零，请先充值', 'lng'=>'balance_not_enough');
			}

			// 当 id_img_status = 200 的时候，需要支付一定的附件费  20180129 jie
			if($info['id_img_status'] == '200'){
				$extra_fee = M('tran_ulist_extra_fee')->where(array('lid'=>$id))->getField('extra_fee');
			}else{
				$extra_fee = '0';
			}

			$freight     = $info['freight'];//订单中的已经计算好的实收运费
			$real_charge = $info['charge'];//订单中的已经计算好的实收服务费
			$tax         = $info['tax'];//订单中的已经计算好的税金总额
			$cost    = sprintf("%.2f", ($freight + $tax + $extra_fee + $real_charge));

	        if($user_amount < $cost){
	        	return array('state'=>'no','msg'=>'账户余额不足以支付订单，请先充值', 'lng'=>'balance_not_enough_to_pay');
	        }

	        $user_amount = sprintf("%.2f", ($user_amount - $cost)); //新余额 = 原余额-消费金额

	/*        //查询订单相关的所有商品声明的价格和数量
	        $pro_list = M('TranUorder')->field('price,number')->where(array('lid'=>$info['id']))->select();*/

	        $Model = M();   //实例化
	        $Model->startTrans();//开启事务

	        //更新账户余额
	        $save_user = M('UserList')->where(array('id'=>$user['id']))->setField('amount',$user_amount);

			$payno   = build_sn();//创建支付单号
			$paytime = date('Y-m-d H:i:s');//支付时间

			$order_data['pay_state'] = 1;  //支付状态
	/*		$order_data['price']     = $goodsPrice;//所有商品声明的总价值
			$order_data['number']    = $goodsNum;//所有商品声明的总数量*/
			$order_data['payno']     = $payno;//支付单号
			$order_data['paytime']   = $paytime;//支付时间

			//更新物流订单
	        $save_order = M('TranUlist')->where(array('random_code'=>$info['random_code'],'user_id'=>$info['user_id']))->save($order_data);

			$t_data['pay_state']        = 1;  //支付状态
			$t_data['payno']            = $payno;//支付单号
			$t_data['paytime']          = $paytime;//支付时间
			$t_data['user_balance_usa'] = $user_amount;//成功消费后的余额  20171017

			//更新消费记录的信息
			$save_record = M('WlorderRecord')->where(array('order_no'=>$info['order_no']))->save($t_data);

			/* 20171030 新增 */
			// 根据终端编号，查询此终端编号信息
			$check_terminal = $this->check_terminal($terminal_code);

			if($check_terminal['state'] == 'no'){
				$Model->rollback();//事务有错回滚
				return $check_terminal;
			}else{
				$Terminal = $check_terminal['Terminal'];

				// 查询条件
				$many_map['member_id'] = array('eq', $info['user_id']);
				$many_map['order_id']  = array('eq', $info['id']);

				// 保存数据
				$many_data['terminal_id'] = $Terminal['id']; // 终端号ID  mk_self_terminal_list.id
				$many_data['member_id']   = $info['user_id']; //会员ID   mk_user_list.id
				$many_data['order_id']    = $info['id']; //订单ID  mk_tran_ulist.id
				$many_data['ctime']       = date('Y-m-d H:i:s');

				// 检查该会员是否在终端操作（打印）过此订单
				$check_relation = M('PrintRelationOrder')->where($many_map)->find();

				// 已存在，则更新
				if($check_relation){
					$save_many = M('PrintRelationOrder')->where(array('id'=>$check_relation['id']))->save($many_data);
				}else{
					// 新增
					$save_many = M('PrintRelationOrder')->add($many_data);
				}
			}
			/* 20171030 新增 end */

	        if($save_user == true && $save_order == true && $save_record == true && $save_many !== false){

				$logs = array();
				$logs['order_no']    = $info['order_no']; //内部订单号
	        	$logs['content']     = '您的订单已经支付成功，等待打印确认';
	        	$logs['create_time'] = $paytime;  //支付时间
	        	$logs['state']       = '3002';
	        	$logs['operator_id'] = $operator_id;//操作人id
	        	M('ULogs')->add($logs);//保存订单操作记录

	        	//支付成功后，将支付单号等必要信息返回给打印系统
	        	$redata = array();
				$redata['paykind'] = $info['paykind'];
				$redata['payno']   = $payno;
				$redata['paytime'] = $paytime;
				$redata['balance'] = sprintf("%.2f", $user_amount);

	        	//支付订单后，扣款次数+1
	        	M('WlorderRecord')->where(array('order_no'=>$info['order_no']))->setInc('deduct_num',1);
	        	$Model->commit();//提交事务成功
	        	return array('state'=>'yes','msg'=>'支付成功','t_data'=>$info, 'redata'=>$redata, 'lng'=>'pay_success');
	        }else{
	        	$Model->rollback();//事务有错回滚
	        	return array('state'=>'no','msg'=>'支付失败，如需帮助请咨询客服', 'lng'=>'pay_failed');
	        }

        }catch (\Exception $e){
            
            $DataNote               = new \Libm\DataNotes\DataNote();
            $DataNote->RequestData  = $info['order_no'];
            $DataNote->ResponseData = $e->getMessage();
            $DataNote->save_dir     = C('AutoSys_Set.Error_Notes');
            $DataNote->file_name    = C('AutoSys_Set.AutoPrintSysLogs');
            $DataNote->save();
        }
	}

	// 检查 终端号 是否存在、可用
	public function check_terminal($terminal_code){
		// 根据终端编号，查询此终端编号信息
		$Terminal = M('SelfTerminalList')->where(array('terminal_name'=>$terminal_code))->find();
		if(!$Terminal){
			return array('state'=>'no', 'msg'=>'该终端编号不存在', 'lng'=>'terminal_code_not_exist', 'terminal_code'=>$terminal_code);
		}else{

			//终端机尚未激活
			if($Terminal['status'] == '0'){
				return array('state'=>'no', 'msg'=>'该终端编号尚未激活', 'lng'=>'terminal_not_activate');
			// }else if($Terminal['type'] != 'print'){//终端机类型不符合规则
			// 	return array('state'=>'no', 'msg'=>'该终端编号类型不符合规则', 'lng'=>'terminal_not_right');
			}else{
				return array('state'=>'yes', 'Terminal'=>$Terminal);
			}
		}
	}

	/**
	 * [保存打印状态]
	 * @param  [type] $id      [订单ID]
	 * @param  [type] $status  [打印状态]
	 * @param  [type] $time    [打印时间]
	 * @param  [type] $MKNO    [美快单号]
	 * @param  [type] $STNO    [快递运单号]
	 * @param  [type] $terminal_code    [终端号]
	 * @param  [type] $operator_id    [操作人id]
	 * @return [type]          [description]
	 */
	public function _step_three($id, $status, $time, $MKNO, $STNO, $terminal_code, $operator_id){
		try{

			$time = date('Y-m-d H:i:s');//打印时间 用服务器的时间

			$info  = M('TranUlist')->where(array('id'=>$id))->find();

			if(!$info){
				return array('state'=>'no', 'msg'=>'订单不存在', 'lng'=>'order_not_exist');
			}

			$Model = M();   //实例化
	        $Model->startTrans();//开启事务

	        /* 如果是已经打印成功的，则无需更新其他相关信息，只需记录打印次数即可 */
			if($info['print_state'] == '200'){
				M('TranUlist')->where(array('id'=>$id))->setInc('print_num',1);
				$Model->commit();//提交事务成功
				return array('state'=>'yes', 'msg'=>'订单打印记录保存成功', 'lng'=>'print_success');
			}
			/* 如果是已经打印成功的，则无需更新其他相关信息，只需记录打印次数即可 */

			$data = array();
			$data['print_state'] = $status;
			$data['print_time']  = $time;
			$data['MKNO']        = $MKNO;
			$data['STNO']        = $STNO;
			
			$save = M('TranUlist')->where(array('id'=>$id))->save($data);

			/* 20171030 新增 */
			// 根据终端编号，查询此终端编号信息
			$check_terminal = $this->check_terminal($terminal_code);
			
			if($check_terminal['state'] == 'no'){
				$Model->rollback();//事务有错回滚
				return $check_terminal;
			}else{
				$Terminal = $check_terminal['Terminal'];

				$t_map['MKNO'] = $MKNO;
				$t_map['STNO'] = $STNO;

				$check_tlist = M('TranList')->where($t_map)->find();

				if(!$check_tlist){
					$Model->rollback();//事务有错回滚
					return array('state'=>'no','msg'=>'该会员订单信息尚未生成对应TranList数据', 'lng'=>'tran_list_not_exist');
				}

				// 查询条件
				$many_map['member_id']   = array('eq', $info['user_id']);
				$many_map['order_id']    = array('eq', $info['id']);
				// $many_map['terminal_id'] = array('eq', $Terminal['id']);

				// 保存数据
				$many_data['tran_id'] = $check_tlist['id']; // 保存 mk_tran_list.id

				// 检查该会员是否在终端操作（打印）过此订单
				$check_relation = M('PrintRelationOrder')->where($many_map)->find();
				
				$Model->rollback();//事务有错回滚
				return $check_relation;
				// 已存在，则更新
				if($check_relation){
					$save_many = M('PrintRelationOrder')->where(array('id'=>$check_relation['id']))->save($many_data);
				}else{
					$Model->rollback();//事务有错回滚
					return M()->getLastSql();
					return array('state'=>'no','msg'=>'无法找到对应的数据进行更新', 'lng'=>'print_terminal_not_same');
				}
			}
			/* 20171030 新增 end */

			if($save !== false){

				$content = '您的订单已经打印成功';

				// 检查记录
				$check_logs = M('ULogs')->where(array('order_no'=>$info['order_no'],'state'=>'3003'))->find();

				//记录不存在，则新增，这个记录只需要保存第一次成功称重的记录
				if(!$check_logs){
					$logs = array();
					$logs['order_no']    = $info['order_no']; //内部订单号
		        	$logs['content']     = $content;	//文字说明
		        	$logs['create_time'] = $time;  //打印时间
		        	$logs['state']       = '3003';
		        	$logs['operator_id'] = $operator_id;// 操作人id
		        	M('ULogs')->add($logs);//保存订单操作记录
				}

				unset($data['MKNO']);
				unset($data['STNO']);
				
				$data['order_no'] = $info['order_no']; //内部订单号
				$data['content'] = $content; //文字说明

				// 打印历史记录全部保存起来
				M('PrintRecord')->add($data);

				//支付成功打印后，打印次数记录+1
				M('TranUlist')->where(array('id'=>$id))->setInc('print_num',1);

				// // 属于 中通 线路的订单  20171127 jie
				// if($info['TranKd'] == '17'){
			 //    	$zt_arr = array(
			 //    		'STNO'       => $STNO,
			 //    		'push_state' => 'Verified',
			 //    		'airno'      => '',
			 //    		'data'       => array(
			 //    			'MKNO' => $MKNO,
			 //    			'STNO' => $STNO,
			 //    		),
			 //    	);
			 //    	include_once(C('Kdno_Path').'\Kdno17.class.php');
	   //  			$Kdno = new \Kdno();
			 //    	$submit_res = $Kdno->SubmitTracking($zt_arr);// 推送“审单”节点 给中通（如果是中通的订单）
				// }
				return array('state'=>'no','msg'=>'我在这', 'lng'=>'print_terminal_not_same');
				$Model->commit();//提交事务成功
				return array('state'=>'yes', 'msg'=>'订单打印记录保存成功', 'lng'=>'print_success');

			}else{
				
				$Model->rollback();//事务有错回滚
				return array('state'=>'no', 'msg'=>'订单打印记录保存失败', 'lng'=>'print_failed');
			}

        }catch (\Exception $e){
            
            $DataNote               = new \Libm\DataNotes\DataNote();
            $DataNote->RequestData  = $info['order_no'];
            $DataNote->ResponseData = $e->getMessage();
            $DataNote->save_dir     = C('AutoSys_Set.Error_Notes');
            $DataNote->file_name    = C('AutoSys_Set.AutoPrintSysLogs');
            $DataNote->save();
        }
	}

	// 获取某条（或全部）线路的价格配置信息
	public function _get_lines_configure($line_id){

		$map = array();

		if($line_id != '') $map['line_id'] = array('eq', $line_id);

        // 查询各线路的价格优惠配置信息
        $Web_Config = M('LinePrice')->field('line_id,fee_service as Charge,weight_first as Weight,fee_first as Price,weight_next as Unit,fee_next as UnitPrice,unit_currency,unit_weight,0 as Discount')->where($map)->select();

        if(is_array($Web_Config) && count(($Web_Config)) > 0){

        	return array_column($Web_Config, NULL, 'line_id');//二维数组以id字段做一维数组的键名
        }else{
        	return false;
        }

	}

	public function terminal_find($no){
		return M('self_terminal_list')->where(array('terminal_name'=>$no))->find();
	}

	public function terminal_list(){
		return M('self_terminal_list')->field('id,computer_name,terminal_name,type')->select();
	}
	public function test(){
	    echo 1;
    }
}