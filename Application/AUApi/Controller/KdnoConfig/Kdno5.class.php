<?php
namespace AUApi\Controller\KdnoConfig;
/**
 * IBS平台客户API对接口 公用方法 soap方式发送
 * 打单/下单
 * 打印MK单
 * 指导文档：物流对接汇总/2016-08-09 IBS平台客户API对接口技术规范/IBS平台客户API对接口技术规范-v1.0-20160220.doc
 */
use Think\Controller;
class Kdno5 extends Controller{
/**
 * $config['express_type'] 快件类型 各个代码对应的文字说明
 * express_type = array(
 * 		•	200      国际特惠-试点
 * 		•	201      国际特惠-文件
 * 		•	202      国际特惠-包裹
 * 		•	100      国际标快-文件
 * 		•	101      国际标快-包裹
 * )
 * 
 */
	protected $STEXT = '';		// 20160907 jie 返回快递其他内容
	protected $no    = '';		// 20160907 jie 返回快递号码 mailno

	//基本配置   20160905 Jie 改为由Kdno5.conf.php中传入配置
	protected $config = array(
		'customerCode'   => '',	//客户编码
		'checkword'      => '',	//IBS系统为客户分配的密钥
		'pmsLoginAction' => "",	//网络服务请求地址
		'lang'           => '',	//语言 官方已默认为en
	);

	//默认的订单信息配置  20160905 Jie 部分配置改为由Kdno5.conf.php中传入配置
	protected $Order_config = array(
		'orderid'         => '',		//客户订单号 (唯一约束)
		'is_gen_bill_no'  => '1',		//默认值1

		'j_custid'        => '',			//设置在config中
		'j_email'         => '',			//设置在config中
		'j_company'       => '',        	//寄件方公司名称，设置在config中
		'j_area_code'     => '',			//寄件方联系区号，设置在config中
		'j_country'       => 'US',			//寄件方国籍   默认值US，设置在config中

		'j_contact'       => '',        //寄件方联系人，设置在config中
		'j_tel'           => '',		//寄件方联系电话，设置在config中
		'j_address'       => '',		//寄件方详细地址，设置在config中
		'j_post_code'     => '',		//寄件方邮编，设置在config中

		'd_contact'       => '',		//到件方联系人
		'd_contact_cn'    => '',		//到件方联系人中文名与上相同
		'd_tel'           => '',		//到件方联络电话
		'd_mobile'        => '',		//到件方手机号码
		'd_area_code'     => '',		//到件方联系区号
		'd_country'       => 'CN',		//到件方国籍  	默认值CN 中国
		'd_province'      => '',		//到件方省份
		'd_city'          => '',		//到件方城市
		'd_county'        => '',		//到件方所在县/区，必须是标准的县/区称谓，示例：“福田区”。
		'd_address'       => '',		//到件方详细地址
		'd_post_code'     => '',		//到件方邮编

		'custid'          => '0010002117',		//月结卡号, 设置在config中
		'buyers_nickname' => 'Megao',			//美购商城，设置在config中
		'reference_no1'   => '',		//auto_Indent1
		'reference_no2'   => '',		// 20160927 将 阿荣传过来的，price 和 discount 生成 price_discount 保存到顺丰reference_no2中
		'express_type'    => '200',		//快件类型	默认值200
		'parcel_quantity' => '1',		//包裹总件数	默认值1
		'pay_method'      => '1',		//默认值1
		'harmonized_code' => '',		//Harmonized Code美国寄出的可填写
		'aes_no'          => '',		//AES No.美国寄出可填写
		'order_cert_type' => '',		//ID(身份证)在tran_list.idkind
		'order_cert_no'   => '',		//证件号tran_list.idno
		'currency'        => '',		//货物声明价值币别，支持以下值：•	CNY 使用tran_list.coin
		'freight'         => '0',		//买家运费，默认没有为0
		'tax'             => '',		//税款
		'payment_tool'    => '',		//支付工具 tran_list.paykind
		'payment_number'  => '',		//支付交易号tran_list.payno
		'payment_time'    => '',		//支付时间 tran_list.paytime 格式：2016-03-18 17:09:09

	);

	//默认的商品信息配置
	protected $Order_Cargo = array(
		'goods_code'        => '',	//商品编号
		'name'              => '',	//货物名称
		'count'             => '',	//货物数量
		'unit'              => '件',	//货物单位，如：个、台、本
		'amount'            => '',	//货物单价，精确到小数点后3位
		'brand'             => '',	//品牌
		'statebarcode'      => '',	//国条码tran_order.barcode
		'specifications'    => '',	//规格型号
		'good_prepard_no'   => '',	//商品海关备案号tran_order.hgid
		'source_area'       => '',	//原产地国别
		'product_record_no' => '',	//海关编码 20170329  用ERP传来的hs_code
	);

