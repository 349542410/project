<?php
/*
	版本号：V1.0
	创建人：Jie
	创建日期：2017-03-10
	修改日期：2016-03-21
	用途：与  海关商品报备  对接
	包含功能：商品报备 专用
	指导文档：跨境公共平台数据接口规范V3.0（电商、支付、物流企业接口）.doc    参考 报文类型：KJ881101
 */
	require_once('GoodsCustoms.function.php'); //功能函数
	
class Custom{
	protected $Head         = array();
	protected $OrderHead    = array();
	protected $GoodsRegHead = array();
	protected $Elec_order   = array();

	function _initialize(){
		ini_set('memory_limit','4088M');
		set_time_limit(0);
		header('Content-type:text/html;charset=UTF-8');	//设置输出格式

		$kdfile 	= dirname(__FILE__).'\GoodsCustoms.conf.php';

		require_once($kdfile);//载入配置信息

		$this->Head         = $Head;			//统一用的xml报文头部
		$this->OrderHead    = $OrderHead;		//订单报文 头部
		$this->GoodsRegHead = $GoodsRegHead;	//商品报备报文 头部
		$this->Elec_order   = $Elec_order;		//订单报文 订单资料配置

	}

	/**
	 * [send 发送xml报文给海关]
	 * @param  [type] $xmlstr [按要求生成的XML原文，为String格式]
	 * @param  [type] $type   [为报文类型，KJ881111为订单报文，KJ881101为商品报备报文]
	 * @param  [type] $prefix [后台将报文与结果进行保存时的文件名前缀；订单建议使用MK单号，商品报备使用商品id]
	 * @param  [type] $CID    [CID 商品报备的时候是字符串，订单报备的时候是数组(订单原有形式数据数组)]
	 * @param  [type] $Trade  [XML报文的数组形态]
	 * @param  [type] $outside[ERP或美快后台]
	 * @return [type]         [description]
	 */
	public function send($xmlstr, $type, $prefix, $CID, $Trade=array(), $outside=false){

		require_once('GoodsCustoms.save.php'); //保存数据的类
		$SA = new \save();

    	$MessageID_2nd = $Trade['InternationalTrade']['Head']['MessageID']; //美快系统生成的

    	$GoodsRegList = $Trade['InternationalTrade']['Declaration']['GoodsRegList']; //报备商品列表 数组

		// 如果请求是 ERP 发出，则发送  商品报备 请求之前，先马上执行商品数据的 保存  jie
		if($outside === false){
			
    		$save = $SA->index($CID, $GoodsRegList, $MessageID_2nd, 'sys');// 保存到美快数据库中 mk_apply_list
			
			// “exist&success” 表示数据库已有此商品信息且报备结果是成功的了，则直接将数据库的结果返回给ERP
			if($save == 'exist&success'){
				return $backArr = array('goods_id'=>$prefix, 'result'=>true, 'msg'=>'报备成功(MG)');
			}
		}

		$GZPort = new \Org\GZP\GZPort();

		// 生成海关$xmlstr
    	$xmlstr = '<?xml version="1.0" encoding="UTF-8"?>'.$xmlstr;

    	// $rs = $GZPort->send($xmlstr,'KJ881101','ERP传来商品唯一编号');
    	$rs = $GZPort->send($xmlstr, $type, $prefix); //发送

    	// 返回成功
    	if($rs['Code'] == '1'){

			// 把报备结果状态  更新  到美快数据库中 mk_apply_list
    		$save = $SA->index('', $GoodsRegList, '', $rs);

    		//返回给ERP的结果 数组形式
    		if($outside === false){
    			
    			//把报备结果状态使用json输出{"goods_id":"ERP传来的商品唯一编号","result":"true|false"}
    			return $backArr = array('goods_id'=>$prefix, 'result'=>$rs['Data']['result'], 'msg'=>$rs['Data']['description']);

    		}else{//美快后台  数组形式

    			$rs['Data']['description'] = error_msg($rs['Data']['description']);
    			return $rs['Data'];//用于美快后台收集结果
    		}

    	}else{//美快内部 发送失败，则直接返回结果
    		return $rs;
    	}

	}

