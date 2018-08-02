<?php
namespace AUApi\Controller\KdnoConfig;
/*
	版本号：V1.0
	创建人：Jie
	创建日期：2018-05-11
	修改日期：2017-05-11
	用途：1.与贝海 对接下单
	指导文档：物流对接汇总/2018-05-11 美快贝海对接/贝海openapi对接文档20180508.pdf
 */
require_once('Kdno21.function.php'); //功能函数
class Kdno21{

	protected $_requestData;			//请求的数据
    protected $_response;				//请求结果
    protected $_responseData;			//请求结果中的PDFStream字段
    protected $_funcy;
	// protected $data_arr = array();
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
		require_once('Kdno21.conf.php');	//载入配置信息

		$this->config              = $config;
		$this->Order_config        = $Order_config;
		$this->_version            = $config['Version'];
		// dump($this->Order_config);die;
		// foreach ($config as $name => $value) $this->$name = $value;
	}

	public function data($data){
		$this->importorder($data);//先 导入订单
	}

	// 电商企业做单
	public function createNo($data){
		// self::_initialize();
		$order = $data['Order'];

		//商品列表
		$detail = array();

		// 内件详情
		foreach($order as $key=>$v){
			//注意：Skucode不为空时，CategoryId、CategoryVersion、UnitPrice、OriginalInternalCount、ProductName、Brand、Model、Specification、Size可为空
			$detail[$key]['SkuCode'] = $v['barcode'];		//商品编码
			$detail[$key]['Count']   = $v['number'];		//购买数量	Y
			if($v['barcode'] == ''){

				$detail[$key]['CategoryId']      = $v['CategoryId'];		//分类Id	Y
				$detail[$key]['CategoryVersion'] = date('Y-m-d H:i:s');		//分类最新版本时间,格式为（yyyy-MM-dd HH:mm:ss）  Y
				$detail[$key]['UnitPrice']       = sprintf("%.2f", $v['price']);//购买单价，精确到2位小数，单位元,如100.25,表示100.25元	Y
				$detail[$key]['ProductName']     = $v['detail'];		//品名	Y
				$detail[$key]['Brand']           = $v['brand'];		//品牌	Y
			}
			
		}

		//生成 物流格式数据
		$this->data_arr = array(
			'BusinessNo'      => $data['MKNO'],//业务编号，一个业务编号只能绑定一个物流面单 	Y
			'Weight'          => $data['weight'],//毛重（千克） 包裹实际重量  重量需要在分类允许的重量范围内	Y
			'Comment'         => $data['notes'],//面单备注  Y
			'LogisticId'      => $this->Order_config['LogisticId'],//货站 ID 
			'LogisticVersion' => date('Y-m-d H:i:s'),//货站最新版本时间,格式为（yyyy-MM-dd HH:mm:ss）  Y
			'LineTypeId'      => $this->Order_config['LineTypeId'],//线路类型Id   1-个人快件 3-电商快件 9-奶粉专线  Y

			// 渠道信息   **注意：电商快件必填**
			'BillSupplyInfo' => array(
				'OrderCode'   =>$data['MKNO'],//渠道订单号
				'TradingNo'   =>$data['payno'],//订单支付单号
				'ChannelName' =>$this->Order_config['ChannelName'],//渠道名称
			),
			//发件人信息
			'BillSenderInfo' => array(
				'Name'    => $data['sender'],//substr_cut($data['sender']),//发件人姓名 Y
				'Address' => $data['sendAddr'],//地址 Y
				'Phone'   => $data['sendTel'],//hidtel($data['sendTel']),//移动电话 Y
			),
			//收件人信息
			'BillReceiverInfo' => array(
				// 只显示收件人的省市区，其余用 * 隐藏
				'Name'     => $data['receiver'],//substr_cut($data['receiver']),//姓名 Y
				'Province' => $data['province'],//省份
				'City'     => $data['city'],//城市 Y
				'District' => $data['town'],//区/县 Y
				'Address'  => $data['reAddr'],//地址 Y
				'Phone'    => $data['reTel'],//hidtel($data['reTel']),//移动电话 Y
				'IdCode'   => $data['idno'],//身份证号码  Y
			),

			'BillCategoryList' => $detail,//订单明细列表	Y
		);

		$this->_funcy = 'xlobo.labels.createNoVerification';

		$this->_setRequest();

		$res = $this->getResponseData();//请求结果
		// dump($res);die;

		$txt = json_decode($res, true);
		// dump($txt);die;

		if(isset($txt) && is_array($txt)){
			
			if($txt['ErrorCount'] == 1){
				$this->STEXT = array('ErrorStr'=>'贝海反馈：'.$txt['Message']);//20171019 Jie
			
			}else{
				// sleep(1.5);
				// $this->labels($txt['Result']['BillCode']);//最后，获取PDF打印码
				// $labels_json = $this->getResponseData();//请求结果
				// $labels_arr = json_decode($labels_json, true);

				// if($labels_arr['ErrorCount'] == 0){
				// 	$pdf = $labels_arr['Result'][0]['BillPdfLabel'];
				// }else{
				// 	$pdf = '';
				// }
				// dump($labels_arr);
				// 返回快递其它内容
				$this->STEXT = array(
					'destcode'    => (isset($txt['Mark'])) ? $txt['Mark'] : '',// 20171019 jie
					'mailno'      => (isset($txt['Result']['BillCode'])) ? $txt['Result']['BillCode'] : '',// 分配到的跟踪号码
					'origincode'  => (isset($txt['originCode'])) ? $txt['originCode'] : '',
					'orderid'     => $data['MKNO'],
					'custid'      => (isset($txt['ParcelId'])) ? $txt['ParcelId'] : '', // 创建成功的包裹编号 20171019 jie
					'DeliveryFee' => (isset($txt['Result']['DeliveryFee'])) ? $txt['Result']['DeliveryFee'] : '',
					'TaxFee'      => (isset($txt['Result']['TaxFee'])) ? $txt['Result']['TaxFee'] : '',
					'Insurance'   => (isset($txt['Result']['Insurance'])) ? $txt['Result']['Insurance'] : '',
					'IsPostPay'   => (isset($txt['Result']['IsPostPay'])) ? $txt['Result']['IsPostPay'] : '',
					// 'zpl'         => $pdf, // 打印编码  不能放在这里，STEXT是要存到mk_tran_list里面的
				);
				// print_r($this->STEXT);die;
				$this->no = (isset($txt['Result']['BillCode'])) ? $txt['Result']['BillCode'] : '';	// 分配到的跟踪号码 mailno
			}

			return $this->getResponseData();// 把返回的原始json直接返回给ERP系统
		}else{
			$this->STEXT = array('ErrorStr'=>'贝海反馈：该订单下单无反馈');
		}

	}

	// 订单导入
	public function importorder($data)
	{
		self::_initialize();
		$order = $data['Order'];

		//商品列表
		$detail = array();
		// 内件详情
		foreach($order as $key=>$v){
			$detail[$key]['SkuCode']               = $v['barcode'];		//商品编码
			$detail[$key]['SecondLevelCategoryId'] = $v['CategoryId'];		//二级分类Id 	Y
			$detail[$key]['CategoryVersion']       = date('Y-m-d H:i:s');		//分类最新版本时间,格式为（yyyy-MM-dd HH:mm:ss）	Y
			$detail[$key]['SkuName']               = $v['detail'];		//商品名称	Y
			$detail[$key]['SendCountryName']       = $this->Order_config['SendCountryName'];		//发货国家	Y
			$detail[$key]['Num']                   = $v['number'];		//购买数量	Y
			$detail[$key]['Brand']                 = $v['brand'];		//品牌	Y
			$detail[$key]['UnitPrice']            = sprintf("%.2f", $v['price']);//申报单价，精确到2位小数，单位元,如100.25,表示100.25元	Y
			$detail[$key]['Weight']                = $v['weight'];		//重量 默认千克，如果发货国家传美国，则默认是磅	Y
		}

		//生成 物流格式数据
		$this->data_arr = array(
			'BusinessNo'       => $data['MKNO'],//业务编号，一个业务编号只能绑定一个物流面单	Y
			'ChannelName'      => $this->Order_config['ChannelName'],//渠道名称   现仅支持：京东、当当、一号店、Higo美丽说、苏宁		Y
			'OrderCode'        => $data['MKNO'],//订单号		Y
			'ReceiverName'     => $data['receiver'],//收货人姓名	Y
			'ReceiverMobile'   => $data['reTel'],//收货人电话	Y
			'ReceiverProvince' => $data['province'],//收货人省份	Y
			'ReceiverCity'     => $data['city'],//收货人城市	Y
			'ReceiverDistrict' => $data['town'],//收货人区/县	Y
			'ReceiverAddress'  => $data['reAddr'],//收货人详细地址	Y
			'ReceiverPostCode' => $data['postcode'],//收货人邮编	Y
			'ReceiverIdCode'   => $data['idno'],//收货人邮编	Y
			'PaymentPrice'     => sprintf("%.2f", $data['price']),//支付金额	Y
			'OrderPrice'       => sprintf("%.2f", $data['price']),//订单金额	Y
			'ProductList'      => $detail,//订单明细列表	Y
			'Remark'           => $data['notes'],//订单备注
		);

		$this->_funcy = 'xlobo.labels.importorder';
		$this->_setRequest();

		$res = $this->getResponseData();

		$arr = json_decode($res, true);
		// dump($arr);
		// die;

		if($arr['ErrorCount'] == 0){
			// sleep(5);
			$this->createNo($data);//再 做单
		}else{
			// 导入订单失败
			$this->STEXT = array('ErrorStr'=>'贝海反馈：'.$arr['Message']);
		}

		
	}

	// 获取热敏格式面单（推荐） PDF
	public function labels($no)
	{
		// self::_initialize();
		$this->_funcy = 'xlobo.labels.file.getFile10x15';

		$this->data_arr = array('BillCodes'=>array($no));
		// dump($no);die;
		$this->_setRequest();
		return $this->getResponseData();
	}

	// 单独 获取热敏格式面单（推荐） PDF
	public function get_labels($no)
	{
		self::_initialize();
		$this->_funcy = 'xlobo.labels.file.getFile10x15';

		$this->data_arr = array('BillCodes'=>array($no));
		// dump($no);die;
		$this->_setRequest();
		$res = $this->getResponseData();
		$arr = json_decode($res, true);
		if($arr['ErrorCount'] == 0){
			return $arr['Result'][0]['BillPdfLabel'];
		}else{
			return 'Error:无法从贝海获取pdf打印码';
		}
		// dump($arr);die;
		// return ;
	}

	// 申报分类获取
	public function catalogue()
	{
		self::_initialize();
		// return 'hello';
		$this->_funcy = 'xlobo.catalogue.get';
		$this->_setRequest();
		return $this->getResponseData();
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
// dump($this->data_arr);die;
		$data_json = json_encode($this->data_arr);
// dump($data_json);die;
		$sign = $this->config['SecretKey'].$data_json.$this->config['SecretKey'];

		$sign = strtolower($sign);//先转为小写

		$sign = iconv("UTF-8", "GBK//IGNORE", $sign);//转为GBK编码
// dump($this->_funcy);die;
		//先base64再md5加密
		$sign = md5(base64_encode($sign));

		$this->_requestData['method']       = $this->_funcy;
		$this->_requestData['v']            = $this->config['Version'];
		$this->_requestData['msg_param']    = $data_json;
		$this->_requestData['client_id']    = $this->config['APPKEY'];
		$this->_requestData['sign']         = $sign;
		$this->_requestData['access_token'] = $this->config['AccessToken'];
// dump($this->_requestData);
// die;
		$this->post();
	}

	// 发送数据
	public function post()
	{
        $o = "";
        foreach ( $this->_requestData as $k => $v ){
        	$o.= "$k=" . urlencode( $v ). "&" ;
            // $o.= "$k=" . $v. "&" ;
        }

        $post_data = substr($o,0,-1);
		// dump($post_data);die;
		$this->_responseData = http_post($this->config['pmsLoginAction'], $post_data);
		// dump($this->_responseData);die;
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