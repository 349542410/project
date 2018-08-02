<?php
namespace AUApi\Controller\KdnoConfig;
/*
	版本号：V1.0
	创建人：Jie
	创建日期：2018-05-11
	修改日期：2018-05-11
	用途：1.与 卓志 对接下单
	指导文档：物流对接汇总/2018-05-17 美快卓志对接/卓志速运_进口业务_标准接口.1.0.1.doc
 */
require_once('Kdno22.function.php'); //功能函数
class Kdno22{

	protected $_requestData;			//请求的数据
    protected $_response;				//请求结果
    protected $_responseData;			//请求结果中的PDFStream字段
    protected $_funcy;
	protected $data_arr = array();
	protected $STEXT        = '';		// 返回快递其他内容
	protected $no           = '';		// 返回快递号码 mailno
	protected $config       = array();
	protected $Order_config = array();

	function _initialize()
	{
		ini_set('memory_limit','500M');
		set_time_limit(0);
		header('Content-type:text/html;charset=UTF-8');	//设置输出格式
		// libxml_disable_entity_loader(false);
		require_once('Kdno22.conf.php');	//载入配置信息

		$this->config              = $config;
		$this->Order_config        = $Order_config;
		$this->_version            = $config['Version'];
		// dump($this->Order_config);die;
		// foreach ($config as $name => $value) $this->$name = $value;
	}

