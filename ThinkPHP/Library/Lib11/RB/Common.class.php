<?php

    namespace Lib11\RB;

    class Common{
        public static $url = 'http://flssandbox.xiaohongshu.com';
        private static $app_key;
        private static $app_secret;
        
        public static function setAll($arr=array()){
            if(!empty($arr['app_key'])||!empty($arr['app_secret'])){
                self::$url = 'https://ark.xiaohongshu.com/';
            }
            $arr['app_key'] ? self::$app_key = $arr['app_key'] : self::$app_key = '3e829b80ad';
            $arr['app_secret'] ? self::$app_secret = $arr['app_secret'] : self::$app_secret = '9f9c1db5e22cc0b775972d2ca11b0bd1';
        }

        public static function getKey(){
            return self::$app_key;
        }

        public static function getSecret(){
            return self::$app_secret;
        }
    }