<?php
/**
 * 快件管理 身份证号码修改 服务器端
 */
namespace Api\Controller;
use Think\Controller\HproseController;
class EditIdnoController extends HproseController{
   // protected $allowMethodList  =   array('index','test1');
    protected $crossDomain =    true;
    protected $P3P         =    true;
    protected $get         =    true;
    protected $debug       =    true;

    public function _update($id,$reTel,$idno){

    	$phone = M('TranList')->where(array('id'=>$id))->getField('reTel');

    	if($phone != $reTel){
    		return array('state'=>'no','msg'=>'电话号码资料验证错误');
    	}
    	if(certificate($idno) === false){
    		return array('state'=>'no','msg'=>'身份证号码格式不正确');
    	}

    	$res = M('TranList')->where(array('id'=>$id))->setField('idno',$idno);

    	if($res !== false){
    		return array('state'=>'yes','msg'=>'操作成功');
    	}else{
    		return array('state'=>'no','msg'=>'操作失败');
    	}
    }

}