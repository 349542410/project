<?php
/**
 * 打印系统  服务器端
 */
namespace AUApi\Controller;
use Think\Controller\HproseController;
class OrderPrintController extends HproseController{

    //判断小数点第三位是否大于0，若是，则进一，否则不变
    private function num_to_change($n){
        $num = floatval($n) * 1000;
        $str = substr($num,(strlen($num)-1),1);

        if($str > 0){
            $num = floatval($num) - floatval($str);
            $num = floatval($num) + 10;
            $num = sprintf("%.2f", floatval($num)/1000);
            return $num;
        }else{
            return sprintf("%.2f", floatval($n));
        }
    }

	//查询未打印的订单总数
	public function _count($where){

		$count = M('TranUlist')->where($where)->count();
		return $count;
	}

	//查询未打印的订单信息和商品信息
	public function _list($where, $p, $ePage){

		$list = M('TranUlist')->field('id,receiver,province,city,town,TranKd')->where($where)->order('id asc')->page($p.','.$ePage)->select();

		if(count($list) > 0){
			$ids = array();
			foreach($list as $key=>$item){
				$ids[] = $item['id'];
			}

			//订单ID集
			$ids = implode(',',$ids);
			$map['lid'] = array('in',$ids);
			$map['delete_time'] = array('exp', 'is null');
			$info = M('TranUorder')->field('lid,detail,number')->where($map)->select();//根据ID集找出所有对应的商品

			//把对应的商品整合到订单中
			foreach($info as $k1=>$v1){

				foreach($list as $k2=>$v2){

					if($v1['lid'] == $v2['id']){
						
						$list[$k2]['goods'][$k1]['detail'] = $v1['detail'];
						$list[$k2]['goods'][$k1]['number'] = $v1['number'];

						sort($list[$k2]['goods']);//数组键值重新以升序方式对数组排序
					}
				}
			}
		}
		
    	return $list;
	}

