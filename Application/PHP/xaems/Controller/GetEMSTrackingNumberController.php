<?php

require_once("../Model/GetEMSTrackingNumber.php");

$data = array(
    "ChannelCode" => "CH0001",
    "CustomerIdentity" => "DEFLYE"
);
$numberObject = new GetEMSTrackingNumber();
$numberObject->setRequestData($data);
$numberObject->soapSubmit();
if ($numberObject->getResponseSuccess()) {
    $number = $numberObject->getResponse();
    $data = $number->Data;
}
var_dump($data);
return;






























?>