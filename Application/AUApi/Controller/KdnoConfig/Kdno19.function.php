<?php
	/** 生成xml格式 香港E特快对接专用 */
	function arrayToXml($arr){
		$xml = "";
		
		foreach ($arr as $key=>$val){
			if(is_numeric($key)){
				$xml .= "<item>".arrayToXml($val)."</item>";
			}else{
				if(is_array($val)){
					$xml.="<".$key.">".arrayToXml($val)."</".$key.">";
				}else{
					$xml.="<".$key.">".$val."</".$key.">";
				}
			}
		}

		return $xml;
	}

	// Xml 转 数组, 包括根键，忽略空元素和属性  EMS邮政肯定要用这个
	function xml_to_array($xml) {
	    $reg = "/<(\\w+)[^>]*?>([\\x00-\\xFF]*?)<\\/\\1>/";
	    if(preg_match_all($reg, $xml, $matches))
	    {
	        $count = count($matches[0]);
	        $arr = array();
	        for($i = 0; $i < $count; $i++)
	        {
	            $key= $matches[1][$i];
	            $val = xml_to_array( $matches[2][$i] );  // 递归
	            if(array_key_exists($key, $arr))
	            {
	                if(is_array($arr[$key]))
	                {
	                    if(!array_key_exists(0,$arr[$key]))
	                    {
	                        $arr[$key] = array($arr[$key]);
	                    }
	                }else{
	                    $arr[$key] = array($arr[$key]);
	                }
	                $arr[$key][] = $val;
	            }else{
	                $arr[$key] = $val;
	            }
	        }
	        return $arr;
	    }else{
	        return $xml;
	    }
	}
	
    /**
     * XML转数组格式  备用
     * @param string $xml
     * @return array $val
     * @author mosishu
     */
    function xmlToArray($xml, $eIsArray=false){
        //禁止引用外部xml实体
        libxml_disable_entity_loader(true);
        $xmlstring = (array)simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA);
        $val = json_decode(json_encode($xmlstring),true);
        return $val;
    }

    //保留一位小数，小数点第二位直接去掉
    function num_to_change($n){
        $num = floatval($n) * 10;
        $arr = explode('.',$num);

        if($arr[0] > 0){
            return sprintf("%.1f", floatval($arr[0])/10);
        }else{
            return '0.1';
        }
    }

    function province_code($str){
    	$arr = array('1'=>'北京市','2'=>'天津市','3'=>'河北省','4'=>'山西省','5'=>'内蒙古自治区','6'=>'辽宁省','7'=>'吉林省','8'=>'黑龙江省','9'=>'上海市','10'=>'江苏省','11'=>'浙江省','12'=>'安徽省','13'=>'福建省','14'=>'江西省','15'=>'山东省','16'=>'河南省','17'=>'湖北省','18'=>'湖南省','19'=>'广东省','20'=>'广西壮族自治区','21'=>'海南省','22'=>'重庆市','23'=>'四川省','24'=>'贵州省','25'=>'云南省','26'=>'西藏自治区','27'=>'陕西省','28'=>'甘肃省','29'=>'青海省','30'=>'宁夏回族自治区','31'=>'新疆维吾尔自治区','32'=>'香港特别行政区','33'=>'澳门特别行政区',
    	);
    	// array_flip($arr);
    	foreach($arr as $key=>$item){
    		if(preg_match("/".$str."/i", $item)){
    			return $key;break;
    		}else{
    			continue;
    		}
    	}

    	return '无法匹配省编号';
    }

	//屏蔽电话号码中间的四位数字
	function hidtel($phone){
	    $IsWhat = preg_match("/^1[0-9]{10}$/",$phone); //手机电话
	    if($IsWhat != 1)
	    {
	        $phone = trim($phone);
	        $phone = explode("-",$phone);
	        return $phone[0]."***".$phone[2];
	    }
	    else
	    {
	        return  preg_replace('/(1[358]{1}[0-9])[0-9]{4}([0-9]{4})/i','$1****$2',$phone);
	    }
	}

	/**
	 * 只保留字符串首尾字符，隐藏中间用*代替（两个字符时只显示第一个）
	 * @param string $user_name 姓名
	 * @return string 格式化后的姓名
	 */
	function substr_cut($user_name){
		if (preg_match("/^([\x{4e00}-\x{9fa5}]+)$/u", $user_name)) {
    	
			$strlen   = mb_strlen($user_name, 'utf-8');
			$firstStr = mb_substr($user_name, 0, 1, 'utf-8');
			$lastStr  = '';//mb_substr($user_name, -1, 1, 'utf-8');
		    return $strlen == 2 ? $firstStr . str_repeat('*', mb_strlen($user_name, 'utf-8') - 1) : $firstStr . str_repeat("**", $strlen - 2) . $lastStr;
    	}else{
    		$user_name = trim($user_name);
    		$site = strpos($user_name,' ');
    		$firstStr = explode(' ', $user_name);
    		return $firstStr[0] . str_repeat('*', strlen($user_name) - $site);
    	}
	}

	// 没用
	function hidaddress($address, $province, $city, $town){
        /* 去除详细地址中的省市区和空格 */
        $address = str_replace($town,'',$address);
        $address = str_replace($city,'',$address);
        $address = str_replace($province,'',$address);
        $address = trim(str_replace(' ','',$address));
        return $address;
	}