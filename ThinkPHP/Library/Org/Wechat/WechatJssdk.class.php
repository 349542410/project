<?php
// +----------------------------------------------------------------------
// | Author: ManHo <humanhe@gmail.com>
// +----------------------------------------------------------------------

namespace Org\Wechat;
class WechatJssdk{
  private $appId;
  private $appSecret;
  private $savefile;
  private $atfile;
  public function __construct($owner='mg'){
    //man 160224
    include('conifg.php');
    $appId              = isset($config[$owner]['appid'])?$config[$owner]['appid']:'';
    $appSecret          = isset($config[$owner]['secret'])?$config[$owner]['secret']:'';
    $this->appId        = $appId;
    $this->appSecret    = $appSecret;
    $this->savefile     = C('ATFILE').'jsapi'.$appId.'.json';
    $this->atfile       = C('ATFILE').'atcode'.$appId.'.json';
  }
  public function getSignPackage($data) {
    if(!self::isWechat()){
      return '';
    }
    $jsapiTicket  = $this->getJsApiTicket();
    $url          = "http".(($_SERVER['HTTPS']=='on')?'s':'')."://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
    $timestamp    = time();
    $nonceStr     = $this->createNonceStr();
    // 这里参数的顺序要按照 key 值 ASCII 码升序排序
    $string       = "jsapi_ticket=$jsapiTicket&noncestr=$nonceStr&timestamp=$timestamp&url=$url";
    $signature    = sha1($string);
    $signPackage  = array(
      "appId"     => $this->appId,
      "nonceStr"  => $nonceStr,
      "timestamp" => $timestamp,
      "url"       => $url,
      "signature" => $signature,
      "rawString" => $string
    );
    return self::genhtml($signPackage,$data);
    //return $signPackage; 
  }
  private function genhtml($signPackage,$data){
    $scanstr  = '';
    if(array_key_exists('scanstr',$data) && is_array($data['scanstr'])){
      $sstr   = '';
      foreach ($data['scanstr'] as $k => $v) {
        $sstr .= $k.'='.$v.'&';
      }
      $scanstr = 'function scan(){wx.scanQRCode({needResult: 1,scanType: ["qrCode","barCode"],success: function (res){window.location.href=res.resultStr+"?'.$sstr.'";}});}';
    }
    return <<<EOF
  <script type="text/javascript" src="http://res.wx.qq.com/open/js/jweixin-1.0.0.js"></script>
  <script type="text/javascript">
  wx.config({debug: false,appId: '{$signPackage['appId']}',timestamp: {$signPackage['timestamp']},nonceStr: '{$signPackage['nonceStr']}',signature: '{$signPackage['signature']}',
      jsApiList: ['checkJsApi','onMenuShareTimeline','onMenuShareAppMessage','onMenuShareQQ','onMenuShareWeibo','scanQRCode']
  });
  wx.ready(function(){
    wx.onMenuShareAppMessage({title:'{$data['title']}',desc:'{$data['desc']}',link:'{$data['link']}',imgUrl:'{$data['imgUrl']}'});
    wx.onMenuShareTimeline({title:'{$data['title']} {$data['desc']}',link:'{$data['link']}',imgUrl:'{$data['imgUrl']}'});
    
  });
  {$scanstr}
</script>
EOF;
  }
  private function createNonceStr($length = 16) {
    $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
    $str = "";
    for ($i = 0; $i < $length; $i++) {
      $str .= substr($chars, mt_rand(0, strlen($chars) - 1), 1);
    }
    return $str;
  }
  private function getJsApiTicket() {
    // jsapi_ticket 应该全局存储与更新，以下代码以写入到文件中做示例
    @$data = json_decode(file_get_contents($this->savefile));
    if (!$data || $data->expire_time < time()) {
        $accessToken  = $this->getAccessToken();
        $url          = "https://api.weixin.qq.com/cgi-bin/ticket/getticket?type=jsapi&access_token=$accessToken";
        $res          = json_decode($this->httpGet($url));
        $ticket       = $res->ticket;
        if ($ticket) {
            $data->expire_time  = time() + 7000;
            $data->jsapi_ticket = $ticket;
            $fp                 = fopen($this->savefile, "w");
            fwrite($fp, json_encode($data));
            fclose($fp);
        }
    } else {
        $ticket = $data->jsapi_ticket;
    }
    return $ticket;
  }
  private function getAccessToken() {
    // access_token 应该全局存储与更新，以下代码以写入到文件中做示例
    @$data = json_decode(file_get_contents($this->atfile));
    if (!$data || $data->expire_time < time()) {
      $url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=$this->appId&secret=$this->appSecret";
      $res = json_decode($this->httpGet($url));
      $access_token = $res->access_token;
      if ($access_token) {
        $data->expire_time = time() + 7000;
        $data->access_token = $access_token;
        $fp = fopen($this->atfile, "w");
        fwrite($fp, json_encode($data));
        fclose($fp);
      }
    } else {
      $access_token = $data->access_token;
    }
    return $access_token;
  }
  private function httpGet($url) {
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_TIMEOUT, 500);
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($curl, CURLOPT_URL, $url);
    $res = curl_exec($curl);
    curl_close($curl);
    return $res;
  }
  private function isWechat(){
    $res = isset($_SERVER['HTTP_USER_AGENT'])?$_SERVER['HTTP_USER_AGENT']:'';
    return strpos($res, 'MicroMessenger');
  }
  //public function getShareUrl($userid,$artid){
  public function getShareUrl(){
    $hname  = $_SERVER["HTTP_HOST"];
    $userid = getUserid();
    $turl   = $_SERVER["REQUEST_URI"];
    $turl   = str_replace('/', '|', $turl);
    //echo $turl;
    //return ($_SERVER['HTTPS']=='on'?'https://':'http://').$hname.U("Share/$userid/").'/'.$artid;
    return ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS']=='on')?'https://':'http://').$hname.U("Share/$userid/").'/'.$turl;
  }
}