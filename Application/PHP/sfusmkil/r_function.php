<?php
	// 创建xml报文
	/**
	 * [createXML description]
	 * @param  [type] $customerCode    [客户编码]
	 * @param  string $tracking_type   [1.根据顺丰运单号查询; 2.根据客户订单号查询; 3.在IBS查询，不区分运单号和订单号]
	 * @param  [type] $tracking_number [查询号]
	 * @param  string $lang            [语言]
	 * @return [type]                  [description]
	 */
	function createXML($customerCode, $tracking_type='3', $tracking_number, $lang='zh-CN'){

		$xml = '<?xml version="1.0"?>
				<Request service="RouteService" lang="'.$lang.'">
					<Head>'.$customerCode.'</Head>
					<Body>
						<Route tracking_type="'.$tracking_type.'" tracking_number="'.$tracking_number.'"/>
					</Body>
				</Request>';

		return $xml;
	}

	//对象转数组
	function xml_array($array) {
	    if(is_object($array)) {
	        $array = (array)$array;
	    } if(is_array($array)) {
	        foreach($array as $key=>$value) {
	            $array[$key] = object_array($value);
	        }
	    }
	    return $array;
	}