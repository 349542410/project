<?php
require_once("../Model/QueryTraceStatusFlow.php");

$data = "BZ000000218US";
$traceFlowObject = new QueryTraceStatusFlow();
$traceFlowObject->setRequestData($data);
$traceFlowObject->soapSubmit();
if ($traceFlowObject->getResponseSuccess()) {
    $traceFlow = $traceFlowObject->getResponse();
    $traceFlows = $traceFlow->Data->TraceFlow;
    $data = objectToArray($traceFlows);
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