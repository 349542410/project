<?php
/**
 * 根据线路ID判断输出 bclist，cclist，picupload  3 个html
 */
namespace Api\Controller;
use Think\Controller\HproseController;
class OrderlistController extends HproseController{

    /**
     * [getline description]
     * @param  [type] $TranKd   [线路ID]
     * @param  [type] $order_id [mk_tran_list.id]
     * @param  [type] $type     [判断是否为下单、再次下单、编辑等页面发起的请求]
     * @return [type]           [description]
     */
    public function getline($TranKd, $order_id, $type){
        $line = M('TransitCenter')->where(array('id'=>$TranKd))->find();

        $pro_list = array();//默认
        $picInfo  = array();//默认

        //根据mk_tran_list.id找出 mk_tran_ulist.id
        if($order_id != ''){
        	
        	if(in_array($type, array('index','newOrder'))){

                /**
                    新版 start
                 */

                $u_id = M('TranList t')->join('left join mk_tran_ulist u on u.MKNO = t.MKNO')
                                        ->where(array('t.id'=>$order_id))
                                        ->getfield('u.id');
                if(!empty($u_id)){
                    // 如果是运输中的再次下单
                    $pro_list = M('TranUorder')->field('brand,detail,catname,number,price,coin,remark,category_one,category_two,product_id,unit,source_area,is_suit,spec_unit')->where(array('lid'=>$u_id))->select();
                }else{
                    // 其他的再次下单
                    $pro_list = M('TranUorder')->field('brand,detail,catname,number,price,coin,remark,category_one,category_two,product_id,unit,source_area,is_suit,spec_unit')->where(array('lid'=>$order_id))->select();
                }


                /**
                新版 end
                 */


	        	/**
	        	    旧版 start
                 */



//                $order = M('TranList t')->field('u.id')->join('left join mk_tran_ulist u on u.MKNO = t.MKNO')->where(array('t.id'=>$order_id))->find();
//
//                //根据mk_tran_ulist.id 找出该订单的货品列表
//                $pro_list = M('TranUorder')->field('brand,detail,catname,number,price,coin,remark,category_one,category_two,product_id,unit,source_area')->where(array('lid'=>$order['id']))->select();


                /**
                旧版 end
                 */



                //如果是需要上传证件照，则读取已上传的证件照信息
                if($line['member_sfpic_state'] == '1'){
                    $picInfo = M('TranUlist t')->field('e.front_id_img,e.back_id_img,e.front_file_name,e.back_file_name')->join('left join mk_user_extra_info e on e.idno=t.idno and e.true_name=t.receiver and e.user_id=t.user_id')->where(array('t.id'=>$order_id))->find();
                }

        	}else{

        		//如果是【编辑页面】发起请求，直接根据$order_id找出该订单的货品列表
        		$pro_list = M('TranUorder')->field('oid,brand,detail,catname,number,price,coin,remark,category_one,category_two,product_id,unit,source_area,is_suit,spec_unit')->where(array('lid'=>$order_id))->select();

//                $last_sql = M('')->getLastSql();

                //如果是需要上传证件照，则读取已上传的证件照信息
                if($line['member_sfpic_state'] == '1'){
                    // $picInfo = M('TranUlist t')->field('e.front_id_img,e.back_id_img,e.front_file_name,e.back_file_name')->join('left join mk_user_extra_info e on e.idno=t.idno and e.true_name=t.receiver')->where(array('t.id'=>$order_id))->find();
                    $picInfo = M('TranUlist t')->field('e.front_id_img,e.back_id_img,e.front_file_name,e.back_file_name')->join('left join mk_user_extra_info e on e.idno=t.idno and e.true_name=t.receiver and e.user_id=t.user_id')->where(array('t.id'=>$order_id))->find();
                }
        	}

        }
        $brand = M('BrandList')->field('brand_name')->select();

        return array('line'=>$line, 'pro_list'=>$pro_list, 'picInfo'=>$picInfo, 'brand'=>$brand,$order_id,$type,$u_id);
    }

    //查出 所有类别列表
    public function cat_list($lineID=''){
        if($lineID != '') $map['TranKd']  = array('like','%,'.$lineID.',%');
        $map['is_show'] = array('eq',1);
        $map['status']  = array('eq',1);
        $cat_list = M('CategoryList')->field('id,fid,cat_name,price')->where($map)->order('sort asc, cat_name asc')->select();
        return $cat_list;
    }

    public function get_goods_name($user_id){

        $res = M('UserOrderGoodsName')->field('goods_name')->where(array('user_id'=>$user_id))->select();

        if(empty($res)){
            return array();
        }else{
            $data = array();
            foreach($res as $k=>$v){
                $data[] = $v['goods_name'];
            }
            return $data;
        }
        

    }
}