<?php

    namespace Lib11\OkBuyAgent\Tools;
    
    class OBDrive{

        private $errorList = array(     //错误列表
            '101'=>'协议参数必须填写',
            '102'=>'签名时间有误，签名时间与好乐买系统时间差距不能超过10分钟',
            '103'=>'代理商ID有误，该代理商不存在或者暂时不可使用',
            '104'=>'请求消息不是一个有效的json字符串',
            '105'=>'签名错误',
            '106'=>'代理商ID有误，该代理商没有开通API权限',
            '107'=>'当日请求次数超限(上限："参数"次)',
            '201'=>'业务参数("参数")必须填写',
            '202'=>'业务参数("参数")不符合要求',
            '203'=>'("参数")传入有误，系统中不存在该信息',
            '204'=>'系统异常，请联系技术人员',
            '205'=>'下单失败，信息("失败信息")',
            '206'=>'取消订单失败，信息("失败信息")',
        );
        private $allowList = array(     //允许接口列表
            'stock',
            'product',
        );

        public function __construct(){

            

        }

        //参数分发
        //  interface : 接口方法名
        //  data ： 接口需要的数据
        public function distribution($interface,$data){

            if(!in_array($interface,$this->allowList)){
                $this->error = "接口方法不在允许的列表内";
                return false;
            }

            $url = '/agent/agentapi/' . $interface;
            $signObj = new OBInterface($data);
            $result = $signObj->run($url);

            // if($result === false){
            //     $this->error = $signObj->getError();
            //     return false;
            // }else{
            //     return $result;
            // }
            return $result;

        }

    }