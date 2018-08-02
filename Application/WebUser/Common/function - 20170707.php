<?php
	
	/**
	 * 注册时，生成注册验证码和语句
	 * @param  [type] $username [会员名称]
	 * @param  [type] $code     [邮箱验证码]
	 * @return [type]           [description]
	 */
    function create_content($username,$code){
        // $background_url = C('TMPL_PARSE_STRING.__IMG__')."/youxiang_head.jpg";

        $content = 
            '<div style="background:#f4fafa; ">
            <div style="margin:0 auto; width:960px ;height:135px; background:#535c6c; background-position: center;">
                <img src="http://res.megao.hk/f/webuser/Member/image/zh-cn/login.png" style="position:relative;top:40px;left:30px;">
                <div style=" width:1180px; margin:0 auto; padding:20px 50px; color:#fff; font-size:18px; line-height:35px;position:relative;top:-20px;left:150px;">
                </div>
            </div>
            <div style="padding:50px 0;background:#fff; width:960px; margin:0 auto;">
                <p style="padding-left:100px; font-size:24px; color:#000;font-weight: bold;line-height: 35px;">'.L('re_Dear').$username.'</p>
                <p style="padding-left:100px; color:#545454; font-size:24px;line-height:35px;">'.L('re_Your_ve').'</p>
                <p style="padding-left:100px; color:#2b7dbd; font-weight: bold;font-size:30px;line-height:50px;">'.$code.'</p>
                <p style="padding-left:100px; color:#ababab; font-size:20px;">'.L('re_which').'</p>
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
            </div>';
        return $content;
    }

    //找回密码报文
    function get_pwd_back_content($username,$url){
        $content = 
            '<div style="background:#f4fafa; font-family: Segoe UI,Lucida Grande,Helvetica,Arial,Microsoft YaHei,FreeSans,Arimo,Droid Sans,wenquanyi micro hei,Hiragino Sans GB,Hiragino Sans GB W3,FontAwesome,sans-serif;">
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
        </div>';
        return $content;
	}


    /** 
     * 验证码检查 
     */
    function check_verify($code, $id = ""){  
        ob_clean();
        $verify = new \Think\Verify();  
        return $verify->check($code, $id);  
    }  
    function verify_c(){
        $Verify = new \Think\Verify();  
        $Verify->fontSize = 20;  
        $Verify->length   = 4;  
        $Verify->useNoise = false;  
        $Verify->codeSet = '0123456789';  
        $Verify->imageW = 150;  
        $Verify->imageH = 50;  
        //$Verify->expire = 600;  
        $Verify->entry();  

    }
    /** 
     * 打印函数 
     */
    function p($var = '')
    {
        echo '<pre>';
        print_r($var);
        echo '</pre>';
        die;
    }

    /**
     * 身份证号码验证
     * @param  [type] $CID [身份证号码]
     * @return [type]      [description]
     */
    function certificate($CID){
        $reg = "/^(\d{6})(\d{4})(\d{2})(\d{2})(\d{3})([0-9]|X)$/";

        //验证格式
        if(preg_match($reg, $CID) != true){
            return false;
        }

        $date    = substr($CID, 6, 8);  //获取身份证中的年月日
        $nowdate = date('Ymd'); //当前实际的年月日
        
        //如果身份证的年月日不超过当前实际日期
        if(intval($date) <= intval($nowdate)){
            $year  = substr($CID, 6, 4);
            $month = substr($CID, 10, 2);
            $day   = substr($CID, 12, 2);

            //如果身份证的月份大于实际月份,则报错
            if(intval($month) > 12){
                return false;
            }else{
                
                //判断身份证里面的月份是否属于润年
                $mday = $month == 2 ? ($year % 4 ? 28 : ($year % 100 ? 29 : ($year % 400 ? 28 : 29))) : (($month - 1) % 7 % 2 ? 30 : 31);

                //如果身份证的 天数 大于 身份证的年月实际该有的天数,则报错
                if(intval($day) > intval($mday)){
                    return false;
                }else{
                    return true;
                }
            }
            
        }else{
            return false;
        }
        
    }

    /**
     * 根据密码字符串判断密码结构
     * @param (string)$pwd
     * return 返回：$msg
     */
    function get_pwd_strength($pwd){
        if (strlen($pwd)>16 || strlen($pwd)<6)
        {
            return L('f_pwd_r1');
        }
        if(preg_match("/^\d*$/", $pwd))
        {
            return L('f_pwd_r2');//全数字  强度:弱
        }
        if(preg_match("/^[A-Za-z]*$/i", $pwd))
        {
            return L('f_pwd_r3');//全字母  强度:中
        }
        if(!preg_match("/^[A-Za-z\d]*$/i", $pwd))
        {
            return L('f_pwd_r4');//有数字有字母 ";  强度:强
        }
    }

    /**
     * 判断用户名结构
     * @param (string)$str
     * return 返回：$msg
     */
    function get_name_rule($str){
        if (strlen($str)>18 || strlen($str)<6)
        {
            return L('f_name_r1');
        }
        if(!preg_match("/^[a-z0-9_]+$/", $str)){
            return L('f_name_r2');
        }
    }
    /**
     * 验证登录账户的格式
     * @param  [type] $str [description]
     * @return [type]      [description]
     */
    function get_login_way($str){
        if(preg_match("/^[a-zA-Z0-9_-]+@[a-zA-Z0-9_-]+(\.[a-zA-Z0-9_-]+)+$/", $str)){
            return true;//邮箱登录
        }else{
            return false;//账号登录
        }
    }

    /**
     * 验证护照号格式
     * @param (string)$str
     * return 返回：$msg
     */
    function get_passport_rule($str){
        // if(!preg_match("/^1[45][0-9]{7}|G[0-9]{8}|P[0-9]{7}|S[0-9]{7,8}|D[0-9]+$/", $str)){
        //     return "护照号格式不正确";
        // }
    }

    //授权书 中英文互译
    function Certificat_content($str, $info){
        $content = sprintf($str,$info['FirstName'],$info['LastName'],$info['self_address'],$info['self_phone'],date('Y-m-d',$info['reg_time']),$info['certificate_number']);

        return $content;
    }