<?php
/**
 * 包裹列表 服务器
 */
namespace Api\Controller;
use Think\Controller\HproseController;
use Think\Log;

class WebController extends HproseController {
    protected $crossDomain =    true;
    protected $P3P         =    true;
    protected $get         =    true;
    protected $debug       =    true;

    /**
     * 数据列表
     * @param  [type] $TranList [description]
     * @param  [type] $map      [description]
     * @param  [type] $p        [description]
     * @param  [type] $ePage    [description]
     * @param  [type] $l        [description]
     * @param  [type] $files    [description]
     * @return [type]           [description]
     */
    public function _list($TranList,$map,$p,$ePage,$l,$files,$l2='',$l3='',$l4='',$l5=''){
        set_time_limit(0);
        $list = M($TranList.' t')->field($files)->join($l2)->join($l3)->join($l4)->join($l5)->where($map)->order('t.id desc')->page($p.','.$ePage)->select();
        Log::write(M('')->_sql());
        // return M('')->getLastSql();
        return $list;
    }

    /**
     * 数据统计
     * @param  [type] $TranList [description]
     * @param  [type] $map      [description]
     * @param  [type] $l        [description]
     * @param  [type] $files    [description]
     * @return [type]           [description]
     */
    public function count($TranList,$map,$l,$l3=''){
        $count = M($TranList.' t')->field('t.id')->join($l3)->where($map)->count();// 查询满足要求的总记录数
        return $count;
    }


