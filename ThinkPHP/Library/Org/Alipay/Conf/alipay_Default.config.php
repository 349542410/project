<?php
/* *
 * 配置文件
 * 版本：3.5
 * 日期：2016-06-25
 * 说明：
 * 以下代码只是为了方便商户测试而提供的样例代码，商户可以根据自己网站的需要，按照技术文档编写,并非一定要使用该代码。
 * 该代码仅供学习和研究支付宝接口使用，只是提供一个参考。

 * 安全校验码查看时，输入支付密码后，页面呈灰色的现象，怎么办？
 * 解决方法：
 * 1、检查浏览器配置，不让浏览器做弹框屏蔽设置
 * 2、更换浏览器或电脑，重新登录查询。
 */
 
//↓↓↓↓↓↓↓↓↓↓请在这里配置您的基本信息↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓
//合作身份者ID，签约账号，以2088开头由16位纯数字组成的字符串，查看地址：https://openhome.alipay.com/platform/keyManage.htm?keyType=partner

//Real
$alipay_config['partner']			= isset($GLOBALS['globalConfig']['partner']) ?$GLOBALS['globalConfig']['partner']:'2088421416276353';
$alipay_config['app_id']			= isset($GLOBALS['globalConfig']['app_id'])? $GLOBALS['globalConfig']['app_id'] : '2016072801676437';


$alipay_config['private_key']		= isset($GLOBALS['globalConfig']['private_key'])? $GLOBALS['globalConfig']['private_key'] : 'MIICXgIBAAKBgQDb9VNqfUzasN4toNWE7fY26ZvvZO7K1Og5nJLFHxxaqgWNrxXNFV2atQAN3yjS5DxNQoQdtGyjkt+/QDnB+V/5DNPCVeSgE2DnM8t0lncxbQHHah8Atm3tJ+ijtjwAh7rdVAYygZDizXJ1YOX7xFAyrJWMZTvC8OoMdwzxcolxWQIDAQABAoGAdVkIy8NVgUbjAczQnT6nINy5CJr8mtHDoxjZZLkYU3ZpyBEkvGktqx/ti3kHOpvxX/agrYhYfVwaatpE9iuo+yR5p1NeSugGTITS/aCHrBPA3bqciiJxtEzaEUE1SlB/hjRhAzuNy5A5WsUOELBwmSIK78zIK8beSAx/zt/4/EECQQD1XjJNlMyZwLxWglPyjbUA38URQD1jcxMA1R6ECRN8xy7y7DoGYt/ylVEbG/APb2EsVa5EVacKg9aFL2WcgEzNAkEA5X1EQkJs1eaobOJ8qhUIIHoYK4RRhXFqjKgMxavEyfqwuRYMie7YUKaHPvWNyEHG6K/3QlDbYx5gghOz9hO2vQJBAMxbSrwQvSMlQfcvDqnKWkFDHceTYE2Ozvn3hjXjtUZMQo7yLhWZjfllYSqZ5yOD2UPqjHy/daMtUKKWaiOhO9UCQQDSGb1EbEv4CRRpm3FGxbqLATzfmmSIJy3FWJVY48lmoXzp9qXEIkcoj02C9oy3qoDQx0k4DY7NUCJK9H7t616BAkEAtXF0y18eUigiS4+1Wjt3nC4B1xeMFxJdBVX/2vvi4HHvVOkRcu89LVeENNA0s6Lr+zfnbEYk5dFPtLr1ZB7KfA==';

$alipay_config['alipay_public_key']	= isset($GLOBALS['globalConfig']['alipay_public_key'])? $GLOBALS['globalConfig']['alipay_public_key'] : 'MIGfMA0GCSqGSIb3DQEBAQUAA4GNADCBiQKBgQCnxj/9qwVfgoUh/y2W89L6BkRAFljhNhgPdyPuBV64bfQNN1PjbCzkIM6qRdKBoLPXmKKMiFYnkd6rAoprih3/PrQEB/VsW8OoM8fxn67UDYuyBTqA23MML9q1+ilIZwBC2AQ2UBVOrFXfFl75p6/B5KsiNG9zpgmLCUYuLkxpLQIDAQAB';



