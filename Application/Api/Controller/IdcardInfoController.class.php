<?php
namespace Api\Controller;
use Think\Controller\HproseController;

/**
 * 公共类
 * 对身份证图片库进行操作
 * 补填身份证图片
 * 其它工具方法
 * 上传身份证后，对其它订单上传状态的更新
 */
class IdcardInfoController extends HproseController{

    // 保存身份证号码、身份证图片到标准库
    public function idno_save($data){

        $rule = [
            'user_id', 'true_name', 'tel', 'idno', 'front_id_img', 'back_id_img',
        ];

        foreach($rule as $k=>$v){
            if(empty($data[$v])){
                return false;
            }
        }
        
        // 收集数据
        $row['user_id'] = $data['user_id'];
        $row['true_name'] = $data['true_name'];
        $row['idno'] = $data['idno'];
        $row['tel'] = $data['tel'];
        $row['front_id_img'] = $data['front_id_img'];
        $row['small_front_img'] = $data['small_front_img'];
        $row['back_id_img'] = $data['back_id_img'];
        $row['small_back_img'] = $data['small_back_img'];

        if(!empty($data['valid_date_start'])){
            // 如果还有识别的信息
            $row['sex'] = $data['sex'];
            $row['nation'] = $data['nation'];
            $row['birth'] = $data['birth'];
            $row['address'] = $data['address'];
            $row['authority'] = $data['authority'];
            $row['valid_date_start'] = $data['valid_date_start'];
            $row['valid_date_end'] = $data['valid_date_end'];

            $valid_date = strtotime($data['valid_date_end']);
            $current = time();
            $current += 60 * 60 * 24 * 14;  //结束时间要大于当前时间+ 14天  才能保存到标准库中
            if($current >=  $valid_date){
                return false;
            }
        }

        // 判断是否存在历史数据，如果没有历史数据就添加到身份证库中
        $where['true_name']        = $data['true_name'];
        $where['tel']               = $data['tel'];
        $where['idno']              = $data['idno'];
        $where['idcard_status']    = 1;
        $res = M('user_extra_info')->where($where)->find();
        if(!empty($res)){
            // 已存在，无需新增
            $extra_id = $res['id'];
        }else{
            // 需要新增
            $extra_id = M('user_extra_info')->data($row)->add();
        }

        // 更新状态
        $file['front_id_img']            = $data['front_id_img'];
        $file['small_front_img']         = $data['small_front_img'];
        $file['back_id_img']              = $data['back_id_img'];
        $file['small_back_img']           = $data['small_back_img'];
        $this->idcard_status_update($row['user_id'], $row['true_name'], $row['tel'], $row['idno'], $file, $extra_id);
        
        return $extra_id;

    }

    // 根据ID获取身份证信息
    public function get_idcard_info_id($idcard_id){
        if(empty($idcard_id)){
            return [];
        }
        $where['id'] = $idcard_id;
        // $where['status'] = 10;

//        define('ONE_DAY', 24*60*60);

//        $valid_date_end = date('Ymd', time() + 14*ONE_DAY);
//        $where['valid_date_end'] = array('gt', $valid_date_end);
//        $where['_string'] = 'valid_date_end > '.$valid_date_end.' AND valid_date_end is not null';

        $where['idcard_status'] = 1;
        $res = M('user_extra_info')->field(
            'id,front_id_img,small_front_img,back_id_img,small_back_img,true_name as name,idno,sex,nation,birth,address,authority,valid_date_start,valid_date_end'
        )->where($where)->find();
        return $res;

    }


    // 查询身份证库的数据
    public function get_idcard_info($name, $tel, $idcardno ='', $user_id = 0){

        if(empty($name) || empty($tel)){
            return array();
        }

        $where['true_name'] = $name;
        $where['tel'] = $tel;
        if(!empty($idcardno)){
            $where['idno'] = $idcardno;
        }
        $where['idcard_status'] = 1;

        define('ONE_DAY', 24*60*60);
        $valid_date_end = date('Ymd', time() + 14*ONE_DAY);
        $where['_string'] = 'valid_date_end > '.$valid_date_end.' AND valid_date_end is not null';

        // 先查询已审核
        $where['status'] = 10;
        $res = M('user_extra_info')->field(
            'id,front_id_img,small_front_img,back_id_img,small_back_img,true_name as name,idno,sex,nation,birth,address,authority,valid_date_start,valid_date_end'
        )->where($where)->select();
        if(empty($user_id) || !empty($res)){
            return $res;
        }

        // 如果有user_id，且没有查询到已审核的信息，则查询该用户提供的未审核的身份证
        $where['status'] = 0;
        $where['user_id'] = $user_id;
        $where['idcard_status'] = 1;
        unset($where['_string']);
        $where['_string'] = "front_id_img is not null and back_id_img is not null";
        $res = M('user_extra_info')->field(
            'id,front_id_img,small_front_img,back_id_img,small_back_img,true_name as name,idno,sex,nation,birth,address,authority,valid_date_start,valid_date_end'
        )->where($where)->select();

        return $res;
    }