    /**
     * 获取某行数据详细信息
     * @param  [type] $id       [订单ID]
     * @param  [type] $type     [从哪个页面访问]
     * @param  [type] $order_no [内部订单号]
     * @return [type]           [description]
     */
    public function getInfo($id, $type, $order_no, $user_id){

        if($type == 'index'){
            
            //如果订单号为空，则用id进行查询
            if($order_no == ''){
                $condition['t.id'] = array('eq',$id);
            }else{
                $condition['t.order_no'] = array('eq',$order_no);
            }

            $condition['t.delete_time'] = array('exp', 'is null');

            //订单信息
            $info = M('TranUlist t')
                    ->field('t.*,tc.shop_state,sr.receipt_img')
//                    ->field('t.*,e.front_id_img,e.back_id_img,e.front_file_name,e.back_file_name,tc.shop_state,sr.receipt_img')
//                    ->join('left join mk_user_extra_info e on e.idno=t.idno and e.true_name=t.receiver and e.user_id='.$user_id)
                    ->join('left join mk_transit_center tc on t.TranKd=tc.id')
                    ->join('left join mk_shopping_receipt sr on t.id=sr.order_id')
                    ->where($condition)
                    ->find();
            // //liao ya di
            if(!$info){
                return false;
            }
            // $sql = M('')->getLastSql();
            // return $sql;
        }elseif($type=='Ulist'){
            $MKNO = M('TranUlist')->where(array('id'=>$id))->getField('MKNO');
            $info = M('TranUlist t')
                    ->field('t.*,e.front_id_img,e.back_id_img,e.front_file_name,e.back_file_name,tc.shop_state,sr.receipt_img')
                    ->join('left join mk_user_extra_info e on e.idno=t.idno and e.true_name=t.receiver and e.user_id='.$user_id)
                    ->join('left join mk_transit_center tc on t.TranKd=tc.id')
                    ->join('left join mk_shopping_receipt sr on t.id=sr.order_id')
                    ->where(array('t.MKNO'=>$MKNO, 't.delete_time'=>array('exp', 'is null')))
                    ->find();
            // //liao ya di
            if(!$info){
                return false;
            }
        }else{

            $MKNO = M('TranList')->where(array('id'=>$id))->getField('MKNO');
            // $MKNO = M('TranUlist')->where(array('id'=>$id))->getField('MKNO');
            $TranUList_MKNO = M('TranUlist')->where(array('MKNO'=>$MKNO))->find();
            // return array('TranList'=>$MKNO,'TranUlist'=>$TranUList_MKNO);

            // //liao ya di
            if(!$TranUList_MKNO){
                return false;
            }

            //订单信息
            $info = M('TranUlist t')
                    ->field('t.*,e.front_id_img,e.back_id_img,e.front_file_name,e.back_file_name,tc.shop_state,sr.receipt_img')
                    ->join('left join mk_user_extra_info e on e.idno=t.idno and e.true_name=t.receiver and e.user_id='.$user_id)
                    ->join('left join mk_transit_center tc on t.TranKd=tc.id')
                    ->join('left join mk_shopping_receipt sr on t.id=sr.order_id')
                    ->where(array('t.MKNO'=>$MKNO, 't.delete_time'=>array('exp', 'is null')))
                    ->find();

        }

        //查询该线路信息
        $center = M('TransitCenter')->where(array('id'=>$info['TranKd']))->find();

        $total_tax = 0; //总税金
        //检查该线路的 bc_state 是否为1
        if($center['bc_state'] == '1'){

            //订单相关商品信息
            $pro_list = M('TranUorder t')->field('t.oid,t.lid,t.number,t.weight,t.remark,t.price,p.unit,p.source_area,p.show_name as detail,p.brand,p.hs_code,p.hgid,p.coin,p.unit,c.cat_name as catname,c.price as tax')
                                        ->join('left join mk_product_list p on p.id = t.product_id')
                                        ->join('left join mk_category_list c on c.id = p.cat_id')
                                        ->where(array('t.lid'=>$info['id']))
                                        ->select();

            foreach($pro_list as $item){
                $total_tax += floatval($item['number']) * floatval($item['tax']);//统计税金 以便保存
            }

        }else if($center['cc_state'] == '1'){
            //订单相关商品信息
            $pro_list = M('TranUorder t')->field('t.*, c.price as tax_rate')->join('left join mk_category_list c on c.id = t.category_two')->where(array('t.lid'=>$info['id'], 't.delete_time' => array('exp', 'is null')))->select();

            //tax_rate 为百分比
            if($center['tax_kind'] == '1'){

                //需要根据二级类别的税率计算税金
                foreach($pro_list as $ko=>$item){
                    $total_tax += floatval($item['number']) * floatval($item['tax_rate']) * floatval($item['price']) / 100;//统计税金 以便保存
                    $pro_list[$ko]['tax'] = floatval($item['tax_rate']) * floatval($item['price']) / 100;//计算单价的税金
                }

            }else{//tax_rate 为固定值

                //需要根据二级类别的税值计算税金
                foreach($pro_list as $ko=>$item){
                    $total_tax += floatval($item['number']) * floatval($item['tax_rate']);//统计税金 以便保存
                    $pro_list[$ko]['tax'] = floatval($item['tax_rate']);//计算单价的税金
                }
            }

        }else{

            //订单相关商品信息
            $pro_list = M('TranUorder')->where(array('lid'=>$info['id'], 'delete_time'=>array('exp', 'is null')))->select();
        }

        $info['tax']   = sprintf("%.2f", $total_tax);//总税金
        
        $msg = $this->getMsg($info, $type);
        return $res = array('info'=>$info, 'pro_list'=>$pro_list, 'msg'=>$msg, 'center'=>$center);
    }

