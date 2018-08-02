<?php
require_once("../Model/ImportOrdersforSplit.php");
require_once("../Model/QueryCategory.php");

$errorCodes = array('0x00000108', '0x00000114', '0x00000116', '0x00000174');
$ImportFileName = "E:/demoesBK/Document/chaixiang.xlsx";
$TrackingCenterCode = "HZ001";
$ChannelCode = "CH0001";
$CustomerIdentity = "DEFLYE";
if ($ImportFileName) {
    $importExpressBill = new ImportOrdersforSplit();//拆箱单接口

    //动态设置 选择值
    $categoryData = array(
        "IncludeMaxCategory" => true,
        "CustomerIdentity" => $CustomerIdentity
    );
    $importExpressBill->dynamicSetSelect('CategoryCode', getCategorysKeyValue($categoryData));
    $importExpressBill->setCustomerIdentity($CustomerIdentity);
    //添加货站编码
    $importExpressBill->setTrackingCenterCode($TrackingCenterCode);
    $importExpressBill->setChannelCode($ChannelCode);
    //动态设置 选择值
    $Package = $importExpressBill->readPackage($ImportFileName);
    if ($Package === false) {
        $serviceError = $importExpressBill->getError();
        $msgTwelve = '导入面单数据不完整或没有按照表格格式书写';
        $serviceError = empty($serviceError) ? $msgTwelve : $serviceError;
        $ret = array('status' => 0, 'message' => $serviceError);
        var_dump($ret);
        return;
    }
    //记录错误信息
    $error = '';
    $errorCode = '';
    foreach ($Package as $k => $v) {
        $value = array_values($v);
        $values = $importExpressBill->handle($value);
        //拆箱单过滤不需要的字段
        $arrayPackages = $importExpressBill->filter($values);

        $importExpressBill->setRequestData($arrayPackages);
        $importExpressBill->soapSubmit();
        if (!$importExpressBill->getResponseSuccess()) {
            $ret = array('status' => 0, 'message' => $importExpressBill->getResponse()->ResponseError->LongMessage);
            $error = $importExpressBill->getResponseErrorMessage();
            $errorCode = $importExpressBill->getResponseErrorCode();
            var_dump($errorCode.$error);
            return;
        }
    }

    if (empty($error)) {
        $msgThirteen = '导入成功';
        $ret = array('status' => 1, 'message' => $msgThirteen);
        var_dump($ret);
        return;
    } else {
        $errors = in_array($errorCode, $errorCodes) ? explode(',', $error) : $error;
        $error = is_array($errors) ? end($errors) : $error;
        $error = in_array($errorCode, $errorCodes) ? $importExpressBill->getResponseShortMessage() . $error : $errors;
        $ret = array('status' => 0, 'message' => $error);
        var_dump($ret);
        return;
    }
} else {
    $msgFourteen = '数据异常';
    $ret = array('status' => 0, 'message' => $msgFourteen);
    var_dump($ret);
    return;
}


/*
* 获取品类 key => value
* code => categoryName
*/
function getCategorysKeyValue($categoryData)
{
    $categorys = getCategorys($categoryData);
    $categorysKeyValue = array();
    foreach ($categorys as $key => $value) {
        foreach ($value['ChildrenCategory']['QueryCategoryModel'] as $z => $y) {
            $categorysKeyValue[$y['Code']] = $y['Name'];
        }
    }
    return $categorysKeyValue;
}


/**
 * 获取品类
 * return array
 */
function getCategorys($categoryData)
{
    $data = array();
    $categoryObject = new QueryCategory();
    $categoryObject->setRequestData($categoryData);
    $categoryObject->soapSubmit();
    if ($categoryObject->getResponseSuccess()) {
        $categorys = $categoryObject->getResponse();
        $childCategorys = $categorys->Data->QueryCategoryModel;
        $data = objectToArray($childCategorys);
    }
    return $data;
}


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