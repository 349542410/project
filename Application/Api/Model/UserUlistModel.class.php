<?php
namespace Api\Model;
use Think\Model;
class UserUlistModel extends Model{

	/* 自动验证 */
	protected $_validate = array(
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