<?php

    //小红书接口工具类

    namespace Lib11\RB\Tool;
    
    class XHSInterface{

        // private $error = '';

        private $time;
        private $app_key;
        private $app_secret;
        private $sign;

        public $url;                //不带参数的url，如 /ark/open_api/v1/items
        public $url_parameters;     //url参数列表，如 array('status'=>0,'page_no'=>1)
        public $url_complete;       //完整的url，如 /ark/open_api/v1/items?status=0&page_no=1

        private $tf;    //ToolsFunction工具函数类

        /**
        *
        *   $url : 不带参数的url，如 /ark/open_api/v1/items
        *
        *   $url_parameters : url参数列表，如 array('status'=>0,'page_no'=>1)
        *
        */
        public function __construct($url , $url_parameters=array()){

            $this->tf = new ToolsFunction();
            $this->app_key = \Lib11\RB\Common::getKey();
            $this->app_secret = \Lib11\RB\Common::getSecret();

            $this->url_parameters = $url_parameters;
            $this->url = $url;
            $this->time = time();

            if(!empty($url_parameters)){
                $this->url_complete = $url . '?' . $this->tf->ass_parameters($url_parameters);
            }else{
                $this->url_complete = $url;
            }

            $arguments = $url_parameters;
            $arguments['timestamp'] = $this->time;
            $arguments['app-key'] = $this->app_key;

            ksort($arguments,SORT_STRING);
            $str = $url . '?' . $this->tf->ass_parameters($arguments) . $this->app_secret;

            // dump($this->time);
            // dump($str);
            // dump($this->url_complete);
            // dump($this->url);

            $this->sign = md5($str);

            // dump($this->sign);

        }

        public function run($data=array(),$method='post'){
            
            $header = array(
                'content-type:application/json;charset=utf-8',
                'timestamp:' . $this->time,
                'app-key:' . $this->app_key,
                'sign:' . $this->sign
            );

            $result = $this->tf->postCurl( \Lib11\RB\Common::$url . $this->url_complete , json_encode($data) , $header , $method);
            $result['data'] = json_decode($result['data'],true);

            // dump($result);
            if($result['success']===true){
                // if(!empty($result['data']['error_msg'])){
                //     $result['data']['error']['str'] = $result['data']['error_msg'];
                //     $result['data']['error']['json'] = json_decode($result['data']['error_msg']);
                //     unset($result['data']['error_msg']);
                // }

                // dump($result);

                return $result['data'];
            }else{
                // $this->error = $result['error'];
                // return false;
                echo '<span style="color:red">-------error--------</span><br /><br />';
                echo $result['error'];
                die;
            }

        }

        // public function getError(){
        //     return $this->error;
        // }

    }