<?php
	//顺丰返回的XML双引号中有双引号的去除方法 Man161101
	function delquotation($s){
		$s9 	= str_replace('&','', $s);
		$i  	= strpos($s9,'remark');
		if(!$i) return $s9;
		while($i){
			$j  	= strpos($s9,'opcode',$i+2);
			$L 	= $j-$i;
			$s2 	= trim(substr($s9,$i,$L));
			$i2 	= strpos($s9,'"',$i)+1;
			$s2 	= trim(substr($s9,$i2,$j-$i2));
			$s2 	= trim($s2,'"');
			$wi 	= strpos($s2,'"',0);
			while($wi>0){
				$s9[$i2+$wi] = '-'; //不能为空，要对应补充原大小
				$wi = strpos($s2,'"',$wi+1);
			}
			$i 	= strpos($s9,'remark',$i+5);
		}
		return $s9;
	}

	//xml转数组
	function object_array($str) {
		//try{
			//echo $str;
			$str = delquotation($str);
			//echo $str ;
			$res = json_decode(json_encode((array) simplexml_load_string($str)),true);
			//return 
		//}catch(Exception $e){
			//echo $str;
			//echo $e;
			//$res = null;
		//}
		return $res;
	}

	//将BusinessLinkCode转换为我们(美快物流)的物流状态	
	function MKIL_State($str){

		if($str == '44'){

			$IL_state = 1005;	//快递派件

		//}else if($str == '130' || $str == '607'  || $str == '80'){
		}else if($str == '80'){

			$IL_state = 1003;	//快递签收

		}else if($str == '50' || $str == '51'){

			$IL_state = 1001;	//快递揽件

		}else if($str == '70' || $str == '611'){

			$IL_state = 1002;	//快递疑难

		}else if(in_array($str, array('33','612','613'))){
			//Man161020
			//$IL_state = 1012;	//延迟
			$IL_state = 1002;	//快递疑难

		}else{

			$IL_state = 1000;	//在途

		}

		return $IL_state;
	}