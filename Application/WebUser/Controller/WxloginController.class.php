<?php
/**
 * 自助打印终端---扫码登录(扫描微信二维码)
 * 功能：
 * 创建时间：2017-08-16
 * 创建人：jie
 */
namespace WebUser\Controller;
use Think\Controller;
use Org\Wechat\Wechat;
use Org\Wechat\WechatAuth;

class WxloginController extends Controller{

    public function login_bak(){
        $no  = trim(I('get.no'));
        $lng = (I('get.lng')) ? trim(I('get.lng')) : 'zh-cn';

        $wx = '';
        $this->redirect('Publiclogin/login',array('no'=>$no,'wx'=>$wx,'lng'=>$lng));
    }

    public function login(){
        $no  = trim(I('get.no'));
        $lng = (I('get.lng')) ? trim(I('get.lng')) : 'zh-cn';
        $wx = '';
        $this->redirect('Publiclogin/login',array('no'=>$no,'wx'=>$wx,'lng'=>$lng));
        /*
		$no  = trim(I('get.no'));
		$lng = (I('get.lng')) ? trim(I('get.lng')) : 'zh-cn';
		if(isinwechat()){
		    $wechat = new WechatAuth('mk');
	        $hname  = $_SERVER["HTTP_HOST"];
	        $state  = 'MGMALL';
	        $rurl   = ($_SERVER['HTTPS']=='on'?'https://':'http://').$hname.U('Wxlogin/jump',array('no'=>$no,'lng'=>$lng));
	        //die($rurl);
	        $url    = $wechat->getRequestCodeURL($rurl,$state,'snsapi_base');

    	}else{
    		$url 	= U('Publiclogin/login',array('no'=>$no,'lng'=>$lng));
    	}
    	//echo $url;
    	header("Location:$url");
		//$this->redirect($rurl);
        */
    }

    public function jump()
    {
        /*
        $code   = I('get.code');
        $state  = I('get.state');
        if($state=='MGMALL'){
            $wechat = new WechatAuth('mk');
            $info   = $wechat->getAccessToken('code',$code);
            if(is_array($info)){
                $wxid   = $info['openid'];
                $this->redirect('Publiclogin/login',array('no'=>I('no'),'wx'=>$wxid,'lng'=>I('lng')));
            }
        }
        */
    }
}