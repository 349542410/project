<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/6/22
 * Time: 10:28
 */
namespace Admin\Controller;
use Think\Controller;
class AdminDiscernController extends AdminbaseController
{
    public $client;
    function __construct()
    {
        parent::__construct();
        vendor('Hprose.HproseHttpClient');
        $this->client = new \HproseHttpClient(C('RAPIURL') . '/AdminDiscern');        //读取、查询操作
        //$this->writes = new \HproseHttpClient(C('WAPIURL').'/AdminCollectPoint');		//添加，修改
    }

    public function index(){

        $where['true_name'] = array('exp', 'is not null');
        $where['front_id_img'] = array('exp', 'is not null');
        $where['back_id_img'] = array('exp', 'is not null');

        $where['_string'] = 'valid_date_end is null';
        $res = M('user_extra_info')->where($where)->select();


        $obj = new \Lib10\Idcardali\AliIdcard();

        foreach ($res as $key => $val){
            if (empty($val['true_name'])){
                continue;
            }
            if (empty($val['idno'])){
                continue;
            }
            if (empty($val['front_id_img'])){
                continue;
            }
            if (empty($val['back_id_img'])){
                continue;
            }
            $front = WU_ABS_FILE . $val['front_id_img'];
            if (!file_exists($front)){
                continue;
            }
            $back  = WU_ABS_FILE .  $val['back_id_img'];
            if (!file_exists($back)){
                continue;
            }

            $row = $obj->authentication_idcard($back, $front, $val['true_name'], $val['idno']);
            if(!$row){
                continue;
            }
            $where['id'] = $val['id'];
            $data['sex']               = $row['sex'];
            $data['nation']            = $row['nation'];
            $data['birth']             = $row['birth'];
            $data['address']           = $row['address'];
            $data['authority']         = $row['authority'];
            $data['valid_date_start'] = $row['valid_date_start'];
            $data['valid_date_end']   = $row['valid_date_end'];

            $res = M('user_extra_info')->where($where)->save($data);
        }

        echo '识别修复完成';
        exit;

    }

    public function order(){
        $where['_string'] = 'valid_date_end is not null';
        $res = M('user_extra_info')->where($where)->select();

        foreach ($res as $key => $val){
            if (empty($val['user_id'])){
                continue;
            }
            $where['user_id'] = $val['user_id'];
            $where['reTel']    = $val['tel'];
            $where['idno']     = $val['idno'];
            $where['receiver'] = $val['true_name'];
            $where['_string']  = "(front_id_img is null OR  front_id_img = '') AND (back_id_img is null OR back_id_img = '') ";

            $data['front_id_img']       = $val['front_id_img'];
            $data['small_front_img']    = empty($val['small_front_img']) ? $val['front_id_img'] : $val['small_front_img'];
            $data['back_id_img']        = $val['back_id_img'];
            $data['small_back_img']     = empty($val['small_back_img']) ? $val['back_id_img'] : $val['small_back_img'];

            $res = M('tran_ulist')->where($where)->save($data);

        }

        echo '订单信息修复成功';
        exit;
    }




}