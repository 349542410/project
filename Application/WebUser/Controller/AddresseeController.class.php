<?php

    /*
    *   收件人管理
    *   liao ya di
    *   create time : 2017-10-14
    */

    namespace WebUser\Controller;
    // use Think\Controller;

    class AddresseeController extends BaseController{

        private $_client;

        public function __construct(){

            parent::__construct();
            vendor('Hprose.HproseHttpClient');
            $this->_client = new \HproseHttpClient(C('RAPIURL').'/UserAddressee');

        }


        //删除图片
        public function del_img($url){
            // if(is_array($url)){
            //     foreach($url as $k=>$v){
            //         unlink($v);
            //     }
            // }else{
            //     unlink($url);
            // }
            return true;
        }

        //显示收件人列表
        public function index(){

            //定义分页数量
            define('PAGE_NO',5);
            $user_id = session("user_id");

            $result = $this->_client->search(array('user_id'=>$user_id, 'delete_time'=>array('exp', 'is null')),'');
            $count = count($result['data']);

            //如果请求分页大于最大分页数量，则跳转到最大分页，而不是给一个空白页面
            if(!empty($_GET['p'])&&$count<PAGE_NO*$_GET['p']){
                $_GET['p'] = ceil($count/PAGE_NO);
            }

            $Page = new \Think\Page($count,PAGE_NO);

            $Page->rollPage = 15;
            $Page->setConfig('prev', L('PrevPage'));    //上一页
            $Page->setConfig('next', L('NextPage'));    //下一页
            $Page->setConfig('first', L('FirstPage'));  //第一页
            $Page->setConfig('last', L('LastPage'));    //最后一页
            $Page->setConfig('header', '<span class="rows">'.L('TotalPage',array('n'=>'%TOTAL_ROW%')).'</span>');
            $Page -> setConfig ( 'theme', '%FIRST% %UP_PAGE% %LINK_PAGE% %DOWN_PAGE% %END% %HEADER%' );
            $show = $Page->show();
            $list = $this->_client->search(array('user_id'=>$user_id, 'delete_time'=>array('exp', 'is null')),$Page->firstRow.','.$Page->listRows);
            

            //拼凑图片路径
            foreach($list['data'] as $k=>$v){

                if($v['id_card_front'] !== 'none' && !empty($v['id_card_front'])){
                    if($v['is_supplement'] == 0){
                        $list['data'][$k]['id_card_front'] = WU_FILE . $v['id_card_front'];
                        $list['data'][$k]['id_card_front_small'] = WU_FILE . $v['id_card_front_small'];
                    }else{
                        $list['data'][$k]['id_card_front'] = C('TMPL_PARSE_STRING.__MEMBER__').'/images/upload_success_f.png';
                        $list['data'][$k]['id_card_front_small'] = C('TMPL_PARSE_STRING.__MEMBER__').'/images/upload_success_f.png';
                    }

                }else{
                    $list['data'][$k]['id_card_front'] = C('TMPL_PARSE_STRING')['__MEMBER__'] . '/images/pho_front.png';
                    $list['data'][$k]['id_card_front_small'] = C('TMPL_PARSE_STRING')['__MEMBER__'] . '/images/pho_front.png';
                }

                if($v['id_card_back'] !== 'none' && !empty($v['id_card_back'])){
                    if($v['is_supplement'] == 0){
                        $list['data'][$k]['id_card_back'] = WU_FILE . $v['id_card_back'];
                        $list['data'][$k]['id_card_back_small'] = WU_FILE . $v['id_card_back_small'];
                    }else{
                        $list['data'][$k]['id_card_back'] = C('TMPL_PARSE_STRING.__MEMBER__').'/images/upload_success_b.png';
                        $list['data'][$k]['id_card_back_small'] = C('TMPL_PARSE_STRING.__MEMBER__').'/images/upload_success_b.png';
                    }


                }else{
                    $list['data'][$k]['id_card_back'] = C('TMPL_PARSE_STRING')['__MEMBER__'] . '/images/pho_back.png';
                    $list['data'][$k]['id_card_back_small'] = C('TMPL_PARSE_STRING')['__MEMBER__'] . '/images/pho_back.png';
                }

            }



            $this->assign('data',$list['data']);
            $this->assign('page',$show);

            $this->display();

        }

        //ajax获取线路
        public function showLineAjax(){

            $result = $this->_client->show_line();
            foreach($result as $k=>$v){
                $result[$k]['line_name'] = L('MKLINES')[$v['lngname']];
            }
            return $result;

        }

        //获取证件类型
        public function get_id_type(){

            return C('ID_TYPE');

            $id_type = C('ID_TYPE');
            foreach($id_type as $k=>$v){
                $id_type[$k] = array(
                    'v' => L($v)
                );
            }
            $id_type['ID']['k'] = 1;        //身份证
            $id_type['PASPORT']['k'] = 2;   //护照
            $info['data'] = $id_type;
            $info['id_type'] = L('DocumentsType');

            return $info;

        }

        // 设置收件人为默认地址
        public function set_default(){

            $id = I('get.id');

            if(!empty($id) && !empty(session('user_id'))){
                $this->_client->set_default($id, session('user_id'));
            }

        }

        //添加收件人信息 - ajax提交
        public function addresseeAjax(){

            if(IS_POST){

                $user_id = session("user_id");

                //收集表单数据
                $data['name'] = I('post.name');
                $data['tel'] = I('post.tel');
                $data['province'] = I('post.province');
                $data['city'] = I('post.city');
                $data['town'] = I('post.town');
                $data['address'] = I('post.address');
                $data['postal_code'] = I('post.postal_code');
                $data['cre_type'] = I('post.cre_type');
                $data['cre_num'] = I('post.cre_num');
                $data['address_alias'] = I('post.address_alias');
                $data['line_id'] = I('post.line_id');

                $data['user_id'] = $user_id;
                foreach($data as $k=>$v){
                    $data[$k] = trim($v);
                }

                //验证身份证格式
                if(!empty($data['cre_num'])){
                    if(!certificate($data['cre_num'])){
                        echo json_encode(array('info'=>L('id_not_correct'),'success'=>false));
                        exit;
                    }
                }

                // 身份证图片
                $file_one_upload = \Lib11\IdcardUpload\IdcardUpload::file_upload($_FILES['id_card_front']);
                $file_two_upload = \Lib11\IdcardUpload\IdcardUpload::file_upload($_FILES['id_card_back']);

                if($file_one_upload['success'] && !empty($_FILES['id_card_front']) && $_FILES['id_card_front']['error']!=4){
                    $data['id_card_front'] = $file_one_upload['info'];
                    $data['id_card_front_small'] = $file_one_upload['small'];
                }else{
                    $data['id_card_front'] = '';
                    $data['id_card_front_small'] = '';
                }

                if($file_two_upload['success'] && !empty($_FILES['id_card_back']) && $_FILES['id_card_back']['error']!=4){
                    $data['id_card_back'] = $file_two_upload['info'];
                    $data['id_card_back_small'] = $file_two_upload['small'];
                }else{
                    $data['id_card_back'] = '';
                    $data['id_card_back_small'] = '';
                }


                $idno_check = new \HproseHttpClient(C('RAPIURL').'/IdentityImgVerify');

                // 正面照片和反面照片都存在
                if(!empty($data['id_card_front']) && !empty($data['id_card_back'])){
                    
                    $back_idcard_info = $this->idcard_photo( WU_ABS_FILE . $data['id_card_back']);
                    $front_idcard_info = $this->idcard_national_emblem( WU_ABS_FILE .  $data['id_card_front']);
                    $idcard_info_merge = array_merge($front_idcard_info['info'], $back_idcard_info['info']);

                    // 验证身份证图片和号码是否正确
                    $idno_check_res = $idno_check->check_idno($idcard_info_merge, trim($data['name']), trim($data['cre_num']));
                    if(!$idno_check_res['status']){
                        echo json_encode(array(
                            'success'=>false,
                            'info' => L($idno_check_res['err_info']),
                        ));
                        die;
                    }

                }                

                // 开始添加
                $result = $this->_client->addUserAddressee($data, $idcard_info_merge);

                if(!empty($result['info'])){
                    $result['info'] = L($result['info']);
                }
                echo json_encode(array(
                    'success' => true,
                    'info' => $result['info'],
                    'url' => 'http://'.$_SERVER['HTTP_HOST'] . __CONTROLLER__ . '/index',
                ));
                
            }

        }

        //添加收件人信息 - 页面显示
        public function addressee(){

            //身份证护照选择
            $this->assign('type',$this->get_id_type());
            //线路选择
            $this->assign('line',$this->showLineAjax());
            $this->display();

        }


        //修改收件人信息 - ajax提交
        public function saveresseeAjax(){

            $s_id_card_front_url = session('id_card_front_url');
            $s_id_card_front_small_url = session('id_card_front_small_url');
            $s_id_card_back_url = session('id_card_back_url');
            $s_id_card_back_small_url = session('id_card_back_small_url');

            session('id_card_front_url', null);
            session('id_card_front_small_url', null);
            session('id_card_back_url', null);
            session('id_card_back_small_url', null);

            if(empty(I('post.id'))){
                //阻止非法请求
                echo json_encode(array(
                    'success' => false,
                    'info' => L('l_data_error'),
                ));
                die;
            }

            if(IS_POST){
                
                $user_id = session("user_id");
                $id = I('post.id');

                //收集表单数据
                $data['name'] = I('post.name');
                $data['tel'] = I('post.tel');
                $data['province'] = I('post.province');
                $data['city'] = I('post.city');
                $data['town'] = I('post.town');
                $data['address'] = I('post.address');
                $data['postal_code'] = I('post.postal_code');
                $data['cre_type'] = I('post.cre_type');
                $data['cre_num'] = I('post.cre_num');
                $data['address_alias'] = I('post.address_alias');
                $data['line_id'] = I('post.line_id');

                $data['user_id'] = $user_id;
                foreach($data as $k=>$v){
                    $data[$k] = trim($v);
                }


                $is_upload = false;

                // 身份证图片
                $file_one_upload = \Lib11\IdcardUpload\IdcardUpload::file_upload($_FILES['id_card_front']);
                $file_two_upload = \Lib11\IdcardUpload\IdcardUpload::file_upload($_FILES['id_card_back']);

                if($file_one_upload['success'] && !empty($_FILES['id_card_front']) && $_FILES['id_card_front']['error']!=4){
                    $is_upload = true;
                    $data['id_card_front'] = $file_one_upload['info'];
                    $data['id_card_front_small'] = $file_one_upload['small'];
                }else{
                    $data['id_card_front'] = $s_id_card_front_url;
                    $data['id_card_front_small'] = $s_id_card_front_small_url;
                }

                if($file_two_upload['success'] && !empty($_FILES['id_card_back']) && $_FILES['id_card_back']['error']!=4){
                    $is_upload = true;
                    $data['id_card_back'] = $file_two_upload['info'];
                    $data['id_card_back_small'] = $file_two_upload['small'];
                }else{
                    $data['id_card_back'] = $s_id_card_back_url;
                    $data['id_card_back_small'] = $s_id_card_back_small_url;
                }


                $idno_check = new \HproseHttpClient(C('RAPIURL').'/IdentityImgVerify');

                // 正面照片和反面照片都存在
                if($is_upload){
                    
                    $back_idcard_info = $this->idcard_photo( WU_ABS_FILE . $data['id_card_back']);
                    $front_idcard_info = $this->idcard_national_emblem( WU_ABS_FILE .  $data['id_card_front']);
                    $idcard_info_merge = array_merge($front_idcard_info['info'], $back_idcard_info['info']);

                    // 验证身份证图片和号码是否正确
                    $idno_check_res = $idno_check->check_idno($idcard_info_merge, trim($data['name']), trim($data['cre_num']));
                    if(!$idno_check_res['status']){
                        echo json_encode(array(
                            'success'=>false,
                            'info' => L($idno_check_res['err_info']),
                        ));
                        die;
                    }

                }   


                // 开始修改
                $result = $this->_client->updateUserAddressee($id, $data, $idcard_info_merge);

                if(!empty($result['info'])){        //多语言
                    $result['info'] = L($result['info']);
                }
                echo json_encode(array(
                    'success' => true,
                    'info' => $result['info'],
                    'url' => 'http://'.$_SERVER['HTTP_HOST'] . __CONTROLLER__ . '/index',
                ));

            }

        }

        //修改收件人信息 - 页面显示
        public function saveressee(){

            session('id_card_front_url', null);
            session('id_card_front_small_url', null);
            session('id_card_back_url', null);
            session('id_card_back_small_url', null);

            if(empty($_GET['id'])){
                //阻止非法请求
                $this->redirect("Addressee/index");
                die;
            }

            // 身份证护照选择
            $this->assign('type',$this->get_id_type());
            // 线路选择
            $this->assign('line',$this->showLineAjax());

            $res = $this->_client->search(array('id'=>I('get.id'), 'delete_time'=>array('exp', 'is null')),'');

            // session存储图片路径（存在才存储）
            if($res['data'][0]['id_card_front']!=='none' && !empty($res['data'][0]['id_card_front'])){
                session('id_card_front_url',$res['data'][0]['id_card_front']);
                session('id_card_front_small_url',$res['data'][0]['id_card_front_small']);
            }else{
                session('id_card_front_url', null);
                session('id_card_front_small_url', null);
            
            }
            if($res['data'][0]['id_card_back']!=='none' && !empty($res['data'][0]['id_card_back'])){
                session('id_card_back_url',$res['data'][0]['id_card_back']);
                session('id_card_back_small_url',$res['data'][0]['id_card_back_small']);
            }else{
                session('id_card_back_url', null);
                session('id_card_back_small_url', null);
            }


            //拼凑图片路径
            if($res['data'][0]['id_card_front']!=='none' && !empty($res['data'][0]['id_card_front'])){
                if($res['data'][0]['is_supplement'] == 0){
                    $res['data'][0]['id_card_front'] = WU_FILE . $res['data'][0]['id_card_front'];
                }else{
                    $res['data'][0]['id_card_front'] = C('TMPL_PARSE_STRING.__MEMBER__').'/images/upload_success_f.png';
                }
            }else{
                $res['data'][0]['id_card_front'] = C('TMPL_PARSE_STRING')['__MEMBER__'] . '/images/pho_back.png';
            }
            if($res['data'][0]['id_card_back']!=='none' && !empty($res['data'][0]['id_card_back'])){
                if($res['data'][0]['is_supplement'] == 0){
                    $res['data'][0]['id_card_back'] = WU_FILE . $res['data'][0]['id_card_back'];
                }else{
                    $res['data'][0]['id_card_back'] = C('TMPL_PARSE_STRING.__MEMBER__').'/images/upload_success_b.png';
                }

            }else{
                $res['data'][0]['id_card_back'] = C('TMPL_PARSE_STRING')['__MEMBER__'] . '/images/pho_front.png';
            }


            $this->assign('info',$res['data'][0]);
            $this->display();

        }

        //删除收件人信息
        public function deleteAddressee(){

            if(empty($_POST['id'])){
                //阻止非法请求
                $this->redirect("Addressee/index");
                die;
            }

            $res = $this->_client->search(array('id'=>I('post.id'), 'delete_time'=>array('exp', 'is null')),'');
            if($res['data'][0]['id_card_front']!='none'){
                $img_url[] = WU_ABS_FILE . $res['data'][0]['id_card_front'];
                $img_url[] = WU_ABS_FILE . $res['data'][0]['id_card_front_small'];
            }
            if($res['data'][0]['id_card_back']!='none'){
                $img_url[] = WU_ABS_FILE . $res['data'][0]['id_card_back'];
                $img_url[] = WU_ABS_FILE . $res['data'][0]['id_card_back_small'];
            }


            $this->_client->deleteUserAddressee(I('post.id'),$img_url);

            // 删除图片
            if(!empty($img_url)){
                $stat = $this->del_img($img_url);
            }

            echo json_encode(array('state' => 'yes', 'msg' => L('l_del_success'), 'code'=>'DeleteSuccess', 'status'=>$stat));
            die;

        }

        // 识别正面图片
        private function idcard_photo($url){
            $obj = new \Lib10\Idcardali\AliIdcard();
            $res = $obj->photo($url);
            if($res){
                return array(
                    'status' => true,
                    'info' => $obj->photo($url)
                );
            }else{
                return array(
                    'status' => false,
                    'info' => $obj->getError()
                );
            }
        }

        // 识别反面图片
        private function idcard_national_emblem($url){
            $obj = new \Lib10\Idcardali\AliIdcard();
            $res = $obj->national_emblem($url);
            if($res){
                return array(
                    'status' => true,
                    'info' => $obj->national_emblem($url)
                );
            }else{
                return array(
                    'status' => false,
                    'info' => $obj->getError()
                );
            }
        }

    }