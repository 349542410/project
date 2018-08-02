<?php
namespace Wap\Controller;
use Think\Controller;
class WorkController extends Controller {
    public function index(){
    	$code 			= I('get.code');
    	$code 			= base64_decode($code);
    	$code 			= json_decode($code,true);
    	$this->code1 	= $code['code1'];
    	$this->code2 	= $code['code2'];
       	$this->display();
    }
    public function save(){
    	//分析单号对应的 确认码是否正确，
    }
}