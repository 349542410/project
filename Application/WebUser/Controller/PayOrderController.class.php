<?php
/**
 * 物流官网---会员平台---支付订单
 */
namespace WebUser\Controller;
use Think\Controller;
class PayOrderController extends BaseController{

    public function _initialize() {
        parent::_initialize();
        $Wclient = new \HproseHttpClient(C('WAPIURL').'/PayOrder');      //增删改操作
        $this->Wclient = $Wclient;
    }

    /**
     * 订单支付 方法
     * @return [type] [description]
     */
	public function index(){
		if(IS_AJAX){
			$sn      = (I('post.sn')) ? trim(I('post.sn')) : '';
			$uucode  = (I('post.uucode')) ? trim(I('post.uucode')) : '';//'1171705121715136';
			$user_id = session('mkuser.uid');

			$sn     = base64_decode(urldecode($sn));
			$uucode = base64_decode(urldecode($uucode));

			if($sn == '' || $uucode == ''){
				$this->ajaxReturn(array('state'=>'no','msg'=>L('LAY_MesPar')));
			}
			$Wclient = $this->Wclient;

			$res = $Wclient->checkInfo($sn, $uucode, $user_id);

			if($res['state'] == 'yes'){
				$res['url'] = U('Member/index');
			}else{
				$res['msg'] = L($res['info']);
			}
			$this->ajaxReturn($res);
		}
	}
}