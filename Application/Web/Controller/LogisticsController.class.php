<?php
namespace Web\Controller;
use Think\Controller;
class LogisticsController extends BaseController{
    public function _initialize() {
        parent::_initialize(); 
    }

	public function index(){
		if(!empty($_REQUEST['order_no'])){
			$MKNO = I('request.order_no');

			vendor('Hprose.HproseHttpClient');
			$client = new \HproseHttpClient(C('APIURL').'/Server');
			$list = $client->query($MKNO,1);

			if(empty($list)){
				$this->assign('errorstr','false');
			}else{
				$this->assign('MKNO',$MKNO);
				$this->assign('errorstr','true');
				$this->assign('list',$list);
			}
		}
		$this->display();
	}

}