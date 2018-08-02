<?php
/**
 * 海关接口类
 *
 */
final class Customs{
	
	const USERNAME ='4423962963';//海关代码
	const DXPID    = 'DXPENT0000012883';//报文传输编号申请
	const APIURL   ='http://221.224.206.244:8081/KJPOSTWEB/Data.aspx';
	const SHOPNAME ='广州美快软件开发有限公司';
    
    private $CUSTOMS_CODE = '8002'; 
    private $BIZ_TYPE_CODE='I20';//I10
    private $CURRENCY_CODE='142';//币制代码
    private $SORTLINE_ID='SORTLINE03';//分拣线标识 SORTLINE01
    private $MessageTime;
    private $MessageId;
    private $PassWord='Hr6qngNf$XjADqQNW$IC3zz2yRvHyodr';
    private $guid ;
    function __construct(){
       $this->guid = $this->guid();
    }
    /* 订单
     * 
     *  
     *  */
  public  function ORDER_INFO($data,$appType=2){
  	header('Content-type:text/json;charset=UTF-8');	//设置输出格式
        $xml = '<?xml version="1.0" encoding="UTF-8"?>
<ceb:CEB311Message guid="'.$this->guid.'" version="1.0"  xmlns:ceb="http://www.chinaport.gov.cn/ceb" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">
	<ceb:Order>
		<ceb:OrderHead>
			<ceb:guid>'.$this->guid.'</ceb:guid>
			<ceb:appType>'.$appType.'</ceb:appType>
			<ceb:appTime>'.date('Ymdhis').'</ceb:appTime>
			<ceb:appStatus>2</ceb:appStatus>
			<ceb:orderType>I</ceb:orderType>
            <ceb:orderNo>'.$data['order_sn'].'</ceb:orderNo>
			<ceb:ebpCode>'.self::USERNAME.'</ceb:ebpCode>
			<ceb:ebpName>'.self::SHOPNAME.'</ceb:ebpName>
			<ceb:ebcCode>'.self::USERNAME.'</ceb:ebcCode>
			<ceb:ebcName>'.self::SHOPNAME.'</ceb:ebcName>
			<ceb:goodsValue>'.number_format((floatval($data['goods_amount'])+floatval($data['discount'])), 2, '.', '').'</ceb:goodsValue>
			<ceb:freight>'.$data['shipping_fee'].'</ceb:freight>
			<ceb:discount>'.$data['discount'].'</ceb:discount>
			<ceb:taxTotal>'.$data['taxrate_total'].'</ceb:taxTotal>
			<ceb:acturalPaid>'.$data['order_amount'].'</ceb:acturalPaid>
			<ceb:currency>'.$this->CURRENCY_CODE.'</ceb:currency>
			<ceb:buyerRegNo>'.$data['buyer_phone'].'</ceb:buyerRegNo>
			<ceb:buyerName>'.$data['reciver_name'].'</ceb:buyerName>
			<ceb:buyerIdType>1</ceb:buyerIdType>
			<ceb:buyerIdNumber>'.trim(strtoupper($data['reciver_card'])).'</ceb:buyerIdNumber>
			<ceb:payCode>312226T003</ceb:payCode>
			<ceb:payName>通联支付网络服务股份有限公司</ceb:payName>
			<ceb:payTransactionId>'.$data['trade_no'].'</ceb:payTransactionId>
			<ceb:batchNumbers>'.$data['buyer_phone'].'</ceb:batchNumbers>
			<ceb:consignee>'.$data['reciver_name'].'</ceb:consignee>
			<ceb:consigneeTelephone>'.trim($data['mob_phone']).'</ceb:consigneeTelephone>
			<ceb:consigneeAddress>'.$data['address'].'</ceb:consigneeAddress>
			<ceb:consigneeDistrict></ceb:consigneeDistrict>
			<ceb:note>'.$data['promotion_info'].'</ceb:note>
		</ceb:OrderHead>';
		 foreach($data['extend_order_goods'] as $v){
			$xml .='<ceb:OrderList>
			<ceb:gnum>'.($v['rec_id']).'</ceb:gnum>
			<ceb:itemNo>'.$v['goods_sn'].'</ceb:itemNo>
			<ceb:itemName>'.$v['goods_name'].'</ceb:itemName>
			<ceb:itemDescribe>'.$v['goods_name'].'</ceb:itemDescribe>
			<ceb:barCode>'.$v['goods_sn'].'</ceb:barCode>
			<ceb:unit>'.$v['unit'].'</ceb:unit>
			<ceb:qty>'.$v['goods_num'].'</ceb:qty>
			<ceb:price>'.$v['goods_price'].'</ceb:price>
			<ceb:totalPrice>'.number_format($v['goods_pay_price'], 2, '.', '').'</ceb:totalPrice>
			<ceb:currency>'.$this->CURRENCY_CODE.'</ceb:currency>
			<ceb:country>'.$v['country_code'].'</ceb:country>
			<ceb:note></ceb:note>
			</ceb:OrderList>';
		 } 
           $xml.='
	</ceb:Order>
	<ceb:BaseTransfer>
		<ceb:copCode>'.self::USERNAME.'</ceb:copCode>
		<ceb:copName>'.self::SHOPNAME.'</ceb:copName>
		<ceb:dxpMode>DXP</ceb:dxpMode>
		<ceb:dxpId>'.self::DXPID.'</ceb:dxpId>
		<ceb:note></ceb:note>
	</ceb:BaseTransfer>	
</ceb:CEB311Message>
';
print_r($xml);
// die;
    	return $this->_curl_post(self::APIURL,$xml);
    }
	/* 清单
     * 
     *  
     *  */
  public  function LIST_INFO($data,$appType=1){
		if($appType==1){
			$data['order_sn_code']='';
			$data['bar_code']='';
		}
	  $xml = '<?xml version="1.0" encoding="UTF-8"?>
<ceb:CEB621Message guid="'.$this->guid.'" version="1.0"  xmlns:ceb="http://www.chinaport.gov.cn/ceb" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">
	<ceb:Inventory>
		<ceb:InventoryHead>
			<ceb:guid>'.$this->guid.'</ceb:guid>
			<ceb:appType>'.$appType.'</ceb:appType>
			<ceb:appTime>'.date('Ymdhis').'</ceb:appTime>
			<ceb:appStatus>1</ceb:appStatus>
			<ceb:orderNo>'.$data['order_sn'].'</ceb:orderNo>
			<ceb:ebpCode>'.self::USERNAME.'</ceb:ebpCode>
			<ceb:ebpName>'.self::SHOPNAME.'</ceb:ebpName>
			<ceb:ebcCode>'.self::USERNAME.'</ceb:ebcCode>
			<ceb:ebcName>'.self::SHOPNAME.'</ceb:ebcName>
			<ceb:logisticsNo>'.$data['shipping_code'].'</ceb:logisticsNo>
			<ceb:logisticsCode>5003980036</ceb:logisticsCode>
			<ceb:logisticsName>中国邮政速递物流股份有限公司重庆市分公司</ceb:logisticsName>
			<ceb:copNo>sbl'.$data['order_sn'].'</ceb:copNo>
			<ceb:preNo>'.$data['bar_code'].'</ceb:preNo>
			<ceb:assureCode>'.self::USERNAME.'</ceb:assureCode>
			<ceb:emsNo>H500561001204</ceb:emsNo>
			<ceb:invtNo>'.$data['order_sn_code'].'</ceb:invtNo>
			<ceb:ieFlag>I</ceb:ieFlag>
			<ceb:declTime>'.date('Ymd').'</ceb:declTime>
			<ceb:customsCode>8015</ceb:customsCode>
			<ceb:portCode>8015</ceb:portCode>
			<ceb:ieDate>'.date('Ymd').'</ceb:ieDate>
			<ceb:buyerIdType>1</ceb:buyerIdType>
			<ceb:buyerIdNumber>'.trim(strtoupper($data['reciver_card'])).'</ceb:buyerIdNumber>
			<ceb:buyerName>'.$data['reciver_name'].'</ceb:buyerName>
			<ceb:buyerTelephone>'.trim($data['mob_phone']).'</ceb:buyerTelephone>
			<ceb:consigneeAddress>'.$data['address'].'</ceb:consigneeAddress>
			<ceb:agentCode>'.self::USERNAME.'</ceb:agentCode>
			<ceb:agentName>'.self::SHOPNAME.'</ceb:agentName>
			<ceb:areaCode>5005610012</ceb:areaCode>
			<ceb:areaName>重庆港腾供应链管理有限公司</ceb:areaName>
			<ceb:tradeMode>1210</ceb:tradeMode>
			<ceb:trafMode>8</ceb:trafMode>
			<ceb:trafNo></ceb:trafNo>
			<ceb:voyageNo></ceb:voyageNo>
			<ceb:billNo></ceb:billNo>
			<ceb:loctNo></ceb:loctNo>
			<ceb:licenseNo></ceb:licenseNo>
			<ceb:country>'.$this->CURRENCY_CODE.'</ceb:country>
			<ceb:freight>'.$data['shipping_fee'].'</ceb:freight>
			<ceb:insuredFee>0</ceb:insuredFee>
			<ceb:currency>'.$this->CURRENCY_CODE.'</ceb:currency>
			<ceb:wrapType>1</ceb:wrapType>
			<ceb:packNo>1</ceb:packNo>
			<ceb:grossWeight>1</ceb:grossWeight>
			<ceb:netWeight>1</ceb:netWeight>
			<ceb:note></ceb:note>
			<ceb:sortlineId>'.$this->SORTLINE_ID.'</ceb:sortlineId>
			<ceb:orgCode>500400</ceb:orgCode>
		</ceb:InventoryHead>';
		 foreach($data['extend_order_goods'] as $v){
		$xml.='<ceb:InventoryList>
			<ceb:gnum>'.($v['rec_id']).'</ceb:gnum>
			<ceb:itemRecordNo>'.$v['goods_barcode'].'</ceb:itemRecordNo>
			<ceb:itemNo>'.$v['goods_sn'].'</ceb:itemNo>
			<ceb:itemName>'.$v['goods_name'].'</ceb:itemName>
			<ceb:gcode>'.$v['goods_hscode'].'</ceb:gcode>
			<ceb:gname>'.$v['gname'].'</ceb:gname>
			<ceb:gmodel>'.$v['customs_spec'].'</ceb:gmodel>
			<ceb:barCode>'.$v['goods_sn'].'</ceb:barCode>
			<ceb:country>'.$v['country_code'].'</ceb:country>
			<ceb:currency>'.$this->CURRENCY_CODE.'</ceb:currency>
			<ceb:qty>'.$v['goods_num'].'</ceb:qty>
			<ceb:unit>'.$v['unit'].'</ceb:unit>
			<ceb:qty1>'.$v['qty1'].'</ceb:qty1>
			<ceb:unit1>'.$v['unit1'].'</ceb:unit1>
			<ceb:qty2>'.$v['qty2'].'</ceb:qty2>
			<ceb:unit2>'.$v['unit2'].'</ceb:unit2>
			<ceb:price>'.$v['goods_price'].'</ceb:price>
			<ceb:totalPrice>'.floatval($v['goods_pay_price']).'</ceb:totalPrice>
			<ceb:note></ceb:note>
		</ceb:InventoryList>';
		 }
	$xml.='</ceb:Inventory>
	<ceb:BaseTransfer>
		<ceb:copCode>'.self::USERNAME.'</ceb:copCode>
		<ceb:copName>'.self::SHOPNAME.'</ceb:copName>
		<ceb:dxpMode>DXP</ceb:dxpMode>
		<ceb:dxpId>'.self::DXPID.'</ceb:dxpId>
		<ceb:note></ceb:note>
	</ceb:BaseTransfer>
</ceb:CEB621Message>
';
      return $this->_curl_post(self::APIURL,$xml);
    }
   
