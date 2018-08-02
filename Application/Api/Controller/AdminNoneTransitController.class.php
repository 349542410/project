<?php
/**
 * 美快优选3(没有批次号的订单数据)
 * 功能包括： 列出没有批次号的各个线路的订单总数，还有对应的各自的导出方法
 * 导出的时候也是根据BC、CC或非BC非CC来进行资料导出
 * 
 */
namespace Api\Controller;
use Think\Controller\HproseController;
class AdminNoneTransitController extends HproseController{

    /**
     * [index 获取没有批次号的各个线路的订单总数]
     * @param  [type] $map  [包含：线路id合集]
     * @return [type]       [description]
     */
	public function _index($map){
		$list = M('TranList t')->field('t.id,t.noid,t.TranKd,t.custom_status,c.name, count(t.id) as num')->join('left join mk_transit_center c on c.id=t.TranKd')->where($map)->group('t.TranKd')->select();
		return $list;
	}

    /**
     * 清关 --- 资料导出(导出成功后直接提成用户下载)
     * 导出文件，是用于手动形式报关，处理结果跟“发送”形式的报关一致
     * @return [type] [description]
     */
    public function _export_file($tcid){

    	$title = "原始单号,证件号码,总费用,订单时间,收货人,收货地址,收货人电话,省,市,县,邮编,备注,店铺货号,数量,单价,支付单号,订单号,订购人姓名,STNO,重量(lb),商品名称,海关商品报备编码,条形码,行邮税则号,货品名称,货品重量,时间,自定义单号1,自定义单号2,品牌,货币类型,计量单位,原厂地国别,备注,规格型号";

    	$line = M('transit_center')->field('bc_state,cc_state')->where(array('id'=>$tcid))->find();

    	// 找出该线路所含有的 类别
    	$map_c['TranKd'] = array('like','%,'.$tcid.',%');
    	$cate_list = M('category_list')->where($map_c)->field('id,cat_name')->select();

    	if($line['bc_state'] == '1'){
	        //因为导出的资料不需要带序号，所以从f1开始记数
	        $fit = "l.MKNO as f1,l.idno as f2,l.price as f3,ifnull(l.paytime,l.optime) as f4,l.receiver as f5,l.reAddr as f6,l.reTel as f7,l.province as f8,l.city as f9,l.town as f10,l.postcode as f11,l.notes as f12,p.hgid as f13,o.number as f14,o.price as f15,l.payno as f16,concat_ws('',l.auto_Indent2,l.auto_Indent1) as f17,l.receiver as f18,l.STNO as f19,l.weight as f20,p.detail as f21,p.hs_code as f22,p.barcode as f23,ifnull(p.tariff_no,p.hs_code) as f24,p.name as f25,p.weight as f26,p.sys_time as f27,o.auto_Indent1 as f28,o.auto_Indent2 as f29,p.brand as f30,p.coin as f31,p.unit as f32,p.remark as f33,p.source_area as f34,o.specifications as f35,p.price as f36,p.show_name as f37,p.net_weight as f38,p.rough_weight as f39,p.parameter_one as f40,p.parameter_two as f41,p.parameter_three as f42,p.parameter_four as f43,p.parameter_five as f44,o.category_one as f45,o.category_two as f46,order.att1 as f47,order.att2 as f48,order.att3 as f49,order.att4 as f50,order.att5 as f51,order.att6 as f52,order.att7 as f53,order.att8 as f54,order.att9 as f55,order.att10 as f56";
	        
	        /* 20160123 */
	        $list = M('TranList l')
	        ->field($fit)
	        ->join('LEFT JOIN mk_tran_ulist u ON u.MKNO = l.MKNO')
	        ->join('LEFT JOIN mk_tran_uorder o ON o.lid = u.id')
			->join('LEFT JOIN mk_product_list p ON p.id = o.product_id')
			->join('LEFT JOIN mk_tran_order order ON order.lid = l.id')
	        ->where(array('l.TranKd'=>$tcid,'l.noid'=>'0'))->select();

	        $title .= ",价格,名称,净重,毛重,参数一,参数二,参数三,参数四,参数五,一级类别,二级类别,att1,att2,att3,att4,att5,att6,att7,att8,att9,att10";

	        foreach($cate_list as $k1=>$v1){
	        	foreach($list as $k2=>$v2){
	        		if($v1['id'] == $v2['f45']){
	        			$list[$k2]['f45'] = $cate_list[$k1]['cat_name'];
	        		}

	        		if($v1['id'] == $v2['f46']){
	        			$list[$k2]['f46'] = $cate_list[$k1]['cat_name'];
	        		}
	        	}
	        }

    	}else if($line['cc_state'] == '1'){
	        //因为导出的资料不需要带序号，所以从f1开始记数
	        $fit = "l.MKNO as f1,l.idno as f2,l.price as f3,ifnull(l.paytime,l.optime) as f4,l.receiver as f5,l.reAddr as f6,l.reTel as f7,l.province as f8,l.city as f9,l.town as f10,l.postcode as f11,l.notes as f12,o.hgid as f13,o.number as f14,o.price as f15,l.payno as f16,concat_ws('',l.auto_Indent2,l.auto_Indent1) as f17,l.receiver as f18,l.STNO as f19,l.weight as f20,o.detail as f21,o.hs_code as f22,o.barcode as f23,o.tariff_no as f24,o.catname as f25,o.weight as f26,o.time as f27,o.auto_Indent1 as f28,o.auto_Indent2 as f29,o.brand as f30,o.coin as f31,ifnull(o.unit,o.num_unit) as f32,o.source_area as f33,o.remark as f34,o.specifications as f35,o.spec_unit as f36,o.num_unit as f37,r.category_one as f38,r.category_two as f39,o.att1 as f40,o.att2 as f41,o.att3 as f42,o.att4 as f43,o.att5 as f44,o.att6 as f45,o.att7 as f46,o.att8 as f47,o.att9 as f48,o.att10 as f49";
	        
	        /* 20160123 */
	        $list = M('TranList l')
	        ->field($fit)
	        ->join('LEFT JOIN mk_tran_ulist u ON u.MKNO = l.MKNO')
	        ->join('LEFT JOIN mk_tran_order o ON o.lid = l.id')
	        ->join('LEFT JOIN mk_tran_uorder r ON o.lid = u.id')
	        ->where(array('l.TranKd'=>$tcid,'l.noid'=>'0'))->select();

	        $title .= ",规格单位,数量单位,一级类别,二级类别,att1,att2,att3,att4,att5,att6,att7,att8,att9,att10";

	        foreach($cate_list as $k1=>$v1){
	        	foreach($list as $k2=>$v2){
	        		if($v1['id'] == $v2['f38']){
	        			$list[$k2]['f38'] = $cate_list[$k1]['cat_name'];
	        		}

	        		if($v1['id'] == $v2['f39']){
	        			$list[$k2]['f39'] = $cate_list[$k1]['cat_name'];
	        		}

	        	}
	        }

    	}else if($line['bc_state'] == '0' && $line['cc_state'] == '0'){
    		$fit = "l.MKNO as f1,l.idno as f2,l.price as f3,ifnull(l.paytime,l.optime) as f4,l.receiver as f5,l.reAddr as f6,l.reTel as f7,l.province as f8,l.city as f9,l.town as f10,l.postcode as f11,l.notes as f12,o.hgid as f13,o.number as f14,o.price as f15,l.payno as f16,concat_ws('',l.auto_Indent2,l.auto_Indent1) as f17,l.receiver as f18,l.STNO as f19,l.weight as f20,o.detail as f21,o.hs_code as f22,o.barcode as f23,o.tariff_no as f24,o.catname as f25,o.weight as f26,o.time as f27,o.auto_Indent1 as f28,o.auto_Indent2 as f29,o.brand as f30,o.coin as f31,o.unit as f32,o.remark as f33,o.source_area as f34,o.specifications as f35,o.att1 as f36,o.att2 as f37,o.att3 as f38,o.att4 as f39,o.att5 as f40,o.att6 as f41,o.att7 as f42,o.att8 as f43,o.att9 as f44,o.att10 as f45";
	        $list = M('TranList l')
	        ->field($fit)
	        ->join('LEFT JOIN mk_tran_order o ON o.lid = l.id')
			->where(array('l.TranKd'=>$tcid,'l.noid'=>'0'))->select();
			
	        $title .= ",att1,att2,att3,att4,att5,att6,att7,att8,att9,att10";			
    	}
        
        return array('list'=>$list, 'title'=>$title);
    }
}