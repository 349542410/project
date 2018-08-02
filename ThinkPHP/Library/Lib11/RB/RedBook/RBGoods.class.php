<?php

    namespace Lib11\RB\RedBook;

    class RBGoods{

        //添加spu
        public function addSpu($authentication=array(),$data){

            // $data = array(
            //     'spu' => array(
            //         'brand_id' => '5328e60bb4c4d61dde95a217',
            //         'category_id' => '572dd172939e250d08ec5967',
            //         // 'name' => '荣耀 7c',
            //         // 'ename' => 'honor 7',
            //         // 'short_name' => '荣耀',
            //     ),
            // );

            \Lib11\RB\Common::setAll($authentication);

            $url = '/ark/open_api/v1/spu';
            $xhsObj = new \Lib11\RB\Tool\XHSInterface($url,$parameters);
            return $xhsObj->run($data,'POST');

        }


        //添加spl
        public function addSpl($authentication=array(),$data,$spu_id){

            // $spu_id = '595ef85bf2c0743d35cc6480';

            // $data = array(
            //     'spl' => array(
            //         'variants' => array(        //id为此末级分类下的规格的id
            //             array('id' => '56d84f9a805d891008705275','value' => '白色'),
            //             array('id' => '5731b32d805d89642beb7154','value' => '4G'),
            //             array('id' => '5731b32d805d89642beb7155','value' => '时尚，商务'),
            //             array('id' => '57331047805d8919fceca624','value' => 'Android 7.0'),
            //             // array('id' => '57fa066e805d8955dcc00b1f','value' => '无'),
            //         ),
            //     ),
            // );

            \Lib11\RB\Common::setAll($authentication);

            $url = '/ark/open_api/v1/spu' . '/' . $spu_id . '/spl' ;
            $xhsObj = new \Lib11\RB\Tool\XHSInterface($url,$parameters);
            return $xhsObj->run($data,'POST');

        }


        //此方法可以同时创建或者更新 SPL ITEM
        public function addSplITEM($authentication=array(),$data,$spl_id){

            // $spl_id = '595ef86ef2c0743d35cc6481';

            // $data = array(
            //     'spl_item' => array(
            //         'image_urls' => array(
            //             'http://img.xiaohongshu.com/items/3135edc58855dcf047fc39161ee9f687@2o.jpg',
            //             'http://img.xiaohongshu.com/items/3135edc58855dcf047fc39161ee9f687@2o.jpg',
            //         ),
            //         'desc' => "very good",
            //         'feature' => "initial",     //商品特色 (8字真言，不超过八个字符)
            //         'attributes' => array(      //产品参数，补充信息，非必填
                        
            //         ),
            //         'faqs' => array(        //常见问题
            //             array( 'question'=>'包邮吗？' , 'answer'=>'不包邮' ),
            //             array( 'question'=>'要钱吗？' , 'answer'=>'不要钱' )
            //         ),
            //         'user_guide' => array(      //使用指南,支持上传图片url,只允许一张图片
            //             'image_urls' => array(
            //                 'http://img.xiaohongshu.com/items/3135edc58855dcf047fc39161ee9f687@2o.jpg',
            //             ),
            //         ),
            //         'image_desc' => array(      //图文详情
            //             'image_urls' => array(
            //                 'http://img.xiaohongshu.com/items/3135edc58855dcf047fc39161ee9f687@2o.jpg',
            //                 'http://img.xiaohongshu.com/items/3135edc58855dcf047fc39161ee9f687@2o.jpg',
            //             ),
            //         ),
            //     ),
            // );

            \Lib11\RB\Common::setAll($authentication);

            $url = '/ark/open_api/v1/spl' . '/' . $spl_id . '/spl_item' ;
            $xhsObj = new \Lib11\RB\Tool\XHSInterface($url,$parameters);
            return $xhsObj->run($data,'POST');

        }


        //添加spv
        public function addSpv($authentication=array(),$data,$spl_id){

            // $spl_id = '595ef86ef2c0743d35cc6481';

            // $data = array(
            //     'spv' => array(
            //         'qty' => 1,
            //         'unit' => '件',
            //         'net_weight' => '110',
            //         'gross_weight' => '220',
            //         'barcode' => 'qwerdf',        //条形码，不能重复
            //         'barcode_type' => 'upc',   //条码类型,使用大写报错
            //         'country' => '中国',
            //         'shelf_life' => 600,    //保质期
            //         'non_desc_variants' => array(   //SPV规格, 无SPV规格可不填
            //             // array( 'id'=>'' , 'value'=>'' ),
            //         ),
            //         'import_cost' => '1800',    //进口成本价(CNY)(可粗略估计)
            //         'manufacturer' => '华为',   //生产厂家(填写全称，中文／英文)
            //         'ingredient' => '4K屏',     //材质或成分含量(例如，水、二氧化碳等；帆布、牛皮等)
            //         'usage' => '手机',      //用途(例如，护肤等；背包、双肩包、凉鞋等)
            //         'customs_photos_urls' => array(     //商品图片（用于海关备案）
            //             'http://img.xiaohongshu.com/items/3135edc58855dcf047fc39161ee9f687@2o.jpg',
            //             'http://img.xiaohongshu.com/items/3135edc58855dcf047fc39161ee9f687@2o.jpg',
            //         ),
            //         'customs_specification' => '280g',      //规格型号(例如，液体：10ml，10ml/支 (等其他计量单位) ；固体：10g，10g/支 (等其他计量单位)；配饰/鞋子/服装：尺寸、尺码、颜色。)
            //     ),
            // );

            \Lib11\RB\Common::setAll($authentication);

            $url = '/ark/open_api/v1/spl' . '/' . $spl_id . '/spv' ;
            $xhsObj = new \Lib11\RB\Tool\XHSInterface($url,$parameters);
            return $xhsObj->run($data,'POST');

        }


        //添加spv_item
        public function addItem($authentication=array(),$data,$spv_id){

            // $spv_id = '595ef977f2c0743d34cc6476';

            // $data = array(
            //     'item' => array(
            //         'price' => '1180',
            //         'original_price' => '1799'
            //     ),
            // );

            \Lib11\RB\Common::setAll($authentication);

            $url = '/ark/open_api/v1/spv' . '/' . $spv_id . '/item' ;
            $xhsObj = new \Lib11\RB\Tool\XHSInterface($url,$parameters);
            return $xhsObj->run($data,'POST');

        }


        //error  成功，待定的更改，另外报错
        public function updateSpu($authentication=array(),$data,$spu_id){

            // $spu_id = '595ef85bf2c0743d35cc6480';

            // $data = array(
            //     'brand_id' => '5328e60bb4c4d61dde95a217',
            //     'category_id' => '572dd172939e250d08ec5967',
            //     'name' => '华为 荣耀 7c',
            //     'ename' => 'huawei honor 7',
            //     'short_name' => '荣耀',
            // );

            \Lib11\RB\Common::setAll($authentication);

            $url = '/ark/open_api/v1/spu' . '/' . $spu_id;
            $xhsObj = new \Lib11\RB\Tool\XHSInterface($url,$parameters);
            return $xhsObj->run($data,'PUT');

        }


        public function updateSpl($authentication=array(),$data,$spl_id){

            // $spl_id = '595ef86ef2c0743d35cc6481';

            // $data = array(
            //     'variants' => array(
            //         array('id' => '5731b32d805d89642beb7155','value' => '时尚,年轻,商务'),
            //     ),
            // );

            \Lib11\RB\Common::setAll($authentication);

            $url = '/ark/open_api/v1/spl' . '/' . $spl_id;
            $xhsObj = new \Lib11\RB\Tool\XHSInterface($url,$parameters);
            return $xhsObj->run($data,'PUT');

        }


        //error ,成功，待定的更改，另外报错
        public function updateSpv($authentication=array(),$data,$spv_id){

            // $spv_id = '595ef977f2c0743d34cc6476';

            // $data = array(
            //     'qty' => 1,
            //     'unit' => '件',
            //     'net_weight' => '500',
            //     'gross_weight' => '600',
            //     'barcode' => 'qwerdf',        //条形码
            //     'barcode_type' => 'upc',   //条码类型,使用大写报错
            //     'country' => '中国',
            //     'shelf_life' => 666,    //保质期
            //     'non_desc_variants' => array(   //SPV规格, 无SPV规格可不填
            //         // array( 'id'=>'' , 'value'=>'' ),
            //     ),
            // );

            \Lib11\RB\Common::setAll($authentication);

            $url = '/ark/open_api/v1/spv' . '/' . $spv_id;
            $xhsObj = new \Lib11\RB\Tool\XHSInterface($url,$parameters);
            return $xhsObj->run($data,'PUT');

        }


        //error
        //修改成功
        //更新海关信息
        public function updateCustomsInfo($authentication=array(),$data,$spv_id){

            // $spv_id = '595ef977f2c0743d34cc6476';

            // $data = array(
            //     'import_cost' => 2000,    //进口成本价(CNY)(可粗略估计)
            //     'manufacturer' => '华为',   //生产厂家(填写全称，中文／英文)
            //     'ingredient' => '超长待机，4K屏',     //材质或成分含量(例如，水、二氧化碳等；帆布、牛皮等)
            //     'usage' => '手机 旗舰级',      //用途(例如，护肤等；背包、双肩包、凉鞋等)
            //     'customs_photos_urls' => array(     //商品图片（用于海关备案）
            //         'http://img.xiaohongshu.com/items/3135edc58855dcf047fc39161ee9f687@2o.jpg',
            //         'http://img.xiaohongshu.com/items/3135edc58855dcf047fc39161ee9f687@2o.jpg',
            //         // 'http://img.xiaohongshu.com/items/3135edc58855dcf047fc39161ee9f687@2o.jpg',
            //     ),
            //     'customs_specification' => '360g',      //规格型号(例如，液体：10ml，10ml/支 (等其他计量单位) ；固体：10g，10g/支 (等其他计量单位)；配饰/鞋子/服装：尺寸、尺码、颜色。)
            // );

            \Lib11\RB\Common::setAll($authentication);

            $url = '/ark/open_api/v1/spv' . '/' . $spv_id . '/customs';
            $xhsObj = new \Lib11\RB\Tool\XHSInterface($url,$parameters);
            return $xhsObj->run($data,'PUT');

        }


        //error   price属于待定的更改，而original_price立即成功，另外报错
        //更新ITEM
        public function updateItem($authentication=array(),$data,$item_id){

            // $item_id = '595ef9b2f2c0743d34cc6478';

            // $data = array(
            //     'price' => '998',
            //     'original_price' => '1200'
            // );

            \Lib11\RB\Common::setAll($authentication);

            $url = '/ark/open_api/v1/item' . '/' . $item_id;
            $xhsObj = new \Lib11\RB\Tool\XHSInterface($url,$parameters);
            return $xhsObj->run($data,'PUT');

        }


        //error
        //提交审核
        public function submit($authentication=array(),$spl_id){

            // $spl_id = '595ef86ef2c0743d35cc6481';

            \Lib11\RB\Common::setAll($authentication);

            $url = '/ark/open_api/v1/spl' . '/' . $spl_id . '/spl_item/submit';
            $xhsObj = new \Lib11\RB\Tool\XHSInterface($url,$parameters);
            return $xhsObj->run(array(),'post');

        }


        public function showGoods($authentication=array(),$parameters=array()){

            //可通过商品id，条形码或小红书编码查询商品详情
            // $parameters = array(
            //     'barcode' => 'qwerdf',
            // );

            \Lib11\RB\Common::setAll($authentication);

            $url = '/ark/open_api/v1/items';
            $xhsObj = new \Lib11\RB\Tool\XHSInterface($url,$parameters);
            return $xhsObj->run(array(),'get');

        }


        public function showGoodsList($authentication=array(),$parameters=array(),$all=false){

            //可通过商品id，条形码或小红书编码查询商品详情
            // $parameters = array(
            //     'status' => 2,     //商品状态(0为编辑中，1为待审核，2为审核通过)
            //     'page_no' => 1,        //商品页数, 从第一页开始,默认为1
            //     'page_size' => 10,      //商品列表每页数量，默认为50，上限为500
            //     'buyable' => false,        //商品是否可售卖，true为在架上可售卖，false为已下架不可售卖
            //     'create_time_from' => '',       //商品创建时间开始时间，Unix-Time时间戳
            //     'create_time_to' => '',     //商品创建时间结束时间，Unix-Time时间戳
            //     'update_time_from' => '',       //商品更新时间开始时间，Unix-Time时间戳
            //     'update_time_to' => '',     //商品更新时间结束时间，Unix-Time时间戳
            //     'stock_gte' => '',      //库存大于等于某数
            //     'stock_lte' => '',      //库存小于等于某数
            // );

            \Lib11\RB\Common::setAll($authentication);

            //传递参数all=true时，获取列表中每个商品的详细信息
            if($all===true){
                $url = '/ark/open_api/v1/items';
            }else{
                $url = '/ark/open_api/v1/items/lite';
            }

            $xhsObj = new \Lib11\RB\Tool\XHSInterface($url,$parameters);
            return $xhsObj->run(array(),'get');

        }


        public function getInfoBySpu($authentication=array(),$spu_id){

            // $spu_id = '595ef86ef2c0743d35cc6481';

            \Lib11\RB\Common::setAll($authentication);

            $url = '/ark/open_api/v1/spu/' . $spu_id;
            $xhsObj = new \Lib11\RB\Tool\XHSInterface($url,$parameters);
            return $xhsObj->run(array(),'get');

        }


        public function available($authentication=array(),$data,$item_id){

            // $item_id = '595ef9b2f2c0743d34cc6478';

            // $data = array(
            //     'available' => false,
            // );

            \Lib11\RB\Common::setAll($authentication);

            $url = '/ark/open_api/v1/item' . '/' . $item_id . '/availability';
            $xhsObj = new \Lib11\RB\Tool\XHSInterface($url,$parameters);
            return $xhsObj->run($data,'PUT');

        }

    }