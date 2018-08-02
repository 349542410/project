<?php
/**
 * 在线下单
 */
namespace WebUser\Controller;
use Think\Controller;
use Think\Log;

class OrderController extends BaseController {

    public function _initialize() {
        parent::_initialize();
        $Wclient = new \HproseHttpClient(C('WAPIURL').'/Order');
        $this->Wclient = $Wclient;

        // 定义最大商品条数
        define('MAX_ORDERNO', 31);
    }

    /**
     * 在线下单 视图
     */
    public function index(){

        //生成一个一次性的令牌以防止重复提交表单
        $_SESSION['token'] = md5(microtime(true));

        // 清空身份证识别的session
        $this->clear_idcard_session();

        $user_id = session('mkuser')['uid'];

        $tranline = $this->tranline;
        $ID_TYPE = C('ID_TYPE');

        vendor('Hprose.HproseHttpClient');
        $sender_client = new \HproseHttpClient(C('RAPIURL').'/UserSender');
        $addr_client = new \HproseHttpClient(C('RAPIURL').'/UserAddressee');

        $sender_data = $sender_client->s_find(array('user_id'=>$user_id, 'is_default'=>'1'))['info'];
        $addr_data = $addr_client->search(array('user_id'=>$user_id, 'is_default'=>'1'), '')['data'][0];

        $idcard_cli = new \HproseHttpClient(C('RAPIURL').'/IdcardInfo');
        $res = $idcard_cli->get_idcard_info_no_examine($addr_data['name'],$addr_data['tel'],$addr_data['cre_num'], $user_id)[0];

        if(!empty($res)){
            $addr_data['lib_idcard'] = $res['id'];
            $addr_data['idcard_old'] = $res['idno'];
            $addr_data['idcard'] = idcard_format($res['idno']);
            unset($addr_data['id_card_back']);
            unset($addr_data['id_card_back_small']);
            unset($addr_data['id_card_front']);
            unset($addr_data['id_card_front_small']);
            $addr_img = 3;

            $addr_data['id_card_back'] = C('TMPL_PARSE_STRING')['__MEMBER__'] . '/images/upload_success_b.png';
            $addr_data['id_card_front'] = C('TMPL_PARSE_STRING')['__MEMBER__'] . '/images/upload_success_f.png';
        }else{
            $addr_data['lib_idcard'] = 0;
            $addr_img = 1;
        }

        $addr_data['addr_img'] = $addr_img;

        $addr_data_json = json_encode(array(
            'success' => true,
            'info' => $addr_data,
        ));
        
//        $addr_data_json = json_encode(array(
//            'success' => true,
//            'info' => $addr_client->search(array('user_id'=>$user_id, 'is_default'=>'1'), '')['data'][0],
//        ));


        // dump($sender_data);
        // dump($addr_data_json);
        $this->assign('sender_data_json', json_encode($sender_data));
        $this->assign('addr_data_json', $addr_data_json);
        $this->assign('sender_id', $sender_data['id']);
        $this->assign('addr_id', $addr_data['id']);


        // 从redis里取出货品声明里的选项信息
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
        if(empty($category_list)){
            $this->assign('category_list', "{options:[],selectedOptions,[]}");
        }else{
            $this->assign('category_list', json_encode($category_list));
        }

        self::assign('tranline',$tranline);
        self::assign('user_id',$user_id);
        // self::assign('info',$info);
        self::assign('ID_TYPE',$ID_TYPE);

        $this->display();

    }


    // 清空身份证识别的session
    public function clear_idcard_session(){
        session('front_idcard', null);
        session('back_idcard', null);
    }


    // ajax获取收件人信息
    // liao ya di 2017-10-16
    public function get_addr_info_ajax(){

        if(empty($_GET['addr_id'])){
            echo \json_encode(array(
                'success' => false,
                //'info' => '缺少参数',
                'info'  => L('lack_of_parameters'),
            ));
            die;
        }

        $user_id = session('mkuser.uid');


        $x_client = new \HproseHttpClient(C('RAPIURL').'/UserAddressee');
        $addr_id = I('get.addr_id');
        $res = $x_client->search(array('id'=>$addr_id),'');

        if(!empty($res['data'][0]['id_card_front']) && $res['data'][0]['id_card_front']!=='none'){
            $res['data'][0]['id_card_front_old'] = $res['data'][0]['id_card_front'];
            $res['data'][0]['id_card_front'] = WU_FILE . $res['data'][0]['id_card_front'];
            $addr_img = 1;
        }else{
            $res['data'][0]['id_card_front'] = C('TMPL_PARSE_STRING')['__MEMBER__'] . '/images/pho_front.png';
            $addr_img = 2;
        }
        if(!empty($res['data'][0]['id_card_back']) && $res['data'][0]['id_card_back']!=='none'){
            $res['data'][0]['id_card_back_old'] = $res['data'][0]['id_card_back'];
            $res['data'][0]['id_card_back'] = WU_FILE . $res['data'][0]['id_card_back'];
            $addr_img = 1;
        }else{
            $res['data'][0]['id_card_back'] = C('TMPL_PARSE_STRING')['__MEMBER__'] . '/images/pho_back.png';
            $addr_img = 2;
        }

        $addr_info = $res['data'][0];

        $idcard_cli = new \HproseHttpClient(C('RAPIURL').'/IdcardInfo');
        $res = $idcard_cli->get_idcard_info_no_examine($addr_info['name'],$addr_info['tel'],$addr_info['cre_num'], $user_id)[0];

        if(!empty($res)){
            $addr_info['lib_idcard'] = $res['id'];
            $addr_info['idcard_old'] = $res['idno'];
            $addr_info['idcard'] = idcard_format($res['idno']);
            unset($addr_info['id_card_back']);
            unset($addr_info['id_card_back_small']);
            unset($addr_info['id_card_front']);
            unset($addr_info['id_card_front_small']);
            $addr_img = 3;

            $addr_info['id_card_back'] = C('TMPL_PARSE_STRING')['__MEMBER__'] . '/images/upload_success_b.png';
            $addr_info['id_card_front'] = C('TMPL_PARSE_STRING')['__MEMBER__'] . '/images/upload_success_f.png';
        }else{
            $addr_info['lib_idcard'] = 0;
        }

        $addr_info['addr_img'] = $addr_img;

        echo json_encode(array(
            'success' => true,
            'info' => $addr_info,
        ));
        die;

    }

    /**
     * ajax查询出相关身份证信息
     * gan 20180601
     */
    public function find_indo_data(){
        if(empty($_GET['true_name']) || empty($_GET['tel'])){
            echo \json_encode(array(
                'success' => false,
                //'info' => '缺少参数',
                'info' => L('lack_of_parameters'),

            ));
            die;
        }
        $user_id = session('mkuser.uid');
        $true_name = I('get.true_name');
        $tel       = I('get.tel');
        $idno      = I('get.idno');
        $idno_save = new \HproseHttpClient(C('RAPIURL').'/IdcardInfo');
        $res = $idno_save->get_idcard_info($true_name,$tel,$idno,$user_id);
        $info = array();
        // 状态为false 没查对应数据
        if(!$res){
            $info = array(
                'status' => false,
                'data' => null,
            );
        }else{
        // 状态为true 查出对应数据返回给前台
            $data = array();
            foreach($res as $k=>$v){
                $data[$k] = array(
                    'idcard_old' => $v['idno'],
                    'value' => idcard_format($v['idno']),
                    'id'    => $v['id'],
                    'back_id_img' => $v['back_id_img'],
                    'front_id_img' => $v['front_id_img']
                );
            }
            $info = array(
                'status' => true,
                'data' => $data,
            );
        }
        // 返回json数据给前台
        echo json_encode($info);
        die;

    }

    // 选择收件人
    // liao ya id 2017-10-16
    public function selectRecipient(){

        define('PAGE_COUNT',4);     //每页显示数量

        $client = new \HproseHttpClient(C('RAPIURL').'/UserAddressee');
        $user_id = session("user_id");


        //中文搜索乱码
        // foreach($_GET as $k=>$v){
        //     if (mb_check_encoding($v, 'gbk')){
        //         // $_GET[$k] = iconv('gbk', 'utf-8', $v);
        //         // $_GET[$k] = mb_convert_encoding($v, 'utf-8', 'gbk');
        //     }
        // }

        //拼凑查询条件
        $map['user_id'] = array('eq',$user_id);
        if(!empty($_GET['search_addr'])){
            $s = trim(I('get.search_addr'));

            // if(mb_check_encoding($s, 'gbk')){
            //     $s = mb_convert_encoding($s, 'utf-8', 'gbk');
            // }

            $map['_string'] = 'a.name like "%' . $s . '%" or tel = "' . $s . '"';
        }
        

        $result = $client->search($map,'');
        $count = count($result['data']);

        if($count<PAGE_COUNT*$_GET['p']){
            //如果页数不够
            $_GET['p'] = ceil($count/PAGE_COUNT);
            // $_GET['p'] = 1;
        }

        $Page = new \Think\Page($count,PAGE_COUNT);

        $Page->rollPage = 5;
        $Page->setConfig('prev', L('PrevPage'));    //上一页
        $Page->setConfig('next', L('NextPage'));    //下一页
        $Page->setConfig('first', L('FirstPage'));  //第一页
        $Page->setConfig('last', L('LastPage'));    //最后一页
        $Page->setConfig('header', '<span class="rows">'.L('TotalPage',array('n'=>'%TOTAL_ROW%')).'</span>');
        $Page -> setConfig ( 'theme', '%FIRST% %UP_PAGE% %LINK_PAGE% %DOWN_PAGE% %END% %HEADER%' );
        $show = $Page->show();
        $list = $client->search($map,$Page->firstRow.','.$Page->listRows);

        // dump($list['data']);

        $this->assign('data',$list['data']);
        $this->assign('page',$show);

        $this->display();

    }

