<?php

    // liao ya di
    // create time : 2017-10-31
    // API CONTROLLER

    namespace Api\Controller;
    use Think\Controller\HproseController;

    class PiecemealController extends HproseController{
    
        //修改密码
        public function update_pwd($data){

            $user_model = M('UserList');
            return $user_model->save($data);

        }

        //获取密码
        public function get_pwd($user_id){

            $user_model = M('UserList');
            return $user_model->field('pwd')->where(array('id'=>$user_id))->find();

        }

        //获取购物小票图片路径
        public function get_receipt_img($where){

            return M('ShoppingReceipt')->field('id,receipt_img')->where($where)->find();

        }

        //删除购物小票记录
        public function del_shopping_rec($oid){

            return M('ShoppingReceipt')->where(array('order_id'=>$oid))->delete();

        }

        //获取线路的首重续重价格等信息
        public function getLineInfo($line_id){

            return M('LinePrice')->where(array('line_id'=>$line_id))->find();

        }

        
        //获取线路的详细信息
        public function getLine($line_id){

            return M('TransitCenter')->where(array('id'=>$line_id))->find();

        }


        //不上传身份证图片，支付费用
        public function set_extra_fee($data){

            return M('TranUlistExtraFee')->add($data);

        }

        //检测用户历史是否有身份证图片
        public function check_idno($user_id, $idno, $true_name){

            $img = M('UserExtraInfo')->field('front_id_img,back_id_img')->where(array('user_id'=>$user_id, 'idno'=>$idno, 'true_name'=>$true_name))->find();

            if(empty($img)){
                return false;
            }

            if(empty($img['front_id_img']) && empty($img['back_id_img'])){
                return false;
            }

            return true;

        }

        //获取身份证号对应的图片
        public function get_img_by_idno($user_id, $idno){
            
            if(empty($idno)){
                return false;
            }

            $where['user_id'] = $user_id;
            $where['_string'] = "idno in (" . $idno . ")";

            return M('UserExtraInfo')->field('idno,front_id_img,back_id_img')->where($where)->select();

        }

        //根据随机码查询MKNO
        public function find_mk_info($un_key){

            if(empty($un_key)){
                return false;
            }

            return
            M('MknoKey')->alias('uk')
                        ->field('ul.order_no,ul.MKNO,ul.receiver,ul.reTel')
                        ->join('left join mk_tran_ulist ul on ul.id=uk.u_id')
                        ->where(array('uk.un_key'=>$un_key))
                        ->find();

        }


        public function get_MKNO_by_Qno($Qno){

            return M('tranUlist')->where(array('order_no'=>$Qno))->getfield('MKNO');

        }


        //设置打印凭证状态为 true
        public function set_print($user_id, $id){

            if(empty($id) || empty($user_id)){
                return false;
            }

            $where['user_id'] = $user_id;
            $where['_string'] = "id in (" . $id . ")";

            return M('tranUlist')->where($where)->save(array('is_print'=>1));

        }


        // // 获取身份证查询条件
        // public function get_idno_condition($is_arr = true){
        //     if($is_arr){
        //         return array();
        //     }else{
        //         return "";
        //     }
        // }

    
    }