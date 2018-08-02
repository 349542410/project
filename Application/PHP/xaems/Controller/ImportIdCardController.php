<?php
require_once("../Model/ImportBatchIdCard.php");

$model = new ImportBatchIDCard();

$model->CustomerIdentity = "DEFLYE";

$postData = array(
    0 => array(
        'IDCardNumber' => "xxxxxxxxxxxx",
        'Receiver' => "shawnli",
        'TrackingNumber' => "dd000000100"
    ),

);

$model->setIdCardInfos($postData);

$request_data = $model->getEntityData();

var_dump($request_data);

$model->setRequestData($request_data);
$model->soapSubmit();
if ($model->getResponseSuccess()) {
    $return_result = '导入成功';
    echo $return_result;
} else {
    echo $model->getError()->message;
}









?>