    /**
     * 商品报备报文 报关/报备
     * @param  [type]  $data    [商品信息数组]
     * @param  [type]  $outside [区分ERP或美快后台系统发送的请求，默认false，即是ERP发送请求]
     * @param  [type]  $debug   [调试模式  默认为0]
     * @return boolean       [description]
     */
    public function isGoods($data, $outside=false, $debug=0){
    	self::_initialize();

    	$Head = $this->Head;
    	$Head['MessageID'] = 'KJ881101_'.$Head['Sender'].'_'.date('YmdHis').rand(10000,99999);
    	$Head['MessageType'] = 'KJ881101';
    	$Head['FunctionCode'] = 'BC';

    	$GoodsRegHead = $this->GoodsRegHead;

    	$GoodsList = array();

    	// ERP 调用(只支持单独报备一个商品，即$order只有一个数组数据)
    	if($outside === false){

			$GoodsList[0]['Seq']            = '001';	//商品序号 固定值
			$GoodsList[0]['EntGoodsNo']     = $data['Code'];	//企业商品自编号
			$GoodsList[0]['EPortGoodsNo']   = '';	//跨境公共平台商品备案申请号   N
			$GoodsList[0]['CIQGoodsNo']     = '';	//检验检疫商品备案编号    N
			$GoodsList[0]['CusGoodsNo']     = '';	//海关正式备案编号    N
			$GoodsList[0]['EmsNo']          = '';	//账册号   N
			$GoodsList[0]['ItemNo']         = '';	//项号    N
			$GoodsList[0]['ShelfGName']     = $data['ShelfGName'];	//上架品名  在电商平台上的商品名称
			$GoodsList[0]['NcadCode']       = $data['PostTaxCode'];	//行邮税号
			$GoodsList[0]['HSCode']         = $data['HsCode'];	//HS编码
			$GoodsList[0]['BarCode']        = '';	//商品条形码    N
			$GoodsList[0]['GoodsName']      = $data['Name'];	//商品中文名称
			$GoodsList[0]['GoodsStyle']     = $data['Specifications'];	//型号规格
			$GoodsList[0]['Brand']          = $data['Brand'];	//品牌
			$GoodsList[0]['GUnit']          = unit_code($data['Unit']);//'142';	//申报计量单位
			$GoodsList[0]['StdUnit']        = unit_code($data['Unit']);	//第一法定计量单位
			$GoodsList[0]['SecUnit']        = '';	//第二法定计量单位    N
			$GoodsList[0]['RegPrice']       = $data['RefPrice'];	//单价
			$GoodsList[0]['GiftFlag']       = ($data['IsNotGift'] == true) ? '1' : '0';	//是否赠品   0-是，1-否，默认否
			$GoodsList[0]['OriginCountry']  = country_code($data['OrginCountryName']);//'502';	//原产国
			$GoodsList[0]['Quality']        = $data['Quality'];	//商品品质及说明
			$GoodsList[0]['QualityCertify'] = '';	//品质证明说明    N
			$GoodsList[0]['Manufactory']    = $data['Manufactory'];	//生产厂家或供应商
			$GoodsList[0]['NetWt']          = $data['NTWeight'];	//净重
			$GoodsList[0]['GrossWt']        = $data['GSWeight'];	//毛重
			$GoodsList[0]['Notes']          = '';	//备注    N

	    	$prefix = $data['Code'];

    	}else{//美快后台操作的时候使用

			$GoodsList[0]['Seq']            = '001';	//商品序号  固定值
			$GoodsList[0]['EntGoodsNo']     = $data['EntGoodsNo'];	//企业商品自编号
			$GoodsList[0]['EPortGoodsNo']   = $data['EPortGoodsNo'];	//跨境公共平台商品备案申请号   N
			$GoodsList[0]['CIQGoodsNo']     = $data['CIQGoodsNo'];	//检验检疫商品备案编号    N
			$GoodsList[0]['CusGoodsNo']     = $data['CusGoodsNo'];	//海关正式备案编号    N
			$GoodsList[0]['EmsNo']          = $data['EmsNo'];	//账册号   N
			$GoodsList[0]['ItemNo']         = $data['ItemNo'];	//项号    N
			$GoodsList[0]['ShelfGName']     = $data['ShelfGName'];	//上架品名  在电商平台上的商品名称
			$GoodsList[0]['NcadCode']       = $data['NcadCode'];	//行邮税号
			$GoodsList[0]['HSCode']         = $data['HSCode'];	//HS编码
			$GoodsList[0]['BarCode']        = $data['BarCode'];	//商品条形码    N
			$GoodsList[0]['GoodsName']      = $data['GoodsName'];	//商品中文名称
			$GoodsList[0]['GoodsStyle']     = $data['GoodsStyle'];	//型号规格
			$GoodsList[0]['Brand']          = $data['Brand'];	//品牌
			$GoodsList[0]['GUnit']          = $data['GUnit'];	//申报计量单位
			$GoodsList[0]['StdUnit']        = $data['StdUnit'];	//第一法定计量单位
			$GoodsList[0]['SecUnit']        = $data['SecUnit'];	//第二法定计量单位    N
			$GoodsList[0]['RegPrice']       = $data['RegPrice'];	//单价
			$GoodsList[0]['GiftFlag']       = $data['GiftFlag'];	//是否赠品   0-是，1-否，默认否
			$GoodsList[0]['OriginCountry']  = $data['OriginCountry'];	//原产国
			$GoodsList[0]['Quality']        = $data['Quality'];	//商品品质及说明
			$GoodsList[0]['QualityCertify'] = $data['QualityCertify'];	//品质证明说明    N
			$GoodsList[0]['Manufactory']    = $data['Manufactory'];	//生产厂家或供应商
			$GoodsList[0]['NetWt']          = $data['NetWt'];	//净重
			$GoodsList[0]['GrossWt']        = $data['GrossWt'];	//毛重
			$GoodsList[0]['Notes']          = $data['Notes'];	//备注    N

			$prefix = $data['EntGoodsNo'];
    	}

    	// 以下是为拼接成完整的xml结构而组成的数组
    	$Declaration = array();
    	$Declaration['GoodsRegHead'] = $GoodsRegHead;
    	$Declaration['GoodsRegList'] = $GoodsList;

    	$Trade = array();
    	$Trade['InternationalTrade']['Head'] = $Head;
    	$Trade['InternationalTrade']['Declaration'] = $Declaration;
    	$xmlstr = arrayToXml($Trade, 'GoodsContent');//转换成xml报文

    	if($debug == 1) return $Trade;  //开启调试的时候，返回调试数据

    	$res = $this->send($xmlstr, 'KJ881101', $prefix, $data['CID'], $Trade, $outside);
    	return $res;

    }

