<?php
/**
 * IBS平台客户API对接口 与ERP对接测试  端口测试用
 */
namespace Api\Controller;
use Think\Controller;
class Kdno12Controller extends Controller{
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
		require_once(dirname(__FILE__).'/Kdno12.class.php');
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

	// 测试用
	public function test(){
		if($_SERVER['HTTP_HOST'] == 'mkapi.megao.hk:83'){
			$js = '{"KD":"toMKIL","CID":"2","SID":"20","CMD5":"d38704931ee1b2ad32c9d30eb6057e70","STM":"20161219033459","LAN":"zh-cn","toMKIL":[{"weight":"1.01","sender":"Ying JD","sendAddr":"40559 Encyclopedia,Fremont,CA","sendTel":"510-509-8478","sendcode":"94538","sfid":"370781197708283029","shopType":"Shop","shopName":"JD","receiver":"谢春光","reAddr":"湖北省 武汉市 江夏区 金融港二路6号","province":"湖北省","city":"武汉市","town":"江夏区","postcode":"430200","reTel":"13720348560","notes":"","premium":0,"MKNO":"MK800256805US","email":"89088668@qq.com","category1st":"成人鞋類","category2nd":"女鞋","category3rd":"","CID":"1","coin":"CNY","paykind":"支付宝","payno":"2525215245225225","paytime":"2016-12-14 12:23:34","idkind":"ID","idno":"370781197708283029","buyers_nickname":"sd23","TranKd":"5","auto_Indent1":205,"auto_Indent2":"1612141122114","number":2,"price":"1900.00","discount":"0.00","Order":[{"detail":"鞋D VENERE D34P8P 02166 C6029","hgid":"06010100","unit":"双","specifications":"鞋底材料:EVA发泡胶 鞋面材料:纺织 款式:中帮 ","source_area":"越南","barcode":"1111102864","number":1,"catname":"女鞋","price":500,"weight":"1.01","coin":"CNY","brand":"GEOX","hs_code":"6598982","size":"USM9-10.5，USW10-11.5"},{"detail":"男鞋BASIC ROLL-TOP 6634A","hgid":"06010100","unit":"打","specifications":"类别:女式 款式:背心 织造方式:机织 成分含量:含丝70%及以上 ","source_area":"越南","barcode":"1111110121","number":1,"catname":"男鞋","price":135.01,"weight":"1.01","coin":"CNY","brand":"TIMBERLAND","hs_code":"4423423","size":"34（US W 3.5 - 4）"}]}]}';

			$arr = json_decode($js,true);

			$list = $arr['toMKIL'][0];
			// echo '<pre>';
			// print_r($list);die;
			require('Kdno12.class.php');

			$EMS = new \Kdno();

			$res = $EMS->data($list);
			dump($res);
		}
	}

	public function demo(){

		$url = ADMIN_FILE."/Uploads/Person/2017-06-12/37001578593e2c6068db5.jpg";
		// $url = "http://pic6.huitu.com/res/20130116/84481_20130116142820494200_1.jpg";
		
		$str = $this->get_img_byte($url);

		$arr['name']   = '谢春光';
		$arr['phone']  = '13720348560';
		$arr['cardId'] = '370781197708283029';
		$arr['bno']    = '070034530723';
		$arr['image']  = $str;//图片需经过base64加密处理
		// $arr['image']  = '/9j/4AAQSkZJRgABAgAAAQABAAD/2wBDAAgGBgcGBQgHBwcJCQgKDBQNDAsLDBkSEw8UHRofHh0aHBwgJC4nICIsIxwcKDcpLDAxNDQ0Hyc5PTgyPC4zNDL/2wBDAQkJCQwLDBgNDRgyIRwhMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIy';//base64_encode($img);//图片需经过base64加密处理
/*
		echo $arr['image'];
		echo '<br/>';
		echo json_encode($arr);
		die();
*/
		require('Kdno12.class.php');
		$EMS = new \Kdno();

		$res = $EMS->uploadIMG($arr);
		dump($res);
	}

	function get_img_byte($url){
		$img = file_get_contents($url);
		// dump($img);die;
		$bm  = unpack('C*',$img);
		$str = call_user_func_array('pack',array_merge(array('C*'),$bm));

		return base64_encode($str);//base64加密处理
		//图片内容写入新的文件
		// file_put_contents(ADMIN_ABS_FILE."/Uploads/Person/tx(2).txt", $str);die;
	}
	
	private function base64EncodeImage($image_file){
		$base64_image	= '';
		$image_info		= getimagesize($image_file);
		$image_data		= fread(fopen($image_file, 'r'), filesize($image_file));
		$base64_image	= chunk_split(base64_encode($image_data));
		return $base64_image;
	}
}