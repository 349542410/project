<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/6/22
 * Time: 10:29
 */
namespace Api\Controller;
use Think\Controller\HproseController;
class AdminDiscernController extends HproseController{

    public function index(){
//        $where['user_id'] = array('gt', 0);
        $where['true_name'] = array('exp', 'is not null');
        $where['front_id_img'] = array('exp', 'is not null');
        $where['back_id_img'] = array('exp', 'is not null');

        $where['_string'] = 'valid_date_end is null';
        $res = M('user_extra_info')->where($where)->select();
        return $res;

    }

    public function edit($id, $data){
        $where['id'] = $id;
        $res = M('user_extra_info')->where($where)->save($data);

        return $res;

    }

    public function extra(){
        $where['_string'] = 'valid_date_end is not null';
        $res = M('user_extra_info')->where($where)->select();
        return $res;

    }

    public function order($where, $data){
         $res = M('tran_ulist')->where($where)->save($data);
         return $res;
    }

}