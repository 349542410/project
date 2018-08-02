//身份证名字+身份证号码识别
$obj = new \Lib10\Idcardno\AliIdcardno();

请求方法
$name = '身份证名字';
$idcardno = '身份证号码';
$result = $obj->IdentificationCard($name, $idcardno);
正确返回
$result = array(
	'name'		=> '姓名',
	'sex'		=> '姓别',
	'birth'		=> '出生日期',
	'address'	=> '地址',
	'idcard'	=> '身份证号码',

);

错误返回
$result = false;
错误信息
$obj->getError();
