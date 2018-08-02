<?php

    // 订单相关的操作
    // liao ya di
    // create time : 2017-10-20
    // API CONTROLLER

    namespace Api\Controller;
    use Think\Controller\HproseController;

    class OrderSeaController extends HproseController{

        //查询税金
        public function search_tax($tax_id_arr){

            $cat_m = M('CategoryList');
            $where['id'] = array('in',$tax_id_arr);
            return $cat_m->field('id,cat_name,price')->where($where)->select();

        }


        public function get_province($line_id){     //获取线路id下所有的省
            if(empty($line_id)){
                return array();
            }
            return M('ZcodeLine')->field('id,name')->where(array(
                'line_id' => $line_id,
                'status' => 1,
                'pid' => 0,
            ))->select();
        }

        public function get_city($id){      // 获取省id下所有的市id
            if(empty($id)){
                return array();
            }
            return M('ZcodeLine')->field('id,name,zipcode')->where(array(
                'pid' => $id,
                'status' => 1,
            ))->select();
        }

        public function get_town($id){      // 获取市id下所有的区id
            if(empty($id)){
                return array();
            }
            return M('ZcodeLine')->field('name,zipcode')->where(array(
                'pid' => $id,
                'status' => 1,
            ))->select();
        }


    }