<?php

    // 金额更新
    // liao ya di
    // create time : 2017-12-26
    // API CONTROLLER

    namespace Api\Controller;
    use Think\Controller\HproseController;

    class AmountOptController extends HproseController{

        public $rules = array(
		    'mode'              =>array(false,3),
            'request_amount'    =>array(true,8),
            'freeze_amount'     =>array(true,8),
		    'service_charge'    =>array(false,8),
        );

        //检测
        public function check_data($data,$rules){
            
            foreach($rules as $k=>$v){

                if($v[0] && empty($data[$k])){
                    //必须存在
                    return array(
                        'success' => false,
                        'error' => array($k,'l_empty'),
                    );
                }

                if(!empty($data[$k]) && strlen($data[$k])>$v[1]){
                    //长度不符合要求
                    return array(
                        'success' => false,
                        'error' => array($k,'l_max_len'),
                    );
                }
                
            }

            return array(
                'success' => true,
                'error' => '',
            );

        }

        //提现
        public function take_money($arguments){

            // return $arguments;
            $a_model = M('WithdrawCash');
            $data = $a_model->create($arguments);
            $user_id = $data['user_id'];


            //数据表字段验证
            if(empty($user_id)){
                return array(
                    'success' => false,
                    'errinfo' => '非法参数',
                );
            }
            $check_field = $this->check_data($data,$this->rules);
            if(!$check_field['success']){
                return array(
                    'success' => false,
                    'errarr' => $check_field['error'],
                    'errinfo' => '',
                );
            }

            // return $data;

            $model = M('');
            $model->startTrans();

            //减少用户余额 == 冻结金额
            $u_model = M('UserList');
            $u_res = $u_model->where(array('id'=>$user_id))->setDec('amount',$data['freeze_amount']);
            if(!$u_res){
                $model->rollback();
                return array(
                    'success' => false,
                    'errinfo' => $u_model->getError(),
                );
            }

            //添加申请记录
            $a_id = $a_model->add($data);
            if(!$a_id){
                $model->rollback();
                return array(
                    'success' => false,
                    'errinfo' => $a_model->getError(),
                );
            }

            //添加审核记录
            $l_data = array(
                'wc_id' => $a_id,
                'examine_status' => 0,
            );
            $l_model = M('WithdrawCashLogs');
            $l_res = $l_model->add($l_data);
            if(!$l_res){
                $model->rollback();
                return array(
                    'success' => false,
                    'errinfo' => $l_model->getError(),
                );
            }


            //事务完成提交
            $model->commit();
            return array(
                'success' => true,
                'errinfo' => '',
            );


        }


        //查询提现申请记录
        public function query_record($user_id,$where=array(),$limit=''){
            $where['user_id'] = $user_id;
            $a_model = M('WithdrawCash');
            $res = $a_model->where($where)
                           ->alias('a')
                           ->join("left join mk_withdraw_cash_logs b on a.id=b.wc_id")
                           ->order('create_time desc')
                           ->limit($limit)
                           ->select();
            if($res === false){
                return array(
                    'success' => false,
                    'data' => $a_model->getError(),
                );
            }
            return array(
                'success' => true,
                'data' => $res,
            );
        }


        //取消提现
        public function cancel($user_id,$id){

            $a_model = M('WithdrawCash');
            $u_model = M('UserList');
            $l_model = M('WithdrawCashLogs');

            $amount = $a_model->where(array('user_id'=>$user_id,'id'=>$id))->find()['freeze_amount'];
            if(empty($amount) || $amount<=0){
                return array(
                    'success' => false,
                    'errinfo' => '内部错误',
                );
            }

            $model = M('');
            $model->startTrans();

            //冻结金额退回用户余额
            $u_res = $u_model->where(array('id'=>$user_id))->setInc('amount',$amount);
            if(!$u_res){
                $model->rollback();
                return array(
                    'success' => false,
                    'errinfo' => $u_model->getError(),
                );
            }

            $l_res = $l_model->where(array('wc_id'=>$id))->save(array('examine_status'=>3));
            if(!$l_res){
                $model->rollback();
                return array(
                    'success' => false,
                    'errinfo' => $l_model->getError(),
                );
            }

            // //删除记录
            // $a_res = $a_model->where(array('id'=>$id))->delete();
            // if(!$a_res){
            //     $model->rollback();
            //     return array(
            //         'success' => false,
            //         'errinfo' => $a_model->getError(),
            //     );
            // }

            // //删除审核记录
            // $l_res = $l_model->where(array('wc_id'=>$id))->delete();
            // if(!$l_res){
            //     $model->rollback();
            //     return array(
            //         'success' => false,
            //         'errinfo' => $l_model->getError(),
            //     );
            // }

            //事务完成提交
            $model->commit();
            return array(
                'success' => true,
                'errinfo' => '',
            );


        }








        //获取用户当前总金额
        public function getAmount($user_id){
            $model = M('UserList');
            return $model->field('amount')->where(array('id'=>$user_id))->find();
        }

        // //为用户增加 count 的余额
        // private function amount_inc($user_id,$count){

        //     return false;

        //     if(empty($user_id) || empty($count) || !is_numeric($count)){
        //         return array(
        //             'success' => false,
        //             'errinfo' => '非法参数',
        //         );
        //     }

        //     $model = M('UserList');
        //     if( $model->where(array('id'=>$user_id))->setInc('amount',$count) ){
        //         return array(
        //             'success' => true,
        //             'errinfo' => '',
        //         );
        //     }else{
        //         return array(
        //             'success' => false,
        //             'errinfo' => $model->getError(),
        //         );
        //     }

        // }

        // //为用户减少 count 的余额
        // private function amount_dec($user_id,$count){

        //     return false;

        //     if(empty($user_id) || empty($count) || !is_numeric($count)){
        //         return array(
        //             'success' => false,
        //             'errinfo' => '非法参数',
        //         );
        //     }

        //     $amount = $this->getAmount($user_id);
        //     if($count>$amount['amount']){
        //         return array(
        //             'success' => false,
        //             'errinfo' => '金额超过限制',
        //         );
        //     }

        //     $model = M('UserList');
        //     if( $model->where(array('id'=>$user_id))->setDec('amount',$count) ){
        //         return array(
        //             'success' => true,
        //             'errinfo' => '',
        //         );
        //     }else{
        //         return array(
        //             'success' => false,
        //             'errinfo' => $model->getError(),
        //         );
        //     }

        // }

    }