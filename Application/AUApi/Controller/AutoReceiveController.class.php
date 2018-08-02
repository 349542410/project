<?php
/**
 * 物流揽收系统
 *                                 已转移到 MkAuto 文件夹
 * 功能：揽件   会员账户充值
 * 创建时间：2017-08-10
 * 创建人：jie
 */

namespace AUApi\Controller;

use Think\Controller\HproseController;

class AutoReceiveController extends HproseController
{

//===========================   登录  ============================
    /**
     * 登陆验证
     * @param  [type] $name [用户输入的账号名]
     * @param  [type] $pwd  [用户输入的密码]
     * @param  [type] $type [账号登录类型：用户名，邮箱]
     * @param  [type] $out  [判断是否为外部访问的登陆方式]
     */
    public function is_login($name, $pwd, $type = 'name')
    {

        $map[$type] = array('eq', $name);

        $user = M('ManagerList m')->field('m.id,m.name,m.tname,m.pwd,m.status,r.power')
            ->join('left join mk_role_list r on r.id = m.groupid')->where($map)->find();

        // $power = unserialize($user['power']); //反序列化

        if (!$user) {
            return array('state' => 'no', 'msg' => '账户不存在', 'lng' => 'user_not_exist');
        }

        if ($user['pwd'] != $pwd) {
            return array('state' => 'no', 'msg' => '密码错误', 'lng' => 'pwd_is_wrong');
        }

        if ($user['status'] == 0) {
            return array('state' => 'no', 'msg' => '账户已被禁用', 'lng' => 'user_is_disabled');
        }

        // if($power['mk_receive'] != 'on'){
        //     return array('state' => 'no', 'msg' => '没有权限操作系统', 'lng'=>'no_power');
        // }

        return $user;
    }

    /**
     * 保存token相关信息
     * @param  [type] $data_a   [description]
     * @param  [type] $data_u   [description]
     * @param  [type] $user     [description]
     * @param  string $time_out [超时时间，默认30分钟]
     * @return [type]           [description]
     */
    public function check_print_user($data_a, $data_u, $user, $time_out = '600')
    {
        $uesr_app_model = M('AdminUserToken');
        // 查询是否存在
        $user_app = $uesr_app_model->where($data_a)->find();

        if ($user_app) {

            // 暂时取消登录限制  20170926
            // //如果token已经存在，且尚未过期，则不能登录
            // if((time()-intval($user_app['time_out'])) <= intval($time_out)){
            //     return 'already_logined';
            // }else{
            $idc = $uesr_app_model->where($data_a)->data($data_u)->save();
            // }

        } else {
            $data_u['user_id'] = $user['id'];
            $idc = $uesr_app_model->data($data_u)->add();
        }

        return $idc;
    }

//===================== 主界面 ======================

