<?php
/*
	版本号：V1.0
	创建人：Jie
	创建日期：2017-03-10
	修改日期：2017-03-21
	用途：与广东邮政(跨境易)对接  (EMS商品报备)
	指导文档：BC直购4.0对接接口说明文档(2016-12-20).docx     参考 4.系统商品备案(EGoodsRecord)
 */
	require_once('Kdno7.function.php'); //功能函数
class Kdno{

	protected $STEXT        = '';		// 20161223 jie 返回快递其他内容
	protected $no           = '';		// 20161223 jie 返回快递号码 mailno
	protected $config       = array();
	protected $Order_config = array();


	function _initialize(){
		ini_set('memory_limit','4088M');
		set_time_limit(0);
		header('Content-type:text/html;charset=UTF-8');	//设置输出格式

		$kdfile 	= dirname(__FILE__).'\Kdno7.conf.php';

		require_once($kdfile);//载入配置信息

		$this->config       = $config;//方便全局调用
		$this->Order_config = $Order_config;//方便全局调用

	}

	// 订单发送  未完成
	public function AddInChinaOrder($data){

		self::_initialize();

		$order   = $data['Order'];
		
		//1.处理成 EMS订单格式的数据
		$log = array(
			'GUID'                 => "",//$data['auto_Indent2']."_".$data['auto_Indent1'],//海关报文GUID 可空，需要订单代发时必填且唯一,订单报文的GUID值,有固定格式
			'OrderCode'            => $data['MKNO'], 	//订单号 必填、唯一
			'ExpressNo'            => '',				//运单号 根据配置选填、填则唯一,不填留空
			'CustomCode'           => $this->Order_config['CustomCode'],			//申报关区代码  必填、4位代码值(默认:5145)
			'TotalWeight'          => $data['weight'],								//订单毛重 必填、该订单对应包裹毛重,单位固定为kg
			'Tax'                  => $this->Order_config['Tax'],					//税费 必填、订单对应产生的税费(可填0)
			'OrderCurrency'        => $this->Order_config['OrderCurrency'],			//核算币制  必填、核算币制中文名(订单内取用的统一币制)
			'Freight'              => $this->Order_config['Freight'],				//运费 必填、可填0
			'Insurance'            => $this->Order_config['Insurance'],				//保费 必填、可填0
			'Note'                 => $data['notes'],								//备注  可空
			
			'ReceiveCode'          => $data['idno'],		//收件人证件号 必填,可与PayerIdNumber报同一值
			'ReceiveName'          => $data['receiver'],	//收件人姓名 必填
			'ReceiveProvince'      => $data['province'],	//收件人省份  必填、省市必须匹配
			'ReceiveCity'          => $data['city'],		//收件人城市名  必填、省市必须匹配
			'ReceiveAddress'       => $data['reAddr'],		//收件人详细地址 必填、必须包含所填省市
			'ReceivePhone'         => $data['reTel'],		//收件人电话  必填
			
			'SenderName'           => $data['sender'],		//发件人名  必填(海外发件人名)
			'SenderPhone'          => $data['sendTel'],		//发件人电话  必填(海外电话)
			'SenderCountry'        => $this->Order_config['SenderCountry'],					//起运国  必填(海外国家中文名)
			'SenderCity'           => $this->Order_config['SenderCity'],					//起运城市  必填(海外城市中文名)
			'SenderAddress'        => $this->Order_config['SenderStreet'],					//发件人地址  必填(海外地址)
			
			'DeductionAmount'      => $this->Order_config['DeductionAmount'],	//抵扣金额  必填 可为0
			'DeductionNote'        => $this->Order_config['DeductionNote'],		//抵扣金额说明  必填 抵扣金额为0时，填”无”
			'OtherCharge'          => $this->Order_config['OtherCharge'],		//其他费用  必填 可为0
			
			'OrderDocAcount'       => $data['buyers_nickname'],	//购买者账户名  必填 消费者在电商网站的用户名
			'PaymentEnterprise'    => $data['paykind'],			//支付企业名称  可空 支付企业的企业名称
			'PaymentTransactionNo' => $data['payno'],			//支付交易编号  可空 支付交易流水号
			'PaymentEntNo'         => $this->Order_config['PaymentEntNo'],						//支付企业编号  可空 广州关:支付企业海关编号 总署:单一窗口编码
			'ActualAmountPaid'     => sprintf("%.2f", $data['price']),			//实际支付金额  必填 实际支付金额(按海关金额算法校验)
			'PayerName'            => $data['receiver'],		//订购人名称  必填 订购人名称
			'PayerIdNumber'        => $data['idno'],			//订购人证件号  必填 订购人证件号
			'PayerIdType'          => $this->Order_config['PayerIdType'],						//订购人证件类型  必填 01身份证02护照04其它
			'PayerTel'             => $data['reTel'],						//订购人电话  必填 订购人电话
			'OrderDate'            => date('Y-m-d\TH:i:s',strtotime($data['paytime'])),			//订单日期  必填 消费者网站下单时间；订单日期小于支付时间
			'PayTime'              => date('Y-m-d\TH:i:s',strtotime($data['paytime'])+5),		//支付时间  可空 消费者实际完成支付时间；支付时间大于订单时间
		);

		//2.处理成 EMS 海关申报产品列表
		$detail = array();
		foreach($order as $key=>$v){
			$detail[$key]['RowNumber']  = $key+1;	//商品序号 int(11) 必填(必填,请务必按照订单报文明细的报送顺序,从1开始连续递增填报)
			$detail[$key]['RecordCode'] = $v['hgid'];	//商品货号  必填(电商在KJY系统上的备案货号)
			$detail[$key]['Count']      = $v['number'];	//商品数量  必填
			$detail[$key]['Price']      = sprintf("%.2f", $v['price']);	//商品价格  必填、价格的币制取用订单币制，且所有商品只能使用统一币制
			$detail[$key]['Note']       = '';	//备注  可空
		}

		$log['Items'] = $detail;

		$result = $this->useSoap($log, $this->config, __FUNCTION__);

		print_r($result);
		die;

		//测试
		$result = array('IsSuccess'=>true, 'Message'=>'推送成功');
		print_r($result);die;

		// 是否输出txt文件 20170109 jie
		if($this->config['exports_switch'] === true){

			$file_name = 'Kdno7_'.$data['auto_Indent2']."_".$data['auto_Indent1'].'_'.time().'.txt';	//文件名

			$content = "======== Request =========\r\n\r\n".json_encode($log)."\r\n\r\n======== Response =========\r\n\r\n".$result;

			if(!is_dir($this->config['xmlsave'])) mkdir($this->config['xmlsave'], 0777, true);//第三个参数设置true 设置递归模式自动创建必需的目录文件夹

			file_put_contents($this->config['xmlsave'].$file_name, $content);
		}

		$txt = $res;
		// 此用于查看
		if($txt['IsSuccess'] == 'false'){
			$this->STEXT = array('ErrorStr'=>'广东邮政反馈：'.$txt['Message']);//20161020 Jie
		
		}else{
			
			// 返回快递其它内容
			$this->STEXT = array(
				'destcode'   => (isset($txt['AreaCode'])) ? $txt['AreaCode'] : '',// 20161228 jie
				'mailno'     => (isset($txt['TrackingNo'])) ? $txt['TrackingNo'] : '',// 分配到的跟踪号码 20161228 jie
				'origincode' => (isset($txt['originCode'])) ? $txt['originCode'] : '',
				'orderid'    => $data['MKNO'],
				'custid'     => (isset($txt['ParcelId'])) ? $txt['ParcelId'] : '', // 创建成功的包裹编号 20161228 jie
			);
			// print_r($this->STEXT);die;
			$this->no = (isset($txt['TrackingNo'])) ? $txt['TrackingNo'] : '';	// 分配到的跟踪号码 mailno
		}

		return $res;//把返回的原始json直接返回给ERP系统

	}

