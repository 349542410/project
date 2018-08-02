<?php
/**
 * 与香港E特快下单对接 端口测试用
 */
namespace Api\Controller;
use Think\Controller;
class Kdno7Controller extends Controller{

	//配合测试用的数据
	function _initialize(){
		// echo $_SERVER['HTTP_HOST'];
		if($_SERVER['HTTP_HOST'] == 'mkapi.megao.hk:83'){
			$js = '{"KD":"toMKIL","CID":"2","SID":"20","CMD5":"d38704931ee1b2ad32c9d30eb6057e70","STM":"20161219033459","LAN":"zh-cn","toMKIL":[{"weight":"1.01","sender":"Ying JD","sendAddr":"40559 Encyclopedia,Fremont,CA","sendTel":"510-509-8478","sendcode":"94538","sfid":"370781197708283029","shopType":"Shop","shopName":"JD","receiver":"谢春","reAddr":"湖北省 武汉市 江夏区 金融港二路6号","province":"湖北省","city":"武汉市","town":"江夏区","postcode":"430200","reTel":"13720348560","notes":"","premium":0,"MKNO":"MK81000054US","email":"89088668@qq.com","category1st":"成人鞋類","category2nd":"女鞋","category3rd":"","CID":"1","coin":"CNY","paykind":"支付宝","payno":"2525215245225225","paytime":"2016-12-14 12:23:34","idkind":"ID","idno":"370781197708283029","buyers_nickname":"sd23","TranKd":"5","auto_Indent1":113,"auto_Indent2":"1612141124153","number":2,"price":"1900.00","discount":"0.00","Order":[{"detail":"鞋D VENERE D34P8P 02166 C6029","hgid":"06010100","unit":"双","specifications":"鞋底材料:EVA发泡胶 鞋面材料:纺织 款式:中帮 ","source_area":"越南","barcode":"1111102864","number":1,"catname":"女鞋","price":500,"weight":"1.01","coin":"CNY","brand":"GEOX","hs_code":""}]}]}';

			$arr = json_decode($js,true);
			
			$list = $arr['toMKIL'][0];
			
			// echo '<pre>';
			// print_r($arr);die;

			// require('Kdno7.class.php');

			// $EMS = new \Kdno();
			// $this->ems = $EMS;
			$this->list = $list;
		}else{
			die('error');
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
		require_once(dirname(__FILE__).'/Kdno7.class.php');
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
	// 新增   +
	public function add(){
		if($_SERVER['HTTP_HOST'] != 'mkapi.megao.hk:83'){
			die('error');
		}

		$list = $this->list;
		require('Kdno7.class.php');

		$EMS = new \Kdno();
		$res = $EMS->AddInChinaOrder($list);
		echo '<pre>';
		print_r($res);

		$STEXT		= $EMS->get(); 	// 返回快递其它内容
		$STNO 	= $EMS->no();  	// 返回快递号码	
		print_r($STNO);
		print_r($STEXT);
	}

	// 推送支付信息
	public function SendPay(){
		if($_SERVER['HTTP_HOST'] != 'mkapi.megao.hk:83'){
			die('error');
		}

		$list = $this->list;

		require('Kdno7.class.php');
		$EMS = new \Kdno();

		$res = $EMS->SendPaymentInfo($list);
		
		print_r($res);
	}

	// 面单打印  +
	public function GetByOrder(){
		if($_SERVER['HTTP_HOST'] != 'mkapi.megao.hk:83'){
			die('error');
		}

		$list = $this->list;
		$EMS = $this->ems;

		$res = $EMS->GetByOrderCode($list);
		print_r($res);
	}

	// 跟踪包裹(物流信息) 用EMS单号查询  +
	public function GetExpressTrack(){
		if($_SERVER['HTTP_HOST'] != 'mkapi.megao.hk:83'){
			die('error');
		}

		$list = $this->list;
		
		$EMS = $this->ems;
		$res = $EMS->GetExpressTrack($list);
		print_r($res);
	}

	// 商品系统备案  +
	public function ApplyGoods(){
		C('SHOW_PAGE_TRACE','');//直接在此关闭SHOW_PAGE_TRACE
		
		// 商品报备用新的json数据格式
		$js = '{"CID":"1","Code":"HWG000342","Name":"\u978bD VENERE D34P8P 02166 C6029","Specifications":"\u978b\u5e95\u6750\u6599:EVA\u53d1\u6ce1\u80f6 \u978b\u9762\u6750\u6599:\u7eba\u7ec7 \u6b3e\u5f0f:\u4e2d\u5e2e ","PostTaxCode":"01019900","OrginCountryName":"\u8d8a\u5357","NTWeight":"1.01","RefPrice":"500","Unit":"\u53cc","Currency":"\u4eba\u6c11\u5e01","HsCode":"3265315623","CIQTypeCode":"","ShelfGName":"\u978bD VENERE D34P8P 02166 C6029","Brand":"GEOX","IsNotGift":true,"Quality":"\u5408\u683c","Manufactory":"Made in US","GSWeight":"1.51","CiqGoodsNo":""}';

		$list = json_decode($js,true);

		require('Kdno7.class.php');
		
		$EMS = new \Kdno();

		$res = $EMS->ApplyGoodsRecord($list);
		print_r($res);
	}

	// 提单绑定(发货通知)  +
	public function UpLoadTotalRelation(){
		header('Content-type:text/json;charset=UTF-8');	//设置输出格式

		if($_SERVER['HTTP_HOST'] != 'mkapi.megao.hk:83'){
			die('error');
		}

		$number  = 'kfks23365234324';
		$re_time = '1488959704';
		$country = '香港';
		$id      = '86';
		$no      = 'TMK032';

    	$where = array();
		$where['noid'] = array('eq',$id);
		$all = M('TranList')->field('auto_Indent1,auto_Indent2,STNO,weight')->where($where)->select();	//总数
		// return $all;
        if(count($all) == 0){
            return array('Status'=>'false', 'ErrorMessage'=>'该批次号暂无数据录入，禁止操作');
        }

        $total_weight = 0;
		// 组装ShipmentNumbers属性($list)
		$list = array();
		foreach($all as $key=>$item){
			// $list[$key]['BagNo']       = '1';
			// $list[$key]['ReferenceId'] = $item['auto_Indent2']."_".$item['auto_Indent1'];
			// $list[$key]['TrackingNo']  = $item['STNO'];
			// $list[$key]['Weight']      = $item['weight'];
			$list[$key] = $item['STNO'];//子运单号集合
			$total_weight += floatval($item['weight']);//总重量
		}
		// return $list;
		// return sprintf("%.2f", $total_weight);

		$total_weight = sprintf("%.2f", $total_weight);
		// dump($total_weight);die;
		$EMS = $this->ems;
		$res = $EMS->UpLoadTotalRelation($list, $number, $re_time, $country, $total_weight);
		print_r($res);		
	}
}