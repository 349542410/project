<?php
namespace Api\Model;
use Think\Model;
class AuthCodeListModel extends Model{

	protected $_validate = array(
		array('tcid','require','线路名称不能为空'),
		array('auth_code','require','授权码不能为空',2),
		array('email','require','至少填写一个邮箱'),
		//验证匹配多个邮箱
		array('email','/^(([a-zA-Z0-9_\-\.]+)@([a-zA-Z0-9_\-\.]+)\.([a-zA-Z]{2,5}){1,25})+([,.](([a-zA-Z0-9_\-\.]+)@([a-zA-Z0-9_\-\.]+)\.([a-zA-Z]{2,5}){1,25})+)*$/','邮箱格式不正确'),
	);

}