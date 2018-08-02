<?php
/**
 * 香港E特快对接
 */
namespace Api\Controller;
use Think\Controller;
class Kdno19Controller extends Controller{
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
		require_once(dirname(__FILE__).'/Kdno19.class.php');
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
			$js = '{"KD":"toMKIL","CID":"2","SID":"20","CMD5":"9adc1944ed67cf9da64d1f19b1f8dad1","STM":"20171201080117","LAN":"zh-cn","toMKIL":[{"weight":"1.5","sender":"Ying CPFS","sendAddr":"225-226,Blk.B,2/F.,Focal Ind.,21 Man Lok St.,Huang Hom,Hong Kong","sendTel":"510-509-8478","sendcode":"94538","sfid":"0","shopType":"Shop","shopName":"CPFS","receiver":"杨希娜","province":"北京","city":"北京市","town":"石景山区","reAddr":"北京 北京市 石景山区 鲁谷街道石景山路甲18号院一号楼","postcode":"100071","reTel":"13691155772","notes":"","premium":0,"MKNO":"","email":"89088668@qq.com","category1st":"成人鞋類","category2nd":"男鞋","category3rd":"","CID":"1","coin":"CNY","paykind":"","payno":"","paytime":"","idkind":"","idno":"0","buyers_nickname":"megaoShop","TranKd":"11","auto_Indent1":360128,"auto_Indent2":"1711281558643","number":1.0,"price":"1200.00","discount":"0.00","Order":[{"detail":"CLARKS 鞋zi11DESERT BOOT","hgid":"22040900","unit":"双","specifications":"品名:鞋zi11DESERT BOOT 类别:男鞋 品牌:CLARKS 款号:000101","source_area":"越南","barcode":"","number":"1","catname":"男鞋","price":"1200.00","weight":"1.5","coin":"CNY","brand":"CLARKS","hs_code":"06020300","tariff_no":"01019900"}]}]}';

			$arr = json_decode($js,true);

			$list = $arr['toMKIL'][0];
			// echo '<pre>';
			// print_r($list);die;
			require('Kdno19.class.php');

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

	public function test4(){

		$no = I('no');

		$mothod = '\AUApi\Controller\KdnoConfig\Kdno19';
        $EMS = new $mothod();

        $res = $EMS->QueryOrderBoxedInfo($no);
        dump($res);
	}

	public function demo(){
		$str = '江苏';
    	$arr = array('1'=>'北京市','2'=>'天津市','3'=>'河北省','4'=>'山西省','5'=>'内蒙古自治区','6'=>'辽宁省','7'=>'吉林省','8'=>'黑龙江省','9'=>'上海市','10'=>'江苏省','11'=>'浙江省','12'=>'安徽省','13'=>'福建省','14'=>'江西省','15'=>'山东省','16'=>'河南省','17'=>'湖北省','18'=>'湖南省','19'=>'广东省','20'=>'广西壮族自治区','21'=>'海南省','22'=>'重庆市','23'=>'四川省','24'=>'贵州省','25'=>'云南省','26'=>'西藏自治区','27'=>'陕西省','28'=>'甘肃省','29'=>'青海省','30'=>'宁夏回族自治区','31'=>'新疆维吾尔自治区','32'=>'香港特别行政区','33'=>'澳门特别行政区',
    	);
    	// array_flip($arr);
    	foreach($arr as $key=>$item){
    		if(preg_match("/".$str."/i", $item)){
    			echo $key;break;
    		}else{
    			continue;
    		}
    	}
    	// echo 'none';
	}

	public function demo2(){
		require_once(dirname(__FILE__).'\Kdno19.function.php'); //功能函数
		$str = '510-509-8478';
		echo hidtel($str);die;

		$str = 'Ying CPFS';
		echo substr_cut($str);
		// $province = '北京';
		// $city = '北京市';
		// $town = '石景山区';
		// $str = '北京 北京市 石景山区 鲁谷街道石景山路甲18号院一号楼';
		// echo hidaddress($str, $province, $city, $town);
	}

	public function test2(){

		$mothod = '\AUApi\Controller\KdnoConfig\Kdno19';
        $EMS = new $mothod();

		$list = array('BZ002207334US','BZ002207348US','BZ002207379US','BZ002207382US');
		// $list = str_replace('，',',', $list);

		// $list = preg_replace("/(\n)|(\s)|(\t)|(\')|(')|(，)|(\.)|(\/)|(\\\)|(、)|(；)|(;)/",',',$list);
		// dump($list);die;

		$res = $EMS->OrderesBoxed($list, 2);

		// // 日志记录
  //       $DataNote               = new \Libm\DataNotes\DataNote();
        
  //       $DataNote->RequestData  = '11';//json_encode($list);
  //       $DataNote->ResponseData = '22';//json_encode($res);
  //       $DataNote->save_dir     = (defined('API_ABS_FILE')) ? API_ABS_FILE.'/OrderesBoxed/' : 'D:/www/upfiles/c/Api/OrderesBoxed/';
  //       $DataNote->file_name    = 'OrderesBoxedRecord.txt';
  //       $DataNote->save();

		if(isset($res['state']) && $res['state'] == '0'){
			dump($res);
		}

		if(isset($res['OrderesBoxedResult'])){
			$OrderesBoxedResult = $res['OrderesBoxedResult'];
		}
		dump($OrderesBoxedResult);die;
		if(isset($OrderesBoxedResult['ResponseResult'])){
			if($OrderesBoxedResult['ResponseResult'] == 'Failure'){
				dump(array('state'=>'no', 'msg'=>$OrderesBoxedResult['ResponseError']['ShortMessage']));
			}else{
				dump(array('state'=>'yes', 'msg'=>$OrderesBoxedResult['ResponseError']['ShortMessage']));
			}
		}
	}

	public function test3(){
		$json = '{"id":"705445","new_weight":"0.50","new_cost":"5.00","new_freight":"5.00","new_discount":"0","uid":"43","xml":{"KD":"toMKIL","CID":"1","SID":"20","CMD5":"3cb1f30ce88d45493e55d048411590d6","STM":"20180409093025","LAN":"zh-cn","toMKIL":[{"MKNO":"MK883489358US","TransitNo":"MKIL","Weight":"0.50","place":"美国北加州","terminal_code":"FE738727-ECAD-4AA7-EFAD-55D5D72182CB","operatorId":"43","operatorName":"rong"}]}}';
		$info = json_decode($json, true);
		dump($info);

		$id           = (isset($info['id'])) ? trim($info['id']) : '';//tran_list.id
		$operator_id  = (isset($info['uid'])) ? trim($info['uid']) : '';//操作人id
		$new_weight   = (isset($info['new_weight'])) ? sprintf("%.2f", trim($info['new_weight'])) : '';//最新称重重量
		$new_cost     = (isset($info['new_cost'])) ? sprintf("%.2f", trim($info['new_cost'])) : '';//最新消费金额
		$new_freight  = (isset($info['new_freight'])) ? sprintf("%.2f", trim($info['new_freight'])) : '';//最新运费
		$new_discount = (isset($info['new_discount'])) ? sprintf("%.2f", trim($info['new_discount'])) : '';//最新优惠金额
		$xml          = (isset($info['xml'])) ? $info['xml'] : '';//揽收报文  base64加密的json报文

		if($id == '' || $operator_id == '' || $new_weight == '' || $new_cost == '' || $new_freight == '' || $new_discount == '' || !is_array($xml)){
			dump(array('state'=>'no','msg'=>'缺少必要参数', 'lng'=>'lack_paramer'));
		}
	}
}