    /**
     * 获取物流信息
     * @param  [type] $info [description]
     * @return [type]       [description]
     */
    public function getMsg($info, $type){

        $ulogs = array();
        $ilogs = array();
        if($type != 'index'){
            //根据MKNO查询order_no
            $order_no = M('TranUlist')->where(array('MKNO'=>$info['MKNO'], 'delete_time'=>array('exp', 'is null')))->getField('order_no');
            // mk_u_logs  mk_tran_list 是没有order_no
            $ulogs = M('ULogs')->where(array('order_no'=>$order_no))->order('id desc,create_time desc')->select();
            
            // mk_il_logs
            $ilogs = M('IlLogs')->where(array('MKNO'=>$info['MKNO']))->order('create_time desc')->select();
        }else{
            // mk_u_logs
            $ulogs = M('ULogs')->where(array('order_no'=>$info['order_no']))->order('id desc,create_time desc')->select();

            // mk_il_logs  因为打印状态为200才会有MKNO的，所以需要判断是否等于200
            if($info['print_state'] == '200') $ilogs = M('IlLogs')->where(array('MKNO'=>$info['MKNO']))->order('create_time desc')->select();
        }

        if($ulogs == '' || !is_array($ulogs)) $ulogs = array();
        if($ilogs == '' || !is_array($ilogs)) $ilogs = array();

        // 合拼数组
        $msg = array_merge($ilogs, $ulogs);

        return $msg;//array('ulogs'=>$ulogs,'ilogs'=>$ilogs);
    }

    // /**
    //  * 更新修改
    //  * @param  [type] $id   [id]
    //  * @param  [type] $data [数据]
    //  * @return [type]       [description]
    //  */
    // public function _update($id,$data,$username){

    //     $checkFirst = M('TranUlist')->where(array('id'=>$id))->find();

    //     //若此单号不存在(可能已被删除的情况下)
    //     if(!$checkFirst){
    //         return $result = array('state'=>'no', 'msg'=>'参数错误，请刷新再试');

    //     }

    //     //IL_state < 200  才可以修改
    //     // if($checkFirst['IL_state'] >= '200'){
    //     //     return $result = array('state' => 'no', 'msg' => '非法操作');
    //     // }

    //     /**记录日志 **/
    //     // $list = '';
    //     // if($data['receiver'] != $checkFirst['receiver']){
    //     //     $list = '收件人：'.$checkFirst['receiver']." => ".$data['receiver']."；";
    //     // }
    //     // if($data['reTel'] != $checkFirst['reTel']){
    //     //     $list = $list.'收件电话：'.$checkFirst['reTel']." => ".$data['reTel']."；";
    //     // }
    //     // if($data['reAddr'] != $checkFirst['reAddr']){
    //     //     $list = $list.'收件地址：'.$checkFirst['reAddr']." => ".$data['reAddr']."；";
    //     // }

    //     $res = M('TranUlist')->where(array('id'=>$id))->limit(1)->save($data);
    //     if($res){
    //         // //保存操作记录到日志
    //         // $recordData['tid']          = $id;
    //         // $recordData['auto_Indent1'] = $checkFirst['auto_Indent1'];
    //         // $recordData['auto_Indent2'] = $checkFirst['auto_Indent2'];
    //         // $recordData['MKNO']         = $checkFirst['MKNO'];
    //         // $recordData['STNO']         = $checkFirst['STNO'];
    //         // $recordData['operate_user'] = $username;
    //         // $recordData['t_optime']     = $checkFirst['optime'];
    //         // $recordData['t_IL_State']   = $checkFirst['IL_state'];
    //         // $recordData['remark']       = '会员操作修改';//$remark;
    //         // $recordData['optype']       = '修改';
    //         // $recordData['change_item']  = $list;
    //         // M('DailyRecord')->add($recordData);

    //         return $result = array('state'=>'yes','msg'=>'更新成功');
    //     }else{
    //         return $result = array('state'=>'no','msg'=>'更新失败');
    //     }
    //     /**记录日志End **/
        
    // }

