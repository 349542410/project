<?php

    /*
    *   liao ya di
    *   create time : 2017-10-31
    */

    namespace WebUser\Controller;

    class PiecemealController extends BaseController{

        private $_client;

        public function __construct(){

            parent::__construct();
            parent::_initialize();
            vendor('Hprose.HproseHttpClient');
            $this->_client = new \HproseHttpClient(C('RAPIURL').'/Piecemeal');

        }


        //修改密码 - ajax提交
        public function check_pwd_ajax(){

            if(IS_POST){

                $data['old_pwd'] = I('post.old_pwd');
                $data['new_pwd'] = I('post.new_pwd');
                // $data['repeat_new_pwd'] = I('post.repeat_new_pwd');
                $data['captcha'] = I('post.captcha');
                // echo json_encode($data);

                foreach($data as $k=>$v){
                    $data[$k] = trim($v);
                }

                if(empty($data['old_pwd'])){
                    echo json_encode(array(
                        'success' => false, 
                        'info' => L('l_old_pwd_empty')
                    ));
                    die;
                }

                if(empty($data['new_pwd'])){
                    echo json_encode(array(
                        'success' => false, 
                        'info' => L('l_new_pwd_empty')
                    ));
                    die;
                }

                if(strlen($data['old_pwd'])<6||strlen($data['old_pwd'])>16){
                    echo json_encode(array(
                        'success' => false, 
                        'info' => L('l_old_pwd_len')
                    ));
                    die;
                }

                if(strlen($data['new_pwd'])<6||strlen($data['new_pwd'])>16){
                    echo json_encode(array(
                        'success' => false, 
                        'info' => L('l_new_pwd_len')
                    ));
                    die;
                }

                if(!check_verify($data['captcha'])){
                    echo json_encode(array(
                        'success' => false, 
                        'info' => L('V_code_error_re')
                    ));
                    die;
                }

                $user_id = session('user_id');
                $old_pwd = $this->_client->get_pwd($user_id);
                if($old_pwd['pwd']!==md5($data['old_pwd'])){
                    echo json_encode(array(
                        'success' => false,
                        'info' => L('l_old_pwd_err')
                    ));
                    die;
                }



                if(false !== ($this->_client->update_pwd(array('id'=>$user_id,'pwd'=>md5($data['new_pwd']))))){
                    echo json_encode(array(
                        'success' => true,
                        'info' => L('l_pwd_success')
                    ));
                    die;
                }else{
                    echo json_encode(array(
                        'success' => false,
                        'info' => L('l_pwd_error')
                    ));
                    die;
                }

            }

        }

        //修改密码 - 页面展示
        public function modify_password(){
            $this->display();
        }

        //验证码生成
        public function verify_c(){  
            verify_c(); 
        }

        //获取购物小票图片路径
        public function get_shopping_receipt(){

            if(empty($_GET['order_id'])){
                echo json_encode(array());
                die;
            }

            $where = array('order_id'=>I('get.order_id'));

            $path = $this->_client->get_receipt_img($where);
            
            if(!$path||$path['receipt_img']=='none'){
                echo json_encode(array());
                die;
            }

            echo json_encode(array('path' => WU_FILE . $path['receipt_img']));
            die;

        }

        public function get_view_status(){

            $line_id = I('get.line_id');

            $map = array(
                'user_id' => session('user_id'),
                'group' => 'batch_import_template',
                'attr_one' => $line_id,
            );
            $StateManagement = new \Api\Controller\StateManagementController();
            echo json_encode(array('status'=>$StateManagement->get_view_status($map)));
        }

    
    }