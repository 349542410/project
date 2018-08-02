<?php

    /*
    *   通知（新闻）
    *   liao ya di
    *   create time : 2018-1-19
    */

    namespace WebUser\Controller;

    class AnnouncementController extends BaseController{

        private $_client;

        // new_ann 天以内发布的通告为最新通告
        private $new_ann = 1;

        public function __construct(){

            parent::__construct();
            parent::_initialize();
            vendor('Hprose.HproseHttpClient');
            $this->_client = new \HproseHttpClient(C('RAPIURL').'/UserAnnouncement');

        }

        //第一次请求，直接渲染
        public function index(){

            $res = $this->get_ann_title();
            if(!$res['status']){
                echo $res['data'];
                die;
            }

            // dump($res);
            $this->assign('list',$res['list']['data']);
            $this->assign('show',$res['show']);

            $this->display();


        }

        //分页请求时，ajax获取数据
        public function get_ann_title_ajax(){
            echo json_encode($this->get_ann_title());
            die;
        }

        //分页查询数据
        private function get_ann_title($where = array()){

            //获取用户id
            $user_id = session('user_id');

            //每页显示的条数
            define('PAGE_NO',5);

            //显示的分页数量
            define('ROLL_PAGE',10);

            //构造查询条件
            $where['lang'] = strtolower(cookie('think_language'));
            // dump(strtolower(cookie('think_language')));

            //获取总数量
            $result = $this->_client->getAnnTitle($user_id,$where,'');
            if(!$result['status']){
                return $result;
            }
            $count = count($result['data']);

            if($_POST['p']){
                $_GET['p'] = $_POST['p'];
            }
            // //如果请求分页大于最大分页数量，则跳转到最大分页，而不是给一个空白页面
            if(!empty($_GET['p'])&&$count<PAGE_NO*$_GET['p']){
                $_GET['p'] = ceil($count/PAGE_NO);
            }

            
            $Page = new \WebUser\Tools\Page($count,PAGE_NO);

            $Page->rollPage = ROLL_PAGE;
            $Page->setConfig('prev', L('PrevPage'));    //上一页
            $Page->setConfig('next', L('NextPage'));    //下一页
            $Page->setConfig('first', L('FirstPage'));  //第一页
            $Page->setConfig('last', L('LastPage'));    //最后一页
            $Page->setConfig('header', '<span class="rows">'.L('TotalPage',array('n'=>'%TOTAL_ROW%')).'</span>');
            $Page -> setConfig ( 'theme', '%FIRST% %UP_PAGE% %LINK_PAGE% %DOWN_PAGE% %END% %HEADER%' );
            $show = $Page->show();

            //开始查询数据
            $list = $this->_client->getAnnTitle($user_id, $where, $Page->firstRow.','.$Page->listRows);
            if(!$list['status']){
                return $list;
            }


            \Lib11\simple_html_dom\simple_html_load::load();
            $html = new \Simple_html_dom();
            // date_default_timezone_set('UTC');
            foreach($list['data'] as $k=>$v){
                // $list['data'][$k]['start_time'] = date("Y-m-d H:i:s", $v['start_time']);
                $list['data'][$k]['start_time'] = date("Y.m.d", $v['start_time']);
                $list['data'][$k]['content_url'] = U('Announcement/get_content?id=' . $v['id']);

                $curr_time = time();
                $old_time = $curr_time-(24*60*60*$this->new_ann);
                if($v['start_time']>$old_time && $v['start_time'] <$curr_time){
                    $list['data'][$k]['is_new'] = true;
                }else{
                    $list['data'][$k]['is_new'] = false;
                }

                $content = '';
                if(!empty($v['content'])){

                    $content = htmlspecialchars_decode($v['content']);
                    $html->load($content);
                    $content = $html->plaintext;
                    $list['data'][$k]['content'] = $content;
                }
            }

            return array(
                'status' => true,
                'show' => $show,
                'list' => $list,
            );

        }


        //显示详细信息
        public function get_content(){

            $id = I('get.id');
            $user_id = session('user_id');
            
            $res = $this->_client->getContent($user_id, $id);

            date_default_timezone_set('UTC');
            $res['data']['start'] = date("Y-m-d H:i:s", $res['data']['start_time']);

            // dump($res);

            $this->assign('data', $res['data']);
            $this->display();

        }


        // //获取评论
        // public function get_feeback(){
        //     $id = I('get.id');

        //     $res = $this->_client->getFeeback($id);

        //     dump($res);
        // }


        // //添加评论
        // public function add_feeback(){

        //     echo json_encode($_POST);
        //     die;

        // }


        //检测是否有新通告
        public function check_new_ann(){

            $user_id = session('user_id');
            $where['lang'] = strtolower(cookie('think_language'));
            $res = $this->_client->checkNewAnn($user_id, $where);
            echo $res;
            die;

        }


    }