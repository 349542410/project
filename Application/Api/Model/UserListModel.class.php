<?php
namespace Api\Model;
use Think\Model;
class UserListModel extends Model{

	/* 自动验证 */
	protected $_validate = array(
		array('username','require',L("Please_nickname_re")),
		array('username','get_name_rule','用户名必须为6-18位的字符串',0,'callback',3 ),
		array('username',"/^[a-z0-9_]+$/",'用户名只能用数字、小写字母、下划线的组合'),
		array('username','','用户名已经存在！',0,'unique',1), // 在新增的时候验证字段是否唯一

		array('pwd','require','密码必须填写'),
		array('pwd','pwd1','密码必须为6-16位的字符串',0,'callback',3 ),
		array('pwd','pwd2','密码必须包含数字和字母',0,'callback',3 ),
		array('repwd','pwd','确认密码不正确',0,'confirm'), // 验证确认密码是否和密码一致

		array('email','require','邮箱必须填写'),
		array('email',"email",'邮箱格式不正确'),
		array('email','','邮箱已经存在！',0,'unique',1), // 在新增的时候验证字段是否唯一

		array('FirstName','require','姓必须填写'),
		array('FirstName','FirstName','姓长度不可超过20个字符',0,'callback',3 ),
		array('LastName','require','名必须填写'),
		array('LastName','LastName','名长度不可超过20个字符',0,'callback',3 ),
		array('CompanyName','require','公司名称必须填写'),
		array('CompanyName','CompanyName','公司名称长度不可超过80个字符',0,'callback',3 ),

		array('countryId','require','非法操作'),
		//Hprose下无法进行以下验证 20160106 Jie
		// array('MKNO','mknolen','美快单号格式不正确',0,callback),
		// array('reTel','telephone','收件人电话格式不正确',0,callback),
	);

    protected function get_name_rule($str){
        if (strlen(trim($str))>18 || strlen(trim($str))<6) return false;
    }

    protected function pwd1($pwd){
        if (strlen(trim($pwd))>16 || strlen(trim($pwd))<6) return false;
    }

    protected function pwd2($pwd){
        if (preg_match("/^\d*$/", trim($pwd)) || preg_match("/^[A-Za-z]*$/i", trim($pwd))) return false;
    }

    protected function FirstName($str){
        if (strlen(trim($str))>20) return false;
    }

    protected function LastName($str){
        if (strlen(trim($str))>20) return false;
    }

    protected function CompanyName($str){
        if (strlen(trim($str))>80) return false;
    }
/*	protected function mknolen($MKNO){
		$reg = "/^MK[a-zA-Z0-9]{10,12}$/";
		if(preg_match($reg,$MKNO) != true){
            return false;
        }
	}

	protected function telephone($reTel){
		$reg = "/^0?(13|14|15|18)[0-9]{9}$/";
		if(preg_match($reg,$reTel) != true){
            return false;
        }
	}*/


}