	/**
	 * 提单绑定(发货通知)
	 * @param [type] $data    [description]
	 * @param [type] $number  [空运提单号码]
	 * @param [type] $re_time [预计到港时间]
	 * @param [type] $country [起运港]
	 */
	public function UpLoadTotalRelation($data, $number, $re_time, $country, $tol_weight){

		self::_initialize();

		$log = array();
		$log['TotalExpressNo']  = $number;	//空运提单号  必填、唯一
		$log['CustomCode']      = $this->Order_config['CustomCode'];	//申报口岸  必填 申报口岸海关代码
		$log['CheckCount']      = count($data);	//包裹数量  必填 提单下包裹数
		$log['ExpressNoList']   = $data;;	//子运单号集合  必填 提单绑定的 运单号集合
		$log['TotalWeight']     = $tol_weight;	//提单重量  必填 航空公司提单表上的总毛重,单位固定为kg
		$log['ExpectDate']      = date('Y-m-d\TH:i:s',$re_time);		//预计到港时间  必填 预估抵达申报口岸的时间
		$log['DestinationPort'] = $country;		//起运港  必填、起运港中文名称
		$log['TrayCount']       = count($data);	//托盘数/袋数  必填、具体的托盘或袋数量
		$log['TrafName']        = $number;		//航班号/车牌号  必填、依据提单类型填写航班号或车牌号

		$res = $this->useSoap($log, $this->config, __FUNCTION__);
		$res = object_array($res);
		$res_arr = $res['UpLoadTotalRelationResult'];

		// //测试
		// $res_arr = array('IsSuccess'=>true, 'Message'=>'推送成功');

		if($res_arr['IsSuccess'] === true){
			if(trim($res_arr['Message']) == '') $res_arr['Message'] = '操作成功，预报总数：'.count($data);
		}else{
			if(trim($res_arr['Message']) == '') $res_arr['Message'] = '操作失败，预报总数：'.count($data);
		}

		// $res = json_encode($res);
		return $res_arr;

	}

