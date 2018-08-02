<?php
/**
* post方式请求
*
* @param string $url 请求的url
* @param array $data 请求的参数数组（关联数组）
* @param integer $timeout 超时时间（s）
* @return string(请求成功) | false(请求失败)
*/
function curl_post($url, $data, $timeout = 2){
    $data = json_encode($data);
    $ch = curl_init();
    $data = 'data='.$data;
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_0);
    curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
    $result = curl_exec($ch);
    curl_close($ch);
    if (is_string($result) && strlen($result)){
        return $result;
    }else{
        return false;
    }
}

?>