	public function index($list=array(), $order=array(), $switch=false, $totalPrice=0){
		ini_set('memory_limit','4088M');
		set_time_limit(0);
		header('Content-type:text/html;charset=UTF-8');	//设置输出格式

		/* 20160905 Jie 载入配置信息 然后 把配置信息写入以下两个变量中 */
		$kdfile 	= 'Kdno5.conf.php'; 
		require_once($kdfile);//载入配置信息

		$this->config = array_merge($this->config, $baseconf);//用$baseconf数组里面的数组覆盖$Order_config

		$this->Order_config = array_merge($this->Order_config, $orderconf);//用$orderconf数组里面的数组覆盖$Order_config
		/* End 载入配置信息 然后 把配置信息写入以下两个变量中 */

		$this->Order_config = array_merge($this->Order_config, $list);//用$list数组里面的数组覆盖$Order_config

		// Jie 20160923  字段“buyers_nickname” 的容错处理
		// 处理办法：阿荣传输来的有值则用他的，否则用$Order_config中的填补；假如$Order_config中此值为空，则用$orderconf(Kdno5.conf.php里面的数组变量)的值填补；假如$orderconf中的值也是空的，则用最后的默认值"Megao"填补
		$this->Order_config['buyers_nickname'] = ($list['buyers_nickname'] == '') ? (($this->Order_config['buyers_nickname'] != '') ? $this->Order_config['buyers_nickname'] : (($orderconf['buyers_nickname'] != '') ? $orderconf['buyers_nickname'] : 'Megao')) : $list['buyers_nickname'];
		// End 容错处理

		// dump($this->Order_config);die;

		/* 20160913 Jie */
		$this->Order_config['freight'] = floatval($baseconf['freight']);//加载默认的运费数值

		// $amount = floatval($totalPrice) - floatval($baseconf['freight']);	//总金额 = 原总金额 - 运费
		$amount = floatval($totalPrice);	//总金额  20160929

		$rate   = 1+floatval($baseconf['rate']);
		
		//税金 = (总金额/1+税率)*税率
		$this->Order_config['tax'] = sprintf("%.2f",(floatval($amount)/floatval($rate))*floatval($baseconf['rate']));//计算税金

		// 计算每个商品优惠之后的价格
		foreach($order as $key=>$v){
			$order[$key]['amount'] = sprintf("%.2f",floatval($v['amount'])/(1+floatval($baseconf['rate'])));  // Jie 20160927 商品单价/(1+0.119)
		}

		/* End */

		// dump($order);die;
		// 商品信息生成xml报文
		$str = '';
		foreach($order as $kk=>$item){
			$arr[$kk] = array_merge($this->Order_Cargo, $item);	//用$v数组里面的数组覆盖$Order_Cargo
			$chtr[$kk] = $this->change($arr[$kk]);	//组装之后的数组拿去生成xml
			$str .= $chtr[$kk];	//再拼接一起
		}
		// dump($str);
		// 订单信息生成xml报文，并加入商品信息xml报文
		$OrderXML = $this->Txml($this->config['customerCode'], $this->Order_config, $service='apiOrderService', $str);
		// dump($OrderXML);die;

		// 对xml报文进行base64加密，并获取校验码
		$data = $this->baseXML($OrderXML);

		// 发送下单请求
		$OrderResponse = $this->useSoap($data[0], $data[1], $list['orderid']);

/*		dump($OrderResponse);
			$orderArr = $this->object_array($OrderResponse);	//对象转数组
			$orderArr2 = json_decode(json_encode((array) simplexml_load_string($orderArr['Return'])), true);// 返回的XML报文转为数组
		dump($orderArr);
		dump($orderArr2);
		die;*/

		$orderArr = $this->object_array($OrderResponse);	//对象转数组
		
		// 有信息返回
		if($orderArr['Return'] != '' && isset($orderArr['Return'])){

			$msgArr = json_decode(json_encode((array) simplexml_load_string($orderArr['Return'])), true);// 返回的XML报文转为数组
			// dump($msgArr);

			// 20160907 jie
			if($switch === true){
				// 20160907 jie 如果返回的Head为 OK 才执行
				if($msgArr['Head'] == 'OK'){
					$txt = $msgArr['Body']['OrderResponse'];

					// 返回快递其它内容
					$this->STEXT = array(
						'destcode'   => (isset($txt['destCode'])) ? $txt['destCode'] : '',//目的地区域 代码
						'mailno'     => (isset($txt['mailNo'])) ? $txt['mailNo'] : '',//顺丰运单号
						'origincode' => (isset($txt['originCode'])) ? $txt['originCode'] : '',//原寄地区域代码
						'orderid'    => $this->Order_config['orderid'],//客户订单号
						'custid'     => $this->Order_config['custid'],//月结卡号
					);
					$this->no = (isset($txt['mailNo'])) ? $txt['mailNo'] : '';	// 返回快递号码 mailno
					
				}else{
					// $this->STEXT = $msgArr;
					$this->STEXT = array('ErrorStr'=>'顺丰反馈：'.$msgArr['ERROR']);//20161020 Jie
				}
			}

			// 把返回的xml传给erp
			return $OrderResponse;

		}else{//没有收到信息反馈 则执行订单查询，查询是否已经下单成功与否

			// 生成 订单查询xml报文
			$OrderSearch = $this->Txml($this->config['customerCode'], $this->Order_config['orderid'], $service='OrderSearchService', $str='');	//再次发起请求

			$sear = $this->baseXML($OrderSearch);	//对xml报文进行base64加密，并获取校验码
			
			//发送查询请求
			$OrderSearchResponse = $this->useSoap($sear[0], $sear[1], $list['orderid']);

			$searchArr = $this->object_array($OrderSearchResponse);	//对象转数组

			if($searchArr['Return'] != '' && isset($searchArr['Return'])){
				$msgArr = json_decode(json_encode((array) simplexml_load_string($searchArr['Return'])), true);// 返回的XML报文转为数组
				// 把返回的xml传给erp
				if($msgArr['Head'] == 'OK'){
					$txt = $msgArr['Body']['OrderResponse'];

					// 返回快递其它内容
					$this->STEXT = array(
						'destcode'   => (isset($txt['destCode'])) ? $txt['destCode'] : '',
						'mailno'     => (isset($txt['mailNo'])) ? $txt['mailNo'] : '',
						'origincode' => (isset($txt['originCode'])) ? $txt['originCode'] : '',
						'orderid'    => $this->Order_config['orderid'],
						'custid'     => $this->Order_config['custid'],
					);
					$this->no = (isset($txt['mailNo'])) ? $txt['mailNo'] : '';	// 返回快递号码 mailno
				}else{
					$this->STEXT = array('ErrorStr'=>'顺丰反馈：'.$msgArr['ERROR']);//20161020 Jie
				}

				return $OrderSearchResponse;

			}else{
				// echo '该单下单与查询均无反馈，需再执行订单查询';
				$this->STEXT = array('ErrorStr'=>'顺丰反馈：该单下单与查询均无反馈，需再执行订单查询');//20161020 Jie

			}
		}

	}

//========================= IBS平台客户API对接口 与ERP对接 20160907 Jie =============================================
	/**
	 * 传入客人基本资料以返回物流信息 20160907 jie
	 * @param  [type] $data [客人基本资料]
	 * @return [type]       [description]
	 */
	public function data($list){
/*		// Jie  20160923  reference_no1 先将-改为_再进行截取，以防存在-
		$list['auto_Indent2'] = str_replace("-","_",$list['auto_Indent2']); // 20161214 jie 注释1  */

		/*// Jie 20160922 截取去除该字段中第一个“_”(包含此下划线)后面的部分
		$reference_no1 = (stripos($list['auto_Indent2'],"_")) ? str_replace(substr($list['auto_Indent2'],stripos($list['auto_Indent2'],"_")),'',$list['auto_Indent2']) : $list['auto_Indent2'];//tran_list.auto_Indent1  20161214 jie 注释2  与注释1同时使用  */
		$dstr = array('-','_');
		$reference_no1 = str_replace($dstr, '', $list['auto_Indent2'].$list['auto_Indent1']); //20161214 jie

		// 20160927 Jie 此函数中切勿再加载Kdno5.conf.php！！！

/*		// 20160923 jie 检验以下是否为空
		$check_arr = array('postcode');
		foreach($check_arr as $ki=>$porn){
			if($list[$porn] == ''){
				return $porn." 不能为空";
			}
		}*/

		// 将 阿荣传过来的，price 和 discount 生成 price_discount 保存到顺丰reference_no2中  20160927
		$list['discount'] = (isset($list['discount']) && $list['discount'] != '') ? $list['discount'] : 0;
		// $price_discount = sprintf("%.2f",floatval($list['price']))."_".sprintf("%.2f",floatval($list['discount']));
		
		$order = $list['Order'];

		//1.处理成 顺丰订单格式的数据
		$log = array(
			'orderid'         => $list['MKNO'],		//客户订单号 (唯一约束)

			"j_company"       => $list['sender'],	//寄件公司  20160922 Jie
			"j_contact"       => $list['sender'],	//发件人
			"j_address"       => $list['sendAddr'],	//发件人地址
			"j_tel"           => $list['sendTel'],	//发件人电话
			"j_post_code"     => $list['sendcode'],	//发件人邮编
			
			"d_contact"       => $list['receiver'],	//收件人
			"d_contact_cn"    => $list['receiver'],	//收件人中文名
			"d_tel"           => $list['reTel'],	//收件人联络电话
			"d_mobile"        => $list['reTel'],	//到件方手机号码
			'd_area_code'     => '',				//到件方联系区号
			"d_province"      => $list['province'],	//省
			"d_city"          => $list['city'],		//市
			"d_county"        => $list['town'],		//区
			"d_address"       => $list['reAddr'],	//收件人详细地址
			"d_post_code"     => $list['postcode'],	//收件人邮编
			
			'buyers_nickname' => ($list['buyers_nickname']) ? $list['buyers_nickname'] : $this->Order_config['buyers_nickname'],

			'reference_no1'   => $reference_no1,//tran_list.auto_Indent1

			'reference_no2'   => sprintf("%.2f",floatval($list['price']))."_".sprintf("%.2f",floatval($list['discount'])),
			'express_type'    => ($this->Order_config['express_type']) ? $this->Order_config['express_type'] : '200',	//快件类型	默认值200
			'parcel_quantity' => $this->Order_config['parcel_quantity'],				//包裹总件数	默认值1
			'pay_method'      => $this->Order_config['pay_method'],				//默认值1
			'harmonized_code' => $this->Order_config['harmonized_code'],				//Harmonized Code美国寄出的可填写
			'aes_no'          => $this->Order_config['aes_no'],				//AES No.美国寄出可填写
			'order_cert_type' => $list['idkind'],	//ID(身份证)在tran_list.idkind
			'order_cert_no'   => ($list['idno'] != 0) ? $list['idno'] : '000000000000000000',//$list['idno'],		//证件号tran_list.idno
			'currency'        => $list['coin'],		//货物声明价值币别，支持以下值：•	CNY 使用tran_list.coin
			'freight'         => $this->Order_config['freight'],				//买家运费，没有为0
			'tax'             => $this->Order_config['tax'],				//税款
			'payment_tool'    => $list['paykind'],		//支付工具 tran_list.paykind
			'payment_number'  => $list['payno'],		//支付交易号tran_list.payno
			'payment_time'    => $list['paytime'],		//支付时间 tran_list.paytime 格式：2016-03-18 17:09:09
			'ddp_remark'      => $list['price'],		//20160929 jie
			'turnover'	      => $list['price'],		//20161109 Man顺丰更新了付填写金额保存的字段
		);

		//2.处理成 顺丰商品格式的数据
		$order_arr = array();
		foreach($order as $key=>$v){
			$order_arr[$key]['goods_code']        = $v['barcode'];
			$order_arr[$key]['name']              = $v['detail'];
			$order_arr[$key]['count']             = $v['number'];
			$order_arr[$key]['unit']              = ($v['unit'] != '') ? $v['unit'] : '件';//Order.unit
			$order_arr[$key]['amount']            = $v['price'];
			$order_arr[$key]['brand']             = $v['brand'];
			$order_arr[$key]['statebarcode']      = $v['barcode'];
			$order_arr[$key]['specifications']    = ($v['specifications'] != '') ? $v['specifications'] : '无';	//Order.specifications
			$order_arr[$key]['good_prepard_no']   = $v['hgid'];
			$order_arr[$key]['source_area']       = ($v['source_area'] != '') ? $v['source_area'] : 'USA';//Order.source_area
			$order_arr[$key]['product_record_no'] = $v['hs_code'];//Order.hs_code  海关编码  20170329
		}
		// dump($log);
		// die;
		// dump($order_arr);
		// die;
		// return $log;
		return $this->index($log, $order_arr, true, $list['price']);
	}