	/**
	 * 获取打印资料  涉及到税金的计算
	 * @param  [type] $id                [订单ID]
	 * @param  [type] $RMB_Free_Duty     [人民币免税金额额度]
	 * @param  [type] $US_TO_RMB_RATE    [美元和人民币汇率]
	 * @return [type]         [description]
	 */
	public function _info($id, $RMB_Free_Duty, $US_TO_RMB_RATE){

		//订单信息
		$info = M('TranUlist')->where(array('id'=>$id))->find();

		//查无数据
		if(!$info) return array('state'=>'no', 'msg'=>'查无数据', 'lng'=>'no_data');

        //判断线路税金起征额
        $centerTaxThreshold = M('transit_center')->field('taxthreshold')->where(['id' => $info['TranKd']])->find();
        if($centerTaxThreshold['taxthreshold'] > 0){
            $RMB_Free_Duty = $centerTaxThreshold['taxthreshold'];
        }

		/* 检查会员订单的商品总数和总金额是否有计算错误 */
        //查询订单相关的所有商品声明的价格和数量
		/*$uOrderWhere['lid'] = $info['id'];
		$uOrderWhere['delete_time'] = array('exp', 'is null');*/
		$uOrderWhere = 'lid = ' . $info['id'] . ' AND delete_time is null';
        $pro_list = M('TranUorder')->field('price,number')->where($uOrderWhere)->select();
		\Think\Log::write('修复包裹类别删除显示问题'. M('TranUorder')->getLastSql());
        $total_nums = 0;//订单商品总数
        $total_price = 0;//订单商品总金额
        foreach($pro_list as $k=>$v){
        	$total_nums += $v['number'];
        	$total_price += ($v['number'] * $v['price']);
        }

        $total_nums = sprintf("%.2f", $total_nums);//订单商品总数
        $total_price = sprintf("%.2f", $total_price);//订单商品总金额

        // 如果数据库中的总金额和总数  与 计算出来的总金额和总数不一致的时候，进行数据更新
        if($info['price'] != $total_price || $info['number'] != $total_nums){
        	
        	$ulist_data = array();
        	if($info['price'] != $total_price) $ulist_data['price']  = $total_price;
			if($info['number'] != $total_nums) $ulist_data['number'] = $total_nums;

        	M('TranUlist')->where(array('id'=>$info['id']))->save($ulist_data);
        	$info = M('TranUlist')->where(array('id'=>$id))->find();
        }
        /* 检查会员订单的商品总数和总金额是否有计算错误 */

		//查询该线路信息
		$center = M('TransitCenter')->where(array('id'=>$info['TranKd']))->find();

        /* 20180112  检查身份证号码和身份证照片  jie   暂定是打单后才验证，因此这里暂时关闭*/
/*		$info['input_idno']         = $center['input_idno'];
		$info['member_sfpic_state'] = $center['member_sfpic_state'];

        $CheckIdInfo = new \AUApi\Controller\CheckIdInfoController();

        $check_res = $CheckIdInfo->check_id($info);

        if ($check_res !== true) return $check_res;*/
        /* end 20180112  检查身份证号码和身份证照片  jie */

		$tax = 0;//税金总金额
		//检查该线路的 bc_state 是否为1
		if($center['bc_state'] == '1'){
			//订单相关商品信息
			$uOrderWhere = 't.lid = ' . $info['id'] . ' AND t.delete_time is null';
			$goods = M('TranUorder t')->field('t.oid,t.lid,t.number,t.weight,t.remark,t.auto_Indent1,t.auto_Indent2,p.show_name,p.name as detail,p.brand,p.hs_code,p.hgid,p.price,p.coin,p.unit,p.source_area,p.specifications,p.barcode,p.unit,p.source_area,p.tariff_no,c.cat_name as catname,c.price as tax_price')->
			join('left join mk_product_list p on p.id = t.product_id')->join('left join mk_category_list c on c.id = p.cat_id')->where($uOrderWhere)->select();

			$tol_price = 0;//商品总金额，bc_state 为1 的订单，商品的金额是用美快后台货品管理里面定义的申报金额

			foreach($goods as $item){
				$tax += $item['number'] * $item['tax_price'];//统计税金 以便保存
				$tol_price += $item['number'] * $item['price'];//统计商品总价值 以便保存
			}

			$info['price'] = sprintf("%.2f", $tol_price);//商品总价值
			
		}else if($center['cc_state'] == '1'){
			//订单相关商品信息.
			$uOrderWhere = 't.lid = ' . $info['id'] . ' AND t.delete_time is null';
			$goods = M('TranUorder t')->field('t.*,t.num_unit as unit,t.catname as specifications,c.cat_name as catname, c.price as tax_rate,c.hs_code,c.hgid,c.hs_code as tariff_no')->
			join('left join mk_category_list c on c.id = t.category_two')->where($uOrderWhere)->select();

            //tax_rate 为百分比
            if($center['tax_kind'] == '1'){

                //需要根据二级类别的税率计算税金
				foreach($goods as $k=>$item){
					$tax += $item['number'] * $item['tax_rate'] * $item['price'] / 100;//统计税金 以便保存
					$goods[$k]['show_name'] = $item['detail'];
				}

				//根据汇率计算出美元免税的额度
	            $free_duty = $RMB_Free_Duty / $US_TO_RMB_RATE;

				// 2017-09-19  整单税金<=7美元的时候，直接免税；>7的直接显示所计算得到的税金（不用减7）
				if(sprintf("%.2f", $tax) <= sprintf("%.2f", $free_duty)){
					$tax = 0;
				}
				
            }else{//tax_rate 为固定值

                //需要根据二级类别的税值计算税金
				foreach($goods as $k=>$item){
					$tax += $item['number'] * $item['tax_rate'];//统计税金 以便保存
					$goods[$k]['show_name'] = $item['detail'];
				}
            }

		}else{
			//订单相关商品信息
			$uOrderWhere = 't.lid = ' . $info['id'] . ' AND t.delete_time is null';
			$goods = M('TranUorder t')->field('t.*,t.catname as specifications')->where($uOrderWhere)->select();
			foreach($goods as $k=>$item){
				$goods[$k]['show_name'] = $item['detail'];
			}
		}

		// 当 id_img_status = 200 的时候，需要支付一定的附件费  20180129 jie
		if($info['id_img_status'] == '200'){
			$info['extra_fee'] = M('tran_ulist_extra_fee')->where(array('lid'=>$id))->getField('extra_fee');
		}else{
			$info['extra_fee'] = '0';
		}

		//订单相关的商品信息，并入到订单信息的goods里面
		$info['goods'] = $goods;//商品列表
		$info['tax']   = sprintf("%.2f", $tax);//总税金
		$info['lng_line_name']   = $center['lngname'];//线路名的多语言关键字  20180201 jie
		
		return $info;
	}

