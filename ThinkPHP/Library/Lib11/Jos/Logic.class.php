<?php

    namespace Lib11\Jos;

    class Logic{

        protected $errcode = array(
            '1' => '服务不可用',
            '2' => '限制时间内调用失败次数',
            '3' => '请求被禁止',
            '4' => '缺少版本参数',
            '5' => '不支持的版本号',
            '6' => '非法的版本参数',
            '7' => '缺少时间戳参数',
            '8' => '非法的时间戳参数',
            '9' => '缺少商家Id参数',
            '10' => '无效的商家Id参数',
            '11' => '缺少签名参数',
            '12' => '无效签名',
            '13' => '无效数据格式',
            '14' => '缺少方法名参数',
            '15' => '不存在的方法名',
            '16' => '缺少流水号参数',
            '17' => '流水号已经存在',
            '18' => '缺少access_token参数',
            '19' => '无效access_token',
            '20' => '缺少app_key参数',
            '21' => '无效app_key',
            '22' => '授权者不是商家',
            '23' => '该API已经停用',
            '24' => '无权调用API',
            '25' => '此应用不是上线状态',
            '26' => '缺少mobile参数',
            '27' => '无效mobile',
            '43' => '系统处理错误',
            '50' => '无效调用',
            '60' => '参数｛0｝不合法，请参照帮助文档确认！',
            '61' => '参数｛0｝值不合法，请参照帮助文档确认！',
            '62' => 'json转换时错误，错误的请求参数',
            '63' => 'json格式不合法',
            '64' => '此类型商家无权调用本接口',
            '65' => '平台连接后端服务超时',
            '66' => '平台连接后端服务不可用',
            '67' => '平台连接后端服务处理过程中出现未知异常信息',
            '68' => '验证可选字段异常信息',
            '69' => '获取数据失败',
            '70' => '该订单正在出库中',
            '71' => '当前的ID不属于此商家',
            '72' => '当前的用户不是此类型（如FBP, SOP等）的商家',
            '73' => '该api是增值api，请将您的app入住云鼎平台方可调用',
        );

        protected function getInfo($result,$errorinfo=''){

            if($result===false){
                return array(
                    'success' => false,
                    'data' => null,
                    'errorstr' => $errorinfo,
                );
            }else{

                if(!empty($result['success']) && $result['success']===false){
                    return array(
                        'success' => false,
                        'data' => null,
                        'errorstr' => $this->errcode[$result['code']],
                    );
                }

                if($result['code']!=='0'){
                    return array(
                        'success' => false,
                        'data' => null,
                        'errorstr' => $this->errcode[$result['code']],
                    );
                }else{
                    return array(
                        'success' => true,
                        'data' => $result['page']['data'],
                        'errorstr' => '',
                    ); 
                }

            }

        }

    }