    // 姓名+证件类型+证件号码 不在收件人列表中时，则添加到收件人列表中去
    // liao ya di 2017-10-18
    public function insert_addr_list($data){

        foreach($data as $k=>$v){
            $data[$k] = trim($v);
        }

        $client = new \HproseHttpClient(C('RAPIURL').'/UserAddressee');

        $res = $client->addUserAddressee($data);
        $res['info'] = L($res['info']);
        return $res;

    }

    // 姓名 不在寄件人列表中时，则添加到寄件人列表中去
    // liao ya di 2017-10-24
    public function insert_sender($send_data){

        $where = array(
            'user_id' => $send_data['user_id'],
            's_name' => $send_data['s_name'],
        );
        $send_client = new \HproseHttpClient(C('RAPIURL').'/UserSender');
        $result = $send_client->s_find($where);
        if($result['success']&&empty($result['info'])){
            return $send_client->s_insert($send_data);
        }else{
            return array(
                'success' => true,
                'info' => '',
            );
        }

    }

    //上传购物小票
    //liao ya di
    private function shopping_receipt(){

        $shop_state = session('shop_state');
        if(empty($shop_state)||$shop_state!=1){
            //无需上传
            session('shop_state',null);
            return array();
        }
        session('shop_state',null);

        if(empty($_FILES['receipt_img'])||$_FILES['receipt_img']['error']!=0){
            //没有上传或者上传失败
            return array('receipt_img'=>'none');
        }

        $upload           = new \Think\Upload();            // 实例化上传类
        $upload->maxSize  = 4200000;                         // 设置附件上传大小  不超过800k
        $upload->exts     = array('jpg', 'png', 'jpeg');    // 设置附件上传类型
        $upload->rootPath = WU_ABS_FILE."/";                // 设置文件上传保存的根路径
        $upload->savePath = C('UPLOADS_ID_IMG');            // 设置文件上传的保存路径（相对于根路径）
        $upload->autoSub  = true;                           // 自动子目录保存文件
        $upload->subName  = array('date','Ymd');
        $upload->saveName = array('uniqid',mt_rand());      // 设置上传文件名

        $info = $upload->uploadOne($_FILES['receipt_img']);
        // return $info;
        if(!$info){
            //上传失败
            return array('receipt_img'=>'none');
        }else{
            return array('receipt_img'=>$info['savepath'] . $info['savename']);
        }

    }

    //删除购物小票
    //liao ya di
    private function del_shopping_rec($order_id){

        if(empty($order_id)){
            return false;
        }

        $where = array('order_id'=>$order_id);

        vendor('Hprose.HproseHttpClient');
        $pie = new \HproseHttpClient(C('RAPIURL').'/Piecemeal');
        $path = $pie->get_receipt_img($where);
        
        if(!$path||$path['receipt_img']=='none'){
            return false;
        }

        unlink(WU_ABS_FILE . $path['receipt_img']);
        return $path['id'];

    }


    //获取线路首重价格与续重价格
    //liao ya di
    public function getLinePriceAjax(){

        echo '';
        die;

    }


    //批量打印的订单信息
    public function batch_print(){

        $ids = trim(I('get.ids'));
        $user_id = session('user_id');
        if(empty($ids) || empty($user_id)){
            echo \json_encode(array('status'=>false, 'data'=>array()));
            die;
        }

        $res = $this->Wclient->get_batch_print_info($user_id, $ids);

        echo \json_encode(array('status'=>true, 'data'=>$res));
        die;

    }

    //设置打印凭证状态为 true
    public function set_print(){

        vendor('Hprose.HproseHttpClient');
        $pie = new \HproseHttpClient(C('RAPIURL').'/Piecemeal');
        $res = $pie->set_print(session('user_id'), trim(I('get.id')));

        // dump($res);
        echo (int)$res;
        die;

    }

    // 获取所有线路的分类并格式化后存储到redis
    public function set_category_cache(){

        dump(\Lib11\Queue\CateCache::set_category_cache());
        die;

    }

    // 智能地址分析
    public function address_analysis(){

        $str = I("get.addr");
        if(empty($str)){
            echo json_encode(array(
                'success' => false,
                'info' => '',
            ));
        }

        $addrobj = new \Lib11\addr_analysis\addr_analysis();

        $res = $addrobj->exec($str);

        // dump($res);

        echo \json_encode($res);

    }

    // redis 缓存收件人地区
    public function addr_cache(){
        $line_id = I('get.line_id');
        dump(\Lib11\Queue\CateCache::set_addr_cache($line_id));
        die;

    }

    // 从redis里取得收件人
    public function get_addr_cache(){

        $line_id = I('get.line_id');
        if(empty($line_id)){
            return array();
        }

        // 从redis里取出货品声明里的选项信息
        $addr_list = array();
        $Host = C('Redis')['Host'];
        $Port = C('Redis')['Port'];
        $Auth = C('Redis')['Auth'];

        try{
            $redis = new \Redis();
            $redis->connect($Host,$Port,$overtime);
            $redis->auth($Auth);

            $addr_list = unserialize($redis->get('address_list_' . $line_id));
        }catch(\RedisException $e){
            
        }

        // $this->assign('addr_list', json_encode($addr_list));
        // dump($addr_list);
        echo json_encode($addr_list);
        die;

    }





