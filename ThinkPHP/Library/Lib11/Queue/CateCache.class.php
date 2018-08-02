<?php

    namespace Lib11\Queue;

    class CateCache{
        
        // 获取所有线路的分类并格式化后存储到redis
        public static function set_category_cache(){

            vendor('Hprose.HproseHttpClient');
            $ua = new \HproseHttpClient(C('RAPIURL').'/UserAddressee');
            $Wclient = new \HproseHttpClient(C('WAPIURL').'/Order');

            $res = $ua->show_line();
            $line_brand = array();
    
            foreach($res as $k=>$v){
                // 线路
                $brand_info = $Wclient->first_level($v['id']);
                foreach($brand_info as $k1=>$v1){
                    // 一级分类
                    $line_brand[$v['id']][$k1] = array(
                        'value' => $v1['id'],
                        'label' => $v1['cat_name'],
                    );
                    $line_brand[$v['id']][$k1]['children'] = array();
                    $brand_info_two = $Wclient->_next_level($v1['id']);
    
                    // dump($brand_info_two);
                    foreach($brand_info_two as $k2=>$v2){
                        // 二级分类
                        $line_brand[$v['id']][$k1]['children'][$k2] = array(
                            'value' => $v2['id'],
                            'label' => $v2['cat_name'],
                            'price' => $v2['price'],
                            'spec_unit' => $v2['spec_unit'],
                            'num_unit' => $v2['num_unit'],
                        );
                        if($v['bc_state'] == 1){
                            // 三级分类
                            $line_brand[$v['id']][$k1]['children'][$k2]['children'] = array();
                            $brand_info_three = $Wclient->_product($v2['id'], '');
                            foreach($brand_info_three as $k3=>$v3){
                                $line_brand[$v['id']][$k1]['children'][$k2]['children'][] = array(
                                    'value' => $v3['id'],
                                    'label' => $v3['name'],
                                );
                            }
                        }
                    }
                }
            }
    
            // dump($line_brand);
    
            $Host = C('Redis')['Host'];
            $Port = C('Redis')['Port'];
            $Auth = C('Redis')['Auth'];
   
            try{
                $redis = new \Redis();
                $redis->connect($Host,$Port,$overtime);
                $redis->auth($Auth);
    
                $redis->set('category_list',\serialize($line_brand));
            }catch(\RedisException $e){
                return array(
                    'status' => 0,
                    'errinfo' => $e->getMessage(),
                );
            }
    
            return array(
                'status' => 1,
                'errinfo' => '',
            );
            
    
        }



        // redis 缓存收件人地区
        public static function set_addr_cache($line_id){

            if(empty($line_id)){
                return array(
                    'status' => false,
                    'errinfo' => "error: line_id can't be empty",
                );
            }

            vendor('Hprose.HproseHttpClient');
            $sea_client = new \HproseHttpClient(C('RAPIURL').'/OrderSea');

            $res = array();

            $province = $sea_client->get_province($line_id);
            if(!empty($province)){
                // dump($province);
                foreach($province as $k1=>$v1){

                    $res[$k1]['value'] = $v1['name'];
                    $res[$k1]['label'] = $v1['name'];
                    // $res[$k1]['children'] = array();

                    $city = $sea_client->get_city($v1['id']);
                    if(!empty($city)){
                        // dump($city);
                        foreach($city as $k2=>$v2){

                            $res[$k1]['children'][$k2]['value'] = $v2['name'];
                            $res[$k1]['children'][$k2]['label'] = $v2['name'];
                            $res[$k1]['children'][$k2]['zipcode'] = $v2['zipcode'];
                            $res[$k1]['children'][$k2]['children'] = array();

                            $town = $sea_client->get_town($v2['id']);
                            if(!empty($town)){
//                                 dump($town);
                                foreach($town as $k3=>$v3){

                                    $res[$k1]['children'][$k2]['children'][$k3]['value'] = $v3['name'];
                                    $res[$k1]['children'][$k2]['children'][$k3]['label'] = $v3['name'];
                                    $res[$k1]['children'][$k2]['children'][$k3]['zipcode'] = $v3['zipcode'];

                                }

                            }

                        }

                    }
                }
            }

            // dump($res);


            $Host = C('Redis')['Host'];
            $Port = C('Redis')['Port'];
            $Auth = C('Redis')['Auth'];

            try{
                $redis = new \Redis();
                $redis->connect($Host,$Port,3);
                $redis->auth($Auth);

                $redis->set('address_list_' . $line_id, \serialize($res));
            }catch(\RedisException $e){
                return array(
                    'status' => false,
                    'errinfo' => 'error: ' . $e->getMessage(),
                );
                // echo 'error: ' . $e->getMessage();
                // die;
            }


            return array(
                'status' => true,
                'errinfo' => '',
            );
            // echo 'success';
            // die;


        }

    }