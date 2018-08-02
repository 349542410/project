<?php
/**
 * 充值记录
 */
namespace Api\Controller;
use Think\Controller\HproseController;
class WebRechargeController extends HproseController {
    protected $crossDomain =    true;
    protected $P3P         =    true;
    protected $get         =    true;
    protected $debug       =    true;

    /**
     * 查 充值记录总数 和 用户余额
     * @param  [type] $UID   [查询条件]
     * @return [type]        [description]
     */
    public function firstCount($UID,$w_where,$r_where){
        $amount = M('UserList')->where(array('id'=>$UID))->getField('amount'); //用户余额
        
        $w_where['UID'] = $UID;
        $r_where['UID'] = $UID;

        //查询充值记录的总数
        $count = M('RechargeRecord')->where($r_where)->count();

        $scount = M('WlorderRecord')->alias('a')->where($w_where)->count();

        return array('amount'=>$amount,'count'=>$count,'scount'=>$scount);
    }

    /**
     * [rechargeList 查询充值记录明细]
     * @param  [type] $UID [description]
     * @return [type]      [description]
     */
    public function rechargeList($UID,$limit,$r_where){
        //查询充值记录的分页数据列表
        $r_where['UID'] = $UID;
        $list = M('RechargeRecord')->where($r_where)->order('ordertime desc')->limit($limit)->select();

    	return $list;
    }

    /**
     * 查询订单记录
     * @param  [type] $UID   [description]
     * @param  [type] $p     [description]
     * @param  [type] $ePage [description]
     * @return [type]        [description]
     */
    public function orderList($UID,$limit,$w_where){
        $w_where['UID'] = $UID;
        $slist = M('WlorderRecord')
                ->alias('a')
                ->where($w_where)
                ->order('ordertime desc')
                ->join('left join mk_tran_ulist b on a.order_no=b.order_no')
                ->join('left join mk_transit_center c on b.TranKd=c.id')
                ->field('a.*,b.MKNO,b.STNO,b.id as order_id,b.sender,b.receiver,c.lngname,c.transit')
                ->limit($limit)
                ->select();

        // return M('')->getLastSql();

        if(empty($slist)){
            return $slist;
        }


        foreach($slist as $k=>$v){

            $extra_fee = array();

            if(!empty($v['order_id'])){
                $sum = 0;
                $extra_fee = M('TranUlistExtraFee')->field('cat_name,extra_fee')->where(array('lid'=>$v['order_id']))->select();
            }
            
            $slist[$k]['extra_fee'] = $extra_fee;

        }
        

        return $slist;
    }
    /**
     * 查询能否启用小数充值
     * @param  [type] $UID   [description]
     * @param  [type]        [description]
     * @param  [type]        [description]
     * @return [type]        [description]
     */
    public function get_decimal_recharge_statu($UID){
        $list = M('UserList')->where(array('id'=>$UID))->getField('decimal_recharge_status');
        return $list;
    }

    // 查询导出数据
    public function get_export_data($ids, $user_id){

        if(empty($ids) || empty($user_id)){
            return [];
        }

        $res = M('wlorder_record')->field('ul.order_no as order_no,ul.MKNO as MKNO,ul.STNO as STNO,tc.lngname as line_name,tc.transit as transit,wr.paytime as paytime,wr.cost_type as cost_type,ul.tax as tax,wr.freight as freight,ul.sender as sender,ul.receiver as receiver,ul.package_id as package_id')
                                ->alias('wr')
                                ->join('left join mk_tran_ulist as ul on ul.order_no=wr.order_no')
                                ->join('left join mk_transit_center as tc on ul.TranKd=tc.id')
                                ->where(['wr.id'=>['in', $ids], 'wr.UID'=>$user_id])
                                ->order('ul.ordertime desc')
                                ->select();

        return $res;

    }
}