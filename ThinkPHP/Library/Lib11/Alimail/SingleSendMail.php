<?php    
    include_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'aliyun-php-sdk-core' . DIRECTORY_SEPARATOR . 'Config.php';
    use Dm\Request\V20151123 as Dm;
    $iClientProfile = DefaultProfile::getProfile("cn-hangzhou", self::$accessKey, self::$accessSecret);
    // $iClientProfile = DefaultProfile::getProfile("cn-hangzhou", "<your accessKey>", "<your accessSecret>");
    $client = new DefaultAcsClient($iClientProfile);
    $request = new Dm\SingleSendMailRequest();

    $request->setAccountName(self::$AccountName);
    $request->setAddressType(self::$AddressType);

    $request->setReplyToAddress(self::$ReplyToAddress);
    $request->setFromAlias(self::$FromAlias);
    $request->setToAddress(self::$ToAddress);
    $request->setSubject(self::$Subject);
    
    switch(strtolower(self::$Type)){
        case 'html' :
            $request->setHtmlBody(self::$Content);
            break;
        case 'image' :
            $request->setHtmlBody(buildImage(self::$Content));
            break;
        case 'text' : 
        default :
            $request->setTextBody(self::$Content);
            break;
    }

    // if(strtolower(self::$Type)==="text"){
    //     $request->setTextBody(self::$Content);
    // }else{
    //     $request->setHtmlBody(self::$Content);
    // }

    // dump(self::$AccountName);
    // dump(self::$AddressType);
    // dump(self::$ReplyToAddress);
    // dump(self::$FromAlias);
    // dump(self::$ToAddress);
    // dump(self::$Subject);

    try {
        $response = $client->getAcsResponse($request);
        dump($response);
    }
    catch (ClientException  $e) {
        dump($e->getErrorCode());   
        dump($e->getErrorMessage());   
    }
    catch (ServerException  $e) {        
        dump($e->getErrorCode());   
        dump($e->getErrorMessage());
    }


    function buildImage($url=array()){
        $urlstr = '';
        foreach($url as $k=>$v){
            $urlstr .= '<div><img src="' . $v . '" /></div>';
        }
        return '<!DOCTYPE HTML><html><head></head><body>' . $urlstr . '</body></html>';
    }

?>