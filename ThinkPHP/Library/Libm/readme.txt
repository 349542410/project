//调用 发送类
$http = new \Org\MK\HTTP();

$rs   = $http->post($url, $json);
$arr = json_decode($rs);