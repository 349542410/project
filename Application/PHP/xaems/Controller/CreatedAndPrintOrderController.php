<?php
        require_once("../Model/CreatedAndPrintOrder.php");
        require_once("../Model/QueryTrackingCenter.php");
        require_once("../Model/QueryExchangeRate.php");

        $inner_goods_info = $channels = array();

        //libxml_disable_entity_loader(false);

        //用户标识
        $customerIdentity = "BNGEWM"; //Man config设置
        //用户报价币种
        $currency = "CNY";

        //报价币种汇率
        //$currency_data = _QueryExchangeData($currency);

        //初始化货站信息
        //$trackingcodes = queryTrackingCenter($customerIdentity);


        //初始化
        $request_tracking_code = $request_channel_code = $min_value_unit = '';
        //初始化
        $request_data = $tracking_other_info = array();
        $request_data['CustomerIdentity'] = $customerIdentity;


        $model = new CreatedAndPrintOrder();

        //判断表单信息
        $getMethod = "POST";
        if ($getMethod === 'POST') {
            //根据仓库 code 获取 渠道集合
            $request_tracking_code = "HZ011";//Man config设置
            $request_channel_code = "CH0026";//Man config设置


            $postData = array(
                "form" => array(
                    'addressee' => array(
                        'ToAddress' => "MMMM",
                        'ToArea' => "MM",
                        'ToCity' => "MM",
                        'ToEmail' => "o00000.@qq.com",
                        'ToMobile' => "13000000000",
                        'ToName' => "MM",
                        'ToProvince' => "哪里",
                        'ToProvinceCode' => "19",
                        'ToZIP' => "518131",
                    ),

                    'ChannelCode' => $request_channel_code,
                    'CustomerIdentity' => $customerIdentity,


                    'Brands' => array(
                        0 => "Brands",
                    ),
                    'CategoryCode' => array(
                        0 => "350",
                    ),
                    'CurrencyCode' => array(
                        0 => "CNY",
                    ),
                    'GoodsName' => array(
                        0 => "GoodsName",
                    ),
                    'ModelNo' => array(
                        0 => "ModelNo",
                    ),
                    'Price' => array(
                        0 => "1",
                    ),
                    'Qty' => array(
                        0 => "1",
                    ),
                    'TariffNumber' => array(
                        0 => "19020412",
                    ),
                    'Unit' => array(
                        0 => "Unit",
                    ),

                    'HasPrepaid' => "true",
                    'HasReplaceUploadIdCard' => "",
                    'Height' => "1",
                    'IdCardNumber' => "",
                    'InsureStatus' => "true",
                    'Length' => "1",
                    'Origin' => "US",

                    'sender' => array(
                        'FromAddress' => "哪里",
                        'FromArea' => "哪里",
                        'FromCity' => "哪里",
                        'FromEmail' => "shawnli@qq.com",
                        'FromMobile' => "13006692876",
                        'FromName' => "13006692876",
                        'FromProvince' => "哪里",
                        'FromZIP' => "518131",
                    ),

                    'TrackingCenterCode' => $request_tracking_code,
                    'Weight' => "10",
                    'Width' => "10",
                )
            );


            $senderAddressData = array();

            //整合商品信息
            $model->setInnerGoods($postData);
            //整合发件人信息
            $model->setSender($postData['form']['sender']);
            //整合收件人信息
            $model->setAddressee($postData['form']['addressee']);
            $InsureRate = 0.02;
            $model->InsureRate = $InsureRate;

            $request_data = $model->getEntityData();

            $inner_goods_info = $request_data['Goods'];
            $model->setRequestData($request_data);
            $model->soapSubmit();
            if ($model->getResponseSuccess()) {
                $return_result = '生成成功';
                echo $return_result;
            } else {
                echo $model->getError()->message;
            }
        } else {
            $request_data = $model->getEntityData();
        }


/**
 * 生成运单 汇率查询
 */
function _QueryExchangeData($currency)
{

    $QueryExchange = new QueryExchangeRate(['Currency' => $currency]);

    $QueryExchange->setRequestData($QueryExchange->getEntityData());
    $QueryExchange->soapSubmit();
    $sendOrderInfo = array();
    if ($QueryExchange->getResponseSuccess()) {
        $QueryExchangeInfo = $QueryExchange->getResponseData();
        $QueryExchangeCurrencyInfo = $QueryExchangeInfo->QueryExchangeRateModel[0]->ChangeRate;
        return $QueryExchangeCurrencyInfo;
    } else {
        $err = $QueryExchange->getError()->message;
        return '';
    }

}


/**
 * 查询用户货站信息
 */
