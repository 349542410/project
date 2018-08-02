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
class MkReceiveController extends HproseController{

//===========================   登录  ============================
    /**
     * 登陆验证
     * @param  [type] $name [用户输入的账号名]
     * @param  [type] $pwd  [用户输入的密码]
     * @param  [type] $type [账号登录类型：用户名，邮箱]
     * @param  [type] $out  [判断是否为外部访问的登陆方式]
     */
    public function is_login($name, $pwd, $type='name'){
        
        $map[$type] = array('eq',$name);

        $user = M('ManagerList m')->field('m.id,m.name,m.tname,m.pwd,m.status,r.power')->join('left join mk_role_list r on r.id = m.groupid')->where($map)->find();

        $power = unserialize($user['power']); //反序列化

        if(!$user){
            return array('state' => 'no', 'msg'=>'账户不存在', 'lng'=>'user_not_exist');
        }

        if($user['pwd'] != $pwd){
            return array('state' => 'no', 'msg' => '密码错误', 'lng'=>'pwd_is_wrong');
        }

        if($user['status'] == 0){
            return array('state' => 'no', 'msg' => '账户已被禁用', 'lng'=>'user_is_disabled');
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
    public function check_print_user($data_a, $data_u, $user, $time_out='600'){
        $uesr_app_model = M('AdminUserToken');
        // 查询是否存在
        $user_app = $uesr_app_model->where($data_a)->find();

        if($user_app){

            // 暂时取消登录限制  20170926
            // //如果token已经存在，且尚未过期，则不能登录
            // if((time()-intval($user_app['time_out'])) <= intval($time_out)){
            //     return 'already_logined';
            // }else{
                $idc = $uesr_app_model->where($data_a)->data($data_u)->save();
            // }

        }else{
            $data_u['user_id']      = $user['id'];
            $idc = $uesr_app_model->data($data_u)->add();
        }

        return $idc;
    }

//===================== 主界面 ======================
    /**
     * [_index description]
     * @param  [type] $MKNO        [美快单号 或 快递运单号]
     * @param  [type] $ilstarr     [快件状态与说明 数组]
     * @param  [type] $weigh_again [是否再次发送称重资料 20180313 jie]
     * @param  [type] $new_weight  [称重重量 20180313 jie]
     * @return [type]              [description]
     */
    public function _index($MKNO, $ilstarr){
        //验证订单号是否MK开头，非MK开头的单号即为物流公司运单号  20170707 jie
        if(!preg_match("/^(MK)\w{0,}$/",$MKNO)){
            //先从tran_list表中通过STNO查出MK号码后，再查物流信息
            if(strlen($MKNO) > 10){
                //订单信息
                $info = M('TranList l')->field('l.id,l.user_id,l.MKNO,l.STNO,l.IL_state,l.sender,l.sendTel,l.receiver,l.reAddr,l.reTel,l.weight,l.TranKd,l.idno,l.auto_Indent2,t.name as line_name,t.member_sfpic_state,t.input_idno')->join('mk_transit_center t on t.id = l.TranKd')->where(array('STNO'=>$MKNO))->find();

                $Ulist = M('TranUlist')->field('id,user_id,freight,tax,order_no,print_time,print_state,print_num,id_img_status,idno,MKNO,STNO')->where(array('STNO'=>$MKNO))->find();
            }else{
                return array('state'=>'no', 'msg'=>'运单号未能匹配', 'lng'=>'freight_number_not_exist');//既不是MKNO也不是STNO，无法查询数据，因此直接返回false
            }

        }else{//属于MKNO格式，则直接根据MKNO查询订单信息
            //订单信息
            $info = M('TranList l')->field('l.id,l.user_id,l.MKNO,l.STNO,l.IL_state,l.sender,l.sendTel,l.receiver,l.reAddr,l.reTel,l.weight,l.TranKd,l.idno,l.auto_Indent2,t.name as line_name,t.member_sfpic_state,t.input_idno')->join('mk_transit_center t on t.id = l.TranKd')->where(array('MKNO'=>$MKNO))->find();

            $Ulist = M('TranUlist')->field('id,user_id,freight,tax,order_no,print_time,print_state,print_num,id_img_status,idno,MKNO,STNO')->where(array('MKNO'=>$MKNO))->find();
        }

        // 查无数据
        if(!$info) return array('state'=>'no', 'msg'=>'查无数据或尚未完成打印流程', 'lng'=>'no_data');
        
        // 20180313 jie 解决打印出纸后死机等问题，造成无法揽收
        if(!$Ulist){
            $u_data = array();
            $u_data['MKNO']        = $info['MKNO'];
            $u_data['STNO']        = $info['STNO'];
            $u_data['print_state'] = '200';
            $u_save = M('TranUlist')->where(array('order_no'=>$info['auto_Indent2']))->save($u_data);
            if($u_save !== false){
                $Ulist['MKNO']        = $info['MKNO'];
                $Ulist['STNO']        = $info['STNO'];
                $Ulist['print_state'] = '200';
            }else{
                $Ulist = false;
            }
            
        }

        // 20171116
        if($Ulist['print_state'] != '200') return array('state'=>'no', 'msg'=>'该订单尚未完成打印流程', 'lng'=>'not_print');

        /* 20180112  检查身份证号码和身份证照片  jie */
        $info['id_img_status'] = $Ulist['id_img_status'];
        $info['idno']          = $Ulist['idno'];//身份证号码用tran_ulist.idno的值

        // 暂时 停止使用  20180313   jie
        // $CheckIdInfo = new \AUApi\Controller\CheckIdInfoController();

        // $check_res = $CheckIdInfo->check_id($info);
        // if ($check_res !== true) return $check_res;

        /* end 20180112  检查身份证号码和身份证照片  jie */

        // 从消费记录中返回运费与关务费(税金)
        $wl_order = M('WlorderRecord')->field('freight,tax')->where(array('order_no'=>$Ulist['order_no']))->find();

        /* 20171030 新增 */
        $re_map['member_id'] = array('eq', $Ulist['user_id']);
        $re_map['order_id']  = array('eq', $Ulist['id']);
        $re_map['tran_id']   = array('eq', $info['id']);
        $check_relation = M('PrintRelationOrder')->where($re_map)->find();

        if(!$check_relation){

            //判断是否属于打印系统版本2.0前的订单
            if(strtotime($Ulist['print_time']) <= strtotime('2017-11-22 00:00:00')){

                // 默认用 最旧的一个终端号，作为旧订单的揽收id
                $self_terminal = M('SelfTerminalList')->where(array('type'=>'print'))->order('id asc')->find();

                if(!$self_terminal){
                    return array('state'=>'no', 'msg'=>'尚未生成对应的关联数据', 'lng'=>'no_relation_data');
                }

                $new_data['tran_id']     = $info['id'];
                $new_data['member_id']   = $Ulist['user_id'];
                $new_data['terminal_id'] = $self_terminal['id'];
                $new_data['order_id']    = $Ulist['id'];
                $new_data['ctime']       = date('Y-m-d H:i:s');
                M('PrintRelationOrder')->add($new_data);
            }else{

                return array('state'=>'no', 'msg'=>'尚未生成对应的关联数据', 'lng'=>'no_relation_data');
            }

        }
        /* 20171030 新增 end */

        //查询该线路信息
        $center = M('TransitCenter')->where(array('id'=>$info['TranKd']))->find();

        //检查该线路的 bc_state 是否为1
        if($center['bc_state'] == '1'){
            //订单相关商品信息
            $goods = M('TranUorder t')->field('t.oid,t.lid,t.number,t.weight,t.remark,t.auto_Indent1,t.auto_Indent2,p.show_name,p.name as detail,p.brand,p.hs_code,p.hgid,p.price,p.coin,p.unit,p.source_area,p.specifications,p.barcode,c.cat_name as catname,c.price as tax_price')->join('left join mk_product_list p on p.id = t.product_id')->join('left join mk_category_list c on c.id = p.cat_id')->where(array('t.lid'=>$Ulist['id']))->select();

        }else{
            //订单相关商品信息
            $goods = M('TranOrder')->where(array('lid'=>$info['id']))->select();
            foreach($goods as $k=>$item){
                $goods[$k]['show_name'] = $item['detail'];
            }
        }

        // 2017-09-19  cc_state的线路，整单税金<=7美元，直接显示“免税”，>7的直接显示所计算得到的金额（不用减7）
        if($center['cc_state'] == '1'){
            if(floatval($wl_order['tax']) <= 7){
                $wl_order['tax'] = 0;
            }
        }

        $ilstarr = array_column($ilstarr, 1, 0); //二维数组转一维数组
        $info['IL_state_msg'] = ($ilstarr[$info['IL_state']]) ? $ilstarr[$info['IL_state']] : '未定义的状态说明';

        //订单相关的商品信息，并入到订单信息的goods里面
        $info['freight'] = sprintf("%.2f", $wl_order['freight']);   //运费金额
        $info['tax']     = sprintf("%.2f", $wl_order['tax']);   //税金
        $info['goods']   = $goods;  //快件所含商品列表

        return array('state'=>'yes', 'rdata'=>$info);
    }

//=================  账户充值 ==============================

    //充值记录(当天的充值记录)
    public function _recharge_list($user_id){
        $map['r.user_id'] = array('eq', $user_id);
        //当天该操作员充值记录
        $map['r.paytime'] = array(
            array('egt',date('Y-m-d H:i:s',strtotime(date('Y-m-d',time())))),
            array('lt',date('Y-m-d H:i:s',strtotime(date('Y-m-d',time()))+86399))
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
    public function _recharging($user_id, $member, $amount, $paykind, $pwd){

        //检查会员资料是否存在
        $check_member = M('UserList')->where(array('username'=>$member))->find();
        if(!$check_member) return array('state'=>'no', 'msg'=>'该会员账号不存在', 'lng'=>'member_not_exist');

        //会员资料，审核状态检查
        if($check_member['step'] < 5){
            return array('state' => 'no', 'msg' => '该会员账户资料尚未完善', 'lng'=>'info_not_perfect');
        }
        if($check_member['status'] == 0){
            return array('state' => 'no', 'msg' => '该会员账户未审核通过', 'lng'=>'must_examine');
        }else if($check_member['status'] > 1){
            return array('state' => 'no', 'msg' => '该会员账户资料审核不通过，请登录官网完善资料再次审核', 'lng'=>'not_examine_need_perfect');
        }
        //会员资料，审核状态检查 end

        $check_manager = M('ManagerList')->where(array('id'=>$user_id))->find();
        if($check_manager['pwd'] != $pwd) return array('state'=>'no', 'msg'=>'密码验证不通过', 'lng'=>'pwd_not_right');

        $total = sprintf("%.2f", floatval($check_member['amount']) + floatval($amount));

        $order_no   = StrOrderOne($check_member['id']);  //获取内部订单号
        $order_time = date('Y-m-d H:i:s');  //内部订单时间
        $payno      = build_sn();//支付订单号
        $paytime    = date('Y-m-d H:i:s');  //支付时间

        $Model = M();   //实例化
        $Model->startTrans();//开启事务

        $data_m = array();
        $data_m['user_id']          = $user_id;//管理员ID
        $data_m['amount']           = $amount;//充值金额 美元
        $data_m['paykind']          = $paykind;//充值方式
        $data_m['payno']            = $payno;//支付单号
        $data_m['paytime']          = $paytime;//支付时间
        $data_m['member']           = $member;//代充值的会员账号
        $data_m['member_id']        = $check_member['id']; // 代充值的会员ID   20171017
        $data_m['user_balance_usa'] = $total;//充值后的账户余额  20171017

        $res_m = M('ManagerRechargeList')->add($data_m);

        $data_r = array();
        $data_r['UID']              = $check_member['id'];
        $data_r['amount']           = sprintf("%.2f", floatval(C('US_TO_RMB_RATE')) * floatval($amount));//充值金额 人民币  20171017
        $data_r['amount_usa']       = $amount;//充值金额 美元
        $data_r['paykind']          = $paykind;//充值方式
        $data_r['ordertime']        = $order_time;//下单时间
        $data_r['order_no']         = $order_no;//下单单号
        $data_r['payno']            = $payno;//支付单号
        $data_r['paytime']          = $paytime;//支付时间
        $data_r['user_balance_usa'] = $total;//充值后的账户余额  20171017

        $res_r = M('RechargeRecord')->add($data_r);

        if($res_m !== false && $res_r !== false){
            $data_u = array();
            $data_u['amount'] = $total;//充值后的账户余额

            //更新会员账户会员
            $user = M('UserList')->where(array('id'=>$check_member['id']))->save($data_u);

            M('ManagerRechargeList')->where(array('id'=>$res_m))->setInc('input_num', 1);//成功充值则次数+1
            M('RechargeRecord')->where(array('id'=>$res_r))->setField('pay_state', 200);
            $Model->commit();//提交事务成功

            // 返回客户端显示以下信息
            $rdata = array();
            $rdata['member_amount'] = $check_member['amount'];//充值前的账户余额
            $rdata['charge_amount'] = $amount;//充值余额
            $rdata['total_amount']  = $total;//充值后的账户余额
            $rdata['charge_list']   = $this->_recharge_list($user_id);//包含最新的当天充值记录

            return array('state'=>'yes', 'msg'=>'充值成功', 'lng'=>'recharge_success', 'xdata'=>$rdata);
        }else{
            $Model->rollback();//事务有错回滚
            return array('state'=>'no', 'msg'=>'充值失败', 'lng'=>'recharge_failed');
        }
    }

    //账户退出
    public function _login_out($token){
        $del = M('AdminUserToken')->where(array('token'=>$token))->delete();

        if($del !== false){
            return array('state'=>'yes', 'msg'=>'退出成功', 'lng'=>'logout_success');
        }else{
            return array('state'=>'no', 'msg'=>'退出失败', 'lng'=>'logout_failed');
        }
    }
//================= 登录状态检验 ===========================
    public function _check_login($token){
        $find = M('AdminUserToken')->where(array('token'=>$token))->find();
        return $find;
    }

    public function hold_login($token){
        M('AdminUserToken')->where(array('token'=>$token))->setField('time_out',time());
    }

}