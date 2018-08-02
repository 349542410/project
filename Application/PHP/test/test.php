<?php
	// $str = '{"KD":"toMKIL","CID":"2","SID":"20","CMD5":"cd8656fc2c97bc2b0ab0030de3f46d51","STM":"20160919035524","LAN":"zh-cn","toMKIL":[{"weight":"1.0","sender":"Ying ALD","sendAddr":"40559 Encyclopedia,Fremont,CA","sendTel":"510-509-8478","sendcode":"94538","sfid":"111222200405055234","shopType":"Shop","shopName":"ALD","receiver":"测试","reAddr":"广东省 广州市 番禺区 保利大都汇A3栋1210","province":"广东省","city":"广州市","town":"番禺区","postcode":"510400","reTel":"13535355353","notes":"","premium":0,"MKNO":"","email":"89088668@qq.com","category1st":"成人鞋類","category2nd":"女鞋","category3rd":"","CID":"1","coin":"CNY","paykind":"支付宝","payno":"1234567777","paytime":"2016-09-18 17:40:20","idkind":"ID","idno":"111222200405055234","TranKd":"5","auto_Indent1":34,"auto_Indent2":"1609181748327","number":1,"price":"1490.00","Order":[{"detail":"靴 CLARISA SUEDE 34E0899-BLK","hgid":"310916629630000008","unit":"件","specifications":"鞋底材料:橡胶 鞋面材料:绒面 款式:靴 ","source_area":"越南","number":1,"catname":"女鞋","price":"1490.00","weight":0,"coin":"CNY","brand":"Calvin Klein","hs_code":"6403511190","barcode":"6403511190"}]}]}';
	// $arr = json_decode($str,true);

	//接收的
	$arr = array(
		'MKNO'       => '',
		'CID'        => '',
		'state'      => '',
		'lang'       => 'zh-cn',
		'toMkil'     => array(
						'Weight'=>array(
							'time'          =>'2016-09-19',
							'content'       =>'XXXXXXXXXX',
							'place'         =>'XXXXXXXXXX',
							'operator'      =>'XXXXXXXXXX',
							'optime'        =>'2016-09-19 08:55:23',
							'machineNumber' =>'32655445',
							'status'        =>'1000',//tran_list.Il_state, logs.state, il_log.status
							'weight'        =>'12kg',
						),
						'Transit'=>array(
							'time'          =>'2016-09-19',//时间
							'content'       =>'XXXXXXXXXX',//内容
							'place'         =>'XXXXXXXXXX',//操作地点
							'operator'      =>'XXXXXXXXXX',//操作员
							'optime'        =>'2016-09-19 08:55:23',//操作时间
							'machineNumber' =>'32655125',//机器编号
							'status'        =>'1000',//物流状态 //tran_list.Il_state, logs.state, il_log.status
							'transit'       =>'SF',	//中转承接公司
							'tranNum'       =>'221014000134',//中装单号
						),
		),
	);
	$str = json_encode($arr);
	// echo '<pre>';
	// print_r($arr);
	echo $str;

	//返回
	$re_arr = array(
		'Title' => 'OK',
		'time'  => '2016-09-19 18:55:23',
		'Type'  => array(
					'Weight'=>array(
						'Head' =>'OK',//Head为ERR的时候，才会有ERROR错误信息返回
						'Code' =>'200',
					),
					'Transit'=>array(
						'Head'  =>'ERR',
						'Code'  =>'400',
						'ERROR' =>'XXXXXXXXXX',
					),
		),
	);
