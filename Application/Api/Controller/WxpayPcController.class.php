<?php
/**
 * 支付宝pc 服务器端  停止使用
 */
namespace Api\Controller;
use Think\Controller\HproseController;
class WxpayPcController extends HproseController{

	//锁定订单状态为  支付中
	public function holdState($arr){

		//回调次数+1
		M('RechargeRecord')->where(array('order_no'=>$arr['out_trade_no']))->setInc('callback',1);

		$checkOrder = M('RechargeRecord')->where(array('order_no'=>$arr['out_trade_no']))->find();

		if($checkOrder['pay_state'] == '0'){

			//回调成功之后，马上进行锁定订单为100(支付中)
			M('RechargeRecord')->where(array('order_no'=>$arr['out_trade_no']))->setField('pay_state',100);
			
			return $code = 'paying';//用于 日志查看 和 操作判断

		}else if($checkOrder['pay_state'] == '100'){

			return $code = 'locked';//用于 日志查看 和 操作判断
			// return array('state'=>'no','msg'=>'订单状态正处于支付中');

		}else if($checkOrder['pay_state'] == '200'){

			return $code = 'paid';//用于 日志查看 和 操作判断
    		// return array('state'=>'no','msg'=>'订单已支付，请勿重复操作');
    	}
	}

	//解除订单锁定
	public function restore($arr){
		//回调之后，如果出现某步操作错误，则解锁订单为0(待支付)
		M('RechargeRecord')->where(array('order_no'=>$arr['out_trade_no']))->setField('pay_state',0);
		return $code = 'unlock';//仅用于日志查看
	}

	/**
	 * 异步回调的数据检查和数据保存
	 * @param  [type] $arr [回调成功返回的数组]
	 * @return [type]      [description]
	 */
	public function verify($arr){

    	$checkOrder = M('RechargeRecord')->where(array('order_no'=>$arr['out_trade_no']))->find();

    	//检查订单是否存在
    	if(!$checkOrder){
    		return array('state'=>'no','msg'=>'订单不存在');
    	}

    	//检查订单状态
    	if($checkOrder['pay_state'] == '200'){
    		// return array('state'=>'no','msg'=>'订单已支付，请勿重复操作');
    		return true;
    	}

    	//检查用户是否存在
        $checkUser = M('UserList')->where(array('id'=>$checkOrder['UID']))->find();
    	if(!$checkUser){
    		return array('state'=>'no','msg'=>'账户不存在');
    	}

    	//回调的金额
    	$cash_fee = floatval($arr['cash_fee']) / 100;

    	if(floatval($checkOrder['amount']) != $cash_fee){
    		return array('state'=>'no','msg'=>'支付金额有误');
    	}

		//更新数据
		$save_data = array();
		$save_data['pay_state']    = 200;  //已支付
		$save_data['pay_amount']   = $cash_fee; //实际充值金额
		$save_data['payno']        = $arr['transaction_id']; //支付订单号
		$save_data['paytime']      = $arr['time_end']; // 支付完成时间 格式为yyyy-MM-dd HH:mm:ss
		$save_data['coin']         = $arr['fee_type']; //货币类型
		$save_data['openid']       = $arr['openid']; //微信id 或 支付宝账号
		$save_data['bank_type']    = $arr['bank_type']; //银行名称
		$save_data['is_subscribe'] = $arr['is_subscribe']; //用户是否关注公众账号，Y-关注，N-未关注，仅在公众账号类型支付有效

		$last_amount = $cash_fee + floatval($checkUser['amount']); //账户余额+充值金额

        $Model = M();   //实例化
        $Model->startTrans();//开启事务

		$save_recharge = M('RechargeRecord')->where(array('order_no'=>$arr['out_trade_no']))->save($save_data);

		$save_user = M('UserList')->where(array('id'=>$checkOrder['UID']))->setField('amount',$last_amount);

		//返回0或者false都是事务失败
		if($save_recharge == true && $save_user == true){

			// 充值金额写入账户余额成功次数加1
			$save_num = M('RechargeRecord')->where(array('order_no'=>$arr['out_trade_no']))->setInc('input_num',1);

			$Model->commit();
			return true;
		}else{

			//回调之后，如果出现某步操作错误，则解锁订单为0(待支付)
			M('RechargeRecord')->where(array('order_no'=>$arr['out_trade_no']))->setField('pay_state',0);
			$Model->rollback();
			return false;
		}

	}
}