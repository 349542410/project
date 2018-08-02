<?php

	/** 生成xml格式 中通对接专用 */
	function arrayToXmlForZhongTong($arr){
		$xml = "";
		
		foreach ($arr as $key=>$val){
			if(is_numeric($key)){
				$xml .= "<Cargo>".arrayToXmlForZhongTong($val)."</Cargo>";
			}else{
				if(is_array($val)){
					$xml.="<".$key.">".arrayToXmlForZhongTong($val)."</".$key.">";
				}else{
					$xml.="<".$key.">".$val."</".$key.">";
				}
			}
		}

		return $xml;
	}

	//对象转数组
	function simplest_xml_to_array($xmlstring) {
	    return json_decode(json_encode((array) simplexml_load_string($xmlstring)), true);
	}
