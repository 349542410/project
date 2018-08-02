<?php

    namespace Lib11\OkBuy;

    class OBOrderLogic extends Logic{


        //获取订单列表接口
        public function getOrderList($authentication=array(),$data=array()){

            // $data = array(
            //     'OrderCodes' => array(      //订单号(最多100个)

            //     ),
            //     'OrderDate' => array(       //成单时间 array(“Start”⇒,'End'⇒)

            //     ),
            //     'OrderStatus' => 4,         //array(2,3,4,6)中的一种；订单状态:确认有货=4,部分发货=6,全部发货=3,已取消=2
            //     'Page' => 1,
            //     'PageSize' => 10,
            // );

            \Lib11\OkBuy\Common::setAll($authentication);

            $interface = 'historyorder';

            $obj = new \Lib11\OkBuy\Drive\HLMDrive();
            return $this->getInfo($obj->distribution($interface,$data));
            
        }


        //获取订单详细信息
        public function getOrderInfo($parameters=array()){

            // $data = array(
            //     'OrderCode' => ''
            // );

            $authentication=$parameters['authentication'];
            $data=array(
                'OrderCode' => $parameters['request']['order_id'],
            );

            \Lib11\OkBuy\Common::setAll($authentication);

            $interface = 'getOdrDetailInfo';
            $obj = new \Lib11\OkBuy\Drive\HLMDrive();
            $result = $this->getInfo($obj->distribution($interface,$data));

            // dump($result);

            $arr = $result['data']['OrderList'][0];
            //未支付
            if($arr['IsPaid']!=='1'||empty($arr['PayDT'])){
                $result['success'] = false;
                $result['data'] = null;
                $result['errorstr'] = '订单未支付';
                return $result;
            }
            //已取消
            if($arr['Stat']==='2'){
                $result['success'] = false;
                $result['data'] = null;
                $result['errorstr'] = '订单已取消';
                return $result;
            }
            //订单状态
            $stat = array(
                '4' => '确认有货',
                '5' => '确认无货',
                '6' => '部分发货',
                '3' => '全部发货',
                '2' => '已取消',
            );

            //拼装order_info
            $order_info = array(
                'buyer_account' => $arr['PhoneNumber'],     //买家用户名（没有，和手机号相同）
                'buyer_phone' => $arr['PhoneNumber'],       //买家手机号（没有，和收件人手机号）
                'cert_id_no' => '',                         //买家身份证号码（无）
                'cert_name' => '',                          //买家身份证名称（无）
                'coupon_amount' => '',                      //优惠券优惠金额（无）
                'deliver_time' => $arr['DeliveryDT'],       //发货时间
                'express_fee' => $arr['AddPr'],             //邮费
                'finish_time' => '',                        //订单完成时间
                'invoice_amount' => '',                     //发票金额
                'invoice_title' => $arr['InvoiceInfo']['marks'],        //发票抬头
                'need_invoice' => 1,                                    //是否需要发票(1 是 0 否)
                'order_origin_price' => $arr['ItemPr'],                 //订单原始价格（不包含邮费）
                'order_real_price' => $arr['TotalAmount'],              //订单实际价格（实付）
                'order_status' => $stat[$arr['Stat']],                  //订单状态：确认有货=4,确认无货=5,部分发货=6,全部发货=3,已取消=2
                'pay_method_name' => $arr['PayModeDscrp'],              //支付名称
                'pay_success_time' => $arr['PayDT'],        //支付时间 
                'receiver_name' => $arr['AddressName'],     //收件人名称 
                'receiver_phone' => $arr['PhoneNumber'],    //收件人手机号
                'receiver_address_detail' => '-' . $arr['AddressProvince'] . $arr['AddressCity'] . $arr['AddressArea'],            //收货人详细地址 
                'receiver_province_name' => $arr['AddressProvince'],    //收件人省名称
                'receiver_city_name' => $arr['AddressCity'],            //收件人市级名称 
                'receiver_district_name' => $arr['AddressArea'],        //手机人区名称  
                'receiver_post_code' => '',
                'tax_fee' => '',                            //订单税费
                'trade_no' => '',                           //订单交易流水号
            );
            //不需要发票
            if($arr['InvoiceInfo']['marks']==null){
                $order_info['need_invoice'] = 0;
                $order_info['invoice_amount'] = 0;
            }


            //拼装order_goods
            $order_goods = array();

            //数组转换为格式良好的形式
            foreach($arr['Products'] as $k=>$v){
                $order_goods[$v['BarCode']][] = $v;
            }

            $order_goods_f = array();
            $i = 0;
            foreach($order_goods as $k=>$v){

                $totle_price = 0;
                $count = 0;
                foreach($v as $v1){
                    $totle_price += $v1['TruePrice'];
                    $count += $v1['Number'];
                }

                $order_goods_f[$i]['goods_id'] = $k;                        //商品id
                $order_goods_f[$i]['product_name'] = $v[0]['Name'];         //商品名称
                $order_goods_f[$i]['origin_price'] = '';                    //商品原始价格
                $order_goods_f[$i]['count'] = $count;                       //购买数量
                $order_goods_f[$i]['real_totle_price'] = $totle_price;      //实际价格
                $order_goods_f[$i]['activity_totle_amount'] = '';           //优惠金额
                $order_goods_f[$i]['goods_no'] = $v[0]['Code'];             //商品货号
                $order_goods_f[$i]['barcode'] = $k;                         //商品条形码

                $i++;

            }


            //返回拼装好的数组
            $result['data'] = array(
                'order_info' => $order_info,
                'order_goods' => $order_goods_f,
            );

            return $result;

        }
        

    }