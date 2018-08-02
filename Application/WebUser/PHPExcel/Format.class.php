<?php

    namespace WebUser\PHPExcel;

    class Format{

        private $list;

        public function __construct($list=array()){
            // $list = array(
            //     '0'  => 'package_id',         //包裹id
            //     '1'  => 'sender',             //发件人姓名
            //     '2'  => 'sendStreet',         //街道
            //     '3'  => 'sendState',          //州
            //     '4'  => 'sendCity',           //市
            //     '5'  => 'sendTel',            //发件人电话
            //     '6'  => 'sendcode',           //发件人邮编
            //     '7'  => 'receiver',           //收件人姓名
            //     '8'  => 'cre_type',           //证件类型
            //     '9'  => 'cre_num',            //证件号码
            //     '10' => 'province',           //省
            //     '11' => 'city',               //市
            //     '12' => 'town',               //区
            //     '13' => 'reAddr',             //收件人详细地址
            //     '14' => 'reTel',              //收信人联系电话
            //     '15' => 'postcode',           //收件人邮编
            //     '16' => 'line',               //线路名称
            //     '17' => 'brand',              //品牌
            //     '18' => 'detail',             //货品名称
            //     '19' => 'catname',            //货品分类
            //     '20' => 'category_one',       //一级分类
            //     '21' => 'category_two',       //二级分类
            //     '22' => 'category_three',     //三级分类
            //     '23' => 'number',             //数量
            //     '24' => 'unit',               //计量单位
            //     '25' => 'source_area',        //产地
            //     '26' => 'price',              //单价（￥）
            // );
            $this->list = $list;
        }

        //整理格式
        // ignore 忽略前多少行
        public function exec($data,$ignore=4){
            
            //去掉没有订单号的行
            $i = 0;
            $data_tmp = array();
            foreach($data as $k=>$v){
                if($i<$ignore){
                    $i++;
                    continue;
                }
                if(empty($v[0]) && $v[0]!==0 && $v[0]!=='0'){
                    foreach($v as $k1=>$v1){
                        if(!empty($v1)){
                            throw new \Exception($i);
                        }
                    }
                    $i++;
                    continue;
                }

                $data_tmp[] = $v;
                $i++;
            }
            $data = $data_tmp;

            // return $data;


            //key替换为具体的字段
            $data_tmp = array();
            foreach($data as $k=>$v){
                $data_tmp[] = $this->replace_key($v);
            }
            $data = $data_tmp;

            return $data;

        }


        //将key由数字替换为具体的字段
        //一次只处理一行
        //要求：list必须是下标从0开始且连续递增1的下标，少一个就会出错
        //例如少一个0下标，直接从1开始，此时$this->list[0]就成了空值，即将空值作为$arr的下标，因此报错了
        //如果data的元素个数超过了list的下标，则多余的会被丢弃
        //如果data的元素个数少于了list的下标，则对应的位置会被赋值空值
        private function replace_key($data){

            $arr = array();
            for( $ii=0 ; $ii<count($this->list) ; $ii++ ){
                if(!empty($data[$ii])){
                    $arr[$this->list[$ii]] = $data[$ii];
                }else{
                    $arr[$this->list[$ii]] = '';
                }
                
            }

            return $arr;

        }

    }