	/**
	 * 页面返回相关资料（保存称重等资料） 涉及到税金的计算
	 * @param  [type] $id               [订单ID]
	 * @param  [type] $weight           [称重重量]
	 * @param  [type] $time             [称重时间]
	 * @param  [type] $RMB_Free_Duty    [人民币免税金额额度]
	 * @param  [type] $US_TO_RMB_RATE   [美元和人民币汇率]
	 * @return [type]                   [description]
	 */
	public function _step_one($id, $weight, $time, $RMB_Free_Duty, $US_TO_RMB_RATE, $terminalCode){

		$info = M('TranUlist')->where(array('id'=>$id))->find();

		if(!$info){
			return array('state'=>'no', 'msg'=>'订单不存在', 'lng'=>'order_not_exist');
		}
		//验证终端所属点
		$terminalState = M('SelfTerminalList')->where(array('terminal_name'=>$terminalCode))->find();

		if(!$terminalState){
			return array('state'=>'no', 'msg'=>'终端设备号不存在', 'lng'=>'terminal_not');
		}
        $center = M('transit_center')->field('taxthreshold,input_idno,member_sfpic_state')->where(['id' => $info['TranKd']])->find();

		if($terminalState['point_id'] != 6) {
			//验证身份证号，及身份证照片
			if (($center['input_idno'] == 1 || $center['member_sfpic_state'] == 1) &&
				((int)$info['id_img_status'] < 100 || (int)$info['id_no_status'] < 100)
			) {
				return array('state' => 'no', 'msg' => '请上传身份证或者填写身份证号码信息', 'lng' => 'identity_id');
			}
		}

        //判断线路税金起征额
        if($center['taxthreshold'] > 0){
            $RMB_Free_Duty = $center['taxthreshold'];
        }

		//根据会员ID，线路ID 查询 线路配置信息
		$Web_Config = M('LinePrice')->field('fee_service,weight_first,fee_first,weight_next,fee_next,unit_currency,unit_weight')->where(array('line_id'=>$info['TranKd']))->find();

		//如果查询出错，find方法返回false，如果查询结果为空返回NULL，查询成功则返回一个关联数组（键值是字段名或者别名）
		$Web_Config = (is_array($Web_Config)) ? $Web_Config : '';

        // 判断传来的中转线路ID是否有对应的配置信息
        if($Web_Config == ''){
            return array('state'=>'no', 'msg'=>'该中转线路尚未配置', 'lng'=>'tranline_not_exist');
        }

        // 查询该会员是否有设置线路折扣
        $member_discount = M('LineDiscount')->field('discount_service,discount_first,discount_next')->where(array('user_id'=>$info['user_id'],'line_id'=>$info['TranKd']))->find();

        /* 检查会员线路优惠的配置 */
        // 如果该会员的线路优惠折扣尚未配置
		if(!$member_discount){
			$member_discount = array();
			$member_discount['discount_service'] = '100';
			$member_discount['discount_first']   = '100';
			$member_discount['discount_next']    = '100';
        }else{
        	// 如果该会员的线路优惠折扣已配置，但是里面的值是空的或者非数字  20180125 jie
        	if(empty(trim($member_discount['discount_service'])) || !is_numeric(trim($member_discount['discount_service']))){
        		$member_discount['discount_service'] = '100';
        	}
        	if(empty(trim($member_discount['discount_first'])) || !is_numeric(trim($member_discount['discount_first']))){
        		$member_discount['discount_first'] = '100';
        	}
        	if(empty(trim($member_discount['discount_next'])) || !is_numeric(trim($member_discount['discount_next']))){
        		$member_discount['discount_next'] = '100';
        	}
        }

        // 会员线路优惠折扣  最后保险设置  20180126 jie
        if(floatval(trim($member_discount['discount_service'])) < 60) $member_discount['discount_service'] = '100';
        if(floatval(trim($member_discount['discount_first'])) < 60) $member_discount['discount_first'] = '100';
        if(floatval(trim($member_discount['discount_next'])) < 60) $member_discount['discount_next'] = '100';
        /* 检查会员线路优惠的配置 end */

        $Web_Config = $Web_Config + $member_discount;

        // 日志
        if(!is_dir(UPFILEBASE.'/Upfile/freight_logs/')) mkdir(UPFILEBASE.'/Upfile/freight_logs/', 0777, true);//第三个参数设置true 设置递归模式自动创建必需的目录文件夹
        $file_name = 'freight_'.date('Ymd').'.txt';   //文件名     
        $content = "===================== ".date('Y-m-d H:i:s')." =====================\r\n\r\n-------- soapError --------\r\n\r\n".json_encode($Web_Config)."\r\n\r\n";
        file_put_contents(UPFILEBASE.'/Upfile/freight_logs/'.$file_name, $content, FILE_APPEND);

		// 检查订单是否已经支付
		if($info['pay_state'] == '1'){
			$rdata = array('id'=>$info['id'], 'weight' => $info['weight'], 'time'=>$info['weigh_time'], 'ctime'=>$info['ctime'], 'freight'=>$info['freight'], 'weigh_config'=>$Web_Config);
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

		$Charge           = $Web_Config['fee_service'];		//服务费，手续费
		$Price            = $Web_Config['fee_first'];		//首重价格
		$Heavy            = $Web_Config['weight_first'];	//首重重量
		$Unit             = $Web_Config['weight_next'];		//续重重量(原意：续重计费单位)
		$UnitPrice        = $Web_Config['fee_next'];		//续重单价(原意：续重每单位金额)
		$Discount_Service = $Web_Config['discount_service'];//服务费折扣 百分比
		$Discount_First   = $Web_Config['discount_first'];	//首重折扣  百分比
		$Discount_Next    = $Web_Config['discount_next'];	//续重折扣  百分比

		// 实际称重>首重
		if($weight - $Heavy > 0){

			//超出的重量 = 实际重量 - 首重
			$surp = sprintf("%.2f", ($weight - $Heavy));

			//续重计费数量 = 超出的重量 / 续重计费单位 -> 如果存在小数则+1
			$surp_num = sprintf("%.2f", ceil($surp / $Unit));

			//续重价格 = 续重计费数量*续重每单位金额
			$amount = sprintf("%.2f", ($UnitPrice * $surp_num));
			
			//总金额 = 首重价格*首重折扣 + 续重价格*续重折扣
			$freight = sprintf("%.2f", (($Price * $Discount_First/100) + ($amount * $Discount_Next/100)));

			//实收金额 = 总金额 + 服务费*服务费折扣
			$cost = sprintf("%.2f", ($freight + ($Charge * $Discount_Service/100)));
			
			//未计算所有折扣优惠的 消费金额 原价
			$original_price = sprintf("%.2f", ($Price + $amount + $Charge));
		}else{

			// 由于实际称重重量没有超过首重，所有运费只收首重运费的金额
			// 实收金额 = 首重价格*首重折扣 + 服务费*服务费折扣
			$cost = sprintf("%.2f", (($Discount_First * $Price/100) + ($Charge * $Discount_Service/100)));

			//未计算所有折扣优惠的 消费金额 原价
			$original_price = sprintf("%.2f", ($Price + $Charge));

		}

		// 优惠金额 = 原价 - 优惠后的金额
		$discount = sprintf("%.2f", ($original_price - $cost));

		$center = M('TransitCenter')->where(array('id'=>$info['TranKd']))->find();
		
		$tax = 0;//税金总金额
		//是否为 顺丰BC  id=5 ，需要计算商品的税金总额(商品数量*税金单价，美元)
		if($center['bc_state'] == '1'){

			//订单所含的商品数量以及对应的税金单价
			$uOrderWhere = 't.lid = ' . $info['id'] . ' AND t.delete_time is null';
			$goods = M('TranUorder t')->field('t.oid,t.number,c.price as tax_price')->join('left join mk_category_list c on c.id = t.category_two')->where($uOrderWhere)->select();

			//统计税金 以便保存
			foreach($goods as $item){
				$tax += $item['number'] * $item['tax_price'];
			}

		}else if($center['cc_state'] == '1'){
			//订单相关商品信息
			$uOrderWhere = 't.lid = ' . $info['id'] . ' AND t.delete_time is null';
			$goods = M('TranUorder t')->field('t.oid,t.price,t.number,c.price as tax_rate')->join('left join mk_category_list c on c.id = t.category_two')->where($uOrderWhere)->select();

            //tax_rate 为百分比
            if($center['tax_kind'] == '1'){

                //需要根据二级类别的税率计算税金
				foreach($goods as $item){
					$tax += $item['number'] * $item['tax_rate'] * $item['price'] / 100;//统计税金 以便保存
				}

				//根据汇率计算出美元免税的额度
	            $free_duty = $RMB_Free_Duty / $US_TO_RMB_RATE;

				// 2017-09-19  整单税金<=7美元的时候，直接免税；>7的直接显示所计算得到的税金（不用减7）
				if(sprintf("%.2f",$tax) <= sprintf("%.2f",$free_duty)){
					$tax = 0;
				}
            }else{//tax_rate 为固定值

                //需要根据二级类别的税值计算税金
				foreach($goods as $item){
					$tax += $item['number'] * $item['tax_rate'];//统计税金 以便保存
				}
            }

		}

		// 当 id_img_status = 200 的时候，需要支付一定的附件费  20180129 jie
		if($info['id_img_status'] == '200'){
			$extra_fee = M('tran_ulist_extra_fee')->where(array('lid'=>$id))->getField('extra_fee');
		}else{
			$extra_fee = '0';
		}

		$tax = sprintf("%.2f", $tax);

		$data = array();
		$data['weigh_time']      = $time;//称重时间
		$data['weight']          = $weight;//称重实际重量
		$data['freight']         = $cost;//实收运费
		$data['discount_amount'] = $discount;//折扣优惠金额   原价 -（原价*优惠折扣比例）
		$data['charge']          = sprintf("%.2f", $Charge);//服务费，手续费
		$data['tax']             = $tax;//统计税金  所有商品加载一起的税金，美元
		$data['original_price']  = $original_price;//消费金额 原价

		$t_data = array();
		$t_data['freight']         = $cost;//消费金额
		$t_data['discount_amount'] = $discount;//折扣优惠金额
		$t_data['fee']             = sprintf("%.2f", $Charge);//服务费，手续费
		$t_data['tax']             = $tax;//统计税金  所有商品加载一起的税金，美元
		$t_data['original_price']  = $original_price;//消费金额 原价

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
	        	M('ULogs')->add($logs);//保存订单操作记录
			}

			$Model->commit();//提交事务成功

			$back_data = array('id'=>$id, 'weight' => $weight, 'time'=>$time, 'freight'=>$cost, 'tax'=>$tax, 'original_price'=>$original_price, 'RMB_Free_Duty'=>$RMB_Free_Duty, 'US_TO_RMB_RATE'=>$US_TO_RMB_RATE, 'extra_fee'=>$extra_fee);

			return array('state'=>'yes', 'rdata'=>$back_data, 'msg'=>'计费成功', 'lng'=>'charge_success');

		}else{
			$Model->rollback();//事务有错回滚

			return array('state'=>'no', 'msg'=>'保存称重数据失败','lng'=>'failed_to_save_weight');
		}

	}

	/**
	 * [订单支付]
	 * @param  [type] $sn            [订单ID]
	 * @param  [type] $user_id       [账户ID]
	 * @param  [type] $terminal_code [终端编号 20171030]
	 * @return [type]                [description]
	 */
	public function _step_two($id, $user_id, $terminal_code){

		$user = M('UserList')->where(array('id'=>$user_id))->find();
		if(!$user){
			return array('state'=>'no','msg'=>'账户不存在', 'lng'=>'user_not_exist');
		}

		$info = M('TranUlist')->where(array('id'=>$id,'user_id'=>$user_id))->find();  //根据此内部订单号和账户ID找出订单信息
		if(!$info){
			return array('state'=>'no','msg'=>'订单不存在', 'lng'=>'order_not_exist');
		}

		/* 已经扣费成功的，则直接跳过 step_two ，进入step_three */
		//拦截位置  注意：这里，如果订单是已支付，则可以提供再次打印订单的，但不会重复扣费
		if($info['pay_state'] == '1'){
        	//支付已经支付了，将支付单号等必要信息返回给打印系统
        	$redata = array();
			$redata['id']      = $id;
			$redata['user_id'] = $user_id;
			$redata['paykind'] = $info['paykind'];
			$redata['payno']   = $info['payno'];
			$redata['paytime'] = $info['paytime'];
			$redata['balance'] = sprintf("%.2f", $user['amount']);
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

		$freight = $info['freight'];//订单中的已经计算好的实收运费
		$tax     = $info['tax'];//订单中的已经计算好的税金总额
		$cost    = sprintf("%.2f", ($freight + $tax + $extra_fee));

        if($user_amount < $cost){
        	return array('state'=>'no','msg'=>'账户余额不足以支付订单，请先充值', 'lng'=>'balance_not_enough_to_pay');
        }

        $user_amount = sprintf("%.2f", ($user_amount - $cost)); //余额-消费金额

        //查询订单相关的所有商品声明的价格和数量
		$uOrderWhere = 'lid = ' . $info['id'] . ' AND delete_time is null';
        $pro_list = M('TranUorder')->field('price,number')->where($uOrderWhere)->select();

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
			}else if($Terminal['type'] != 'print'){//终端机类型不符合规则
				return array('state'=>'no', 'msg'=>'该终端编号类型不符合规则', 'lng'=>'terminal_not_right');
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
	 * @return [type]          [description]
	 */
	public function _step_three($id, $status, $time, $MKNO, $STNO, $terminal_code){

		$time = date('Y-m-d H:i:s');//打印时间 用服务器的时间

		$info  = M('TranUlist')->where(array('id'=>$id))->find();

		if(!$info){
			return array('state'=>'no', 'msg'=>'订单不存在', 'lng'=>'order_not_exist');
		}

		$Model = M();   //实例化
        $Model->startTrans();//开启事务

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
			$many_map['terminal_id'] = array('eq', $Terminal['id']);

			// 保存数据
			$many_data['tran_id'] = $check_tlist['id']; // 保存 mk_tran_list.id

			// 检查该会员是否在终端操作（打印）过此订单
			$check_relation = M('PrintRelationOrder')->where($many_map)->find();

			// 已存在，则更新
			if($check_relation){
				$save_many = M('PrintRelationOrder')->where(array('id'=>$check_relation['id']))->save($many_data);
			}else{
				$Model->rollback();//事务有错回滚
				return array('state'=>'no','msg'=>'无法找到对应的数据进行更新', 'lng'=>'print_terminal_not_same');
			}
		}
		/* 20171030 新增 end */

		if($save !== false){
			//写入外部订单号
			$packageId  = M('TranUlist')->where(array('MKNO'=>$MKNO))->field('package_id')->find();
			\Think\Log::write('自助--写入外部订单号' . json_encode(M('TranUlist')->getLastSql(),320));
			if(!empty($packageId['package_id'])){
				M('TranList')->where(['MKNO' => $MKNO])->save(['package_id' => $packageId['package_id']]);
				\Think\Log::write('自助--写入外部订单号' . json_encode(M('TranList')->getLastSql(),320));
			}
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

			// 属于 中通 线路的订单  20171127 jie
			if($info['TranKd'] == '17'){
		    	$zt_arr = array(
		    		'STNO'       => $STNO,
		    		'push_state' => 'Verified',
		    		'airno'      => '',
		    		'data'       => array(
		    			'MKNO' => $MKNO,
		    			'STNO' => $STNO,
		    		),
		    	);
		    	include_once(C('Kdno_Path').'\Kdno17.class.php');
    			$Kdno = new \Kdno();
		    	$submit_res = $Kdno->SubmitTracking($zt_arr);// 推送“审单”节点 给中通（如果是中通的订单）
			}

			$Model->commit();//提交事务成功
			return array('state'=>'yes', 'msg'=>'订单打印记录保存成功', 'lng'=>'print_success');

		}else{
			
			$Model->rollback();//事务有错回滚
			return array('state'=>'no', 'msg'=>'订单打印记录保存失败', 'lng'=>'print_failed');
		}
	}

//==========================
	// 获取账户余额
	public function _get_user_balance($user_id){
		$user = M('UserList')->where(array('id'=>$user_id))->find();
		if(!$user){
			return false;
		}else{
			return $user['amount']; //账户余额
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
}