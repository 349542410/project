<?php
    // xml转成数组
	function xmlToArray($xml){
        //禁止引用外部xml实体
        libxml_disable_entity_loader(true);
        $values = json_decode(json_encode(simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA)), true);
        return $values;
    }

	//对象转数组 方法1
	function object_array($array) {
	    if(is_object($array)) {
	        $array = (array)$array;
	    }
	    if(is_array($array)) {
	        foreach($array as $key=>$value) {
	            $array[$key] = object_array($value);
	        }
	    }
	    return $array;
	}

	/**
	 * 对象 转 数组  方法2
	 * @param object $obj 对象
	 * @return array
	 */
	function object_to_array($obj){
		$obj = (array)$obj;
		foreach ($obj as $k => $v){
			if (gettype($v) == 'resource'){
				return;
			}
			if (gettype($v) == 'object' || gettype($v) == 'array'){
				$obj[$k] = (array)object_to_array($v);
			}
		}

		return $obj;
	}