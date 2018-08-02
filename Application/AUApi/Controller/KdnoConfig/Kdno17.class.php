<?php
namespace AUApi\Controller\KdnoConfig;
/*
	版本号：V1.0
	创建人：Jie
	创建日期：2017-10-19
	修改日期：2017-10-23
	用途：1.与中通快递下单对接，获取中通运单号；2.向中通推送节点
	指导文档：
 */
// namespace Api\Controller;
class Kdno17{

	protected $STEXT        = '';		// 20171019 jie 返回快递其他内容
	protected $no           = '';		// 20171019 jie 返回快递号码 mailno
	protected $config       = array();
	protected $Order_config = array();

	function __construct(){
		ini_set('memory_limit','4088M');
		set_time_limit(0);
		header('Content-type:text/html;charset=UTF-8');	//设置输出格式

		require_once(dirname(__FILE__).'\Kdno17.conf.php');	//载入配置信息
		require_once(dirname(__FILE__).'\Kdno17.function.php'); //功能函数

		$this->config       = $config;
		$this->Order_config = $Order_config;
	}

	public function data($data){

		return $this->SubmitOrder($data);
	}

    //保留一位小数，小数点第二位直接去掉
    private function num_to_change_for_zt($n){
        $num = floatval($n) * 10;
        $arr = explode('.',$num);

        if($arr[0] > 0){
            return sprintf("%.1f", floatval($arr[0])/10);
        }else{
            return '0.1';
        }
    }

	/**
	 * 提交我方的订单给中通，然后获取中通的单号
	 * @param [type] $data [订单数据信息，包括订单中的商品信息]
	 */
	public function SubmitOrder($data){

		$order = $data['Order'];

		//商品列表
		$detail = array();

		// 货物信息列表
		foreach($order as $key=>$v){
			$detail[$key]['Name']         = $v['detail'];		//商品名称	Y
			$detail[$key]['Quantity']     = $v['number'];		//商品数量	Y
			$detail[$key]['CurrencyCode'] = 'USD';		//货币代码，如：USD, CNY
		}

        /* 去除详细地址中的省市区和空格 */
        $data['reAddr'] = str_replace($data['town'],'',$data['reAddr']);
        $data['reAddr'] = str_replace($data['city'],'',$data['reAddr']);
        $data['reAddr'] = str_replace($data['province'],'',$data['reAddr']);
        $data['reAddr'] = trim($data['reAddr'],' ');//清除两侧的空格

		//生成 中通 物流格式数据
		$log = array(
			'BranchCode'            => $this->Order_config['BranchCode'], //物流公司海外仓编号
			'LogisticsProviderCode' => $this->Order_config['LogisticsProviderCode'], //线路编号（如果指定LogisticsServiceCode该字段为必选）
			'LogisticsServiceCode'  => $this->Order_config['LogisticsServiceCode'], //线路服务编号
			'MemberId'              => $this->Order_config['MemberId'], //线路服务编号

			'OrderDetails'          => array(//订单详情

				'PartnerOrder'=>array(
					'PartnerOrderNumber' => $data['MKNO'], //合作方订单编号  MKNO
				),
				'Order'   => array(
					'GrossWeight' => $data['weight'],//$this->num_to_change_for_zt(0.454 * floatval($data['weight'])), //订单中货物的总重量 ，需转kg
					'UnitType'    => 'Imperial', //单位制类型：公制：Metric, 英制：Imperial
				),
				'CargoList'   => $detail, //货物信息列表
				//发件人
				'Sender'      => array(
					'ContactName'   => $data['sender'],  //发件人姓名
					'MobilePhone'   => $data['sendTel'], //发件人手机
					'Province'      => $this->Order_config['SenderProvince'], //省
					'City'          => $this->Order_config['SenderCity'], //市
					'StreetAddress' => $data['sendAddr'],//发件人街道地址
					'PostalCode'    => $data['sendcode'],//发件人邮编
					'CountryCode'   => $this->Order_config['SenderCountryCode'],//发件人国家代码
				),
				//收件人
				'Receiver'    => array(
					'ContactName'   => $data['receiver'],//收件人姓名
					'MobilePhone'   => $data['reTel'],//收件人手机
					'Province'      => $data['province'],//省
					'City'          => $data['city'],//市
					'District'      => $data['town'],//区
					'StreetAddress' => $data['reAddr'],//收件人街道地址
					'PostalCode'    => $data['postcode'],//收件人邮编
					'CountryCode'   => $this->Order_config['ReceiverCountryCode'],//收件人国家代码
					'IDNumber'      => '*',//收件人身份证号码
				),
			),
		);

		$list = array();
		
		$list['SubmitOrder'] = $log;//组成完成的数据格式

		$result = $this->common_solo($list, 'SubmitOrder', $this->config['version'], $data);

		$res = simplest_xml_to_array($result);
	
		if(is_array($res) && isset($res['Success'])){
			// 此用于查看
			if($res['Success'] == 'false'){
				$this->STEXT = array('ErrorStr'=>'中通反馈：'.$res['ReasonDesc']);//20171019 Jie
			
			}else{
				$txt = $res['Data'];
				// 返回快递其它内容
				$this->STEXT = array(
					'destcode'   => (isset($txt['Mark'])) ? $txt['Mark'] : '',// 20171019 jie
					'mailno'     => (isset($txt['OrderNumber'])) ? $txt['OrderNumber'] : '',// 分配到的跟踪号码 20171019 jie
					'origincode' => (isset($txt['originCode'])) ? $txt['originCode'] : '',
					'orderid'    => $data['MKNO'],
					'custid'     => (isset($txt['ParcelId'])) ? $txt['ParcelId'] : '', // 创建成功的包裹编号 20171019 jie
					// 'mark'       => (isset($txt['Mark'])) ? $txt['Mark'] : '', // 标记 20171019 jie
				);
				// print_r($this->STEXT);die;
				$this->no = (isset($txt['OrderNumber'])) ? $txt['OrderNumber'] : '';	// 分配到的跟踪号码 mailno
			}

			return $result;// 把返回的原始json直接返回给ERP系统
		}else{
			$this->STEXT = array('ErrorStr'=>'中通反馈：该订单下单无反馈');
		}
	}

