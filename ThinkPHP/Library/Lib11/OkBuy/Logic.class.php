<?php

    namespace Lib11\OkBuy;

    class Logic{

        protected $errorList = array(
            '101'=>'协议参数必须填写',
            '102'=>'签名时间有误，签名时间与好乐买系统时间差距不能超过10分钟',
            '103'=>'ID有误，该ID不存在或者暂时不可使用',
            '104'=>'请求消息不是一个有效的json字符串',
            '105'=>'签名错误',
            '201'=>'业务参数("参数")必须填写',
            '202'=>'业务参数("参数")不符合要求',
            '203'=>'("参数")传入有误，系统中不存在该信息',
            '204'=>'系统异常，请联系技术人员',
            '205'=>'("参数")传入有误，该信息不符合操作要求',
            '301'=>'库存更新失败',
        );

        protected function getInfo($arr){
            if(!$arr['success']){
                return $arr;
            }else{
                // dump($arr);
                if($arr['data']['ErrorCode']!=0){
                    return array(
                        'success' => false,
                        'data' => false,
                        'errorstr' => $arr['data']['ErrorMessage'],
                    );
                }else{
                    $arr['data'] = $arr['data']['Result'];
                    return $arr;
                }
            }
        }

    }