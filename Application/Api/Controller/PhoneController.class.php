<?php
/**
 * 手机端 服务器端 (手机端查物流信息)
 */
namespace Api\Controller;
use Think\Controller\HproseController;
class PhoneController extends HproseController{
	protected $crossDomain =	true;
	protected $P3P		   =    true;
	protected $get		   =    true;
	protected $debug 	   =    true;

	public function getList($MKNO){
		$list = M('IlLogs')->order('create_time desc')->where(array('MKNO'=>$MKNO))->select();
		return $list;
	}


}