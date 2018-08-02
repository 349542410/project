<?php

    /*
    *   寄件人管理
    *   liao ya di
    *   create time : 2017-10-24
    */

    namespace WebUser\Controller;

    class SenderController extends BaseController{

        private $_client;

        public function __construct(){

            parent::__construct();
            parent::_initialize();
            vendor('Hprose.HproseHttpClient');
            $this->_client = new \HproseHttpClient(C('RAPIURL').'/UserSender');

        }

        // 设置收件人为默认地址
        public function set_default(){

            $id = I('get.id');

            if(!empty($id) && !empty(session('user_id'))){
                $this->_client->set_default($id, session('user_id'));
            }

        }


        //寄件人管理首页
        public function index(){

            $res = $this->searchAction(10,15);
            $list = $res['list'];
            $show = $res['show'];

            // dump($list);
            // dump($show);
            
            $this->assign('data',$list['info']);
            $this->assign('page',$show);
            $this->display();

        }


        //收集数据 - 私有
        private function collecting_data(){

            $data['s_name'] = I('post.name');
            $data['s_street'] = I('post.street');
            $data['s_country'] = I('post.country');
            $data['s_state'] = I('post.state');
            $data['s_city'] = I('post.city');
            $data['s_tel'] = I('post.tel');
            $data['s_code'] = I('post.code');

            foreach($data as $k=>$v){
                $data[$k] = trim($v);
            }
            $data['user_id'] = session("user_id");

            return $data;

        }


        //添加数据 - 提交
        public function insertAction(){

            if(IS_POST){

                $data = $this->collecting_data();
                $result = $this->_client->s_insert($data);
                
                if(!$result['success']){
                    $result['info'] = L($result['info']);
                }
                $result['url'] = 'http://'.$_SERVER['HTTP_HOST'] . __CONTROLLER__ . '/index';

                //更新 redis 缓存
                $this->update_cache();

                echo json_encode($result);
                die;

            }

        }

        //添加数据 - 显示
        public function addsender(){
            $this->display();
        }


        //查询所有 - 分页 - 私有
        //page_no - 分页数量
        //rollPage - 显示最大页数
        private function searchAction($page_no,$rollPage,$where=array()){

            //定义分页数量
            define('PAGE_NO',$page_no);
            $user_id = session("user_id");
            $where['user_id'] = array('eq',$user_id);

            $result = $this->_client->s_search($where,'','');
            $count = count($result['info']);

            //如果请求分页大于最大分页数量，则跳转到最大分页，而不是给一个空白页面
            if(!empty($_GET['p'])&&$count<PAGE_NO*$_GET['p']){
                $_GET['p'] = ceil($count/PAGE_NO);
            }

            $Page = new \Think\Page($count,PAGE_NO);

            $Page->rollPage = $rollPage;                //最大页码数量
            $Page->setConfig('prev', L('PrevPage'));    //上一页
            $Page->setConfig('next', L('NextPage'));    //下一页
            $Page->setConfig('first', L('FirstPage'));  //第一页
            $Page->setConfig('last', L('LastPage'));    //最后一页
            $Page->setConfig('header', '<span class="rows">'.L('TotalPage',array('n'=>'%TOTAL_ROW%')).'</span>');
            $Page -> setConfig ( 'theme', '%FIRST% %UP_PAGE% %LINK_PAGE% %DOWN_PAGE% %END% %HEADER%' );
            $show = $Page->show();
            $list = $this->_client->s_search($where,$Page->firstRow.','.$Page->listRows,'');

            return array(
                'show' => $show,
                'list' => $list,
            );

        }


        //更新 - 提交
        public function updateAction(){
            
            if(empty($_POST['id'])){
                //阻止非法请求
                $this->redirect("Sender/index");
                die;
            }

            $data = $this->collecting_data();
            $data['id'] = I('post.id');
            $result = $this->_client->s_update($data);

            if(!$result['success']){
                $result['info'] = L($result['info']);
            }
            $result['url'] = 'http://'.$_SERVER['HTTP_HOST'] . __CONTROLLER__ . '/index';

            //更新 redis 缓存
            $this->update_cache();

            echo json_encode($result);
            die;

        }


        //更新 - 显示
        public function savesender(){
            if(empty($_GET['id'])){
                //阻止非法请求
                $this->redirect("Sender/index");
                die;
            }
            $res = $this->_client->s_find(array('id'=>I('get.id')));
            if($res['success']){
                $this->assign('data',$res['info']);
            }else{
                $this->assign('data',array());
            }
            // dump($res['info']);
            $this->display();
        }


        //删除
        public function delAction(){
            if(empty($_POST['id'])){
                //阻止非法请求
                $this->redirect("Sender/index");
                die;
            }
            $res = $this->_client->s_delete(array('id'=>I('post.id')));

            //更新 redis 缓存
            $this->update_cache();

            echo json_encode(array('state' => 'yes', 'msg' => L('l_del_success'), 'code'=>'DeleteSuccess'));
            die;
        }


        //在线下单 - 获取寄件人信息
        public function getSenderInfo(){

            //中文搜索乱码
            foreach($_GET as $k=>$v){
                if (mb_check_encoding($v, 'gbk')){
					$_GET[$k] = iconv('gbk', 'utf-8', $v);
				}
            }

            //拼装搜索条件
            if(!empty($_GET['search_send'])){
                $s = trim(I('get.search_send'));
                $map['_string'] = 's_name like "%' . $s . '%" or s_tel = "' . $s . '"';
            }


            $res = $this->searchAction(4,5,$map);
            $list = $res['list'];
            $show = $res['show'];

            // dump($list['info']);
            // dump($show);

            $this->assign('data',$list['info']);
            $this->assign('page',$show);
            $this->display();

        }

        //ajax获取某个寄件人的信息
        public function senderAjax(){
            if(empty($_POST['id'])){
                //阻止非法请求
                $this->redirect("Sender/index");
                die;
            }
            $result = $this->_client->s_find(array('id'=>I('post.id')));
            if($result['success']){
                echo json_encode($result['info']);
            }else{
                echo json_encode(array());
            }
        }




        //ajax实时搜索 - 姓名
        public function real_time_search_ajax(){

            // if(empty($_GET['field'])){
            //     //阻止非法请求
            //     echo json_encode(array(
            //         'success' => false,
            //         'info' => '',
            //     ));
            //     die;
            // }

            // echo \json_encode(array('test'=>'xxx','user_id'=>session("user_id")));
            // die;

            $user_id = session("user_id");
            if(empty($user_id)){
                return json_encode(array());
            }

            if(empty(C('OPEN_REDIS')) || !C('OPEN_REDIS')){
                //是否开启redis
                //不开启就直接查询mysql
                $result = $this->_client->s_search(array('user_id'=>$user_id),'','id,s_name');
                // $result = $this->_client->real_time_search(array('user_id',$user_id));

                $info = array();
                if(!$result['success']){
                    // $info = array();
                }else{
                    foreach($result['info'] as $k=>$v){
                        $info[$v['id']] = $v['s_name'];
                    }
                }
                echo json_encode($info);
                die;
            }
            

            //从redis中取回数据
            $redis = new \Redis();
            $redis->connect(C('Redis')['Host'],C('Redis')['Port']);
            $redis->auth(C('Redis')['Auth']);

            $key = 'sender_info_' . $user_id;
            if($redis->exists($key)){
                //存在
                $str = $redis->get($key);
            }else{
                //不存在，需要更新
                $this->update_cache();
                $str = $redis->get($key);
            }

            $redis->close();
            $info = unserialize($str);
            // dump($info);
            echo json_encode($info);
            die;

            //对取回的数据进行匹配，并返回给前端
            if($info){
                $field = I('get.field');
                $preg = '/^' . $field . '/';
                $sender_arr = array();
                foreach($info as $k=>$v){
                    if(preg_match($preg,$v)==1){
                        $sender_arr[$k] = $v;
                    }
                }
                echo json_encode($sender_arr);
            }else{
                echo json_encode(array());
            }

        }

        //更新 mysql 到 redis 中
        private function update_cache(){

            if(!C('OPEN_REDIS')){
                //是否开启redis
                return ;
            }

            $user_id = session("user_id");
            $result = $this->_client->s_search(array('user_id',$user_id),'','id,s_name');

            $info = array();
            $str = '';
            if(!$result['success']){
                // $str = '';
            }else{
                foreach($result['info'] as $k=>$v){
                    $info[$v['id']] = $v['s_name'];
                }
                $str = serialize($info);
            }

            //存储到 redis 中，有效时间为 6h
            define('SENDER_MAX_TIME',6*3600);

            $redis = new \Redis();
            $redis->connect(C('Redis')['Host'],C('Redis')['Port']);
            $redis->auth(C('Redis')['Auth']);

            $key = 'sender_info_' . $user_id;
            $redis->setex($key,SENDER_MAX_TIME,$str);

            $redis->close();

        }
    
    }