    /**
     * [_index description]
     * @param  [type] $MKNO        [美快单号 或 快递运单号]
     * @param  [type] $ilstarr     [快件状态与说明 数组]
     * @return [type]              [description]
     */
    public function _index($MKNO, $ilstarr, $RMB_Free_Duty, $US_TO_RMB_RATE)
    {

        $field_info = 'l.id,l.user_id,l.MKNO,l.STNO,l.IL_state,l.sender,l.sendTel,l.receiver,l.reAddr,l.reTel,l.weight,l.TranKd,l.idno,l.auto_Indent2,t.name as line_name,t.member_sfpic_state,t.input_idno';

        $field_Ulist = 'id,user_id,freight,tax,order_no,print_time,print_state,print_num,id_img_status,idno,MKNO,STNO,cost,discount_amount,charge';

        //验证订单号是否MK开头，非MK开头的单号即为物流公司运单号  20170707 jie
        if (!preg_match("/^(MK)\w{0,}$/", $MKNO)) {
            //先从tran_list表中通过STNO查出MK号码后，再查物流信息
            if (strlen($MKNO) > 10) {
                //订单信息
                $info = M('TranList l')->field($field_info)->join('mk_transit_center t on t.id = l.TranKd')->where(array('STNO' => $MKNO))->find();

                $Ulist = M('TranUlist')->field($field_Ulist)->where(array('STNO' => $MKNO))->find();
            } else {
                return array(
                    'state' => 'no',
                    'msg' => '运单号未能匹配',
                    'lng' => 'freight_number_not_exist'
                );//既不是MKNO也不是STNO，无法查询数据，因此直接返回false
            }

        } else {//属于MKNO格式，则直接根据MKNO查询订单信息
            //订单信息
            $info = M('TranList l')->field($field_info)->join('mk_transit_center t on t.id = l.TranKd')->where(array('MKNO' => $MKNO))->find();

            $Ulist = M('TranUlist')->field($field_Ulist)->where(array('MKNO' => $MKNO))->find();
        }

        //判断线路税金起征额
        $centerTaxThreshold = M('transit_center')->field('taxthreshold')->where(['id' => $Ulist['TranKd']])->find();
        if($centerTaxThreshold['taxthreshold'] > 0){
            $RMB_Free_Duty = $centerTaxThreshold['taxthreshold'];
        }

        // 查无数据
        if (!$info) {
            return array('state' => 'no', 'msg' => '查无数据或尚未完成打印流程', 'lng' => 'no_data');
        }

        // 20180313 jie 解决打印出纸后死机等问题，造成无法揽收
        if (!$Ulist) {
            $u_data = array();
            $u_data['MKNO'] = $info['MKNO'];
            $u_data['STNO'] = $info['STNO'];
            $u_data['print_state'] = '200';
            $u_save = M('TranUlist')->where(array('order_no' => $info['auto_Indent2']))->save($u_data);
            if ($u_save !== false) {
                $Ulist['MKNO'] = $info['MKNO'];
                $Ulist['STNO'] = $info['STNO'];
                $Ulist['print_state'] = '200';
            } else {
                $Ulist = false;
            }

        }

        // 20171116
        if ($Ulist['print_state'] != '200') {
            return array('state' => 'no', 'msg' => '该订单尚未完成打印流程', 'lng' => 'not_print');
        }

        /* 20180112  检查身份证号码和身份证照片  jie */
        $info['id_img_status'] = $Ulist['id_img_status'];
        $info['idno'] = $Ulist['idno'];//身份证号码用tran_ulist.idno的值

        // 暂时 停止使用  20180313   jie
        // $CheckIdInfo = new \AUApi\Controller\CheckIdInfoController();

        // $check_res = $CheckIdInfo->check_id($info);
        // if ($check_res !== true) return $check_res;

        /* end 20180112  检查身份证号码和身份证照片  jie */

        // 从消费记录中返回运费与关务费(税金)
        // $wl_order = M('WlorderRecord')->field('freight,tax')->where(array('order_no'=>$Ulist['order_no']))->find();

        /* 20171030 新增 */
        $re_map['member_id'] = array('eq', $Ulist['user_id']);
        $re_map['order_id'] = array('eq', $Ulist['id']);
        $re_map['tran_id'] = array('eq', $info['id']);
        $check_relation = M('PrintRelationOrder')->where($re_map)->find();

        if (!$check_relation) {

            //判断是否属于打印系统版本2.0前的订单
            if (strtotime($Ulist['print_time']) <= strtotime('2017-11-22 00:00:00')) {

                // 默认用 最旧的一个终端号，作为旧订单的揽收id
                $self_terminal = M('SelfTerminalList')->where(array('type' => 'print'))->order('id asc')->find();

                if (!$self_terminal) {
                    return array('state' => 'no', 'msg' => '尚未生成对应的关联数据', 'lng' => 'no_relation_data');
                }

                $new_data['tran_id'] = $info['id'];
                $new_data['member_id'] = $Ulist['user_id'];
                $new_data['terminal_id'] = $self_terminal['id'];
                $new_data['order_id'] = $Ulist['id'];
                $new_data['ctime'] = date('Y-m-d H:i:s');
                M('PrintRelationOrder')->add($new_data);
            } else {
                return array('state' => 'no', 'msg' => '尚未生成对应的关联数据', 'lng' => 'no_relation_data');
            }

        }
        /* 20171030 新增 end */

        //查询该线路信息
        $center = M('TransitCenter')->where(array('id' => $info['TranKd']))->find();

        //检查该线路的 bc_state 是否为1
        if ($center['bc_state'] == '1') {
            //订单相关商品信息
            $goods = M('TranUorder t')->field('t.oid,t.lid,t.number,t.weight,t.remark,t.auto_Indent1,t.auto_Indent2,p.show_name,p.name as detail,p.brand,p.hs_code,p.hgid,p.price,p.coin,p.unit,p.source_area,p.specifications,p.barcode,c.cat_name as catname,c.price as tax_price')->join('left join mk_product_list p on p.id = t.product_id')->join('left join mk_category_list c on c.id = p.cat_id')->where(array('t.lid' => $Ulist['id']))->select();

        } else {
            //订单相关商品信息
            $goods = M('TranOrder')->where(array('lid' => $info['id']))->select();
            foreach ($goods as $k => $item) {
                $goods[$k]['show_name'] = $item['detail'];
            }
        }

        // 2017-09-19  cc_state的线路，整单税金<=7美元，直接显示“免税”，>7的直接显示所计算得到的金额（不用减7）
        if ($center['cc_state'] == '1') {
            if ($center['tax_kind'] == '1') {

                //根据汇率计算出美元免税的额度
                $free_duty = number_format(($RMB_Free_Duty / $US_TO_RMB_RATE), 2, '.', '');

                // 2017-09-19  整单税金<=7美元的时候，直接免税；>7的直接显示所计算得到的税金（不用减7）
                if (sprintf("%.2f", $Ulist['tax']) <= $free_duty) {
                    $Ulist['tax'] = 0;
                }

            }
        }

        $ilstarr = array_column($ilstarr, 1, 0); //二维数组转一维数组
        $info['IL_state_msg'] = ($ilstarr[$info['IL_state']]) ? $ilstarr[$info['IL_state']] : '未定义的状态说明';

        //订单相关的商品信息，并入到订单信息的goods里面
        $info['charge'] = sprintf("%.2f", $Ulist['charge']);   //实收服务费
        $info['freight'] = sprintf("%.2f", $Ulist['freight']);   //运费金额
        $info['cost'] = sprintf("%.2f", $Ulist['cost']);   //总消费金额 不包含税金
        $info['tax'] = sprintf("%.2f", $Ulist['tax']);   //税金
        $info['discount_amount'] = sprintf("%.2f", $Ulist['discount_amount']);   //优惠金额，取数据表的作为初始值显示
        $info['goods'] = $goods;  //快件所含商品列表

        $SystemCharging = new \AUApi\Controller\SysFreightCountController();

        $Web_Config = $SystemCharging->_get_lines_configure($info);//线路价格配置与会员线路优惠

        if (isset($Web_Config['state']) && $Web_Config['state'] == 'no') {
            return $Web_Config;
        }

        $info['Web_Config'] = $Web_Config;

        return array('state' => 'yes', 'rdata' => $info);
    }
//=======================================