    /**
     * 订单删除
     * @param  [type] $id [id]
     * @return [type]     [description]
     */
    public function _delete($id,$MKNO,$username){
        $checkFirst = M('TranUlist')->where(array('id'=>$id, 'delete_time'=>array('exp', 'is null')))->find();

        if(!$checkFirst){
            $result = array('state'=>'no', 'msg'=>'参数错误，请刷新再试', 'code'=>'ErrorParameter');
            return $result;
        }

        // //IL_state < 12  才可以删除
        // if($checkFirst['IL_state'] >= '12' && $checkFirst['candel'] == '0'){
        //     $result = array('state' => 'no', 'msg' => '非法操作');
        //     return $result; 
        // }
        
        //查找tran_order里面是否有这个lid
        $lid = M('TranUorder')->where(array('lid'=>$id, 'delete_time'=>array('exp', 'is null')))->select();

        $Model = M();   //实例化
        $Model->startTrans();//开启事务

        //il_logs有这个MKNO，tran_order也有此lid，则都删除
        if($lid){

            //备份
            // foreach($lid as $item){
            //     M('TranUorderBackup')->add($item);
            // }
            
            //删除
            // $dels = M('TranUorder')->where(array('lid'=>$id))->delete();
            $dels = M('TranUorder')->where(array('lid'=>$id, 'delete_time'=>array('exp', 'is null')))->save(['delete_time'=>date('Y-m-d H:i:s')]);

        }else if(!$lid){

            //tran_order没有对应$id的lid,则tran_list里面的此ID数据可以安心删了
            //备份
            // M('TranUlistBackup')->add($checkFirst);
            //删除
            M('TranUlist')->where(array('id'=>$id))->delete();     

                // //保存操作记录到日志
                // $recordData['tid']          = $id;
                // $recordData['auto_Indent1'] = $checkFirst['auto_Indent1'];
                // $recordData['auto_Indent2'] = $checkFirst['auto_Indent2'];
                // $recordData['MKNO']         = $checkFirst['MKNO'];
                // $recordData['STNO']         = $checkFirst['STNO'];
                // $recordData['operate_user'] = $username;
                // $recordData['t_optime']     = $checkFirst['optime'];
                // $recordData['t_IL_State']   = $checkFirst['IL_state'];
                // $recordData['remark']       = '用户操作删除';
                // $recordData['optype']       = '删除';
                // M('DailyRecord')->add($recordData);

            $Model->commit();//提交事务成功

            $result = array('state' => 'yes', 'msg' => '删除成功', 'code'=>'DeleteSuccess');
            
            return $result;

        }
        
        //如果已经在il_logs或tran_order上进行了删除，则tran_list也要删除对应的ID数据
        if($dels){

            //备份
            // M('TranUlistBackup')->add($checkFirst);
            //删除
            // M('TranUlist')->where(array('id'=>$id))->delete();
            M('TranUlist')->where(array('id'=>$id, 'delete_time'=>array('exp', 'is null')))->save(['delete_time'=>date('Y-m-d H:i:s')]);

                // //保存操作记录到日志
                // $recordData['tid']          = $id;
                // $recordData['auto_Indent1'] = $checkFirst['auto_Indent1'];
                // $recordData['auto_Indent2'] = $checkFirst['auto_Indent2'];
                // $recordData['MKNO']         = $checkFirst['MKNO'];
                // $recordData['STNO']         = $checkFirst['STNO'];
                // $recordData['operate_user'] = $username;
                // $recordData['t_optime']     = $checkFirst['optime'];
                // $recordData['t_IL_State']   = $checkFirst['IL_state'];
                // $recordData['remark']       = '用户操作删除';
                // $recordData['optype']       = '删除';
                // M('DailyRecord')->add($recordData);

            $Model->commit();//提交事务成功

            $result = array('state' => 'yes', 'msg' => '删除成功', 'code'=>'DeleteSuccess');

            return $result;
        }else{//其他情况，则终止所有删除行为

            $Model->rollback();//事务有错回滚

            $result = array('state' => 'no', 'msg' => '删除失败', 'code'=>'DeleteFalse');

            return $result;
        }

    }

    /**
     * 进入包裹列表界面前 先执行清理货品声明为空的订单
     * @return [type] [description]
     */
    public function cleanEmptyOrder(){

        // M('TranUlist')->where(array('number'=>'0'))->order('ctime desc')->limit(1)->delete();
        // M('TranUlist')->where(array('number'=>'0'))->delete();
        M('TranUlist')->where(array('number'=>'0', 'delete_time'=>array('exp', 'is null')))->save(['delete_time'=>date('Y-m-d H:i:s')]);
    }

}