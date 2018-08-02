<?php
namespace AUApi\Controller\KdnoConfig;
/*
	版本号：V1.0
	创建人：Jie
	创建日期：2018-06-09
	修改日期：2018-06-09
	用途：1.香港E特快 对接下单
	指导文档：物流对接汇总/2017-12-01 香港E特快/eexpressAPI.PDF
 */
require_once('Kdno11.function.php'); //功能函数
class Kdno11{
	protected $STEXT        = '';		// 返回快递其他内容
	protected $no           = '';		// 返回快递号码 mailno
	protected $config       = array();
	protected $Order_config = array();
	protected $userToken    = 'C5A2262097C1F283F6D659DA7CF6923C';//只要获取一次即可，无需重复获取

	function _initialize(){
		ini_set('memory_limit','500M');
		set_time_limit(0);
		header('Content-type:text/html;charset=UTF-8');	//设置输出格式

		require_once('Kdno11.conf.php');	//载入配置信息

		$this->config       = $config;
		$this->Order_config = $Order_config;

		if($this->userToken == ''){
			die('尚未设置或获取token指令');
			// $this->userToken = $this->eExpress_Login();
		}

	}

	public function data($data){
		return $this->eExpress_shipment_import_label_zpl($data);
	}

	// 身份校验  返回 userToKEN
	public function eExpress_Login(){
		self::_initialize();
		$login_arr = array('loginID' =>$this->config['partnerName'], 'pwd'=>$this->config['checkword']);

		$res = $this->useSoap($login_arr, $this->config, __FUNCTION__);

		$arr = json_decode(json_encode($res),TRUE);
		$xml = $arr['eExpress_LoginResult']['any'];

		// 用截取的方式获取需要的数据
		// $str = substr($xml, strpos($xml, '<result>'));
		// $str = substr($str, 0, strpos($str, '</Table>'));

		$arr_d = xml_to_array($xml);

		$table = $arr_d['NewDataSet']['Table'];

		if(isset($table)){
			if($table['result'] == 'T'){
				return $table['msg'];
			}else{
				return false;
			}
		}else{
			return false;
		}

	}

	// 包裹提交且返回ZPL打印指令
	public function eExpress_shipment_import_label_zpl($data){
		self::_initialize();
		$order = $data['Order'];

		//商品列表
		$detail = array();

		$this->data = $data;
		// 货物信息列表
		foreach($order as $key=>$v){
			$detail[$key]['itemDescription'] = $v['detail'];		//商品详细描述信息	Y
			$detail[$key]['hsCode']          = $v['hs_code'];		//商品税号 如果是DDP，税号必填且必须有效 （参考引用数据）
			$detail[$key]['itemPrice']       = $v['price'];			//商品申报价格  Y
			$detail[$key]['itemPieces']      = $v['number'];		//商品件数  Y
			$detail[$key]['itemWeight']      = num_to_change(0.454 * floatval($v['weight']));		//商品重量，单位KG  Y
		}

		//生成 香港E特快 物流格式数据
		$log = array(
			'awb' => array(
				'userToken'    => $this->userToken, //该参数来自于方法1返回的user token   Y
				'customerHawb' => $data['MKNO'], //客户自己的相关号码，如订单号
				'shipmentDate' => date('Y-m-d',strtotime($data['paytime'])),//发货日期(格式yyyy-MM-dd)   Y
				'rName'        => $data['receiver'],//收件人姓名   Y
				'rCountry'     => $this->Order_config['ReceiverCountryCode'],//收件人所属国家，默认值（CN）   Y
				'rProvince'    => $data['province'],//收件人所属省份（参考引用数据）   Y
				'rAddress1'    => $data['reAddr'],//收件人地址1   Y
				'rAddress2'    => '',//收件人地址2
				'rCity'        => $data['city'],//收件人城市（参考引用数据）   Y
				'rZip'         => $data['postcode'],//收件人邮编（参考引用数据）   Y
				'rTel'         => $data['reTel'],//收件人电话，必须是11位大陆手机号码。如果产生税金，系统会发送缴纳税金通知至该手机号   Y
				'Pieces'       => $this->Order_config['Pieces'],//默认1   Y
				'weight'       => num_to_change(0.454 * floatval($data['weight'])),//包裹重量，不超过10KG   Y
				'dCurrency'    => $data['coin'],//申报货币，（USD,CNY,HKD,EUR）   Y
				'dValue'       => ($data['premium'] == 0) ? $this->Order_config['dValue'] : $data['premium'],//申报价值   Y
				'duty_paid'    => $this->Order_config['duty_paid'],//税金已付（Y/N，若不填写默认“N”）   Y
			),
			'objAwbDetail' => $detail,// 货物信息列表
		);
		// return $log;
		$res = $this->useSoap($log, $this->config, __FUNCTION__);

		$res = json_decode(json_encode($res),TRUE);

		// $arr = xml_to_array($res);
		$txt = $res['eExpress_shipment_import_label_zplResult'];

		if(is_array($txt) && isset($txt)){

			if($txt['Result'] === false){
				$this->STEXT = array('ErrorStr'=>'EMS反馈：'.$txt['Message']);//20171019 Jie

			}else{

				// 返回快递其它内容
				$this->STEXT = array(
					'destcode'   => (isset($txt['Mark'])) ? $txt['Mark'] : '',// 20171019 jie
					'mailno'     => (isset($txt['ShipmentNumber'])) ? $txt['ShipmentNumber'] : '',// 分配到的跟踪号码 20171019 jie
					'origincode' => (isset($txt['originCode'])) ? $txt['originCode'] : '',
					'orderid'    => $data['MKNO'],
					'custid'     => (isset($txt['ParcelId'])) ? $txt['ParcelId'] : '', // 创建成功的包裹编号 20171019 jie
					'zpl'        => (isset($txt['ZplShipmentLabel'])) ? base64_encode($txt['ZplShipmentLabel']) : '', // 打印编码
				);
				// print_r($this->STEXT);die;
				$this->no = (isset($txt['ShipmentNumber'])) ? $txt['ShipmentNumber'] : '';	// 分配到的跟踪号码 mailno
			}

			return $result;// 把返回的原始json直接返回给ERP系统
		}else{
			$this->STEXT = array('ErrorStr'=>'EMS反馈：该订单下单无反馈');
		}
	}

