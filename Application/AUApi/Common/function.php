<?php
//========== 自助、揽收 ================
    /**
     * 标识码生成方法
     * @return [type] [description]
     */
    function create_guid(){
        if (function_exists('com_create_guid')){
            return com_create_guid();
        }else{
            mt_srand((double)microtime()*10000);//optional for php 4.2.0 and up.
            $charid = strtoupper(md5(uniqid(rand(), true)));
            $hyphen = chr(45);// "-"
            $uuid = //chr(123)// "{"
                    substr($charid, 0, 8).$hyphen
                    .substr($charid, 8, 4).$hyphen
                    .substr($charid,12, 4).$hyphen
                    .substr($charid,16, 4).$hyphen
                    .substr($charid,20,12);
                    //.chr(125);// "}"
            return $uuid;
        }
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
	 * [build_order_no 生成唯一订单号]
	 * @return [type] [description]
	 */        
	function StrOrderOne($user_id, $no='order_no'){
	    // $mytime=mktime(0, 0, 0, date('m'), date('d'), date('Y')-1);//获取时间戳
		// $startdate=date("Y-m-d H:i:s", strtotime("-1 year")); //获取当前时间的一年前的时间，格式为2016-05-30 13:26:13
	    $startdate="2017-01-01 00:00:00";
	    // 当前时间减去一年前得到的描述
	    $sn = floor((time()-strtotime($startdate))).substr(explode ( ".", explode ( " ", microtime () )[0] )[1],3,2).$user_id;

	    $check = M('TranUlist')->where(array($no=>$sn))->find();
	    if($check){
	    	return StrOrderOne($user_id);
	    }else{
	    	return $sn;
	    }
	}

	//生成支付交易号/流水号 自助打印系统的订单支付用这个
	function build_sn(){
	    return date('Ymd').substr(implode(NULL, array_map('ord', str_split(substr(uniqid(), 7, 13), 1))), 0, 8);
	}
//========== 自助、揽收 end ================