	/**
	 * 返回快递其他内容 20160907 jie
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
	 * 返回快递号码 mailno  20160907 jie
	 * @return [type] [description]
	 */
	public function no(){
		return $this->no;//直接返回mailno
	}

//======================================================================
	/**
	 * 用soap 发送请求
	 * @param  [type] $data        [base64加密之后的报文]
	 * @param  [type] $validateStr [校验码]
	 * @return [type]              [description]
	 */
	public function useSoap($data, $validateStr, $orderid){
		set_time_limit(0);
		$soap = new \SoapClient($this->config['pmsLoginAction']);//网络服务请求地址
		$customerCode = $this->config['customerCode'];//客户编码
		$result = $soap->sfexpressService(array('data'=>$data, 'validateStr'=>$validateStr, 'customerCode'=>$customerCode));//查询，返回的是一个结构体

		// 是否输出txt文件
		if($this->config['exports_switch'] === true){
			$orderArr = $this->object_array($result);	//对象转数组
			$msgArr = json_encode((array) simplexml_load_string($orderArr['Return']));// XML报文转为json

			$file_name = 'Kdno5_'.$orderid.'_'.time().'.txt';	//文件名

			$content = "======== Request =========\r\n\r\n====== data =======\r\n".$data."\r\n\r\n======== (data)base64解密后： =========\r\n\r\n".base64_decode($data)."\r\n\r\n======= verifyCode(校验码) ========\r\n\r\n".$validateStr."\r\n\r\n======== Response =========\r\n\r\n".$msgArr;

			if(!is_dir($this->config['xmlsave'])) mkdir($this->config['xmlsave'], 0777);

			//echo 
			file_put_contents($this->config['xmlsave'].$file_name, $content);
		}

		return $result;
	}

/////////////////////////////////////////////////////////////////////////////////
	