// $alipay_config['private_key']		= 'MIIEvQIBADANBgkqhkiG9w0BAQEFAASCBKcwggSjAgEAAoIBAQCFThtGAJbKjXE3xzF8Zc+Rs+On/jglpQ4GUYEkb3so613KttE7DTIm+KBckLsUWmJzAQC6gIClv5FSbH6eb4IVgy1uJdlu4NgRKc1fizosQRGEJKHaUTX2QKfliTQ5FvJOUsLO35Og2IWxdOJs9PeBkGNDzVSslzqD+nMP/xVy4a18vp2KEQlIuEF6m1b+7rPn1HN0S/bmxAPL1/7FeOTIUO1end5vZmjqp/l03e3CJlH2RSOiznsSAB2NnHj1KRDFeBR5h33otpANCUt6RGugvD6MV0T6CpSocqCoc06FDRxcjTpevqSGY9xVkj2hJWNYJ/eXTBJOuQ00YnsZMX7bAgMBAAECggEAJ6ANGWTpbfJXekYa3qiA6AW6IWC/HemPXq9xnPwKdyJnse9gCJamltEHEhzvj2BCrX7Z0ZaLJznnn8GibcQlvfFNWtWjaYMqlwd/Beyj8S2yYD5nYjcLvFSB1AhR3rqEcmXFhKsO/hv+ub5N5Cd5PylFaI0ro1Yczchv1Yx0ur4NEp9FgO7VOXuQTFNmQ7Upfw6427sG46zyzbRiYtwrAzo26KTHaqWcPb8OpT7FP58KZU58dRO5A97LtO89rTm8FiAtogtaWjm2whZ1veQV2b+wWaErTQn4usO0xI2HlocqzucVSZfcHk7jTgU1NvEty4L7gnb8nROi90N4o/x14QKBgQDedZRq7vacTxXHM8WXphQjwRVxzzMyGBKYbrd9Ag7FZSlxS5GQIGAsf4wQBKzYxsE9QkCOc8Xq3n3gLqq3Jj1s/uHNJHR6ijRhO+j7zm0QYR8lfZAqc0W/64OX+qiOhyRMt2c7kM06vO6GxQSZ5gFoRI+Td3gm9M8VteCUMeNCSwKBgQCZZ2D3vp5zip9JicQeGwGuYswze5lVD2bJCZZNPEqZWAVUUyCzL8ZR0gaGu26CexvHtJkAGs6cN6nuXuR6A0gwEiuulAvR315O4jGKk+nqOY/MQeptdOS9jDAgoawM+/e/fm+6HuPDdYAWswPx6wVubrER1FbeU+EBCS3C54BbsQKBgQCsIIRPXUj51wN91+Q6m6mXpK3IGs99Ij05LunQ0wfE2qp/XD5sK3De3W3tcwCe1uLsWFgFITVxrufsz48OGYuLy4fBqERsEIXI+ociVy3yb4OfCZHEFt9QSZXPwYYsigqfRYWeBEOYAFn7c3RE9EAAgpQpQVZ5phCZttnnMNasEwKBgC91Rw9+HmEaaqsCfvTdYAjGMexfeZFSIXdiiug8FcwY6hUrXntw9UbM1g4KoGanlXGUEp1wraiwo9bF7qM8rrGIfZEV5g44r2FyIud/WSeIRU9ouRDB0B4/54fA3Ixryzqn8ALBma8Xg9gB69+E2PpQLmGYsoM3qe17HHgmzJQRAoGARYR3Ie4m2G5jJB0mdlifil/X8CXWoCPpKa21dhqXJQ3E32WaZj8QglOt6UnweQTWCK8InF3StQ9JY+ZnRFYjdW1l816G0tCYcY011Ld9yHRca1T2cqJBLbM3TR4N7sbvnj/BKbLKEomjNclfy/FQCSFBKl2iKC64uCQHrJG8JB4=';
// $alipay_config['alipay_public_key']	= 'MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAjzGdad/tc+K3p7r2NTXMte7WGiOC2rv/bXiqVGJ9ko79eVu3Khol5J2kad9BTPAAtQpR/5/1Xo9Os0jjshd6zQ6o+Wkcs+snZIJWzrdx3aHeZi6fhg02I5zr6tqj3Y4CA70MW87ESGEuT/I1wL1KWG6AeD+SIwqABXXzW/Semhwj6vXNdi3ixXAocRp5W/HW991X1tAjbknzoolxqvYzSxZXJmt0pOg/HOsVV6ZASjKaH0trNHi0AOADFD40OoHvfAasBf8h/ot4AbZSduePyP8EJNxkFQuNfTWKYY6A3ANjn2W5cem+xkNaLyrJeNuZJcBU40n3RNPberOtaazn9wIDAQAB';

//收款支付宝账号，以2088开头由16位纯数字组成的字符串，一般情况下收款账号就是签约账号
$alipay_config['seller_id']	= $alipay_config['partner'];
//商户的私钥,此处填写原始私钥去头去尾，RSA公私钥生成：https://doc.open.alipay.com/doc2/detail.htm?spm=a219a.7629140.0.0.nBDxfy&treeId=58&articleId=103242&docType=1

/*
// 服务器异步通知页面路径  需http://格式的完整路径，不能加?id=123这类自定义参数，必须外网可以正常访问
$alipay_config['notify_url'] = "http://pay.megao.hk:81/Alipay/notifyurl";
// 页面跳转同步通知页面路径 需http://格式的完整路径，不能加?id=123这类自定义参数，必须外网可以正常访问
$alipay_config['return_url'] = "http://pay.megao.hk:81/Alipay/returnurl";
*/
// 服务器异步通知页面路径  需http://格式的完整路径，不能加?id=123这类自定义参数，必须外网可以正常访问
$alipay_config['notify_url'] =  C("pay_do") . "Alipay/notifyurl";
// $alipay_config['notify_url'] =  C("pay_do") . "home/Index/notifyurl";

// 页面跳转同步通知页面路径 需http://格式的完整路径，不能加?id=123这类自定义参数，必须外网可以正常访问
$alipay_config['return_url'] =  C("pay_do") . "Alipay/returnurl";
// $alipay_config['return_url'] =  C("pay_do") . "home/Index/returnurl";

//签名方式
$alipay_config['sign_type']    = strtoupper('RSA');
//$alipay_config['sign_type']    = strtoupper('MD5');

//字符编码格式 目前支持utf-8
$alipay_config['input_charset']= strtolower('utf-8');

//ca证书路径地址，用于curl中ssl校验
//请保证cacert.pem文件在当前文件夹目录中
$alipay_config['cacert']    = getcwd().'\\cacert.pem';

//访问模式,根据自己的服务器是否支持ssl访问，若支持请选择https；若不支持请选择http
$alipay_config['transport']    = 'http';

// 支付类型 ，无需修改
$alipay_config['payment_type'] = "1";
		
// 产品类型，无需修改
$alipay_config['service'] 		=  "alipay.wap.create.direct.pay.by.user"; // "alipay.trade.app.pay"; // 手机网站
// $alipay_config['service'] 		=  "create_direct_pay_by_user"; // "alipay.trade.app.pay"; // pc

//↑↑↑↑↑↑↑↑↑↑请在这里配置您的基本信息↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑


?>