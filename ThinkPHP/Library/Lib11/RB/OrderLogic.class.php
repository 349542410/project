<?php

    namespace Lib11\RB;

    class OrderLogic extends Logic{

        private $_orderObj;

        public function __construct(){

            $this->_orderObj = new \Lib11\RB\RedBook\RBOrder();

        }



        public function getOrderInfo($parameters=array()){

            $authentication = $parameters['authentication'];
            $order_id = $parameters['request']['order_id'];

            $result = $this->_getOrderInfo($authentication,$order_id);

            // dump($result);
            if(empty($result['data'])||empty($result['data']['pay_time'])){
                $result['success'] = false;
                $result['data'] = null;
                $result['errorstr'] = '未查询到该订单，或者订单未支付';
                return $result;
            }


            //拼凑order_info
            $arr = $result['data'];

            $stat = array(
                'waiting' => '待配货',
                'shipped' => '已发货',
                'received' => '已收货',
            );

            $order_info = array(
                'buyer_account' => $arr['receiver_phone'],      //买家用户名
                'buyer_phone' => $arr['receiver_phone'],        //买家手机号
                'cert_id_no' => '',                             //买家身份证号码
                'cert_name' => '',                              //买家身份证名称
                'coupon_amount' => '',                          //优惠券优惠金额
                'deliver_time' => $arr['delivery_time_preference'],       //发货时间
                'express_fee' => '',                            //邮费
                'finish_time' => date('Y-m-d H:i:s',$arr['confirm_time']),                    //订单完成时间
                'invoice_amount' => '',                         //发票金额
                'invoice_title' => '',                          //发票抬头
                'need_invoice' => '',                           //是否需要发票(1 是 0 否)
                'order_origin_price' => '',                     //订单原始价格（不包含邮费）
                'order_real_price' => $arr['pay_amount'],       //订单实际价格（实付）
                'order_status' => $stat[$arr['status']],        //订单状态：waiting待配货,shipped已发货,received已收货
                'pay_method_name' => '',                        //支付名称
                'pay_success_time' => date('Y-m-d H:i:s',$arr['pay_time']),         //支付时间 
                'receiver_name' => $arr['receiver_name'],       //收件人名称 
                'receiver_phone' => $arr['receiver_phone'],     //收件人手机号
                'receiver_address_detail' => $arr['receiver_address'],      //收货人详细地址 
                'receiver_province_name' => $arr['province'],               //收件人省名称
                'receiver_city_name' => $arr['city'],                       //收件人市级名称 
                'receiver_district_name' => $arr['district'],               //手机人区名称  
                'receiver_post_code' => '',
                'tax_fee' => '',                            //订单税费
                'trade_no' => '',                           //订单交易流水号
            );


            //拼凑order_goods
            $order_goods = array();

            foreach($arr['item_list'] as $k=>$v){
                $order_goods[$v['skucode']][] = $v;
            }

            // dump($order_goods);

            $goods_m = new GoodsLogic();

            $i = 0;
            foreach($order_goods as $k=>$v){

                $count = 0;
                $totle_price = 0;
                foreach($v as $v1){
                    $count += $v1['qty'];
                    $totle_price += $v1['pay_price'];
                }

                $parameter = array(
                    'authentication' => $authentication,
                    'criteria' => array(
                        'skucode' => $v[0]['skucode'],
                    ),
                );
                $r = $goods_m->getGoodsInfoByItem($parameter);
                $goods_id = $r['data'][0]['item_id'];
                if(is_null($goods_id)){
                    $goods_id = '';
                }

                $order_goods_f[$i]['goods_id'] = $goods_id;                 //商品id（可能为空）
                $order_goods_f[$i]['product_name'] = $v[0]['item_name'];    //商品名称
                $order_goods_f[$i]['origin_price'] = $v[0]['price'];        //商品原始价格（单价）
                $order_goods_f[$i]['count'] = $count;                       //购买数量
                $order_goods_f[$i]['real_totle_price'] = $totle_price;      //实际价格（总价）
                $order_goods_f[$i]['activity_totle_amount'] = $v[0]['price']*$count-$totle_price;   //优惠金额（原总价减实际总价）
                $order_goods_f[$i]['goods_no'] = $k;                        //商品货号
                $order_goods_f[$i]['barcode'] = $v[0]['barcode'];           //商品条形码

                $i++;

            }

            $result['data'] = array(
                'order_info' => $order_info,
                'order_goods' => $order_goods_f,
            );




            return $result;

        }















        public function getOrderList($authentication=array(),$parameters=array()){

            $result = $this->_orderObj->getOrderList($authentication,$parameters);
            return $this->getInfo($result);

        }


        private function _getOrderInfo($authentication=array(),$order_id=''){

            if(empty($order_id)){
                return $this->getInfo(false,'缺少必要的参数order_id');
            }
            $result = $this->_orderObj->orderOperation($authentication,$order_id);
            return $this->getInfo($result);

        }

    }