    /**
     * [_extra_step 当揽收扫描第一次，有发送重量的时候，才执行]
     * @param  [type] $id     [tran_list.id]
     * @param  [type] $weight [新的称重重量]
     * @return [type]         [description]
     */
    public function _extra_step($data_arr)
    {
        set_time_limit(0);

        $id = $data_arr['id'];//tran_list.id
        $weight = $data_arr['new_weight'];//最新称重重量
        $new_cost = $data_arr['new_cost'];//最新消费金额
        $new_freight = $data_arr['new_freight'];//最新运费
        $new_discount = $data_arr['new_discount'];//最新优惠金额
        $xml = $data_arr['xml'];//揽收报文  base64加密的json报文
        $operator_id = $data_arr['uid'];//操作人id

        $list = M('TranList')->where(array('id' => $id))->find();

        if (!$list) {
            return array('state' => 'no', 'msg' => '订单不存在', 'lng' => 'order_not_exist');
        }

        // 检查订单是否已经 揽收
        if ($list['IL_state'] >= '12') {
            return array('state' => 'no', 'msg' => '已揽收，请勿重复操作');
        }

        $user = M('UserList')->where(array('id' => $list['user_id']))->find();
        if (!$user) {
            return array('state' => 'no', 'msg' => '账户不存在', 'lng' => 'user_not_exist');
        }

        // 会员订单的信息
        $info = M('TranUlist')->where(array('order_no' => $list['auto_Indent2']))->find();

        /* 判断新的称重重量 == 原有的重量 */
        if ($info['weight'] == $weight) {
            // return array('state'=>'no', 'msg'=>'重量没变，无需再次计费');

            //重量没变的，可以直接揽收
            try {

                $SysTransit = new \AUApi\Controller\SysTransitController();

                $transit_res = $SysTransit->index($xml);

                if ($transit_res['Code'] == '1') {
                    return array('state' => 'yes', 'msg' => '操作成功', 'lng' => 'pay_success');
                } else {
                    return array('state' => 'no', 'msg' => $transit_res['LOGSTR']);
                }

            } catch (\Exception $e1) {
                return array('state' => 'no', 'msg' => $e1->getMessage());
            }
        }
        /* 判断新的称重重量 == 原有的重量 */

        // 重量发生变化的时候，执行下面程序

        /* 公共计费公式 */
        $SystemCharging = new \AUApi\Controller\SysFreightCountController();
        $sys_arr = array();
        $sys_arr['weight'] = $weight;
        $sys_arr['TranKd'] = $list['TranKd'];
        $sys_arr['user_id'] = $list['user_id'];
        $SystemCharge = $SystemCharging->index($sys_arr);

        if (isset($SystemCharge['state']) && $SystemCharge['state'] == 'no') {
            return $SystemCharge;
        }

        $freight = $SystemCharge['freight'];//计重运费
        $cost = $SystemCharge['cost'];//实际总消费（未包含税金）
        $original_price = $SystemCharge['original_price'];//原始消费金额
        $discount = $SystemCharge['discount'];//总优惠金额
        $Web_Config = $SystemCharge['Web_Config'];//线路价格配置与会员线路优惠
        /* 公共计费公式 */

        $cost = number_format(($info['tax'] + $cost), 2, '.', ''); // 总消费金额（包含税金）
        $original_price = number_format(($info['tax'] + $original_price), 2, '.', ''); // 未计算所有折扣优惠的 消费金额（包含税金）

        /* 如果新重量 <= 线路配置的首重，则只需要更改重量的值 */
        // if($weight <= $Web_Config['weight_first']){
        //     $data = array();
        //     $data['weigh_time']      = date('Y-m-d H:i:s');//称重时间
        //     $data['weight']          = $weight;//称重实际重量
        //     $data['old_weight']      = $info['weight'];//称重实际重量

        //     $save_order = M('TranUlist')->where(array('id'=>$info['id']))->save($data);//更新tran_ulist 部分字段

        //     $save_list = M('TranList')->where(array('id'=>$id))->setField('weight', $weight);//更新tran_list  重量字段

        //     if($save_order === false || $save_list === false){
        //         $Model->rollback();//事务有错回滚
        //         return array('state'=>'no','msg'=>'重量更新失败，请重试', 'lng'=>'weight_save_failed');
        //     }else{
        //         $logs = array();
        //         $logs['order_no']    = $info['order_no']; //内部订单号
        //         $logs['content']     = '称重重量与打印时不一致，已更新，费用不变';
        //         $logs['create_time'] = date('Y-m-d H:i:s');  //支付时间
        //         $logs['state']       = '3006';
        //         M('ULogs')->add($logs);//保存订单操作记录

        //         $Model->commit();//提交事务成功
        //         return array('state'=>'yes','msg'=>'操作成功','t_data'=>$data, 'lng'=>'weight_save_success');

        //     }
        // }
        /* 如果新重量 <= 线路配置的首重，则只需要更改重量的值 */

        /* 后台计算的结果 与 终端计算结果 比较 */

        // 揽收系统重新计费的时候，金额的比较的允许误差值
        $Error_Value = (C('Allow_Error_Value')) ? number_format(C('Allow_Error_Value'), 2, '.', '') : '0.02';

        // 允许存在一定的误差值
        if (abs(bcsub($new_cost, $cost, 2)) > $Error_Value || abs(bcsub($new_freight, $freight,
                2)) > $Error_Value || abs(bcsub($new_discount, $discount, 2)) > $Error_Value
        ) {

            return array(
                'state' => 'no',
                'msg' => '费用校对错误，请重试',
                'lng' => 'proof_price_failed',
                'cost' => $cost,
                'new_cost' => $new_cost,
                'freight' => $freight,
                'new_freight' => $new_freight,
                'discount' => $discount,
                'new_discount' => $new_discount,
                'Error_Value' => $Error_Value
            );
        }

        /* 后台计算的结果 与 终端计算结果 比较 */

        /* 如果新重量 > 线路配置的首重，则只需要更改重量的值 */
        $user_amount = $user['amount']; //账户余额

        // 差额
        $difference = number_format(($freight - $info['freight']), 2, '.', '');

        // 优惠金额的差额
        $diff_discount = number_format(($discount - $info['discount_amount']), 2, '.', '');

        // 原重量 小于 新重量
        if ($info['weight'] < $weight) {
            $cost_type = 1;//补扣
            $content = '您已确认订单揽收时产生差额，差额款项补扣成功';

            if ($user_amount == 0) {
                return array('state' => 'no', 'msg' => '账户余额为零，请先充值', 'lng' => 'balance_not_enough');
            }

            if ($user_amount < $difference) {
                return array('state' => 'no', 'msg' => '账户余额不足以支付订单，请先充值', 'lng' => 'balance_not_enough_to_pay');
            }

        } else {
            if ($info['weight'] > $weight) {
                $cost_type = 2;//退款
                $content = '您已确认订单揽收时产生差额，差额款项退还成功';

            }
        }
        $user_amount = number_format(($user_amount - $difference), 2, '.', ''); //新余额 = 原余额-补扣差额

        $Model = M();   //实例化
        $Model->startTrans();//开启事务

        try {

            //更新账户余额
            $save_user = M('UserList')->where(array('id' => $user['id']))->setField('amount', $user_amount);

            $data = array();
            $data['weigh_time'] = date('Y-m-d H:i:s');//称重时间
            $data['weight'] = $weight;//称重实际重量
            $data['old_weight'] = $info['weight'];//称重实际重量
            $data['freight'] = $freight;//总运费
            $data['cost'] = $cost;//实际消费金额（包含税金）
            $data['discount_amount'] = $discount;//折扣优惠金额   原价 - 实际消费金额
            $data['original_price'] = $original_price;//消费金额 原价（包含税金）

            $t_data = array();
            $t_data['UID'] = $info['user_id'];
            $t_data['paykind'] = $info['paykind'];
            $t_data['order_no'] = $info['order_no'];
            $t_data['ordertime'] = $info['ordertime'];
            $t_data['payno'] = build_sn();//创建支付单号
            $t_data['paytime'] = date('Y-m-d H:i:s');
            $t_data['user_balance_usa'] = $user_amount;// 消费后的账户余额
            $t_data['cost'] = $difference;//消费金额  用差额
            $t_data['freight'] = $difference;//计重运费
            $t_data['discount_amount'] = $diff_discount;//折扣优惠金额  差额
            $t_data['original_price'] = 0;//$original_price;//消费金额 原价
            $t_data['pay_state'] = 1;
            $t_data['deduct_num'] = 1;
            $t_data['cost_type'] = $cost_type;
            $t_data['note'] = ($cost_type == '1') ? '补扣' : '退款';

            //保存称重资料且计费成功后
            $save_order = M('TranUlist')->where(array('id' => $info['id']))->save($data);//更新tran_ulist 部分字段

            $save_list = M('TranList')->where(array('id' => $id))->setField('weight', $weight);//更新tran_list  重量字段

            $save_record = M('WlorderRecord')->add($t_data);
            // return M()->getLastSql();

            if ($save_user === false || $save_order === false || $save_record === false || $save_list === false) {
                $Model->rollback();//事务有错回滚
                return array('state' => 'no', 'msg' => '操作失败，如需帮助请咨询客服', 'lng' => 'pay_failed');
            } else {
                $logs = array();
                $logs['order_no'] = $info['order_no']; //内部订单号
                $logs['content'] = $content;
                $logs['operator_id'] = $operator_id;
                $logs['create_time'] = date('Y-m-d H:i:s');  //支付时间
                $logs['state'] = ($cost_type == '1') ? '3004' : '3005';
                $ulogs = M('ULogs')->add($logs);//保存订单操作记录

                try {

                    $SysTransit = new \AUApi\Controller\SysTransitController();

                    $transit_res = $SysTransit->index($xml);

                    if ($transit_res['Code'] == '1') {
                        $Model->commit();//提交事务成功
                        return array('state' => 'yes', 'msg' => '操作成功', 'lng' => 'pay_success');
                    } else {
                        $Model->rollback();//事务有错回滚
                        return array('state' => 'no', 'msg' => $transit_res['LOGSTR']);
                    }

                } catch (\Exception $e2) {
                    $Model->rollback();//事务有错回滚
                    return array('state' => 'no', 'msg' => $e2->getMessage());
                }

            }
        } catch (\Exception $e3) {
            $Model->rollback();//事务有错回滚
            return array('state' => 'no', 'msg' => $e3->getMessage());
        }
        /* 如果新重量 > 线路配置的首重，则只需要更改重量的值 */

    }
//=================  账户充值 ==============================

