<?php

    namespace Lib11\OkBuy;

    class Common{
        public static $url = 'http://49.4.161.45:9011';

        private static $key;
        private static $userId;

        private static function transform($arr){
            return array(
                'key' => $arr['app_key'],
                'userId' => $arr['app_secret'],
            );
        }
        
        public static function setAll($arr=array()){
            $arr = self::transform($arr);
            if(!empty($arr['key'])||!empty($arr['userId'])){
                self::$url = 'http://platform.okbuy.com';
            }
            $arr['key'] ? self::$key = $arr['key'] : self::$key = '2144a8e6c1bcf83698bfd7817898bc7b';
            $arr['userId'] ? self::$userId = $arr['userId'] : self::$userId = 828;
        }

        public static function getKey(){
            return self::$key;
        }

        public static function getUserId(){
            return self::$userId;
        }
    }