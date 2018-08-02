<?php

	namespace Lib11\Jos;

	class GoodsLogic extends Logic{

		private $jd;

		public function __construct(){

			$this->jd = new \Lib11\Jos\Jdsdk\Entrance();

		}

        //根据商品id获取skuId
        public function getGoodsInfo($parameter){

            if(empty($parameter['request']['goods_id'])){
                return $this->getInfo(false,'缺少必要的参数');
            }

            $parameter['request'] = array(
                'wareId' => $parameter['request']['goods_id']
            );

            $result = $this->getGoodsList($parameter);
            // dump($result);
            // die;

            // $result = array(
            //     "success" => bool,
            //     "data" => array(
            //         0 => array(
            //             "stockNum" => '',
            //             "status" => '',
            //             "jdPrice" => '',
            //             "skuName" =>  "迈克·科尔斯/MICHAEL KORS 时尚女士MK包 纯色手提包斜跨包两用 Navy 深蓝",
            //             "saleAttrs" => array(
            //                 0 => array(
            //                     "attrId" =>  "1000020007",
            //                     "attrValueAlias" => array(
            //                         0 => "Navy 深蓝",
            //                         1 => "jhhx 重大",
            //                         2 => "hxyd 说的",
            //                     ),
            //                     "attrValues" => array(
            //                         0 =>  "1551223420",
            //                     ),
            //                 ),
            //                 1 => array(
            //                     "attrId" =>  "1000020007",
            //                     "attrValueAlias" => array(
            //                         0 => "asd 哈哈哈",
            //                         1 => "zdw 呵呵呵",
            //                     ),
            //                     "attrValues" => array(
            //                         0 =>  "1551223420",
            //                     ),
            //                 ),
            //                 2 => array(
            //                     "attrId" =>  "1000020007",
            //                     "attrValueAlias" => array(
            //                         0 => "ots 约定书",
            //                     ),
            //                     "attrValues" => array(
            //                         0 =>  "1551223420",
            //                     ),
            //                 ),
            //             ),
            //             "skuId" => '',
            //             "wareId" => '',
            //             "outerId" =>  "",
            //         ),
            //         1 => array(
            //             "stockNum" => '',
            //             "status" => '',
            //             "jdPrice" => '',
            //             "skuName" =>  "迈克·科尔斯/MICHAEL KORS 时尚女士MK包 纯色手提包斜跨包两用 Sun 土黄",
            //             "saleAttrs" => array(
            //                 0 => array(
            //                     "attrId" =>  "1000020007",
            //                     "attrValueAlias" => array(
            //                         0 => "Sun 土黄",
            //                     ),
            //                     "attrValues" => array(
            //                         0 =>  "1551223420",
            //                     ),
            //                 ),
            //             ),
            //             "skuId" => '',
            //             "wareId" => '',
            //             "outerId" =>  "",
            //         ),
            //         2 => array(
            //             "stockNum" => '',
            //             "status" => '',
            //             "jdPrice" => '',
            //             "skuName" =>  "迈克·科尔斯/MICHAEL KORS 时尚女士MK包 纯色手提包斜跨包两用 Vanilla 乳白",
            //             "saleAttrs" => array(
            //                 0 => array(
            //                     "attrId" =>  "1000020007",
            //                     "attrValueAlias" => array(
            //                         0 => "Vanilla 乳白",
            //                     ),
            //                     "attrValues" => array(
            //                         0 =>  "1551223420",
            //                     ),
            //                 ),
            //             ),
            //             "skuId" => '',
            //             "wareId" => '',
            //             "outerId" =>  "",
            //         ),
            //     ),
            //     "errorstr" =>  "",
            // );


            

            if(!$result['success']){
                return $result;
            }else{

                $data = $result['data'];
                foreach($data as $k=>$v){
                    $a = array();
                    foreach($v['saleAttrs'] as $k1=>$v1){
                        $a[] = implode(',',$v1['attrValueAlias']);
                    }
                    $result['data'][$k]['saleAttrs'] = implode('-',$a);
                }

                $data = array();
                foreach($result['data'] as $k=>$v){
                    $data[] = array(
                        'goods_id' => $v['wareId'],
                        'goods_name' => $v['skuName'],
                        'goods_attr_id' => $v['skuId'],
                        'barcode' => $v['outerId'],
                        'outerId' => $v['outerId'],
                        'attr_name_list' => $v['saleAttrs'],
                        'stockNum' => $v['stockNum'],
                    );
                }
                $result['data'] = array(
                    'stock_list' => $data,
                );

                return $result;

            }

            

        }

        //更新库存
        public function updateStock($parameter){

            if(empty($parameter['request']['goods_attr_id']) || empty($parameter['request']['stock_num'])){
                return $this->getInfo(false,'缺少必要的参数');
            }

            $p = $parameter;

            $parameter['request'] = array(
                'skuId' => $parameter['request']['goods_attr_id'],
                'stockNum' => $parameter['request']['stock_num'],
            );

            // dump($parameter);
            $result = $this->setStockNum($parameter);

            if(!$result['success']){
                return $result;
            }else{
                $result['data'] = array(
                    'modify_time' => date("Y-m-d H:i:s",time()),    //修改时间
                    'resulf' => true,       //修改是否成功
                    'goods_id' => $p['request']['goods_id'],
                    'goods_attr_id' => $p['request']['goods_attr_id'],
                    'barcode' => $p['request']['barcode'],
                    'stock_num' => $p['request']['stock_num'],   //更新后的库存
                );

            }

            return $result;

        }

        //查询库存
        public function getStock($authentication,$skuId){

            $parameter = array(
                'authentication' => $authentication,
                'request' => array(
                    'skuId' => $skuId,
                ),
            );

            // dump($parameter);

            $info = $this->getGoodsInfoBySku($parameter);
            // dump($info);
            if($info['code']!=='0'){
                return -1;
            }else{
                return $info['sku']['stockNum'];
            }

        }














        public function setStockNum($parameter){

            $authentication = $parameter['authentication'];
            $data = $parameter['request'];
            $method = 'jingdong.stock.write.updateSkuStock';
			$this->jd->init($authentication,$method,$data);
            $result = $this->jd->execute();
            return $this->getInfo($result);
            // return $result;

        }


        // //根据wareId获取其下面的所有sku
        // public function getSkuByWare($parameter){

        //     $authentication = $parameter['authentication'];
        //     $data = $parameter['request'];
        //     $data['field'] = "stockNum,skuName,saleAttrs,props,ware";
        //     $method = 'jingdong.ware.read.findWareById';
		// 	$this->jd->init($authentication,$method,$data);
        //     $result = $this->jd->execute();
        //     return $result;

        // }

        //获取所有sku
		public function getGoodsList($parameter){

            $authentication = $parameter['authentication'];
            $data = $parameter['request'];
            $data['startCreatedTime'] = date("Y-m-d H:i:s",0);
            $data['field'] = "barCode,stockNum,skuName,saleAttrs";
            $method = 'jingdong.sku.read.searchSkuList';
			$this->jd->init($authentication,$method,$data);
            $result = $this->jd->execute();
            return $this->getInfo($result);

		}

        // //获取单个sku
        public function getGoodsInfoBySku($parameter){

            $authentication = $parameter['authentication'];
            $data = $parameter['request'];
            $data['field'] = "stockNum";
            $method = 'jingdong.sku.read.findSkuById';
			$this->jd->init($authentication,$method,$data);
            $result = $this->jd->execute();
            // dump($result);
            return $result;

        }


	}