	/**
	 * 物流节点推送
	 * @param [type] $STNO       [中通单号]
	 * @param [type] $push_state [节点]
	 * @param string $airno      [航空单号]
	 * @param [type] $data       [数据数组]
	 */
	public function SubmitTracking($arr){
		
		$STNO       = $arr['STNO'];
		$push_state = $arr['push_state'];
		$airno      = $arr['airno'];
		$data       = $arr['data'];

		if(in_array($push_state, array('Arrival','Departure'))){
			$model = array(
				'SubmitTracking' => array(
					'entityType'    => 'Order',
					'entityStatus'  => $push_state,
					'eventMessage'  => '',
					'ExpressNumber' => $STNO,
					'BranchCode'    => $this->Order_config['BranchCode'],
					'operatorID'    => $this->Order_config['operatorID'],
					'FlightNumber'  => $airno,
					'BLNumber'      => '*',
				),
			);
		}else{
			$model = array(
				'SubmitTracking' => array(
					'entityType'    => 'Order',
					'entityStatus'  => $push_state,
					'eventMessage'  => '',
					'ExpressNumber' => $STNO,
					'BranchCode'    => $this->Order_config['BranchCode'],
					'operatorID'    => $this->Order_config['operatorID'],
				),
			);
		}

		$result = $this->common_solo($model, 'SubmitTracking', '1.0', $data);

		$res = simplest_xml_to_array($result);

		if(isset($res['Success'])){
			if($res['Success'] == 'true'){
				return true;
			}else{
				return false;
			}
		}else{
			return false;
		}
		
		// //测试数据
		// return true;
	}

	// 把中通和菜鸟单号进行推送
	public function updateCustomerOrderNumber($STNO, $LPNO, $data){
		$model = array(
			'SubmitOrder' => array(
				'ExpressOrderNumber'  => $STNO, // 中通单号
				'CustomerOrderNumber' => $LPNO,	// 菜鸟单号
			),
		);

		$result = $this->common_solo($model, 'SubmitOrder', 'updateCustomerOrderNumber', $data);

		$res = simplest_xml_to_array($result);

		if(isset($res['Success'])){
			if($res['Success'] == 'true'){
				return true;
			}else{
				return false;
			}
		}else{
			return false;
		}
		
		// //测试数据
		// return true;
	}

	// 中通单号剩余数量
	public function RemainingNumberOfOrders(){
		$model = array(
			'root' => array(
				'branchCode' => $this->Order_config['BranchCode'],
			),
		);

		$result = $this->common_solo($model, 'SubmitOrder', 'RemainingNumberOfOrders', array('MKNO'=>'Remaining','STNO'=>'NumberOfOrders'));

		// 测试数据
		// $result = '<Response><Success>true</Success><Reason></Reason><ReasonDesc></ReasonDesc><Data><RemainingNumberOfOrders>12</RemainingNumberOfOrders></Data></Response>';

		$res = simplest_xml_to_array($result);

		if(isset($res['Success'])){
			if($res['Success'] == 'true'){
				return $res['Data']['RemainingNumberOfOrders']; //返回剩余数量
			}else{
				return false;
			}
		}else{
			return false;
		}
	}

//===================================================
	// 推送数据 统一调用方法
	public function common_solo($list, $messageType, $version, $data){
		
		$xml = arrayToXmlForZhongTong($list);//转换成xml报文

		$up_to_low = strtolower($xml);//通知内容（xml/json）全部转化为小写
		$with_key = $up_to_low.$this->config['checkword'];//加密钥：上一步得到的字符串追加密钥
		$with_md5 = md5($with_key);//将上一步得到的字符串进行MD5

		$sendData = array();
		$sendData['content']     = $xml;//要发送的XML内容
		$sendData['cryptograph'] = $with_md5;//数据验证密文
		$sendData['partnerName'] = $this->config['partnerName'];//合作商名称
		$sendData['version']     = $version;//API版本号
		$sendData['messageType'] = $messageType;//发送的消息类型
		$sendData['format']      = $this->config['format'];//要发送内容的数据格式，目前支持xml，默认值为xml

		$MK = new \Org\MK\HTTP();
		$result = $MK->post($this->config['pmsLoginAction'], $sendData, 1200);

		// 是否输出txt文件 20171019 jie
		if($this->config['exports_switch'] === true){

			if(!is_dir($this->config['xmlsave'])) mkdir($this->config['xmlsave'], 0777, true);//第三个参数设置true 设置递归模式自动创建必需的目录文件夹

			$file_name = 'Kdno17_'.$messageType.'_'.$data['MKNO']."_".$data['STNO'].'.txt';	//文件名

			$content = "=================== ".date('Y-m-d H:i:s')." ===================\r\n\r\n-------- Request --------\r\n\r\n".$xml."\r\n\r\n-------- Response --------\r\n\r\n".$result."\r\n\r\n";

			if(is_file($file_name)){

				file_put_contents($this->config['xmlsave'].$file_name, $content);
			}else{
				file_put_contents($this->config['xmlsave'].$file_name, $content, FILE_APPEND);
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