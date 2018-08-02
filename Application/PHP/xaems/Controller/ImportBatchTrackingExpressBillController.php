<?php
require_once("../Model/ImportBatchTrackingExpressBill.php");
require_once("../Model/QueryCategory.php");


//得到货站编码
$TrackingCenterCode = "HZ001";
//得到渠道编码
$ChannelCode = "CH0001";
// 导入 SmallLabelTranshipment
$transhipmentType = "GeneralTranshipment";
//用户标识
$customerIdentity = "DEFLYE";

/**
 * 上传安全判断,只允许上传 excel, 大小 2097152
 */
set_time_limit(0);
$allowSize = 2097152;
$errorCodes = array('0x00000108', '0x00000114', '0x00000116', '0x00000174');
$allowType = array('application/vnd.ms-excel', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'application/octet-stream');

//开发时打开获取文件
/*$ImportFileName = $_FILES["expressbill"]["tmp_name"];
$fileType = $_FILES["expressbill"]['type'];
$fileSize = $_FILES["expressbill"]['size'];
if (!in_array($fileType, $allowType)) {
    $msgten = '请上传已下载的模板文件';
    $ret = array('status' => 0, 'message' => $msgten);
    exit($ret);
}
if ($fileSize > $allowSize) {
    $msgEleven = '上传大小最大不能超过2M';
    $ret = array('status' => 0, 'message' => $msgEleven);
    exit($ret);
}*/

//demo直接引入的文件
$ImportFileName = "E:/demoesBK/Document/zhuanyun.xlsx";

//初始化
$init_data = [
    'transhipmentType' => $transhipmentType,
];
if ($ImportFileName) {
    $importExpressBill = new ImportBatchTrackingExpressBill($init_data);//普通转运单接口

    //动态设置 选择值
    $categoryData = array(
        "IncludeMaxCategory" => true,
        "CustomerIdentity" => $customerIdentity
    );
    $importExpressBill->dynamicSetSelect('MinCategoryCode', getCategorysKeyValue($categoryData));


    //添加基本信息
    $importExpressBill->setCustomerIdentity($customerIdentity);
    $importExpressBill->setTranshipmentType($transhipmentType);

    //添加货站编码
    $importExpressBill->setTrackingCenterCode($TrackingCenterCode);
    $importExpressBill->setChannelCode($ChannelCode);

    //动态设置值
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
        $v = array_values($v);
        $importExpressBill->setRequestData($v);
        $importExpressBill->soapSubmit();

        if (!$importExpressBill->getResponseSuccess()) {
            $error = $importExpressBill->getResponseErrorMessage();
            $errorCode = $importExpressBill->getResponseErrorCode();
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
    $msgFourteen = $this->get('translator')->trans($msgFourteen);
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