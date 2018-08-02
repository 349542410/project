<?php

    namespace Lib11\RB\RedBook;

    class RBInventories{

        public function getInv($authentication=array(),$item_id){

            //根据item_id查询商品库存
            // $item_id = '595ef9b2f2c0743d34cc6478';

            \Lib11\RB\Common::setAll($authentication);

            $url = '/ark/open_api/v0/items/' . $item_id . '/stock';
            $xhsObj = new \Lib11\RB\Tool\XHSInterface($url,$parameters);
            return $xhsObj->run(array(),'get');

        }

        public function updateInv($authentication=array(),$data=array(),$key=array(),$method=false){

            //更新库存
            //如果$method为false，则直接设置库存
            //如果$method为true，则进行增量更新

            //通过item_id或者barcode其中之一设置库存

            // $key = array(
            //     'item_id' => '595ef9b2f2c0743d34cc6478',
            //     'barcode' => 'lllooo',
            // );

            // $data = array(
            //     'qty' => 18,
            // );

            \Lib11\RB\Common::setAll($authentication);

            if(!empty($key['item_id'])){
                $url = '/ark/open_api/v0/inventories/item/' . $key['item_id'];
            }else if(!empty($key['barcode'])){
                $url = '/ark/open_api/v0/inventories/' . $key['barcode'];
            }else{
                return false;
            }

            foreach($data as $k=>$v){
                if($k!=='qty'){
                    unset($data[$k]);
                }
            }

            if(empty($data['qty'])){
                return false;
            }

            if($method===true){
                $mh = 'PATCH';
            }else{
                $mh = 'PUT';
            }

            $xhsObj = new \Lib11\RB\Tool\XHSInterface($url,$parameters);
            return $xhsObj->run($data,$mh);
            // $r = $xhsObj->run($data,$mh);
            // dump($r);

        }

    }