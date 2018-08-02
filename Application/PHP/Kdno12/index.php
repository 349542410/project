<?php
	header('Content-type:text/html;charset=UTF-8');	
	$config = array(
		'customerCode'    => 'OSMS_1',
		'checkword'       => 'fc34c561a34f',
		'imgUploadAction' => "http://osms.sit.sf-express.com:2080/osms/hessian/uploadIdentityService",
	);

		// $url = "http://pic6.huitu.com/res/20130116/84481_20130116142820494200_1.jpg";
		$url = "http://file.megao.hk/c/Admin/Uploads/Person/2017-06-12/tx(1).jpg";
		$img = file_get_contents($url);
		// dump($img);die;
		$bm  = unpack('C*',$img);
		$str = call_user_func_array('pack',array_merge(array('C*'),$bm));

		$str = base64_encode($str);//base64加密处理

		$arr['name']   = '谢春光';
		$arr['phone']  = '13720348560';
		$arr['cardId'] = '370781197708283029';
		$arr['bno']    = '070034530723';
		$arr['image']  = $str;//图片需经过base64加密处理

		$xml = json_encode($arr);

		$data = base64_encode($xml);

		
	// $xml = '{"name":"李三","phone":"13898744567","cardId":"430879199415451874","bno":"44597865445600","image":"'.$str.'"}';
	// // print_r($xml);die;
	// $data = base64_encode($xml);



	$checkword = $config['checkword'];

	$validateStr = base64_encode(md5($xml.$checkword, false));
	
	$customerCode = $config['customerCode'];

	include_once 'HessianPHP/HessianClient.php';

	$http = new HessianClient($config['imgUploadAction']);

    $result = $http->uploadIdentity($data, $validateStr, $customerCode);
	
    echo $result;

