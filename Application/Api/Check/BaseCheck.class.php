<?php

    // liao ya di
    // create time 2018-04-26

    namespace Api\Check;
    
    class BaseCheck{

        protected $allow_list = array();

        public function __construct(){

        }

        public function __call($name, $arguments){
            echo '请求的方法不存在';
            die;
        }


        // 根据 allow_list 数组，过虑参数 data 的元素
        // 第二个参数表示当【值的类型为浮点数】时，是否将【浮点数】转换为【字符串类型且保留两位小数】，默认为 FALSE
        public function create($data,$conv_str = false){

            $tmp = array();
            foreach($this->allow_list as $k=>$v){
                if($conv_str){
                    // 需要将 浮点数 转换为 字符串，保留两位小数
                    if(ceil($data[$k]) != $data[$k]){
                        // 是小数
                        $tmp[$k] = sprintf("%.2f",$data[$k]);
                    }else{
                        // 不是小数
                        $tmp[$k] = (string)$data[$k];
                    }
                }else{
                    // 无需转换
                    $tmp[$k] = $data[$k];
                }
            }

            return $tmp;

        }


        // 检测字段是否符合要求，只验证【长度】和【是否为空】
        public function check_data($data){
            
            foreach($this->allow_list as $k=>$v){
                if($v[0] && empty($data[$k]) && $data[$k]!==0 && $data[$k]!=='0'){
                    // 必须存在
                    return array(
                        'status' => false,
                        'info' => array($k,'l_empty'),
                    );
                }

                // 验证长度时，无法验证【浮点数】，请先将【浮点数】转换为【字符串】再进行验证
                // 原因是，浮点数无法精确存储，验证时会出现失误
                if(!empty($data[$k]) && strlen((string)$data[$k])>$v[1]){
                    // 长度不符合要求
                    return array(
                        'status' => false,
                        'info' => array($k,'l_max_len'),
                    );
                }
            }

            return array(
                'status' => true,
                'info' => '',
            );

        }

    }