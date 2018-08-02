<?php
/**
 * 充值
 */
namespace Api\Controller;
use Think\Controller\HproseController;
class TopupController extends HproseController {
    protected $crossDomain =    true;
    protected $P3P         =    true;
    protected $get         =    true;
    protected $debug       =    true;

    private function num_to_change($n){
        $num = floatval($n) * 1000;
        $str = substr($num,(strlen($num)-1),1);

        if($str > 0){
            $num = floatval($num) - floatval($str);
            $num = floatval($num) + 10;
            $num = sprintf("%.2f", floatval($num)/1000);
            return $num;
        }else{
            return sprintf("%.2f", floatval($n));
        }
    }
    /**
     * 保存充值记录
     * @param [type] $arr      [数据数组]
     * @param [type] $UID      [用户ID]
     * @param [type] $Pay_Kind [支付方式数组]
     * @param [type] $rate     [汇率]
     */
    public function addRecord($arr,$UID,$Pay_Kind,$rate){

        //验证支付方式是否符合我方物流后台定义的方式
        if(isset($arr['paykind']) && $Pay_Kind[$arr['paykind']] != '1'){
            return array('state'=>'no','msg'=>'该支付方式尚未开通','code'=>'pay_way_not_exist');
        }
    	//检查用户是否存在
        $check = M('UserList')->where(array('id'=>$arr['UID']))->find();
    	if(!$check){
    		return array('state'=>'no','msg'=>'用户不存在','code'=>'user_not_exist');
    	}
    	
        // 验证金额是否为数字
        if(!is_numeric($arr['amount_usa'])){
            return array('state'=>'no', 'msg'=>'请输入合法数字','code'=>'enter_number');
        }

        //验证金额格式
        $chemon = "/^\d+(\.{0,1}\d+){0,1}$/";
        if(!preg_match($chemon,$arr['amount_usa'])){
            return array('state'=>'no', 'msg'=>'输入金额格式有误，请输入数字','code'=>'enter_wrong_format');
        }

        // $count = floatval($arr['amount_usa']) * floatval($rate);

        //根据汇率计算出人民币金额，且判断小数点第三位是否大于0，若是，则进一，否则不变
        $count = $this->num_to_change(floatval($arr['amount_usa']) * floatval($rate));

        $order_no = StrOrderOne($UID);  //获取订单号

        $arr['amount']           = $count;//保存 根据汇率计算后的人民币金额
        $arr['order_no']         = $order_no;//保存订单号到数组以便保存都数据库
        $arr['user_balance_usa'] = '0'; //充值后的账户余额 由于只是生成记录，暂未充值成功，所以默认为0

    	$pid = M('RechargeRecord')->add($arr); //订单ID

        return array('state'=>'yes', 'order_no'=>$order_no);

/*        //生成日志
        $xmlsave = API_ABS_FILE.'/alipay/';
        $file_name = 'request'.'_'.$data['out_trade_no'].'_'.time().'.txt';  //文件名

        $content = "======== Result =========\r\n\r\n".json_encode($data);

        if(!is_dir($xmlsave)) mkdir($xmlsave, 0777, true);//第三个参数设置true 设置递归模式自动创建必需的目录文件夹

        file_put_contents($xmlsave.$file_name, $content);
        //生成日志 end*/

    }

    /**
     * 根据内部订单号查询并返回 跳转到(支付宝/微信)二维码扫描支付 所需的数据
     * @param  [type] $order_no [我方内部订单号]
     * @return [type]           [description]
     */
    public function _recharge($order_no){
        $info = M('RechargeRecord')->where(array('order_no'=>$order_no))->find();  //根据此随机码找出一条匹配的数据

        //检验订单是否存在
        if(!$info){
            return false;
        }

        //检查订单是否已经支付或支付中，如果是，则终止界面显示并跳转页面
        if($info['pay_state'] == '200' || $info['pay_state'] == '100'){
            return false;
        }

/*      由客户端的控制器生成这个组合
        //生成请求数组
        $data = array();
        $data['total_fee']    = floatval($info['amount']) * 100;  // 支付金额 单位分 因此 x 100
        $data['body']         = "账户充值";             // 支付内容
        $data['out_trade_no'] = $order_no;              // 订单号
        $data['show_url']     = "";                     // 商品展示地址（可为空）
        $data['subject']      = "充值";                 // 支付标题
        $data['product_id']   = $info['id'];            // 产品id 微信支付必填 支付宝支付可为空*/

        return $info;//返回资料

    }

    /**
     * 跳转到(支付宝/微信)二维码扫描支付 查询订单号对应的充值状态
     * @return [type] [description]
     */
    public function _check_order($order_no){
        $info = M('RechargeRecord')->where(array('order_no'=>$order_no))->find();

        return $info;
    }
}