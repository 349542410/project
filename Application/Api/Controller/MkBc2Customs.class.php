<?php
/**
 * 美快BC优选2  api
 * 创建时间：2017-04-21
 * 修改时间：2017-04-21
 * created by Jie
 * 指导文档：纵腾跨境贸易通关辅助系统API文档.pdf  的  1. 提交初始订单
 * 功能包括： 报关
 * 
 */
require_once('MKBc2Customs.function.php'); //功能函数
class MKBc2CustomsApi{

	//默认配置
	protected $config = array(
		'PlatformCode'     => '35012619EK',		//载单平台编码 
		'EcpName'          => '测试店铺1',		//店铺名 
		'Freight'          => '0',		//运费 
		'Other'            => '0',		//其他费用 
		'ConsigneeCountry' => '142',	//收货人所在国   海关国家参数 
		'IdType'           => '1',		//证件类型   进口必填, 出口非必填 1-身份证 2-军官证  3-护照  4-其它 
		'OrderType'        => '0',		//订单类型 必填; 0-普通订单;1-保税订单;2-直邮订单；默认填 0 
		'ShopGoodsNo'      => 'test1',	//店铺货号
		'url'              => 'http://218.106.146.52:60102/api/order/AddOriginalOrder',	//请求地址
	);

	//公有请求数据
	protected $commonRequestData = array(
		'Version'       => '1.0.0.1',		//Api 版本号 
		'UserId'        => 'RI3kZgbp33c=',	//用户标识
		'UserGroupCode' => 'wnV6C0PsEh8CtVBjIc2yxw==',	//用户组编码
		'Key'           => '8d969eef6ecad3c29a3a629280e686cf0c3f5d5a86aff3ca12020c923adc6c92',	//用户访问 Key
		'RequestId'     => '123214234',	//请求唯一标识
	);

    // 订单报关
    public function request($list){

    	$reqtime = date('Y-m-d H:i:s');

		// dump($list);die;

    	$ClientOriginalOrder = array(
			'PlatformCode'       => $this->config['PlatformCode'],  //载单平台编码 
			'EcpName'            => $this->config['EcpName'],   //店铺名
			'Charge'             => $list['price'],//总费用
			'GoodsValue'         => $list['price'],//货值
			'Freight'            => $this->config['Freight'],//运费
			'Other'              => $this->config['Other'],//其他费用
			'Customer'           => '',//客户姓名    N
			'Consignee'          => $list['receiver'],//收货人名称
			'ConsigneeAddress'   => $list['reAddr'],//收货人地址
			'ConsigneeCountry'   => $this->config['ConsigneeCountry'],//收货人所在国   海关国家参数
			'ConsigneeTelephone' => $list['reTel'],//收货人电话
			'Consigneeprovince'  => $list['province'],//收货人省份
			'Consigneecity'      => $list['city'],//收货人城市
			'Consigneecounty'    => $list['town'],//收货人区/县
			'ConsigneeZipCode'   => $list['postcode'],//收货人邮编    N
			'ConsigneeDitrict'   => $list['postcode'],//收货人行政区域代码   N
			'BuyerRegNo'         => $list['reTel'],//订购人注册号
			'BuyerName'          => $list['receiver'],//订购人姓名
			'IdType'             => $this->config['IdType'],//证件类型   进口必填, 出口非必填 1-身份证 2-军官证  3-护照  4-其它
			'CustomerId'         => $list['idno'],//证件号码
			'Raworderno'         => $list['payno'].rand(10000,20000),//原始单号
			'OrderNo'            => $list['payno'],//支付订单号
			'OrderDate'          => (trim($list['paytime']) != '') ? $list['paytime'] : $list['optime'],//订单日期
			'OrderType'          => $this->config['OrderType'],//订单类型 必填; 0-普通订单;1-保税订单;2-直邮订单；默认填 0
			'PaymentNo'          => '',//支付交易号   N
			'PayAmt'             => $list['price'],//支付金额
			'PayDate'            => (trim($list['paytime']) != '') ? $list['paytime'] : $list['optime'],//支付时间
			'Note'               => $list['notes'],//备注   N
    	);

    	$order = $list['Order'];
    	$OrderItems = array();
    	foreach($order as $key=>$item){
			$OrderItems[$key]['ShopGoodsNo'] = $this->config['ShopGoodsNo'];  //店铺货号
			$OrderItems[$key]['Price']       = $item['price']; //成交单价
			$OrderItems[$key]['Quantity']    = $item['number']; //数量
			$OrderItems[$key]['Discount']    = '0';  //折扣优惠
    	}

		$ClientOriginalOrder['OrderItems'] = $OrderItems;

		$data = array();
		$data['ClientOriginalOrder'] = $ClientOriginalOrder;
		$data['Version']             = $this->commonRequestData['Version']; //Api 版本号 
		$data['UserId']              = $this->commonRequestData['UserId'];	//用户标识
		$data['UserGroupCode']       = $this->commonRequestData['UserGroupCode'];	//用户组编码
		$data['Key']                 = $this->commonRequestData['Key'];	//用户访问 Key
		$data['RequestId']           = $this->commonRequestData['RequestId'];//请求唯一标识
		$data['RequestTime']         = $reqtime;//请求时间 

		$json = json_encode($data);

        //调用 发送类
        $http = new \Org\MK\HTTP();

        $rs   = $http->post($this->config['url'], $json);
        $obj = json_decode($rs);

        $arr = object_array($obj);// 对象转数组

        $this->save($list,$arr,$reqtime);

        return $arr;
        // dump($arr);

    }

    // 保存/更新 报关操作记录
    public function save($list,$arr,$reqtime){

    	$state = $arr['Result'];

    	//更新报关状态
		$res = M('TranList')->where(array('MKNO'=>$list['MKNO']))->setField('custom_status',$state);

		//检查报关信息是否已存在
		$check_trainer = M('Trainer')->where(array('LogisticsNo'=>$list['STNO']))->find();

		// 新增或更新报关信息
		$data = array();
		if(!$check_trainer){
			$data['LogisticsNo'] = $list['STNO'];
			$data['Status']      = $state;
			$data['Result']      = ($arr['Result'] == '1') ? '推送成功' : $arr['Error']['LongMessage'];
			$data['CreateTime']  = $reqtime;
			$data['TranKd']      = $list['TranKd'];
			M('Trainer')->add($data);

		}else{//更新
			$data['Status']      = $state;
			$data['Result']      = ($arr['Result'] == '1') ? '推送成功' : $arr['Error']['LongMessage'];
			$data['CreateTime']  = $reqtime;
			M('Trainer')->where(array('id'=>$check_trainer['id']))->save($data);
		}

		// 保存报关操作记录
		$log = array();
		$log['LogisticsNo'] = $list['STNO'];
		$log['Status']      = $state;
		$log['content']     = ($arr['Result'] == '1') ? '推送成功' : $arr['Error']['LongMessage'];
		$log['CreateTime']  = $reqtime;
		M('TrainerLogs')->add($log);

    }
}