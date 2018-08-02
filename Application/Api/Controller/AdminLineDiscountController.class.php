<?php
/**
 * 会员管理---线路优惠
 * 使用数据表：mk_line_discount, mk_user_list, mk_manager_list, mk_transit_center
 */
namespace Api\Controller;
use Think\Controller\HproseController;
class AdminLineDiscountController extends HproseController{

    public function _index($where,$p,$ePage){

        $map = array();
        $map['optional'] = array('eq',1);//会员可选
        $map['status']   = array('eq',1);//状态正常
        $line = M('TransitCenter')->where($map)->select();//只显示会员可视及状态正常的线路 左侧导航栏
        
        $operator_list = M('ManagerList')->field('id,name as manager_name')->select(); //操作人 列表  搜索栏

        // 线路对应的优惠列表
		$discount_list = M('LineDiscount l')->field('l.*,u.username,m.name as operator')->join('left join mk_user_list u on u.id = l.user_id')->join('left join mk_manager_list m on m.id = l.operator_id')->where($where)->page($p.','.$ePage)->select();

		$discount_count = M('LineDiscount l')->join('left join mk_user_list u on u.id = l.user_id')->join('left join mk_manager_list m on m.id = l.operator_id')->where($where)->count();
        return array('line'=>$line, 'operator_list'=>$operator_list, 'discount_list'=>$discount_list, 'discount_count'=>$discount_count);

    }
}