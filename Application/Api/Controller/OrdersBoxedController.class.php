<?php
/**
 * 西安物流
 * 功能包括： 
 * 
 */
namespace Api\Controller;
use Think\Controller\HproseController;

class OrdersBoxedController extends HproseController
{   
    //查询订单的装板信息
    public function tran_info($where,$p,$ePage){

        $j = 'LEFT JOIN mk_tran_list l ON s.lid = l.id';

        $list = M('TranListState s')
                ->field('s.xa_bnum,s.xa_btime,l.MKNO,l.STNO')
                ->join($j)
                ->where($where)
                ->order('s.xa_btime desc')
                ->page($p.','.$ePage)
                ->select();
            
        $count = M('TranListState s')->field('s.xa_bnum,s.xa_btime,l.MKNO,l.STNO')->join($j)->where($where)->count();

        return array('list'=>$list,'count'=>$count);
    }
    
    //20180417 改为在数据库中进行分页操作
    public function tran_info_pno($where,$p,$ePage){
        
        $arr = M('TransitNoState s')
                ->field('s.xa_bnum,s.xa_btime,l.id,l.no')
                ->join('LEFT JOIN mk_transit_no l ON s.noid = l.id')
                ->where($where)
                ->order('s.xa_btime desc')
                ->page($p.','.$ePage)                
                ->select();
        
        $list = array();

        foreach ($arr as $key => $v) {
            
            $tmp = $v;

            //获取该批次号的全部订单数量
            $tmp['tran_count'] = M('TranList')->where(array('noid'=>$v['id']))->count();

            //获取该批次号装板成功的订单数量
            $tmp['success_count'] = M()->table('mk_tran_list tran,mk_tran_list_state state')->where('tran.noid = '.$v['id'].' and state.lid = tran.id')->count();

            $list[] = $tmp;
        }

        $count = M('TransitNoState s')
                ->field('s.xa_bnum,s.xa_btime,l.id,l.no')
                ->join('LEFT JOIN mk_transit_no l ON s.noid = l.id')
                ->where($where)             
                ->count();

        return array('list'=>$list,'count'=>$count);
    }
}