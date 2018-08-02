<?php

    namespace Lib11\RB;

    class GoodsLogic extends Logic{

        private $rb;

        public function __construct(){

            $this->rb = new \Lib11\RB\RedBook\RBGoods();

        }


        //获取所有的spu_id和spu_name
        public function getSpuId($parameter){

            // $parameter = array(
            //     'authentication' => array(      //不传递此参数为测试环境
            //         'app_key' => '',
            //         'app_secret' => '',
            //     ),
            //     'criteria' => array(    //查询条件
            //         'status' => 2,              //商品状态(0为编辑中，1为待审核，2为审核通过)
            //         'page_no' => 1,             //商品页数, 从第一页开始,默认为1
            //         'page_size' => 10,          //商品列表每页数量，默认为50，上限为500
            //         'buyable' => false,         //商品是否可售卖，true为在架上可售卖，false为已下架不可售卖
            //         'create_time_from' => '',   //商品创建时间开始时间，Unix-Time时间戳
            //         'create_time_to' => '',     //商品创建时间结束时间，Unix-Time时间戳
            //         'update_time_from' => '',   //商品更新时间开始时间，Unix-Time时间戳
            //         'update_time_to' => '',     //商品更新时间结束时间，Unix-Time时间戳
            //         'stock_gte' => '',          //库存大于等于某数
            //         'stock_lte' => '',          //库存小于等于某数
            //     )
            // );

            $result = $this->rb->showGoodsList($parameter['authentication'],$parameter['criteria'],true);
            $res = $this->getInfo($result);

            if(!$res['success']){
                return $res;
            }else{
                $data = $res['data']['hits'];
                $arr = array();
                $arr1 = array();
                $j = 0;

                foreach($data as $k=>$v){
                    if(!in_array($v['spu']['id'],$arr)){
                        $arr[] = $v['spu']['id'];
                        $arr1[$j]['spu_id'] = $v['spu']['id'];
                        $arr1[$j]['spu_name'] = $v['spu']['name'];
                        $arr1[$j]['spu_ename'] = $v['spu']['ename'];
                    }
                    $j++;
                }

                $res['data'] = null;
                $res['data']['total'] = count($arr);
                $res['data']['spu_id'] = $arr1;
                return $res;
            }

        }


        //获取item的信息
        public function getGoodsInfoByItem($parameter){

            // $parameter = array(
            //     'authentication' => array(      //不传递此参数为测试环境
            //         'app_key' => '',
            //         'app_secret' => '',
            //     ),
            //     'criteria' => array(
            //         'id' => '595af8a8f2c0743d34cc63ed',         //item_id
            //         'barcode' => 'lllooo',                      //条形码
            //         'skucode' => '',                            //小红书编码
            //     ),
            // );

            $authentication = $parameter['authentication'];
            $criteria = $parameter['criteria'];

            if(empty($criteria['id'])&&empty($criteria['barcode'])&&empty($criteria['skucode'])){
                return $this->getInfo(false,'缺少查询参数');
            }

            $result = $this->rb->showGoods($authentication,$criteria);
            $res = $this->getInfo($result);

            if(!$res['success']){
                return $res;
            }else{
                // $data = $res['data']['hits'][0];
                $arr = array();
                foreach($res['data']['hits'] as $k=>$data){

                    $arr[$k]['spu_id'] = $data['spu']['id'];
                    $arr[$k]['spu_name'] = $data['spu']['name'];
                    $arr[$k]['spu_ename'] = $data['spu']['ename'];
                    $arr[$k]['spl_id'] = $data['spl']['id'];
                    $arr[$k]['spl_variants'] = $data['spl']['variants'];
                    $arr[$k]['spv_id'] = $data['spv']['id'];
                    $arr[$k]['spv_variants'] = $data['spv']['non_desc_variants'];
                    $arr[$k]['skucode'] = $data['item']['skucode'];
                    $arr[$k]['item_id'] = $data['item']['id'];
                    $arr[$k]['item_name'] = $data['item']['name'];
                    $arr[$k]['item_ename'] = $data['item']['ename'];
                    $arr[$k]['price'] = $data['item']['price'];
                    $arr[$k]['original_price'] = $data['item']['original_price'];
                    $arr[$k]['barcode'] = $data['item']['barcode'];
                    $arr[$k]['stock'] = $data['item']['stock'];

                }
                
            }

            $res['data'] = $arr;
            return $res;

        }




        //根据spu获取item信息
        public function getGoodsInfo($parameter){

            // $parameter = array(
            //     'authentication' => array(      //不传递此参数为测试环境
            //         'app_key' => '',
            //         'app_secret' => '',
            //     ),
            //     'request' => array(            //查询条件
            //         'goods_id' => '对应spu_id',           
            //     ),
            // );
            // $result = array(
            //     'success' => true,      
            //     'data' => array(    
            //         'stock_list' => array(
            //             '0' => array(
            //                 'goods_id' => '对应spu_id',
            //                 'goods_name' => '对应spu_name',
            //                 'goods_attr_id' => '对应item_id',
            //                 'barcode' => '条形码',
            //                 'attr_name_list' => '属性名称列表，以-连接',
            //             ),
            //             // ...
            //         ),    
                    
            //     ),
            //     'errorstr' => '',       
            // );

            $para = array();
            $para['authentication'] = $parameter['authentication'];
            $para['spu_id'] = $parameter['request']['goods_id'];
            // dump($para);

            $result = $this->searchGoodsInfo($para);

            foreach($result['data'] as $k=>$v){
                $result['data'][$k]['goods_id'] = $result['data'][$k]['spu_id'];
                unset($result['data'][$k]['spu_id']);
                $result['data'][$k]['goods_name'] = $result['data'][$k]['spu_name'];
                unset($result['data'][$k]['spu_name']);
                $result['data'][$k]['goods_attr_id'] = $result['data'][$k]['item_id'];
                unset($result['data'][$k]['item_id']);
                $barcode = $result['data'][$k]['barcode'];
                unset($result['data'][$k]['barcode']);
                $result['data'][$k]['barcode'] = $barcode;

                $arr1 = array();
                $arr2 = array();
                foreach($result['data'][$k]['spl_variants'] as $k1=>$v1){
                    $arr1[] = $v1['value'];
                }
                foreach($result['data'][$k]['spv_variants'] as $k2=>$v2){
                    $arr2[] = $v2['value'];
                }
                $str1 = implode('-',$arr1);
                $str2 = implode('-',$arr2);
                if(!empty($str1)&&!empty($str2)){
                    $result['data'][$k]['attr_name_list'] = $str1 . '-' . $str2;
                }else if(!empty($str1)){
                    $result['data'][$k]['attr_name_list'] = $str1;
                }else{
                    $result['data'][$k]['attr_name_list'] = $str2;
                }

                unset($result['data'][$k]['spl_variants']);
                unset($result['data'][$k]['spv_variants']);
            }

            $zs = $result['data'];
            $result['data'] = array(
                'stock_list' => $zs
            );
            return $result;

        }












        //根据spu获取格式整齐的商品信息
        public function searchGoodsInfo($parameter){

            // $parameter = array(
            //     'authentication' => array(      //不传递此参数为测试环境
            //         'app_key' => '',
            //         'app_secret' => '',
            //     ),
            //     'spu_id' => '59784213f2c0745c8f89bffe',     //不区分规格的商品id
            // );

            $authentication = $parameter['authentication'];
            $spu_id = $parameter['spu_id'];

            if(empty($spu_id)){
                return $this->getInfo(false,'缺少spu_id参数');
            }
            $result = $this->rb->getInfoBySpu($authentication,$spu_id);
            $res = $this->getInfo($result);

            if(!$res['success']){
                return $res;
            }else{
                $data = $res['data'];
                $arr = array();

                foreach($data['items'] as $k=>$v){
                    $v = (array)$v;
                    $arr[$k]['spu_id'] = $v['spu_id'];      //不带属性的商品id
                    $arr[$k]['spu_name'] = $data['spus'][0]['name'];
                    // $arr[$k]['spu_ename'] = $data['spus'][0]['ename'];

                    // $arr[$k]['spl_id'] = $v['spl_id'];      //一级属性（外观）
                    foreach($data['spls'] as $k1=>$v1){
                        if($v1['id']==$v['spl_id']){
                            $arr[$k]['spl_variants'] = $v1['variants'];
                        }
                    }

                    // $arr[$k]['spv_id'] = $v['spv_id'];      //二级属性（规格）
                    foreach($data['spvs'] as $k2=>$v2){
                        if($v2['id']==$v['spv_id']){
                            $arr[$k]['spv_variants'] = $v2['non_desc_variants'];
                        }
                    }

                    $arr[$k]['item_id'] = $v['id'];         //带有属性的商品id（具体的商品）
                    // $arr[$k]['item_name'] = $v['name'];
                    // $arr[$k]['item_ename'] = $v['ename'];
                    // $arr[$k]['price'] = $v['price'];
                    // $arr[$k]['original_price'] = $v['original_price'];
                    $arr[$k]['barcode'] = $v['barcode'];    //条形码
                    // $arr[$k]['stock'] = $v['stock'];        //商品库存

                }

                $res['data'] = $arr;
                return $res;
            }

        }























        public function addSpu($authentication=array(),$data){

            $result = $this->rb->addSpu($authentication,$data);
            return $this->getInfo($result);

        }

        public function addSpl($authentication=array(),$data,$spu_id){

            if(empty($spu_id)){
                return $this->getInfo(false,'缺少spu_id参数');
            }
            $result = $this->rb->addSpl($authentication,$data,$spu_id);
            return $this->getInfo($result);
            
        }

        public function addSplITEM($authentication=array(),$data,$spl_id){

            if(empty($spl_id)){
                return $this->getInfo(false,'缺少spl_id参数');
            }
            $result = $this->rb->addSplITEM($authentication,$data,$spl_id);
            return $this->getInfo($result);
            
        }

        public function addSpv($authentication=array(),$data,$spl_id){

            if(empty($spl_id)){
                return $this->getInfo(false,'缺少spl_id参数');
            }
            $result = $this->rb->addSpv($authentication,$data,$spl_id);
            return $this->getInfo($result);
            
        }

        public function addItem($authentication=array(),$data,$spv_id){

            if(empty($spv_id)){
                return $this->getInfo(false,'缺少spv_id参数');
            }
            $result = $this->rb->addItem($authentication,$data,$spv_id);
            return $this->getInfo($result);
            
        }

        public function updateSpu($authentication=array(),$data,$spu_id){

            if(empty($spu_id)){
                return $this->getInfo(false,'缺少spu_id参数');
            }
            $result = $this->rb->updateSpu($authentication,$data,$spu_id);
            return $this->getInfo($result);
            
        }

        public function updateSpl($authentication=array(),$data,$spl_id){

            if(empty($spl_id)){
                return $this->getInfo(false,'缺少spl_id参数');
            }
            $result = $this->rb->updateSpl($authentication,$data,$spl_id);
            return $this->getInfo($result);
            
        }

        public function updateSpv($authentication=array(),$data,$spv_id){

            if(empty($spv_id)){
                return $this->getInfo(false,'缺少spv_id参数');
            }
            $result = $this->rb->updateSpv($authentication,$data,$spv_id);
            return $this->getInfo($result);
            
        }

        public function updateCustomsInfo($authentication=array(),$data,$spv_id){

            if(empty($spv_id)){
                return $this->getInfo(false,'缺少spv_id参数');
            }
            $result = $this->rb->updateCustomsInfo($authentication,$data,$spv_id);
            return $this->getInfo($result);
            
        }

        public function updateItem($authentication=array(),$data,$item_id){

            if(empty($item_id)){
                return $this->getInfo(false,'缺少item_id参数');
            }
            $result = $this->rb->updateItem($authentication,$data,$item_id);
            return $this->getInfo($result);
            
        }

        public function submit($authentication=array(),$spl_id){

            if(empty($spl_id)){
                return $this->getInfo(false,'缺少spl_id参数');
            }
            $result = $this->rb->submit($authentication,$spl_id);
            return $this->getInfo($result);
            
        }

        public function showGoods($authentication=array(),$parameter=array()){

            $result = $this->rb->showGoods($authentication,$parameter);
            return $this->getInfo($result);

        }

        public function showGoodsList($authentication=array(),$parameter=array()){

            $result = $this->rb->showGoodsList($authentication,$parameter,true);
            return $this->getInfo($result);

        }

        public function getInfoBySpu($authentication=array(),$spu_id){

            if(empty($spu_id)){
                return $this->getInfo(false,'缺少spu_id参数');
            }
            $result = $this->rb->getInfoBySpu($authentication,$spu_id);
            return $this->getInfo($result);

        }

        public function available($authentication=array(),$data,$item_id){

            if(empty($item_id)){
                return $this->getInfo(false,'缺少item_id参数');
            }
            $result = $this->rb->available($authentication,$data,$item_id);
            return $this->getInfo($result);

        }
        

    }