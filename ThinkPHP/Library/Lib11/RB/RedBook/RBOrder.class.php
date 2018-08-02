<?php

    namespace Lib11\RB\RedBook;

    class RBOrder{

        //分页获取最多半小时内创建的所有订单信息
        //支持所有物流模式
        public function getOrderInfoLatest($authentication=array(),$parameters=array()){

            // $parameters = array(
            //     'order_time_from' => time() - 15*60,     //必填
            //     'order_time_to' => time(),               //必填
            //     'page_no' => 1,
            //     'page_size' => 10,
            // );

            \Lib11\RB\Common::setAll($authentication);

            $url = '/ark/open_api/v0/packages/latest_packages';
            $xhsObj = new \Lib11\RB\Tool\XHSInterface($url,$parameters);
            return $xhsObj->run(array(),'get');

        }


        //分页获取被确认的订单列表
        public function getOrderList($authentication=array(),$parameters=array()){

            // $parameters = array(
            //     'status' => '',     //订单状态, waiting待配货,shipped已发货,received已收货
            //     'page_no' => 1,
            //     'page_size' => 10,
            //     'start_time' => 0,
            //     'end_time' => 0,
            // );

            \Lib11\RB\Common::setAll($authentication);

            $url = '/ark/open_api/v0/packages';
            $xhsObj = new \Lib11\RB\Tool\XHSInterface($url,$parameters);
            return $xhsObj->run(array(),'get');

        }


        //查询订单状态
        public function orderStatus($authentication=array(),$parameters=array()){

            // $parameters = array(
            //     'package_ids' => '',    //订单id，多个以逗号分隔，一次最多可查询20条
            // );

            \Lib11\RB\Common::setAll($authentication);

            $url = '/ark/open_api/v0/packages/packages_status';
            $xhsObj = new \Lib11\RB\Tool\XHSInterface($url,$parameters);
            return $xhsObj->run(array(),'get');

        }


        //订单详情 & 订单发货
        //如无$data，则查询订单详情，否则为订单发货
        public function orderOperation($authentication=array(),$order_id='',$data=array()){

            // $data = array(
            //     'status' => 'shipped',                      //更新订单状态为发货状态
            //     'express_company_code' => 'zhongtong',      //订单使用的快递公司编码
            //     'express_no' => '34732472',                 //订单使用的快递单号
            // );

            \Lib11\RB\Common::setAll($authentication);

            $url = '/ark/open_api/v0/packages/' . $order_id;

            $xhsObj = new \Lib11\RB\Tool\XHSInterface($url,$parameters);
            if(empty($data)){
                return $xhsObj->run(array(),'get');
            }else{
                return $xhsObj->run($data,'put');
            }

        }


        //获取小红书系统中所有支持发货使用的快递公司列表
        public function getExpressCompanyList($authentication=array()){

            \Lib11\RB\Common::setAll($authentication);

            $url = '/ark/open_api/v0/express_companies';
            $xhsObj = new \Lib11\RB\Tool\XHSInterface($url,$parameters);
            return $xhsObj->run(array(),'get');

        }

    }