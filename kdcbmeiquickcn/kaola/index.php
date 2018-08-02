<?php
/**
 * 考拉获取物流信息 客户端
 */



header("Content-type: text/html; charset=utf-8");
$config = require('../../config.php');
define('API_URL', $config['API_URL']);

//生成日志
$xmlsave = $config['UPFILEBASE'].'/KaoLaLog/';
$file_name = date('Ymd',time()).'.txt';	//文件名

$content = "\r\n\r\n".date('Y-m-d H:i:s',time()) . json_encode($_POST);

if(!is_dir($xmlsave)) mkdir($xmlsave, 0777, true);//第三个参数设置true 设置递归模式自动创建必需的目录文件夹

file_put_contents($xmlsave.$file_name, $content, FILE_APPEND);
//生成日志 end

$mkjs 	= isset($_POST['billno'])?$_POST['billno']:"";
if($mkjs=='') $mkjs = file_get_contents('php://input', 'r');

$mkjs 	= trim($mkjs);
$data 	= array("code"=>1,"message"=>'');
//$mkjs   = '{"billno":"MK883203689US"}';
if(strlen($mkjs)<10){
	$data['message'] = '请传入运单号';
	exit(json_encode($data));
}

$mkar 	= json_decode($mkjs,true);
if(!is_array($mkar)){
	$data['message'] = '发送的JSON不正确';
    exit(json_encode($data));
}

$mkno 	= trim($mkar['billno']);
if(strlen($mkno)<9){
	$data['message'] = '运单号'.$mkno.'不正确';
    exit(json_encode($data));
}
require_once("../../Application/hprose_php5/HproseHttpClient.php");
//$serurl = 'http://app.megao.hk:888/Api/MkilKaola';

$serurl = API_URL.'/MkilKaola';
$client = new HproseHttpClient($serurl);
$msg = $client->getlogs($mkno);

if(!is_array($msg)){
	$data['message'] = '没有运单号'.$mkno.'的相关信息';
	exit(json_encode($data));
}
echo json_encode($msg);
