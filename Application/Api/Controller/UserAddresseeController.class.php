<?php

    // 收件人管理
    // liao ya di
    // create time : 2017-10-14
    // API CONTROLLER

    namespace Api\Controller;
    use Think\Controller\HproseController;

    class UserAddresseeController extends HproseController{


        //添加数据
        public function addUserAddressee($data, $idcard_info=array()){

            $addr_m = new \Api\Model\UserAddresseeModel();
            if(false === $addr_m->addr_insert($data)){
                return array(
                    'success' => false,
                    'info' => $addr_m->getError(),
                );
            }else{

                // 写入身份证图片库
                if(!empty($data['id_card_front']) && !empty($data['id_card_back']) && !empty($idcard_info)){
                    $idcard_cli = new \Api\Controller\IdcardInfoController();

                    unset($idcard_info['name']);
                    $idcard_info['front_id_img'] = $data['id_card_front'];
                    $idcard_info['small_front_img'] = $data['id_card_front_small'];
                    $idcard_info['back_id_img'] = $data['id_card_back'];
                    $idcard_info['small_back_img'] = $data['id_card_back_small'];

                    $idcard_info['true_name'] = $data['name'];
                    $idcard_info['idno'] = $data['cre_num'];
                    $idcard_info['tel'] = $data['tel'];
                    $idcard_info['user_id'] = $data['user_id'];

                    $idcard_cli->idno_save($idcard_info);
                }

                return array(
                    'success' => true,
                    'info' => '',
                );
            }

        }

        // 设置为默认
        public function set_default($id, $user_id){

            $model = M('');
            $model->startTrans();

            $res = M('UserAddressee')->where(array('user_id'=>$user_id, 'is_default'=>'1'))->save(array('is_default'=>'0'));
            if($res === false){
                $model->rollback();
                return false;
            }

            $res = M('UserAddressee')->where(array('user_id'=>$user_id, 'id'=>$id))->save(array('is_default'=>'1'));
            if($res === false){
                $model->rollback();
                return false;
            }

            $model->commit();
            return true;

        }

        //搜索数据
        public function search($where,$limit){

            if(empty($where)){
                return array(
                    'success'=>false,
                    'data'=>null,
                    'error'=>'缺少查找条件',
                );
            }

            $addr_m = new \Api\Model\UserAddresseeModel();
            
            $result = $addr_m->addr_search($where,$limit);
            if($result===false){
                return array(
                    'success'=>false,
                    'data'=>null,
                    'error'=>$addr_m->getError(),
                );
            }else{
                return array(
                    'success'=>true,
                    'data'=>$result,
                    'error'=>'',
                );
            }

        }

        // 更新数据
        // url_arr 原先的图片路径，待删除
        // checkIdCard 是否需要检测身份证重复性
        public function updateUserAddressee($id, $data, $idcard_info=array()){

            $addr_m = new \Api\Model\UserAddresseeModel();
            if(false === $addr_m->addr_update($id,$data)){
                return array(
                    'success' => false,
                    'info' => $addr_m->getError(),
                );
            }else{

                // 写入身份证图片库
                if(!empty($idcard_info)){
                    $idcard_cli = new \Api\Controller\IdcardInfoController();

                    unset($idcard_info['name']);
                    $idcard_info['front_id_img'] = $data['id_card_front'];
                    $idcard_info['small_front_img'] = $data['id_card_front_small'];
                    $idcard_info['back_id_img'] = $data['id_card_back'];
                    $idcard_info['small_back_img'] = $data['id_card_back_small'];

                    $idcard_info['true_name'] = $data['name'];
                    $idcard_info['idno'] = $data['cre_num'];
                    $idcard_info['tel'] = $data['tel'];
                    $idcard_info['user_id'] = $data['user_id'];

                    $idcard_cli->idno_save($idcard_info);
                }

                return array(
                    'success' => true,
                    'info' => '',
                );
            }

        }

        //删除数据
        public function deleteUserAddressee($id,$img_url){

            $addr_m = new \Api\Model\UserAddresseeModel();
            $addr_m->addr_delete($id,array());

        }

        //检测线路是否需要上传身份证
        //return: true表示需要上传，false表示不需要上传
        public function check_id_card($id){

            if(empty($id)){
                return false;
            }
            $transit_center_m = M('TransitCenter');
            $result = $transit_center_m->where(array('id'=>$id))->find();
            if($result['member_sfpic_state']!=1){
                return false;
            }else{
                return true;
            }

        }

        //哪些线路需要显示在页面上
        public function show_line(){

            $transit_center_m = M('TransitCenter');
            $result = $transit_center_m->field('id,lngname,lngremark,bc_state')->where(array('status'=>1,'optional'=>1))->order('id desc')->select();
            return $result;

        }

        //查找线路所属类型（BC or CC）
        public function line_cat($line_id){

            $transit_center_m = M('TransitCenter');
            return $transit_center_m->field('bc_state,cc_state')->where(array('id'=>array('eq',$line_id)))->select()[0];

        }

    }