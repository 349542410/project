<?php
/**
 * PDA
 * 称重中转打印面单160323
 */
namespace MkAuto\Controller;
use Think\Controller;
class WeigtransController extends BaseController {

    public function _initialize(){
        //$this->usertype = 60;
        //parent::_initialize();
    }
    public function index(){
        $this->display();
    }
}