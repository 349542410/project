<?php
/**
 * 补充证件资料  服务器端
 */
namespace Api\Controller;
use Think\Controller\HproseController;
class CertificatesController extends HproseController{
    protected $crossDomain =    true;
    protected $P3P         =    true;
    protected $get         =    true;
    protected $debug       =    true;

    public function check($mkno){
        $map['MKNO']     = array('eq',$mkno);
        $res = M('TranList tl')->field('tl.idno,tl.IL_state,tc.cid,tl.MKNO')
                                ->join('LEFT JOIN mk_transit_center tc ON tc.id=tl.TranKd')
                                ->where($map)
                                ->find();
		session('mkno',$mkno);
		return $res;
    }

    public function add_one($arr){

        // 当前查询的是什么表
        $table = 'ulist';

        $list_data = array();
        if(preg_match('/^MK[a-zA-Z0-9]+$/',$arr['order_no'])){
            // ulist 表
            $list_data = M('TranUlist')->alias('a')
                                        ->where(array('a.MKNO'=>$arr['order_no']))
                                        ->find();
            
            if(empty($list_data)){
                // list 表
                $list_data = M('TranList')->alias('a')
                                        ->where(array('a.MKNO'=>$arr['order_no']))
                                        ->find();
                $table = 'list';
            }
        }else{
            // ulist 表
            $list_data = M('TranUlist')->alias('a')
                                        ->where(array('a.order_no'=>$arr['order_no']))
                                        ->find();
            if(empty($list_data)){
                // list 表
                $list_data = M('TranList')->alias('a')
                                        ->where(array('a.auto_Indent2'=>$arr['order_no']))
                                        ->find();
                $table = 'list';
            }
        }

        if(!$list_data){
            $result = array('status' => '0', 'msg' => '美快单号['.$arr['order_no'].']不存在');
            return $result;
        }

        if($list_data['receiver'] != $arr['receiver']){
            $result = array('status' => '0', 'msg' => '收件人['.$arr['receiver'].']与该单资料不匹配，请重新输入');
            return $result;
        }

        if($list_data['reTel'] != $arr['reTel']){
            $result = array('status' => '0', 'msg' => '收件人电话['.$arr['reTel'].']与该单资料不匹配，请重新输入');
            return $result;
        }

        if($table == 'list'){
            if(!empty($list_data['idno']) && !empty($list_data['front_id_img']) && !empty($list_data['back_id_img'])){
                $result = array('status' => '0', 'msg' => '订单已录入身份证信息');
                return $result;
            }
        }else{
            if($list_data['id_img_status'] == '100' && $list_data['id_no_status'] == '100'){
                $result = array('status' => '0', 'msg' => '订单已录入身份证信息');
                return $result;
            }
        }


        // 查找成功，返回数据
        $res = M('TransitCenter')->field('member_sfpic_state,input_idno')->where(array('id'=>$list_data['TranKd']))->find();
        if($res !== false){
            if($table == 'list'){
                $result = array(
                    'status'=>'1', 
                    // 'table'=>$table, 
                    // 'msg'=>$res, 
//                     'sid'=>$list_data['id'],
                    'idno'=>$list_data['idno'],
                    'reTel'=>$list_data['reTel'],
                    'order_no'=>$list_data['auto_Indent2'], 
                    // 'TranKd'=>$list_data['TranKd'], 
                    'user_id'=>$list_data['user_id'], 
                    'receiver'=>$list_data['receiver'],  
                    'MKNO'=>$list_data['MKNO'], 
                    // 'ulist_data'=>$list_data,
                    'f'=>base64_encode($list_data['front_id_img']), 
                    'sf'=>base64_encode($list_data['small_front_img']), 
                    'b'=>base64_encode($list_data['back_id_img']), 
                    'sb'=>base64_encode($list_data['small_back_img']), 
                );
            }else{
                $result = array(
                    'status'=>'1',
                    'idno'=>$list_data['idno'],
                    // 'table'=>$table, 
                    // 'msg'=>$res, 
                    // 'sid'=>$list_data['id'],
                    'reTel'=>$list_data['reTel'],
                    'order_no'=>$list_data['order_no'], 
                    // 'TranKd'=>$list_data['TranKd'], 
                    'user_id'=>$list_data['user_id'], 
                    'receiver'=>$list_data['receiver'], 
                    'MKNO'=>$list_data['MKNO'], 
                    // 'ulist_data'=>$list_data,
                    'f'=>base64_encode($list_data['front_id_img']), 
                    'sf'=>base64_encode($list_data['small_front_img']), 
                    'b'=>base64_encode($list_data['back_id_img']), 
                    'sb'=>base64_encode($list_data['small_back_img']), 
                );
            }
            
        }else{
            $result = array('status'=>'0', 'msg'=>'查询失败');
        }
        return $result;


    }

}