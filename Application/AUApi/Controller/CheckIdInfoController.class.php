<?php

namespace AUApi\Controller;

use Think\Controller;

class CheckIdInfoController extends Controller
{

    public function check_id($info)
    {
        // 20180112 jie
        // 订单所属线路 要求必须 1.填写身份证号码而不需要上传身份证照片，2.必须上传身份证照片
        if (($info['input_idno'] == '1' && $info['member_sfpic_state'] == '0') || ($info['member_sfpic_state'] == '1')) {
            if (empty(trim($info['idno']))) {
                return array('state' => 'no', 'msg' => '请先填写收件人身份证号码', 'lng' => 'lack_idno');
            }

            $idno = strtoupper($info['idno']); //所有字符转成大写
            if (!certificate($idno)) {
                return array('state' => 'no', 'msg' => '收件人身份证号码格式不正确', 'lng' => 'idno_not_right');
            }
        }

        // 订单所属线路要求 必须上传身份证照片
        if ($info['member_sfpic_state'] == '1') {
            // 0   => { 1.客人未上传 }
            // 100 => { 1.无需上传证件照；2.寄件人上传完成；3.客人上传完成； }
            // 200 => { 1.均不上传，支付0.5usd }

            // 因此只需判断 id_img_status 等于 0 的时候
            if ($info['id_img_status'] == '0') {

                // 检查 mk_user_extra_info 的数据  以确认
                $check_extra = M('user_extra_info')->field('front_id_img,back_id_img')->where(array('idno' => $info['idno']))->find();
                if (empty($check_extra['front_id_img']) && empty($check_extra['back_id_img'])) {

                    return array('state' => 'no', 'msg' => '收件人的身份证照片尚未上传', 'lng' => 'lack_id_img');
                }
            }
        }

        return true;
        // 20180112 jie End
    }

}