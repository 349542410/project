<?php
	
	function getArr($arr, $type, $customs, $client){
		// header('Content-type:text/html;charset=UTF-8');	//设置输出格式

		$data = array();
		$order = array();

		$data['order_sn']       = $arr['STNO'];
		$data['goods_amount']   = $arr['price'];
		$data['discount']       = $arr['discount'];
		$data['shipping_fee']   = 0;//运费
		$data['taxrate_total']  = 0;//税率总计
		$data['order_amount']   = $arr['price'];//实际支付
		$data['buyer_phone']    = $arr['reTel'];
		$data['reciver_name']   = $arr['receiver'];
		$data['reciver_card']   = ($arr['idno'] != 0) ? $arr['idno'] : '000000000000000000';//买家的身份证号码 
		$data['trade_no']       = '20160001634226001';//支付交易ID 
		$data['mob_phone']      = $arr['reTel'];
		$data['address']        = $arr['reAddr'];
		$data['promotion_info'] = $arr['notes'];//备注

		if($type != 'ORDER_INFO'){
			$data['order_sn_code'] = '';
		}
		
		if($type == 'LIST_INFO' || $type == 'RE_LIST_INFO'){
			$data['bar_code']      = '';
			$data['shipping_code'] = '';
		}

		foreach($arr['extend_order_goods'] as $key=>$v){
			$order[$key]['rec_id']          = $key+1;
			$order[$key]['goods_sn']        = $v['barcode'];
			$order[$key]['goods_name']      = $v['detail'];//商品名称
			$order[$key]['unit']            = (trim($v['unit']) != '') ? unit_code(trim($v['unit'])) : '007';//单位。填写海关标准的参数代码，参照《JGS-20 海关业务代码集》- 计量单位代码。
			$order[$key]['goods_num']       = $v['number'];//商品实际数量
			$order[$key]['goods_price']     = $v['price'];//商品单价。赠品单价填写为“0”
			$order[$key]['goods_pay_price'] = number_format((floatval($v['price']) * floatval($v['number'])), 2, '.', '');//商品总价，等于单价乘以数量
			$order[$key]['country_code']    = (trim($v['source_area']) != '') ? country_code(trim($v['source_area'])) : '503';//$v['source_area'];//原产国。填写海关标准的参数代码，参照《JGS-20 海关业务代码集》-国家（地区）代码表。

			if($type == 'LIST_INFO'){
				$order[$key]['unit1']         = '007';
				$order[$key]['unit2']         = '035';
				$order[$key]['qty1']          = $v['number'];
				$order[$key]['qty2']          = $v['number'];
				$order[$key]['goods_barcode'] = $v['barcode'];
				$order[$key]['goods_hscode']  = $v['hscode'];
				$order[$key]['gname']         = $v['detail'];
				$order[$key]['customs_spec']  = $v['specifications'];
			}
		}

		//=======//
		$data['extend_order_goods'] = $order;

		$result = $customs->$type($data);
		
		print_r($result);
		die;

			$result = '<?xml version="1.0" encoding="UTF-8"?>
<CEB624Message guid="4CDE1CFD-EDED-46B1-946C-B8022E42FCTG" version="1.0"  xmlns="http://www.chinaport.gov.cn/ceb" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">
	<InvtCancelReturn>
		<guid>ERFT1CFD-EDED-46B1-946C-B8022E42FC94</guid>
		<customsCode>0102</customsCode>
		<agentCode>1105910158</agentCode>
		<ebpCode>1105910158</ebpCode>
		<ebcCode>1105910158</ebcCode>
		<copNo>cop2016032210052	</copNo>
		<preNo>B20160321000000152</preNo>
		<invtNo>150032133004000520</invtNo>
		<returnStatus>23</returnStatus>
		<returnTime>20160330184802222</returnTime>
		<returnInfo>test</returnInfo>
	</InvtCancelReturn>
</CEB624Message>
';
		$rlist = xml_to_array($result);
		// print_r($rlist);
		// $aa = three_to_one($rlist);
		// print_r($aa);die;

		$res = $client->save($rlist, $arr['id'], $type);	//直接传入数组形式的数据
		print_r($res);
	}

	// Xml 转 数组, 包括根键，忽略空元素和属性
	function xml_to_array( $xml ) {
	    $reg = "/<(\\w+)[^>]*?>([\\x00-\\xFF]*?)<\\/\\1>/";
	    if(preg_match_all($reg, $xml, $matches))
	    {
	        $count = count($matches[0]);
	        $arr = array();
	        for($i = 0; $i < $count; $i++)
	        {
	            $key= $matches[1][$i];
	            $val = xml_to_array( $matches[2][$i] );  // 递归
	            if(array_key_exists($key, $arr))
	            {
	                if(is_array($arr[$key]))
	                {
	                    if(!array_key_exists(0,$arr[$key]))
	                    {
	                        $arr[$key] = array($arr[$key]);
	                    }
	                }else{
	                    $arr[$key] = array($arr[$key]);
	                }
	                $arr[$key][] = $val;
	            }else{
	                $arr[$key] = $val;
	            }
	        }
	        return $arr;
	    }else{
	        return $xml;
	    }
	}

	function three_to_one($arr,$i=0){
		if($i < 2){
			$i++;
			return three_to_one($arr[key($arr)], $i);
		}else{
			return $arr;
		}
	}