	/**
	 * 商品系统备案
	 * @param [type]  $data    [商品数据]
	 * @param boolean $outside [判断区分ERP或美快后台的请求]
	 */
	public function ApplyGoodsRecord($data, $outside=false){

		self::_initialize();

		// ERP
		if($outside === false){

			$log = array(
				'Code'             => $data['Code'], //商品货号 varchar(40) 必填、全域唯一、由字母或数字组成，不能包含标点符号或其他,不与其他电商重复
				'Name'             => $data['Name'], //商品名称 varchar(40) 必填 商品的名称
				'Specifications'   => $data['Specifications'], //规格型号 varchar(200) 必填 商品的规格型号信息
				'PostTaxCode'      => $data['PostTaxCode'], //行邮税号 varchar(20) 必填 该商品的行邮税则码
				'OrginCountryName' => $data['OrginCountryName'], //原产国名称 varchar(100) 必填、商品的原产地国家名称
				'NTWeight'         => $data['NTWeight'], //净重 decimal(18,4) 必填、商品的净重,单位固定为kg
				'RefPrice'         => $data['RefPrice'], //参考价格 decimal(18,4) 必填、商品在商城销售的价格(可为0)
				'Unit'             => $data['Unit'], //申报单位 varchar(100) 必填、商品的申报计量单位
				'Currency'         => $data['Currency'], //参考币制 varchar(30) 必填、参考价格的币制(中文名称)
				'HsCode'           => $data['HsCode'], //海关HS商品编码 varchar(10) 必填、10位海关商品编码(纯数字)
				'CIQTypeCode'      => $data['CIQTypeCode'], //国检商品分类编码 varchar(30) 可空 填写时校验正确性;留空则匹配默认国检商品分类编码
				'ShelfGName'       => $data['ShelfGName'], //上架品名 varchar(200) 必填、商品在电商网站的上架品名
				'Brand'            => $data['Brand'], //品牌 varchar(100) 必填、商品的品牌
				'IsNotGift'        => $data['IsNotGift'], //非赠品 bool 必填、(false-是赠品 true-非赠品)
				'Quality'          => $data['Quality'], //商品品质 varchar(100) 必填、商品的品质,如:"合格"、"优良"等
				'Manufactory'      => $data['Manufactory'], //生产厂家或供应商 varchar(200) 必填、商品的生产厂家或供应商
				'GSWeight'         => $data['GSWeight'], //毛重(KG) decimal(18,4) 必填、商品的毛重;毛重大于等于净重
				'CiqGoodsNo'       => $data['CiqGoodsNo'], //商品国检备案号 varchar(60) 可空 商品的国检备案号(自主备案的可自行填写)
			);

			$no = $data['Code'];
		}else{//美快后台系统
			$log = array(
				'Code'             => $data['EntGoodsNo'], //商品货号 varchar(40) 必填、全域唯一、由字母或数字组成，不能包含标点符号或其他,不与其他电商重复
				'Name'             => $data['GoodsName'], //商品名称 varchar(40) 必填 商品的名称
				'Specifications'   => $data['GoodsStyle'], //规格型号 varchar(200) 必填 商品的规格型号信息
				'PostTaxCode'      => $data['NcadCode'], //行邮税号 varchar(20) 必填 该商品的行邮税则码
				'OrginCountryName' => country_code($data['OriginCountry']), //原产国名称 varchar(100) 必填、商品的原产地国家名称
				'NTWeight'         => $data['NetWt'], //净重 decimal(18,4) 必填、商品的净重,单位固定为kg
				'RefPrice'         => $data['RegPrice'], //参考价格 decimal(18,4) 必填、商品在商城销售的价格(可为0)
				'Unit'             => unit_code($data['GUnit']), //申报单位 varchar(100) 必填、商品的申报计量单位
				'Currency'         => '人民币', //参考币制 varchar(30) 必填、参考价格的币制(中文名称)
				'HsCode'           => $data['HSCode'], //海关HS商品编码 varchar(10) 必填、10位海关商品编码(纯数字)
				'CIQTypeCode'      => '', //国检商品分类编码 varchar(30) 可空 填写时校验正确性;留空则匹配默认国检商品分类编码
				'ShelfGName'       => $data['ShelfGName'], //上架品名 varchar(200) 必填、商品在电商网站的上架品名
				'Brand'            => $data['Brand'], //品牌 varchar(100) 必填、商品的品牌
				'IsNotGift'        => $data['GiftFlag'], //非赠品 bool 必填、(false-是赠品 true-非赠品)
				'Quality'          => $data['Quality'], //商品品质 varchar(100) 必填、商品的品质,如:"合格"、"优良"等
				'Manufactory'      => $data['Manufactory'], //生产厂家或供应商 varchar(200) 必填、商品的生产厂家或供应商
				'GSWeight'         => $data['GrossWt'], //毛重(KG) decimal(18,4) 必填、商品的毛重;毛重大于等于净重
				'CiqGoodsNo'       => '', //商品国检备案号 varchar(60) 可空 商品的国检备案号(自主备案的可自行填写)
			);

			$no = $data['id'];
		}

		$result = $this->useSoap($log, $this->config, __FUNCTION__);

		$res = object_array($result);
		$res_arr = $res['ApplyGoodsRecordResult'];
		/*//测试
		$res_arr = array('IsSuccess'=>true, 'Message'=>'申报成功');
		// return $res_arr;*/

		require_once('Kdno7.save.php'); //保存数据的类
		$SA = new \save();
		
		$save = $SA->index($no, $res_arr, $outside);//保存数据

		//如果保存数据操作方面存在问题，则返回的是有关保存的错误提示
		if(isset($save['IsSuccess'])){
			$backArr = $save;//返回保存错误的提示信息
		}else{
			$backArr = $res_arr;//返回报备请求返回的结果
		}

		return $backArr;//数组形式返回
		
	}

