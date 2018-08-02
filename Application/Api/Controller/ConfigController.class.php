<?php
namespace  Api\Controller;

use Think\Controller;

class ConfigController extends Controller
{


    public function getConfig($key)
    {
        if(empty($key)){
            return false;
        }
        $info =  M('admin_config')->field('value')->where(array('name'=>$key))->find();
        return !empty($info) ? $info['value'] : '';
    }

}