<?php
/**
 * PDA
 */
namespace MkAuto\Controller;
use Think\Controller;
class IndexController extends Controller {
    public function index(){
        \Think\Log::write('回掉微信支付');exit();
    	if(I('get.fr','')=='mkil') cookie('fr','3');
        if(I('post.fr','')=='mkil') cookie('fr','3');
        $this->redirect('Login/index/');
    }
    public function index2(){
        $this->ttt = base64_encode('{}"asdfj asdlkfasowe osdjfla sdflasdofiwe23i401287401234-123{}');
        $this->display('index');
    }
    public function testonline(){ //20150528返回测试网络状态时的JSON
    	$data['st']    	= '200';
		$data['str'] 	= 'OK';
	    $this->ajaxReturn($data);
    }
}