    // 查询信息 - 已废弃，但不可删除
    public function get_idcard_info_no_examine($name, $tel, $idcardno ='', $user_id = 0){
        return $this->get_idcard_info($name, $tel, $idcardno, $user_id);
    }

    // 判断此数据是否在标准库中
    public function is_exist_idcard($name, $tel, $idno){
        $where['true_name'] = $name;
        $where['tel'] = $tel;
        $where['idno'] = $idno;
        $where['idcard_status'] = 1;
        $res = M('user_extra_info')->where($where)->find();

        $audited = $res['status'] == 10 ? true :false;
        $unaudited = $audited ? false : true;
        $is_existence = !empty($res) ? true :false;
        return array(
            'audited'       => $audited,   //已审核
            'unaudited'     => $unaudited, //未审核
            'is_existence'  => $is_existence,   //是否存在
        );

    }

    // 补填身份证号码、身份证图片（如果需要，会被添加到标准库）
    // 参数为 ulist.id
    public function idno_save_order($order_id, $data){


        $info = ((!empty($data['file'])) ? array(
            // 'receiver' => $data['receiver'],
            'idno' => $data['idno'],
            'front_id_img' => $data['file']['front_id_img'],
            'back_id_img' => $data['file']['back_id_img'],
            'small_front_img' => $data['file']['small_front_img'],
            'small_back_img' => $data['file']['small_back_img'],
            'id_img_status' => '100',
            'id_no_status' => '100',
            'idno_auth' => '1',
        ) : array(
            // 'receiver' => $data['receiver'],
            'idno' => $data['idno'],
            'id_img_status' => '100',
            'id_no_status' => '100',
            'idno_auth' => '1',
        ));
        
        // 修改ulist表
        M('TranUlist')->where(array('id'=>$order_id))->save($info);

        // 如果已打单，则同样需要修改list表
        $order_res = $this->getInfoByOrderId($order_id);
        $MKNO = $order_res['MKNO'];
        if(!empty($MKNO)){
            M('TranList')->where(array('MKNO'=>$MKNO))->save($info);
        }

//        // 添加到标准库
//        $idcard_info = $data['idcard_info'];
//        if(!empty($idcard_info)){
//            unset($idcard_res['name']);
//            unset($idcard_res['idcard']);
//            $idcard_info['user_id'] = $order_res['user_id'];
//            $idcard_info['front_id_img'] = $info['front_id_img'];
//            $idcard_info['small_front_img'] = $info['small_front_img'];
//            $idcard_info['back_id_img'] = $info['back_id_img'];
//            $idcard_info['small_back_img'] = $info['small_back_img'];
//            $idcard_info['true_name'] = $data['receiver'];
//            $idcard_info['idno'] = $data['idno'];
//            $idcard_info['tel'] = $order_res['tel'];
//            $this->idno_save($idcard_info);
//        }
        $idcard_info = [];
        $idcard_info['user_id'] = $order_res['user_id'];
        $idcard_info['front_id_img'] = $info['front_id_img'];
        $idcard_info['small_front_img'] = $info['small_front_img'];
        $idcard_info['back_id_img'] = $info['back_id_img'];
        $idcard_info['small_back_img'] = $info['small_back_img'];
        $idcard_info['true_name'] = $data['receiver'];
        $idcard_info['idno'] = $data['idno'];
        $idcard_info['tel'] = $order_res['tel'];

        $extra_id = $this->idno_save($idcard_info);
        if(!empty($extra_id)){
            M('TranUlist')->where(array('id'=>$order_id))->save(array('lib_idcard'=>$extra_id));
        }

        return true;

    }


