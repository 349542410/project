<?php
/**
 * 微信二维码充值
 */

namespace AUApi\Controller;

use Think\Controller\HproseController;

class WxCodepayController extends HproseController
{
    protected $crossDomain = true;
    protected $P3P = true;
    protected $get = true;
    protected $debug = true;

    //判断小数点第三位是否大于0，若是，则进一，否则不变
    private function num_to_change($n)
    {
        $num = floatval($n) * 1000;
        $str = substr($num, (strlen($num) - 1), 1);

        if ($str > 0) {
            $num = floatval($num) - floatval($str);
            $num = floatval($num) + 10;
            $num = sprintf("%.2f", floatval($num) / 1000);
            return $num;
        } else {
            return sprintf("%.2f", floatval($n));
        }
    }

    public function set_order($user_id, $price, $rate)
    {
        //检查用户是否存在
        $check_user = M('UserList')->where(array('id' => $user_id))->find();
        if (!$check_user) {
            return array('state' => 'no', 'msg' => '用户不存在', 'lng' => 'user_not_exist');
        }
        //会员资料，审核状态检查
        if ($check_user['step'] < 5) {
            return array('state' => 'no', 'msg' => '该会员账户资料尚未完善', 'lng' => 'info_not_perfect');
        }
        if ($check_user['status'] == 0) {
            return array('state' => 'no', 'msg' => '该会员账户未审核通过', 'lng' => 'must_examine');
        } else {
            if ($check_user['status'] > 1) {
                return array(
                    'state' => 'no',
                    'msg' => '该会员账户资料审核不通过，请登录官网完善资料再次审核',
                    'lng' => 'not_examine_need_perfect'
                );
            }
        }

        $order_no = StrOrderOne($user_id);  //新建订单号

// return array('state'=>'no','msg'=>'用户不存在','user_id'=>$user_id,'price'=>$price,'rate'=>$rate);
        //根据汇率计算出人民币金额，且判断小数点第三位是否大于0，若是，则进一，否则不变
        $rmb = $this->num_to_change(floatval($price) * floatval($rate));

        $arr = array();
        $arr['UID'] = $user_id;
        $arr['amount'] = $rmb;  // 根据汇率计算后的人民币金额
        $arr['amount_usa'] = $price;   //充值金额 美元
        $arr['paykind'] = 'wechat';  //默认微信支付
        $arr['ordertime'] = date("Y-m-d H:i:s");
        $arr['order_no'] = $order_no; //内部订单号
        $arr['user_balance_usa'] = '0'; //充值后的账户余额  由于只是生成记录，暂未充值成功，所以默认为0  20171017

        $add_order = M('RechargeRecord')->add($arr); //订单ID

        if ($add_order) {

            $parameter = array(
                "out_trade_no" => $order_no,           //订单号
                "subject" => "账户充值",          //主题
                "total_fee" => round(floatval($rmb) * 100),   // 充值金额  单位分
                "body" => "充值",              //描述
                "product_id" => $add_order,          // 产品id  微信支付必填
                "show_url" => "",                  //商品展示地址（可为空）
            );

            return array('state' => 'yes', 'order_no' => $order_no, 'parameter' => $parameter, 'rmb' => $rmb);
        } else {
            return array('state' => 'no', 'msg' => '订单创建失败', 'lng' => 'create_order_failed');
        }
    }

    /**
     * 跳转到(微信)二维码扫描支付 查询订单号对应的充值状态
     * @return [type] [description]
     */
    public function _check_order($order_no)
    {
        $info = M('RechargeRecord')->field('amount_usa,payno,paytime,pay_state')->where(array('order_no' => $order_no))->find();
        if (!$info) {
            return array('state' => 'no', 'msg' => '订单号不存在', 'lng' => 'order_no_not_exist');
        } else {
            return array('state' => 'yes', 'info' => $info, 'wait_time' => '60');
        }

    }


}