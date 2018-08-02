<?php
/**
 * 与广州EMS对接
 */
	//基本配置
	$config = array(
		'UserToken' => 'token_123',// 客户唯一值
		'Url'       => "http://www.emspost.cn/apidemo/api.php",// 请求地址 必需
	);

	//默认的订单信息配置
	$Order_config = array(
		'orderid'      => '',		//订单唯一编号
		'contact'      => '',		//收货人
		'mobile'       => '',		//收货人号码
		'email'        => '',		//收件人邮箱
		'country'      => '',       //收货人国家
		'address'      => '',		//收货人地址
		'city'         => '',		//收货人城市
		'province'     => '',		//收货人省份
		'post_code'    => '',		//邮政编码
		'idcard'       => '',		//身份证号码
		'total_weight' => '',		//总重量（kg）
	);

	//默认的商品信息配置
	$Detail = array(
		'name'      => '',	//产品名称
		'short'     => '',	//名称（产品的简称）
		'code'      => '',	//货号
		'taxno'     => '',	//行邮税号
		'unit'      => '',	//产品单位
		'amount'    => '',	//产品数量
		'weight'    => '',	//产品重量
		'netweight' => '',	//净重
		'spec'      => '',	//产品规格 例如：奶粉的规格有600g,800g
		'brand'     => '',	//产品品牌
		'hscode'    => '',	//海关商品报备代码
		'price'     => '',	//单价
		'currency'  => '',	//币别 例如：USD，RMB
	);