	/**
	 * 推送支付信息  暂时无法测试
	 * @param [type] $arr    [订单数据  一维数组]
	 * @param [type] $config [Kdno7的默认配置信息]
	 */
	public function SendPaymentInfo($arr, $config){

        if(preg_match("/^支付宝/", $arr['paykind'])){
            $type = 'ali';

        }else if(preg_match("/^微信支付/", $arr['paykind'])){
            $type = 'wx';

        }else if(preg_match("/^美快支付/", $arr['paykind'])){
        	$type = 'yl';

        }else{//默认值
        	$type= 'yl';
        }

		$tolPrice = sprintf("%.2f", $arr['price']);
		$TaxAmount = sprintf("%.2f", (floatval($tolPrice)*$config['rate'])/(1+$config['rate']));

		$cname = array( //yl必填
			'RealName'        => $arr['receiver'],	 //持卡人真实姓名
			'CredentialsType' => '证件类型',	//（详细参考说明参数	$CredentialsType）   证件类型
			'CredentialsNo'   => $arr['idno'],	//证件号码
		);

		$order = $arr['Order'];

		//存在wx微信子订单号 必填    20170330 jie 暂时是不需要用到
		$sub_order = array();
		if(in_array($type, array('wx'))){
			foreach($order as $key=>$item){
				$sub_order[$key]['sub_order_no']  = $item['auto_Indent2']; 	//子订单号
				$sub_order[$key]['order_fee']     = sprintf("%.2f",(floatval($item['price']) * intval($item['number']) + floatval($config['freight'])));	//子订单应付金额
				$sub_order[$key]['transport_fee'] = $config['freight'];	//物流费
				$sub_order[$key]['product_fee']   = sprintf("%.2f",(floatval($item['price']) * intval($item['number'])));	//商品价格
			}
		}
		
		// 订单交易（支付）日期
		$paytime = ($arr['paytime'] == '') ? $arr['optime'] : $arr['paytime'];

		$data = array(
			'type'            => $type, 	//必填，ALI,WX,YL, 统一使用小写
			'type_operation'  => 'submit',	//必填   固定为submit

			// 待处理
			'customs'         => (in_array($type, array('ali','yl'))) ? '海关' : 'GUANGZHOU',	//(详细看说明参数	customs) //ali yl必填
			'portcode'        => (in_array($type, array('yl'))) ? '口岸代码' : '',	 //yl必填   口岸代码
			// End

			'cname'           => (in_array($type, array('yl'))) ? $cname : '',  //yl必填
			'sub_order'       => (in_array($type, array('wx'))) ? $sub_order : '',  //存在wx微信子订单号必填

			'order'           => array( 		//必填
				'out_trade_no'    => $arr['MKNO'], //必填  商家订单号
				'transaction_id'  => (in_array($type, array('ali','wx'))) ? $arr['payno'] : '', //ali wx  必填   支付订单号
				
				'ShoppingDate'    => date('YmdTHis',strtotime($paytime)),		//(格式：YYYYMMDD)', 必填  订单交易（支付）日期 
				
				'order_amount'    => (in_array($type, array('ali','yl'))) ? $tolPrice : '',	//ali yl 必填  订单实际支付金额
				'TaxAmount'       => (in_array($type, array('wx','yl'))) ? $TaxAmount : '',	//wx/yl必填 【= (order_amount*0.119/1.119),config中取0.119,1.119】  关税/税款

				'GoodsAmount'     => (in_array($type, array('yl'))) ? sprintf("%.2f",(floatval($tolPrice) - floatval($TaxAmount))) : '',	//yl  商品价格 【= order_amount - TaxAmount】
				'Freight'         => (in_array($type, array('yl'))) ? '0' : '0',	//yl必填,暂时固定为0   运费
				'InsuredFee'      => (in_array($type, array('yl'))) ? '0' : '0',	//yl必填,暂时固定为0   保费
			),
		);

		// return $data;

/*		$Customs = new \Org\GZP\SendCustoms();
		$res = $Customs->send($data);
		*/

		// // 模拟
		$res = array(
			'code'  => '1',
			'err'   => '当code=0时显示出错原因',//（即报关接口的'错误代码'+'错误代码描述'）
			'state' => '1',
		);

		require_once('Kdno7.save.php'); //保存数据的类
		$SA = new \save();
		
		// 1为操作成功，则进行数据保存
		if($res['code'] == '1'){
			//保存数据
			$save = $SA->save_pay($res, $arr['MKNO'], $type);
		}

		return $res;

	}