    // 补填身份证号码、身份证图片（如果需要，会被添加到标准库）
    // 参数为 mkno 或者 order_no
    public function idno_save_mkno_or_orderno($no, $data){

        if(preg_match('/^MK[a-zA-Z0-9]+$/',$no)){
            $no_type = 'MKNO';
        }else{
            $no_type = 'order_no';
        }

        $order_res = M('TranUlist')->where(array($no_type=>$no))->find();

        $info = ((!empty($data['file'])) ? array(
            // 'receiver' => $data['receiver'],
            'idno' => $data['idno'],
            'front_id_img' => $data['file']['front_id_img'],
            'back_id_img' => $data['file']['back_id_img'],
            'small_front_img' => $data['file']['small_front_img'],
            'small_back_img' => $data['file']['small_back_img'],
            'id_img_status' => '100',
            'id_no_status' => '100',
            'idno_auth' => '1',
        ) : array(
            // 'receiver' => $data['receiver'],
            'idno' => $data['idno'],
            'id_img_status' => '100',
            'id_no_status' => '100',
            'idno_auth' => '1',
        ));
        
        // 修改ulist表和list表
        M('TranUlist')->where(array($no_type=>$no))->save($info);
        if($no_type == 'order_no'){
            M('TranList')->where(array('auto_Indent2'=>$no))->save($info);
        }else{
            M('TranList')->where(array('MKNO'=>$no))->save($info);
        }

//        // 添加到标准库
//        $idcard_info = $data['idcard_info'];
//        if(!empty($idcard_info)){
//            unset($idcard_res['name']);
//            unset($idcard_res['idcard']);
//            $idcard_info['user_id'] = $order_res['user_id'];
//            $idcard_info['front_id_img'] = $info['front_id_img'];
//            $idcard_info['small_front_img'] = $info['small_front_img'];
//            $idcard_info['back_id_img'] = $info['back_id_img'];
//            $idcard_info['small_back_img'] = $info['small_back_img'];
//            $idcard_info['true_name'] = $data['receiver'];
//            $idcard_info['idno'] = $data['idno'];
//            $idcard_info['tel'] = $order_res['reTel'];
//            $this->idno_save($idcard_info);
//        }
        $idcard_info = [];
        $idcard_info['user_id'] = $order_res['user_id'];
        $idcard_info['front_id_img'] = $info['front_id_img'];
        $idcard_info['small_front_img'] = $info['small_front_img'];
        $idcard_info['back_id_img'] = $info['back_id_img'];
        $idcard_info['small_back_img'] = $info['small_back_img'];
        $idcard_info['true_name'] = $data['receiver'];
        $idcard_info['idno'] = $data['idno'];
        $idcard_info['tel'] = $order_res['reTel'];

        $extra_id = $this->idno_save($idcard_info);

        if(!empty($extra_id)){
            M('TranUlist')->where(array($no_type=>$no))->save(array('lib_idcard'=>$extra_id));
        }

        return true;

    }


    // 只修改身份证号码 - order_id
    public function idno_update_by_id($order_id, $idno){

        $info = array(
            'idno'=>$idno,
            'id_no_status' => '100',
            'idno_auth' => '1',
        );

        // 修改ulist表
        M('TranUlist')->where(array('id'=>$order_id))->save($info);

        // 如果已打单，则同样需要修改list表
        $order_res = $this->getInfoByOrderId($order_id);
        $MKNO = $order_res['MKNO'];
        if(!empty($MKNO)){
            M('TranList')->where(array('MKNO'=>$MKNO))->save($info);
        }

    }

    // 只修改身份证号码 - order_no
    public function idno_update_by_no($no, $idno){

        if(preg_match('/^MK[a-zA-Z0-9]+$/',$no)){
            $no_type = 'MKNO';
        }else{
            $no_type = 'order_no';
        }

        $info = array('idno'=>$idno, 'idno_auth'=>'1');

        // 修改ulist表和list表
        M('TranUlist')->where(array($no_type=>$no))->save($info);
        if($no_type == 'order_no'){
            M('TranList')->where(array('auto_Indent2'=>$no))->save($info);
        }else{
            M('TranList')->where(array('MKNO'=>$no))->save($info);
        }

    }



    // 根据订单id获取收件人信息
    public function getInfoByOrderId($order_id)
    {
        $uList = M('TranUlist')->where(array('id'=>$order_id))->find();
        if(empty($uList))
        {
            return false;
        }
        $receiver = $uList['receiver'];
        $MKNO = empty($uList['MKNO']) ? '' : $uList['MKNO'];
        $idno = $uList['idno'];
        $order_no = $uList['order_no'];
        return [
            'receiver' => $receiver,
            'MKNO' => $MKNO,
            'idno' => $idno,
            'order_no' => $order_no,
            'front_id_img' => $uList['front_id_img'],
            'small_front_img' => $uList['small_front_img'],
            'back_id_img' => $uList['back_id_img'],
            'small_back_img' => $uList['small_back_img'],
            'tel' => $uList['reTel'],
            'user_id' => $uList['user_id'],
            'id_img_status' => $uList['id_img_status'],
            'id_no_status' => $uList['id_no_status'],
            'idno_auth' => $uList['idno_auth'],
        ];
    }
    
