<?php
/* 支付宝预支付签证处理 */
namespace Org\Alipay;

class Pay {


    /** APP预支付接口
    **  请求参数 kind = "配置文件名", data = array("total_fee" => 支付金额 , "body_ali" => 支付标题 , "out_trade_no" => 支付订单号)
    *   返回数据
        Array
            (
                [app_id] => 2016072801676437
                [method] => alipay.trade.app.pay
                [charset] => utf-8
                [sign_type] => RSA2
                [timestamp] => 2017-04-26 11:30:07
                [format] => json
                [notify_url] => Alipay/appnotifyurl
                [biz_content] => {"timeout_express":"30m","seller_id":2088421416276353,"product_code":"QUICK_MSECURITY_PAY","total_amount":"0.01","subject":"1","body":"测试数据","out_trade_no":"20171493177407"}
                [version] => 1.0
                [sign] => bp1P2l9OKWx1BhX5Oty8J65WvOQlAnsDYBLbiDgIwo63jd+tuN1Ca7AbuobAWj017Ezdz16D56nKnhi8euVDYjn6tU4ia8QV4gxkKV/4QAAihyZ7X28pB2QEAT6CSFZzi4kgyp/+SL6SkNBboS6fu+wGzvehkyY3rpsqrmg0tjfwZaKvUKBk98/ipKISq2o7RCc3NTqG9hu0GMfXcMitp4Ll7Rjc1yzGcRYx8W4YMcZ86UHuAmUMXFpP6YV919sle0N27d9XLG0ZGQViGZhHBuiTrewmyd8D3wb99ZTBqx/TLuO1Z8ZuijDT2tcaRehUWFhhzs/swLDY/PDsPJqgyg==
            )
    *
    */
    public function AppPay($kind = "" , $data = array())
    {   

        $kind  = trim($kind) == "" ? "Default" : trim($kind);
        // 加载文件
        require_once('Conf/alipay_' . $kind .'.config.php');
        require_once('SDK/alipayios/aop/AopClient.php');         
        require_once('SDK/alipay/lib/alipay_core.function.php');         

        if(!$data) return false;

        $total_fee          = number_format($data['total_fee']/100, 2, '.', ''); //单位转化为元
        $body_ali           = $data['body'];
        $out_trade_no       = $data['out_trade_no'];
        $subject            = $data['subject'];

        // print_r($alipay_config);exit;
        // 待生成签名时数组
        $parameter = array(
            "app_id"            => $alipay_config['app_id'],
            "method"            => $alipay_config['service'],
            "charset"           => $alipay_config['input_charset'],
            "sign_type"         => $alipay_config['sign_type'],
            "timestamp"         => date('Y-m-d H:i:s' ,time()),
            "format"            => 'json',            
            "notify_url"        => $alipay_config['notify_url'],
            "biz_content"       => '{"timeout_express":"30m","seller_id":'.$alipay_config['partner'].',"product_code":"QUICK_MSECURITY_PAY","total_amount":"'.$total_fee.'","subject":"'.$subject.'","body":"'.$body_ali.'","out_trade_no":"'.$out_trade_no.'"}',
            "version"           => '1.0'            

        );
        
        $alipaySubmit = new \AopClient($alipay_config);
                      

        $para = $alipaySubmit->rsaSign($parameter); 
       
        $parameter['sign'] = $para;
        $return_s = createLinkstringUrlencode($parameter);
        return  $return_s;



    }

     // 支付宝预支付接口
    public function Html5Pay($kind = "" ,$data = array())
    {
        if(!$data) return false;
        $kind  = trim($kind) == "" ? "Default" : trim($kind);

        // 加载文件
        require_once('SDK/alipay/lib/alipay_core.function.php');
        require_once('SDK/alipay/lib/alipay_rsa.function.php');
        require_once('SDK/alipay/lib/alipay_notify.class.php');
        require_once('SDK/alipay/lib/alipay_submit.class.php');
        require_once('Conf/alipay_' . $kind .'.config.php');

        $out_trade_no       = $data['out_trade_no'];        // 订单号
        $subject            = $data['subject'];             // 订单名称
        $total_fee          = number_format($data['total_fee']/100, 2, '.', '');       // 订单金额 单位转化为元
        $show_url           = $data['show_url'];            // 商品浏览地址
        $body               = $data['body'];                // 订单描述

        //构造要请求的参数数组，无需改动
        $parameter = array(

            "service"           => $alipay_config['service'],
            "partner"           => $alipay_config['partner'],
            "seller_id"         => $alipay_config['seller_id'],
            "payment_type"      => $alipay_config['payment_type'],
            "notify_url"        => $alipay_config['notify_url'],
            "return_url"        => $alipay_config['return_url'],
            "_input_charset"    => trim(strtolower($alipay_config['input_charset'])),
            "out_trade_no"      => $out_trade_no,
            "subject"           => $subject,
            "total_fee"         => $total_fee, //总金额
            "show_url"          => $show_url,
            "body"              => $body,
        );
        //建立请求
        $alipaySubmit = new \AlipaySubmit($alipay_config);
        $html_text = $alipaySubmit->buildRequestForm($parameter,"get", "确认");
        echo $html_text;
        exit();
    }




}
