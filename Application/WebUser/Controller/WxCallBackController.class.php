<?php
/**
 * 微信充值 回调数据处理
 */

namespace WebUser\Controller;

use Think\Controller;
use Think\Think;
use Think\Log;

class WxCallBackController extends BaseController
{

    //回调查询支付状态
    public function callback222222()
    {
        echo time();

        //生成日志
        $xmlsave = WU_ABS_FILE . '/wxpay/';
        $file_name = '2017wxpay_' . time() . '.txt';    //文件名

        $content = "======== 回调结果 =========\r\n\r\n";//.json_encode($verify_result);

        if (!is_dir($xmlsave)) {
            mkdir($xmlsave, 0777, true);
        }//第三个参数设置true 设置递归模式自动创建必需的目录文件夹

        file_put_contents($xmlsave . $file_name, $content);
    }

    //回调结果的处理
    public function callback()
    {
        \Think\Log::write('微信回调成功');
        \Think\Log::write('微信回调信息' . json_encode($_REQUEST, 320));
       /*$data = [
             'out_trade_no' => '4393240350143',
             'return_code' => 'SUCCESS',
             'cash_fee' => 1,
         ];
         vendor('Hprose.HproseHttpClient');
         $Wclient = new \HproseHttpClient(C('WAPIURL').'/PaybackPc');
         $code = $Wclient->console($data);//锁定订单状态为  支付中
         dump($code);
         exit();*/
        $notify = new \Org\Wxpay\Notify();
        $verify_result = $notify->verifyNotify("MkilCode");
        \Think\Log::write('微信接收信息' . json_encode($verify_result, 320));
        //\Think\Log::write('回调结果' . json_encode($verify_result, 320));
// 模拟返回结果
// $verify_result = '{
//     "code": 1,
//     "error": "回调成功",
//     "data": {
//         "out_trade_no": "21488212202",
//         "cash_fee": "10200",
//         "fee_type": "CNY",
//         "openid": "o9COGwJgjxQfuZQiWWXemrjqWb3w",
//         "return_code": "SUCCESS",
//         "transaction_id": "4008812001201709060483110893",
//         "bank_type": "CFT",
//         "time_end": "2017-09-06 09:13:58",
//         "is_subscribe": "Y"
//     }
// }';
// $verify_result = json_decode($verify_result, true);
        //生成日志
        if ($verify_result['code'] == 1) {
            vendor('Hprose.HproseHttpClient');
            $Wclient = new \HproseHttpClient(C('WAPIURL') . '/PaybackPc');
            // 成功则保存数据库
            $data = $verify_result['data'];
            $code = $Wclient->console($data);//锁定订单状态为  支付中
            \Think\Log::write('支付中' . json_encode($code, 320));
            if ($code['state'] == 'no') {
                // paid 订单已经支付成功
                if ($code['msg'] == 'paid') {
                    $notify->setSuccess();
                    exit;//如果所有操作成功
                } else {
                    $notify->setFail();
                    exit;
                }
            } else {
                $notify->setSuccess();
                exit;//如果所有操作成功
            }
        } else {
            //验证失败，再次回调
            $notify->setFail();
            exit;
        }
    }
}