    // 根据mkno获取收件人信息
    public function getInfoByMKNO($mkno)
    {
        $uList = M('TranUlist')->where(array('MKNO'=>$mkno))->find();
        if(empty($uList))
        {
            return false;
        }
        $receiver = $uList['receiver'];
        $MKNO = empty($uList['MKNO']) ? '' : $uList['MKNO'];
        $idno = $uList['idno'];
        $order_no = $uList['order_no'];
        return [
            'receiver' => $receiver,
            'MKNO' => $MKNO,
            'idno' => $idno,
            'order_no' => $order_no,
            'front_id_img' => $uList['front_id_img'],
            'small_front_img' => $uList['small_front_img'],
            'back_id_img' => $uList['back_id_img'],
            'small_back_img' => $uList['small_back_img'],
            'tel' => $uList['reTel'],
            'user_id' => $uList['user_id'],
            'id_img_status' => $uList['id_img_status'],
            'id_no_status' => $uList['id_no_status'],
            'idno_auth' => $uList['idno_auth'],
        ];
    }

    // 根据订单号获取收件人信息
    public function getInfoByOrderNo($order_no)
    {
        $uList = M('TranUlist')->where(array('order_no'=>$order_no))->find();
        if(empty($uList))
        {
            return false;
        }
        $receiver = $uList['receiver'];
        $MKNO = empty($uList['MKNO']) ? '' : $uList['MKNO'];
        $idno = $uList['idno'];
        $orderNo = $uList['order_no'];
        return array(
            'receiver' => $receiver,
            'MKNO' => $MKNO,
            'idno' => $idno,
            'order_no' => $orderNo,
            'front_id_img' => $uList['front_id_img'],
            'small_front_img' => $uList['small_front_img'],
            'back_id_img' => $uList['back_id_img'],
            'small_back_img' => $uList['small_back_img'],
            'tel' => $uList['reTel'],
            'user_id' => $uList['user_id'],
            'id_img_status' => $uList['id_img_status'],
            'id_no_status' => $uList['id_no_status'],
            'idno_auth' => $uList['idno_auth'],
        );
    }


    // 在写入标准库后，调用此方法更新同一用户下其它订单的身份证状态
    private function idcard_status_update($user_id, $receiver, $tel, $idno, $file, $extra_id){

        if(!empty($user_id) && !empty($receiver) && !empty($tel) && !empty($idno) && !empty($file)){
            M('TranUlist')->where(array(
                'user_id' => $user_id,
                'receiver' => $receiver,
                'tel' => $tel,
                'delete_status' => 1,           // 未删除
//                'certify_upload_type' => 2,     // 收件人上传
                'lib_idcard' => 0,              // 没有身份证库id
                'id_img_status' => 0,           // 未上传
            ))->save(array(
//                'idno' => $idno,
                'front_id_img' => $file['front_id_img'],
                'small_front_img' => $file['small_front_img'],
                'back_id_img' => $file['back_id_img'],
                'small_back_img' => $file['small_back_img'],
                'id_img_status' => '100',
//                'id_no_status' => '100',
                'lib_idcard' => $extra_id,
            ));

            M('TranUlist')->where(array(
                'user_id' => $user_id,
                'receiver' => $receiver,
                'tel' => $tel,
                'delete_status' => 1,           // 未删除
//                'lib_idcard' => 0,              // 没有身份证库id
//                'id_img_status' => 200,         // 无需上传
                'idno_auth' => '0',             // 未实名认证
                'id_no_status' => '0',          // 需要填写身份证号码
            ))->save(array(
                'idno' => $idno,
                'id_no_status' => '100',
                'idno_auth' => '1',
            ));

//            M('user_addressee')->where(array(
//                'user_id' => $user_id,
//                'name' => $receiver,
//                'tel' => $tel,
//                'cre_num' => $idno,
//                'delete_status' => 1,           // 未删除
//                'is_supplement' => 0,           // 没有补填过的
//            ))->save(array(
//                'id_card_front' => $file['front_id_img'],
//                'id_card_back' => $file['back_id_img'],
//                'id_card_front_small' => $file['small_front_img'],
//                'id_card_back_small' => $file['small_back_img'],
//                'is_supplement' => '1',
//            ));
        }

    }

    // 根据线路id来查询线路是否需要填写身份证号码已经是否需要上传身份证图片
    public function get_line_info_idcard($line_id){

        if(empty($line_id)){
            return array();
        }

        return M('transit_center')->field('input_idno as idno_isset, member_sfpic_state as idcard_isset')->where(array('id'=>$line_id))->find();

    }
    

}