    /**
     * 保存修改
     * @return [type] [description]
     */
    public function saveEdit(){

        $step = (I('get.step')) ? trim(I('get.step')) : '';

        if(IS_POST){
            $tranline = $this->tranline;
            $tranline = array_column($tranline, NULL, 'id');    //二维数组以id字段做一维数组的键名

            $id      = trim(I('post.id'));
            $arr     = I('post.');
            $user_id = session('mkuser.uid');                   //获取当前登陆的用户id

            $chelist = array(
                'PostName'     => L('PostNameMsg'),//寄件人姓名
                // 'PostAddress'  => L('PostAddressMsg'),//寄件人详细地址
                // 'PostCountry'  => L('PostCountryMsg'),//寄件人 国家 暂时默认 USA
                'PostState'    => L('PostStateMsg'),//寄件人 州
                'PostCity'     => L('PostCityMsg'),//寄件人城 市
                'PostStreet'   => L('PostStreetMsg'),//寄件人 街道
                'PostPhone'    => L('PostPhoneMsg'),//寄件人电话
                'PostCode'     => L('PostCodeMsg'),//寄件人邮编
                'RecName'      => L('RecNameMsg'),//收件人姓名
                'Province'     => L('ProvinceMsg'),//收件人 省
                'City'         => L('CityMsg'),//收件人 市
                'Town'         => L('TownMsg'),//收件人 区
                'RecAddress'   => L('RecAddressMsg'),//收件人详细地址
                'RecPhone'     => L('RecPhoneMsg'),//收件人电话
                'RecCode'      => L('RecCodeMsg'),//收件人邮编
                'TransferLine' => L('TransferLineMsg'),//中转线路
                'Id_tpye'      => L('Id_tpyeMsg'),//证件类型
            );

            $idcard_cli = new \HproseHttpClient(C('RAPIURL').'/IdcardInfo');
            // 获取原订单信息
            $idno_info = $idcard_cli->getInfoByOrderId($id);
            if(!$idno_info){
                echo json_encode(array(
                    'status' => 'no',
                    'msg' => '订单信息有误',
                ));
                die;
            }

            $arr['IdNo'] = strtoupper($arr['IdNo']);

            if(strlen($arr['PostCity'])>=50){
                echo json_encode(array('msg'=>L('IdNoMsg'),'state'=>'no'));
                exit;
            }
            $arr['PostCity'] = (string)$arr['PostCity'];


            //身份证是否必填
            $ID_card_is_empty = $this->Wclient->get_tranline();
            $list0 = array();
            foreach($ID_card_is_empty as $k=>$v){
                $list0[$v['id']] = $v['input_idno'];
            }
            $ID_card_is_empty = $list0;
            if($ID_card_is_empty[$arr['TransferLine']]==1 && empty($arr['IdNo'])){
                if(!empty($idno_info['idno'])){
                    echo json_encode(array('msg'=>L('IdNoMsg'),'state'=>'no'));exit;
                }
            }else if($ID_card_is_empty[$arr['TransferLine']]==1 && !empty($arr['IdNo'])){
                // 需要填写，也填写了
                $arr['id_no_status'] = '100';
            }else{
                // 无需填写
                $arr['id_no_status'] = '200';
            }

            //检查字段是否为空
            foreach($chelist as $k=>$dis){
                if(empty($arr[$k])){
                    echo json_encode(array('state'=>'no','msg'=>$chelist[$k]));
                    exit;
                }
            }



            // 是否是从身份证库中拿到的信息
            // 如果是从身份证库里拿到的信息，则身份证号码是一个无效的字段
            // 如果已经通过实名认证，则同样不需要修改身份证号码
            // 否则，需要对身份证号码进行验证和实名认证
            $idcard_id = I('post.lib_idcard');
            $arr['lib_idcard'] = empty($idcard_id) ? 0 : $idcard_id;
            if(!empty($idcard_id) || ($idno_info['idno_auth'] == 1)){
                $arr['IdNo'] = $idno_info['idno'];
            }else{

                $arr['IdNo'] = trim($arr['IdNo']);
                $arr['RecName'] = trim($arr['RecName']);

                //验证身份证格式
                if(!empty($arr['IdNo'])){
                    if(!certificate($arr['IdNo'])){
                        $result = array('msg'=>L('id_not_correct'),'state'=>'no');
                        echo json_encode($result);exit;
                    }
                }

                /* 进行实名认证 */
                if(!empty($arr['RecName']) && !empty($arr['IdNo']) && $arr['id_no_status'] != '200'){
                    $obj = new \Lib10\Idcardno\AliIdcardno();
                    $idnoauth = $obj->IdentificationCard($arr['RecName'], $arr['IdNo']);
                    if(!$idnoauth){
                        $arr['idno_auth'] = 2;
                        echo json_encode(array('msg'=>L($obj->getError()),'state'=>'no'));
                        exit;
                    }
                    $arr['idno_auth'] = 1;
                }else{
                    $arr['idno_auth'] = 0;
                }

            }



            
            /******************************************************/
            /* 当线路的 member_sfpic_state = 1，进行图片上传 */

            $file_one_upload = \Lib11\IdcardUpload\IdcardUpload::file_upload($_FILES['file_one']);
            $file_two_upload = \Lib11\IdcardUpload\IdcardUpload::file_upload($_FILES['file_two']);

            if($file_one_upload['success'] && !empty($_FILES['file_one']) && $_FILES['file_one']['error']!=4){
                $front_idcard = $file_one_upload['info'];
                $front_idcard_small = $file_one_upload['small'];
            }

            if($file_two_upload['success'] && !empty($_FILES['file_two']) && $_FILES['file_two']['error']!=4){
                $back_idcard = $file_two_upload['info'];
                $back_idcard_small = $file_two_upload['small'];
            }



            if($tranline[$arr['TransferLine']]['member_sfpic_state'] == 1){

                if(!empty($front_idcard) && !empty($back_idcard)){
                    // 取出session中的身份证数据 - 优先级高
                    $file_one = $front_idcard;
                    $file_one_small = $front_idcard_small;
                    $file_two = $back_idcard;
                    $file_two_small = $back_idcard_small;
                    $arr['id_img_status'] = '100';
                }else{
                    $file_one = '';
                    $file_one_small = '';
                    $file_two = '';
                    $file_two_small = '';
                }

                // 清除session
                $this->clear_idcard_session();

                // 身份证图片信息 - 待写入身份证库的信息
                $idcard['front_id_img'] = $file_one;
                $idcard['small_front_img'] = $file_one_small;
                $idcard['back_id_img'] = $file_two;
                $idcard['small_back_img'] = $file_two_small;
                
            }else{
                //无需上传

                $idcard['front_id_img'] = '';
                $idcard['small_front_img'] = '';
                $idcard['back_id_img'] = '';
                $idcard['small_back_img'] = '';

                $arr['id_img_status'] = '200';
                unset($arr['pic_radio']);
            }


            $idcard['user_id'] = $user_id;
            $idcard['true_name'] = trim($arr['RecName']);
            $idcard['idno'] = trim($arr['IdNo']);
            $idcard['tel'] = trim($arr['RecPhone']);

            /******************************************************/



            // 商品列表 把用户填写的商品整理到数组中
            $pro_list = array();

            // 订单商品总数量
            $num      = 0;

            $arrs    = array_keys($arr);
            $arrc    = implode(',',$arrs);
            $arcount = substr_count($arrc,'brand_');

            // 最多只能有MAX_ORDERNO条货品
            if($arcount > MAX_ORDERNO){
                echo json_encode(array('msg'=>str_replace('{**}', MAX_ORDERNO, L('almost_in_ten')),'state'=>'no'));
                exit;
            }

            for($i=0;$i<=$arcount;$i++){

                $pro_list[$i]['oid']          = I('post.oid_'.$i);
                $pro_list[$i]['brand']        = trim(I('post.brand_'.$i));
                $pro_list[$i]['detail']       = trim(I('post.detail_'.$i));
                $pro_list[$i]['catname']      = trim(I('post.catname_'.$i));
                $pro_list[$i]['price']        = trim(I('post.price_'.$i));
                $pro_list[$i]['number']       = trim(I('post.amount_'.$i));
                $pro_list[$i]['coin']         = trim(I('post.coin_'.$i));
                $pro_list[$i]['unit']         = trim(I('post.unit_'.$i));
                $pro_list[$i]['source_area']  = trim(I('post.source_area_'.$i));
                $pro_list[$i]['remark']       = trim(I('post.remark_'.$i));
                $pro_list[$i]['category_one'] = trim(I('post.category_one_'.$i));   //一级类别ID
                $pro_list[$i]['category_two'] = trim(I('post.category_two_'.$i));   //二级类别ID
                $pro_list[$i]['product_id']   = trim(I('post.product_type_'.$i));   //货品类别ID
                $pro_list[$i]['spec_unit']       = trim(I('post.spec_unit_'.$i));

                //$pro_list[$i]['num_unit']       = trim(I('post.num_unit_'.$i));
                $pro_list[$i]['is_suit']       = trim(I('post.is_suit_'.$i));

                if(empty($pro_list[$i]['is_suit'])){
                    $pro_list[$i]['is_suit'] = 0;
                }else{
                    $pro_list[$i]['is_suit'] = 1;
                }

                if($pro_list[$i]['is_suit'] == 1){
                    $pro_list[$i]['num_unit'] = '套';
                    $pro_list[$i]['unit'] = '套';
                }else{
                    $pro_list[$i]['num_unit'] = '件';
                    $pro_list[$i]['unit'] = '件';
                }

                $num += intval(trim(I('post.amount_'.$i)));                         //累计商品总数

            }
            if(empty($pro_list)){
                $result = array('msg'=>L('one_data'),'no'=>$kk+1,'state'=>'no');
                echo json_encode($result);exit;
            }


            if($tranline[$arr['TransferLine']]['bc_state'] == 1){       //当选择的线路的bc_state=1的时候，检查字段

                /* 暂停使用 */
                
                foreach($pro_list as $kk=>$po){
                    if($po['category_one'] == ''){
                        echo json_encode(array('msg'=>L('category_one'),'state'=>'no'));
                        exit;
                    }

                    if($po['category_two'] == ''){
                        echo json_encode(array('msg'=>L('category_two'),'state'=>'no'));
                        exit;
                    }

                    if($po['product_id'] == ''){
                        echo json_encode(array('msg'=>L('product_type'),'state'=>'no'));
                        exit;
                    }
                }
            
            }else if($tranline[$arr['TransferLine']]['cc_state'] == 1){     //当选择的线路的cc_state=1的时候，检查字段

                // 去除空项
                $pro_tmp = [];
                foreach($pro_list as $kk=>$po){
                    if(empty($po['category_one']) && empty($po['category_two']) && empty($po['brand']) && empty($po['detail']) &&
                        empty($po['price']) && empty($po['number']) && empty($po['spec_unit'])){
                        continue;
                    }else{
                        $pro_tmp[$kk] = $po;
                    }
                }
                $pro_list = $pro_tmp;

                if(empty($pro_list)){
                    $result = array('msg'=>L('one_data'),'no'=>$kk+1,'state'=>'no');
                    echo json_encode($result);exit;
                }

                foreach($pro_list as $kk=>$po){
                    if(empty($po['category_one'])){
                        echo json_encode(array('msg'=>L('category_one'),'no'=>$kk+1,'state'=>'no'));
                        exit;
                    }

                    if(empty($po['category_two'])){
                        echo json_encode(array('msg'=>L('category_two'),'no'=>$kk+1,'state'=>'no'));
                        exit;
                    }

                    if(empty($po['brand'])){
                        echo json_encode(array('msg'=>L('brand'),'no'=>$kk+1,'state'=>'no'));
                        exit;
                    }

                    if(empty($po['detail'])){
                        echo json_encode(array('msg'=>L('detail'),'no'=>$kk+1,'state'=>'no'));
                        exit;
                    }

                    if(empty($po['price'])){
                        echo json_encode(array('msg'=>L('price'),'no'=>$kk+1,'state'=>'no'));
                        exit;
                    }

                    if(empty($po['number'])){
                        echo json_encode(array('msg'=>L('number'),'no'=>$kk+1,'state'=>'no'));
                        exit;
                    }

                    if(empty($po['spec_unit'])){
                        echo json_encode(array('msg'=>L('spec_unit'),'no'=>$kk+1,'state'=>'no'));
                        exit;
                    }


                    if(!$this->Wclient->check_first_level($arr['TransferLine'], $po['category_one'])){
                        echo json_encode(array('msg'=>L('category_one'),'no'=>$kk+1,'state'=>'no'));
                        exit;
                    }

                    if(!$this->Wclient->check_next_level($po['category_one'], $po['category_two'])){
                        echo json_encode(array('msg'=>L('category_two'),'no'=>$kk+1,'state'=>'no'));
                        exit;
                    }

                }
            }

            foreach($pro_list as $k=>$v){           //验证价格和数量
                if(empty($v['price'])){
                    echo json_encode(array(
                        'msg'=>'[' . L('l__price') . '] ' . L('l_empty'),
                        'state'=>'no',
                    ));
                    die;
                }
                if(!is_numeric($v['price'])){
                    echo json_encode(array(
                        'msg'=>'[' . L('l__price') . '] ' . L('l_mb_num'),
                        'state'=>'no',
                    ));
                    die;
                }
                if($v['price']<=0){
                    echo json_encode(array(
                        'msg'=>'[' . L('l__price') . '] ' . L('l_eq_o'),
                        'state'=>'no',
                    ));
                    die;
                }
                if(empty($v['number'])){
                    echo json_encode(array(
                        'msg'=>'[' . L('l__number') . '] ' . L('l_empty'),
                        'state'=>'no',
                    ));
                    die;
                }
                if(!is_numeric($v['number'])){
                    echo json_encode(array(
                        'msg'=>'[' . L('l__number') . '] ' . L('l_mb_num'),
                        'state'=>'no',
                    ));
                    die;
                }
                if($v['number']<=0){
                    echo json_encode(array(
                        'msg'=>'[' . L('l__number') . '] ' . L('l_eq_o'),
                        'state'=>'no',
                    ));
                    die;
                }

                if(empty($v['detail'])){
                    $result = array('msg'=>L('goods_cannot_be_empty'),'no'=>$kk+1,'state'=>'no');
                    echo json_encode($result);exit;
                }

            }

            // 验证价格 start
            $LineCostObj = new \Lib10\LineAmount\LineCost();
            $l_line_id = $arr['TransferLine'];
            $l_recipient_arr = array('province'=>$arr['Province'],'city'=>$arr['City'],'town'=>$arr['Town'],'address'=>$arr['RecAddress']);
            $l_price_arr = array();
            foreach($pro_list as $lk=>$lv){
                $l_price_arr[$lk]['price'] = $lv['price'];          //单价
                $l_price_arr[$lk]['number'] = $lv['number'];        //数量
                $l_price_arr[$lk]['cid'] = $lv['category_two'];     //可以查找税金的分类id
            }

            if( ! $LineCostObj->cost($l_line_id, $l_price_arr,$l_recipient_arr) ){
                $ste_err = L($LineCostObj->getError());
                $parameter = $LineCostObj->parameter();
                $str_parameter = $parameter['1'];
                $msg = $ste_err . $str_parameter;
                //echo json_encode(array('msg'=>L($LineCostObj->getError()),'state'=>'no'));
                echo json_encode(array('msg'=>$msg,'state'=>'no'));
                exit;
            }
            //end


            $pro_list = array_values($pro_list);        // 数组的键值重新排序
            $count    = count($pro_list);               // 计算此数组的总数

            $goodsPrice = 0;        // 商品总价
            $goodsNum   = 0;        // 商品总数量
            foreach($pro_list as $k=>$vo){
                $goodsPrice += (intval($vo['number']) * floatval($vo['price']));
                $goodsNum += intval($vo['number']);
            }

            $arr['price']  = sprintf("%.2f", $goodsPrice);
            $arr['number'] = $goodsNum;
        
            if($count < 1){
                echo json_encode(array('state'=>'no','msg'=>L('GoodsListMsg')));
                exit;
            }

            $Wclient = $this->Wclient;

            //修改购物小票
            //liao ya di
            if(!empty($_FILES['receipt_img'])&&$_FILES['receipt_img']['error']==0){
                $test = $this->del_shopping_rec($id);
                $sr_data = $this->shopping_receipt();
            }else{
                $sr_data = array();
            }
            // die;


            $res = $Wclient->_saveEdit($user_id, $id, $num, $arr, $pro_list, $sr_data, $idcard);
            // echo json_encode($res);
            // die;


            if($res['state'] == 'yes'){
                //如果是从step_two页面发起 修改 请求的，则需要保存数据然后jq控制跳转回到step_two页面
                if($step != ''){

                    $uucode = authcode($res['uucode'], 'ENCODE', C('private_key'), 0);//加密
                    $sn     = authcode($res['sn'], 'ENCODE', C('private_key'), 0);//加密
                    $cid    = authcode($res['cid'], 'ENCODE', C('private_key'), 0);//加密
                    
                    $backArr = array('state'=>'yes','msg'=>L('save_success'),'url'=>U('Order/step_two',array('uucode'=>base64_encode($uucode),'sn'=>base64_encode($sn),'lid'=>base64_encode($cid))));
                }else{
                    $backArr = array('state'=>'yes','msg'=>L('save_success'),'url'=>U('Member/index'));
                }
            }else{
                $backArr = array('state'=>'no','msg'=>L('save_failed'));
            }

            echo json_encode($backArr);
            exit;
        }
        
    }




