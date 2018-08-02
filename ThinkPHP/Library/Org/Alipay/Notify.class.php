<?php
/* 支付宝支付完成后的回调验证 */
namespace Org\Alipay;

class Notify {

    /*  异步回调验证     
        请求方式 post
        请求数据 支付宝回调请求参数
        返回数据
            array(
                "code"  => 0|1,         // 验证通过返回 1 验证失败返回 0
                "error" => "验证失败"   // 错误信息
                $data   => array(
                    "out_trade_no"      => '',      // 订单号
                    "cash_fee"          => "",      // 支付金额 单位分
                    "fee_type"          => "",      // 货币类型 
                    "openid"            => ""       // 微信id 或 支付宝账号
                    "return_code"       => ""       // 支付状态 SUCCESS
                    "transaction_id"    => ""       // 支付订单号
                    "bank_type"         => ""       // 银行名称
                    "time_end"          => ""       // 支付完成时间
                    "is_subscribe"      => ""       // 用户是否关注公众账号，Y-关注，N-未关注，仅在公众账号类型支付有效 

                    
                
                )
            )   
    */
    
    public function verifyNotify($kind = ""){
        $kind  = trim($kind) == "" ? "Default" : trim($kind);

        require_once('SDK/alipay/lib/alipay_core.function.php');
        require_once('SDK/alipay/lib/alipay_rsa.function.php');
        require_once('SDK/alipay/lib/alipay_notify.class.php');
        require_once('SDK/alipay/lib/alipay_submit.class.php');

        require_once('Conf/alipay_' . $kind .'.config.php');

        $alipayNotify = new \AlipayNotify($alipay_config);
        $verify_result = $alipayNotify->verifyNotify();

         // 返回数据处理
        if(count($verify_result["data"]) > 0 ){

            switch($kind){
            
                case "pc":
                    $openid = $arr['buyer_email'];
                    break;
                case "app":
                    $openid = $arr['buyer_logon_id'];
                    break;
                default:
                    $openid = "";
            }

            $arr = $verify_result["data"];
            switch($kind){            
                case "pc":
                    $openid = $arr['buyer_email'];
                    break;
                case "app":
                    $openid = $arr['buyer_logon_id'];
                    break;
                default:
                    $openid = "";
            }
            $data = array(
                "out_trade_no"      => $arr['out_trade_no'],        // 订单号
                "cash_fee"          => $kind == 'app' ?$arr['total_amount'] * 100 : $arr['total_fee'] * 100,     // 支付金额 单位分
                "fee_type"          => "CNY",                       // 货币类型 CNY 人民币
                "openid"            => $openid,         // 微信id 或 支付宝账号
                "return_code"       => $arr['trade_status'] == "TRADE_SUCCESS" ? "SUCCESS" : $arr['trade_status'],         // 支付状态 SUCCESS
                "transaction_id"    => $arr['trade_no'],            // 支付订单号
                "bank_type"         => "",                          // 银行名称
                "time_end"          => $arr['notify_time'],         // 支付完成时间
                "is_subscribe"      => "N"                          // 用户是否关注公众账号，Y-关注，N-未关注，仅在公众账号类型支付有效 

            );

            $verify_result["data"] = $data;
        }


        return $verify_result;
    }

    /* 同步回调验证 */
    /*
        请求方式 get
        请求数据 支付宝回调请求参数
        返回数据
             array(
                "code"  => 0|1,         // 验证通过返回 1 验证失败返回 0
                "error" => "验证失败"   // 错误信息
                $data   => array(
                    "out_trade_no"      => '',      // 订单号
                    "cash_fee"          => "",      // 支付金额 单位分
                    "fee_type"          => "",      // 货币类型 
                    "openid"            => ""       // 微信id 或 支付宝账号
                    "return_code"       => ""       // 支付状态 SUCCESS
                    "transaction_id"    => ""       // 支付订单号
                    "bank_type"         => ""       // 银行名称
                    "time_end"          => ""       // 支付完成时间
                    "is_subscribe"      => ""       // 用户是否关注公众账号，Y-关注，N-未关注，仅在公众账号类型支付有效 
                
                )
            )
    */
    public function verifyReturn($kind = ""){  

        $kind  = trim($kind) == "" ? "Default" : trim($kind);

        // 加载文件 
        require_once('SDK/alipay/lib/alipay_core.function.php');
        require_once('SDK/alipay/lib/alipay_rsa.function.php');
        require_once('SDK/alipay/lib/alipay_notify.class.php');
        require_once('SDK/alipay/lib/alipay_submit.class.php');  

        require_once('Conf/alipay_' . $kind .'.config.php');


        $alipayNotify = new \AlipayNotify($alipay_config);      //计算得出通知验证结果
        $verify_result = $alipayNotify->verifyReturn();  

         // 返回数据处理
        if(count($verify_result["data"]) > 0 ){

            $arr = $verify_result["data"];
            $data = array(
                "out_trade_no"      => $arr['out_trade_no'],        // 订单号
                "cash_fee"          => $arr['total_fee'] * 100,     // 支付金额 单位分
                "fee_type"          => "CNY",                       // 货币类型 CNY 人民币
                "openid"            => $arr['buyer_logon_id'],         // 微信id 或 支付宝账号
                "return_code"       => $arr['trade_status'] == "TRADE_SUCCESS" ? "SUCCESS" : $arr['trade_status'],         // 支付状态 SUCCESS
                "transaction_id"    => $arr['trade_no'],            // 支付订单号
                "bank_type"         => "",                          // 银行名称
                "time_end"          => $arr['notify_time'],         // 支付完成时间
                "is_subscribe"      => "N"                          // 用户是否关注公众账号，Y-关注，N-未关注，仅在公众账号类型支付有效 

            );

            $verify_result["data"] = $data;

        }
        return $verify_result;
    }


     // 操作成功 并 打印 success
    public function setSuccess(){

        echo "success";
    }   
    
    // 验证失败 并 希望再次回调 打印 fail
    public function setFail(){

        echo "fail";
    } 
}
