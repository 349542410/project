<?php
namespace Api\Model;
use Think\Model;
class PrintRelationOrderModel extends Model{

	public function count_freight($weight, $Web_Config){
		$Charge           = $Web_Config['fee_service'];		//服务费，手续费
		$Price            = $Web_Config['fee_first'];		//首重价格
		$Heavy            = $Web_Config['weight_first'];	//首重重量
		$Unit             = $Web_Config['weight_next'];		//续重重量(原意：续重计费单位)
		$UnitPrice        = $Web_Config['fee_next'];		//续重单价(原意：续重每单位金额)
		$Discount_Service = $Web_Config['discount_service'];//服务费折扣 百分比
		$Discount_First   = $Web_Config['discount_first'];	//首重折扣  百分比
		$Discount_Next    = $Web_Config['discount_next'];	//续重折扣  百分比

		$data = array();
		
		// 实际称重>首重
		if(floatval($weight) - floatval($Heavy) > 0){

			//超出的重量 = 实际重量 - 首重
			$surp = sprintf("%.2f", (floatval($weight) - floatval($Heavy)));

			//续重计费数量 = 超出的重量 / 续重计费单位 -> 如果存在小数则+1
			$surp_num = sprintf("%.2f", ceil(floatval($surp) / floatval($Unit)));

			//续重价格 = 续重计费数量*续重每单位金额
			$amount = sprintf("%.2f", (floatval($UnitPrice) * floatval($surp_num)));
			
			//总金额 = 首重价格*首重折扣 + 续重价格*续重折扣
			$freight = sprintf("%.2f", ((floatval($Price) * floatval($Discount_First)/100) + (floatval($amount) * floatval($Discount_Next)/100)));

			//实收金额 = 总金额 + 服务费*服务费折扣
			$data['cost'] = sprintf("%.2f", (floatval($freight) + (floatval($Charge) * floatval($Discount_Service)/100)));
			
			//未计算所有折扣优惠的 消费金额 原价
			$data['original_price'] = sprintf("%.2f", (floatval($Price) + floatval($amount) + floatval($Charge)));
		}else{

			// 由于实际称重重量没有超过首重，所有运费只收首重运费的金额
			// 实收金额 = 首重价格*首重折扣 + 服务费*服务费折扣
			$data['cost'] = sprintf("%.2f", ((floatval($Discount_First)/100 * floatval($Price)) + (floatval($Charge) * floatval($Discount_Service)/100)));

			//未计算所有折扣优惠的 消费金额 原价
			$data['original_price'] = sprintf("%.2f", (floatval($Price) + floatval($Charge)));

		}

		// 优惠金额 = 原价 - 优惠后的金额
		$data['discount'] = sprintf("%.2f", (floatval($original_price) - floatval($cost)));

		return $data;
	}

}