    /**
     * 表单提交之前后台验证数据
     * @return [type] [description]
     */
    public function checkForm(){

        if(IS_POST){

            session('pro_list',null);

            $user_id = session('user_id');

            $tranline = $this->tranline;
            $tranline = array_column($tranline, NULL, 'id');

            $arr = I('post.');


            $chelist = array(
                'PostName'     => L('PostNameMsg'),             // 寄件人姓名
                // 'PostAddress'  => L('PostAddressMsg'),       // 寄件人详细地址
                // 'PostCountry'  => L('PostCountryMsg'),       // 寄件人 国家 暂时默认 USA
                'PostState'    => L('PostStateMsg'),            // 寄件人 州
                'PostCity'     => L('PostCityMsg'),             // 寄件人城 市
                'PostStreet'   => L('PostStreetMsg'),           // 寄件人 街道
                'PostPhone'    => L('PostPhoneMsg'),            // 寄件人电话
                'PostCode'     => L('PostCodeMsg'),             // 寄件人邮编
                'RecName'      => L('RecNameMsg'),              // 收件人姓名
                'Province'     => L('ProvinceMsg'),             // 收件人 省
                'City'         => L('CityMsg'),                 // 收件人 市
//                'Town'         => L('TownMsg'),                 // 收件人 区
                'RecAddress'   => L('RecAddressMsg'),           // 收件人详细地址
                'RecPhone'     => L('RecPhoneMsg'),             // 收件人电话
                'RecCode'      => L('RecCodeMsg'),           // 收件人邮编
                'TransferLine' => L('TransferLineMsg'),         // 中转线路
                'Id_tpye'      => L('Id_tpyeMsg'),              // 证件类型
            );

            $arr['IdNo'] = strtoupper($arr['IdNo']);

            // 身份证是否必填
            $ID_card_is_empty = $this->Wclient->get_tranline();
            $list0 = array();
            foreach($ID_card_is_empty as $k=>$v){
                $list0[$v['id']] = $v['input_idno'];
            }
            $ID_card_is_empty = $list0;
            if($ID_card_is_empty[$arr['TransferLine']]==1 && empty($arr['IdNo'])){
                // 如果需要填写，而且也没有填写，则需要补填身份证号码
                $arr['id_no_status'] = '0';
                // 现在暂时先不允许为空
                // echo json_encode(array('msg'=>L('IdNoMsg'),'state'=>'no'));exit;
            }else if($ID_card_is_empty[$arr['TransferLine']]==1 && !empty($arr['IdNo'])){
                // 需要填写，也填写了
                $arr['id_no_status'] = '100';
            }else{
                // 无需填写
                $arr['id_no_status'] = '200';
            }


            // 验证字段是否为空
            foreach($chelist as $k=>$dis){
                if(trim($arr[$k]) == ''){
                    echo json_encode(array('msg'=>$chelist[$k],'state'=>'no'));
                    exit;
                }
            }

            if($arr['RecCode'] == '000000'){
                echo json_encode(array('msg'=>'收件人邮编不正确','state'=>'no'));
                exit;
            }


            /******************************************************/
            /*  开始身份证相关的处理  */
            $file_one_upload = \Lib11\IdcardUpload\IdcardUpload::file_upload($_FILES['file_one']);
            $file_two_upload = \Lib11\IdcardUpload\IdcardUpload::file_upload($_FILES['file_two']);

            if($file_one_upload['success'] && !empty($_FILES['file_one']) && $_FILES['file_one']['error']!=4){
                $front_idcard = $file_one_upload['info'];
                $front_idcard_small = $file_one_upload['small'];
            }else if(!empty($_FILES['file_one']) && $_FILES['file_one']['error']!=4){
                echo json_encode(array('msg'=>$file_one_upload['info'],'state'=>'no'));
                exit;
            }

            if($file_two_upload['success'] && !empty($_FILES['file_two']) && $_FILES['file_two']['error']!=4){
                $back_idcard = $file_two_upload['info'];
                $back_idcard_small = $file_two_upload['small'];
            }else if(!empty($_FILES['file_two']) && $_FILES['file_two']['error']!=4){
                echo json_encode(array('msg'=>$file_two_upload['info'],'state'=>'no'));
                exit;
            }

            $idcard_cli = new \HproseHttpClient(C('RAPIURL').'/IdcardInfo');

            // 是否是从身份证库中拿到的信息
            // 如果是从身份证库里拿到的信息，则身份证号码是一个无效的字段
            $idcard_id = I('post.lib_idcard');
            if(!empty($idcard_id)){
                $idcard_info = $idcard_cli->get_idcard_info_id($idcard_id);
                if($arr['id_no_status'] != '200'){
                    if(!empty($idcard_info['idno'])){
                        $arr['IdNo'] = $idcard_info['idno'];
                        $arr['id_no_status'] = '100';
                    }else{
                        $arr['IdNo'] = '';
                        $arr['id_no_status'] = '0';
                    }
                }
            }
            // 身份证库id
            $arr['lib_idcard'] = empty($idcard_id) ? 0 : $idcard_id;


            //验证身份证格式
            $arr['IdNo'] = trim($arr['IdNo']);
            if(!empty($arr['IdNo']) && $arr['id_no_status'] != '200'){
                if(!certificate($arr['IdNo'])){
                    echo json_encode(array('msg'=>L('id_not_correct'),'state'=>'no'));
                    exit;
                }
            }

            /* 进行实名认证 */
            $arr['RecName'] = trim($arr['RecName']);
            if(!empty($arr['RecName']) && !empty($arr['IdNo']) && $arr['id_no_status'] != '200'){
                $obj = new \Lib10\Idcardno\AliIdcardno();
                $idnoauth = $obj->IdentificationCard($arr['RecName'], $arr['IdNo']);
                if(!$idnoauth){
                    $arr['idno_auth'] = 2;
                    echo json_encode(array('msg'=>L($obj->getError()),'state'=>'no'));
                    exit;
                }
                $arr['idno_auth'] = 1;
            }else{
                $arr['idno_auth'] = 0;
            }

            /******************************************************/
            /* 当线路的 member_sfpic_state = 1，进行图片上传 */


            if($tranline[$arr['TransferLine']]['member_sfpic_state'] == 1){

                if($arr['pic_radio'] == '1'){
                    // 寄件人上传

                    // 选择收件人时上传的身份证图片
                    $addr_pro_img = I('post.addr_pro_img');
                    $addr_bak_img = I('post.addr_bak_img');
                    $addr_pro_img_small = I('post.addr_pro_img_small');
                    $addr_bak_img_small = I('post.addr_bak_img_small');

                    if(!empty($front_idcard) && !empty($back_idcard)){
                        // 取出session中的身份证数据 - 优先级高
                        $file_one = $front_idcard;
                        $file_one_small = $front_idcard_small;
                        $file_two = $back_idcard;
                        $file_two_small = $back_idcard_small;
                    }else if(!empty($idcard_info)){
                        // 拿到身份证库中的信息 - 优先级中
                        $file_one = $idcard_info['front_id_img'];
                        $file_one_small = $idcard_info['small_front_img'];
                        $file_two = $idcard_info['back_id_img'];
                        $file_two_small = $idcard_info['small_back_img'];
                    }else if(!empty($addr_pro_img) && !empty($addr_bak_img)){
                        // 没有上传和识别，但是选择了收件人且有图片
                        $file_one = $addr_pro_img;
                        $file_one_small = $addr_pro_img_small;
                        $file_two = $addr_bak_img;
                        $file_two_small = $addr_bak_img_small;
                    }else{
                        // 什么都没有
                        $file_one = '';
                        $file_one_small = '';
                        $file_two = '';
                        $file_two_small = '';
                    }

                    // 缺少正面照片或者背面照片，则报错
                    if(empty($file_one) || empty($file_two)){
                        echo json_encode(array('msg'=>L('idcard_no_photo'),'state'=>'no'));
                        exit;
                    }

                    // 清除session  - 验证成功后再清理
                    $this->clear_idcard_session();
                    
                    // 上传状态为已上传
                    // 上传方式为寄件人上传
                    $arr['id_img_status'] = '100';
                    $arr['certify_upload_type'] = '1';
                    unset($arr['pic_radio']);

                }else{
                    // 收件人上传

                    if(!empty($front_idcard) && !empty($back_idcard)){
                        // 取出session中的身份证数据 - 优先级高
                        $file_one = $front_idcard;
                        $file_one_small = $front_idcard_small;
                        $file_two = $back_idcard;
                        $file_two_small = $back_idcard_small;
                    }else if(!empty($idcard_info)){
                        // 如果有识别出来的，则即使选择了收件人上传，仍然是上传完成了
                        $file_one = $idcard_info['front_id_img'];
                        $file_one_small = $idcard_info['small_front_img'];
                        $file_two = $idcard_info['back_id_img'];
                        $file_two_small = $idcard_info['small_back_img'];
                    }else{
                        $file_one = '';
                        $file_one_small = '';
                        $file_two = '';
                        $file_two_small = '';
                    }

                    // 清除session - 直接清理，因为无论是验证成功还是失败，都会下单成功
                    $this->clear_idcard_session();

                    if((empty($file_one) || empty($file_two))){
                        $arr['id_img_status'] = '0';
                        $arr['certify_upload_type'] = '2';
                        unset($arr['pic_radio']);
                    }else{
                        $arr['id_img_status'] = '100';
                        $arr['certify_upload_type'] = '2';
                        unset($arr['pic_radio']);
                    }

                }

            }else{
                //无需上传

                $file_one = '';
                $file_one_small = '';
                $file_two = '';
                $file_two_small = '';

                $arr['id_img_status'] = '200';
                $arr['certify_upload_type'] = '1';
                unset($arr['pic_radio']);

            }

            // 身份证图片信息 - 待保存到身份证库的id
            $idcard['front_id_img'] = $file_one;
            $idcard['small_front_img'] = $file_one_small;
            $idcard['back_id_img'] = $file_two;
            $idcard['small_back_img'] = $file_two_small;
            $idcard['user_id'] = $user_id;
            $idcard['true_name'] = trim($arr['RecName']);
            $idcard['idno'] = trim($arr['IdNo']);
            $idcard['tel'] = trim($arr['RecPhone']);

            // 清除session
            $this->clear_idcard_session();

            /******************************************************/
            


            // 商品列表 把用户填写的商品整理到数组中
            $pro_list = array();
            $num      = 0;

            $arrs    = array_keys($arr);
            $arrc    = implode(',',$arrs);
            $arcount = substr_count($arrc,'brand_');

            // 最多只能有MAX_ORDERNO条货品
            if($arcount>MAX_ORDERNO){
                echo json_encode(array('msg'=>str_replace('{**}', MAX_ORDERNO, L('almost_in_ten')),'state'=>'no'));
                exit;
            }
             for($i=0; $i<MAX_ORDERNO; $i++){
                $pro_list[$i]['oid']          = I('post.oid_'.$i);
                $pro_list[$i]['brand']        = trim(I('post.brand_'.$i));
                $pro_list[$i]['detail']       = trim(I('post.detail_'.$i));
                $pro_list[$i]['catname']      = trim(I('post.catname_'.$i));
                $pro_list[$i]['price']        = trim(I('post.price_'.$i));
                $pro_list[$i]['number']       = trim(I('post.amount_'.$i));
                $pro_list[$i]['coin']         = trim(I('post.coin_'.$i));
                $pro_list[$i]['unit']         = trim(I('post.unit_'.$i));
                $pro_list[$i]['source_area']  = trim(I('post.source_area_'.$i));
                $pro_list[$i]['remark']       = trim(I('post.remark_'.$i));
                $pro_list[$i]['category_one'] = trim(I('post.category_one_'.$i)); // 一级类别ID
                $pro_list[$i]['category_two'] = trim(I('post.category_two_'.$i)); // 二级类别ID
                $pro_list[$i]['product_id']   = trim(I('post.product_type_'.$i)); // 货品类别ID
                $pro_list[$i]['spec_unit']       = trim(I('post.spec_unit_'.$i));

                // $pro_list[$i]['num_unit']       = trim(I('post.num_unit_'.$i));

                $pro_list[$i]['is_suit']       = trim(I('post.is_suit_'.$i));
                if(empty($pro_list[$i]['is_suit'])){
                    $pro_list[$i]['is_suit'] = 0;
                }else{
                    $pro_list[$i]['is_suit'] = 1;
                }

                if($pro_list[$i]['is_suit'] == 1){
                    $pro_list[$i]['num_unit'] = '套';
                    $pro_list[$i]['unit'] = '套';
                }else{
                    $pro_list[$i]['num_unit'] = '件';
                    $pro_list[$i]['unit'] = '件';
                }

                $num += intval(trim(I('post.amount_'.$i)));
            }

            if(empty($pro_list)){
                $result = array('msg'=>L('one_data'),'no'=>$kk+1,'state'=>'no');
                echo json_encode($result);exit;
            }


            if($tranline[$arr['TransferLine']]['bc_state'] == 1){           // 当选择的线路的bc_state=1的时候，检查各字段

                /* 暂停使用 */

                foreach($pro_list as $kk=>$po){
                    if(empty($po['category_one'])){
                        echo json_encode(array('msg'=>L('category_one'),'no'=>$kk+1,'state'=>'no'));
                        exit;
                    }

                    if(empty($po['category_two'])){
                        echo json_encode(array('msg'=>L('category_two'),'no'=>$kk+1,'state'=>'no'));
                        exit;
                    }

                    if(empty($po['product_id'])){
                        echo json_encode(array('msg'=>L('product_type'),'no'=>$kk+1,'state'=>'no'));
                        exit;
                    }
                }
            
            }else if($tranline[$arr['TransferLine']]['cc_state'] == 1){     // 当选择的线路的cc_state=1的时候，检查各字段

                // 去除空项
                $pro_tmp = [];
                foreach($pro_list as $kk=>$po){
                    if(empty($po['category_one']) && empty($po['category_two']) && empty($po['brand']) && empty($po['detail']) &&
                        empty($po['price']) && empty($po['number']) && empty($po['spec_unit'])){
                        continue;
                    }else{
                        $pro_tmp[$kk] = $po;
                    }
                }
                $pro_list = $pro_tmp;

                if(empty($pro_list)){
                    $result = array('msg'=>L('one_data'),'no'=>$kk+1,'state'=>'no');
                    echo json_encode($result);exit;
                }
                
                foreach($pro_list as $kk=>$po){
                    if(empty($po['category_one'])){
                        echo json_encode(array('msg'=>L('category_one'),'no'=>$kk+1,'state'=>'no'));
                        exit;
                    }

                    if(empty($po['category_two'])){
                        echo json_encode(array('msg'=>L('category_two'),'no'=>$kk+1,'state'=>'no'));
                        exit;
                    }

                    if(empty($po['brand'])){
                        echo json_encode(array('msg'=>L('brand'),'no'=>$kk+1,'state'=>'no'));
                        exit;
                    }

                    if(empty($po['detail'])){
                        echo json_encode(array('msg'=>L('detail'),'no'=>$kk+1,'state'=>'no'));
                        exit;
                    }

                    if(empty($po['price'])){
                        echo json_encode(array('msg'=>L('price'),'no'=>$kk+1,'state'=>'no'));
                        exit;
                    }

                    if(empty($po['number'])){
                        echo json_encode(array('msg'=>L('number'),'no'=>$kk+1,'state'=>'no'));
                        exit;
                    }

                    if(empty($po['spec_unit'])){
                        echo json_encode(array('msg'=>L('spec_unit'),'no'=>$kk+1,'state'=>'no'));
                        exit;
                    }


                    if(!$this->Wclient->check_first_level($arr['TransferLine'], $po['category_one'])){
                        echo json_encode(array('msg'=>L('category_one'),'no'=>$kk+1,'state'=>'no'));
                        exit;
                    }

                    if(!$this->Wclient->check_next_level($po['category_one'], $po['category_two'])){
                        echo json_encode(array('msg'=>L('category_two'),'no'=>$kk+1,'state'=>'no'));
                        exit;
                    }

                }

            }


            // 验证价格和数量
            // liao ya di
            foreach($pro_list as $k=>$v){
                if(!empty($v['catname']) && strlen((string)$v['catname'])>49){
                    echo json_encode(array(
                        'msg'=>'[' . L('l__catname') . '] ' . L('l_max_len'),
                        'state'=>'no',
                    ));
                    die;
                }
                if(empty($v['price'])){
                    echo json_encode(array(
                        'msg'=>'[' . L('l__price') . '] ' . L('l_empty'),
                        'state'=>'no',
                    ));
                    die;
                }
                if(!is_numeric($v['price'])){
                    echo json_encode(array(
                        'msg'=>'[' . L('l__price') . '] ' . L('l_mb_num'),
                        'state'=>'no',
                    ));
                    die;
                }
                if($v['price']<=0){
                    echo json_encode(array(
                        'msg'=>'[' . L('l__price') . '] ' . L('l_eq_o'),
                        'state'=>'no',
                    ));
                    die;
                }
                if(empty($v['number'])){
                    echo json_encode(array(
                        'msg'=>'[' . L('l__number') . '] ' . L('l_empty'),
                        'state'=>'no',
                    ));
                    die;
                }
                if(!is_numeric($v['number'])){
                    echo json_encode(array(
                        'msg'=>'[' . L('l__number') . '] ' . L('l_mb_num'),
                        'state'=>'no',
                    ));
                    die;
                }
                if($v['number']<=0){
                    echo json_encode(array(
                        'msg'=>'[' . L('l__number') . '] ' . L('l_eq_o'),
                        'state'=>'no',
                    ));
                    die;
                }

                if(empty($v['detail'])){
                    $result = array('msg'=>L('goods_cannot_be_empty'),'no'=>$kk+1,'state'=>'no');
                    echo json_encode($result);exit;
                }

            }

            // 验证价格 start
            $LineCostObj = new \Lib10\LineAmount\LineCost();
            $l_line_id = $arr['TransferLine'];//线路id
            $l_recipient_arr = array('province'=>$arr['Province'],'city'=>$arr['City'],'town'=>$arr['Town'],'address'=>$arr['RecAddress']);
            $l_price_arr = array();
            foreach($pro_list as $lk=>$lv){
                $l_price_arr[$lk]['price'] = $lv['price'];          //单价
                $l_price_arr[$lk]['number'] = $lv['number'];        //数量
                $l_price_arr[$lk]['cid'] = $lv['category_two'];     //可以查找税金的分类id
            }
            if( ! $LineCostObj->cost($l_line_id, $l_price_arr, $l_recipient_arr) ){
                $ste_err = L($LineCostObj->getError());
                $parameter = $LineCostObj->parameter();
                $str_parameter = $parameter['1'];
                $msg = $ste_err . $str_parameter;
                echo json_encode(array('msg'=>$msg,'state'=>'no'));
                exit;
            }
            //end

            $pro_list = array_values($pro_list);    // 数组的键值重新排序



            $count    = count($pro_list);           // 计算此数组的总数
            // 验证货品声明是否至少填写一行
            if($count < 1){
                echo json_encode(array('msg'=>L('GoodsListMsg'),'state'=>'no'));
                exit;
            }


            // 验证通过，则保存订单

            $res = $this->addOrder($num, $arr, $pro_list, $idcard);

            if($res['state'] == 'no'){          // 保存失败
                $res['msg'] = L($res['code']);
                echo json_encode($res);
                exit;
            }else{                              // 保存成功

                // 生成收件人数据，添加到收件人列表
                if($_POST['is_save_addr'] == '1'){

                    $addr_data = array(
                        'user_id' => session('mkuser.uid'),
                        'name' => $arr['RecName'],
                        'tel' => $arr['RecPhone'],
                        'province' => $arr['Province'],
                        'city' => $arr['City'],
                        'town' => $arr['Town'],
                        'address' => $arr['RecAddress'],
                        'postal_code' => $arr['RecCode'],
                        'cre_type' => $arr['Id_tpye'],
                        'cre_num' => $arr['IdNo'],
                        'line_id' => $arr['TransferLine'],
                    );
    
                    if(!empty($idcard['front_id_img'])){
                        $addr_data['id_card_front'] = $idcard['front_id_img'];
                        $addr_data['id_card_front_small'] = $idcard['small_front_img'];
                    }
    
                    if(!empty($idcard['back_id_img'])){
                        $addr_data['id_card_back'] = $idcard['back_id_img'];
                        $addr_data['id_card_back_small'] = $idcard['small_back_img'];
                    }


                    $this->insert_addr_list($addr_data);

                }

                //生成寄件人数据
                $sender_data = array(
                    's_name' => trim(I('post.PostName')),
                    's_street' => trim(I('post.PostStreet')),
                    's_country' => trim(I('post.PostCountry')),
                    's_state' => trim(I('post.PostState')),
                    's_city' => trim(I('post.PostCity')),
                    's_tel' => trim(I('post.PostPhone')),
                    's_code' => trim(I('post.PostCode')),
                    'user_id' => session("user_id"),
                );
                $this->insert_sender($sender_data);

                // END


                $uucode = authcode($res['uucode'], 'ENCODE', C('private_key'), 0);
                $sn     = authcode($res['sn'], 'ENCODE', C('private_key'), 0);
                $cid    = authcode($res['cid'], 'ENCODE', C('private_key'), 0);
                echo json_encode(array(
                    'state'=>'yes',
                    'url'=>U('Order/step_two',array('uucode'=>base64_encode($uucode),
                                                    'sn'=>base64_encode($sn),
                                                    'lid'=>base64_encode($cid)
                                              ))
                ));
                exit;
            }

        }
    }



