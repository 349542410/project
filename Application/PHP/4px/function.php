<?php
	
	//将BusinessLinkCode转换为我们(美快物流)的物流状态	
	function MKIL_State($str){

		if($str == 'STT01'){
			$IL_state = 1001;	//快递揽件

		}else if($str == 'STT04'){
			$IL_state = 1005;	//快递派件

		}else if($str == 'STT99'){
			$IL_state = 1003;	//快递签收

		}else if($str == 'STT07'){
			$IL_state = 1002;	//快递疑难

		}else{

			if(strlen($str) >= 2){
				
				$head = substr($str, 0, 2);
				// echo $head;

				switch ($head) {

					//错件
					case 'SH':
						$IL_state = 1010;
						break;

					//海关问题件
					case 'HC':
						$IL_state = 1011;
						break;

					//延迟
					case 'TD':
						$IL_state = 1012;
						break;

					//其它算作在途
					default:
						$IL_state = 1000;
						break;
				}

			}else{
				//业务状态代码 长度少于2的时候也算作 在途
				$IL_state = 1000;

			}	
		}

		return $IL_state;
	}