    /**
     * 订单报文 报关/报备
     * @param  [type]  $data [订单数组]
     * @return boolean       [description]
     */
    public function isOrder($data, $Head, $OrderHead, $Elec_order, $GZPort){

    	$order = $data['Order']; //商品列表

		$Head['MessageID']    = 'KJ881111_'.$Head['Sender'].'_'.date('YmdHis').rand(10000,99999);
		$Head['MessageType']  = 'KJ881111';
		$Head['FunctionCode'] = 'BOTH';

		$rate = 1+floatval($Elec_order['rate']);
		
		//税款 = (总金额/1+税率)*税率
		$tax = sprintf("%.2f",(floatval($data['price'])/floatval($rate))*floatval($Elec_order['rate']));//计算税金

    	$OrderDetail = array();
		$OrderDetail['EntOrderNo']             = $data['MKNO'];	//企业电子订单编号
		$OrderDetail['OrderStatus']            = $Elec_order['OrderStatus'];	//电子订单状态
		$OrderDetail['PayStatus']              = $Elec_order['PayStatus'];	//支付状态
		$OrderDetail['OrderGoodTotal']         = $data['price'];	//订单商品总额
		$OrderDetail['OrderGoodTotalCurr']     = coin_code($data['coin']);	//订单商品总额币制
		$OrderDetail['Freight']                = $Elec_order['freight'];	//订单运费
		$OrderDetail['Tax']                    = $tax;	//税款
		$OrderDetail['OtherPayment']           = $data['discount'];	//抵付金额  优惠减免金额，无则填“0”
		$OrderDetail['OtherPayNotes']          = (floatval($data['discount']) > 0) ? $Elec_order['OtherPayNotes'] : '';	//抵付说明抵付情况说明。如果填写抵付金额时，此项必填。
		
		$OrderDetail['ActualAmountPaid']       = sprintf("%.2f",floatval($data['price'])+floatval($tax));	//实际支付金额
		$OrderDetail['RecipientName']          = $data['receiver'];	//收货人名称
		$OrderDetail['RecipientAddr']          = $data['reAddr'];	//收货人地址
		$OrderDetail['RecipientTel']           = $data['reTel'];	//收货人电话
		$OrderDetail['RecipientCountry']       = $Elec_order['RecipientCountry'];	//收货人所在国
		$OrderDetail['RecipientProvincesCode'] = $Elec_order['RecipientProvincesCode'];	//收货人收货人行政区代码  进口需要填收货人所在行政区域代码 出口可空
		$OrderDetail['OrderDocAcount']         = $data['reTel'];	//下单人账户
		$OrderDetail['OrderDocName']           = $data['receiver'];	//下单人姓名
		$OrderDetail['OrderDocType']           = '01';	//下单人证件类型   01:身份证、02:护照、04:其他 固定值01
		$OrderDetail['OrderDocId']             = $data['idno'];		//下单人证件号
		$OrderDetail['OrderDocTel']            = $data['reTel'];	//下单人电话

		$OrderDate = ($data['paytime'] == '') ? $data['optime'] : $data['paytime'];
		$OrderDetail['OrderDate']              = date('YmdHis',strtotime($OrderDate));	//订单日期

		$OrderDetail['OtherCharges']           = '';	//其它费用   N
		$OrderDetail['BatchNumbers']           = '';	//商品批次号   N
		$OrderDetail['InvoiceType']            = '';	//发票类型     N
		$OrderDetail['InvoiceNo']              = '';	//发票编号	   N
		$OrderDetail['InvoiceTitle']           = '';	//发票抬头     N
		$OrderDetail['InvoiceIdentifyID']      = '';	//纳税人标识号 N
		$OrderDetail['InvoiceDesc']            = '';	//发票内容		N
		$OrderDetail['InvoiceAmount']          = '0';	//发票金额		N  必须填个0才能通过

		$InvoiceDate = ($data['paytime'] == '') ? $data['optime'] : $data['paytime'];
		$OrderDetail['InvoiceDate']            = date('Y-m-d\TH:i:s\Z',strtotime($InvoiceDate));	//开票日期		N
		$OrderDetail['Notes']                  = $data['notes'];	//备注  N

    	$OrderGoodsList = array();
		foreach($order as $key=>$v){
			$OrderGoodsList[$key]['Seq']           = $key+1;	//商品序号
			$OrderGoodsList[$key]['EntGoodsNo']    = $v['auto_Indent2'];	//企业商品自编号
			$OrderGoodsList[$key]['GoodsDescribe'] = '';	//企业商品描述  N
			$OrderGoodsList[$key]['CIQGoodsNo']    = $Elec_order['CIQGoodsNo'];	//检验检疫商品备案编号
			$OrderGoodsList[$key]['CusGoodsNo']    = $v['hgid'];	//海关正式备案编号
			$OrderGoodsList[$key]['HSCode']        = $v['hs_code'];	//HS编码
			$OrderGoodsList[$key]['GoodsName']     = $v['detail'];	//商品名称
			$OrderGoodsList[$key]['GoodsStyle']    = $v['specifications'];	//规格型号
			$OrderGoodsList[$key]['OriginCountry'] = country_code($v['source_area']);	//原产国
			$OrderGoodsList[$key]['BarCode']       = '';			//商品条形码   N
			$OrderGoodsList[$key]['Brand']         = $v['brand'];	//品牌  N
			$OrderGoodsList[$key]['Qty']           = $v['number'];	//数量
			$OrderGoodsList[$key]['Unit']          = unit_code($v['unit']);	//计量单位
			$OrderGoodsList[$key]['Price']         = $v['price'];	//单价
			$OrderGoodsList[$key]['Total']         = sprintf("%.2f",floatval($v['number'])*floatval($v['price']));	//总价
			$OrderGoodsList[$key]['CurrCode']      = coin_code($v['coin']);	//币制
			$OrderGoodsList[$key]['Notes']         = $v['remark'];	//备注  N
		}

		// 以下是为拼接成完整的xml结构而组成的数组
		$OrderDetail['GoodsList'] = $OrderGoodsList;

		$OrderList = array();
		$OrderList['OrderContent']['OrderDetail'] = $OrderDetail;

    	$Declaration = array();
    	$Declaration['OrderHead'] = $OrderHead;
    	$Declaration['OrderList'] = $OrderList;

    	$Trade = array();
    	$Trade['InternationalTrade']['Head'] = $Head;
    	$Trade['InternationalTrade']['Declaration'] = $Declaration;
    	// End
    	
    	$xmlstr = arrayToXml($Trade, 'OrderGoodsList');
    	// dump($Trade);die;
   // return $Trade;
    	$res = $this->orderSend($xmlstr, 'KJ881111', $data['MKNO'], $GZPort, $data, true);
    	return $res;
    }