	// 获取包裹标签 ZPL/PDF文件   未完
	public function eExpress_getLabel($data)
	{

		$array = array(
			'Token'				=> $this->userToken,
			'ShipmentNumber'	=> 'EK246471559HK',//$data['STNO'],
			'LabelType'			=> 'Zpl',
		);

		$res = $this->useSoap($array, $this->config, __FUNCTION__);
		var_dump($res);
	}

	// 包裹状态追踪  未完
	public function eExpress_shipment_tracking($tracking_number, $config)
	{

		$array = array(
			'Token'				=> $this->userToken,
			'shipment_number'	=> $tracking_number,//'EL025731329HK',
		);

		$res = $this->useSoap($array, $config, __FUNCTION__);
		var_dump($res);//暂时未收到有效的返回结果
	}
//======================================================================
	/**
	 * 用soap 发送请求
	 * @param  [type] $data        [base64加密之后的报文]
	 * @param  [type] $validateStr [校验码]
	 * @return [type]              [description]
	 */
	public function useSoap($data, $config, $funcy){

		$soap = new \SoapClient($config['pmsLoginAction']);//网络服务请求地址

		$result = $soap->$funcy($data);//查询，返回的是一个结构体

		// 非身份校验，则生成日志
		if($funcy != 'eExpress_Login'){
			// 是否输出txt文件 20171208 jie
			if($config['exports_switch'] === true){

				if(!is_dir($config['xmlsave'])) mkdir($config['xmlsave'], 0777, true);//第三个参数设置true 设置递归模式自动创建必需的目录文件夹

				$file_name = 'Kdno11_'.date('Ymd').'.txt';	//文件名

				$content = "\r\n\r\n=================== ".date('Y-m-d H:i:s')."===================\r\n\r\n-------- Original Data --------\r\n\r\n".json_encode($this->data)." --------\r\n\r\n-------- Request --------\r\n\r\n".json_encode($data)."\r\n\r\n-------- Response --------\r\n\r\n".json_encode($result)."\r\n\r\n";

				if(is_file($file_name)){

					file_put_contents($config['xmlsave'].$file_name, $content);
				}else{
					file_put_contents($config['xmlsave'].$file_name, $content, FILE_APPEND);
				}
			}
		}

		return $result;
	}

//===============================================================

	/**
	 * 返回快递其他内容 20160907 jie
	 * @return [type] [description]
	 */
	public function get(){
		if($this->STEXT != ''){
			return base64_encode((json_encode($this->STEXT)));
		}else{
			return $stext = '';
		}
	}

	/**
	 * 返回快递号码 mailno  20160907 jie
	 * @return [type] [description]
	 */
	public function no(){
		return $this->no;//直接返回mailno
	}


































}