    /**
     * 订单保存   checkForm()数据 校验通过 后执行 订单数据 保存
     * @return [type] [description]
     */
    public function addOrder($num, $arr, $pro_list, $idcard){

        $user_id   = session('mkuser.uid');         // 获取当前登陆的用户id
        $user_name = session('mkuser.username');    // 获取当前登陆的用户名
        // $uucode  = session('ship');              // 根据session中的ship判断是否已经生成过一个随机码
        $goodsPrice = 0;        // 商品总价
        $goodsNum   = 0;        // 商品总数量
        foreach($pro_list as $k=>$vo){
            $goodsPrice += (intval($vo['number']) * floatval($vo['price']));
            $goodsNum += intval($vo['number']);
        }

        $arr['price']  = sprintf("%.2f", $goodsPrice);
        $arr['number'] = $goodsNum;

        // 上传购物小票图片
        $data = $this->shopping_receipt();

        $Wclient = $this->Wclient;

        $res = $Wclient->_addOrder($user_id,$user_name,$num,$arr,$pro_list,C('Web_Config'),$data,$idcard);     // 保存订单数据

        if(!empty($res['catch_content']) && $res['state'] == 'yes'){
            // 设置redis消息队列
            $queue = new \Lib11\Queue\JoinQueue();
            $queue->join_queue($res['catch_content']);
        }

        return $res;
    }


