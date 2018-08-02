<?php
namespace WebUser\Controller;
use Think\Controller;
class TestController extends Controller{

    public function test2(){
        $n = 0.53*6.8;
        $num = floatval($n) * 1000;
        $str = substr($num,(strlen($num)-1),1);
        // echo $str;
        if($str > 0){
            $num = floatval($num) - floatval($str);
            $num = floatval($num) + 10;
            $num = sprintf("%.2f", floatval($num)/1000);
            echo $num;
        }else{
            echo sprintf("%.2f", floatval($n));
        }
    }
    public function test(){
        echo L('lng');
        // echo substr(sprintf("%.3f", $num), 0, -1);echo '<br>';
        // dump(L(''));die;
        // echo json_encode(array('status'=>'1', 'msg'=>'保存成功', 'sid'=>1, 'mkno'=>MK883072128US, 'TranKd'=>12));die;
        // echo date('Ymd').substr(implode(NULL, array_map('ord', str_split(substr(uniqid(), 7, 13), 1))), 0, 8);die;
        // $no = 'MK883010354US';
        // $rule = "/^(MK)\w{0,}$/";
        // if(!preg_match($rule,$no)){
        //     echo '2';
        // }else{
        //     echo '1';
        // }
    }

	public function index(){
		$c = '<div style="background:#f4fafa; font-family: Segoe UI,Lucida Grande,Helvetica,Arial,Microsoft YaHei,FreeSans,Arimo,Droid Sans,wenquanyi micro hei,Hiragino Sans GB,Hiragino Sans GB W3,FontAwesome,sans-serif;">
            <div style="margin:0 auto; width:960px ;height:135px; background:#535c6c; background-position: center;">
                <img src="http://res.megao.hk/f/webuser/Member/image/zh-cn/login.png 

" style="position:relative;top:40px;left:30px;">
                <div style=" width:1180px; margin:0 auto; padding:20px 50px; color:#fff; font-size:18px; line-height:35px;position:relative;top:-20px;left:150px;">
                </div>
            </div>
            <div style="padding:50px 0;background:#fff; width:960px; margin:0 auto;">
                <p style="padding:0 30px 0 50px; font-size:18px; color:#000;line-height: 35px;">'.L("re_Dear").$username.'</p>
                <p style="padding:0 50px 0 100px; color:#000; font-size:18px;line-height:35px;">'.L("re_Your_pwd").'</p>
                <p style="padding:0 30px 0 100px; color:#000; font-size:18px;line-height:35px;">'.$url.'</p>
                <p style="padding:0 50px 0 100px; color:#919191; font-size:18px;line-height:35px;">'.L("re_Your_c1").'</p>
                <p style="padding:0 30px 0 50px; color:#000; font-size:18px;line-height:35px;">'.L("re_Your_c2").'</p>
                <br>
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
		echo $c;
	}

    public function demo(){
        $c = '<div style="background:#f4fafa; font-family: Segoe UI,Lucida Grande,Helvetica,Arial,Microsoft YaHei,FreeSans,Arimo,Droid Sans,wenquanyi micro hei,Hiragino Sans GB,Hiragino Sans GB W3,FontAwesome,sans-serif;">
            <div style="margin:0 auto; width:960px ;height:135px; background:#535c6c; background-position: center;">
                <img src="http://res.megao.hk/f/webuser/Member/image/zh-cn/login.png" style="position:relative;top:40px;left:30px;">
                <div style=" width:1180px; margin:0 auto; padding:20px 50px; color:#fff; font-size:18px; line-height:35px;position:relative;top:-20px;left:150px;">
                </div>
            </div>
            <div style="margin:0 auto; width:880px;background:#fff; padding:15px 40px; font-size:18px;">
                <p style="padding:12px 0">亲爱的会员 '.$username.'，</p>
                <p style="margin-left:2em; line-height:28px">
                很抱歉，您的美快国际物流注册资料未通过审核，请您再一次提交资料核实。</p>
                <p style="margin-left:2em;font-weight:bold;font-size:16px;">原因：'.$msg.'审核不通过，请用您的账号登录官网根据提示重新完善您的资料信息。</p>
                <p style="margin-left:2em; line-height:28px">
                您可以通过美快客服热线或在线客服联系我们，客服热线 020-29828309<br/>
                （工作时间为周一到周五 北京时间 09:00-18:00）</p>
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
        </div>';
        echo $c;
    }
}