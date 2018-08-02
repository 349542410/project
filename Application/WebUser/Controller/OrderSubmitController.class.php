<?php

namespace WebUser\Controller;

// 在线下单提交

class OrderSubmitController extends BaseController {

    private $order_cli;
    private $line_info;     // 线路信息

    public function __construct(){

        parent::__construct();

        $this->order_cli = new \HproseHttpClient(C('WAPIURL').'/OrderSubmit');

        $this->line_info = $this->order_cli->get_tranline();

        // 定义最大商品条数
        define(MAX_ORDERNO, 31);

    }


    public function order_insert(){


        $data = I('post.');
        if(empty($data)){
            $this->failed('data can not be empty');
            die;
        }

                                // 不知道什么用
                                session('pro_list',null);

                                //是否需要添加到消息队列
                                $neer_set_catch = false;


        $this->successful($this->create_data($data));









    }


    public function order_save(){



    }


    private function successful($info=array()){
        echo \json_encode(array(
            'status' => true,
            'info' => $info,
            'err' => '',
        ));
        die;
    }

    private function failed($err='', $info=array()){
        echo \json_encode(array(
            'status' => true,
            'info' => $info,
            'err' => $err,
        ));
        die;
    }


    // 创建数据
    private function create_data($data){

        $rule_1 = array(
            'sender', 'sendState', 'sendCity', 'sendStreet', 'sendTel', 'sendcode',     //寄件人
            'receiver', 'province', 'city', 'town', 'reAddr', 'reTel', 'postcode', 'id_type', 'cre_num',      // 收件人
            'user_id', 'user_name', 'line', 'id_img_status',
        );

        $rule_2 = array(
            'brand', 'detail', 'catname', 'category_one', 'category_two',
            'number', 'unit', 'price', 'remark',
        );

        $res_1 = array();
        foreach($rule_1 as $k=>$v){
            $res_1[$v] = \trim($data[$v]);
        }

        $res_2_tmp = array();
        foreach($rule_2 as $k=>$v){
            $res_2_tmp[$v] = $data[$v];
        }

        $res_2 = array();
        foreach($res_2_tmp as $k=>$v){
            foreach($v as $k1=>$v1){
                $res_2[$k1][$k] = trim($v1);
            }
        }

        return array(
            'res_1' => $res_1,
            'res_2' => $this->del_empty_row($res_2),
        );

    }


    // 删除空行
    private function del_empty_row($data){

        $res = array();
        foreach($data as $k=>$row){
            $is_e = true;
            foreach($row as $k1=>$v1){
                if(!empty($v1)){
                    $is_e = false;
                    break;
                }
            }
            if(!$is_e){
                $res[] = $row;
            }
        }

        return $res;

    }


}