	// 把订单信息转为xml，且把商品信息xml一同并入
	private function Txml($head, $data_array, $service, $str, $lang=''){

		$lang = ($lang != '') ? $lang : $this->config['lang'];
		$xml  = '';

		if($service == 'apiOrderService') $xml = "<?xml version=\"1.0\" encoding=\"utf-8\"?>\n";

		$xml .= "<Request service=\"".htmlspecialchars($service,ENT_QUOTES)."\" lang=\"".htmlspecialchars($lang,ENT_QUOTES)."\">\n";
		$xml .= "<Head>".$head."</Head>\n";
		$xml .= "<Body>\n";

		if($service == 'apiOrderService'){
			$xml .= "<Order";

			if(count($data_array) > 0){
				// 循环创建XML单项
				foreach ($data_array as $k=>$data) {
					$xml .= $this->create_item($k, $data);
				}
			}
			$xml .= " >\n";
			$xml .= $str;
			$xml .= "</Order>\n";

		}else if($service == 'OrderSearchService'){

			$xml .= "<OrderSearch ";
			// $xml .= "orderid=\"".$data_array."\"";
			$xml .= " " .$orderid."=". "\"".htmlspecialchars($data_array,ENT_QUOTES)."\"";
			$xml .= " />\n";

		}

		$xml .= "</Body>";
		$xml .= "</Request>";
		
		// dump($xml);
		return $xml;
	}
 
