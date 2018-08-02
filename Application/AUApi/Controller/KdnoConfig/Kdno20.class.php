<?php
namespace AUApi\Controller\KdnoConfig;
/*
	版本号：V1.0
	创建人：Jie
	创建日期：2017-12-22
	修改日期：2017-12-22
	用途：1.与ems快递下单对接，获取运单号
	指导文档：
 */
require_once(dirname(__FILE__).'\Kdno20.function.php'); //功能函数
class Kdno20{

    protected $_requestData;
    protected $_appToken;
    protected $_funcy;
	protected $_version;				// 版本
	protected $STEXT        = '';		// 返回快递其他内容
	protected $no           = '';		// 返回快递号码 mailno
	protected $config       = array();
	protected $Order_config = array();

	function _initialize(){
		ini_set('memory_limit','500M');
		set_time_limit(0);
		header('Content-type:text/html;charset=UTF-8');	//设置输出格式
		require_once(dirname(__FILE__).'\Kdno20.conf.php');	//载入配置信息

		$this->config       = $config;
		$this->Order_config = $Order_config;
		$this->_version     = $config['Version'];
		$this->_appToken    = $config['AppToken'];
	}

	function data($data){
		self::_initialize();
		$order = $data['Order'];

		//商品列表
		$detail = array();

		// 内件详情
		foreach($order as $key=>$v){
			$detail[$key]['Name']       = $v['detail'];		//货物名称	Y
			$detail[$key]['Hs_Code']    = $v['hs_code'];		//HS 编码	Y
			$detail[$key]['Unit_Price'] = sprintf("%.2f", ($v['price'] / $this->config['rmb_rate'] * $this->config['percent']));		//货物单价	Y
			$detail[$key]['Act_Weight'] = $v['weight'];		//货物实际重量(KG)	Y
			$detail[$key]['Dim_Weight'] = $v['weight'];		//货物体积重量(KG)	Y
			$detail[$key]['Quantity']   = $v['number'];		//数量	Y
		}

		//生成 物流格式数据
		$log = array(
			'ReferenceNo'          => $data['MKNO'],//平台自有的订单号 	Y
			'BuyerName'            => $data['sender'],//订购人姓名		Y
			'CustomerId'           => $data['idno'],//订购人身份证	Y
			'Freight'              => $this->Order_config['Freight'],//运费 无则填 0	Y
			'InsuredFee'           => $this->Order_config['InsuredFee'],//保价费 无则填 0	Y
			'Weight'               => $data['weight'],//毛重（千克） 包裹实际重量	Y
			'GoodsInfo'            => $data['province'],//货物信息	Y
			'Shipper'              => $data['sender'],//发货人姓名	Y
			'ShipperTelephone'     => $data['sendTel'],//发货人电话	Y
			'ShipperCountry'       => $this->Order_config['ShipperCountry'],//发货人所在国	Y
			'ShipperProvince'      => $this->Order_config['ShipperProvince'],//发货人省份	Y
			'ShipperCity'          => $this->Order_config['ShipperCity'],//发货人城市	Y
			'ShipperCounty'        => $this->Order_config['ShipperCounty'],//发货人区/县	Y
			'ShipperAddress'       => $data['sendAddr'],//发货人详细地址	Y
			'ShipperZipCode'       => $data['sendcode'],//发货人邮编	Y
			'Consignee'            => $data['receiver'],//收货人姓名	Y
			'ConsigneeTelephone'   => $data['reTel'],//收货人电话	Y
			'ConsigneeProvince'    => $data['province'],//收货人省份	Y
			'ConsigneeCity'        => $data['city'],//收货人城市	Y
			'ConsigneeCounty'      => $data['town'],//收货人区/县	Y
			'ConsigneeAddress'     => $data['reAddr'],//收货人详细地址	Y
			'ConsigneeZipCode'     => $data['postcode'],//收货人邮编	Y
			'LogisticsProductCode' => $this->Order_config['LogisticsProductCode'],//物流产品编码	Y
			'Note'                 => $data['notes'],//订单备注
			'OrderType'            => $this->Order_config['OrderType'],//订单类型  必填;0:直购 2:保税;默认：0	Y
			'Items'                => $detail,//订单明细列表	Y
		);

		$this->_requestData = $log;

		$sendData = $this->_setRequest();

		$sign = base64_encode(json_encode($sendData,JSON_UNESCAPED_UNICODE));

		$sendData['AppToken'] = $this->_appToken;
		$sendData['Sign']     = $sign;

		$res = $this->common_solo($sendData, $this->config, 'OrderMarking');

		$arr = json_decode($res,true);
		// dump($arr);die;
		
		if(isset($arr) && is_array($arr)){

			if($arr['Result'] == 0){
				$this->STEXT = array('ErrorStr'=>'中邮反馈：'.(!empty($arr['Error']['LongMessage'])?$arr['Error']['LongMessage']:$arr['Error']['ShortMessage']));//20171019 Jie
			}else{
				// 返回快递其它内容
				$this->STEXT = array(
					'destcode'   => (isset($arr['Mark'])) ? $arr['Mark'] : '',// 20171019 jie
					'mailno'     => (isset($arr['LogisticsNo'])) ? $arr['LogisticsNo'] : '',// 分配到的跟踪号码
					'origincode' => (isset($arr['originCode'])) ? $arr['originCode'] : '',
					'orderid'    => $data['MKNO'],
					'custid'     => (isset($arr['ParcelId'])) ? $arr['ParcelId'] : '', // 创建成功的包裹编号 20171019 jie
				);
				// print_r($this->STEXT);die;
				$this->no = (isset($arr['LogisticsNo'])) ? $arr['LogisticsNo'] : '';	// 分配到的跟踪号码 mailno
			}
		}else{
			$this->STEXT = array('ErrorStr'=>'EMS反馈：该订单下单无反馈');
		}
	}

	// 综合处理
	public function common_solo($sendData, $config, $funcy){
		$timeout = 1200; //超时时间
		$jsonData = json_encode($sendData,JSON_UNESCAPED_UNICODE);

		$result = http_post($config['pmsLoginAction'], $jsonData, $timeout);

		// 是否输出txt文件 20171208 jie
		if($config['exports_switch'] === true){

			if(!is_dir($config['xmlsave'])) mkdir($config['xmlsave'], 0777, true);//第三个参数设置true 设置递归模式自动创建必需的目录文件夹

			$file_name = 'Kdno20_'.$funcy.'_'.date('Ymd').'.txt';	//文件名

			$content = "===================== ".date('Y-m-d H:i:s')." =====================\r\n\r\n-------- RequestData --------\r\n\r\n".$jsonData."\r\n\r\n-------- ResponseData --------\r\n\r\n".$result."\r\n\r\n";

			if(is_file($file_name)){
				file_put_contents($config['xmlsave'].$file_name, $content);
			}else{
				file_put_contents($config['xmlsave'].$file_name, $content, FILE_APPEND);
			}
		}

		return $result;
	}


    private function _setRequest()
    {
		$headArgs['Order']    = $this->_requestData;
		$headArgs['Version']  = $this->_version;
        preg_match('/0\.(\d+) (\d+)/', microtime(), $p);
        $headArgs['RequestTime'] = sprintf('%s.%sZ', date('Y-m-d\TH:i:s', $p[2]), $p[1]);
        preg_match('/(\w{8})(\w{4})(\w{4})(\w{4})(\w{12})/', md5(session_id()), $p);
		$headArgs['RequestId'] = sprintf('%s-%s-%s-%s-%s', $p[1], $p[2], $p[3], $p[4], $p[5]);
		
        return $headArgs;
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