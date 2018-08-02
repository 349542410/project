<?php

// 在线下单提交

namespace Api\Controller;
use Think\Controller\HproseController;
class OrderSubmitController extends HproseController {


    // 获取当前所有可用的线路信息
    public function get_tranline(){

        return M('TransitCenter')->where(array('status'=>1,'optional'=>1))->order('id desc')->select();

    }


}