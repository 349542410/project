<?php

    /*
    *   订单其它来源
    *   liao ya di
    *   create time : 2017-11-29
    */

    namespace WebUser\Controller;

    class OrderSourceController extends BaseController{

//        private $list_CC = array(                        //广东EMS个人快件,顺丰个人快件  $line['cc_state'] == '1'
//            '0'  => 'package_id',         //包裹id
//            '1'  => 'sender',             //发件人姓名
//            '2'  => 'sendStreet',         //街道-详细地址
//            '3'  => 'sendCity',           //市
//            '4'  => 'sendState',          //州
//            '5'  => 'sendTel',            //发件人电话
//            '6'  => 'sendcode',           //发件人邮编
//
//            '7'  => 'receiver',           //收件人姓名
//            '8'  => 'cre_num',            //证件号码
//            // '9' => 'province',           //省
//            // '10' => 'city',               //市
//            // '11' => 'town',               //区
//            // '12' => 'reAddr',             //收件人详细地址
//            '9' => 'addr_info',           //详细地址（需要解析）
//            '10' => 'reTel',              //收信人联系电话
//            // '11' => 'postcode',           //收件人邮编
//
//            '11' => 'category_one',       //一级分类
//            '12' => 'category_two',       //二级分类
//            '13' => 'detail',             //货品名称
//            '14' => 'brand',              //（英文）品牌
//
//
//            '15' => 'catname',            //规格/容量
//            '16' => 'spec_unit',          //规格单位
//
//            '17' => 'number',             //数量
//            // '18' => 'num_unit',        //计量单位
//            '18' => 'is_suit',            //是否套装
//
//            '19' => 'price',              //单价（￥）
//            '20' => 'note',               //备注
//        );

        // private $list_BC = array(                        //广东EMS电商快件  $line['bc_state'] == '1'
        //     '0'  => 'package_id',         //包裹id
        //     '1'  => 'sender',             //发件人姓名
        //     '2'  => 'sendStreet',         //街道
        //     '4'  => 'sendState',          //州
        //     '3'  => 'sendCity',           //市
        //     '5'  => 'sendTel',            //发件人电话
        //     '6'  => 'sendcode',           //发件人邮编
        //     '7'  => 'receiver',           //收件人姓名
        //     '8'  => 'cre_type',           //证件类型
        //     '9'  => 'cre_num',            //证件号码
        //     '10' => 'province',           //省
        //     '11' => 'city',               //市
        //     '12' => 'town',               //区
        //     '13' => 'reAddr',             //收件人详细地址
        //     '14' => 'reTel',              //收信人联系电话
        //     '15' => 'postcode',           //收件人邮编
        //     // '16' => 'brand',              //品牌
        //     // '17' => 'detail',             //货品名称
        //     // '18' => 'catname',            //货品分类
        //     // '19' => 'category_one',       //一级分类
        //     // '20' => 'category_two',       //二级分类
        //     // '21' => 'category_three',     //三级分类
        //     // '22' => 'number',             //数量
        //     // '23' => 'unit',               //计量单位
        //     // '24' => 'source_area',        //产地
        //     // '25' => 'price',              //单价（￥）
        //     '18' => 'catname',            //货品分类
        //     '16' => 'category_one',       //一级分类
        //     '17' => 'category_two',       //二级分类
        //     '19' => 'number',             //数量
        //     '20' => 'price',              //单价（￥）
        //     '21' => 'note',               //备注
        // );
        // private $list_NO = array(                        //香港E特快  else
        //     '0'  => 'package_id',         //包裹id
        //     '1'  => 'sender',             //发件人姓名
        //     '2'  => 'sendStreet',         //街道
        //     '4'  => 'sendState',          //州
        //     '3'  => 'sendCity',           //市
        //     '5'  => 'sendTel',            //发件人电话
        //     '6'  => 'sendcode',           //发件人邮编
        //     '7'  => 'receiver',           //收件人姓名
        //     '8'  => 'cre_type',           //证件类型
        //     '9'  => 'cre_num',            //证件号码
        //     '10' => 'province',           //省
        //     '11' => 'city',               //市
        //     '12' => 'town',               //区
        //     '13' => 'reAddr',             //收件人详细地址
        //     '14' => 'reTel',              //收信人联系电话
        //     '15' => 'postcode',           //收件人邮编
        //     '16' => 'brand',              //品牌
        //     '17' => 'detail',             //货品名称
        //     '18' => 'catname',            //货品分类

        //     '19' => 'category_one',       //一级分类
        //     '20' => 'category_two',       //二级分类
        //     // '21' => 'category_three',     //三级分类
        //     // '22' => 'number',             //数量
        //     // '23' => 'unit',               //计量单位
        //     // '24' => 'source_area',        //产地
        //     // '25' => 'price',              //单价（￥）

        //     '21' => 'number',             //数量
        //     '22' => 'unit',               //计量单位
        //     '23' => 'source_area',        //产地
        //     '24' => 'price',              //单价（￥）
        //     '25' => 'note',               //备注
        // );


        private $list_CC = array(                        //广东EMS个人快件,顺丰个人快件  $line['cc_state'] == '1'
            'package_id',         //包裹id

            'receiver',           //收件人姓名
            'cre_num',            //证件号码
            'addr_info',          //详细地址（需要解析）
            'reTel',              //收信人联系电话
            'category_one',       //一级分类
            'category_two',       //二级分类
            'detail',             //货品名称
            'brand',              //（英文）品牌
            'catname',            //规格/容量
            'spec_unit',          //规格单位
            'number',             //数量
            'is_suit',            //是否套装
            'price',              //单价（￥）
            'note',               //备注

            'sender',             //发件人姓名
            'sendStreet',         //街道-详细地址
            'sendCity',           //市
            'sendState',          //州
            'sendTel',            //发件人电话
            'sendcode',           //发件人邮编
        );




        private $_client;

        public function __construct(){

            parent::__construct();
            parent::_initialize();
            vendor('Hprose.HproseHttpClient');
            $this->_client = new \HproseHttpClient(C('RAPIURL').'/OrderExcel');

        }






        public function index(){

            $this->no_import();

            $data = $this->get_order_excel_data();

            $this->assign('tranline',$this->showLineAjax());

            //第一次直接写入页面
            $this->assign('data',$data);

            $this->assign('empty','<span class="empty"></span>');

            // dump($data);

            $this->display();

        }


        //使用ajax获取数据
        public function order_excel_ajax(){
            echo json_encode($this->get_order_excel_data());
        }


        //获取数据并分页
        public function get_order_excel_data(){

            //每页显示的条数
            define('PAGE_NO',10);

            //显示的分页数量
            define('ROLL_PAGE',15);

            //user id
            $user_id = session("user_id");


            //构造搜索条件
            $where['user_id'] = $user_id;
            $postWhere = I("post.where");
            if(!empty($postWhere['addr_name'])){
                $where['a.receiver'] = $postWhere['addr_name'];
            }
            if(!empty($postWhere['addr_tel'])){
                $where['a.reTel'] = $postWhere['addr_tel'];
            }
            if(!empty($postWhere['line_id'])){
                $where['a.line'] = $postWhere['line_id'];
            }

            if(!empty($postWhere['stat_time']) && !empty($postWhere['end_time'])){
                $where['_string'] = "a.create_time > '" . date("Y-m-d H:i:s",$postWhere['stat_time']) . "' AND a.create_time < '" . date("Y-m-d H:i:s",$postWhere['end_time']) . "'";
            }else if(!empty($postWhere['stat_time'])){
                $where['_string'] = "a.create_time > '" . date("Y-m-d H:i:s",$postWhere['stat_time']) . "'";
            }else if(!empty($postWhere['end_time'])){
                $where['_string'] = "a.create_time < '" . date("Y-m-d H:i:s",$postWhere['end_time']) . "'";
            }





            //获取总数量
            $result = $this->_client->query_data($where,'',false);
            $count = count($result['order_data']);


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
            $list = $this->_client->query_data($where,$Page->firstRow.','.$Page->listRows,false);

            foreach($list['order_data'] as $k=>$v){
                $list['order_data'][$k]['line_name'] = L('MKLINES')[$v['lngname']];
            }

            return array(
                'show' => $show,
                'list' => $list,
            );

        }


        //获取线路
        public function showLineAjax(){

            $result = $this->_client->show_line();

            foreach($result as $k=>$v){
                $result[$k]['line_name'] = L('MKLINES')[$v['lngname']];
            }
            return $result;

        }

        //转化为内部订单
        public function importOrder(){

            if(empty($_GET['id'])){
                $this->redirect("OrderSource/index");
                die; 
            }


            $where = array('id'=>I('get.id'));
            $result = $this->_client->find_data($where);
            
            // 恢复包裹号
            $package_arr = \explode(' ', $result['order_data']['package_id']);
            array_pop($package_arr);
            $result['order_data']['package_id'] = \implode(' ', $package_arr);
            // dump($result['order_data']);

            $this->assign('info',$result['order_data']);
            $this->assign('ID_TYPE',C('ID_TYPE'));
            $this->assign('tranline',$this->showLineAjax());
            $this->assign('type','import');
            
            // dump($result['order_goods_data']);

            foreach($result['order_goods_data'] as $k=>$v){
                $result['order_goods_data'][$k]['remark'] = $v['note'];
            }

            $this->assign('pro_list',json_encode($result['order_goods_data']));

            // dump($result['order_goods_data']);

            session('pro_list',$result['order_goods_data']);


            $category_list = array();
            $Host = C('Redis')['Host'];
            $Port = C('Redis')['Port'];
            $Auth = C('Redis')['Auth'];
            try{
                $redis = new \Redis();
                $redis->connect($Host,$Port,$overtime);
                $redis->auth($Auth);

                $category_list = unserialize($redis->get('category_list'));
            }catch(\RedisException $e){
                
            }
            // dump($category_list);
            $this->assign('category_list', json_encode($category_list));

            $this->display();

        }


        //删除
        public function delete(){

            $id = I('post.id');
            if(empty($id)){
                echo json_encode(array(
                    'success' => false,
                    'msg' => L('l_del_error')
                ));
            }

            $this->_client->delete_data($id);
            echo json_encode(array(
                'success' => true,
                'msg' => L('l_del_success')
            ));

        }


        // 批量删除
        public function delete_all(){

            $ids = I('post.ids');
            if(empty($ids)){
                echo json_encode(array(
                    'success' => false,
                    'msg' => L('l_del_error')
                ));
            }

            $this->_client->delete_data_all($ids);
            echo json_encode(array(
                'success' => true,
                'msg' => L('l_del_success')
            ));

        }







        //excel导入订单 - 验证
        public function import(){

            $this->no_import();

            if(empty($_FILES['file_stu'])||empty(session('user_id'))){
               //阻止非法请求
                $this->redirect("OrderSource/index");
                die; 
            }

            if(empty($_POST['line_id'])){
                //必须有line_id
                $this->redirect("OrderSource/index");
                die;
            }
            $line_id = I("post.line_id");
            $line_info = $this->_client->get_line_info($line_id);
            if(empty($line_info[0])){
                echo json_encode(array(
                    'excel' => false,
                    'info' => L('l_line_empty'),
                ));
                die;
            }


            // //线路是BC或者CC或者NO_BC_CC
            // if($line_info[0]['bc_state'] == '1'){
            //     $line_type = 'BC';
            // }else if($line_info[0]['cc_state'] == '1'){
            //     $line_type = 'CC';
            // }else{
            //     $line_type = 'NO';
            // }
            $line_type = 'CC';  // 目前只支持CC


            $result = array();

            $excel = new \WebUser\PHPExcel\PHPExcel();
            if( false === ($res = $excel->read($_FILES['file_stu']['tmp_name']) ) ){
                $result = array(
                    'excel' => false,
                    'info' => $excel->getError(),
                );
            }else{


                //整理格式
                $list = 'list_' . $line_type;
                $excel_format = new \WebUser\PHPExcel\Format($this->$list);

                try{
                    $data = $excel_format->exec($res,4);
                }catch(\Exception $e){
                    echo json_encode(array(
                        'excel' => false,
                        'info' => str_replace('{$el}',$e->getMessage(),L('l_err_line')),
                    ));
                    die;
                }

                // dump($data);
                // die;
                
                // 暂时只有身份证
                // foreach($data as $k=>$v){
                //     if($v['cre_type'] == '护照'){
                //         $data[$k]['cre_type'] = 'PASPORT';
                //     }else{
                //         $data[$k]['cre_type'] = 'ID';
                //     }
                // }

                // 整理每行数据
                error_reporting(E_ALL ^ E_NOTICE);
                $addrobj = new \Lib11\addr_analysis\addr_analysis();
                // 批次号，同一批导入的订单拥有同一个批次号
                // 注意，这个批次号只用于直接下单失败时的未完成订单列表里，当下单成功以后，这个批次号就不存在了，因此无法在我的订单中跟踪导入状态
                $batch_id = rand(100,999) . date("YmdHis", time()) . rand(100,999) . session('user_id');
                foreach($data as $k=>$v){
                    $data[$k]['cre_type'] = 'ID';
                    if(!empty($data[$k]['addr_info'])){
                        $res = $addrobj->exec($data[$k]['addr_info']);

                        $data[$k]['province'] = $res['addrinfo']['province'];
                        $data[$k]['city'] = $res['addrinfo']['city'];
                        $data[$k]['town'] = $res['addrinfo']['town'];
                        $data[$k]['reAddr'] = $res['addrinfo']['addr'];

                        if(!empty($data[$k]['town'])){
                            $data[$k]['postcode'] = $this->_client->get_zcode($data[$k]['province'], $data[$k]['city'], $data[$k]['town'], $line_id);
                        }else{
                            $data[$k]['postcode'] = $this->_client->get_zcode($data[$k]['province'], $data[$k]['city'], '', $line_id);
                        }
                        
                        $data[$k]['parsing_addr'] = $data[$k]['addr_info'];
                        $data[$k]['batch_id'] = $batch_id;
                        $data[$k]['cre_num'] = strtoupper($data[$k]['cre_num']);
                    }
                }
                // die;

                $data_tmp = array();
                foreach($data as $k=>$v){
                    $data_tmp[$v['package_id']][] = $v;
                }
                $data = $data_tmp;

                // dump($data);
                // die;
                // echo json_encode($data);
                // die;


                $result = array(
                    'excel' => true,
                    'info' => array(),
                    // 'dump' => $data,
                );

                //循环验证
                foreach($data as $k=>$v){
                    $res = $this->_client->inspect($v,$line_type, $line_id);
                    $res['package_id'] = (string)$k;
                    if(!$res['success']){
                        $res['error'] = '[' . L('l__' . $res['error'][0]) . ']:  ' . L($res['error'][1]);
                    }

                    $result['info'][] = $res;

                }

                foreach($result['info'] as $k=>$v){
                    if(!$v['success']){
                        echo json_encode($result);
                        die;
                    }
                }

                session('order_excel_data',array('data'=>$data,'line_id'=>$line_id));

            }


            /* 验证通过，开始统计订单信息 */
            $result['statistics'] = $this->_client->check_add_order($data,session('user_id'),$line_id);
            // dump($result['statistics']);die;

            foreach($result['statistics']['err_all'] as $k=>$v){
                $result['statistics']['err_all'][$k][1] = getErrorInfo($v[1]);
            }

            echo json_encode($result);
            die;

        }


        //excel导入订单 - 插入
        public function import_t(){

            $data = session('order_excel_data')['data'];
            $line_id = session('order_excel_data')['line_id'];


            $data_tmp = array();
            foreach($data as $k=>$v){
                $data_tmp[$k . 'q'] = $v;
            }
            $data = $data_tmp;


            //插入
            $res = array();
            if(!empty($data) && !empty($line_id)){
                $res = $this->_client->insert_order($data,session('user_id'),$line_id);
            }else{
                $res = array(
                    'success' => false,
                    'info' => L('l_data_missing'),
                );
            }

            // dump($res);
            // die;

            if($res['success']){
                // 设置redis消息队列
                // 只有当线路必须填写身份证的时候，才会加入短信队列
                if($this->_client->line_idno_find($line_id)){
                    $queue = new \Lib11\Queue\JoinQueue();
                    foreach($res['catch_content'] as $k=>$v){
                        $queue->join_queue($v);
                    }
                }
            }

            // $res['dump'] = $data;

            $this->no_import();            
            echo json_encode($res);
            die;

        }


        // 批量导出
        public function bulk_export(){

            $rule = array(
                'package_id', 'sender', 'sendStreet', 'sendCity', 'sendState', 'sendTel', 'sendcode',
                'receiver', 'cre_num', 'parsing_addr', 'reTel', 'postcode',
                'category_one', 'category_two', 'detail', 'brand', 'catname', 'spec_unit', 'number', 'num_unit', 'price', 'note',
            );

            $order_id = I('get.order_id');

            if(empty($order_id)){
                return false;
            }

            $res = $this->_client->get_derivation_data($order_id);

            $print_arr = array();
            foreach($res as $k=>$v){
                foreach($rule as $value){
                    // $print_arr[$k][$value] = $v[$value];
                    if($value == 'package_id'){
                        $arr = \explode(' ', $v[$value]);
                        array_pop($arr);
                        $print_arr[$k][] = \implode(' ', $arr);
                    }else{
                        $print_arr[$k][] = $v[$value];
                    }
                }
            }

            $this->_client->export_count_inc($order_id);

            // dump($print_arr);
            // die;


            // $data = array(
            //     array('0'=>1, '1'=>2, '2'=>3),
            //     array(),
            //     array(),
            //     array('3'=>1, '4'=>2, '5'=>3),
            // );

            $excel = new \WebUser\PHPExcel\PHPExcel();
            $excel->write($print_arr);

        }


        //移除文件时，清除session
        public function no_import(){
            session('order_excel_data',null);
        }


        //下载模版
        public function download_tmp(){

            // line_id => 线路id
            // name => 下载时文件名
            if(!empty($_GET['line_id']) && !empty($_GET['name'])){

                $StateManagement = new \Api\Controller\StateManagementController();
                $StateManagement->set_view_status(array(
                    'user_id' => session('user_id'),
                    'group' => 'batch_import_template',
                    'attr_one' => trim(I('get.line_id')),
                ));
                
                // 获取文件路径
                $path = WU_ABS_FILE . '/webuser/download/' . C('environment') . '/import_tmp';
                $file_name = $path . '/' . trim(I('get.line_id')) . '.xlsx';
//                dump($path);die;

                if(!file_exists($path)){

                	echo "<h1>目录不存在</h1>";
                    if($_GET['debug'] == '10086'){
                        dump($path);
                    }
//                    dump(mkdir($path, 0777, true));
                    // $path =  C('tmp_download_path') . '/mklinetemplate/template.xlsx'; //不存在使用默认模板
                    die;
                }
                // 打开文件
                $files = file_get_contents($file_name);

                if(!empty($files) && strlen($files)>0){

                    // 设置mime类型
                    header("Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet");
                    // 设置数据单位
                    header("Accept-Ranges: bytes");
                    // 设置文件长度
                    header("content-length:" . strlen($files));
                    // 设置文件名
                    header("Content-Disposition: attachment;filename=" . trim(I('get.name')) . ".xlsx" );

                    //开始下载
                    echo $files;
                    die;

                }
      
            }

        }

    }