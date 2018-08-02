<?php
	$config = array(
		'clientid' 	=> 'CO0000000033',
		'key' 		=> '12345678',
		'sender'	=> 'TEST17',
		'sendUrl'	=> 'http://58.63.50.170:18080/cbt/client/declare/sendMessage.action?',
		//'sendUrl'	=> 'https://open.singlewindow.gz.cn/swcbes/client/declare/sendMessage.action?',
		//'sendUrl'	=> 'https://open.singlewindow.gz.cn/swcbes/client/declare/sendMessage.action?',
		'logs'		=> true,
		'saveto'	=> 'D:/www/upfiles/g/gzc/logs/',
	);
	//http://58.63.50.170:18080/cbt/client/declare/sendMessage.action?';//?clientid=CO0000000033&key=12345678&messageType=';
function xml_to_array( $xml )
{
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