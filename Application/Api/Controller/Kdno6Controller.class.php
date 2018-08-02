<?php
/**
 * 与香港E特快下单对接 端口测试用
 */
namespace Api\Controller;
use Think\Controller;
class Kdno6Controller extends Controller{

	//配合测试用的数据
	function _initialize(){
		// echo $_SERVER['HTTP_HOST'];
		if($_SERVER['HTTP_HOST'] == 'mkapi.app.megao.hk:83'){
			$js = '{"KD":"toMKIL","CID":"2","SID":"20","CMD5":"d38704931ee1b2ad32c9d30eb6057e70","STM":"20161219033459","LAN":"zh-cn","toMKIL":[{"weight":"1.01","sender":"Ying JD","sendAddr":"40559 Encyclopedia,Fremont,CA","sendTel":"510-509-8478","sendcode":"94538","sfid":"370781197708283029","shopType":"Shop","shopName":"JD","receiver":"谢春","reAddr":"湖北省 武汉市 江夏区 金融港二路6号","province":"湖北省","city":"武汉市","town":"江夏区","postcode":"430200","reTel":"13720348560","notes":"","premium":0,"MKNO":"MK81000053US","email":"89088668@qq.com","category1st":"成人鞋類","category2nd":"女鞋","category3rd":"","CID":"1","coin":"CNY","paykind":"支付宝","payno":"2525215245225225","paytime":"2016-12-14 12:23:34","idkind":"ID","idno":"370781197708283029","buyers_nickname":"sd23","TranKd":"5","auto_Indent1":113,"auto_Indent2":"1612141124153","number":2,"price":"1900.00","discount":"0.00","Order":[{"detail":"鞋D VENERE D34P8P 02166 C6029","hgid":"06010100","unit":"双","specifications":"鞋底材料:EVA发泡胶 鞋面材料:纺织 款式:中帮 ","source_area":"越南","barcode":"1111102864","number":1,"catname":"女鞋","price":500,"weight":"1.01","coin":"CNY","brand":"GEOX","hs_code":""},{"detail":"男鞋BASIC ROLL-TOP 6634A","hgid":"06010100","unit":"打","specifications":"类别:女式 款式:背心 织造方式:机织 成分含量:含丝70%及以上 ","source_area":"越南","barcode":"1111110121","number":1,"catname":"男鞋","price":130.01,"weight":"1.01","coin":"CNY","brand":"TIMBERLAND","hs_code":""}]}]}';

			$arr = json_decode($js,true);
			
			$list = $arr['toMKIL'][0];
			// echo '<pre>';
			// print_r($arr);die;
			require('Kdno6.class.php');

			$EMS = new \Kdno();
			$this->ems = $EMS;
			$this->list = $list;
		}
		
	}

	// EMS下单 正式用  测试时如无特殊原因不可修改此方法
	public function read()
	{	
		C('SHOW_PAGE_TRACE','');//直接在此关闭SHOW_PAGE_TRACE
		$jn 	= new \Org\MK\JSON;
		$data 	= $jn->get();
		if(!is_array($data)){
			echo $jn->respons("","",null,0,L("SYSERROR0"));
			exit;
		}
		//将物流号设置到$data2中
		$data2 		= $data['toMKIL'];
		require_once(dirname(__FILE__).'/Kdno6.class.php');
		$Kdno 		= new \Kdno();
		//直接读取$data['ToMK']拆成单个数据
		$i = 0;
		foreach ($data2 as $key => $value) {
			$value['MKNO']	 	= 'MK' . time();
			$Kdno->data($value);
			$STEXT		= $Kdno->get(); 	// 返回快递其它内容
			$STNO 	= $Kdno->no();  	// 返回快递号码	
			$back[$i]=Array(
				"auto_Indent1" => $value['auto_Indent1'],
				"auto_Indent2" => $value['auto_Indent2'],
				"MKNO"         => $value['MKNO'],
				"STNO"         => $STNO,
				'LineName'     => 'lname',
				"STEXT"        => isset($STEXT)?$STEXT:'',
				'sfinfo'       => isset($sfinfo)?$sfinfo:'',
				'jdate'        => date('Y-m-d'),
				"Success"      => true,
				"LOGSTR"       => '',
				'CID'          => $value['CID'], 
			);
			$i++;
		}
		echo $jn->respons( $data['KD'], $data['CID'],$back);
	}

//======================  以下方法均为测试用=======================
	// 新增
	public function add(){
		if($_SERVER['HTTP_HOST'] != 'mkapi.megao.hk:83'){
			die('error');
		}

		$list = $this->list;
		$EMS = $this->ems;
		$res = $EMS->addOrUpdateOrder($list);
		print_r($res);

		$STEXT		= $EMS->get(); 	// 返回快递其它内容
		$STNO 	= $EMS->no();  	// 返回快递号码	
		print_r($STNO);
		print_r($STEXT);
	}

	// 删除
	public function delete(){
		if($_SERVER['HTTP_HOST'] != 'mkapi.megao.hk:83'){
			die('error');
		}

		$list = $this->list;
		
		$EMS = $this->ems;
		$res = $EMS->deleteOrder($list);
		print_r($res);
	}

	// 跟踪包裹(物流信息) 用EMS单号查询
	public function track(){
		if($_SERVER['HTTP_HOST'] != 'mkapi.megao.hk:83'){
			die('error');
		}

		$list = $this->list;
		
		$EMS = $this->ems;
		$res = $EMS->trackOrder($list);
		print_r($res);
	}

	// 查询清关状态
	public function getTaxStatus(){
		if($_SERVER['HTTP_HOST'] != 'mkapi.megao.hk:83'){
			die('error');
		}

		$list = $this->list;
		
		$EMS = $this->ems;
		$res = $EMS->getTaxStatus($list);
		print_r($res);
	}

	// 查询运送方式编码
	public function listShipwayCodes(){
		if($_SERVER['HTTP_HOST'] != 'mkapi.megao.hk:83'){
			die('error');
		}

		$EMS = $this->ems;
		$res = $EMS->listShipwayCodes();
		print_r($res);
	}

}