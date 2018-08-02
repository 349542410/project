<?php
/**
 * 无用途 停止使用
 */
namespace WebUser\Controller;
use Think\Controller;
class WxpayController extends Controller{

	public function _initialize() {
		vendor('Hprose.HproseHttpClient');
		$Wclient = new \HproseHttpClient(C('WAPIURL').'/WxpayPc');
		$this->Wclient = $Wclient;
	}

	/**
	 * 异步回调(服务器后端的回调)
	 * @return [type] [description]
	 */
	public function notifyurl(){
		$notify = new \Org\Wxpay\Notify();
		// $verify_result = $notify->verifyNotify();
		$verify_result = $notify->verifyNotify("MkilWxPc");

		//生成日志
		$xmlsave = API_ABS_FILE.'/wxpay/';
		$file_name = 'wxpay_'.time().'.txt';	//文件名

		$content = "======== 回调结果 =========\r\n\r\n".json_encode($verify_result);

		if(!is_dir($xmlsave)) mkdir($xmlsave, 0777, true);//第三个参数设置true 设置递归模式自动创建必需的目录文件夹

		file_put_contents($xmlsave.$file_name, $content);
		//生成日志 end
		die;
		
		//1为成功 0为失败
		if($verify_result['code'] == '1'){
			
			$Wclient = $this->Wclient;

			$data = $verify_result['data']; //校验回调的数据

			$code = $Wclient->holdState($data);//锁定订单状态为  支付中

			if($code == 'locked'){
				$notify->setFail();//订单正在锁定处于支付中，希望再次回调
			}else if($code == 'paid'){
				$notify->setSuccess();//订单已经成功支付，终止回调
			}

			//支付状态 SUCCESS
			if($data['return_code'] == 'SUCCESS'){

				if(trim($data['out_trade_no']) != ''){

		        	$res = $Wclient->verify($data);

		        	if($res == true){
						$notify->setSuccess();//如果所有操作成功
		        	}else{
						$notify->setFail();//如果操作错误，希望再次回调
		        	}

				}else{
					$code = $Wclient->restore($data);//解锁订单状态还原为 待支付
					$notify->setFail();//如果操作错误，希望再次回调
				}

			}else{
				$code = $Wclient->restore($data);//解锁订单状态还原为 待支付
				$notify->setFail();//如果操作错误，希望再次回调
			}

			//生成日志
			$xmlsave = API_ABS_FILE.'/wxpay/';
			$file_name = 'wxpay'.'_'.$data['out_trade_no'].'_'.time().'.txt';	//文件名

			$content = "======== 回调结果 =========\r\n\r\n".json_encode($data)."\r\n\r\n======== 请求数据(true表示成功支付) =========\r\n\r\n".json_encode($res)."\r\n\r\n======== 回调成功后马上检查订单锁定状态 =========\r\n\r\n".json_encode($code);

			if(!is_dir($xmlsave)) mkdir($xmlsave, 0777, true);//第三个参数设置true 设置递归模式自动创建必需的目录文件夹

			file_put_contents($xmlsave.$file_name, $content);
			//生成日志 end
			
		}else{

			//生成日志
			$xmlsave = API_ABS_FILE.'/wxpay/';
			$file_name = 'wxpay_'.time().'.txt';	//文件名

			$content = "======== 回调结果 =========\r\n\r\n".json_encode($verify_result);

			if(!is_dir($xmlsave)) mkdir($xmlsave, 0777, true);//第三个参数设置true 设置递归模式自动创建必需的目录文件夹

			file_put_contents($xmlsave.$file_name, $content);
			//生成日志 end
			//
			$notify->setFail();//如果操作错误，希望再次回调
		}

	}

	/**
	 * 同步回调（客人看到的界面）
	 * @return [type] [description]
	 */
/*	public function returnurl(){
		$notify = new \Org\Wxpay\Notify();
		$verify_result = $notify->verifyReturn();
		
		if($verify_result['code'] == '1'){
			$data = $verify_result['data'];

			$cash_fee = $data['cash_fee'];//充值金额

			//充值成功界面
			$this->redirect('WebRecharge/recharge_success',array('order_sn'=>$data['out_trade_no'],'account'=>$cash_fee));
		}else{
			//充值失败界面
			$this->redirect('WebRecharge/recharge_error',array('error'=>$verify_result['error']));
			// $this->redirect('WebRecharge/index');
		}

	}*/

}