	//  创建XML单项
	private function create_item($key, $data){

	    // $item .= " " .$key."=". "\"".$data."\"";
	    $item .= " " .$key."=". "\"".htmlspecialchars($data,ENT_QUOTES)."\"";
	 
	    return $item;
	}

	//把商品列表信息转为xml
	private function change($arr){
		$item = "<Cargo";
		foreach($arr as $k=>$it){
			// $item .= " " .$k."=". "\"".$it."\"";
			$item .= " " .$k."=". "\"".htmlspecialchars($it,ENT_QUOTES)."\"";
		}
		$item .= "/>\n";

		return $item;
	}

	//对xml报文进行base64加密
	public function baseXML($xml){

		$checkword = $this->config['checkword'];

		//对xml报文进行base64加密
		$data = base64_encode($xml);

		/*1.拼接报文和密钥的
		2.对拼接后的字符串进行MD5加密
		3.再进行base64加密得到校验码*/
		
		$validateStr = base64_encode(md5($xml.$checkword, false));
		// $validateStr = base64_encode(md5(utf8_encode($xml).$checkword, false));

		return $arr = array($data, $validateStr);
	}

	//对象转数组
	public function object_array($array) {
	    if(is_object($array)) {
	        $array = (array)$array;
	    } if(is_array($array)) {
	        foreach($array as $key=>$value) {
	            $array[$key] = $this->object_array($value);
	        }
	    }
	    return $array;
	}
}