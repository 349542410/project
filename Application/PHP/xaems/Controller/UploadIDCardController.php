<?php
require_once("../Model/UploadIdCard.php");

$getMethod = "POST";
if($getMethod === "POST") {
    $cardFront = "E:/BTZ_DEMO/demoesZY/Image/front.jpg";
    $cardBack = "E:/BTZ_DEMO/demoesZY/Image/back.jpg";

    $CoverOldIDCard = true;//获取是否覆盖旧的身份证
    if($CoverOldIDCard == 'true') {
        $CoverOldIDCard = 1;
    }else{
        $CoverOldIDCard = 0;
    }
    $OrderNumberOrTrackingNumber = "BZ000000836US";
    $IDCardNumber = "51170219870626850X";
    $Addressee = "皇氏";
    // TODO: 需要安全验证
    $IDCardFrond = file_get_contents($cardFront);//二进制
    $IDCardBack = file_get_contents($cardBack);//二进制
    $uploadIDCard = new UploadIDCard();
    $requestData = ['Addressee' => $Addressee,'IDCardFront'=>$IDCardFrond,'IDCardBack'=>$IDCardBack,'OrderNumberOrTrackingNumber'=>$OrderNumberOrTrackingNumber,'IDCardNumber'=>$IDCardNumber,'CoverOldIDCard'=>$CoverOldIDCard];
    $uploadIDCard->setRequestData($requestData);
    $uploadIDCard->soapSubmit();
    if ($uploadIDCard->getResponseSuccess())
    {
        echo "身份证明上传成功";
    }
    else
    {
        echo $uploadIDCard->getError()->message;
    }
    return;
}























?>