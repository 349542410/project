<?php

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

		//curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);   //注意 hua 20180526

		$result = curl_exec($ch);
		curl_close($ch);
		return $result;
	}


	//===== 随机码 会员id+linux时间戳
	function randomCode($user_id){
		
		$uucode = rand(200, 888) . $user_id . date('yHmidsz') . rand(100, 999);
		$uucode = str_pad($uucode, 32, '0');
		$res = M('TranUlist')->where(array('random_code'=>$uucode))->getfield('id');
		return empty($res) ? $uucode : randomCode($user_id);

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
	 * 身份证号码验证
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
	 * 计量单位代码表 2018-06-27
	 * @param  [type]  $str [description]
	 * @param  boolean $on  [是否直接返回 计量单位代码数组]
	 * @return [type]       [description]
	 */
	function unit_code($str, $on=false){

		$arr = array(
			'台' => '001','座' => '002','辆' => '003','艘' => '004','架' => '005','套' => '006','个' => '007','只' => '008','头' => '009','张' => '010','件' => '011','支' => '012','枝' => '013','根' => '014','条' => '015','把' => '016','块' => '017','卷' => '018','副' => '019','片' => '020','组' => '021','份' => '022','幅' => '023','双' => '025','对' => '026','棵' => '027','株' => '028','井' => '029','米' => '030','盘' => '031','平方米' => '032','立方米' => '033','筒' => '034','千克' => '035','克' => '036','盆' => '037','具' => '039','刀' => '045','疋' => '046','公担' => '047','扇' => '048','百枝' => '049','千只' => '050','千块' => '051','千盒' => '052','千枝' => '053','千个' => '054','亿支' => '055','亿个' => '056','万套' => '057','千张' => '058','万张' => '059','千伏安' => '060','千瓦' => '061','千瓦时' => '062','千升' => '063','英尺' => '067','吨' => '070','长吨' => '071','短吨' => '072','司马担' => '073','司马斤' => '074','斤' => '075','磅' => '076','担' => '077','英担' => '078','短担' => '079','两' => '080','市担' => '081','盎司' => '083','克拉' => '084','市尺' => '085','码' => '086','英寸' => '088','寸' => '089','升' => '095','毫升' => '096','英加仑' => '097','美加仑' => '098','立方英尺' => '099','立方尺' => '101','平方码' => '110','平方英尺' => '111','平方尺' => '112','英制马力' => '115','公制马力' => '116','令' => '118','箱' => '120','批' => '121','罐' => '122','桶' => '123','扎' => '124','包' => '125','箩' => '126','打' => '127','筐' => '128','罗' => '129','匹' => '130','册' => '131','本' => '132','发' => '133','枚' => '134','捆' => '135','袋' => '136','粒' => '139','盒' => '140','合' => '141','瓶' => '142','千支' => '143','万双' => '144','万粒' => '145','千米' => '146','千米' => '147','千英尺' => '148','舱' => '149','部' => '163',
		);
		
		if($on == true){
			return $arr;
		}

		$str = trim($str);
		
		return (isset($arr[$str])) ? $arr[$str] : '007';
	}