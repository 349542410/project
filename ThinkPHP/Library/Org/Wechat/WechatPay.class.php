<?php
/*/ +----------------------------------------------------------------------
// | Author: ManHo <humanhe@gmail.com>
	如果闪一下，不出现密码界面，请检查 “支付授权目录”，“测试授权目录”
	它是以/结尾，如http://ad.mg.megao.hk/Advert/Order/place_order
	只需填写 http://ad.mg.megao.hk/Advert/Order/即可
// +----------------------------------------------------------------------*/
namespace Org\Wechat;

class WechatPay{
	var $wxp;
	var $wdt;
	var $wpoq;
	var $callback;
	public function CreateJSPay($data,$tourl=null,$para=null){
        vendor('Wxpay.WxPay#Data');
		vendor('Wxpay.WxPay#Api');
		$openId = getwxid();//'o9COGwJgjxQfuZQiWWXemrjqWb3w';//
		if($openId==''){
			throw new \Exception("无法获取微信身份 ");
		}

		$input = new \WxPayUnifiedOrder();
		$needvalue = array('body','detail','trade_no','fee','attach');
		foreach ($needvalue as $value){
			if(!array_key_exists($value,$data)){
				throw new \Exception("Data 缺少 ".$value);
			}
		}
		
		$input->SetBody($data['body']);
		$input->SetDetail($data['detail']);
		$input->SetOut_trade_no($data['trade_no']);
		$input->SetTotal_fee($data['fee']);

		//商品标记，代金券或立减优惠功能的参数，说明详见代金券或立减优惠
		//$input->SetGoods_tag($data['tag']);

		//$input->SetProduct_id($data['product_id']);

		//$input->SetFee_type('CNY'); 		 // 货币
		$input->SetAttach($data['attach']);  // 附加数据，在查询API和支付通知中原样返回，该字段主要用于商户携带订单的自定义数据
		$input->SetTime_start(date("YmdHis"));
		$input->SetTime_expire(date("YmdHis", time() + 600));
		$input->SetTrade_type("JSAPI");
		$input->SetOpenid($openId);
		//$input->SetNotify_url("http://m.megao.cn/t.php");
		$input->SetNotify_url("http://vip.megao.cn/Wxpay/notify");
		//$input->SetNotify_url("http://m.megao.cn/p/example/notify.php");

		$WxPayApi 			= new \WxPayApi();
		$order 				= $WxPayApi->unifiedOrder($input);
		//echo $openId;
		//var_dump($order);die();
		
		//160409Man添加出错时的处理
		if(!array_key_exists("appid",$order)
		|| !array_key_exists("prepay_id", $order)
		|| $order['prepay_id'] == ""){
			//echo json_encode($order);
			return 'errororder';
		}


		$jsApiParameters 	= $WxPayApi->GetJsApiParameters($order);
		$str 				= self::genJS($jsApiParameters,$tourl);
		//echo $str;
		return $str;

		//return $jsApiParameters;
    }
    private function genJS($jsApiParameters,$url=null){
    	$url = $url?:U('Index/index');
    	//"{:U('done')}?err_code="+res.err_code+"&err_desc="+res.err_desc+"&err_msg="+res.err_msg
    	//window.location.href="{$url}";WeixinJSBridge.log(res.err_msg);
return <<<EOF
<script type="text/javascript">
function jsApiCall(){WeixinJSBridge.invoke('getBrandWCPayRequest',{$jsApiParameters},function(res){window.location.href="{$url}"});}
function callpay(){if(typeof WeixinJSBridge == "undefined"){if(document.addEventListener){document.addEventListener('WeixinJSBridgeReady', jsApiCall, false);}else if(document.attachEvent){document.attachEvent('WeixinJSBridgeReady', jsApiCall);document.attachEvent('onWeixinJSBridgeReady', jsApiCall);}}else{jsApiCall();}}
</script>
EOF;
    }
    public function notify($callback){
    	if(!$callback) return 'callback';
    	$this->callback 	= $callback;
        vendor('Wxpay.WxPay#Data');
		vendor('Wxpay.WxPay#Api');
		//当返回false的时候，表示notify中调用NotifyCallBack回调失败获取签名校验失败，此时直接回复失败
		$msg 		= "OK";
		$wxp 		= new \WxpayApi();
		$wdt 		= new \WxPayNotifyReply();
		$wpoq 		= new \WxPayOrderQuery();
		$this->wxp 	= $wxp;
		$this->wdt 	= $wdt;
		$this->wpoq = $wpoq;
		$result = $this->wxp->notify(array($this, 'NotifyCallBack'), $msg);
		if($result == false){
			$wdt->SetReturn_code("FAIL");
			$wdt->SetReturn_msg($msg);
			self::ReplyNotify(false);
			return;
		} else {
			//该分支在成功回调到NotifyCallBack方法，处理完成之后流程
			$wdt->SetReturn_code("SUCCESS");
			$wdt->SetReturn_msg("OK");
		}
		self::ReplyNotify($needSign);
    }
    /**
	 * 
	 * 回复通知
	 * @param bool $needSign 是否需要签名输出
	 */
	final private function ReplyNotify($needSign = true){
		//如果需要签名
		$wdt 	= $this->wdt;
		if($needSign == true && 
			$wdt->GetReturn_code($return_code) == "SUCCESS")
		{
			$wdt->SetSign();
		}
		$rxml = $wdt->ToXml();
		exit($rxml);
	}
	public function NotifyCallBack($data){
		if(!array_key_exists("transaction_id", $data)){
			$msg = "输入参数不正确";
			return false;
		}

		//查询订单，判断订单真实性
		if(!$this->Queryorder($data["transaction_id"])){
			$msg = "订单查询失败";
			return false;
		}
		//return true;
		if($this->callback){
			return call_user_func($this->callback,$data);
		}
		return false;
	}
	//查询订单,以确认是否是真实的支付了
	public function Queryorder($transaction_id)
	{
		$input 	= $this->wpoq;
		$wxp 	= $this->wxp;
		$input->SetTransaction_id($transaction_id);
		$result = $wxp->orderQuery($input);
		//echo json_encode($result);
		//Log::DEBUG("query:" . json_encode($result));
		if(array_key_exists("return_code", $result)
			&& array_key_exists("result_code", $result)
			&& $result["return_code"] == "SUCCESS"
			&& $result["result_code"] == "SUCCESS")
		{
			return true;
		}
		return false;
	}	
}