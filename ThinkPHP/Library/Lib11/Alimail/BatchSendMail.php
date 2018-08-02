<?php    
    include_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'aliyun-php-sdk-core' . DIRECTORY_SEPARATOR . 'Config.php';
    use Dm\Request\V20151123 as Dm;
    $iClientProfile = DefaultProfile::getProfile("cn-hangzhou", self::$accessKey, self::$accessSecret);
    // $iClientProfile = DefaultProfile::getProfile("cn-hangzhou", "<your accessKey>", "<your accessSecret>");
    $client = new DefaultAcsClient($iClientProfile);
    $request = new Dm\BatchSendMailRequest();

    $request->setAccountName(self::$AccountName);
    $request->setAddressType(self::$AddressType);

    $request->setTemplateName(self::$TemplateName);
    $request->setReceiversName(self::$ReceiversName);
    $request->setTagName(self::$TagName);

    try {
        $response = $client->getAcsResponse($request);
        print_r($response);
    }
    catch (ClientException  $e) {
        print_r($e->getErrorCode());   
        print_r($e->getErrorMessage());   
    }
    catch (ServerException  $e) {        
        print_r($e->getErrorCode());   
        print_r($e->getErrorMessage());
    }
?>