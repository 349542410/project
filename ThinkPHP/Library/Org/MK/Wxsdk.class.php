<?php
namespace Org\MK;
class Wxsdk{
    protected $config = array(
        'appId'           	=> '',
        'appSecret'         => ''
    );	
	public function __construct($config = array()){
		$this->config   =   array_merge($this->config, $config);
	}
    private function getAccessToken(){
        // access_token 应该全局存储与更新，以下代码以写入到文件中做示例
        // Man160202 文件不存在时会出现，所以加 @ ,!$data,new stdClass
        @$data = json_decode(file_get_contents($this->tokenfile));
        if (!$data || $data->expire_time < time()) {
            $url = "https://open.weixin.qq.com/cgi-bin/gettoken?appid=$this->config[appId]&appsecret=$this->appSecret";
            https://api.weixin.qq.com/sns/oauth2/access_token?appid=$this->config[appId]&secret=$this->config[appSecret]&code=CODE&grant_type=authorization_code
            $res = json_decode($this->httpGet($url));
            $access_token = $res->access_token;
            if ($access_token) {

                //Man160202
                if(!$data){
                    $data = new stdClass();
                }

                $data->expire_time = time() + 7000;
                $data->access_token = $access_token;
                $fp = fopen($this->tokenfile, "w");
                fwrite($fp, json_encode($data));
                fclose($fp);
            }
        } else {
          $access_token = $data->access_token;
        }
        return $access_token;
    }
    //20160218读取登录员id
    public function urlinget($REDIRECT_URI,$code=null){
        if($code==null){
            $url    = "https://open.weixin.qq.com/connect/oauth2/authorize?appid=".$this->config['appId']."&redirect_uri=$REDIRECT_URI&response_type=code&scope=SCOPE&state=STATE#wechat_redirect";
            return $url;
        }else{
            $url    = "https://qyapi.weixin.qq.com/cgi-bin/user/getuserinfo?code=$code&access_token=".$this->getAccessToken();
            return $this->httpGet($url);
        }
    }
    public function getUserInfo($userid){
        $atstr      = $this->getAccessToken();
        $url        = "https://qyapi.weixin.qq.com/cgi-bin/user/convert_to_openid?access_token=$atstr";
        $data       = '{"userid": "'.$userid.'","agentid":'.$this->agentid.'}';
        $dstr       = $this->https_post($url,$data);
        //echo $dstr;
        $data2      = json_decode($dstr,true);
        $openid     = $data2['openid'];
        //echo $atstr;
        //下面这步提示 token错误 160218
        $url        = "https://api.weixin.qq.com/cgi-bin/user/info?access_token=$atstr&openid=$openid&lang=zh_CN";
        return $this->httpGet($url);
    }    
}