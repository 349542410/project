<?php
/**
 * 微信二维码充值
 */
namespace WebUser\Controller;
use Think\Controller;
class WxCodepayController extends PrintSysController {

    public function _initialize(){

        $mkuser = session('appuser');

        //检查用户是否登录，登录通行证验证
        if(!$mkuser || $mkuser['isLoged'] != md5(md5('passed'))){

            vendor('Hprose.HproseHttpClient');
            $client = new \HproseHttpClient(C('RAPIURL').'/PrintSysLogin');

            $header = $this->get_all_headers();
            $token = $header['token'];

            //检查登录状态
            $is_login = $client->_is_login($token);

            //检查登录状态
            if(!$is_login || $is_login['status'] != '200' || (time()-intval($is_login['time_out'])) > 3600){
                $result = array('state' => 'noLogin', 'msg'=>'未登陆或登录超时', 'lng'=>'login_timeout');
                echo json_encode($result);exit;
            }else{

                //刷新登录状态和时间
                $client->hold_login($token);

                $author = array(
                    'uid'      => $is_login['id'],          //登入的id值
                    'isLoged'  => md5(md5('passed')),
                );
                // $this->tokenID = $is_login['user_id'];
                session('appuser',$author); //session赋值
            }
        }

    }

	public function index()
	{
		echo 'Hei';
	}

	public function getcode()
	{
		/*	
		1.获取传来的金额，转为分后赋于total_fee
		2.生成待支付的记录到数据库中，以下out_trade_no为是订单号
		3.$verify_result为生成二维码内容
		*/
        $json = (I('info')) ? trim(I('info')) : '';
        //验证是否 info 为空
        if($json == ''){
            $result = array('state'=>'no','msg'=>'缺少必要的相关资料', 'lng'=>'miss_parameter');
            echo json_encode($result);exit;
        }

        $info = json_decode(urldecode(base64_decode($json)),true);

        if($info['type'] != 'wechatPay'){
            $result = array('state'=>'no','msg'=>'指令校对失败', 'lng'=>'order_is_wrong');
            echo json_encode($result);exit;
        }

        //收到的数据
        $data = $info['data'];

        $user_id = $data['user_id']; //用户ID
        $price   = $data['price'];//充值金额  此充值金额单位是美元，需要转为人民币

        //检验用户id是否存在
        if(trim($user_id) == ''){
            $result = array('state'=>'no','msg'=>'缺少用户字段', 'lng'=>'lack_user_id');
            echo json_encode($result);exit;
        }
        //验证充值金额是否为空或者等于0
        if(trim($price) == '' || floatval($price) == 0){
            $result = array('state'=>'no','msg'=>'充值金额必须填写', 'lng'=>'lack_price');
            echo json_encode($result);exit;
        }
        // 验证金额是否为数字
        if(!is_numeric($price)){
            $result = array('state'=>'no','msg'=>'充值金额必须为数字', 'lng'=>'enter_number');
            echo json_encode($result);exit;
        }
        //验证金额格式
        $chemon = "/^\d+(\.{0,1}\d+){0,1}$/";
        if(!preg_match($chemon,$price)){
            $result = array('state'=>'no','msg'=>'充值金额格式不正确', 'lng'=>'enter_wrong_format');
            echo json_encode($result);exit;
        }

        //根据人民币与美元之间的汇率进行换算， 汇率规则由config定义的
        $pay_price = (floatval(C('US_TO_RMB_RATE')) * floatval($price)) * 100;//精确到人民币的分

        vendor('Hprose.HproseHttpClient');
        $client = new \HproseHttpClient(C('WAPIURL').'/WxCodepay');

        $order = $client->set_order($user_id, $price, C('US_TO_RMB_RATE'));

        // //测试数据
        // $parameter = array(
            
        //     "out_trade_no"      => "2017" . time(),
        //     "subject"           => "测试",
        //     "total_fee"         => "1",                 // 单位分
        //     "body"              => "测试数据",
        //     "product_id"        => "312",           // 产品id
        //     "show_url"          => "",
        // );

        if($order['state'] == 'no'){
            echo json_encode($order);exit;
        }

        $parameter = $order['parameter'];

        $pay = new \Org\Wxpay\Pay();
        $verify_result = $pay->NativePay("MkilCode" , $parameter,2); //生成微信的二维码信息
        // echo $verify_result;

        $rdata  = array('verify_result'=>$verify_result, 'user_id'=>$user_id, 'price'=>$price, 'order_no'=>$order['order_no'], 'rmb'=>$order['rmb'],'out_time'=>'60');

        $result = array('state'=>'yes','rdata'=>$rdata);
        echo json_encode($result);exit;
	}

