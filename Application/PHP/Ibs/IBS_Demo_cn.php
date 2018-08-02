<?php

//请求设置
set_time_limit(0);
header('Content-type:text/json;charset=UTF-8');

//报文
$xml = '<?xml version="1.0" encoding="utf-8"?><Request service="apiOrderService" lang="en"><Head>OSMS_1</Head><Body><Order orderid="INV00553410219154-test" reference_no1="INV00553410219154-test" express_type="100" custid="0010002117" j_company="Fashionvalet Sdn Bhd" j_contact="Fashionvalet Sdn Bhd" j_tel="0377335065" j_mobile="0377335065" j_address="E5-G Empire Damansara Jalan PJU 8/8 Damansara Perdana" j_province="Selangor" j_city="Damansara Perdana" j_county="" j_country="MY" j_post_code="47820" d_company="Nurashikin Hanafi" d_contact="Nurashikin Hanafi" d_tel="92363609" d_mobile="92363609" d_address="blk 308B punggol walk #11-380 lobby 5 singapore" d_province="singapore" d_city="singapore" d_county="" d_country="SG" d_post_code="822308" declared_value="10" currency="MYR" parcel_quantity="1" pay_method="1" tax="DDP"><Cargo goods_code="JAL01031" product_record_no="JAL01031" name="Just a Leaf Organic Tea" count="1" brand="VISA" currency="USD" unit="box" amount="5.88" specifications="1" good_prepard_no="123445" source_area="MY" /></Order></Body></Request>';

//IBS系统为客户分配的密钥
$checkword = 'fc34c561a34f';

//对报文进行base64加密
$data=base64_encode($xml);

//1.拼接报文和密钥的
//2.对拼接后的字符串进行MD5加密
//3.再进行base64加密得到校验码
$validateStr = base64_encode(md5(utf8_encode($xml).$checkword, false));

//网络服务请求地址
$pmsLoginAction = 'http://osms.sit.sf-express.com:2080/osms/services/OrderWebService?wsdl';

$client = new \SoapClient ($pmsLoginAction);
/*
* 获取SoapClient对象引用的服务所提供的所有方法
*/
echo ("SOAP服务器提供的开放函数:");
echo ('<pre>');
var_dump ( $client->__getFunctions () );//获取服务器上提供的方法
echo ('</pre>');
echo ("SOAP服务器提供的Type:");
echo ('<pre>');
var_dump ( $client->__getTypes () );//获取服务器上数据类型
echo ('</pre>');
echo ("执行GetGUIDNode的结果:");
$result=$client->sfexpressService(array('data'=>$data,'validateStr'=>$validateStr,'customerCode'=>'OSMS_1'));//查询，返回的是一个结构体

//显示结果
var_dump($result);



?>