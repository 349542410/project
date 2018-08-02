<?php
/* 支付宝预支付签证处理 */
namespace Org\Wxpay;

class Pay {


    /** PC 扫码支付
    **  请求参数 kind = "配置文件名", data = array(         
            "out_trade_no"      => "2017" . time(),     // 订单号
            "subject"           => "测试",              // 只回复标题
            "total_fee"         => "1",                 // 金额 单位分
            "body"              => "测试数据",                  
            "product_id"        => "312",           // 产品id
        );
    *   返回数据
        
    *
    */
    public function NativePay($kind = "" , $data = array())
    {   


       // 加载文件
        $kind  = trim($kind) == "" ? "Default" : trim($kind);

        require_once('Conf/WxPay_'.$kind.'.Config.php');
        require_once('SDK/Wxpay/lib/WxPay.Api.php');
        require_once('SDK/Wxpay/example/WxPay.NativePay.php');   
        
        $body           = $data['body'];
        $out_trade_no   = $data['out_trade_no'];
        $subject        = $data['subject'];
        $total_fee      = $data['total_fee'];
        $product_id     = $data['product_id'];



        $input = new \WxPayUnifiedOrder();
        $input->SetBody($body);
        $input->SetAttach($subject);
        $input->SetOut_trade_no($out_trade_no);
        $input->SetTotal_fee($total_fee);
        $input->SetTime_start(date("YmdHis"));
        $input->SetTime_expire(date("YmdHis", time() + 600));       // 二维码过期时间600s
        $input->SetGoods_tag("");
        $input->SetTrade_type("NATIVE");
        $input->SetProduct_id($product_id);
        $notify = new \NativePay();
        $result = $notify->GetPayUrl($input);

        $code_url = $result["code_url"];
        $code = '<img alt="扫码支付" src="http://paysdk.weixin.qq.com/example/qrcode.php?data='. urlencode($code_url).'" style="width:150px;height:150px;"/>';

        return $code;
    }

   
}