	/**
	 * 面单打印
	 * @param [type] $data [订单数据]
	 */
	public function GetByOrderCode($data){
		self::_initialize();

		$log = array(
			'CustomerCode'     => $this->Order_config['CustomerCode'], //大客户代码 varchar(30) 电商在KJY里一般的大客户代码
			'CountryName'      => '', //原寄地 varchar(50) 原寄件地的国家中文名称
			'WaybillNum'       => $data['STNO'], //运单号 varchar(20) 订单对应的EMS的包裹单号
			'Sender'           => $data['sender'], //寄件人 varchar(60) 海外寄件人名称，通常是英文名称
			'SendAddress'      => $data['sendAddr'], //寄件人地址 varchar(300) 海外寄件人地址，通常是英文名称
			'SendPhone'        => $data['sendTel'], //寄件人电话 varchar(30) 海外寄件人的电话(或移动电话号)
			'Receiver'         => $data['receiver'], //收件人 varchar(30) 收件人名称，通常是中文
			'ReceiveAddress'   => $data['reAddr'], //收件人地址 varchar(300) 收件人的国内收件地址
			'ReceivePhone'     => $data['reTel'], //收件人电话 varchar(30) 收件人的电话号码(或手机号)
			'ReceiveZip'       => $data['postcode'], //收件人邮编 varchar(6) 收件人的国内邮编
			'OrderValue'       => $data['price'], //订单价值 decimal(18,4)
			'Detail'           => '', //内件描述 varchar(6)
			'OrderCode'        => $data['MKNO'], //关联单号(订单号) varchar(50) 商家的订单号
			'RealWeight'       => $data['weight'], //实际重量 decimal(18,4) 包裹的实际重量，单位为千克
			'BulkWeight'       => $data['weight'], //体积重量 decimal(18,4) 包裹的体积重量，单位为千克
			'TotalPrice'       => $data['premium'], //申报价值 decimal(18,4) 订单的总价值，单位人民币
			'ApplySeaPort'     => $this->Order_config['ApplySeaPort'], //进口口岸 varchar(30) 申报海关口岸(中文名称)
			'OrginCountryName' => '', //原产国 varchar(30) 商品原产地或寄件地
		);

		$result = $this->useSoap($log, $this->config, __FUNCTION__);
		print_r($result);
	}

