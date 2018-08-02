<?php
/**
 * 香港E特快对接
 */
namespace Api\Controller;
use Think\Controller;
class Kdno11Controller extends Controller{
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
		require_once(dirname(__FILE__).'/Kdno11.class.php');
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
			$js = '{
    "KD": "toMKIL",
    "CID": "2",
    "SID": "20",
    "CMD5": "039232bc404cbff9096dbe9dd079be06",
    "STM": "20180105013347",
    "LAN": "zh-cn",
    "toMKIL": [
        {
            "weight": "0.3",
            "sender": "Ying JDWF",
            "sendAddr": "40559 Encyclopedia Cir, Fremont, CA 94538",
            "sendTel": "510-676-3267",
            "sendcode": "94538",
            "sfid": "330104197610053513",
            "shopType": "Shop",
            "shopName": "JDWF",
            "receiver": "张楠",
            "province": "内蒙古自治区",
            "city": "呼和浩特市",
            "town": "赛罕区",
            "reAddr": "内蒙古自治区 呼和浩特市 赛罕区 敕勒川路街道 敕勒川大街绿地塞尚公馆4号楼",
            "postcode": "010020",
            "reTel": "15354840311",
            "notes": "",
            "premium": 0,
            "MKNO": "MK9898956651US",
            "email": "89088668@qq.com",
            "category1st": "配件",
            "category2nd": "男装钱包",
            "category3rd": "",
            "CID": "1",
            "coin": "CNY",
            "paykind": "",
            "payno": "",
            "paytime": "",
            "idkind": "",
            "idno": "330104197610053513",
            "buyers_nickname": "megaoShop",
            "TranKd": "11",
            "auto_Indent1": 360146,
            "auto_Indent2": "1801031422802",
            "number": 1,
            "price": "132.00",
            "discount": "0.00",
            "Order": [
                {
                    "detail": "dyson戴森测试",
                    "hgid": "06020300",
                    "unit": "只",
                    "specifications": "品名:男装钱包31TL22X062 类别:男装钱包 品牌:TOMMY 款号:0091-5673/02 BROWN",
                    "source_area": "越南",
                    "barcode": "",
                    "number": "1",
                    "catname": "男装钱包",
                    "price": "132.00",
                    "weight": "0.3",
                    "coin": "CNY",
                    "brand": "TOMMY",
                    "hs_code": "01010120"
                }
            ]
        }
    ]
}
';

			$arr = json_decode($js,true);

			$list = $arr['toMKIL'][0];
			// echo '<pre>';
			// print_r($list);die;

			$mothod = '\AUApi\Controller\KdnoConfig\Kdno11';
            $EMS = new $mothod();
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
	public function demo(){
		$no = I('no');

			$mothod = '\AUApi\Controller\KdnoConfig\Kdno11';
            $EMS = new $mothod();

			$res = $EMS->toGetLabel($no);
			print_r($res['ZplShipmentLabel']);
	}
	public function testone()
	{
		$soap = new \SoapClient('http://eexpress-ws.linexsolutions.com/eExpressClientWebService.asmx?wsdl');//网络服务请求地址
		$array = array('Parameter'=>array('loginID' =>'test01' , 'PassWord'=>'123456')); //wrong
		$array = array('loginID' =>'test01' , 'PassWord'=>'123456');  //wrong
		$array = array('loginID' =>'test01' , 'pwd'=>'123456');  //wrong
		$res = $soap->eExpress_Login($array);//查询，返回的是一个结构体
		var_dump($res);
	}
	public function test2()
	{
		
		$soap = new \SoapClient('http://eexpress-ws.linexsolutions.com/eExpressClientWebService.asmx?wsdl');//网络服务请求地址
		$array = array(
			'Token'				=> 'C5A2262097C1F283F6D659DA7CF6923C',
			//'ShipmentNumber'	=> 'EL025731831HK',
			'ShipmentNumber'	=> 'EK246471559HK',
			'LabelType'			=> 0,//'Zpl',
		);
		$res = $soap->eExpress_getlabel($array);//查询，返回的是一个结构体
		var_dump($res);
	}
	public function test3()
	{
		
		$soap = new \SoapClient('http://eexpress-ws.linexsolutions.com/eExpressClientWebService.asmx?wsdl');//网络服务请求地址
		$array = array(
			'Token'				=> 'T150b7adb-9748-4400-ae26-774e0dd3af54',
			'shipment_number'	=> 'EL025731329HK',
		);
		$res = $soap->eExpress_shipment_tracking($array);//查询，返回的是一个结构体
		var_dump(json_encode($res));
	}
	public function testxa()
	{
		//$soap = new \SoapClient('http://202.104.134.94:50081/PackageService.svc?wsdl');//网络服务请求地址
		//$soap = new \SoapClient('http://202.104.134.94:50081/PackageService.svc?wsdl');//网络服务请求地址
		$url = 'https://chongqing-api.11183.hk/packageService.svc?wsdl';
		//$soap = new \SoapClient($url);
		$arr = array(
                'location' => 'https://chongqing-api.11183.hk/packageService.svc?wsdl',
                'uri'      => 'https://chongqing-api.11183.hk/'
                ); 
                //$client = new \SoapClient(null, $arr);
		$opts = array(
        'ssl' => array(
            'ciphers' => 'RC4-SHA',
            'verify_peer' => false,
            'verify_peer_name' => false
        ),
    	);
		$_soapConfig = array(
	        'trace' => 1,       
	        'connection_timeout' => 1200,
	        'keep_alive' => 1,
	        'cache_wsdl' => WSDL_CACHE_NONE,
	        //'features' => SOAP_SINGLE_ELEMENT_ARRAYS,
	        'stream_context' => stream_context_create($opts),
   		 );
		$soap = new \SoapClient($url);//,$_soapConfig);//网络服务请求地址
                //var_dump($client);
		var_dump($soap->__getTypes ());
		var_dump($soap->__getFunctions());
		exit;
		$array = array(
			'Token'				=> 'T150b7adb-9748-4400-ae26-774e0dd3af54',
			'shipment_number'	=> 'EL025731329HK',
		);
		$res = $soap->CreatedAndPrintOrder($array);//查询，返回的是一个结构体
		var_dump(json_encode($res));
	}

}