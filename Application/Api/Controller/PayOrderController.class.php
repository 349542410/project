<?php
/**
 * 物流官网---会员平台---支付订单  暂停使用
 */
namespace Api\Controller;
use Think\Controller\HproseController;
class PayOrderController extends HproseController{

	/**
	 * [checkInfo description]
	 * @param  [type] $sn      [内部订单号]
	 * @param  [type] $uucode  [凭证号]
	 * @param  [type] $user_id [账户ID]
	 * @return [type]          [description]
	 */
	public function checkInfo($sn, $uucode, $user_id){

		$user = M('UserList')->where(array('id'=>$user_id))->find();
		if(!$user){
			return array('state'=>'no','msg'=>'账户不存在','info'=>'user_not_exist');
		}

		if($user['status'] != '1'){
			return array('state'=>'no','msg'=>'账户审核未通过','info'=>'user_not_examine');
		}

		$info = M('TranUlist')->where(array('order_no'=>$sn,'user_id'=>$user_id))->find();  //根据此内部订单号和账户ID找出订单信息
		if(!$info){
			return array('state'=>'no','msg'=>'订单资料不存在','info'=>'order_not_exist');
		}

		if($info['pay_state'] == '1'){
			return array('state'=>'no','msg'=>'订单已支付，请勿重复操作','info'=>'order_already_paid');
		}

		$user_amount = floatval($user['amount']); //账户余额
		if($user_amount == 0){
			return array('state'=>'no','msg'=>'账户余额不足，请先充值','info'=>'balance_not_enough');
		}

		//查询对应的中转线路ID的配置信息
        $trankd = $info['TranKd'];
        $Web_Config = C('Web_Config')[$trankd];

        $freight  = floatval($Web_Config['Price']);  //首重运费价格
        $discount = floatval($Web_Config['Price']) * floatval($Web_Config['Discount']); //折扣金额
        $charge   = floatval($Web_Config['Charge']);  //手续费

		//总金额 = 手续费 + 首重运费价格 - 折扣金额
        $cost = $charge + $freight - $discount;

        if($user_amount < $cost){
        	return array('state'=>'no','msg'=>'账户余额不足以支付订单，请先充值','info'=>'balance_not_enough_to_pay');
        }

        $user_amount = $user_amount - $cost; //余额-消费金额

        //查询订单相关的所有商品声明的价格和数量
        $pro_list = M('TranUorder')->field('price,number')->where(array('lid'=>$info['id']))->select();

        //支付订单的时候，才把订单相关的商品声明的总价值输入到订单的总价字段上保存
        $goodsPrice = 0;//商品总价
        $goodsNum   = 0;//商品总数量
        foreach($pro_list as $vo){
            $goodsPrice += (intval($vo['number']) * floatval($vo['price']));
            $goodsNum += intval($vo['number']);
        }

        $Model = M();   //实例化
        $Model->startTrans();//开启事务

        //更新账户余额
        $save_user = M('UserList')->where(array('id'=>$user['id']))->setField('amount',$user_amount);

		$payno   = StrOrderOne($user_id, 'payno');//支付单号
		$paytime = date('Y-m-d H:i:s');//支付时间

		$order_data['pay_state'] = 1;  //支付状态
		$order_data['price']     = $goodsPrice;//所有商品声明的总价值
		$order_data['number']    = $goodsNum;//所有商品声明的总数量
		$order_data['payno']     = $payno;//支付单号
		$order_data['paytime']   = $paytime;//支付时间
		$order_data['freight']   = $cost;//运费
		$order_data['discount']  = $discount;//折扣金额
		$order_data['charge']    = $charge;//手续费

		//更新物流订单
        $save_order = M('TranUlist')->where(array('random_code'=>$info['random_code'],'user_id'=>$info['user_id']))->save($order_data);

		$t_data['pay_state'] = 1;  //支付状态
		$t_data['payno']     = $payno;//支付单号
		$t_data['paytime']   = $paytime;//支付时间
		$t_data['freight']   = $cost;//消费金额
		$t_data['discount']  = $discount;//折扣金额
		$t_data['fee']       = $charge;//手续费

		$save_record = M('WlorderRecord')->where(array('order_no'=>$info['order_no']))->save($t_data);

        if($save_user == true && $save_order == true && $save_record == true){

        	//支付订单后，扣款次数+1
        	M('WlorderRecord')->where(array('order_no'=>$info['order_no']))->setInc('deduct_num',1);
        	$Model->commit();//提交事务成功
        	return array('state'=>'yes','msg'=>'支付成功','t_data'=>$info,'info'=>'pay_success');
        }else{
        	$Model->rollback();//事务有错回滚
        	return array('state'=>'no','msg'=>'支付失败，如需帮助请咨询客服','info'=>'pay_failed');
        }
	}
}