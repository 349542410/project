<?php
/**
 * 中通 补录 菜鸟单号
 */
namespace Admin\Controller;
use Think\Controller;
class StTransferLpController extends Controller {

	public function index(){
		// $noid = (I('noid')) ? trim(I('noid')) : '';

		// vendor('Hprose.HproseHttpClient');
		// $client = new \HproseHttpClient(C('RAPIURL').'/AdminStTransferLp');

		// $map = array();
		// if($noid != '') $map['noid'] = array('eq',$noid);
		// $res = $client->_index($map);

		// $this->assign('tran',$res['tran']);
		// $this->assign('lp',$res['lp']);
		$this->display();
	}

	public function add(){

		$arr = array();
		$arr['STNO'] = trim(I('post.ztno'));
		$arr['LPNO'] = trim(I('post.lpno'));

		if($arr['STNO'] == '') $this->ajaxReturn(array('state'=>'no', 'msg'=>'请输入中通单号'));
		if($arr['LPNO'] == '') $this->ajaxReturn(array('state'=>'no', 'msg'=>'请输入菜鸟单号'));

		vendor('Hprose.HproseHttpClient');
		$client = new \HproseHttpClient(C('RAPIURL').'/AdminStTransferLp');

		$res = $client->_add($arr);
		$this->ajaxReturn($res);
	}
}