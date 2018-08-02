<?php

    namespace Lib11\OkBuyAgent\Tools;

    class Common{
        public static $url = '';
        private static $key;
        private static $paypwd;
        private static $agentId;
        
        public static function setAll($arr=array()){
            if(!empty($arr['key'])||!empty($arr['paypwd'])||!empty($arr['agentId'])){
                self::$url = 'http://platform.okbuy.com';
            }
            $arr['key'] ? self::$key = $arr['key'] : self::$key = '';
            $arr['paypwd'] ? self::$paypwd = $arr['paypwd'] : self::$paypwd = '';
            $arr['agentId'] ? self::$agentId = $arr['agentId'] : self::$agentId = '';
        }

        public static function getKey(){
            return self::$key;
        }

        public static function getPaypwd(){
            return self::$paypwd;
        }

        public static function getAgentId(){
            return self::$agentId;
        }
    }