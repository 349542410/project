<?php
/*
 * 仅用于提供 会员审核 的邮件报文的编译，不作其他任何用途
 */
namespace Admin\Controller;
use Think\Controller;
class MemberExamineController extends Controller{

	// 4种邮件报文格式
	public $arr = array(
		// 2017-08-18 会员审核通过 中文版
		'examine_success_content_cn' => "<div style='background:#f4fafa; font-family: Segoe UI,Lucida Grande,Helvetica,Arial,Microsoft YaHei,FreeSans,Arimo,Droid Sans,wenquanyi micro hei,Hiragino Sans GB,Hiragino Sans GB W3,FontAwesome,sans-serif;'>
	            <div style='margin:0 auto; width:960px ;height:135px; background:#535c6c; background-position: center;'>
	                <img src='http://res.megao.hk/f/webuser/Member/image/zh-cn/login.png' style='position:relative;top:40px;left:30px;'>
	                <div style='width:1180px; margin:0 auto; padding:20px 50px; color:#fff; font-size:18px; line-height:35px;position:relative;top:-20px;left:150px;'>
	                </div>
	            </div>
	            <div style='margin:0 auto; width:880px;background:#fff; padding:15px 40px; font-size:18px;'>
	                <p style='padding:12px 0'>亲爱的会员 %1\$s，</p>
	                <p style='margin-left:2em; line-height:28px;'>
	                恭喜您，您的美快国际物流注册资料已通过审核。</p>
	                <p style='margin-left:2em; line-height:28px;'>
	                您可以通过美快客服热线或在线客服联系我们，客服热线 020-4008802969<br/>
	                （工作时间为周一到周五 北京时间 09:00-18:00）</p>
	                <p style='padding:12px 0;'>此邮件为系统自动发送，请勿回复。</p>
	            </div>
	            <div style='height:1px; background:#ababab;width:960px;margin:0 auto;'></div>
	            <div style='background:#fff; width:960px; margin:0 auto; text-align:center; color:#919191;'>
	                <p style='text-align:center; line-height:40px; margin:0;'>感谢您选择美快国际物流！</p>
	                <p style='text-align:center; line-height:40px; margin:0;'>接下来的日子，我们将全力协助您，并与您携手一起用技术和创意改变世界！</p>
	            </div>
	            <div style='margin:0 auto; width:960px; background:#535c6c; color:#fff; padding:8px 0;'>   
	            <div style='line-height:160%;margin:20px 0 0 0 ;'>
	                <p style='text-align:center;'>美快国际物流&nbsp;<a style='color:#fff;' href='www.meiquick.com' target='_blank'>www.meiquick.com</a></p>
	                <p style='text-align:center;'><span times='' t='5' style=''>%2\$s</span></p>
	            </div>
	        </div>
	        </div>",

	    // 会员审核不通过 中文版
		'examine_fail_content_cn' => "<div style='background:#f4fafa; font-family: Segoe UI,Lucida Grande,Helvetica,Arial,Microsoft YaHei,FreeSans,Arimo,Droid Sans,wenquanyi micro hei,Hiragino Sans GB,Hiragino Sans GB W3,FontAwesome,sans-serif;'>
	            <div style='margin:0 auto; width:960px ;height:135px; background:#535c6c; background-position: center;'>
	                <img src='http://res.megao.hk/f/webuser/Member/image/zh-cn/login.png' style='position:relative;top:40px;left:30px;'>
	                <div style='width:1180px; margin:0 auto; padding:20px 50px; color:#fff; font-size:18px; line-height:35px;position:relative;top:-20px;left:150px;'>
	                </div>
	            </div>
	            <div style='margin:0 auto; width:880px;background:#fff; padding:15px 40px; font-size:18px;'>
	                <p style='padding:12px 0'>亲爱的会员 %1\$s，</p>
	                <p style='margin-left:2em; line-height:28px'>
	                很抱歉，您的美快国际物流注册资料未通过审核，请您再一次提交资料核实。</p>
	                <p style='margin-left:2em;font-weight:bold;font-size:16px;'>原因： %2\$s 审核不通过，请用您的账号登录官网根据提示重新完善您的资料信息。</p>
	                <p style='margin-left:2em; line-height:28px'>
	                您可以通过美快客服热线或在线客服联系我们，客服热线 020-4008802969<br/>
	                （工作时间为周一到周五 北京时间 09:00-18:00）</p>
	                <p style='padding:12px 0;'>此邮件为系统自动发送，请勿回复。</p>
	            </div>
	            <div style='height:1px; background:#ababab;width:960px;margin:0 auto;'></div>
	            <div style='background:#fff; width:960px; margin:0 auto; text-align:center; color:#919191;'>
	                <p style='text-align:center; line-height:40px; margin:0;'>感谢您选择美快国际物流！</p>
	                <p style='text-align:center; line-height:40px; margin:0;'>接下来的日子，我们将全力协助您，并与您携手一起用技术和创意改变世界！</p>
	            </div>
	            <div style='margin:0 auto; width:960px; background:#535c6c; color:#fff; padding:8px 0;'>   
	            <div style='line-height:160%;margin:20px 0 0 0 ;'>
	                <p style='text-align:center;'>美快国际物流&nbsp;<a style='color:#fff;' href='www.meiquick.com' target='_blank'>www.meiquick.com</a></p>
	                <p style='text-align:center;'><span times='' t='5' style=''>%3\$s</span></p>
	            </div>
	        </div>
	        </div>",

		// 2017-08-18  会员审核通过 英文版
		'examine_success_content_en' => "<div style='background:#f4fafa; font-family: Segoe UI,Lucida Grande,Helvetica,Arial,Microsoft YaHei,FreeSans,Arimo,Droid Sans,wenquanyi micro hei,Hiragino Sans GB,Hiragino Sans GB W3,FontAwesome,sans-serif;'>
	            <div style='margin:0 auto; width:960px ;height:135px; background:#535c6c; background-position: center;'>
	                <img src='http://res.megao.hk/f/webuser/Member/image/en-us/login.png' style='position:relative;top:40px;left:30px;'>
	                <div style='width:1180px; margin:0 auto; padding:20px 50px; color:#fff; font-size:18px; line-height:35px;position:relative;top:-20px;left:150px;'>
	                </div>
	            </div>
	            <div style='margin:0 auto; width:880px;background:#fff; padding:15px 40px; font-size:18px;'>
	                <p style='padding:12px 0'>Dear %1\$s，</p>
	                <p style='margin-left:2em; line-height:28px;'>
	                Congratulations! Your register information for Meiquick International Logistics has passed verification.</p>
	                <p style='margin-left:2em; line-height:28px;'>
	                You can contact us through Meiquick hotline or online customer service, customer service hotline 020-4008802969<br/>
	                (Mon-Fri, Beijing time 09:00-18:00)</p>
	                <p style='padding:12px 0;'>This mail is send by system automatically, please do not reply.</p>
	            </div>
	            <div style='height:1px; background:#ababab;width:960px;margin:0 auto;'></div>
	            <div style='background:#fff; width:960px; margin:0 auto; text-align:center; color:#919191;'>
	                <p style='text-align:center; line-height:40px; margin:0;'>Thanks for choosing Meiquick International Logistics,</p>
	                <p style='text-align:center; line-height:40px; margin:0;'>we will help you and change world with technology and creativity in the following days!</p>
	            </div>
	            <div style='margin:0 auto; width:960px; background:#535c6c; color:#fff; padding:8px 0;'>   
	            <div style='line-height:160%;margin:20px 0 0 0 ;'>
	                <p style='text-align:center;'>Meiquick International Logistics&nbsp;<a style='color:#fff;' href='www.meiquick.com' target='_blank'>www.meiquick.com</a></p>
	                <p style='text-align:center;'><span times='' t='5' style=''>%2\$s</span></p>
	            </div>
	        </div>
	        </div>",

	    // 会员审核不通过 英文版
		'examine_fail_content_en' => "<div style='background:#f4fafa; font-family: Segoe UI,Lucida Grande,Helvetica,Arial,Microsoft YaHei,FreeSans,Arimo,Droid Sans,wenquanyi micro hei,Hiragino Sans GB,Hiragino Sans GB W3,FontAwesome,sans-serif;'>
	            <div style='margin:0 auto; width:960px ;height:135px; background:#535c6c; background-position: center;'>
	                <img src='http://res.megao.hk/f/webuser/Member/image/en-us/login.png' style='position:relative;top:40px;left:30px;'>
	                <div style='width:1180px; margin:0 auto; padding:20px 50px; color:#fff; font-size:18px; line-height:35px;position:relative;top:-20px;left:150px;'>
	                </div>
	            </div>
	            <div style='margin:0 auto; width:880px;background:#fff; padding:15px 40px; font-size:18px;'>
	                <p style='padding:12px 0'>Dear %1\$s，</p>
	                <p style='margin-left:2em; line-height:28px'>
	                I'm sorry, your register information for Meiquick International Logistics has not passed verification, please submit information again for verification.</p>
	                <p style='margin-left:2em;font-weight:bold;font-size:16px;'>Reason: %2\$s verification failure, please login on the official website and complete your information again as required.</p>
	                <p style='margin-left:2em; line-height:28px'>
	                You can contact us through Meiquick hotline or online customer service, customer service hotline 020-4008802969<br/>
	                (Mon-Fri, Beijing time 09:00-18:00)</p>
	                <p style='padding:12px 0;'>This mail is send by system automatically, please do not reply.</p>
	            </div>
	            <div style='height:1px; background:#ababab;width:960px;margin:0 auto;'></div>
	            <div style='background:#fff; width:960px; margin:0 auto; text-align:center; color:#919191;'>
	                <p style='text-align:center; line-height:40px; margin:0;'>Thanks for choosing Meiquick International Logistics,</p>
	                <p style='text-align:center; line-height:40px; margin:0;'>we will help you and change world with technology and creativity in the following days!</p>
	            </div>
	            <div style='margin:0 auto; width:960px; background:#535c6c; color:#fff; padding:8px 0;'>   
	            <div style='line-height:160%;margin:20px 0 0 0 ;'>
	                <p style='text-align:center;'>Meiquick International Logistics&nbsp;<a style='color:#fff;' href='www.meiquick.com' target='_blank'>www.meiquick.com</a></p>
	                <p style='text-align:center;'><span times='' t='5' style=''>%3\$s</span></p>
	            </div>
	        </div>
	        </div>",
		);
	public function index(){

	}
}