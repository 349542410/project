<?php
/**
 * 会员管理---会员优惠
 * 使用数据表：mk_line_discount, mk_user_list, mk_manager_list, mk_transit_center
 */
namespace Api\Controller;
use Think\Controller\HproseController;
class AdminMemberDiscountController extends HproseController{

    public function _index($where,$p,$ePage){

        // 只显示有优惠设置的会员列表 左侧导航栏
        $member = M('LineDiscount l')->field('l.user_id,u.username')->join('left join mk_user_list u on u.id = l.user_id')->group('user_id')->select();

        $line_list = M('TransitCenter')->field('id,name as linename')->select(); //线路列表 搜索栏
        
        $operator_list = M('ManagerList')->field('id,name as manager_name')->select(); //操作人 列表

        // 线路对应的优惠列表
		$discount_list = M('LineDiscount l')->field('l.*,u.username,m.name as operator,t.name as linename')->join('left join mk_user_list u on u.id = l.user_id')->join('left join mk_manager_list m on m.id = l.operator_id')->join('left join mk_transit_center t on t.id = l.line_id')->where($where)->page($p.','.$ePage)->select();

		$discount_count = M('LineDiscount l')->join('left join mk_user_list u on u.id = l.user_id')->join('left join mk_manager_list m on m.id = l.operator_id')->join('left join mk_transit_center t on t.id = l.line_id')->where($where)->count();
        return array('member'=>$member, 'line_list'=>$line_list, 'operator_list'=>$operator_list, 'discount_list'=>$discount_list, 'discount_count'=>$discount_count);

    }
}