   /* 撤销清单
     * 
     *  
     *  */
  public  function RE_LIST_INFO($data){
	  $xml = '<?xml version="1.0" encoding="UTF-8"?>
<ceb:CEB623Message guid="'.$this->guid.'" version="1.0" xmlns:ceb="http://www.chinaport.gov.cn/ceb" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">
	<ceb:InvtCancel>
		<ceb:guid>'.$this->guid.'</ceb:guid>
		<ceb:appType>1</ceb:appType>
		<ceb:appTime>'.date('Ymdhis').'</ceb:appTime>
		<ceb:appStatus>2</ceb:appStatus>
		<ceb:customsCode>8015</ceb:customsCode>
		<ceb:orderNo>'.$data['order_sn'].'</ceb:orderNo>
		<ceb:ebpCode>'.self::USERNAME.'</ceb:ebpCode>
		<ceb:ebpName>'.self::SHOPNAME.'</ceb:ebpName>
		<ceb:ebcCode>'.self::USERNAME.'</ceb:ebcCode>
		<ceb:ebcName>'.self::SHOPNAME.'</ceb:ebcName>
		<ceb:logisticsNo>'.$data['shipping_code'].'</ceb:logisticsNo>
		<ceb:logisticsCode>5003980036</ceb:logisticsCode>
		<ceb:logisticsName>中国邮政速递物流股份有限公司重庆市分公司</ceb:logisticsName>
		<ceb:copNo>sbl'.$data['order_sn'].'</ceb:copNo>
		<ceb:preNo>'.$data['bar_code'].'</ceb:preNo>
		<ceb:invtNo>'.$data['order_sn_code'].'</ceb:invtNo>
		<ceb:buyerIdType>1</ceb:buyerIdType>
		<ceb:buyerIdNumber>'.trim($data['reciver_card']).'</ceb:buyerIdNumber>
		<ceb:buyerName>'.$data['reciver_name'].'</ceb:buyerName>
		<ceb:buyerTelephone>'.trim($data['mob_phone']).'</ceb:buyerTelephone>
		<ceb:agentCode>'.self::USERNAME.'</ceb:agentCode>
		<ceb:agentName>'.self::SHOPNAME.'</ceb:agentName>
		<ceb:reason></ceb:reason>
		<ceb:note></ceb:note>
	</ceb:InvtCancel>
	<ceb:BaseTransfer>
		<ceb:copCode>'.self::USERNAME.'</ceb:copCode>
		<ceb:copName>'.self::SHOPNAME.'</ceb:copName>
		<ceb:dxpMode>DXP</ceb:dxpMode>
		<ceb:dxpId>'.self::DXPID.'</ceb:dxpId>
		<ceb:note></ceb:note>
	</ceb:BaseTransfer>
</ceb:CEB623Message>
';
     return $this->_curl_post(self::APIURL,$xml);
    }
	
