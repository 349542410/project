<?php
/**
 * 支付宝/微信回调综合处理
 */
namespace WebUser\Controller;
use Think\Controller;
class PaybackController extends Controller {

	/**
	 * 异步回调(服务器后端的回调)  区分 支付宝/微信
	 * $type  [回调类型，默认支付宝]
	 * @return [type] [description]
	 */
	public function notifyurl(){
		$type = (I('get.t')) ? strtolower(trim(I('get.t'))) : ''; //回调类型

		if($type == ''){
			die('缺少参数“t”');
		}

		//判断 支付宝 / 微信 / paypal
		switch ($type) {
			case 'mkilalipc'://支付宝
				$notify = new \Org\Alipay\Notify();
				$verify_result = $notify->verifyNotify("MkilAliPc");
				break;

			case 'mkilwxpc'://微信

				$notify = new \Org\Wxpay\Notify();
				$verify_result = $notify->verifyNotify("MkilWxPc");
				break;

			case 'mkilpaypalpc'://paypal  20170926
				$conf = '';//尚未配置
				$data = array(
					"access_token" => $access_token,	//访问口令  尚未配置
				);

				$notify = new \Lib82\paypal\Notify();
				$verify_result = $notify->verifyReturn($conf, $data);
				break;

			default:
				die('参数错误');
				break;
		}

        //生成日志
        $xmlsave = WU_ABS_FILE.'/'.date('Y').$type.'/';
        $file_name = date('Y').$type.'_'.time().'.txt';    //文件名

        $content = "======== 回调结果 =========\r\n\r\n".json_encode($verify_result);

        if(!is_dir($xmlsave)) mkdir($xmlsave, 0777, true);//第三个参数设置true 设置递归模式自动创建必需的目录文件夹

        file_put_contents($xmlsave.$file_name, $content);
        //生成日志 end

		//1为成功 0为失败
        if($verify_result['code'] == '1'){
            vendor('Hprose.HproseHttpClient');
            $Wclient = new \HproseHttpClient(C('WAPIURL').'/PaybackPc');

            // 成功则保存数据库
            $data = $verify_result['data'];

            //测试用途
            //$arr['cash_fee'] = 0.07;

            $code = $Wclient->console($data, $type);//锁定订单状态为  支付中

            file_put_contents($xmlsave.$file_name, json_encode($code), FILE_APPEND);//日志文件追加处理结果的收集

            if($code['state'] == 'no'){

                // paid 订单已经支付成功
                if($code['msg'] == 'paid'){
                    $notify->setSuccess();exit;//如果所有操作成功
                }else{
                    $notify->setFail();exit;
                }
                
            }else{
                $notify->setSuccess();exit;//如果所有操作成功
            }

        }else{

            //验证失败，再次回调
            $notify->setFail();exit;
        }

	}

	/**
	 * 同步回调（客人看到的界面）  支付宝独有，微信没有同步回调
	 * @return [type] [description]
	 */
	public function returnurl(){
        \Think\Log::write('同步回调'.json_encode($_REQUEST, 320));
		$notify = new \Org\Alipay\Notify();
		$verify_result = $notify->verifyReturn();
		
		if($verify_result['code'] == '1'){
			$data = $verify_result['data'];

			$cash_fee = $data['cash_fee'];//充值金额

			//充值成功界面
			// $this->redirect('WebRecharge/recharge_success',array('order_sn'=>$data['out_trade_no'],'account'=>$cash_fee));
			$this->redirect('WebRecharge/index');
		}else{
			//充值失败界面
			// $this->redirect('WebRecharge/recharge_error',array('error'=>$verify_result['error']));
			// $this->redirect('WebRecharge/index');
		}

	}
}