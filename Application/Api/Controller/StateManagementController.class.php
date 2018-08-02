<?php
namespace Api\Controller;
use Think\Controller;

class StateManagementController extends Controller{

    public function get_view_status($map){

        $res = M('view_status')->where($map)->find();
        return empty($res) ? true : false;

    }

    public function set_view_status($data){
        $res = M('view_status')->where($data)->find();
        if(empty($res)){
            M('view_status')->add($data);
        }
    }

    public function del_view_status($map){
        M('view_status')->where($map)->delete();
    }

}