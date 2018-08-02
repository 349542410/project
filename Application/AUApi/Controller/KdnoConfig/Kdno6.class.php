<?php
namespace AUApi\Controller\KdnoConfig;
/*
	版本号：V1.0
	创建人：Jie
	创建日期：2016-10-10
	修改日期：2016-10-11
	用途：与香港E特快下单对接
	指导文档：物流对接汇总/2016-12-22 香港E特快/香港E特快Userapi_1.2.pdf
 */
	require_once('Kdno6.function.php'); //功能函数
class Kdno6{

	protected $STEXT        = '';		// 20161223 jie 返回快递其他内容
	protected $no           = '';		// 20161223 jie 返回快递号码 mailno
	protected 	$config     = array();
	protected $Order_config = array();

	function _initialize(){
		ini_set('memory_limit','4088M');
		set_time_limit(0);
		header('Content-type:text/html;charset=UTF-8');	//设置输出格式

		$kdfile 	= dirname(__FILE__).'\Kdno6.conf.php'; 
		// print_r($kdfile);die;
		require_once($kdfile);//载入配置信息

		$this->config       = $config;//方便全局调用
		$this->Order_config = $Order_config;//方便全局调用
	}

	/**
	 * 订单上传
	 * @param [type] $data [description]
	 */
	function addOrUpdateOrder($data){
		self::_initialize();
		// print_r($data);die;
		// print_r($this->config);
		// print_r($this->Order_config);
		// die;

		$order   = $data['Order'];
		$Customs = $data['Customs'];
		
		//1.处理成 EMS订单格式的数据
		$log = array(
			'ReferenceId'          => $data['auto_Indent2']."_".$data['auto_Indent1'],//$data['MKNO'],		//参考编号
			'ShipwayCode'          => $this->config['ShipwayCode'],	//运 送 方 式 编 码 ， 可 以 通 过listShipwayCodes 接口获取
			'SenderName'           => $data['sender'],		//发件人姓名
			'SenderCountryCode'    => $this->Order_config['SenderCountryCode'],					//发件人国家二字编码
			'SenderState'          => $this->Order_config['SenderState'],      			//发件人省份
			'SenderCity'           => $this->Order_config['SenderCity'],					//发件人城市
			'SenderStreet'         => $this->Order_config['SenderStreet'],					//发件人地址
			'SenderPhone'          => $data['sendTel'],		//发件人电话
			'SenderPostCode'       => $data['sendcode'],	//发件人邮编
			'ReceiverName'         => $data['receiver'],		//收件人名
			'ReceiverAddressLine1' => $data['reAddr'],		//收件人地址第一行
			'ReceiverAddressLine2' => '',		//收件人地址第二行 默认空 20170110 jie
			'ReceiverCity'         => $data['city'],		//收件人城市
			'ReceiverState'        => $data['province'],		//收件人省份
			'ReceiverCountryCode'  => $this->Order_config['ReceiverCountryCode'],		//收件人国家二字编码，中国为 CN
			'ReceiverPhoneNumber'  => $data['reTel'],		//收件人电话
			'ReceiverPostCode'     => $data['postcode'],		//收件人邮编
			'ReceiverEmail'        => $data['email'],		//收件人 email
			'ReceiverIdNo'         => '',//$data['idno'],		//收件人身份证号码  20170119 jie 改为空
			'Weight'               => $data['weight'],		//包裹重量
			'DutyPaid'             => $this->Order_config['DutyPaid'],		//是否做关税预付
			'IsCarTransportation'  => $this->Order_config['IsCarTransportation'],		//是否全程陆运
			'Remark'               => $data['notes'],		//备注信息

		);

		//2.处理成 EMS 海关申报产品列表
		$detail = array();
		$cuts   = array();

		foreach($order as $key=>$v){
			$detail[$key]['Quantity']          = $v['number'];	//申报数量
			$detail[$key]['DescriptionEn']     = '';			//英文描述
			$detail[$key]['DescriptionCn']     = $v['detail'];	//中文描述
			$detail[$key]['UnitWeight']        = $v['weight'];	//申报重量
			$detail[$key]['UnitValue']         = sprintf("%.2f", $v['price']);	//申报价值
			$detail[$key]['HsCode']            = (trim($v['hs_code']) != '') ? $v['hs_code'] : '';			//海关编码
			$detail[$key]['TaxCode']           = $v['hgid'];	//税号
			$detail[$key]['Brand']             = '';//$v['brand'];	//品牌 如 polo
			$detail[$key]['Specifications']    = '';//($v['unit'] != '') ? $v['unit'] : '件';//规格 如 件 盒
			$detail[$key]['OriginCountryCode'] = '';//change_code($v['source_area']);	//生产国家二字编码

			//3.处理成 EMS 配货信息列表
			$cuts[$key]['Title']    = $v['detail'];		//配货信息内容
			$cuts[$key]['Sku']      = $v['barcode'];	//配货信息 SKU
			$cuts[$key]['Quantity'] = $v['number'];		//配货信息数量
		}

		$log['OrderItemList'] = $cuts;

		$log['OrderCustoms']['Currency'] = $this->config['Currency'];
		$log['OrderCustoms']['CustomsType'] = $this->config['CustomsType'];
		$log['OrderCustoms']['CustomsItemList'] = $detail;

		$list = array();
		$list['ApiToken'] = $this->config['ApiToken'];
		$list['OrderList'][] = $log;
		
		$json = json_encode($list);

		$res = $this->common($json, $this->config['Url'], __FUNCTION__);

		$res_arr = json_decode($res,true);

		// 是否输出txt文件 20170109 jie
		if($this->config['exports_switch'] === true){

			$file_name = 'Kdno6_'.$data['auto_Indent2']."_".$data['auto_Indent1'].'_'.time().'.txt';	//文件名

			$content = "======== Request =========\r\n\r\n====== data =======\r\n".$json."\r\n\r\n======== Response =========\r\n\r\n".$res;

			if(!is_dir($this->config['xmlsave'])) mkdir($this->config['xmlsave'], 0777, true);//第三个参数设置true 设置递归模式自动创建必需的目录文件夹

			//echo 
			file_put_contents($this->config['xmlsave'].$file_name, $content);
		}
		
		// echo '<pre>';
		// print_r($list);die;

		$txt = $res_arr['Result'][0];
		// 此用于查看
		if($txt['Status'] == 'fail'){
			$this->STEXT = array('ErrorStr'=>'EMS反馈：'.$txt['Error']);//20161020 Jie
		
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

		return $res;//把EMS返回的原始json直接返回给ERP系统
	}

	/**
	 * 删除订单
	 * @param  [type] $data [description]
	 * @return [type]       [description]
	 */
	public function deleteOrder($data){
		self::_initialize();

		$list = array();
		$list['ApiToken'] = $this->config['ApiToken'];	// 口令环
		$list['OrderList'][]['ReferenceId'] = $data['MKNO'];	// OrderList 包裹列表

		$json = json_encode($list);
		$res = $this->common($json, $this->config['Url'], __FUNCTION__);
		return $res;
	}

	/**
	 * 跟踪包裹(物流信息)  用EMS单号(STNO)查询 20170110 jie
	 * 此方法被 HkEms 文件夹里面的rount.php调用
	 * @param  [type] $data          [STNO 香港E特快运单号]
	 * @param  [type] $config        [香港E特快的必需的配置信息]
	 */
	public function trackOrder($data, $config=array()){

		$list = array();
		$list['ApiToken'] = $config['ApiToken'];	// 口令环
		$list['TrackingNos'][] = $data;	// OrderList 包裹列表
		$json = json_encode($list);
		// print_r($config['Url']);die;
		$res = $this->common($json, $config['Url'], __FUNCTION__);
// $res = '{
//     "Result":[
//         {
//             "TrackingNo":"'.$data.'",
//             "CurrentStatusCode":"delivered",
//             "LocalCurrentDealtime":"2016-05-11 00:00:00",
//             "LocalCurrentAction":"Delivered.",
//             "LocalCurrentLocation":"",
//             "DestCurrentDealtime":"2016-05-11 11:59:24",
//             "DestCurrentAction":"投递并签收，签收人：本人收",
//             "DestCurrentLocation":"苏州市",
//             "LocalTraces":[
//                 {
//                     "DealTime":"2016-05-05 09:40:50",
//                     "Action":"Shipment Created",
//                     "Location":"HongKong"
//                 },
//                 {
//                     "DealTime":"2016-05-06 08:38:10",
//                     "Action":"shipment exported by agent",
//                     "Location":""
//                 },
//                 {
//                     "DealTime":"2016-05-06 16:56:16",
//                     "Action":"Shipment inscanned in warehouse",
//                     "Location":""
//                 },
//                 {
//                     "DealTime":"2016-05-07 00:00:00",
//                     "Action":"The item left Hong Kong for its destination on 7-May-2016",
//                     "Location":""
//                 },
//                 {
//                     "DealTime":"2016-05-07 11:26:36",
//                     "Action":"HK Customs clearance",
//                     "Location":""
//                 },
//                 {
//                     "DealTime":"2016-05-07 11:26:51",
//                     "Action":"Shipment data submitted to HKPOST",
//                     "Location":""
//                 },
//                 {
//                     "DealTime":"2016-05-07 11:35:00",
//                     "Action":"Shipment accepted by hkpost",
//                     "Location":""
//                 },
//                 {
//                     "DealTime":"2016-05-07 11:58:00",
//                     "Action":"Shipment despatch by hkpost",
//                     "Location":""
//                 },
//                 {
//                     "DealTime":"2016-05-08 00:00:00",
//                     "Action":"In transit.",
//                     "Location":""
//                 },
//                 {
//                     "DealTime":"2016-05-08 00:00:00",
//                     "Action":"Arrived and is being processed.",
//                     "Location":""
//                 },
//                 {
//                     "DealTime":"2016-05-11 00:00:00",
//                     "Action":"Delivered.",
//                     "Location":""
//                 }
//             ],
//             "DestTraces":[
//                 {
//                     "DealTime":"2016-05-11 11:59:24",
//                     "Action":"投递并签收，签收人：本人收",
//                     "Location":"苏州市"
//                 }
//             ]
//         }
//     ],
//     "Status":"success",
//     "ErrorMessage":""
// }';
		return $res;
	}

	/**
	 * 查询清关状态
	 */
	public function getTaxStatus($data, $config=array()){

		$list = array();
		$list['ApiToken'] = $config['ApiToken'];	// 口令环
		$list['TrackingNo'] = $data;	// OrderList 包裹列表
		$json = json_encode($list);
		$res = $this->common($json, $config['Url'], __FUNCTION__);
		return $res;
	}

	/**
	 * 查询运送方式编码
	 */
	public function listShipwayCodes(){
		self::_initialize();

		$list = array();
		$list['ApiToken'] = $this->config['ApiToken'];	// 口令环
		$json = json_encode($list);
		print_r($json);die;
		$res = $this->common($json, $this->config['Url'], __FUNCTION__);
		return $res;
	}

	/**
	 * 预报订单
     * 思路：讲收到的批次号id和其他必要的数据
     * 1.先进行数据检验，还有时间日期的验证；
     * 2.根据批次号id，搜索出mk_tran_list中符合的所有数据，将这些数据 组装成ShipmentNumbers必需的数组形式，然后把其他必要的数据和ShipmentNumbers数组传入
     * 	  Kdno6.class.php类中，调用reportOrder()方法；
     * 3. Kdno6类 讲这些传入的数据组合成数组并json格式化，再将此json发送到请求地址，收到反馈原路返回结果；如果反馈的结果为成功，则更新此批次号id对应的字段send_report
     * 为1(表示已经执行预报订单操作)；
     * 4.在此，反馈给前端页面之前，需要对反馈信息进行加工或改进处理再进行返回
	 * @param  [type] $data    [包裹列表]
	 * @param  [type] $number  [空运提单号码或者交货车辆号码]
	 * @param  [type] $re_time [预计到达时间]
	 * @param  [type] $country [起运国国家二字编码]
	 * @return [type]          [description]
	 */
	public function reportOrder($data, $number, $re_time, $country){
		self::_initialize();
		// return $data;
		
		$list = array();
		$list['ApiToken']            = $this->config['ApiToken'];	// 口令环
		$list['WayBillNumber']       = $number;		//空运提单号码或者交货车辆号码
		$list['ETA']                 = $re_time;	//预计到达时间
		$list['ShipFromCountryCode'] = $country;	//起运国国家二字编码
		$list['ShipmentNumbers']     = $data;		//包裹列表
		$json = json_encode($list);

		$res = $this->common($json, $this->config['Url'], __FUNCTION__);


// $res = '{
// "Status": true,
// "ErrorMessage": ""
// }';

		$res_arr = json_decode($res,true);
		if($res_arr['Status'] == 'true'){
			if(trim($res_arr['ErrorMessage']) == '') $res_arr['ErrorMessage'] = '操作成功，预报总数：'.count($data);
		}else{
			if(trim($res_arr['ErrorMessage']) == '') $res_arr['ErrorMessage'] = '操作失败，预报总数：'.count($data);
		}

		$res = json_encode($res_arr);
		return $res;
	}
	
	/**
	 * 传入客人基本资料以返回物流信息 20161227 jie
	 * @param  [type] $data [客人基本资料]
	 * @return [type]       [description]
	 */
	public function data($list){
		return $this->addOrUpdateOrder($list);
	}

//=======================
	/**
	 * curl post发送请求
	 * @param  [type] $json  [description]
	 * @param  [type] $url   [description]
	 * @param  string $funcy [description]
	 * @return [type]        [description]
	 */
	public function common($json, $url, $funcy=''){

		$url .= $funcy;//请求地址拼接完整
		// print_r($url);die;
		$res = sendXML($url,$json);

		return $res;
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