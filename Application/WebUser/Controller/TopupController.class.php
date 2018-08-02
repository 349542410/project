<?php
/**
 * 充值页面
 */
namespace WebUser\Controller;
use Think\Controller;
class TopupController extends BaseController {

    public function _initialize() {
        parent::_initialize();
        $Wclient = new \HproseHttpClient(C('WAPIURL').'/Topup');      //增删改操作
        $this->Wclient = $Wclient;
    }

    /**
     * 提交表单数据之前，先验证数据 
     */
    public function checkForm(){
        if(IS_POST){
            //用数组装载post过来的所有数据
            $checkArr = I('post.');
            $this->checkInfo($checkArr);

            //开始处理数据
            $UID     = session('mkuser.uid');  //登录用户ID
            $amount  = trim(I('post.amount'));   //支付金额
            $paykind = trim(I('post.paykind')); //支付方式

            $arr = array();
            $arr['amount_usa'] = $amount;   //充值金额 美元
            $arr['paykind']    = $paykind;
            $arr['UID']        = $UID;
            $arr['ordertime']  = date("Y-m-d H:i:s");

            //检验通过后保存充值订单到数据库
            $Wclient = $this->Wclient;
            $res = $Wclient->addRecord($arr,$UID,C('Pay_Kind'),C('US_TO_RMB_RATE'));//提交订单后马上保存到数据库并生成数据数组返回, $data是 支付宝/微信 需要用的 数组
            // dump($res);die;
            if($res['state'] == 'no'){
                $res['msg'] = L($res['code']);
            }

            //通过ajax返回保存成功的订单信息到页面
            $this->ajaxReturn($res);
        }
    }

    /**
     * 专门用于检验数据
     * @param  [type] $arr    [description]
     * @param  [type] $amount [description]
     * @return [type]         [description]
     */
    public function checkInfo($arry){
        // $mkuser = session('mkuser');
        // //验证是否登录
        // if(!$mkuser || $mkuser['isLoged'] != md5(md5('passed'))){
        //     $result = array('state'=>'no', 'msg'=>L('not_login'));
        //     $this->ajaxReturn($result);
        // }

        /* 验证数据是否为空 */
        $chelist = array(
            'amount' => L('enter_amount'),
            'paykind'=>  L('choose_pay_way'),
        );
        
        foreach($chelist as $k=>$v){
            if(trim($arry[$k]) == ''){
                $result = array('state'=>'no', 'msg'=>$chelist[$k]);
                $this->ajaxReturn($result);
            }
        }

        // 验证金额是否为数字
        if(!is_numeric($arry['amount'])){
            $result = array('state'=>'no', 'msg'=>L('enter_number'));
            $this->ajaxReturn($result);
        }

        //验证金额格式
        $chemon = "/^\d+(\.{0,1}\d+){0,1}$/";
        if(!preg_match($chemon,$arry['amount'])){
            $result = array('state'=>'no', 'msg'=>L('enter_wrong_format'));
            $this->ajaxReturn($result);
        }
    }

    /**
     * 跳转到(支付宝/微信/paypal)二维码扫描支付 页面
     * @return [type] [description]
     */
    public function recharge(){
        //用数组装载post过来的所有数据
        $checkArr = I('post.');
        $order_no = trim(I('post.order_no'));
        $paykind  = trim(I('post.paykind')); //支付方式

        $this->checkInfo($checkArr); //处理数据之前，先进行数据校验

        $Wclient = $this->Wclient;
        $info = $Wclient->_recharge($order_no);//提交订单后马上保存到数据库并生成数据数组返回, $data是 支付宝/微信 需要用的 数组

        //微信支付只有一个二维码，因此界面需要自己编写
        switch ($paykind) {
            case 'wechat':
                $pay = new \Org\Wxpay\Pay();
                $verify_result = $pay->NativePay('MkilWxPc' , $data);
                // self::assign('pay',$pay);
                // self::assign('data',$data);
                
                // if($verify_result == ''){
                //     $this->redirect('Public/404');
                // }

                self::assign('order_no',$data['out_trade_no']);
                self::assign('verify_result',$verify_result);
                $this->display();//显示我方自己编写的微信支付界面
            break;

            default:
                $this->alipay($info, $order_no); //支付宝、paypal有自己的支付界面，直接调用即可
            break;
        }

    }

    /**
     * 调用支付宝支付界面
     */
    public function alipay($info, $order_no){
        //生成请求数组
        $data = array();
        $data['total_fee']    = floatval($info['amount']) * 100;  // 支付金额 单位分 因此 x 100
        $data['body']         = "账户充值";             // 支付内容
        $data['out_trade_no'] = $order_no;              // 订单号
        $data['show_url']     = "";                     // 商品展示地址（可为空）
        $data['subject']      = "充值";                 // 支付标题
        $data['product_id']   = $info['id'];            // 产品id 微信支付必填 支付宝支付可为空
//        dump($data);exit();
        $pay = new \Org\Alipay\Pay();
        $pay->Html5Pay("MkilAliPc" , $data); //调取支付界面

    }

    /**
     * paypal支付调用
     * @return [type] [description]
     */
    public function paypal($info, $order_no){
        $conf = '';
        $pay = new \Lib82\paypal\paytoken();
        $access_token = $pay->token($conf);     //$conf 为调用配置文件名称一部分  不存在则加载默认配置文件
        $access_token = array(
            'access_token'  => '3333333',//访问口令
            'expires_in'    => '1800',//存活时间, 单位秒
            
        );

        $data = array(
            "total_fee"         => floatval($info['amount_usa']) * 100,               // 支付金额 单位分    
            "out_trade_no"      => $order_no,     // 订单号
            "access_token"      => $access_token['access_token'],   //访问口令
            "return_url"        => 'http://payment.app.megao.hk:82/Paypalec/respond?out_trade_no='.$order_no,    //回调URL = 返回地址 + 订单号 
            "shipping_fee"      => '0', //航运费
            "goods_amount"      => '30000', //商品费用
            "tax"               => '200',   //税费
            "insurance"         => '0',     //保险费
            "handling_fee"      => '0',     //手续费
            "shipping_discount" => '0',     //航运折扣
        );  
        
        //调用接口
        $pay = new \Lib82\paypal\PaypalApi();
        $res = $pay->pay($conf, $data); 
    }

    /**
     * 前端js每三秒发送请求 检测 支付是否成功
     * @return [type] [description]
     */
    public function check_order(){
        $order_no = (I('post.order_no')) ? trim(I('post.order_no')) : '';

        if($order_no != ''){
            $Wclient = $this->Wclient;
            $res = $Wclient->_check_order($order_no);

            //只有支付成功才返回
            if($res['pay_state'] == '200'){
                $backArr = array('state'=>'200', 'url'=>U('WebRecharge/recharge_success',array('order_sn'=>$res['order_no'])));
                $this->ajaxReturn($backArr);
            }
        }
    }
}