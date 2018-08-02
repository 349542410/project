<?php

    namespace Lib11\RB;

    class InventoriesLogic extends Logic{

        private $rb;

        public function __construct(){
            $this->rb = new \Lib11\RB\RedBook\RBInventories();
        }


        //修改后的更新库存接口
        public function updateStock($parameter){

            // $parameter = array(
            //     'authentication' => array(      //不传递此参数为测试环境
            //         'app_key' => '',
            //         'app_secret' => '',
            //     ),
            //     'request' => array(                     //查询条件
            //         'goods_id' => '对应spu_id',
            //         'goods_attr_id' => '对应item_id',
            //         'barcode' => '条形码',    
            //         'stock_num' => 0,
            //     ),
            // );
            // $result = array(
            //     'success' => true,      
            //     'data' => array(
            //         'modify_time' => '',    //修改时间
            //         'resulf' => true,       //修改是否成功
            //         'goods_id' => '对应spu_id',
            //         'goods_attr_id' => '对应item_id',
            //         'barcode' => '条形码',
            //         'stock_num' => 0,   //更新后的库存
            //     ),
            //     'errorstr' => '',       
            // );

            $para = array();
            $para['authentication'] = $parameter['authentication'];
            $para['data'] = array(
                'qty' => $parameter['request']['stock_num']
            );

            if(!empty($parameter['request']['goods_attr_id'])){
                $para['key'] = array(
                    'item_id' => $parameter['request']['goods_attr_id']
                ); 
            }else if(!empty($parameter['request']['barcode'])){
                // $para['key'] = array(
                //     'barcode' => $parameter['request']['barcode']
                // ); 
                $para['key'] = array();
            }else{
                $para['key'] = array();
            }
            
            $para['method'] = false;


            $result = $this->setInv($para);

            if($result['success']){
                $result['data'] = null;
                $result['data']['modify_time'] = date("Y-m-d H:i:s",time());
                $result['data']['resulf'] = true;
                $result['data']['goods_id'] = $parameter['request']['goods_id'];
                $result['data']['goods_attr_id'] = $parameter['request']['goods_attr_id'];
                $result['data']['barcode'] = $parameter['request']['barcode'];

                $stock_num = $this->getInv($parameter['authentication'],$para['key']);  //检验库存
                $result['data']['stock_num'] = $stock_num['data'];
            }

            return $result;

        }








        //更新库存（直接设置或者增量更新）
        public function setInv($parameter){

            // $parameter = array(
            //     'authentication' => array(      //不传递此参数为测试环境
            //         'app_key' => '',
            //         'app_secret' => '',
            //     ),
            //     'data' => array(                //修改为多少 & 增加多少
            //         'qty' => 1,
            //     ),
            //     'key' => array(                 //item_id 或者 条形码
            //         'item_id' => '5978465ef2c0745c8f89c009',
            //         'barcode' => 'lllooo01',
            //     ),
            //     'method' => false               //false为直接设置库存值，true则增量更新
            // );

            $authentication = $parameter['authentication'];
            $data = $parameter['data'];
            $key = $parameter['key'];
            $method = $parameter['method'];

            if(empty($key['item_id'])&&empty($key['barcode'])){
                return $this->getInfo(false,'缺少商品参数');
            }

            if(empty($data['qty'])){
                return $this->getInfo(false,'缺少库存参数参数');
            }

            $result = $this->rb->updateInv($authentication,$data,$key,$method);
            return $this->getInfo($result);

        }


        // public function getInvByItem($parameter){

        //     $obj = new \Lib11\RB\GoodsLogic();
        //     $result = $obj->getGoodsInfoByItem($parameter);
        //     dump($result);

        // }















        //获取库存
        public function getInv($authentication=array(),$data){

            if(empty($data['item_id'])){
                return $this->getInfo(false,'缺少item_id参数');
            }
            $result = $this->rb->getInv($authentication,$data['item_id']);
            return $this->getInfo($result);

        }

        //直接设置库存
        public function updateInv($authentication=array(),$data=array(),$key=array(),$method=false){

            if(empty($key['item_id'])&&empty($key['barcode'])){
                return $this->getInfo(false,'缺少key参数');
            }

            if(empty($data['qty'])){
                return $this->getInfo(false,'缺少data["qty"]参数');
            }

            $result = $this->rb->updateInv($authentication,$data,$key,$method);
            return $this->getInfo($result);

        }

    }