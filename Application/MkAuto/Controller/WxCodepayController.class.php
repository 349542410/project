<?php
/**
 * 自助打印终端---微信二维码充值
 * 包含：充值，返回充值状态给终端
 */

namespace MkAuto\Controller;

class WxCodepayController extends PrintSysBaseController
{

    public function __construct()
    {
        parent::__construct();
    }

    public function index()
    {
        echo 'Hei';
    }

    public function getcode()
    {
        /*
        1.获取传来的金额，单位转为 分 后赋于total_fee
        2.生成待支付的记录到数据库中，以下out_trade_no为是订单号
        3.$verify_result为生成二维码内容
        */
        $json = (I('info')) ? trim(I('info')) : '';

        //验证是否 info 为空
        if ($json == '') {
            $result = array('state' => 'no', 'msg' => '缺少必要的相关资料', 'lng' => 'miss_parameter');
            $this->Language->get_lang($result);
            exit;
        }

        $arr = json_decode(urldecode(base64_decode($json)), true);

        if ($arr['type'] != 'wechatPay') {
            $result = array('state' => 'no', 'msg' => '指令校对失败', 'lng' => 'order_is_wrong');
            $this->Language->get_lang($result);
            exit;
        }

        //收到的数据
        $data = $arr['data'];

        $user_id = $data['user_id']; //用户ID
        $price = $data['price'];//充值金额  此充值金额单位是美元，需要转为人民币

        //检验用户id是否存在
        if (trim($user_id) == '') {
            $result = array('state' => 'no', 'msg' => '缺少用户字段', 'lng' => 'lack_user_id');
            $this->Language->get_lang($result);
            exit;
        }
        //验证充值金额是否为空或者等于0
        if (trim($price) == '' || floatval($price) == 0) {
            $result = array('state' => 'no', 'msg' => '充值金额必须填写', 'lng' => 'lack_price');
            $this->Language->get_lang($result);
            exit;
        }
        // 验证金额是否为数字
        if (!is_numeric($price)) {
            $result = array('state' => 'no', 'msg' => '充值金额必须为数字', 'lng' => 'enter_number');
            $this->Language->get_lang($result);
            exit;
        }
        //验证金额格式
        $chemon = "/^\d+(\.{0,1}\d+){0,1}$/";
        if (!preg_match($chemon, $price)) {
            $result = array('state' => 'no', 'msg' => '充值金额格式不正确', 'lng' => 'enter_wrong_format');
            $this->Language->get_lang($result);
            exit;
        }

        //根据人民币与美元之间的汇率进行换算， 汇率规则由config定义的
        $pay_price = (floatval(C('US_TO_RMB_RATE')) * floatval($price)) * 100;//精确到人民币的分

        // vendor('Hprose.HproseHttpClient');
        $client = new \HproseHttpClient(C('WAPIURL') . '/WxCodepay');
        //判断是否测试用户充值
        if(in_array($this->tokenID, explode(',', C('PAY_TEST_USER_ID')))){
            $exchangeRate = '0.01';
        }else{
            $exchangeRate = C('US_TO_RMB_RATE');
        }
        $order = $client->set_order($user_id, $price, $exchangeRate);

        if ($order['state'] == 'no') {
            $this->Language->get_lang($order);
            exit;
        }

        $parameter = $order['parameter'];

        // //测试数据
        // $parameter = array(

        //     "out_trade_no"      => "2017" . time(),
        //     "subject"           => "测试",
        //     "total_fee"         => "1",                 // 单位分
        //     "body"              => "测试数据",
        //     "product_id"        => "312",           // 产品id
        //     "show_url"          => "",
        // );

        $pay = new \Org\Wxpay\Pay();
        $verify_result = $pay->NativePay("MkilCode", $parameter, 2); //生成微信的二维码信息
        // echo $verify_result;

        $rdata = array(
            'verify_result' => $verify_result,
            'user_id' => $user_id,
            'price' => $price,
            'order_no' => $order['order_no'],
            'rmb' => $order['rmb'],
            'out_time' => '60'
        );

        $result = array('state' => 'yes', 'rdata' => $rdata);
        $this->Language->get_lang($result);
        exit;
    }

    /**
     * 前端js每三秒发送请求 检测 支付是否成功
     * @return [type] [description]
     */
    public function check_order()
    {
        $json = (I('info')) ? trim(I('info')) : '';
        //验证是否 info 为空
        if ($json == '') {
            $result = array('state' => 'no', 'msg' => '缺少必要的相关资料', 'lng' => 'miss_parameter');
            $this->Language->get_lang($result);
            exit;
        }

        $arr = json_decode(urldecode(base64_decode($json)), true);

        if ($arr['type'] != 'wechatState') {
            $result = array('state' => 'no', 'msg' => '指令校对失败', 'lng' => 'order_is_wrong');
            $this->Language->get_lang($result);
            exit;
        }

        $order_no = trim($arr['data']['order_no']);

        if ($order_no != '') {
            // vendor('Hprose.HproseHttpClient');
            $client = new \HproseHttpClient(C('WAPIURL') . '/WxCodepay');
            $result = $client->_check_order($order_no);

            $this->Language->get_lang($result);
            exit;

        }
    }

}