	// 上传订单
	public function data($data){
		self::_initialize();
		$order = $data['Order'];
// dump($this->config);die;
		//商品列表
		$detail = array();

		// 内件详情
		foreach($order as $key=>$v){
			$detail[$key]['code']                = $v['hgid'];		//商品货号     Y
			$detail[$key]['isCiq']               = '1';		//是否商品备案，1=是,0=否	Y
			$detail[$key]['hsCode']              = $v['hs_code'];		//HS编号	Y
			$detail[$key]['name']                = $v['detail'];		//物品名称   Y
			$detail[$key]['num']                 = $v['number'];		//商品数量	Y
			$detail[$key]['value']               = $v['price'];		//单价，单位：元	Y
			$detail[$key]['currency']            = $v['coin'];		//价格，货币单位	Y
			$detail[$key]['brand']               = $v['brand'];		//品牌	Y
			$detail[$key]['spec']                = $v['specifications'];		//规格型号，如120粒/瓶	Y
			$detail[$key]['unit']                = $v['unit'];		//计量单位：如瓶，个，件等	Y
			$detail[$key]['grossWeight']         = $v['weight'];		//单件的毛重，单位KG，如：1.2	Y
			$detail[$key]['netWeight']           = $v['att5'];		//单件的净重，单位KG，如：1.1	Y
			$detail[$key]['country']             = $v['source_area'];		//原产国	Y
			$detail[$key]['additive']            = '0';		//（可选）超范围使用添加剂（0=无，1=有）	Y
			$detail[$key]['toxicStuff']          = '0';		//含有毒害物质（0=无，1=有）	Y
			$detail[$key]['quality']             = '1';		//商品品质（1=合格，0=不合格）	Y
			$detail[$key]['assemCountry']        = $v['source_area'];		// 国检原产地 新增	Y
			$detail[$key]['unitFirst']           = $v['unit'];		// 第一法定计量单位 如：011 或 件，请见3.1计量单位，为确保数据准确，建议值为三位码（三位数字的编码值）	Y
			$detail[$key]['unitSecond']          = '';		// 第二法定计量单位 新增	Y
			$detail[$key]['numFirst']            = $v['number'];		// 第一法定单位数量 新增	Y
			$detail[$key]['numSecond']           = '1';		// 第二法定单位数量 新增	Y
			
		}

		$totalPrice = sprintf("%.2f", ($data['price']+$this->Order_config['freight']+$this->Order_config['insuredFee']+$this->Order_config['taxFcy']-$this->Order_config['discount']));
		//生成 物流格式数据
		$log = array(
			'siteCode' => $this->config['sitecode'],//仓库标识，由系统分配   Y
			'order'    => array(
				'code'               => $data['MKNO'],//业务编号，一个业务编号只能绑定一个物流面单 	Y
				'productCode'        => $this->Order_config['productCode'],//产品代码，分配物流公司单号使用	Y
				'declareType'        => $this->Order_config['declareType'],//(可选)申报类型  Y
				'goodsPriceCurrency' => $this->Order_config['goodsPriceCurrency'],//订单货款金额币制，BC必填，默认142：人民币  Y
				'freight'            => $this->Order_config['freight'],//订单运费，无运费=0  Y
				'freightCurrency'    => $this->Order_config['freightCurrency'],//运费币制，BC必填，默认142：人民币  Y
				'insuredFee'         => $this->Order_config['insuredFee'],//订单保费，无保费=0  Y
				'insuredCurrency'    => $this->Order_config['insuredCurrency'],//保费币制，BC必填，默认142：人民币  Y
				'taxFcy'             => $this->Order_config['taxFcy'],//订单税费，无税费=0  Y
				'taxCurrency'        => $this->Order_config['taxCurrency'],//税费币制，BC必填，默认142：人民币  Y
				'discount'           => $this->Order_config['discount'],//优惠减免金额，无优惠=0  Y
				'discountCurrency'   => $this->Order_config['discountCurrency'],//优惠减免金额币制，BC必填，默认142：人民币  Y
				'payCurrency'        => $this->Order_config['payCurrency'],//支付币制-货币单位：CNY    Y
				'changeSingle'       => $this->Order_config['changeSingle'],//换单标志，0：不需要、1：需要    Y
				'tradeModels'        => $this->Order_config['tradeModels'],//贸易模式1：跨境模式 （默认）2：一般贸易    Y
				'goodsPrice'         => $data['price'],//订单货款金额，商品实际成交价不包括优惠减免  Y
				'totalPrice'         => $totalPrice,//实际支付金额  ∑商品单价*数量+总运费+总保费+总税费-优惠减免金额，与支付保持一致    Y
				'orderDate'          => $data['paytime'],//订单生成时间，日期格式：yyyy-mm-dd HH:mi:ss  Y
				'payTransactionId'   => $data['payno'],//支付流水号，BC必填  Y
				'payCode'            => $data['paykind'],//支付企业编码，BC必填  Y
				'grossWeight'        => $data['weight'],//包裹毛重，单位KG，BC必填，必须大于商品净重之和    Y
				'remark'             => $data['notes'],//面单备注  Y
			),
			// 订购人信息
			'buyer' => array(
				'name'     =>$data['receiver'],//订购人姓名    Y
				'phone'    =>$data['reTel'],//订购人电话    Y
				'regNo'    =>$data['buyers_nickname'],//订购人注册账号    Y
				'idType'   =>$this->Order_config['buyer_idType'],//值为：1=身份证，0=其他    Y
				'idNumber' =>$data['idno'],//证件号码    Y
			),
			//发件人信息
			'sender' => array(
				'name'    => $data['sender'],//发件人姓名    Y
				'phone'   => $data['sendTel'],//发件人电话 Y
				'country' => $this->Order_config['sender_country'],//发件人国家【溯源必填】   Y
				'address' => $data['sendAddr'],//发件人地址   Y
			),
			//收件人信息
			'receiver' => array(
				'name'     => $data['receiver'],//substr_cut($data['receiver']),//姓名 Y
				'phone'    => $data['reTel'],//收件人电话
				'country'  => $this->Order_config['receiver_country'],//收件人国家
				'province' => $data['province'],//省份
				'city'     => $data['city'],//城市 Y
				'district' => $data['town'],//区/县 Y
				'address'  => $data['reAddr'],//地址 Y
				'idNumber' => $data['idno'],//收件人证件号码（身份证）  Y
			),

			'goods' => $detail,//订单明细列表	Y
		);

		$data_arr['RequestOrder'] = $log;

		$this->xml = arrayToXml($data_arr);//转换成xml报文


		$this->_funcy = 'CEB_ORDER_CREATE';
		
		$this->_setRequest();

		$res = $this->getResponseData();//请求结果

		$content = "\r\n\r\n-------- res --------\r\n\r\n".$res."\r\n\r\n";

		file_put_contents($this->config['xmlsave'].$this->filename, $content, FILE_APPEND);

		$arr = xmlToArray($res);

		if(isset($arr) && is_array($arr)){
			
			if($arr['result']['status'] != 'success'){
				$this->STEXT = array('ErrorStr'=>'卓志反馈：'.$arr['result']['reason']);//20171019 Jie

			}else{

				$txt = $arr['order'];

				// 返回快递其它内容
				$this->STEXT = array(
					'destcode'   => (isset($txt['Mark'])) ? $txt['Mark'] : '',// 20171019 jie
					'mailno'     => (isset($txt['traceCode'])) ? $txt['traceCode'] : '',// 卓志运单号  分配到的跟踪号码
					'origincode' => (isset($txt['originCode'])) ? $txt['originCode'] : '',//
					'orderid'    => $data['MKNO'],
					'custid'     => (isset($txt['ParcelId'])) ? $txt['ParcelId'] : '', // 创建成功的包裹编号 20171019 jie
					'traceCode'  => (isset($txt['logisticsCode'])) ? $txt['logisticsCode'] : '',//中通单号  物流公司单号
					'title'      => (isset($txt['title'])) ? $txt['title'] : '',
					'package'    => (isset($txt['package'])) ? $txt['package'] : '',
				);
				// print_r($this->STEXT);die;
				$this->no = (isset($txt['traceCode'])) ? $txt['traceCode'] : '';	// 卓志运单号  分配到的跟踪号码 mailno
			}

			return $this->getResponseData();// 把返回的原始xml直接返回给ERP系统
		}else{
			$this->STEXT = array('ErrorStr'=>'卓志反馈：该订单下单无反馈');
		}

	}

