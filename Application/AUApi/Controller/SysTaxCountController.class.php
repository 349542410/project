<?php
/**
 * 公共计费公式(税金)
 * 功能：1.获取 会员线路优惠配置， 2.运费的计算
 * 创建时间：2018-03-21
 * 创建人：jie
 */
namespace AUApi\Controller;
use Think\Controller;
class SysTaxCountController extends Controller{

    public function index($sys_tax_arr){

        $center         = $sys_tax_arr['center'];
        $info           = $sys_tax_arr['info'];
        $RMB_Free_Duty  = $sys_tax_arr['RMB_Free_Duty'];
        $US_TO_RMB_RATE = $sys_tax_arr['US_TO_RMB_RATE'];

        $tax = 0;//税金总金额

        //检查该线路的 bc_state 是否为1
        if($center['bc_state'] == '1'){
            //订单相关商品信息
            $uOrderWhere = 't.lid = ' . $info['id'] . ' AND t.delete_time is null';
            $goods = M('TranUorder t')->field('t.oid,t.lid,t.number,t.weight,t.remark,t.auto_Indent1,t.auto_Indent2,p.show_name,p.name as detail,p.brand,p.hs_code,p.hgid,p.price,p.coin,p.unit,p.source_area,p.specifications,p.barcode,p.unit,p.source_area,p.tariff_no,c.cat_name as catname,c.price as tax_price')->
            join('left join mk_product_list p on p.id = t.product_id')->
            join('left join mk_category_list c on c.id = p.cat_id')->where($uOrderWhere)->select();


            foreach($goods as $item){
                $tax += $item['number'] * $item['tax_price'];//统计税金 以便保存
            }
            
        }else if($center['cc_state'] == '1'){
            //订单相关商品信息
            $uOrderWhere = 't.lid = ' . $info['id'] . ' AND t.delete_time is null';
            $goods = M('TranUorder t')->field('t.*,t.num_unit as unit,t.catname as specifications,c.cat_name as catname, c.price as tax_rate,c.hs_code,c.hgid,c.hs_code as tariff_no')->
            join('left join mk_category_list c on c.id = t.category_two')->where($uOrderWhere)->select();

            //tax_rate 为百分比
            if($center['tax_kind'] == '1'){

                //需要根据二级类别的税率计算税金
                foreach($goods as $k=>$item){
                    $tax += $item['number'] * $item['tax_rate'] * $item['price'] / 100;//统计税金 以便保存
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
                    $tax += $item['number'] * $item['tax_rate'];//统计税金 以便保存
                    $goods[$k]['show_name'] = $item['detail'];
                }
            }

        }else{
            //订单相关商品信息
            $uOrderWhere = 't.lid = ' . $info['id'] . ' AND t.delete_time is null';
            $goods = M('TranUorder t')->field('t.*,t.catname as specifications')->where($uOrderWhere)->select();
            foreach($goods as $k=>$item){
                $goods[$k]['show_name'] = $item['detail'];
            }
        }

        // 当 id_img_status = 200 的时候，需要支付一定的附加费  20180129 jie
        if($info['id_img_status'] == '200'){
            $extra_fee = M('tran_ulist_extra_fee')->where(array('lid'=>$info['id']))->getField('extra_fee');
        }else{
            $extra_fee = '0';
        }

        return array('tax'=>sprintf("%.2f", $tax), 'goods'=>$goods, 'extra_fee'=>sprintf("%.2f", $extra_fee));
    }

}