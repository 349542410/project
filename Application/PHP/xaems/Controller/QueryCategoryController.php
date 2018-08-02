<?php
require_once("../Model/QueryCategory.php");

$data = array(
    "IncludeMaxCategory" => true,
    "CustomerIdentity" => "DVJIXK"
);
$categoryObject = new QueryCategory();
$categoryObject->setRequestData($data);
$categoryObject->soapSubmit();
if ($categoryObject->getResponseSuccess()) {

    $categorys = $categoryObject->getResponse();
    $childCategorys = $categorys->Data->QueryCategoryModel;
    $data = objectToArray($childCategorys);
    var_dump($data);

}else{

    //获取各类型的错误信息

}

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