	// 未完
	public function get_logistics(){
		//生成 物流格式数据
		$log = array(
			'siteCode' => $this->config['sitecode'],//仓库标识，由系统分配   Y
			'orderBy' => $this->config['orderBy'],//仓库标识，由系统分配   Y
			//发件人信息
			'order' => array(
				$data['sender'],//发件人姓名    Y
			),

		);

		$data_arr['RequestOrder'] = $log;
	}




//===============================================================

    /**
     * 接口response部分
     */
    public function getResponse()
    {
        return $this->_response;
    }

    public function getResponseData()
    {
        return $this->_responseData;
    }

	// 安全及数据完整性
	public function _setRequest()
	{
		$data_digest = base64_encode(strtolower(md5($this->_funcy.$this->xml.time().$this->config['key'])));

		$this->_requestData['logistics_interface'] = $this->xml;
		$this->_requestData['data_digest']         = $data_digest;
		$this->_requestData['timestamp']           = time();
		$this->_requestData['cuscode']             = $this->config['cuscode'];
		$this->_requestData['msg_type']            = $this->_funcy;

		$this->post();
	}

	// 发送数据
	public function post()
	{

		$MK = new \Org\MK\HTTP();
		$this->_responseData = $MK->post($this->config['pmsLoginAction'], $this->_requestData, 1200);
		// dump($this->_responseData);die;
		
		// 是否输出txt文件 20171208 jie
		if($this->config['exports_switch'] === true){

			if(!is_dir($this->config['xmlsave'])) mkdir($this->config['xmlsave'], 0777, true);//第三个参数设置true 设置递归模式自动创建必需的目录文件夹

			$file_name = 'Kdno22_'.$this->_funcy.'_'.date('Ymd').'.txt';	//文件名

			$this->filename = $file_name;

			$content = "===================== ".date('Y-m-d H:i:s')." =====================\r\n\r\n-------- RequestData --------\r\n\r\n".$this->xml."\r\n\r\n-------- ResponseData --------\r\n\r\n".$this->_responseData."\r\n\r\n";

			if(is_file($file_name)){
				file_put_contents($this->config['xmlsave'].$file_name, $content);
			}else{
				file_put_contents($this->config['xmlsave'].$file_name, $content, FILE_APPEND);
			}
		}

	}
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