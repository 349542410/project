<?php

    namespace Lib11\OkBuyAgent\Tools;

    class OBInterface{

        private $signDate;
        private $paypwd;
        private $key;
        private $agentId;

        private $error = '';

        public $request;    //查询数据
        public $sign;

        public function __construct($request=array(),$json_encode=true){
            $this->paypwd = Common::getPaypwd();
            $this->key = Common::getKey();
            $this->agentId = Common::getAgentId();

            if($json_encode){
                $this->request = json_encode($request);
            }else{
                $this->request = $request;
            }
            $this->signDate = date("Y-m-d H:i:s");
            $sign_data = 'SignDate=' . $this->signDate . ',Request=' . $this->request;
            // dump($sign_data);
            // dump($this->key);
            $this->sign = hash_hmac('md5',$sign_data,($this->key).($this->paypwd));
        }

        public function run($parameter){

            $post_data = array(
                'SignDate' => $this->signDate,
                'AgentId' => $this->agentId,
                'Request' => $this->request,
                'Sign' => $this->sign,
            );

            $url = \Lib11\OkBuy\Common::$url . $parameter;
            $http = new \Org\MK\HTTP();
            $outstr = $http->post($url, $post_data);
            $response = json_decode($outstr,true);

            //验签
            $sign_data = 'SignDate='.$response['SignDate'].',Result='.$response['Result'];
            $sign_new = hash_hmac("md5",$sign_data, ($this->key).($this->paypwd));
            if ( $response['ErrorCode']==0 && $sign_new != $response['Sign'] ){
                $this->error = "验证签名失败";
                return false;
            }

            $response['Result'] = json_decode($response['Result'],true);
            return $response;

        }

        public function getError(){
            return $this->error;
        }

    }