<?php

    namespace Lib11\RB\RedBook;

    class RBCategory{

        //获取子分类
        public function categorySearch($authentication=array(),$parameters=array()){

            //查找子分类，多级分类以逗号隔开
            //如果不提供参数，则返回所有一级分类
            // $parameters = array(
            //     'category_ids' => '52ce1c02b4c4d649b58b8935,54f19451b4c4d660f7f18acc',
            // );

            \Lib11\RB\Common::setAll($authentication);

            $url = '/ark/open_api/v1/categories';
            $xhsObj = new \Lib11\RB\Tool\XHSInterface($url,$parameters);
            return $xhsObj->run(array(),'get');

        }


        //由末级分类获取规格
        public function getSpec($authentication=array(),$category_id){

            //末级分类的id
            // $category_id = '572dd172939e250d08ec5967';

            \Lib11\RB\Common::setAll($authentication);

            $url = '/ark/open_api/v1/categories' . '/' . $category_id . '/variations';
            $xhsObj = new \Lib11\RB\Tool\XHSInterface($url,$parameters);
            return $xhsObj->run(array(),'get');

        }


        //由末级分类获取产品参数
        public function getProduct($authentication=array(),$category_id){

            //末级分类的id
            // $category_id = '572dd172939e250d08ec5967';

            \Lib11\RB\Common::setAll($authentication);

            $url = '/ark/open_api/v1/categories' . '/' . $category_id . '/attribute_options';
            $xhsObj = new \Lib11\RB\Tool\XHSInterface($url,$parameters);
            return $xhsObj->run(array(),'get');

        }

    }