<?php
//============= 签名 ===========
	$data = file_get_contents('./file/KJ881111_test1_2016061609460872271.xml');
	// var_dump($data);die;
extension_loaded('openssl') or die('php需要openssl扩展支持'); 
	// 测试数据
	// $data = 'If you are still new to things, we’ve provided a few walkthroughs to get you started.';

	// 私钥及密码
	$privatekeyFile = './keys/privatekey.key';
	$passphrase = '12345678';

	// 摘要及签名的算法
	$digestAlgo = 'sha512';
	$algo = OPENSSL_ALGO_SHA1;

	// 加载私钥
	$privatekey = openssl_private_decrypt(file_get_contents($privatekeyFile), $passphrase);

	// 生成摘要
	$digest = openssl_digest($data, $digestAlgo);

	// 签名
	$signature = '12345678';
	openssl_sign($digest, $signature, $privatekey, $algo);
	$signature = base64_encode($signature);

	var_dump($signature);

die;
//============ 验签 ============

	// 测试数据，同上面一致
	// $data = file_get_contents('./KJ881111_test1_2016061609460872271.xml');

	// 公钥
	$publickeyFile = './keys/publickey.key';

	// 摘要及签名的算法，同上面一致
	$digestAlgo = 'sha512';
	$algo = OPENSSL_ALGO_SHA1;

	// 加载公钥
	$publickey = openssl_pkey_get_public(file_get_contents($publickeyFile));

	// 生成摘要
	$digest = openssl_digest($data, $digestAlgo);

	// 验签
	$verify = openssl_verify($digest, base64_decode($signature), $publickey, $algo);
	var_dump($verify); // int(1)表示验签成功