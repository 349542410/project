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