    //充值记录(当天的充值记录)
    public function _recharge_list($user_id)
    {
        $map['r.user_id'] = array('eq', $user_id);
        //当天该操作员充值记录
        $map['r.paytime'] = array(
            array('egt', date('Y-m-d H:i:s', strtotime(date('Y-m-d', time())))),
            array('lt', date('Y-m-d H:i:s', strtotime(date('Y-m-d', time())) + 86399))
        );
        $list = M('ManagerRechargeList r')->field('r.*,m.name')->join('left join mk_manager_list m on m.id = r.user_id')->where($map)->order('paytime desc,sys_time desc')->select();

        return $list;
    }

    /**
     * 充值 方法
     * @param  [type] $user_id [管理员ID]
     * @param  [type] $member  [代充值的会员账号]
     * @param  [type] $amount  [充值金额]
     * @param  [type] $paykind [充值方式]
     * @param  [type] $pwd     [管理员账号密码]
     * @return [type]          [description]
     */
    public function _recharging($user_id, $member, $amount, $paykind, $pwd, $ip)
    {
        //检查会员资料是否存在
        $check_member = M('UserList')->where(array('username' => $member))->find();
        if (!$check_member) {
            return array('state' => 'no', 'msg' => '该会员账号不存在', 'lng' => 'member_not_exist');
        }

        //会员资料，审核状态检查
        if ($check_member['step'] < 5) {
            return array('state' => 'no', 'msg' => '该会员账户资料尚未完善', 'lng' => 'info_not_perfect');
        }
        if ($check_member['status'] == 0) {
            return array('state' => 'no', 'msg' => '该会员账户未审核通过', 'lng' => 'must_examine');
        } else {
            if ($check_member['status'] > 1) {
                return array(
                    'state' => 'no',
                    'msg' => '该会员账户资料审核不通过，请登录官网完善资料再次审核',
                    'lng' => 'not_examine_need_perfect'
                );
            }
        }
        //会员资料，审核状态检查 end

        $check_manager = M('ManagerList')->where(array('id' => $user_id))->find();
        if ($check_manager['pwd'] != $pwd) {
            return array('state' => 'no', 'msg' => '密码验证不通过', 'lng' => 'pwd_not_right');
        }

        $total = sprintf("%.2f", floatval($check_member['amount']) + floatval($amount));

        $order_no = StrOrderOne($check_member['id']);  //获取内部订单号
        $order_time = date('Y-m-d H:i:s');  //内部订单时间
        $payno = build_sn();//支付订单号
        $paytime = date('Y-m-d H:i:s');  //支付时间

        $Model = M();   //实例化
        $Model->startTrans();//开启事务

        try {

            $data_m = array();
            $data_m['user_id'] = $user_id;//管理员ID
            $data_m['amount'] = $amount;//充值金额 美元
            $data_m['paykind'] = $paykind;//充值方式
            $data_m['payno'] = $payno;//支付单号
            $data_m['paytime'] = $paytime;//支付时间
            $data_m['member'] = $member;//代充值的会员账号
            $data_m['member_id'] = $check_member['id']; // 代充值的会员ID   20171017
            $data_m['user_balance_usa'] = $total;//充值后的账户余额  20171017
            $data_m['ip'] = $ip;//ip

            $res_m = M('ManagerRechargeList')->add($data_m);

            $data_r = array();
            $data_r['UID'] = $check_member['id'];
            $data_r['amount'] = sprintf("%.2f", floatval(C('US_TO_RMB_RATE')) * floatval($amount));//充值金额 人民币  20171017
            $data_r['amount_usa'] = $amount;//充值金额 美元
            $data_r['paykind'] = $paykind;//充值方式
            $data_r['ordertime'] = $order_time;//下单时间
            $data_r['order_no'] = $order_no;//下单单号
            $data_r['payno'] = $payno;//支付单号
            $data_r['paytime'] = $paytime;//支付时间
            $data_r['user_balance_usa'] = $total;//充值后的账户余额  20171017

            $res_r = M('RechargeRecord')->add($data_r);

            if ($res_m !== false && $res_r !== false) {
                $data_u = array();
                $data_u['amount'] = $total;//充值后的账户余额

                //更新会员账户会员
                $user = M('UserList')->where(array('id' => $check_member['id']))->save($data_u);

                M('ManagerRechargeList')->where(array('id' => $res_m))->setInc('input_num', 1);//成功充值则次数+1
                M('RechargeRecord')->where(array('id' => $res_r))->setField('pay_state', 200);
                $Model->commit();//提交事务成功

                // 返回客户端显示以下信息
                $rdata = array();
                $rdata['member_amount'] = $check_member['amount'];//充值前的账户余额
                $rdata['charge_amount'] = $amount;//充值余额
                $rdata['total_amount'] = $total;//充值后的账户余额
                $rdata['charge_list'] = $this->_recharge_list($user_id);//包含最新的当天充值记录

                return array('state' => 'yes', 'msg' => '充值成功', 'lng' => 'recharge_success', 'xdata' => $rdata);
            } else {
                $Model->rollback();//事务有错回滚
                return array('state' => 'no', 'msg' => '充值失败', 'lng' => 'recharge_failed');
            }
        } catch (\Exception $e) {
            return array('state' => 'no', 'msg' => $e->getMessage());
        }
    }

    //账户退出
    public function _login_out($token)
    {
        $del = M('AdminUserToken')->where(array('token' => $token))->delete();

        if ($del !== false) {
            return array('state' => 'yes', 'msg' => '退出成功', 'lng' => 'logout_success');
        } else {
            return array('state' => 'no', 'msg' => '退出失败', 'lng' => 'logout_failed');
        }
    }

//================= 登录状态检验 ===========================
    public function _check_login($token)
    {
        $find = M('AdminUserToken')->where(array('token' => $token))->find();
        return $find;
    }

    public function hold_login($token)
    {
        M('AdminUserToken')->where(array('token' => $token))->setField('time_out', time());
    }

}