function queryTrackingCenter($customer_identity)
{
    $queryTrackingCenter = new QueryTrackingCenter();
    $queryTrackingCenter->setRequestData(['CustomerIdentity' => $customer_identity]);
    $queryTrackingCenter->soapSubmit();
    // 接口调用成功
    if ($queryTrackingCenter->getResponseSuccess()) {
        $service_data = $queryTrackingCenter->getResponseData();

        $service_data = (isset($service_data->SearchResultTrackingCenterInfo->TrackingCenterInfo) and (!empty($service_data->SearchResultTrackingCenterInfo->TrackingCenterInfo))) ? $service_data->SearchResultTrackingCenterInfo->TrackingCenterInfo : false;
        // 接口返回数据异常
        if ($service_data === false) {
            return false;
        } else {
            // 函数  getWarehouseAndChannel解析 服务器反回的数据
            $parse_data = getWarehouseAndChannel($service_data);
            if ($parse_data) {
                return $parse_data;
            } else {
                return false;
            }
        }
    } else {
        return false;
    }
}


/**
 * 根据跟踪号获取渠道
 */
function getChannelByTrackingcode($tracking_info, $channel_code)
{
    $return_data = [];
    if (isset($tracking_info[$channel_code]['Channeles']) and !empty($tracking_info[$channel_code]['Channeles'])) {
        foreach ($tracking_info[$channel_code]['Channeles'] as $k => $v) {
            $return_data[$v['ChannelCode']]['channel'] = $v['ChannelName'];
            if($v['PrintType'] == "Needle"){ //针式打印
                $return_data[$v['ChannelCode']]['printType'] = 1;
            }else{
                if($v['AllowedAuto'] == 1) { //小标签打印
                    $return_data[$v['ChannelCode']]['printType'] = 2;
                }else{ //普通标签打印
                    $return_data[$v['ChannelCode']]['printType'] = 3;
                }
            }
        }
    }
    return $return_data;
}



/**
 * 解析 仓库和渠道
 * @param $service_data
 */
function getWarehouseAndChannel($service_data)
{
    $return_data = [];
    foreach ($service_data as $k => $v) {
        $return_data[$v->TrackingCenterCode]['Country'] = $v->Country;
        $return_data[$v->TrackingCenterCode]['Currency'] = $v->Currency;
        $return_data[$v->TrackingCenterCode]['Describe'] = $v->Describe;
        $return_data[$v->TrackingCenterCode]['Title'] = $v->Title;
        $return_data[$v->TrackingCenterCode]['TrackingCenterCode'] = $v->TrackingCenterCode;
        foreach ($v->Channeles->ChannelModel as $x => $y) {
            $return_data[$v->TrackingCenterCode]['Channeles'][$y->ChannelCode]['ChannelCode'] = $y->ChannelCode;
            $return_data[$v->TrackingCenterCode]['Channeles'][$y->ChannelCode]['ChannelDesc'] = $y->ChannelDesc;
            $return_data[$v->TrackingCenterCode]['Channeles'][$y->ChannelCode]['ChannelName'] = $y->ChannelName;
            $return_data[$v->TrackingCenterCode]['Channeles'][$y->ChannelCode]['AllowedAuto'] = $y->AllowedAuto;
            $return_data[$v->TrackingCenterCode]['Channeles'][$y->ChannelCode]['PrintType'] = $y->PrintType;
            $return_data[$v->TrackingCenterCode]['Channeles'][$y->ChannelCode]['Currency'] =
                isset($y->ValueAddedServicePrices->Currency) ? $y->ValueAddedServicePrices->Currency : 'USD';
            $return_data[$v->TrackingCenterCode]['Channeles'][$y->ChannelCode]['FoundationReinforcement'] = isset($y->ValueAddedServicePrices->FoundationReinforcement) ? $y->ValueAddedServicePrices->FoundationReinforcement : 0;
            $return_data[$v->TrackingCenterCode]['Channeles'][$y->ChannelCode]['IntelligentExchangeBox'] = isset($y->ValueAddedServicePrices->IntelligentExchangeBox) ? $y->ValueAddedServicePrices->IntelligentExchangeBox : 0;
            $return_data[$v->TrackingCenterCode]['Channeles'][$y->ChannelCode]['InventoryServices'] = isset($y->ValueAddedServicePrices->InventoryServices) ? $y->ValueAddedServicePrices->InventoryServices : 0;
            $return_data[$v->TrackingCenterCode]['Channeles'][$y->ChannelCode]['OutBoxExchanges'] = isset($y->ValueAddedServicePrices->OutBoxExchanges) ? $y->ValueAddedServicePrices->OutBoxExchanges : 0;
            $return_data[$v->TrackingCenterCode]['Channeles'][$y->ChannelCode]['PackagePackingServices'] = isset($y->ValueAddedServicePrices->PackagePackingServices) ? $y->ValueAddedServicePrices->PackagePackingServices : 0;
            $return_data[$v->TrackingCenterCode]['Channeles'][$y->ChannelCode]['SpecialReinforcement'] = isset($y->ValueAddedServicePrices->SpecialReinforcement) ? $y->ValueAddedServicePrices->SpecialReinforcement : 0;
        }
    }
    return $return_data;
}




?>