    //回调查询支付状态   此方法已转移到WxCallBackController
	public function callback()
	{
		$notify = new \Org\Wxpay\Notify();
        $verify_result = $notify->verifyNotify("MkilCode");

        //生成日志
        $xmlsave = API_ABS_FILE.'/wxpay/';
        $file_name = '2017wxpay_'.time().'.txt';    //文件名

        $content = "======== 回调结果 =========\r\n\r\n".json_encode($verify_result);

        if(!is_dir($xmlsave)) mkdir($xmlsave, 0777, true);//第三个参数设置true 设置递归模式自动创建必需的目录文件夹

        file_put_contents($xmlsave.$file_name, $content);
        //生成日志 end

        if($verify_result['code'] == 1){
            vendor('Hprose.HproseHttpClient');
            $Wclient = new \HproseHttpClient(C('WAPIURL').'/PaybackPc');

            // 成功则保存数据库
            $data = $verify_result['data'];

            $code = $Wclient->holdState($data);//锁定订单状态为  支付中

            if($code == 'locked'){
                $notify->setFail();//订单正在锁定处于支付中，希望再次回调
            }else if($code == 'paid'){
                $notify->setSuccess();//订单已经成功支付，终止回调
            }

            //支付状态 SUCCESS
            if($data['return_code'] == 'SUCCESS'){

                if(trim($data['out_trade_no']) != ''){

                    $res = $Wclient->verify($data);

                    if($res == true){
                        $notify->setSuccess();//如果所有操作成功
                    }else{
                        $notify->setFail();//如果操作错误，希望再次回调
                    }

                }else{
                    $code = $Wclient->restore($data);//解锁订单状态还原为 待支付
                    $notify->setFail();//如果操作错误，希望再次回调
                }

            }else{
                $code = $Wclient->restore($data);//解锁订单状态还原为 待支付
                $notify->setFail();//如果操作错误，希望再次回调
            }

            // 操作完成则打印
            // $notify->setSuccess();  
        }else{

            //验证失败，再次回调
            $notify->setFail();
        }
	}

    /**
     * 前端js每三秒发送请求 检测 支付是否成功
     * @return [type] [description]
     */
    public function check_order(){
        $json = (I('info')) ? trim(I('info')) : '';
        //验证是否 info 为空
        if($json == ''){
            $result = array('state'=>'no','msg'=>'缺少必要的相关资料', 'lng'=>'miss_parameter');
            echo json_encode($result);exit;
        }

        $info = json_decode(urldecode(base64_decode($json)),true);

        if($info['type'] != 'wechatState'){
            $result = array('state'=>'no','msg'=>'指令校对失败', 'lng'=>'order_is_wrong');
            echo json_encode($result);exit;
        }

        $order_no = trim($info['data']['order_no']);

        if($order_no != ''){
            vendor('Hprose.HproseHttpClient');
            $client = new \HproseHttpClient(C('WAPIURL').'/WxCodepay');
            $res = $client->_check_order($order_no);

            echo json_encode($res);exit;

        }
    }

    /**
     * 获取自定义的header数据
     */
    private function get_all_headers(){

        // 忽略获取的header数据
        $ignore = array('host','accept','content-length','content-type');

        $headers = array();

        foreach($_SERVER as $key=>$value){
            if(substr($key, 0, 5)==='HTTP_'){
                $key = substr($key, 5);
                $key = str_replace('_', ' ', $key);
                $key = str_replace(' ', '-', $key);
                $key = strtolower($key);

                if(!in_array($key, $ignore)){
                    $headers[$key] = $value;
                }
            }
        }

        return $headers;
    }
}