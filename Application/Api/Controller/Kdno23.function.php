<?php
	/** 生成xml格式 */
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