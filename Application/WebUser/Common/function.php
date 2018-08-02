<?php

    // 斜杠号转换
    function K($str){
        return str_replace('/', '\\', str_replace('///', '\\', $str));
    }

    function J($str){
        return str_replace('./', '/', str_replace('///', '/', $str));
    }
    
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
        // ob_clean();
        if(empty($code)){
            return false;
        }
        $verify = new \Think\Verify();
        return $verify->check($code, $id);
    }
    function verify_c(){
        ob_end_clean();//此项必须，解决多语言的时候验证码显示不了
        $Verify = new \Think\Verify();
        $Verify->fontSize = 22;
        $Verify->length   = 4;
        $Verify->useNoise = false;
        $Verify->codeSet = '0123456789';
        $Verify->imageW = 0;
        $Verify->imageH = 0;
        $Verify->fontttf = '5.ttf';
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
     * 身份证号码验证  暂停使用
     * @param  [type] $CID [身份证号码]
     * @return [type]      [description]
     */
    function certificate($CID){

        $reg = "/^(\d{6})(\d{4})(\d{2})(\d{2})(\d{3})([0-9]|[X|x])$/";

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

    //验证身份证号是否正确的函数
    function validation_filter_id_card($id_card){
        if(strlen($id_card)==18){
            return idcard_checksum18($id_card);
        // }else if((strlen($id_card)==15)){
        //     $id_card=idcard_15to18($id_card);
        //     return idcard_checksum18($id_card);
        }else{
            return false;
        }
    }
    // 计算身份证校验码，根据国家标准GB 11643-1999
    function idcard_verify_number($idcard_base){
        if(strlen($idcard_base)!=17){
            return false;
        }
        //加权因子
        $factor=array(7,9,10,5,8,4,2,1,6,3,7,9,10,5,8,4,2);
        //校验码对应值
        $verify_number_list=array('1','0','X','9','8','7','6','5','4','3','2');
        $checksum=0;
        for($i=0;$i<strlen($idcard_base);$i++){
            $checksum += substr($idcard_base,$i,1) * $factor[$i];
        }
        $mod=$checksum % 11;
        $verify_number=$verify_number_list[$mod];
        return $verify_number;
    }
    // 将15位身份证升级到18位
    function idcard_15to18($idcard){
        if(strlen($idcard)!=15){
            return false;
        }else{
            // 如果身份证顺序码是996 997 998 999，这些是为百岁以上老人的特殊编码
            if(array_search(substr($idcard,12,3),array('996','997','998','999')) !== false){
                $idcard=substr($idcard,0,6).'18'.substr($idcard,6,9);
            }else{
                $idcard=substr($idcard,0,6).'19'.substr($idcard,6,9);
            }
        }
        $idcard=$idcard.idcard_verify_number($idcard);
        return $idcard;
    }
    // 18位身份证校验码有效性检查
    function idcard_checksum18($idcard){
        if(strlen($idcard)!=18){
            return false;
        }
        $idcard_base=substr($idcard,0,17);
        if(idcard_verify_number($idcard_base)!=strtoupper(substr($idcard,17,1))){
            return false;
        }else{
            return true;
        }
    }
    //验证身份证号是否正确的函数 end

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
    function get_name_rules($str){
        if (strlen($str)>20 || strlen($str)<6)
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
        if(!preg_match("/^1[45][0-9]{7}|G[0-9]{8}|P[0-9]{7}|S[0-9]{7,8}|D[0-9]+$/", $str)){
            return "护照号格式不正确";
        }
    }

    //授权书 中英文互译
    function Certificat_content($str, $info){
        $content = sprintf($str,$info['FirstName'],$info['LastName'],$info['self_address'],$info['self_phone'],date('Y-m-d',$info['reg_time']),$info['certificate_number']);

        return $content;
    }
function Certificat_contents($str, $info){
    $content = sprintf($str,$info['company_representative'],'',$info['company_address'],$info['self_phone'],date('Y-m-d',$info['reg_time']),$info['business_license']);

    return $content;
}

//====================  自助打印系统所需 ===================
    //验证密匙key
    function ckey($str,$uname,$ucode){
        $_md5 = md5(base64_encode($uname.$ucode.C('MkWl2Key')));
        return $str == $_md5;
    }

    // 生成token
    function set_token(){
        $str = md5(uniqid(md5(microtime(true)),true));  //生成一个不会重复的字符串
        $str = sha1($str);  //加密
        return $str;
    }

    /**
     * 获取自定义的header数据
     */
    function get_all_headers(){

        // 忽略获取的header数据
        $ignore = array('host','accept','content-length','content-type');

        $headers = array();

        foreach($_SERVER as $key=>$value){
            if(substr($key, 0, 5)==='HTTP_'){
                $key = substr($key, 5);
                $key = str_replace('_', ' ', $key);
                $key = str_replace(' ', '-', $key);
                $key = strtolower($key);

                if(!in_array($key, $ignore)){
                    $headers[$key] = $value;
                }
            }
        }

        return $headers;
    }

    /**
     * 字符串加密/解密
     * @param  [type]  $string    [字符串，明文或密文]
     * @param  string  $operation [加密/解密]
     * @param  string  $key       [密匙]
     * @param  integer $expiry    [密文有效期]
     * @return [type]             [description]
     */
    function authcode($string, $operation = 'DECODE', $key = '', $expiry = 0) {
        // 动态密匙长度，相同的明文会生成不同密文就是依靠动态密匙
        $ckey_length = 4;
        // 密匙
        $key = md5($key ? $key : 'http://www.meiquick.com');

        // 密匙a会参与加解密
        $keya = md5(substr($key, 0, 16));
        // 密匙b会用来做数据完整性验证
        $keyb = md5(substr($key, 16, 16));
        // 密匙c用于变化生成的密文
        $keyc = $ckey_length ? ($operation == 'DECODE' ? substr($string, 0, $ckey_length) : substr(md5(microtime()), -$ckey_length)) : '';
        // 参与运算的密匙
        $cryptkey = $keya.md5($keya.$keyc);
        $key_length = strlen($cryptkey);
        // 明文，前10位用来保存时间戳，解密时验证数据有效性，10到26位用来保存$keyb(密匙b)，
        //解密时会通过这个密匙验证数据完整性
        // 如果是解码的话，会从第$ckey_length位开始，因为密文前$ckey_length位保存 动态密匙，以保证解密正确
        $string = $operation == 'DECODE' ? base64_decode(substr($string, $ckey_length)) : sprintf('%010d', $expiry ? $expiry + time() : 0).substr(md5($string.$keyb), 0, 16).$string;
        $string_length = strlen($string);
        $result = '';
        $box = range(0, 255);
        $rndkey = array();
        // 产生密匙簿
        for($i = 0; $i <= 255; $i++) {
            $rndkey[$i] = ord($cryptkey[$i % $key_length]);
        }
        // 用固定的算法，打乱密匙簿，增加随机性，好像很复杂，实际上对并不会增加密文的强度
        for($j = $i = 0; $i < 256; $i++) {
            $j = ($j + $box[$i] + $rndkey[$i]) % 256;
            $tmp = $box[$i];
            $box[$i] = $box[$j];
            $box[$j] = $tmp;
        }
        // 核心加解密部分
        for($a = $j = $i = 0; $i < $string_length; $i++) {
            $a = ($a + 1) % 256;
            $j = ($j + $box[$a]) % 256;
            $tmp = $box[$a];
            $box[$a] = $box[$j];
            $box[$j] = $tmp;
            // 从密匙簿得出密匙进行异或，再转成字符
            $result .= chr(ord($string[$i]) ^ ($box[($box[$a] + $box[$j]) % 256]));
        }
        if($operation == 'DECODE') {
            // 验证数据有效性，请看未加密明文的格式
            if((substr($result, 0, 10) == 0 || substr($result, 0, 10) - time() > 0) && substr($result, 10, 16) == substr(md5(substr($result, 26).$keyb), 0, 16)) {
                return substr($result, 26);
            } else {
                return '';
            }
        } else {
            // 把动态密匙保存在密文里，这也是为什么同样的明文，生产不同密文后能解密的原因
            // 因为加密后的密文可能是一些特殊字符，复制过程可能会丢失，所以用base64编码
            return $keyc.str_replace('=', '', base64_encode($result));
        }
    }
    function isinwechat(){
        //暂不支持windowsPhone
        $user_agent = isset($_SERVER['HTTP_USER_AGENT'])?$_SERVER['HTTP_USER_AGENT']:'';
        //echo $user_agent;
        return strpos($user_agent, 'MicroMessenger');
    }

    /**
     *  将普通数据转成树形结构
     *  @param array   $list   传进来的数组
     *  @param num     $pid    上一级的ID（数据表需要有pid字段）
     *  @param num     $level  它的等级（数据表需要有level字段）
     *  @return array
     */
    function getTree($list,$pid=0,$level=0)
    {
        // dump($list);die;
        static $tree = array();
        //如果$list为null，说明想清空数据
        if(is_null($list))
        {
            $tree = array();
            return $tree;
        }
        foreach($list as $row){
            // dump($row);die;
            if($row['fid']==$pid){
                $row['level'] = $level;
                $tree[] = $row;
                getTree($list,$row['id'],$level + 1);
            }
        }
        return $tree;
    }


    function getErrorInfo($errInfo){
        $err_type = $errInfo[0];
        $err_value = $errInfo[1];
        if($err_type == 0){
            // 0类型错误
            return L($err_value);
        }else if($err_type == 1){
            // 1类型错误
            return str_replace($err_value[0], L($err_value[1]), L($err_value[2]));
        }else if($err_type == 2){
            // 2类型错误
            return '[' . L($err_value[0]) . '] : ' . L($err_value[1]);
        }else{
            // 3类型错误
            $err = '';
            foreach($err_value as $k=>$v){
                $err .= L($v);
            }
            return $err;
        }
    }













    /* API */

	/**
	 * 生成url附加到短信上
	 * @param  [type] $mkno   [美快单号]
	 * @param  string $letter [首字母]
	 * @return [type]         [description]
	 */
	function curl($mkno,$letter=''){
		//Man161019
		$astr 	= array('','a','b','c','d','e','f','g','h','i','j','k','l','m','n');
		$sstr 	= intval($mkno[4]);
		$bstr 	= $astr[$sstr];
		if(strlen(trim($letter))>0 && $letter[0]=='z'){
			$bstr = 'z'.$bstr;
		}
		
		$number = str_replace(array("MK88".$sstr,"US") ,'' ,$mkno);	//把符合数组中的数据替换为空

		$number = intval($number);	//转换整数类型

		$cback['url'] = C("MESSAGE.CREATE_URL").$bstr.$number;

		return $cback;
	}

	/**
	 * curl函数发送数据到ERP
	 * @param  [type] $url       [description]
	 * @param  [type] $post_data [description]
	 * @return [type]            [description]
	 */
	function posturl($url,$post_data){
		//通过curl函数发送
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_URL,$url);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);

		//当CURLOPT_RETURNTRANSFER设置为1时，如果成功只将结果返回，不自动输出返回的内容。如果失败返回FALSE；
		//若不使用这个选项：如果成功只返回TRUE，自动输出返回的内容。如果失败返回FALSE

		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);   //注意 hua 20180526

		$result = curl_exec($ch);
		curl_close($ch);
		return $result;
	}


	//===== 随机码 会员id+linux时间戳
	function randomCode($user_id){
		
		$uucode = $user_id.date('yHmidsz');

		return $uucode;
	}

	/**
	 * 会员审核成功 生成邮件发送语句    中英文互译
	 * @param  [type] $username [会员名称]
	 * @param  [type] $code     [邮箱验证码]
	 * @return [type]           [description]
	 */
	function create_success_content($str, $username){
		$time = date('Y-m-d');
        return sprintf($str, $username, $time);
	}

	/**
	 * 会员审核失败 生成邮件发送语句   中英文互译
	 * @param  [type] $username [会员名称]
	 * @param  [type] $code     [邮箱验证码]
	 * @return [type]           [description]
	 */
	function create_fail_content($str, $username,$msg){
		$time = date('Y-m-d');
        return sprintf($str, $username, $msg, $time);
	}

	//验证香港、大陆、澳门手机号码正确性
	function checkPhoneNum($phone){
		// $res = "/^[1][3-8]\d{9}$|^([2|3|5|6|9])\d{7}$|^[6]([8|6])\d{5}$/";
		// $res = "/^[1][3-8]\d{9}$|^\d{6,8}$/";
		$res = "/^\d{11}$|^\d{6,8}$/";
		return preg_match($res,$phone);
	}



	/**
	 * 邮政编码的校验
	 * @param  [type] $code [description]
	 * @return [type]       [description]
	 */
	function checkZipcode($code){
		//去掉多余的分隔符
		$code = preg_replace("/[\. -]/", "", $code);
		//包含一个6位的邮政编码 包含0开头的
		if(preg_match("/^[0-9]{6}$/", $code)){
			return true;
		}else{
			return false;
		}
	}

	/**
	 * [build_order_no 生成唯一订单号]
	 * @return [type] [description]
	 * Man 180328 增加$prefix前缀，后缀为$user_id
	 */        
	function StrOrderOne($user_id, $no='order_no',$prefix='Q'){
	    // $mytime=mktime(0, 0, 0, date('m'), date('d'), date('Y')-1);//获取时间戳
		// $startdate=date("Y-m-d H:i:s", strtotime("-1 year")); //获取当前时间的一年前的时间，格式为2016-05-30 13:26:13
	    $startdate="2017-01-01 00:00:00";
	    // 当前时间减去一年前得到的描述
	    $sn = $prefix . floor((time()-strtotime($startdate))).substr(explode ( ".", explode ( " ", microtime () )[0] )[1],3,2).$user_id;

	    $check = M('TranUlist')->where(array($no=>$sn))->find();
	    if($check){
	    	return StrOrderOne($user_id,$no,$prefix);
	    }else{
	    	return $sn;
	    }
	}

	//生成支付交易号/流水号 自助打印系统的订单支付用这个
	function build_sn(){
	    return date('Ymd').substr(implode(NULL, array_map('ord', str_split(substr(uniqid(), 7, 13), 1))), 0, 8);
	}

    /**
     * [get_cat_list 根据传入的类别ID，查出此类别ID所属的所有下级ID。若此ID是顶级类别ID，
     * 则返回的结果($str)里面不包含此ID；若此ID是非顶级类别ID，则返回的结果里面是包含此ID]
     * @param  [type] $id [传入的类别的ID]
     * @return [type]     [description]
     */
    function get_cat_list($id){
		//检查是否在顶级类别下创建货品
		$dog = M('CategoryList')->where(array('id'=>$id))->find();

		$ids = $dog['id'];//类别ID
		$pid = $dog['fid'];//该类别的上级类别ID，判断是否为顶级类别
		// dump($pid);
		// die;
		$str = '';//返回的处理结果
		//顶级类别
		while(strlen($ids) > 0){
			// 如果是顶级类别(fid=0)，则此次的ID不保存到$str里面
			if($pid != '0') $str .= $ids.',';
			// 查询条件，  查询上级类别ID是属于$ids里面的 类别
			$where['fid'] = array('in',$ids);
			// 按条件查询 符合的 类别ID集
			$ban = M('CategoryList')->field('id')->where($where)->select();
			// 二维数组转一维数组
			$ids = array_column($ban, 'id');
			if(is_array($ids)) $ids = implode($ids,',');//如果$ids是数组，则数组转字符串
			$pid++;//改变此值，以区分下一次的传入是非顶级类别
			// dump($ids);die;
		}
		$str = rtrim($str, ',');//去除字符串右边的逗号

		return $str;
    }

    /**
     * 身份证证号码加密
     * @param  [type] $idno [所需身份证号码]
     * @param  [type] $hide [默认值，所需隐藏字符]
     * @return [type]       [description]
     */
    function idcard_format($idno, $hide=[3,9,10]){

        $arr = [];
        for($i=0; $i<strlen($idno); $i++){
            $arr[$i] = substr($idno, $i, 1);
        }

        $hide[] = count($arr)-3;
        $hide[] = count($arr)-2;
        $hide[] = count($arr)-1;
        $hide[] = count($arr)-0;


        foreach($arr as $k=>$v){
            if(in_array($k+1, $hide)){
                $arr[$k] = '*';
            }
        }

        return implode($arr);

    }