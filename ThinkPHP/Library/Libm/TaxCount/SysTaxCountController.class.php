<?php
/**
 * 公共计费公式(税金)   暂时是提供会员后台使用
 * 功能：1.获取 会员线路优惠配置， 2.运费的计算
 * 创建时间：2018-04-25
 * 创建人：jie
 */
namespace Libm\TaxCount;
use Think\Controller;
class SysTaxCountController extends Controller{

    public function index($sys_tax_arr){

        $center         = $sys_tax_arr['center'];
        $map            = $sys_tax_arr['map'];
        $RMB_Free_Duty  = $sys_tax_arr['RMB_Free_Duty'];
        $US_TO_RMB_RATE = $sys_tax_arr['US_TO_RMB_RATE'];

        $tax = 0;//税金总金额  总计

        //检查该线路的 bc_state 是否为1
        if($center['bc_state'] == '1'){
            //订单相关商品信息
            $goods = M('TranUorder t')->field('t.oid,t.lid,t.number,t.weight,t.remark,t.auto_Indent1,t.auto_Indent2,p.show_name,p.name as detail,p.brand,p.hs_code,p.hgid,p.price,p.coin,p.unit,p.source_area,p.specifications,p.barcode,p.unit,p.source_area,c.cat_name as catname,c.price as tax_price')->join('left join mk_product_list p on p.id = t.product_id')->join('left join mk_category_list c on c.id = p.cat_id')->where($map)->select();


            foreach($goods as $k=>$item){
                $tax_price = number_format(($item['number'] * $item['tax_price']),2,'.','');//统计税金 以便保存
                $goods[$k]['tax_price'] = $tax_price;//各列商品自身的税金额小计
                $tax += $tax_price;//统计税金 以便保存
            }
            
        }else if($center['cc_state'] == '1'){
            //订单相关商品信息
            $goods = M('TranUorder t')->field('t.*,t.catname as specifications,c.cat_name as catname, c.price as tax_rate')->join('left join mk_category_list c on c.id = t.category_two')->where($map)->select();

            //tax_rate 为百分比
            if($center['tax_kind'] == '1'){

                //需要根据二级类别的税率计算税金
                foreach($goods as $k=>$item){
                    $tax_price = number_format(($item['number'] * $item['tax_rate'] * $item['price'] / 100),2,'.','');
                    $goods[$k]['tax_price'] = $tax_price;//各列商品自身的税金额小计

                    $tax += $tax_price;//统计税金 以便保存
                    $goods[$k]['show_name'] = $item['detail'];
                }

                //根据汇率计算出美元免税的额度
                $free_duty = $RMB_Free_Duty / $US_TO_RMB_RATE;

                // 2017-09-19  整单税金<=7美元的时候，直接免税；>7的直接显示所计算得到的税金（不用减7）
                if(sprintf("%.2f", $tax) <= sprintf("%.2f", $free_duty)){
                    $tax = '0';
                }
                
            }else{//tax_rate 为固定值

                //需要根据二级类别的税值计算税金
                foreach($goods as $k=>$item){
                    $tax_price = number_format(($item['number'] * $item['tax_rate']),2,'.','');
                    $goods[$k]['tax_price'] = $tax_price;//各列商品自身的税金额小计
                    
                    $tax += $tax_price;//统计税金 以便保存
                    $goods[$k]['show_name'] = $item['detail'];
                }
            }

        }else{
            //订单相关商品信息
            $goods = M('TranUorder t')->field('t.*,t.catname as specifications')->where($map)->select();
            foreach($goods as $k=>$item){
                $goods[$k]['show_name'] = $item['detail'];
                $goods[$k]['tax_price'] = '0';//各列商品自身的税金额小计
            }
        }

        return array('tax'=>sprintf("%.2f", $tax), 'goods'=>$goods);
    }

}