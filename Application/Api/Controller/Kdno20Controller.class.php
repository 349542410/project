<?php
/**
 * 
 */
namespace Api\Controller;
use Think\Controller;
class Kdno20Controller extends Controller{
	public function read()
	{
		$jn 	= new \Org\MK\JSON;
		$data 	= $jn->get();
		if(!is_array($data)){
			echo $jn->respons("","",null,0,L("SYSERROR0"));
			exit;
		}
		//将物流号设置到$data2中
		$data2 		= $data['toMKIL'];
		require_once(dirname(__FILE__).'/Kdno20.class.php');
		$Kdno 		= new \Kdno();
		//直接读取$data['ToMK']拆成单个数据
		$i = 0;
		foreach ($data2 as $key => $value) {
			$value['MKNO']	 	= 'MK' . time();
			$Kdno->data($value);
			$STEXT  = $Kdno->get(); 	// 返回快递其它内容
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

	// 测试用
	public function test(){
		if($_SERVER['HTTP_HOST'] == 'mkapi.app.megao.hk:83'){
			$js = '{"KD":"toMKIL","CID":"2","SID":"20","CMD5":"9adc1944ed67cf9da64d1f19b1f8dad1","STM":"20171201080117","LAN":"zh-cn","toMKIL":[{"weight":"1.5","sender":"Ying CPFS","sendAddr":"225-226,Blk.B,2/F.,Focal Ind.,21 Man Lok St.,Huang Hom,Hong Kong","sendTel":"18664619194","sendcode":"94538","sfid":"0","shopType":"Shop","shopName":"CPFS","receiver":"杨希娜","province":"北京","city":"北京市","town":"石景山区","reAddr":"北京 北京市 石景山区 鲁谷街道石景山路甲18号院一号楼","postcode":"100071","reTel":"13691155772","notes":"","premium":0,"MKNO":"MK883093357US","email":"89088668@qq.com","category1st":"成人鞋類","category2nd":"男鞋","category3rd":"","CID":"1","coin":"CNY","paykind":"","payno":"","paytime":"","idkind":"","idno":"420105197706137939","buyers_nickname":"megaoShop","TranKd":"11","auto_Indent1":360128,"auto_Indent2":"1711281558643","number":1.0,"price":"1200.00","discount":"0.00","Order":[{"detail":"CLARKS 鞋zi11DESERT BOOT","hgid":"22040900","unit":"双","specifications":"品名:鞋zi11DESERT BOOT 类别:男鞋 品牌:CLARKS 款号:000101","source_area":"越南","barcode":"","number":"1","catname":"男鞋","price":"1200.00","weight":"1.5","coin":"CNY","brand":"CLARKS","hs_code":"06020300"}]}]}';

			$arr = json_decode($js,true);

			$list = $arr['toMKIL'][0];
			// echo '<pre>';
			// print_r($list);die;
			require('Kdno20.class.php');

			$EMS = new \Kdno();

			// $res = $EMS->eExpress_Login();
			// dump($res);die;
			$res = $EMS->data($list);
			// dump($res);die;
			$STEXT  = $EMS->get(); 	// 返回快递其它内容
			$STNO 	= $EMS->no();  	// 返回快递号码	
			dump($res);
			dump($STNO);
			dump(json_decode(base64_decode($STEXT)));
		}
	}

}