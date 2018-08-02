<?php
namespace AUApi\Controller\KdnoConfig;
/*
	版本号：V1.0
	创建人：Jie
	创建日期：2016-10-10
	修改日期：2016-10-11
	用途：与广州EMS对接
 */
	require_once('Kdno3.function.php'); //功能函数

class Kdno3{

	function _initialize(){
		ini_set('memory_limit','4088M');
		set_time_limit(0);
		header('Content-type:text/html;charset=UTF-8');	//设置输出格式

		$kdfile 	= dirname(__FILE__).'/Kdno3.conf.php'; 
		require_once($kdfile);//载入配置信息
		$this->config = $config;//方便全局调用
	}

	/**
	 * 推送订单
	 * @param [type] $data [description]
	 */
	function addOrder($data){

		$order = $data['Order'];

		//1.处理成 EMS订单格式的数据
		$log = array(
			'orderid'      => $data['MKNO'],		//订单唯一编号
			'contact'      => $data['receiver'],		//收货人
			'mobile'       => $data['reTel'],		//收货人号码
			'email'        => $data['email'],		//收件人邮箱
			'country'      => 'CA',       //收货人国家
			'address'      => $data['reAddr'],		//收货人地址
			'city'         => $data['city'],		//收货人城市
			'province'     => $data['province'],		//收货人省份
			'post_code'    => $data['postcode'],		//邮政编码
			'idcard'       => $data['idno'],		//身份证号码
			'total_weight' => $data['weight'],		//总重量（kg）
			
			's_contact' => $data['sender'],		//发件人
			's_country' => 'US',		//发件人国家
		);

		//2.处理成 EMS商品格式的数据
		$detail = array();
		foreach($order as $key=>$v){
			$detail[$key]['name']      = $v['detail'];
			$detail[$key]['short']     = $v['detail'];
			$detail[$key]['code']      = '';
			$detail[$key]['taxno']     = '';
			$detail[$key]['unit']      = ($v['unit'] != '') ? $v['unit'] : '件';
			$detail[$key]['amount']    = $v['number'];
			$detail[$key]['weight']    = $v['weight'];
			$detail[$key]['netweight'] = $v['weight'];
			$detail[$key]['spec']      = ($v['specifications'] != '') ? $v['specifications'] : '无';
			$detail[$key]['brand']     = $v['brand'];
			$detail[$key]['hscode']    = $v['hs_code'];
			$detail[$key]['price']     = $v['price'];
			$detail[$key]['currency']  = $v['coin'];
		}

		$log['detail'] = $detail;

		$res = $this->common($log, __FUNCTION__);
		return $res;
	}

	/**
	 * 删除订单
	 * @param  [type] $data [description]
	 * @return [type]       [description]
	 */
	public function delOrder($data){
		$log = array(
			'orderid' => $data['MKNO'],		//订单唯一编号
		);

		$res = $this->common($log, __FUNCTION__);
		return $res;
	}

	/**
	 * 获取订单跟踪号
	 * @param  [type] $data [description]
	 * @return [type]       [description]
	 */
	public function getTrackingNumber($data){
		$log = array(
			'orderid' => $data['MKNO'],		//订单唯一编号
		);

		$res = $this->common($log, __FUNCTION__);
		return $res;
	}

	/**
	 * 推送航空单
	 */
	public function addBill(){

	}

//================ 发送XML数据 =========================
	function common($log, $funcy=''){
		self::_initialize();
		// print_r($this->config);die;
		$list  = array();
		$order = array();

		if(in_array($funcy, array('addOrder','delOrder'))){
			$order['order'] = $log;

		}else if($funcy == 'getTrackingNumber'){
			$order['track'] = $log;

		}else if($funcy == 'addBill'){
			$order['name'] = '';
			$order['memo'] = '';
			$order['track'] = $log;
		}

		
		$list['header']['token'] = $this->config['UserToken'];
		$list['header']['key'] = md5(arrayToXml($order));

		if($funcy != '') $list['header']['action'] = $funcy;

		$list['body'] = $order;

		$xml = arrayToXml($list);//数组换XML
		// print_r($xml);die;

		$res = sendXML($this->config['Url'],$xml);
		return $res;
	}
}