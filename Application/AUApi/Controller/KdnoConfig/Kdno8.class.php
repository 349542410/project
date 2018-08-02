<?php
namespace AUApi\Controller\KdnoConfig;
/*
	版本号：V1.0
	创建人：Jie
	创建日期：2016-10-10
	修改日期：2016-10-11
	用途：与中国圆通下单对接
	指导文档：网址：http://open.yto.net.cn/OpenPlatform/doc
 */
	require_once('Kdno8.function.php'); //功能函数
class Kdno8{

	protected $STEXT        = '';		// 20161223 jie 返回快递其他内容
	protected $no           = '';		// 20161223 jie 返回快递号码 mailno
	protected $config       = array();
	protected $Order_config = array();

	function _initialize(){
		ini_set('memory_limit','4088M');
		set_time_limit(0);
		header('Content-type:text/html;charset=UTF-8');	//设置输出格式

		$kdfile 	= 'Kdno8.conf.php'; 
		// print_r($kdfile);die;
		require_once($kdfile);//载入配置信息

		$this->config       = $config;//方便全局调用
		$this->Order_config = $Order_config;//方便全局调用
	}

	public function CommonOrderModeB($data){
		self::_initialize();

		$order = $data['Order'];
		
		//1.处理成 EMS订单格式的数据
		$log = array(
			'clientID'           => $this->config['clientId'],//$data['auto_Indent2']."_".$data['auto_Indent1'],	//商家代码（必须与customerId一致） Y
			'logisticProviderID' => $this->Order_config['logisticProviderID'],			//物流公司ID   Y
			'customerId'         => $this->config['clientId'],//$data['auto_Indent2']."_".$data['auto_Indent1'],	//商家代码 (由商家设置， 必须与clientID一致)   Y
			'txLogisticID'       => $data['MKNO'],			//物流订单号  Y
			'tradeNo'            => '',      				//业务交易号（可选）  N
			'mailNo'             => '',						//物流运单号    N

			'serviceType'        => $this->Order_config['serviceType'],		//服务类型(1-上门揽收, 2-次日达 4-次晨达 8-当日达,0-自己联系)。默认为0   Y
			'orderType'          => $this->Order_config['orderType'],		//订单类型(0-COD,1-普通订单,2-便携式订单3-退货单)   Y
			
			//发件人
			'sender' => array(
				'name'               => $data['sender'],		//用户姓名		Y
				'postCode'           => $data['sendcode'],		//用户邮编		N
				'phone'              => $data['sendTel'],		//用户电话，包括区号、电话号码及分机号，中间用“-”分隔；		N
				'mobile'             => $data['sendTel'],		//用户移动电话， 手机和电话至少填一项	N
				'prov'               => '',			//用户所在省	Y
				'city'               => '',			//用户所在市县（区），市区中间用英文“,”分隔；注意有些市下面没有区	Y
				'address'            => $data['sendAddr'],		//用户详细地址	Y
			),

			//收件人
			'receiver' => array(
				'name'               => $data['receiver'],		//用户姓名		Y
				'postCode'           => $data['postcode'],			//用户邮编		N
				'phone'              => $data['reTel'],			//用户电话，包括区号、电话号码及分机号，中间用“-”分隔；		N
				'mobile'             => $data['reTel'],		//用户移动电话， 手机和电话至少填一项	N
				'prov'               => $data['province'],			//用户所在省	Y
				'city'               => $data['city'],			//用户所在市县（区），市区中间用英文“,”分隔；注意有些市下面没有区	Y
				'address'            => $data['reAddr'],		//用户详细地址	Y
			),

			'itemsValue'         => $data['price'],		//货物价值	N
			'itemsWeight'        => $data['weight'],	//货物总重量	N
			'goodsValue'         => $data['price'],		//商品金额，包括优惠和运费，但无服务费 N
			'totalValue'         => $data['price'],		//goodsValue+总服务费	N

			'totalServiceFee'    => '0.0',		//保值金额=insuranceValue*货品数量(默认为0.0）   N
			'codSplitFee'        => '0.0',		//物流公司分润[COD] （暂时没有使用，默认为0.0）  N
			'type'               => '',			//订单类型（该字段是为向下兼容预留）   N
			'insuranceValue'     => '0.0',		//保值金额 （保价金额为货品价值（大于等于100少于3w），默认为0.0）   N
			'special'            => '',			//商品类型（保留字段，暂时不用）	N
			'flag'               => '0',		//订单flag标识，默认为 0，暂无意义   N
			'agencyFund'         => '0.0',		//代收金额，如果是代收订单， 必须大于0；非代收设置为0.0  	N
			'sendStartTime'      => '',			//物流公司上门取货时间段，通过”yyyy-MM-dd HH:mm:ss”格式化，本文中所有时间格式相同。 N
			'sendEndTime'        => '',			//物流公司上门取货时间段，通过”yyyy-MM-dd HH:mm:ss”格式化，本文中所有时间格式相同。 N
			'remark'             => $data['notes'],		//备注	N
		);

		//商品列表
		$detail = array();

		foreach($order as $key=>$v){
			// 商品详细
			$detail[$key]['itemName']  = $v['detail'];		//商品名称	Y
			$detail[$key]['number']    = $v['number'];		//商品数量	Y
			$detail[$key]['itemValue'] = $v['price'];		//商品单价（两位小数）	N
		}

		$log['items'] = $detail;//商品列表并入到订单信息中

		$list = array();
		
		$list['RequestOrder'] = $log;//组成完成的数据格式

		$xml = arrayToXml($list);//转换成xml报文

		//数字签名
		$data_digest = base64_encode(md5($xml.$this->config['partnerId']));
		
		$sendData['logistics_interface'] = urlencode($xml);
		$sendData['data_digest'] = $data_digest;
		$sendData['type'] = 'offline';
		$sendData['clientId'] = $this->config['clientId'];
// dump($sendData);die;
		
		$res = sendData($this->config['OrderModeUrl'], $sendData);
		dump($res);
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