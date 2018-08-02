<?php
namespace Api\Model;
use Think\Model;
class TranListModel extends Model{

	protected $_validate = array(
		array('MKNO','require','美快单号不能为空'),
		array('receiver','require','收件人不能为空'),
		array('reTel','require','收件人电话不能为空'),
		array('MKNO','/^MK[a-zA-Z0-9]{10,12}$/','美快单号格式不正确'),
		array('reTel','/^(13|14|15|17|18)[0-9]{9}$/','收件人电话格式不正确'),
		//Hprose下无法进行以下验证 20160106 Jie
		// array('MKNO','mknolen','美快单号格式不正确',0,callback),
		// array('reTel','telephone','收件人电话格式不正确',0,callback),
	);

	// protected function mknolen($MKNO){
	// 	$reg = "/^MK[a-zA-Z0-9]{10,12}$/";
	// 	if(preg_match($reg,$MKNO) != true){
 //            return false;
 //        }
	// }

	// protected function telephone($reTel){
	// 	$reg = "/^0?(13|14|15|18)[0-9]{9}$/";
	// 	if(preg_match($reg,$reTel) != true){
 //            return false;
 //        }
	// }
	
}