    /**
     * [step_two 订单信息确认页面]
     * @param  [type] $user_id [用户ID]
     * @param  [type] $sn      [内部订单号]
     * @param  [type] $uucode  [凭证号]
     * @return [type]          [description]
     */
    public function step_two(){
        $uucode = base64_decode(trim(I('uucode')));         // 凭证号
        $sn     = base64_decode(trim(I('sn')));             // 内部订单号
        $lid    = base64_decode(trim(I('lid')));            // 订单ID

        $uucode = authcode($uucode, 'DECODE', C('private_key'), 0);
        $sn     = authcode($sn, 'DECODE', C('private_key'), 0);
        $lid    = authcode($lid, 'DECODE', C('private_key'), 0);

        $user_id = session('mkuser.uid');

        $Wclient = $this->Wclient;
        $res = $Wclient->_step_two($user_id,$sn,$uucode);   // 获取订单信息


        /* 显示已上传的证件照正反面图片 */
        // 证件照正面文件名不为空
        if($res['info']['lib_idcard'] != 0){
            $this->assign('front_id_img', C('TMPL_PARSE_STRING.__MEMBER__').'/images/upload_success_f.png');   // 显示默认国徽图片
            $this->assign('back_id_img', C('TMPL_PARSE_STRING.__MEMBER__').'/images/upload_success_b.png'); // 显示默认照片图片
            $res['info']['idno'] = idcard_format($res['info']['idno']);
        }else{
            if($res['info']['front_id_img'] != ''){
//                $this->assign('front_id_img', WU_FILE.$res['info']['front_id_img']);    // 显示证件照正面图片
                $this->assign('front_id_img', C('TMPL_PARSE_STRING.__MEMBER__').'/images/upload_success_f.png');   // 显示默认国徽图片
            }else{
                $this->assign('front_id_img', C('TMPL_PARSE_STRING.__MEMBER__').'/images/pho_front.png');   // 显示默认图片
            }
            if($res['info']['back_id_img'] != ''){
//                $this->assign('back_id_img', WU_FILE.$res['info']['back_id_img']);      // 显示证件照反面图片
                $this->assign('back_id_img', C('TMPL_PARSE_STRING.__MEMBER__').'/images/upload_success_b.png'); // 显示默认照片图片
            }else{
                $this->assign('back_id_img', C('TMPL_PARSE_STRING.__MEMBER__').'/images/pho_back.png'); // 显示默认图片
            }
        }
        /* 显示已上传的证件照正反面图片 end */

        // 验证订单，验证不通过则跳转错误，不显示页面
        if($res === false){
            $this->error(L('wrong_operate'),U('Member/index'),3);exit;
        }

        // 加载 线路种类
        $tranline = $this->tranline;

        $alist = array();
        foreach($tranline as $item){
            $alist[$item['id']] = $item['lngname'];
        }

        $tranline = array_column($tranline, NULL, 'id');//二维数组中的一维数组的id作为二维数组的键名

        // $goodsPrice = sprintf("%.2f", $goodsPrice);
        $trankd = $res['center']['id'];
        $Web_Config = (isset(C('Web_Config')[$trankd])) ? C('Web_Config')[$trankd] : '';
        $freight  = sprintf("%.2f",floatval($Web_Config['Price']));
        $discount = sprintf("%.2f", floatval($Web_Config['Price']) * floatval($Web_Config['Discount']));
        $charge   = sprintf("%.2f",floatval($Web_Config['Charge']));

        $ID_TYPE = C('ID_TYPE');                // 证件类型

        $cat_list = $Wclient->cat_list();       // 查询所有类别
        $cat_list = array_column($cat_list, NULL, 'id');    // 二维数组中的一维数组的id作为二维数组的键名
        
        if($res['center']['cc_state'] == '1' && $res['center']['tax_kind'] == '1'){
            // 根据汇率计算出美元免税的额度
            //$free_duty = sprintf("%.2f", floatval(C('RMB_Free_Duty')) / floatval(C('US_TO_RMB_RATE')));
            if(floor($res['center']['taxthreshold']) > 0){
                $free_duty = sprintf("%.2f", $res['center']['taxthreshold'] / floatval(C('US_TO_RMB_RATE')));
            }else{
                $free_duty = sprintf("%.2f", floatval(C('RMB_Free_Duty')) / floatval(C('US_TO_RMB_RATE')));
            }
            $free_duty = sprintf("%.2f", $free_duty);
        }else{
            $free_duty = '';
        }

        self::assign('free_duty',$free_duty);
        self::assign('cat_list',$cat_list);                         // 类别列表
        self::assign('TranKd',$res['info']['TranKd']);              // 线路id
        self::assign('count',count($res['pro_list']));              // 货品声明 总数
        self::assign('pro_list',$res['pro_list']);                  // 货品列表
        self::assign('alist',$alist);                               // 中转线路
        self::assign('info',$res['info']);                          // 寄件人，收件人信息
        self::assign('cid',$lid);                                   // 订单ID
        self::assign('sn',urlencode(base64_encode($sn)));           // 内部订单号
        self::assign('uucode',urlencode(base64_encode($uucode)));   // 凭证号
        self::assign('member_sfpic_state',$res['center']['member_sfpic_state']);
        self::assign('freight',$freight);                           // 运费
        self::assign('discount',$discount);                         // 折扣金额
        self::assign('charge',$charge);                             // 手续费
        self::assign('ID_TYPE',$ID_TYPE);                           // 证件类型
        self::assign('tranline',$tranline);                         // 证件类型
        $this->display();
    }


