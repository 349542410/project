<?php

    namespace Lib11\TencentCOS;
    use QCloud\Cos\Api;
    
    class Cos{

        public function __construct(){

        }

        public function upload($args){

            require(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'cos-php-sdk-v4' . DIRECTORY_SEPARATOR . 'include.php');

            $conf = array(
                'app_id' => C('COS')['app_id'],
                'secret_id' => C('COS')['secret_id'],
                'secret_key' => C('COS')['secret_key'],
                'region' => C('COS')['region'],
                'timeout' => C('COS')['timeout'],
            );

            date_default_timezone_set('PRC');
            $cosApi = new Api($conf);

            // Upload file into bucket.
            $bucket = C('COS')['bucket'];
            $src = $args['src'];
            $dst = $args['dst'];
            $ret = $cosApi->upload($bucket, $src, $dst);
            // var_dump($ret);
            if($ret['code']==0&&$ret['message']=='SUCCESS'){
                return array(
                    'success' => true,
                    'message' => ''
                );
            }else{
                return array(
                    'success' => false,
                    'message' => $ret['message'],
                );
            }

        }

        public function download($args){
            
            require(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'cos-php-sdk-v4' . DIRECTORY_SEPARATOR . 'include.php');

            $conf = array(
                'app_id' => C('COS')['app_id'],
                'secret_id' => C('COS')['secret_id'],
                'secret_key' => C('COS')['secret_key'],
                'region' => C('COS')['region'],
                'timeout' => C('COS')['timeout'],
            );
            date_default_timezone_set('PRC');
            $cosApi = new Api($conf);

            // Upload file into bucket.
            $bucket = C('COS')['bucket'];
            $src = $args['src'];
            $dst = $args['dst'];
            // $ret = $cosApi->download($bucket, $src, $dst);
            // dump($ret);
            return $cosApi->download($bucket, $src, $dst);

        }

    }