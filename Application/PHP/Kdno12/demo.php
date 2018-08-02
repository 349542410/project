<?php
	$config = array(
		'customerCode'    => 'OSMS_1',
		'checkword'       => 'fc34c561a34f',
		'imgUploadAction' => "http://osms.sit.sf-express.com:2080/osms/hessian/uploadIdentityService",
	);

	$xml = '{"name":"abcc","phone":"13898744567","cardId":"430879199415451874","bno":"445978654456","image":"/9j/4AAQSkZJRgABAgAAAQABAAD/2wBDAAgGBgcGBQgHBwcJCQgKDBQNDAsLDBkSEw8UHRofHh0aHBwgJC4nICIsIxwcKDcpLDAxNDQ0Hyc5PTgyPC4zNDL/2wBDAQkJCQwLDBgNDRgyIRwhMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIy"}';

	$data = base64_encode($xml);


	$checkword = $config['checkword'];

	$validateStr = base64_encode(md5($xml.$checkword, false));
	
	$customerCode = $config['customerCode'];

	include_once 'HessianPHP/HessianClient.php';

	$http = new HessianClient($config['imgUploadAction']);

    $result = $http->uploadIdentity($data, $validateStr, $customerCode);
	
    echo $result;