    /**
     * 打印随机码  视图 ok
     * @return [type] [description]
     */
    public function step_three(){

        $id = I('cid');
        $cname = I('cname');

        $user_id = session('mkuser.uid');

        $Wclient = $this->Wclient;
        $res = $Wclient->tofinish($user_id,$id);

        // Jie 20151120 检查是否存在或是否属于该登陆会员
        if(!$res){
            $this->redirect('Public/404');
        }

        //生成二维码 Jie 20151126
        self::assign('res',$res);
        $this->display();
    }

    /**
     * 条形码生成 方法
     * @return [type] [description]
     */
    public function barcode(){
        $barcode = new \Libm\barcode\barcodeApi();
        $barcode->text = trim(I('text'));
        echo $barcode->png();
    }
    
    /**
     * 二维码生成 方法  前端页面请求数据后返回二维码图片 Jie 20151126
     * @param  string  $url     [生成链接地址]
     * @param  string  $outfile [输出图片类型]
     * @param  integer $level   [容错级别]
     * @param  integer $size    [图片大小]
     * @return [type]           [description]
     */
    public function qrcode(){
        $qrcode = new \Libm\phpqrcode\qrcodeApi();
        $qrcode->text = trim(I('get.url'));
        echo $qrcode->png();
    }

    /**
     * 再次下单  视图 ok
     * @return [type] [description]
     */
    public function newOrder(){
        //生成一个一次性的令牌以防止重复提交表单
        $_SESSION['token'] = md5(microtime(true));

        // session('ship',NULL);   //清除session
        $id = I('get.id');
        $user_id = session('mkuser.uid');

        $Q_no = I('get.Q_no');

        $Wclient = $this->Wclient;
        $res = $Wclient->_newOne($id, $Q_no);

        // 清空身份证识别的session
        $this->clear_idcard_session();

		if(!$res){
			echo '<h3>该单在打单时出现问题，请与店员或客服联系，给你带来不便，敬请原谅</h3>';
			die;
		}

        $tranline = $this->tranline;

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

        if(empty($category_list)){
            $this->assign('category_list', "{}");
        }else{
            $this->assign('category_list', json_encode($category_list));
        }

        $idcard_cli = new \HproseHttpClient(C('RAPIURL').'/IdcardInfo');
        $result = $idcard_cli->get_idcard_info_no_examine($res['info']['receiver'], $res['info']['reTel'], $res['info']['idno'], $user_id)[0];

        if(!empty($result)){
            $res['info']['lib_idcard'] = $result['id'];
            $res['info']['idno_old'] = $res['info']['idno'];
            $res['info']['idno'] = idcard_format($res['info']['idno']);

            unset($res['info']['id_card_back']);
            unset($res['info']['id_card_back_small']);
            unset($res['info']['id_card_front']);
            unset($res['info']['id_card_front_small']);

            $res['info']['id_card_back'] = C('TMPL_PARSE_STRING')['__MEMBER__'] . '/images/upload_success_b.png';
            $res['info']['id_card_front'] = C('TMPL_PARSE_STRING')['__MEMBER__'] . '/images/upload_success_f.png';
        }else{
            $res['info']['lib_idcard'] = 0;
        }


        $ID_TYPE = C('ID_TYPE');//证件类型
        self::assign('TranKd',$res['info']['TranKd']);     //线路id
        self::assign('tranline',$tranline);
        self::assign('info',$res['info']);
        self::assign('type','newOrder');   //是否为再次下单页面发起的请求
        self::assign('user_id',$user_id);
        self::assign('ID_TYPE',$ID_TYPE);//证件类型
        $this->display();
    }

