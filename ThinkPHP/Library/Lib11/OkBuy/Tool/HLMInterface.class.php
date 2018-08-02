<?php

    //好乐买接口工具类

    namespace Lib11\OkBuy\Tool;

    class HLMInterface{

        private $signDate;
        private $key;
        private $userId;

        private $error = '';

        public $request;    //查询数据
        public $sign;

        public function __construct($request=array(),$json_encode=true){
            $this->key = \Lib11\OkBuy\Common::getKey();
            $this->userId = \Lib11\OkBuy\Common::getUserId();

            if($json_encode){
                $this->request = json_encode($request);
            }else{
                $this->request = $request;
            }
            $this->signDate = date("Y-m-d H:i:s");
            $sign_data = 'SignDate=' . $this->signDate . ',Request=' . $this->request;
            $this->sign = hash_hmac('md5',$sign_data,$this->key);
        }

        public function run($parameter){

            $post_data = array(
                'SignDate' => $this->signDate,
                'UserId' => $this->userId,
                'Request' => $this->request,
                'Sign' => $this->sign,
            );

            $url = \Lib11\OkBuy\Common::$url . $parameter;
            $http = new \Org\MK\HTTP();
            $outstr = $http->post($url, $post_data);
            $response = json_decode($outstr,1);

            //验签
            $sign_data = 'SignDate='.$response['SignDate'].',Result='.$response['Result'];
            $sign_new = hash_hmac("md5",$sign_data, $this->key);
            if ( $response['ErrorCode']==0 && $sign_new != $response['Sign'] ){
                $this->error = "验证签名失败";
                return false;
            }

            $response['Result'] = json_decode($response['Result'],1);
            return $response;

        }

        public function getError(){
            return $this->error;
        }

    }