<?php
namespace Web\Controller;
use Think\Controller;
class SolutionController extends BaseController {

    public function _initialize() {
       	// 20161028 伦
        // parent::_initialize(); 
    }
    
    public function index(){
        $this->display();
    }
}