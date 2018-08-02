<?php
namespace Web\Controller;
use Think\Controller;
use Think\Db;

class IndexController extends BaseController {

    public function _initialize() {
        //parent::_initialize();
    }
    
    public function index(){

        $serviceLink = M('admin_config')->field('value')->where(array('name'=>'app.customer_service'))->find();
        $this->assign('serviceLink',!empty($serviceLink)?$serviceLink['value']:C('CUSTOMER_SERVICE'));
        $this->display();
    }

    public function test()
    {
        $code='
            <div style="background:#f4fafa; font-family: Segoe UI,Lucida Grande,Helvetica,Arial,Microsoft YaHei,FreeSans,Arimo,Droid Sans,wenquanyi micro hei,Hiragino Sans GB,Hiragino Sans GB W3,FontAwesome,sans-serif;">
            <div style="margin:0 auto; width:960px ;height:135px; background:#535c6c; background-position: center;">
                <img src="http://res.megao.hk/f/webuser/Member/image/zh-cn/login.png 

" style="position:relative;top:40px;left:30px;">
                <div style=" width:1180px; margin:0 auto; padding:20px 50px; color:#fff; font-size:18px; line-height:35px;position:relative;top:-20px;left:150px;">
                </div>
            </div>
           
			<div style="margin:0 auto; width:880px;background:#fff; padding:15px 40px; font-size:18px;">
				<p style="padding:12px 0">亲爱的会员 '.$username.'，</p>
				<p style="margin-left:2em; line-height:28px">恭喜您，您的资料已审核通过！<br/>恭喜您，您的资料已审核通过！<br/>恭喜您，您的资料已审核通过！</p>
				
				<p style=" padding:12px 0;">此邮件为系统自动发送，请勿回复。</p>
			</div>
			

            <div style="height:1px; background:#ababab;width:960px;margin:0 auto;"></div>
            <div style="background:#fff; width:960px; margin:0 auto; text-align:center; color:#919191;">
                <p style="text-align:center; line-height:40px; margin:0;">'.L('re_Thank_y').'</p>
                <p style="text-align:center; line-height:40px; margin:0;">'.L('re_we_are').'</p>
            </div>
            <div style=";margin:0 auto; width:960px; background:#535c6c; color:#fff; padding:8px 0;">   
            <div style="line-height:160%;margin:20px 0 0 0 ;">
                <p style="text-align:center;">'.L('re_Meikuai').'&nbsp;<a style=" color:#fff;" href="www.meiquick.com" target="_blank">www.meiquick.com</a></p>
                <p style="text-align:center;"><span times="" t="5" style="">'.date('Y-m-d',time()).'</span></p>
            </div>
        </div>
        </div> 
        ';
        echo $code;
    }
}