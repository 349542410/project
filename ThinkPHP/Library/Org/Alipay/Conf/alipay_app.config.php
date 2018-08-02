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
//$alipay_config['partner']			= '2088421416276353';
//$alipay_config['app_id']			= '2016072801676437';

/*web
$alipay_config['private_key']		= 'MIICXgIBAAKBgQDb9VNqfUzasN4toNWE7fY26ZvvZO7K1Og5nJLFHxxaqgWNrxXNFV2atQAN3yjS5DxNQoQdtGyjkt+/QDnB+V/5DNPCVeSgE2DnM8t0lncxbQHHah8Atm3tJ+ijtjwAh7rdVAYygZDizXJ1YOX7xFAyrJWMZTvC8OoMdwzxcolxWQIDAQABAoGAdVkIy8NVgUbjAczQnT6nINy5CJr8mtHDoxjZZLkYU3ZpyBEkvGktqx/ti3kHOpvxX/agrYhYfVwaatpE9iuo+yR5p1NeSugGTITS/aCHrBPA3bqciiJxtEzaEUE1SlB/hjRhAzuNy5A5WsUOELBwmSIK78zIK8beSAx/zt/4/EECQQD1XjJNlMyZwLxWglPyjbUA38URQD1jcxMA1R6ECRN8xy7y7DoGYt/ylVEbG/APb2EsVa5EVacKg9aFL2WcgEzNAkEA5X1EQkJs1eaobOJ8qhUIIHoYK4RRhXFqjKgMxavEyfqwuRYMie7YUKaHPvWNyEHG6K/3QlDbYx5gghOz9hO2vQJBAMxbSrwQvSMlQfcvDqnKWkFDHceTYE2Ozvn3hjXjtUZMQo7yLhWZjfllYSqZ5yOD2UPqjHy/daMtUKKWaiOhO9UCQQDSGb1EbEv4CRRpm3FGxbqLATzfmmSIJy3FWJVY48lmoXzp9qXEIkcoj02C9oy3qoDQx0k4DY7NUCJK9H7t616BAkEAtXF0y18eUigiS4+1Wjt3nC4B1xeMFxJdBVX/2vvi4HHvVOkRcu89LVeENNA0s6Lr+zfnbEYk5dFPtLr1ZB7KfA==';
// $alipay_config['alipay_public_key']	= 'MIGfMA0GCSqGSIb3DQEBAQUAA4GNADCBiQKBgQCVFPTaSUYZfJBfFrnIX1TC4C4ZOGwsklS8NaOZ BZ5Aamt1uzYeAmMH2pc8vi5GmTFytgYHMpDcXqqsMVgPBV2jWeeFJf+ULZg1p0dhWRyuLRYFtuGF hM3yoldzTrPhsojK/EPdK8NTpYIFgTG+JjtvILIuODUyRH7wxWEq4qL9TQIDAQAB';
$alipay_config['alipay_public_key']	= 'MIGfMA0GCSqGSIb3DQEBAQUAA4GNADCBiQKBgQCnxj/9qwVfgoUh/y2W89L6BkRAFljhNhgPdyPuBV64bfQNN1PjbCzkIM6qRdKBoLPXmKKMiFYnkd6rAoprih3/PrQEB/VsW8OoM8fxn67UDYuyBTqA23MML9q1+ilIZwBC2AQ2UBVOrFXfFl75p6/B5KsiNG9zpgmLCUYuLkxpLQIDAQAB';
*/

/*Error APp Key
$alipay_config['private_key']		= 'MIICdwIBADANBgkqhkiG9w0BAQEFAASCAmEwggJdAgEAAoGBAMvUQ227YWMya9Ol7X4aGyGhPc1qUtfz0CPHFJjuhYRgZsF4gshU7GXIOPX7L+kjX2hEF/4hBmWjkAEaS4zcmGt4fr5tjg7oJJWfSQHaVRpaWviaVuu/708zksFkgAz5ioUlaIAqdQ/zjDSaV7A5Vs2vFszsP8fI34ZLFX96RHozAgMBAAECgYA/Hf27Txj7JLPrGCiQsfjQ0yNWJqR8ps0/Jvij2siRk2B+bJji/Bkv825gDWZqpT94BA4B7awTcTC4hrH6bpzrrFmcAM1vxnfcNFXwNB4O4AXJcFdmgGhy/N09LqDzfPZTsa8PvNEciB3VIEv4CEKy7XpmH3qrxKSG+RBNmBKxEQJBAP6DkbSYsE9atVKecH8eiMJ2xjIsIZEIfbMqqqXL2CTLTzO7qtVSXDbeswuZNkpbSmgCmftKAVppUZ4B30ef0BcCQQDNBO8VIKfJqXk19VCeHhV9fk2gpQOoV7nyPNH/SfCWW4wgaklDD4+4NSW/RWXFxinWkMKfaNRXZgnq+Q0bdDxFAkEAzsAmfuSCZRQ0s9bNYBZ31jESM/OxmNWi9waz9VcwUENwJYBP+FadXl1uaP+fIKwkN3XVjFLD4qQnjwnKTa6/JQJBAIcLpfSTwxIZ+Qaq5YSRqnQ0WyvjJkqgJpLCr0zqxng0K1Q6zrFGsdOT5p5cNxKUWoDEb/6TDbPYOdY2YQThhsUCQCnI1BStat/WMvcOdOxC1nVrN8w6YKBSGRYonKTFse/Cq6wrlKKnJao+2xhr0++zE5OGqWy+vZ1+b4EnkmSsNRY=';
$alipay_config['alipay_public_key']	= 'MIGfMA0GCSqGSIb3DQEBAQUAA4GNADCBiQKBgQDDI6d306Q8fIfCOaTXyiUeJHkrIvYISRcc73s3vF1ZT7XN8RNPwJxo8pWaJMmvyTn9N4HQ632qJBVHf8sxHi/fEsraprwCtzvzQETrNRwVxLO5jVmRGi60j8Ue1efIlzPXV9je9mkjzOmdssymZkh2QhUrCmZYI/FCEa3/cNMW0QIDAQAB';
*/