    /**
     * 订单报文 报关/报备 发送方法
	 * @param  [type] $xmlstr [按要求生成的XML原文，为String格式]
	 * @param  [type] $type   [为报文类型，KJ881111为订单报文，KJ881101为商品报备报文]
	 * @param  [type] $prefix [后台将报文与结果进行保存时的文件名前缀；订单建议使用MK单号，商品报备使用商品id]
	 * @param  [type] $GZPort [发送方法的类]
	 * @param  [type] $outside[ERP或美快后台]
     * @return boolean       [description]
     */
    public function orderSend($xmlstr, $type, $prefix, $GZPort, $data, $outside=false){
		require_once('GoodsCustoms.save.php'); //保存数据的类
		$SA = new \save();

		// 生成海关$xmlstr
    	$xmlstr = '<?xml version="1.0" encoding="UTF-8"?>'.$xmlstr;

    	// $rs = $GZPort->send($xmlstr,'KJ881101','ERP传来商品唯一编号');
    	$rs = $GZPort->send($xmlstr, $type, $prefix); //发送

    	// 返回成功
    	if($rs['Code'] == '1'){

    		$rs['Data']['description'] = error_msg($rs['Data']['description']);
    		// return $rs;
    		$save = $SA->order_save($prefix, $rs, $data); //$prefix为MKNO ，根据MKNO更新数据状态

    		//返回给ERP的结果 数组形式
    		if($outside === false){
    			
    			//把报备结果状态使用json输出{"goods_id":"ERP传来的商品唯一编号","result":"true|false"}
    			return $backArr = array('goods_id'=>$prefix, 'result'=>$rs['Data']['result'], 'msg'=>$rs['Data']['description']);

    		}else{//美快后台  数组形式
    			
    			return $rs['Data'];//用于美快后台收集结果
    		}

    	}else{//美快内部 发送失败，则直接返回结果
    		return $rs;
    	}
    }
}