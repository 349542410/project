<?php

    namespace Lib11\Jos;

    class OrderLogic extends Logic{

        private $jd;

		public function __construct(){

			$this->jd = new \Lib11\Jos\Jdsdk\Entrance();

		}


        public function getOrderInfo($parameter){

            if(empty($parameter['request']['order_id'])){
                return $this->getInfo(false,'缺少必要的参数');
            }

            $parameter['request'] = array(
                'orderId' => $parameter['request']['order_id'],
            );

            $result = $this->getOrderInfoA($parameter);
            if(!$result['success']){
                return $result;
            }

            $arr = $result['data'];
            // dump($arr);

            if(empty($arr['paymentConfirmTime'])){
                return $this->getInfo(false,"订单未支付");
            }

            // 实际支付 = 商品总价 - 优惠卷金额 + 运费
            $couponPrice = 0;   //优惠卷金额
            foreach($arr['couponDetailList'] as $v){
                $couponPrice += $v['couponPrice'];
            }
            $orderPrice = 0;    //订单原始价格
            foreach($arr['itemInfoList'] as $v){
                $orderPrice += $v['jdPrice'];
            }
            //订单类型表
            $orderTypeMap = array(
                'WAIT_SELLER_STOCK_OUT' => '等待出库',
                'WAIT_GOODS_RECEIVE_CONFIRM' => '等待确认收货',
                'RECEIPTS_CONFIRM' => '收款确认（服务完成）',
                'WAIT_SELLER_DELIVERY' => '等待发货',
                'FINISHED_L' => '完成',
                'TRADE_CANCELED' => '取消',
                'LOCKED' => '已锁定',
            );

            $order_info = array(
                'buyer_account' => $arr['consigneeInfo']['fullname'],                       //买家用户名
                'buyer_phone' => $arr['consigneeInfo']['mobile'],                           //买家手机号
                'cert_id_no' => '',                                                         //买家身份证号码
                'cert_name' => '',                                                          //买家身份证名称
                'coupon_amount' => $couponPrice,                                            //优惠券优惠金额
                'deliver_time' => $arr['deliveryType'],                                     //发货时间
                'express_fee' => $arr['freightPrice'],                                      //邮费
                'finish_time' => '',                                                        //订单完成时间
                'invoice_amount' => '',                                                     //发票金额
                'invoice_title' => '',                                                      //发票抬头
                'need_invoice' => '',                                                       //是否需要发票(1 是 0 否)
                'order_origin_price' => $orderPrice,                                        //订单原始价格（不包含邮费）
                'order_real_price' => $arr['orderPayment'],                                 //订单实际价格（实付）
                'order_status' => $orderTypeMap[$arr['orderState']],                        //订单状态
                'pay_method_name' => '',                                                    //支付名称
                'pay_success_time' => $arr['paymentConfirmTime'],                           //支付时间 
                'receiver_name' => $arr['consigneeInfo']['fullname'],                       //收件人名称 
                'receiver_phone' => $arr['consigneeInfo']['mobile'],                        //收件人手机号
                'receiver_address_detail' => $arr['consigneeInfo']['fullAddress'],          //收货人详细地址 
                'receiver_province_name' => $arr['consigneeInfo']['province'],              //收件人省名称
                'receiver_city_name' => $arr['consigneeInfo']['city'],                      //收件人市级名称 
                'receiver_district_name' => $arr['consigneeInfo']['county'],                //手机人区名称  
                'receiver_post_code' => '',
                'tax_fee' => '',                                                            //订单税费
                'trade_no' => '',                                                           //订单交易流水号
            );

            $order_goods = array();
            foreach($arr['itemInfoList'] as $k=>$v){

                $order_goods[$k]['goods_id'] = $v['wareId'];
                $order_goods[$k]['product_name'] = $v['skuName'];
                $order_goods[$k]['origin_price'] = $v['jdPrice'];
                $order_goods[$k]['count'] = $v['itemTotal'];
                $order_goods[$k]['real_totle_price'] = $v['jdPrice'];
                $order_goods[$k]['activity_totle_amount'] = '';
                $order_goods[$k]['goods_no'] = $v['productNo'];
                $order_goods[$k]['barcode'] = $v['outerSkuId'];
                $order_goods[$k]['goods_attr_id'] = $v['skuId'];

            }

            $result['data'] = array(
                'order_info' => $order_info,
                'order_goods' => $order_goods,
            );
            return $result;

            // return $result;

        }





        public function getOrderInfoA($parameter){

            $authentication = $parameter['authentication'];
            $data = $parameter['request'];
            $data['optionalFields'] = 'orderinfo,orderTotalPrice,paymentConfirmTime,orderStateRemark,pauseBizInfo,pauseBizStatusList,pausebizstatus,bizStatus,bizType,pauseBizDataYy,ljDT,dbDT,codDT,venderId,orderRemark,orderEndTime,returnOrder,logisticsId,storeOrder,orderSellerPrice,parentOrderId,tuiHuoWuYou,invoiceInfo,orderStartTime,customs,orderId,waybill,venderRemark,serviceFee,orderState,orderSource,deliveryType,orderType,consigneeInfo,fullAddress,county,province,telephone,fullname,city,mobile,scDT,taxFee,idSopShipmenttype,vatInfo,phoneRegIstered,addressRegIstered,depositBank,vatNo,bankAccount,directParentOrderId,balanceUsed,modified,orderPayment,couponDetailList,coupondetail,couponType,couponPrice,skuId,orderId,invoiceCode,pin,payType,itemInfoList,iteminfo,productNo,outerSkuId,itemTotal,skuId,jdPrice,skuName,wareId,giftPoint,serviceName,customsModel,freightPrice,storeId,sellerDiscount,orderTotal';
            $method = 'jingdong.pop.order.get';
			$this->jd->init($authentication,$method,$data);
            $result = $this->jd->execute();

            if($result['orderDetailInfo']['apiResult']['success']){

                //成功
                return array(
                    'success' => true,
                    'data' => $result['orderDetailInfo']['orderInfo'],
                    'errorstr' => '',
                );

            }else{

                //失败
                return array(
                    'success' => false,
                    'data' => null,
                    'errorstr' => $result['orderDetailInfo']['apiResult']['chineseErrCode'],
                );

            }

        }


        

    }