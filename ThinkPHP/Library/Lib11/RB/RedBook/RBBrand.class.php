<?php

    namespace Lib11\RB\RedBook;

    class RBBrand{

        // 品牌搜索
        public function brandSearch($authentication=array(),$parameters=array()){

            // $parameters = array(
            //     'keyword' => '华为',   //查找关键字
            //     'page_no' => 2,
            //     'page_size' => 5,
            // );

            \Lib11\RB\Common::setAll($authentication);

            $url = '/ark/open_api/v1/brand_search';
            $xhsObj = new \Lib11\RB\Tool\XHSInterface($url,$parameters);
            return $xhsObj->run(array(),'get');

        }

    }