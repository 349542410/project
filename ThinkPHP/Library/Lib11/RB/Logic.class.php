<?php

    namespace Lib11\RB;

    class Logic{

        protected $error_code = array(
            '500'   =>  '系统内部错误',
            '502'   =>  '系统内部错误',
            '401'   =>  '未授权',
            '403'   =>  '签名验证失败',
            '-9000'   =>  '品牌ID不存在',
            '-9001'   =>  '规格ID不存在',
            '-9003'   =>  '找不到条码对应的商品',
            '-9004'   =>  '原产国错误',
            '-9005'   =>  '计量单位错误',
            '-9006'   =>  '种类ID不存在',
            '-9007'   =>  '对商品无访问权限',
            '-9008'   =>  '毛重或净重小于或等于0',
            '-9009'   =>  '规格值不符合要求（不得为空）',
            '-9010'   =>  '分类不符合要求（不得为空）',
            '-9011'   =>  '数据中字段缺失',
            '-9012'   =>  '件数不得为0',
            '-9013'   =>  '数据中字段类型有误',
            '-9014'   =>  '存在正在审核的改动',
            '-9015'   =>  '商品规格不一致',
            '-8001'   =>  'sku不存在',
            '-8002'   =>  '库存不足，减库存操作失败',
            '-8003'   =>  '库存增减失败',
            '-8004'   =>  '同步库存值小于0',
            '-8005'   =>  '库存值qty缺失',
            '-7001'   =>  '订单查询开始时间大于结束时间',
            '-7002'   =>  '订单状态非法',
            '-7003'   =>  '订单不存在',
            '-7004'   =>  '订单状态不是安全检查通过或配货中，打包失败',
            '-7005'   =>  '快递公司编码错误',
            '-7006'   =>  '此接口不支持该卖家的物流模式 接口使用错误',
            '-7007'   =>  '批次不存在',
            '-7008'   =>  '批次已经配货',
            '-7009'   =>  '发运单已经存在',
            '-7010'   =>  '发运单创建失败',
            '-7011'   =>  '同步至WMS失败',
            '-7012'   =>  '不支持该口岸',
            '-7013'   =>  '上传订单数量超出限制',
            '-7014'   =>  '上传失败',
            '-7015'   =>  '缺少国际物流信息',
            '-7016'   =>  '缺少批次号',
            '-7017'   =>  '批次中无包裹',
            '-7018'   =>  '批次发运失败',
            '-7019'   =>  '批次已经发运',
            '-7020'   =>  '状态更新失败，请先更新“清关完成”再更新“配货中”',
            '-7021'   =>  '税则号未收录，请填入海关10位税则号，若无税则号请联系小红书Tech support',
        );

        protected function getInfo($result,$error_info=''){

            $arr = array();
            if(false!==$result){
                if(!$result['success']){
                    if($result['error_code']==-10000){
                        $arr['success'] = false;
                        // $arr['data'] = null;
                        $arr['data'] = $result['data'];
                        // $arr['errorstr'] = json_decode($result['error_msg'],true);
                        $arr['errorstr'] = $result['error_msg'];
                    }elseif(!empty($error_code[$result['error_code']])){
                        $arr['success'] = false;
                        $arr['data'] = null;
                        $arr['errorstr'] = $error_code[$result['error_code']];
                    }else{
                        $arr['success'] = false;
                        $arr['data'] = null;
                        $arr['errorstr'] = $result['error_msg'];
                    }
                    return $arr;
                }else{
                    $arr['success'] = true;
                    // $arr['data'] = array(

                    // );
                    $arr['data'] = $result['data'];
                    $arr['errorstr'] = null;
                    return $arr;
                }
            }else{
                $arr['success'] = false;
                $arr['data'] = null;
                $arr['errorstr'] = $error_info;
                return $arr;
            }

        }

    }