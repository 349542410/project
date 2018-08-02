<?php
/**
 * 支付宝/微信pc 服务器端
 * 用途：专门处理微信/支付宝回调的结果的数据处理
 */
namespace Api\Controller;
use Think\Controller\HproseController;
use Think\Log;
class PaybackPcController extends HproseController{

	//锁定订单状态为  支付中
	public function holdState($arr){
		//回调次数+1
		M('RechargeRecord')->where(array('order_no'=>$arr['out_trade_no']))->setInc('callback',1);
		$checkOrder = M('RechargeRecord')->where(array('order_no'=>$arr['out_trade_no']))->find();

		//检测账户
        $checkUser = M('UserList')->where(array('id'=>$checkOrder['UID']))->find();
        if(!$checkUser){
            Log::write('账户不存在' .json_encode($arr, 320));
            return array('state'=>'no','msg'=>'账户不存在');
        }
		if($checkOrder['pay_state'] == '0'){
            //$Model = M();   //实例化
            //$Model->startTrans();//开启事务

            //回调成功之后，马上进行锁定订单为100(支付中)
            $where['pay_state'] = 100;
            M('RechargeRecord')->where(array('order_no'=>$arr['out_trade_no']))->save($where);

            // 充值金额写入账户余额成功次数加1
           // M('RechargeRecord')->where(array('order_no'=>$arr['out_trade_no']))->setInc('input_num',1);

            //增加会员总金额
           // $lastAmount = floatval($checkOrder['amount_usa']) + floatval($checkUser['amount']); //充值金额 + 账户余额
           // M('UserList')->where(array('id'=>$checkOrder['UID']))->setField('amount',$lastAmount);

            //提交支付处理
            //$Model->commit();
            // return $code = 'paying';//用于 日志查看 和 操作判断
            return array('state'=>'yes','msg'=>'paying');

		}else if($checkOrder['pay_state'] == '100'){
			//因为这次检查的时候发现订单状态是支付中，所以这次发现之后，就需要还原为0，并要求再次回调
            M('RechargeRecord')->where(array('order_no'=>$arr['out_trade_no']))->setField('pay_state',0);

			// return $code = 'locked';//用于 日志查看 和 操作判断
			return array('state'=>'no','msg'=>'locked');

		}else if($checkOrder['pay_state'] == '200'){

			// return $code = 'paid';//用于 日志查看 和 操作判断
    		return array('state'=>'no','msg'=>'paid');
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
	public function verify($arr, $type){

    	$checkOrder = M('RechargeRecord')->where(array('order_no'=>$arr['out_trade_no']))->find();

    	//检查订单是否存在
    	if(!$checkOrder){
    		return array('state'=>'no','msg'=>'订单不存在');
    	}

    	//检查订单状态
    	if($checkOrder['pay_state'] == '200'){
    		return array('state'=>'yes','msg'=>'订单已支付，请勿重复操作');
    		// return true;
    	}

    	//检查用户是否存在
        $checkUser = M('UserList')->where(array('id'=>$checkOrder['UID']))->find();
    	if(!$checkUser){
    		return array('state'=>'no','msg'=>'账户不存在');
    	}

    	//回调的金额
    	$cash_fee = floatval($arr['cash_fee']) / 100;

    	/*if(floatval($checkOrder['amount']) - $cash_fee != 0){
    		return array('state'=>'no','msg'=>'支付金额有误');
    	}*/

    	$last_amount = floatval($checkOrder['amount_usa']) + floatval($checkUser['amount']); //充值金额 + 账户余额

		//更新数据
		$save_data = array();
		$save_data['pay_state']        = 200;  //已支付
		$save_data['pay_amount']       = sprintf("%.2f", $cash_fee); //实际充值金额
		$save_data['payno']            = $arr['transaction_id']; //支付订单号
		$save_data['paytime']          = $arr['time_end']; // 支付完成时间 格式为yyyy-MM-dd HH:mm:ss
		$save_data['coin']             = $arr['fee_type']; //货币类型
		$save_data['openid']           = $arr['openid']; //微信id 或 支付宝账号
		$save_data['bank_type']        = $arr['bank_type']; //银行名称
		$save_data['is_subscribe']     = $arr['is_subscribe']; //用户是否关注公众账号，Y-关注，N-未关注，仅在公众账号类型支付有效
		$save_data['user_balance_usa'] = sprintf("%.2f", $last_amount); //账户余额  20171017

		//20170926 jie 新增 paypal支付 独有的字段
		if($type == 'mkilpaypalpc') $save_data['payerid'] = $arr['payerid'];//付款人ID

        $Model = M();   //实例化
        $Model->startTrans();//开启事务

		$save_recharge = M('RechargeRecord')->where(array('order_no'=>$arr['out_trade_no']))->save($save_data);
        $amount['amount'] = $last_amount;
		$save_user = M('UserList')->where(array('id'=>$checkOrder['UID']))->save($amount);

		//返回0或者false都是事务失败
		if($save_recharge == true && $save_user == true){

			// 充值金额写入账户余额成功次数加1
			$save_num = M('RechargeRecord')->where(array('order_no'=>$arr['out_trade_no']))->setInc('input_num',1);

			$Model->commit();
			// return true;
			return array('state'=>'yes','msg'=>'数据保存成功');
		}else{

			//回调之后，如果出现某步操作错误，则解锁订单为0(待支付)
            $pay_state['pay_state'] = 0;
			M('RechargeRecord')->where(array('order_no'=>$arr['out_trade_no']))->save($pay_state);
			$Model->rollback();
			// return false;
			return array('state'=>'no','msg'=>'数据保存失败');
		}

	}

	//回调数据的综合处理方法
	public function console($data = 0, $type = 0){
		$code = $this->holdState($data);

		//只有paying的时候才能继续往下执行
        if($code['state'] == 'no'){
            return $code;
        }

        //支付状态 SUCCESS(支付宝，微信)  ||  approved(paypal 20170926)
        if($data['return_code'] == 'SUCCESS' || $data['return_code'] == 'approved'){

            if(trim($data['out_trade_no']) != ''){

                $res = $this->verify($data, $type);

                return $res;

            }else{
            	$this->restore($data);//解锁订单状态还原为 待支付
                return array('state'=>'no', 'msg'=>'out_trade_no为空');
            }

        }else{
            return array('state'=>'no', 'msg'=>'return_code为失败');
        }
	}
}