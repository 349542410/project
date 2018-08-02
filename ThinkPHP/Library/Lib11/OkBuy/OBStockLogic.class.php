<?php

    namespace Lib11\OkBuy;

    class OBStockLogic extends Logic{


        public function searchStock($parameters=array()){

            // $data = array(
            //     'BarCode' => array(      //条形码
            //         '10013',     
            //         '12580'
            //     ),
            //     'Page' => 1,
            //     'PageSize' => 40,
            // );

            $authentication=$parameters['authentication'];
            $data=$parameters['request'];

            \Lib11\OkBuy\Common::setAll($authentication);


            $interface = strtolower(__FUNCTION__);
            // $interface = 'asd';
            $obj = new \Lib11\OkBuy\Drive\HLMDrive();
            return $this->getInfo($obj->distribution($interface,$data));

        }


        //获取商品信息
        //此方法并没有真正的查询数据，只是封装了数据而已
        public function getGoodsInfo($parameters){

            // $parameters = array(
            //     'authentication' => array(      //不传递此参数为测试环境
            //         'app_key' => '',
            //         'app_secret' => '',
            //     ),
            //     'request' => array(            //查询条件
            //         'goods_id' => '条形码',           
            //     ),
            // );

            return array(

                'success' => true,      
                'data' => array(    
                    'stock_list' => array(
                        '0' => array(
                            'goods_id' => $parameters['request']['goods_id'],
                            'goods_name' => '',
                            'goods_attr_id' => $parameters['request']['goods_id'],
                            'barcode' => $parameters['request']['goods_id'],
                            'attr_name_list' => '',
                        ),
                    ),
                ),
                'errorstr' => '',

            );


        }


        //  更新库存
        public function updateStock($parameters=array()){

            // $data = array(
            //     'BarCode' => array(      //条形码
            //         '10013' => 888,     
            //         '12580' => 999,
            //     ),
            // );

            $authentication = $parameters['authentication'];
            $arr = $parameters['request'];
            $data = array(
                'BarCode' => array(
                    $arr['barcode'] => $arr['stock_num'],
                ),
            );

            \Lib11\OkBuy\Common::setAll($authentication);
            $interface = strtolower(__FUNCTION__);
            // $interface = 'asd';
            $obj = new \Lib11\OkBuy\Drive\HLMDrive();
            // return $this->getInfo($obj->distribution($interface,$data));


            //拼接返回参数
            $result = $this->getInfo($obj->distribution($interface,$data));
            if($result['success']===false){
                return $result;
            }else{
                $p['authentication'] = $parameters['authentication'];
                $p['request'] = array(
                    'BarCode' => array($arr['barcode']),
                );
                $r = $this->searchStock($p);
                if($r['success']===false){
                    $stock_number = -1;
                }else{
                    $stock_number = $r['data']['StockList'][0]['StockNum'];
                }

                $info = array(
                    'modify_time' => date("Y-m-d H:i:s",time()),    //修改时间
                    'resulf' => true,           //修改是否成功
                    'goods_id' => $arr['goods_id'],
                    'goods_attr_id' => $arr['goods_attr_id'],
                    'barcode' => $arr['barcode'],
                    'stock_num' => $stock_number,   //更新后的库存
                );

                $result['data'] = $info;
                return $result;

            }

        }

    }