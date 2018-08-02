<?php
/**
 * 财务流水 服务器端
 */
namespace Api\Controller;
use Think\Controller\HproseController;
class FinanceController extends HproseController{
	protected $crossDomain =	true;
	protected $P3P		   =    true;
	protected $get		   =    true;
	protected $debug 	   =    true;

    /**
     * 消费记录
     * @param  [type] $where [查询条件]
     * @return [type]        [description]
     */
    public function count($where,$p,$ePage){
        set_time_limit(0);
        $list = M('WlorderRecord r')->field('r.*,t.MKNO,t.STNO,t.id_img_status,u.username,a.id AS lid, a.IL_state,a.weight,a.ex_context,a.ex_time,a.mode,a.optime,a.auto_Indent2,s.terminal_name,c.point_name,p.manager_tname as operator,f.extra_fee')
        ->join('left join mk_tran_ulist t on t.order_no = r.order_no')
        ->join('left join mk_user_list u on u.id = r.UID')
        ->join('left join mk_tran_list a on a.MKNO = t.MKNO')
        ->join('left join mk_print_relation_order p on p.order_id = t.id')
        ->join('left join mk_self_terminal_list s on s.id = p.terminal_id')
        ->join('left join mk_collect_point c on c.id = p.point_id')
        ->join('left join mk_tran_ulist_extra_fee f on f.lid = t.id')
        // ->join('left join mk_manager_list m on m.id = p.manager_id')
        ->where($where)->group('r.id')->order('r.id desc')->page($p.','.$ePage)->select();

        $count = M('WlorderRecord r')
        ->join('left join mk_tran_ulist t on t.order_no = r.order_no')
        ->join('left join mk_user_list u on u.id = r.UID')
        ->join('left join mk_tran_list a on a.MKNO = t.MKNO')
        ->join('left join mk_print_relation_order p on p.order_id = t.id')
        ->join('left join mk_self_terminal_list s on s.id = p.terminal_id')
        ->join('left join mk_collect_point c on c.id = p.point_id')
        ->join('left join mk_tran_ulist_extra_fee f on f.lid = t.id')
        // ->join('left join mk_manager_list m on m.id = p.manager_id')
        ->where($where)->count('distinct(r.id)');

// return M()->getLastSql();
        // $line_list = M('TransitCenter')->field('id,name as linename')->select(); //线路列表 搜索栏
        set_time_limit(30);
        return array('count'=>$count, 'list'=>$list);

        // // 20180110 jie 停止使用以下查询方式
        // $list = M('WlorderRecord r')->field('r.*,t.MKNO,t.STNO,u.username,a.IL_state,a.ex_context,a.ex_time,a.mode,a.optime')->join('left join mk_tran_ulist t on t.order_no = r.order_no')->join('left join mk_user_list u on u.id = r.UID')->join('left join mk_tran_list a on a.MKNO = t.MKNO')->where($where)->order('ordertime desc')->page($p.','.$ePage)->select();
        // // $list = M('WlorderRecord w')->field('u.*')->join('right join mk_tran_ulist u on u.order_no = w.order_no')->where($where)->order('w.ordertime')->page($p.','.$ePage)->select();
        // $count = M('WlorderRecord r')->join('left join mk_tran_ulist t on t.order_no = r.order_no')->join('left join mk_user_list u on u.id = r.UID')->join('left join mk_tran_list a on a.MKNO = t.MKNO')->where($where)->count();
        // // $count = M('WlorderRecord w')->join('left join mk_tran_ulist u on u.order_no = w.order_no')->where($where)->count();
        // $line_list = M('TransitCenter')->field('id,name as linename')->select(); //线路列表 搜索栏
        // return array('count'=>$count, 'list'=>$list, 'line_list'=>$line_list);


    }

    /**
     * 弹出层
     * @param  [type] $map [查询条件]
     * @return [type]      [description]
     */
    public function _recharge_info($map){
        $info = M('RechargeRecord')->where($map)->find();
        return $info;
    }

    /**
     * 充值记录
     * @param  [type] $where [description]
     * @param  [type] $p     [description]
     * @param  [type] $ePage [description]
     * @return [type]        [description]
     */
    public function _charge($where,$p,$ePage){
        set_time_limit(0);
        $list = M('RechargeRecord r')->field('r.*,u.username,a.name as operator')->join('left join mk_user_list u on u.id = r.UID')->join('left join mk_manager_recharge_list m on m.payno = r.payno')->join('left join mk_manager_list a on a.id = m.user_id')->where($where)->order('r.ordertime desc')->page($p.','.$ePage)->select();

        $count = M('RechargeRecord r')->join('left join mk_user_list u on u.id = r.UID')->where($where)->count();
        set_time_limit(30);
        return array('count'=>$count, 'list'=>$list);
    }


    /**
     * 充值记录导出
     * @param  [type] $where [description]
     * @param  [type] $p     [description]
     * @param  [type] $ePage [description]
     * @return [type]        [description]
     */
    public function exportse($where){
        set_time_limit(0);
        $list = M('RechargeRecord r')->field('r.*,u.username,a.name as operator')
            ->join('left join mk_user_list u on u.id = r.UID')
            ->join('left join mk_manager_recharge_list m on m.payno = r.payno')
            ->join('left join mk_manager_list a on a.id = m.user_id')
            ->where($where)->order('r.id desc')->select();
        set_time_limit(30);
        return $list;

    }


    /**
     * 获取订单商品名称
     */
    public function goods($where){
        $res = M('tran_order')->field('detail	')->where($where)->select();
        return $res;

    }


    /**
     * 消费记录下载
     * @param  [type] $where [查询条件]
     * @return [type]        [description]
     */
    public function consume($where){
        set_time_limit(0);
        $list = M('WlorderRecord r')->field('r.*,t.MKNO,t.STNO,t.id_img_status,u.username, a.id AS lid, a.IL_state,a.weight,a.ex_context,a.ex_time,a.mode,a.optime,a.auto_Indent2,s.terminal_name,c.point_name,p.manager_tname as operator,f.extra_fee')
            ->join('left join mk_tran_ulist t on t.order_no = r.order_no')
            ->join('left join mk_user_list u on u.id = r.UID')
            ->join('left join mk_tran_list a on a.MKNO = t.MKNO')
            ->join('left join mk_print_relation_order p on p.order_id = t.id')
            ->join('left join mk_self_terminal_list s on s.id = p.terminal_id')
            ->join('left join mk_collect_point c on c.id = p.point_id')
            ->join('left join mk_tran_ulist_extra_fee f on f.lid = t.id')
            ->where($where)->group('r.id')->order('r.id desc')->select();
        set_time_limit(30);
        return array('list'=>$list);


    }



}