<?php
require_once("../Model/ImportExpressBill.php");
require_once("../Model/QueryCategory.php");

//得到货站编码
$TrackingCenterCode = "HZ001";
//得到渠道编码
$ChannelCode = "CH0001";
/**
 * 上传安全判断,只允许上传 excel, 大小 2097152
 */
set_time_limit(0);
$allowSize = 2097152;
$allowType = array('application/vnd.ms-excel', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
//$ImportFileName = $_FILES["expressbill"]["tmp_name"];
$ImportFileName = "E:/demoesTW/Document/zhiyou.xlsx";
//$fileType = $_FILES["expressbill"]['type'];
//$fileSize = $_FILES["expressbill"]['size'];
$errorCodes = array('0x00000108', '0x00000114', '0x00000116', '0x00000174');

/*if (!in_array($fileType, $allowType)) {
    $msgfive = '请上传已下载的模板文件';
    $msgfive = $this->get('translator')->trans($msgfive);
    $ret = array('status' => 0, 'message' => $msgfive);
    return new Response(json_encode($ret));
}
if ($fileSize > $allowSize) {
    $msgsix = '上传大小最大不能超过2M';
    $msgsix = $this->get('translator')->trans($msgsix);
    $ret = array('status' => 0, 'message' => $msgsix);
    return new Response(json_encode($ret));
}*/

if ($ImportFileName) {
    $importExpressBill = new ImportExpressBill();
    $importExpressBill->UserID = "";
    //设置货站编码
    $importExpressBill->setTrackingCenterCode($TrackingCenterCode);
    //设置渠道编码
    $importExpressBill->setChannelCode($ChannelCode);

    $customerIdentity = "DEFLYE";

    $importExpressBill->setCustomerIdentity($customerIdentity);
    //动态设置 选择值
    $importExpressBill->dynamicSetSelect('MinCategoryCode', getCategorysKeyValue());

    $Package = $importExpressBill->readPackage($ImportFileName);

    if ($Package === false) {
        $serviceError = $importExpressBill->getError();
        $msgseven = '导入面单数据不完整或没有按照表格格式书写';
        $serviceError = empty($serviceError) ? $msgseven : $serviceError;
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
            $error .= $importExpressBill->getResponseErrorMessage();
            $errorCode = $importExpressBill->getResponseErrorCode();
        }
    }
    if (empty($error)) {
        $msgeight = '导入成功';
        $ret = array('status' => 1, 'message' => $msgeight);
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
    $msgnine = '数据异常';
    $ret = array('status' => 0, 'message' => $msgnine);
    var_dump($ret);
    return;
}



/*
     * 获取品类 key => value
     * code => categoryName
     */
function getCategorysKeyValue()
{
    $categorys = getCategorys();
    $categorysKeyValue = array();
    foreach ($categorys as $key => $value) {
        foreach ($value['ChildCategorys']['ChildCategoryModel'] as $z => $y) {
            $categorysKeyValue[$y['Code']] = $y['Name'];
        }
    }
    return $categorysKeyValue;
}


/**
 * 获取品类
 * return array
 */
function getCategorys()
{
    $data = array();
    $categoryObject = new QueryCategory();
    $categoryObject->setRequestData(array('Data' => null));
    $categoryObject->soapSubmit();
    if ($categoryObject->getResponseSuccess()) {
        $categorys = $categoryObject->getResponse();
        $childCategorys = $categorys->Data->CategoryModel;
        $data = $this->objectToArray($childCategorys);
    }
    return $data;
}
























?>