<?php
	// curl post发送
    function sendXML($url, $xmlData){
		$data['data'] = $xmlData;
		$ch = curl_init();
		// $header[] = "Content-type: text/xml";//定义content-type为xml
		curl_setopt($ch, CURLOPT_URL, $url); //定义表单提交地址
		curl_setopt($ch, CURLOPT_POST, 1);   //定义提交类型 1：POST ；0：GET
		curl_setopt($ch, CURLOPT_HEADER, 0); //定义是否显示状态头 1：显示 ； 0：不显示
		// curl_setopt($ch, CURLOPT_TIMEOUT,10); //超时6秒
		// curl_setopt($ch, CURLOPT_HTTPHEADER, $header);//定义请求类型
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);//定义是否直接输出返回流，即把返回的请求结果赋予给$info
		curl_setopt($ch, CURLOPT_POSTFIELDS, $xmlData); //定义提交的数据，这里是XML文件
    		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // 跳过证书检查
		$info = curl_exec($ch);//执行
		curl_close($ch);//关闭
		return $info;
    }



	// 验证是一维数组或二维数组
	function ck_array($arr){
		$s = 1;//默认为 一维数组
		foreach($arr as $val){
			if(is_array($val)){
				$s = 2;
			}
		}
		return $s;

		/*if (count($data) == count($data, 1))
		{
		    echo '是一维数组';
		}
		else
		{
		    echo '不是一维数组';
		}*/
	}

	// Xml 转 数组, 包括根键，忽略空元素和属性
	function xml_to_array( $xml ) {
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

	/** 生成xml格式 广州EMS下单专用 */
	function arrayToXml($arr){
		$xml = "";
		foreach ($arr as $key=>$val){
			if(is_numeric($key)){
				$xml .= "<child>".arrayToXml($val)."</child>";
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

	// 生产国家二字编码  中文 转成 二字字母  例如 美国=》'US'
	function change_code($str){
		return 'US';
	}