    /**
     * 修改 视图 ok
     * @return [type] [description]
     */
    public function edit(){

        $category_list = array();
        $Host = C('Redis')['Host'];
        $Port = C('Redis')['Port'];
        $Auth = C('Redis')['Auth'];
        try{
            $redis = new \Redis();
            $redis->connect($Host,$Port,5);
            $redis->auth($Auth);

            $category_list = unserialize($redis->get('category_list'));
        }catch(\RedisException $e){
            
        }
        // dump($category_list);
        if(empty($category_list)){
            $this->assign('category_list', "{}");
        }else{
            $this->assign('category_list', json_encode($category_list));
        }

        // 清空身份证识别的session
        $this->clear_idcard_session();

        $id   = (I('get.id')) ? trim(I('get.id')) : '';
        //用于判断是否经由step_two发起的修改请求，是有别于正常途径的修改的
        $step = (I('get.step')) ? trim(I('get.step')) : '';

        //检查是否传入ID
        if($id == ''){
            $this->error(L('wrong_operate'),U('Member/index'),3);
        }

        $user_id = session('mkuser.uid');

        $Wclient = $this->Wclient;
        $res = $Wclient->_edit($id);

        if($res['info']['lib_idcard'] != 0){
            $res['info']['idno'] = idcard_format($res['info']['idno']);
        }

        //如果未填写身份证，则身份证图片不可能存在（如果存在，其实那是一个错误的记录）
		if(empty($res['info']['idno'])){
			$res['info']['front_file_name'] = '';
			$res['info']['back_file_name'] = '';
			$res['info']['front_id_img'] = '';
			$res['info']['back_id_img'] = '';
		}

        //验证订单，验证不通过则跳转错误，不显示页面
        if($res['info']['pay_state'] == '1'){
            $this->error(L('wrong_operate'),U('Member/index'),3);exit;
        }

        $tranline = $this->tranline;

        $ID_TYPE = C('ID_TYPE');//证件类型

        self::assign('TranKd',$res['info']['TranKd']);     //线路id

        self::assign('tranline',$tranline); //全部线路类型
        self::assign('info',$res['info']);   //订单信息
        self::assign('type','edit');   //是否为编辑页面发起的请求
        self::assign('user_id',$user_id);   //用户id
        self::assign('id',$id);   //订单ID
        self::assign('step',$step);   //用于判断是否经由step_two发起的修改请求，是有别于正常途径的修改的
        self::assign('ID_TYPE',$ID_TYPE);   //证件类型

        $this->display();
    }


    /**
     * 用于编辑的时候删除某一行货品声明
     * @return [type] [description]
     */
    public function del(){

        if(!IS_AJAX){
            $result = array('state'=>'404','msg'=>L('illegal_operation'));
            return $result;
            die;
        }

        $oid = I('post.ttc');

        $Wclient = $this->Wclient;

        $result = $Wclient->delete($oid);
        
        //根据多语言输出错误信息
        $result['msg'] = L($result['code']);

        $this->ajaxReturn($result);
    }


    //删除订单的同时删除购物小票
    //liao ya di
    public function del_shopping_receipt(){

        $oid = I('get.order_id');
        if(!empty($oid)){
            $this->del_shopping_rec($oid);
            $pie = new \HproseHttpClient(C('RAPIURL').'/Piecemeal');
            $pie->del_shopping_rec($oid);
        }

    }

    public function order_res_text(){
        $this->display();
    }



    // 导出excel

    /**
     *
     */
    public function export(){

        $ids = I('get.ids');
        $user_id = session('user_id');

        $sf = [
            ['sender', '发件人姓名'],
            ['sendTel', '发件人手机号码'],
            ['sendAddr', '发件人地址'],
            ['sendcode', '发件人邮编'],
            ['receiver', '收件人姓名'],
            ['reTel', '收件人手机号码'],
            ['reAddr', '收件人详细地址'],
            ['postcode', '收件人邮编'],
            ['idno', '收件人身份证号码'],
            ['number', '商品总数量'],
            ['price', '商品总价'],
            ['ordertime', '下单时间'],
            ['package_id', '外部订单号'],
            ['order_no', '美快订单号'],
            ['MKNO', '美快运单号'],
            ['STNO', '快递单号'],
            ['line_name', '线路'],
            ['weight', '总重量'],
            ['tax', '总税金'],
            ['freight', '运费'],
            ['ex_context', '当前物流状态'],
        ];

        $where['user_id'] = $user_id;
        $where['id'] = ['in', $ids];

        $result = $this->Wclient->get_export_info($where);

        foreach($result as $k=>$v){
            $result[$k]['line_name'] = L('MKLINES')[$v['line_name']];
            if(empty($v['ex_context'])){
                $result[$k]['ex_context'] = '';
            }
        }

//        dump($result); die;

        $header = [];
        $body = [];

        foreach($sf as $k=>$v){
            $header[$k] = $v[1];
        }

        foreach($result as $k1=>$v1){
            foreach($sf as $k2=>$v2){
                $body[$k1][$k2] = $v1[$v2[0]];
            }
        }

        foreach($body as $k1=>$v1){
            foreach($v1 as $k2=>$v2){
                $body[$k1][$k2] = ' ' . (string)$v2;
            }
        }

//        dump($header); dump($body); die;

        $excel = new \WebUser\PHPExcel\PHPExcel();
        $excel->write_empty($header, $body, '订单信息' . date("Ymd"));


    }

}