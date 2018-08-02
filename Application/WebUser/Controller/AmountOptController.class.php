<?php

    /*
    *   金额
    *   liao ya di
    *   create time : 2017-12-26
    */

    namespace WebUser\Controller;

    class AmountOptController extends BaseController{

        private $a_client;

        private $ex_status = array(
            '0' => '未审核',
            '1' => '待确认',
            '2' => '已完成',
            '3' => '已取消',
        );

        private $_mode = array(
            'alipay' => 1,
        );

        public function __construct(){

            parent::__construct();
            parent::_initialize();
            vendor('Hprose.HproseHttpClient');
            $this->a_client = new \HproseHttpClient(C('RAPIURL').'/AmountOpt');

        }


        //生成签名
        private function setSign(){
            // return date("Y-m-d H:i:s",time());
            $sign = md5( date("Y-m-d H:i:s",time()) . "/" . session('user_id') . '/AmountOpt' );
            session('amount_sign',$sign);
            return $sign;
        }

        //删除签名
        public function delSign(){
            session('amount_sign',null);
        }

        //验证签名
        private function checkSign($sign){
            $res = (session('amount_sign') === $sign) ? true : false;
            $this->delSign();
            return $res;
        }

        //ajax获取签名
        public function getSignAjax(){
            echo $this->setSign();
        }


        
        //检测提现申请是否符合要求 - 业务逻辑验证
        public function check($amount,$freeze_amount){
            if(!is_numeric($amount)){
                return array(
                    'success' => false,
                    'errinfo' => '金额必须为数值',
                );
            }
            // if(intval($amount)>1000){
            //     return array(
            //         'success' => false,
            //         'errinfo' => '金额必须小于1000',
            //     );
            // }

            //提现金额和冻结金额都必须大于余额
            $now_amount = $this->a_client->getAmount(session('user_id'))['amount'];
            if($amount>$now_amount || $freeze_amount>$now_amount){
                return array(
                    'success' => false,
                    'errinfo' => '余额不足',
                );
            }

            //一个用户只能同时有一条待审核的提现记录
            $qure = $this->query_record();
            if(!$qure['success']){
                return $qure;
            }
            if(!empty($qure['data'])){
                foreach($qure['data'] as $k=>$v){
                    if($v['examine_status'] != 2 && $v['examine_status'] != 3){
                        return array(
                            'success' => false,
                            'errinfo' => '一个用户只能同时有一条提现记录',
                        );
                    }
                }
            }

            //验证成功
            return array(
                'success' => true,
                'errinfo' => '',
            );
        }

        //计算手续费
        private function get_service_charge($amount){
            return 0;
            // $sc = intval($amount / 100 * 5);
            // return (($sc<5) ? 5 : $sc);
        }

        //计算冻结金额
        private function get_freeze_amount($amount){
            // return $amount+1000;
            // return round((float)$amount,2);
            return sprintf("%.2f",$amount + $this->get_service_charge($amount));
            // return \round($amount + $this->get_service_charge($amount),2);
            // return \round($amount + $this->get_service_charge($amount),2);
        }

        //获取当前余额
        public function get_amount_ajax(){
            $user_id = session('user_id');
            echo json_encode($this->a_client->getAmount($user_id));
        }

        //ajax获取手续费
        public function get_sc_ajax(){
            $amount = I('get.amount');
            if(!empty($amount)){
                echo $this->get_service_charge($amount);
            }
            die;
        }

        //ajax获取当前未完成的提现记录
        public function get_ao_ajax(){
            $where['_string'] = 'b.examine_status <> 2 AND b.examine_status <> 3';
            $res = $this->query_record($where);
            $res['empty'] = true;
            if($res['success'] && !empty($res['data'])){
                $res['empty'] = false;
                $res['data'] = $res['data'][0];
                $res['data']['examine_status'] = $this->ex_status[$res['data']['examine_status']];
                if(!empty($res['data']['examine_time']) && $res['data']['examine_time']==='0000-00-00 00:00:00'){
                    // $res['data']['examine_time'] = '————';
                    // $res['data']['examine_time'] = str_replace('0','X',$res['data']['examine_time']);
                    // unset($res['data']['examine_time']);
                    $res['data']['examine_time'] = false;
                }
            }
            
            echo json_encode($res);
            die;
        }

        public function create_lock(){
            $path = dirname(__file__) . '/../Common/write.lock';
            if(!file_exists($path)){
                if(!($fp = fopen($path, 'w'))){
                    dump(false);
                    return;
                }
            }
            dump(true);
            return;
        }

        //提现
        public function withdraw_money(){
            $path = dirname(__file__) . '/../Common/write.lock';
            if(!($fp = fopen($path,'r'))){
                return;
            }
            //加锁
            flock($fp,LOCK_EX);


            $user_id = session('user_id');
            $amount = trim(I('post.amount_count'));     //申请金额
            $sign = I('post.sign');                     //签名
            // $mode = I('post.mode');                     //支付模式
            $mode = 0;                     //支付模式
            $freeze_amount = $this->get_freeze_amount($amount);     //获取冻结金额
            $service_charge = $this->get_service_charge($amount);   //获取手续费


            //验证签名
            if(!$this->checkSign($sign)){
                echo json_encode(array(
                    'success' => false,
                    'errinfo' => '签名验证失败',
                ));
                die;
            };
            
            //检测提现申请是否符合要求
            $sign = $this->check($amount,$freeze_amount);
            if(!$sign['success']){
                echo json_encode($sign);
                die;
            }

            //验证支付方式
            // if(empty($this->_mode[$mode])){
            //     echo json_encode(array(
            //         'success' => false,
            //         'errinfo' => '不支持的支付方式',
            //     ));
            //     die;
            // }


            //开始执行提现操作
            $data = array(
                'user_id' => $user_id,
                'mode' => $this->_mode[$mode],
                'request_amount' => $amount,
                'freeze_amount' => $freeze_amount,
                'service_charge' => $service_charge,
            );
            foreach($data as $k=>$v){
                $data[$k] = strval($v);
            }

            // dump($data);
            // die;

            $res = $this->a_client->take_money($data);
            if(!$res['success'] && !empty($res['errarr'])){
                $res['errinfo'] = '[' . L($res['errarr'][0]) . ']: ' .  L($res['errarr'][1]);
            }
            echo json_encode($res);
            die;



            //释放锁
            flock($fp,LOCK_UN);
            fclose($fp);

        }

        //查询提现申请记录
        public function query_record($where=array(),$limit=""){
            $user_id = session('user_id');
            return $this->a_client->query_record($user_id,$where,$limit);
        }


        public function show_record(){

            //每页显示的条数
            define('PAGE_NO',12);

            //显示的分页数量
            define('ROLL_PAGE',15);

            //获取总数量
            $result = $this->query_record();
            $count = count($result['data']);

            // if($_GET['p3']){
            //     $_GET['p'] = $_GET['p3'];
            // }
            // //如果请求分页大于最大分页数量，则跳转到最大分页，而不是给一个空白页面
            if(!empty($_GET['p'])&&$count<PAGE_NO*$_GET['p']){
                $_GET['p'] = ceil($count/PAGE_NO);
            }

            $Page = new \Think\Page($count,PAGE_NO);

            $Page->rollPage = ROLL_PAGE;
            $Page->setConfig('prev', L('PrevPage'));    //上一页
            $Page->setConfig('next', L('NextPage'));    //下一页
            $Page->setConfig('first', L('FirstPage'));  //第一页
            $Page->setConfig('last', L('LastPage'));    //最后一页
            $Page->setConfig('header', '<span class="rows">'.L('TotalPage',array('n'=>'%TOTAL_ROW%')).'</span>');
            $Page -> setConfig ( 'theme', '%FIRST% %UP_PAGE% %LINK_PAGE% %DOWN_PAGE% %END% %HEADER%' );
            $show = $Page->show();
            $list = $this->query_record(array(),$Page->firstRow.','.$Page->listRows);

            $mode_flip = array_flip($this->_mode);
            foreach($list['data'] as $k=>$v){
                $list['data'][$k]['examine_status'] = $this->ex_status[$v['examine_status']];
                $list['data'][$k]['mode'] = $mode_flip[$v['mode']];
            }

            // echo "<br /><br /><br /><br /><br /><br /><br /><br />";
            // dump($show);
            // dump($list['data']);
            $this->assign('page3',$show);
            $this->assign('tlist',$list['data']);

            $this->assign('US_TO_RMB_RATE',C('US_TO_RMB_RATE'));// 汇率
            $this->display("WebRecharge/index");

        }


        //取消提现
        public function cancel(){
            $id = I('get.id');
            if(empty($id)){
                echo json_encode(array(
                    'success' => false,
                    'errinfo' => '参数错误',
                ));
                die;
            }
            $where['a.id'] = $id;
            $res = $this->query_record($where,'')['data'][0];
            if(empty($res)){
                echo json_encode(array(
                    'success' => false,
                    'errinfo' => '未查询到数据',
                ));
                die;
            }
            if($res['examine_status'] != 0){
                if($res['examine_status'] == 3){
                    //已取消的订单无法再次取消
                    echo json_encode(array(
                        'success' => false,
                        'errinfo' => '操作失败',
                    ));
                    die;
                }else{
                    //审核通过的提现申请无法取消
                    echo json_encode(array(
                        'success' => false,
                        'errinfo' => '操作失败',
                    ));
                    die;
                }
            }

            $user_id = session('user_id');
            $result = $this->a_client->cancel($user_id,$id);

            echo json_encode($result);
            die;
        }



    }