//  APP 使用
$alipay_config['partner']			= isset($GLOBALS['globalConfig']['partner']) ?$GLOBALS['globalConfig']['partner']:'2088421416276353';
$alipay_config['app_id']			= isset($GLOBALS['globalConfig']['app_id'])? $GLOBALS['globalConfig']['app_id'] : '2016072801676437';


$alipay_config['private_key']		= isset($GLOBALS['globalConfig']['private_key'])? $GLOBALS['globalConfig']['private_key'] : 'MIICXgIBAAKBgQDb9VNqfUzasN4toNWE7fY26ZvvZO7K1Og5nJLFHxxaqgWNrxXNFV2atQAN3yjS5DxNQoQdtGyjkt+/QDnB+V/5DNPCVeSgE2DnM8t0lncxbQHHah8Atm3tJ+ijtjwAh7rdVAYygZDizXJ1YOX7xFAyrJWMZTvC8OoMdwzxcolxWQIDAQABAoGAdVkIy8NVgUbjAczQnT6nINy5CJr8mtHDoxjZZLkYU3ZpyBEkvGktqx/ti3kHOpvxX/agrYhYfVwaatpE9iuo+yR5p1NeSugGTITS/aCHrBPA3bqciiJxtEzaEUE1SlB/hjRhAzuNy5A5WsUOELBwmSIK78zIK8beSAx/zt/4/EECQQD1XjJNlMyZwLxWglPyjbUA38URQD1jcxMA1R6ECRN8xy7y7DoGYt/ylVEbG/APb2EsVa5EVacKg9aFL2WcgEzNAkEA5X1EQkJs1eaobOJ8qhUIIHoYK4RRhXFqjKgMxavEyfqwuRYMie7YUKaHPvWNyEHG6K/3QlDbYx5gghOz9hO2vQJBAMxbSrwQvSMlQfcvDqnKWkFDHceTYE2Ozvn3hjXjtUZMQo7yLhWZjfllYSqZ5yOD2UPqjHy/daMtUKKWaiOhO9UCQQDSGb1EbEv4CRRpm3FGxbqLATzfmmSIJy3FWJVY48lmoXzp9qXEIkcoj02C9oy3qoDQx0k4DY7NUCJK9H7t616BAkEAtXF0y18eUigiS4+1Wjt3nC4B1xeMFxJdBVX/2vvi4HHvVOkRcu89LVeENNA0s6Lr+zfnbEYk5dFPtLr1ZB7KfA==';

$alipay_config['alipay_public_key']	= isset($GLOBALS['globalConfig']['alipay_public_key'])? $GLOBALS['globalConfig']['alipay_public_key'] : 'MIGfMA0GCSqGSIb3DQEBAQUAA4GNADCBiQKBgQCnxj/9qwVfgoUh/y2W89L6BkRAFljhNhgPdyPuBV64bfQNN1PjbCzkIM6qRdKBoLPXmKKMiFYnkd6rAoprih3/PrQEB/VsW8OoM8fxn67UDYuyBTqA23MML9q1+ilIZwBC2AQ2UBVOrFXfFl75p6/B5KsiNG9zpgmLCUYuLkxpLQIDAQAB';

//收款支付宝账号，以2088开头由16位纯数字组成的字符串，一般情况下收款账号就是签约账号
$alipay_config['seller_id']	= $alipay_config['partner'];
//商户的私钥,此处填写原始私钥去头去尾，RSA公私钥生成：https://doc.open.alipay.com/doc2/detail.htm?spm=a219a.7629140.0.0.nBDxfy&treeId=58&articleId=103242&docType=1
/*
// 服务器异步通知页面路径  需http://格式的完整路径，不能加?id=123这类自定义参数，必须外网可以正常访问
// $alipay_config['notify_url'] = "http://pay.megao.hk:81/Alipay/notifyurl";
$alipay_config['notify_url'] = "http://pay.megao.hk:81/Alipay/appnotifyurl";
// 页面跳转同步通知页面路径 需http://格式的完整路径，不能加?id=123这类自定义参数，必须外网可以正常访问
$alipay_config['return_url'] = "http://pay.megao.hk:81/Alipay/returnurl";
*/
// 服务器异步通知页面路径  需http://格式的完整路径，不能加?id=123这类自定义参数，必须外网可以正常访问
$alipay_config['notify_url'] = C("pay_do") . "Alipay/appnotifyurl";
// 页面跳转同步通知页面路径 需http://格式的完整路径，不能加?id=123这类自定义参数，必须外网可以正常访问
$alipay_config['return_url'] = C("pay_do") . "Alipay/returnurl";

//签名方式
$alipay_config['sign_type']    = strtoupper('RSA2');
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
$alipay_config['service'] 		=  "alipay.trade.app.pay"; // "alipay.wap.create.direct.pay.by.user"; // 

//↑↑↑↑↑↑↑↑↑↑请在这里配置您的基本信息↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑


?>