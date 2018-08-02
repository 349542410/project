<?php
/**
 * EMS推送订单失败时修改订单信息
 * xieyiyi
 */
namespace Api\Controller;
use Think\Controller\HproseController;

class EmsOrderController extends HproseController
{
    public function _index($lading_id,$line,$p,$ePage)
    {
        //获取提单信息
        $lading_where['TranKd']      = $line;
        $lading_where['id']          = $lading_id;
        $lading_where['zzState']     = 2;
        $lading_info = M('TransitLading')->where($lading_where)->field('id,lading_no,order_state')->find();
        if(empty($lading_info)){
            return array('state'=>'no','msg'=>'数据出错');
        }else if($lading_info['order_state'] != 2){
            return array('state'=>'no','msg'=>'该提单没有需要修改的订单');
        }

        //获取批次号
        $no_where['lading_id'] = $lading_info['id'];
        $no_id = M('TransitNo')->where($no_where)->getField('id',true);

        //获取所有的订单
        $ids = implode(',',$no_id);
        $list_where['noid']          = array('in',$ids);
        $list_where['_string']       = 'pause_status!=20 OR IL_state!=400';
        $list = M('TranList')->where($list_where)->order('optime')->field('id,MKNO,STNO')->page($p.','.$ePage)->select();

        $array = array();
        $state_model = M('TranListState');
        foreach ($list as $key=>$val) {
            $tmp = $val;
            $tmp['res'] = $state_model->where(array('lid'=>$val['id']))->order('sys_time desc')->field('sys_time,ems_state,ems_return')->find();

            $json_arr = json_decode($tmp['res']['ems_return'],true);
            $tmp['res']['ems_return'] = $json_arr['desc'];

            $array[] = $tmp;
        }

        $count = M('TranList')->where($list_where)->order('optime')->count();

        return array('list'=>$array,'count'=>$count);
    }

    //处理需要传给EMS的数据
    public function _pushData($order_id,$lading_id)
    {
        //获取提单信息
        $data['lading_no'] = M('TransitLading')->where(array('id'=>$lading_id))->getField('lading_no');

        //获取订单信息
        $data['order'] = M('TranList')->where(array('id'=>$order_id))->field('id,MKNO,STNO,receiver,reTel,reAddr,province,city,town,idno,sender,sendTel,sendAddr')->find();

        $order_goods = M('TranOrder')->where(array('lid'=>$order_id))->field('barcode,number,price')->select();

        $data['order']['order_goods'] = $order_goods;

        return $data;
    }
}