	  /* 退款 
     * 
     * 
     * 
     * */
   public function ORDER_RETURN_INFO($data){
        $xml = '<?xml version="1.0" encoding="UTF-8"?>
<ceb:CEB625Message guid="'.$this->guid.'" version="1.0"  xmlns:ceb="http://www.chinaport.gov.cn/ceb" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">
	<ceb:InvtRefund>
		<ceb:InvtRefundHead>
			<ceb:guid>'.$this->guid.'</ceb:guid>
			<ceb:appType>3</ceb:appType>
			<ceb:appTime>'.date('Ymdhis').'</ceb:appTime>
			<ceb:appStatus>1</ceb:appStatus>
			<ceb:customsCode>8015</ceb:customsCode>
		    <ceb:orderNo>'.$data['order_sn'].'</ceb:orderNo>
			<ceb:ebpCode>'.self::USERNAME.'</ceb:ebpCode>
			<ceb:ebpName>'.self::SHOPNAME.'</ceb:ebpName>
			<ceb:ebcCode>'.self::USERNAME.'</ceb:ebcCode>
			<ceb:ebcName>'.self::SHOPNAME.'</ceb:ebcName>
			<ceb:logisticsNo>'.$data['shipping_code'].'</ceb:logisticsNo>
			<ceb:logisticsCode>5003980036</ceb:logisticsCode>
			<ceb:logisticsName>中国邮政速递物流股份有限公司重庆市分公司</ceb:logisticsName>
			<ceb:copNo>sbl'.$data['order_sn'].'</ceb:copNo>
			<ceb:invtNo>'.$data['order_sn_code'].'</ceb:invtNo>
			<ceb:buyerIdType>1</ceb:buyerIdType>
			<ceb:buyerIdNumber>'.trim($data['reciver_card']).'</ceb:buyerIdNumber>
			<ceb:buyerName>'.$data['reciver_name'].'</ceb:buyerName>
			<ceb:buyerTelephone>'.trim($data['mob_phone']).'</ceb:buyerTelephone>
			<ceb:agentCode>'.self::USERNAME.'</ceb:agentCode>
			<ceb:agentName>'.self::SHOPNAME.'</ceb:agentName>
			<ceb:reason>'.$data['reason'].'</ceb:reason>
		</ceb:InvtRefundHead>';
		foreach($data['extend_order_goods'] as $v){
		$xml.='<ceb:InvtRefundList>
			<ceb:gnum>'.$v['goods_id'].'</ceb:gnum>
			<ceb:gcode>'.$v['goods_hscode'].'</ceb:gcode>
			<ceb:gname>'.$v['goods_name'].'</ceb:gname>
			<ceb:qty>'.$v['goods_num'].'</ceb:qty>
			<ceb:unit>'.$v['unit'].'</ceb:unit>
		</ceb:InvtRefundList>';
		}
	$xml.='</ceb:InvtRefund><ceb:BaseTransfer>
		<ceb:copCode>'.self::USERNAME.'</ceb:copCode>
		<ceb:copName>'.self::SHOPNAME.'</ceb:copName>
		<ceb:dxpMode>DXP</ceb:dxpMode>
		<ceb:dxpId>'.self::DXPID.'</ceb:dxpId>
		<ceb:note></ceb:note>
	</ceb:BaseTransfer>
</ceb:CEB625Message>';
      
      return $this->_curl_post(self::APIURL,$xml);
    }
	
  /**
   * 
   * 
   * */
	private function _curl_post($url,$data){
	    $data = array('data'=>base64_encode($data));
	    $data = http_build_query($data);
	    $ch = curl_init();
	    curl_setopt($ch, CURLOPT_URL, $url);
	    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	    curl_setopt($ch, CURLOPT_POST, true);
	    curl_setopt($ch, CURLOPT_HEADER, 0);
	    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
	    $response = curl_exec($ch);
	    curl_close($ch);
	    return $response;
	}
public function guid(){
    if (function_exists('com_create_guid')){
        $uuid =  com_create_guid();
    }else{
        mt_srand((double)microtime()*10000);
        $charid = strtoupper(md5(uniqid(rand(), true)));
        $hyphen = chr(45);// "-"
        $uuid = chr(123)// "{"
            .substr($charid, 0, 8).$hyphen
            .substr($charid, 8, 4).$hyphen
            .substr($charid,12, 4).$hyphen
            .substr($charid,16, 4).$hyphen
            .substr($charid,20,12)
            .chr(125);// "}"
    }
    return str_replace(array('{','}'),'',$uuid);
}
  
}