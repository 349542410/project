<?php

require_once("../Model/QueryOrderIDCardStatus.php");

$data = array(
    "CustomerIdentity" => "DEFLYE",
    "TrackingNumberSet" => array(
        0 => "BZ000000116US",
        1 => "BZ000000306US"
    )
);
$statusObject = new QueryOrderIDCardStatus();
$statusObject->setRequestData($data);
$statusObject->soapSubmit();
var_dump($statusObject);
$idCardStatus = "";
if ($statusObject->getResponseSuccess()) {
    $idCardStatus = $statusObject->getResponse();
    $data = objectToArray($idCardStatus->Data->OrderIDCardStatusSet);
}
var_dump($data);
return;




/*
     * 对象转化为数组
     * @param object $obj
     * @access public
     * return array
*/
function objectToArray($obj)
{
    $_arr = is_object($obj) ? get_object_vars($obj) : $obj;
    foreach ($_arr as $key => $val) {
        $val = (is_array($val) || is_object($val)) ? objectToArray($val) : $val;
        $arr[$key] = $val;
    }
    return $arr;
}





























?>