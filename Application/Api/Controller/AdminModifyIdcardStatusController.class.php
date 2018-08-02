<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/5/29
 * Time: 9:52
 */

namespace Api\Controller;
use Think\Controller\HproseController;
class AdminModifyIdcardStatusController extends HproseController{

    //查询会员订单信息
    public function update($data){
        if ($data['type'] ==1){
            $list = M('tran_ulist')->alias('tu')->field('tu.id, tu.id_img_status, ue.front_id_img, ue.back_id_img')->join('left join mk_user_extra_info AS ue ON tu.user_id = ue.user_id AND tu.idno = ue.idno AND tu.receiver = ue.true_name')
                ->page($data['p'],$data['epage'])->select();
            $count = M('tran_ulist')->alias('tu')->field('tu.id, tu.id_img_status, ue.front_id_img, ue.back_id_img')->join('left join mk_user_extra_info AS ue ON tu.user_id = ue.user_id AND tu.idno = ue.idno AND tu.receiver = ue.true_name')
                ->count();


        }else{
            $list = M('tran_ulist')->alias('tu')->field('tu.id, tu.id_img_status, ue.front_id_img, ue.back_id_img')->join('left join mk_user_extra_info AS ue ON tu.idno = ue.idno AND tu.receiver = ue.true_name')
                ->page($data['p'],$data['epage'])->select();
            $count = M('tran_ulist')->alias('tu')->field('tu.id, tu.id_img_status, ue.front_id_img, ue.back_id_img')->join('left join mk_user_extra_info AS ue ON tu.idno = ue.idno AND tu.receiver = ue.true_name')
                ->count();
        }
        $res['list'] = $list;
        $res['count'] = $count;

        return $res;

    }

    //更新会员订单身份证状态
    public function update_data($data){
        $res = M()->execute($data);
        return $res;
    }


}