<?php
/* 微信支付完成后的回调验证 */
namespace Org\Wxpay;
class Notify{    
    /*

    返回数据
    array(
        "code"  => 1|0,     // 1为成功 0为失败
        "error" => "",      // 失败原因
        "data"  => array(   // 成功时返回
            "out_trade_no"      => '',      // 订单号
            "cash_fee"          => "",      // 支付金额 单位分
            "fee_type"          => "",      // 货币类型 
            "openid"            => ""       // 微信id 或 支付宝账号
            "return_code"       => ""       // 支付状态 SUCCESS
            "transaction_id"    => ""       // 支付订单号
            "bank_type"         => ""       // 银行名称
            "time_end"          => ""       // 支付完成时间 格式为yyyy-MM-dd HH:mm:ss
            "is_subscribe"      => ""       // 用户是否关注公众账号，Y-关注，N-未关注，仅在公众账号类型支付有效 
        )

    )

    */
    public function verifyNotify($kind = ""){


        $kind  = trim($kind) == "" ? "Default" : trim($kind);
        require_once('Conf/WxPay_'.$kind.'.Config.php');
        require_once('SDK/Wxpay/lib/WxPay.Api.php');
        require_once('SDK/Wxpay/lib/WxPay.Notify.php');
        require_once('SDK/Wxpay/example/log.php');
        require_once('SDK/Wxpay/example/notify.php');


        $notify = new \PayNotifyCallBack();
        $returnValues = $notify->Handle(false);


        // 返回数据处理
        if(count($returnValues["data"]) > 0 ){

            $arr = $returnValues["data"];
            $data = array(
                "out_trade_no"      => $arr['out_trade_no'],        // 订单号
                "cash_fee"          => $arr['cash_fee'],            // 支付金额 单位分
                "fee_type"          => $arr['fee_type'],            // 货币类型 
                "openid"            => $arr['openid'],              // 微信id 或 支付宝账号
                "return_code"       => $arr['return_code'],         // 支付状态 SUCCESS
                "transaction_id"    => $arr['transaction_id'],      // 支付订单号
                "bank_type"         => $arr['bank_type'],           // 银行名称
                "time_end"          => date("Y-m-d H:i:s",strtotime($arr['time_end'])),            // 支付完成时间 格式为yyyy-MM-dd HH:mm:ss
                "is_subscribe"      => $arr['is_subscribe']         // 用户是否关注公众账号，Y-关注，N-未关注，仅在公众账号类型支付有效 

            );

            $returnValues["data"] = $data;

        }

        return $returnValues;
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
