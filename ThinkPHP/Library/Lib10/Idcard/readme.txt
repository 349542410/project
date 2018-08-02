//身份证识别
$obj = new \Lib10\Idcard\idcard();
1.识别身份证头像面
请求方法
$photo = '图片地址绝对路径';
$result = $obj->photo($photo);
正确返回
$result = array(
	'name'		=> '姓名',
	'sex'		=> '姓别',
	'nation'	=> '民族',
	'birth'		=> '出生日期',
	'address'	=> '地址',
	'idcard'	=> '身份证号码',

);

错误返回
$result = false;
错误信息
$obj->getError();


2.识别身份证国徽面
请求方法
$files = '图片地址绝对路径';
$result = $obj->national_emblem($files);
正确返回
$result = array(
	'authority'		=> '发证机关',
	'valid_date_start'		=> '证件有效期开始时间',
	'valid_date_end'	=> '证件有效期结束时间
);

错误返回
$result = false;
错误信息
$obj->getError();



3.识别身份证正反面
请求方法
$photos = '身份证头像面图片地址绝对路径';
$national = '身份证国徽面图片地址绝对路径';
$result = $obj->authentication($photos, $national)
正确返回
$result = array(
	'name'		=> '姓名',
	'sex'		=> '姓别',
	'nation'	=> '民族',
	'birth'		=> '出生日期',
	'address'	=> '地址',
	'idcard'	=> '身份证号码',
	'authority'		=> '发证机关',
	'valid_date_start'		=> '证件有效期开始时间',
	'valid_date_end'	=> '证件有效期结束时间
);

错误返回
$result = false;
错误信息
$obj->getError();

4.识别身份证正反面与名字跟身份证号码是否一致
请求方法
$photos = '身份证头像面图片地址绝对路径';
$national = '身份证国徽面图片地址绝对路径';
$idcard_name = '名字';
$idcard_idno = '身份证号码';
$result = $obj->authentication_idcard($photos, $national, $idcard_name, $idcard_idno);
正确返回
$result = array(
	'name'		=> '姓名',
	'sex'		=> '姓别',
	'nation'	=> '民族',
	'birth'		=> '出生日期',
	'address'	=> '地址',
	'idcard'	=> '身份证号码',
	'authority'		=> '发证机关',
	'valid_date_start'		=> '证件有效期开始时间',
	'valid_date_end'	=> '证件有效期结束时间
);

错误返回
$result = false;
错误信息
$obj->getError();