	/**
	 * 物流追踪
	 * @param [type] $data [description]
	 */
	public function GetExpressTrack($data, $config=array()){

		// $data = 'BE972032890HK';
		$result = $this->useSoap($data, $config, __FUNCTION__);

		$res = object_array($result);

		$res_arr = $res['GetExpressTrackResult'];

		return $res_arr;
	}

//=================返回特定的两个变量========================

	/**
	 * 调用soap发送
	 * @param  [type] $data   [数组数据]
	 * @param  [type] $config [配置信息]
	 * @param  [type] $funcy  [方法名]
	 * @return [type]         [description]
	 */
	public function useSoap($data, $config, $funcy){

		$Order = array(
			'userName'  => $config['userName'],
			'passWord'  => $config['passWord'],
			'key'       => $config['key'],
		);

		// 面单打印、EMS物流追踪  使用此url
		if(in_array($funcy, array('GetByOrderCode','GetExpressTrack'))){

			$url = $config['OtherUrl'];

			if($funcy == 'GetByOrderCode') $Order['OrderCode'] = $data;//面单打印

			if($funcy == 'GetExpressTrack') $Order['ExpressNo'] = $data;//EMS物流追踪

		}else if(in_array($funcy, array('AddInChinaOrder','UpLoadTotalRelation','SendPaymentInfo','ApplyGoodsRecord'))){

			$url = $config['pmsLoginAction'];

			if($funcy == 'AddInChinaOrder') $Order['order'] = $data;//订单发送

			if($funcy == 'ApplyGoodsRecord') $Order['goods'] = $data;//商品系统备案

			if($funcy == 'UpLoadTotalRelation') $Order['tr'] = $data;//提单绑定

			if($funcy == 'SendPaymentInfo') $Order['payment'] = $data;//推送支付信息
		}

		$soap = new \SoapClient($url);//网络服务请求地址
// echo ("SOAP服务器提供的开放函数:");
// echo ('<pre>');
// var_dump ( $soap->__getFunctions () );//获取服务器上提供的方法
// echo ('</pre>');
		return $soap->$funcy($Order);//查询，返回的是一个结构体

	}

	/**
	 * 返回快递其他内容 20161223 jie
	 * @return [type] [description]
	 */
	public function get(){
		if($this->STEXT != ''){
			$stext = $this->STEXT;
			return base64_encode((json_encode($stext)));
		}else{
			return $stext = '';
		}
	}

	/**
	 * 返回快递号码 mailno  20161223 jie
	 * @return [type] [description]
	 */
	public function no(){
		return $this->no;//直接返回mailno
	}


}