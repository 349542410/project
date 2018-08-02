<?php
/**
 * 在线下单
 */
namespace Api\Controller;
use Think\Controller\HproseController;
use Think\Log;

class OrderController extends HproseController {
    protected $crossDomain =    true;
    protected $P3P         =    true;
    protected $get         =    true;
    protected $debug       =    true;



    public function get_tranline(){

        return M('TransitCenter')->where(array('status'=>1,'optional'=>1))->order('id desc')->select();

    }



    //step_two用 所有类别列表
    public function cat_list(){
        $cat_list = M('CategoryList')->field('id,fid,cat_name,price')->order('sort asc, cat_name asc')->select();
        return $cat_list;
    }

    // 查询分类id是否属于该线路
    public function check_first_level($line_id, $cid){
        $id = M('CategoryList')->where(array('TranKd'=>array('like','%,'.$line_id.',%'), 'id'=>array('eq', $cid)))->getfield('id');
        return empty($id) ? false : true;
    }

    // 查询二级分类id是否属于一级分类
    public function check_next_level($fid, $cid){
        $id = M('CategoryList')->where(array('id'=>$cid))->getField('fid');
        if(!empty($id) && ($id == $fid)){
            return true;
        }else{
            return false;
        }
    }

    //查出 所有一级类别列表
    public function first_level($lineID=''){
        if($lineID != '') $map['TranKd']  = array('like','%,'.$lineID.',%');
        $map['is_show'] = array('eq',1);
        $map['status']  = array('eq',1);
        $map['fid']     = array('eq',0);
        $cat_list = M('CategoryList')->field('id,fid,cat_name,price')->where($map)->order('sort asc, cat_name asc')->select();
        return $cat_list;
    }

    /**
     * 根据上一级的类别ID找出对应的下一级分类
     * @param  [type] $id [上级ID]
     * @return [type]     [description]
     */
    public function _next_level($id){
        $map['a.is_show'] = array('eq',1);
        $map['a.status']  = array('eq',1);
        $map['a.fid']     = array('eq',$id);

        // $next_list = M('CategoryList')->field('id,fid,cat_name,price,hs_code')->where($map)->order('sort asc, cat_name asc')->select();
        // if(!empty($next_list)){
        //     foreach($next_list as $k=>$v){
        //         $next_list[$k]['spec_unit'] = M('TaxRulesClass')->field('specifications as spec_unit')->where(array('hs_code'=>$v['hs_code']))->select();
        //         $next_list[$k]['num_unit'] = M('TaxRulesClass')->field('number as num_unit')->where(array('hs_code'=>$v['hs_code']))->select();
        //     }
        // }

        $next_list = M('CategoryList')->alias('a')
                                    ->field('a.id as id,a.fid as fid,a.cat_name as cat_name,a.price as price,b.specifications as spec_unit,b.number as num_unit')
                                    ->where($map)
                                    ->join('left join mk_tax_rules_class b on a.hs_code=b.hs_code')
                                    ->order('a.sort asc, a.cat_name asc')
                                    ->select();

        // $next_list = (count($next_list) > 0) ? $next_list : array(array('id'=>'','fid'=>'','cat_name'=>'无','price'=>'0'));
        $next_list = (count($next_list) > 0) ? $next_list : array();
        return $next_list;

        
    }

    /**
     * 根据二级类别ID找出对应其对应的货品列表
     * @param  [type] $id      [上级ID]
     * @param  [type] $keyword [搜索关键字]
     * @return [type]          [description]
     */
    public function _product($id, $keyword){
        $map = array();
        $map['cat_id']  = array('eq',$id);//二级类别的id
        if($keyword != '') $map['name'] = array('like','%'.$keyword.'%');//关键字

        $next_list = M('ProductList')->field('id,show_name as name,cat_id,price as pro_price,unit,source_area')->where($map)->order('ctime desc')->select();
        // $next_list = (count($next_list) > 0) ? $next_list : array(array('id'=>'','name'=>'无','cat_id'=>'','price'=>'0'));
        return $next_list;
    }

    //会员下单，默认显示该会员 上一份件的寄件人信息
    public function _index($user_id){
        return M('TranUlist')->field('sender as PostName,sendAddr as PostAddress,sendTel as PostPhone,sendcode as PostCode,sendCountry as PostCountry,sendState as PostState,sendCity as PostCity,sendStreet as PostStreet')->where(array('user_id'=>$user_id))->order('id desc')->find();
    }

    /**
     * 在线下单，再次下单  订单数据保存
     * @param [type] $user_id      [用户ID]
     * @param [type] $user_name    [用户名]
     * @param [type] $num          [商品总数量]
     * @param [type] $arr          [订单信息]
     * @param [type] $pro_list     [订单对应的商品声明数组]
     * @param [type] $Web_Config   [线路价格等配置信息]
     * @param [type] $need_upload  [用于判断 是否需要保存上传的证件照]
     */
    public function _addOrder($user_id,$user_name,$num,$arr,$pro_list,$Web_Config,$s_data=array(), $idcard){

        // 如果没有匹配的数据，则生成新的随机码
        $uucode = randomCode($user_id);         // 获取随机码
        $uucode = strtoupper($uucode);

        $sn        = StrOrderOne($user_id);     // 生成订单号
        $ordertime = date("Y-m-d H:i:s");       // 下单时间

        $center = M('TransitCenter')->where(array('id'=>$arr['TransferLine']))->find();

        // 判断传来的中转线路ID是否有对应的配置信息
        if(!is_array($center)){
            return array('state'=>'no', 'msg'=>'中转线路不存在', 'code'=>'tranline_not_exist');
        }

        $data['user_id']         = $user_id;                    // 会员id
        $data['buyers_nickname'] = $user_name;                  // 会员昵称
        $data['sender']          = trim($arr['PostName']);      // 发件人
        $data['sendCountry']     = 'USA';                       // 寄件人 国家 暂时默认 USA         //trim($arr['PostCountry']);
        $data['sendState']       = trim($arr['PostState']);     // 寄件人 州
        $data['sendCity']        = trim($arr['PostCity']);      // 寄件人 市
        $data['sendStreet']      = trim($arr['PostStreet']);    // 寄件人 街道
        $data['sendAddr']        = trim($arr['PostStreet'])." ".trim($arr['PostCity'])." ".trim($arr['PostState']);  // 发件人详细地址
        $data['sendTel']         = trim($arr['PostPhone']);     // 发件人电话
        $data['sendcode']        = trim($arr['PostCode']);      // 发件人邮编
        $data['receiver']        = trim($arr['RecName']);       // 收件人
        $data['province']        = trim($arr['Province']);      // 省
        $data['city']            = trim($arr['City']);          // 地级市
        $data['town']            = trim($arr['Town']);          // 市、县、区
        $data['reAddr']          = trim($arr['Province'])." ".trim($arr['City'])." ".trim($arr['Town'])." ".trim($arr['RecAddress']);     // 收件人详细地址
        $data['reTel']           = trim($arr['RecPhone']);      // 收件人电话
        $data['postcode']        = trim($arr['RecCode']);       // 收件人邮编
        $data['number']          = trim($arr['number']);        // 数量
        $data['price']           = trim($arr['price']);         // 商品的总价
        $data['TranKd']          = trim($arr['TransferLine']);  // 中转线路
        $data['email']           = '';                          // 国内收件人email
        $data['ctime']           = date('Y-m-d H:i;s');         // 创建时间
        $data['random_code']     = $uucode;                     // 随机码
        $data['premium']         = 0;                           // 保险金额
        $data['order_no']        = $sn;                         // 下单单号
        $data['ordertime']       = $ordertime;                  // 下单时间
        $data['idkind']          = trim($arr['Id_tpye']);       // 证件类型
        $data['idno']            = trim($arr['IdNo']);          // 证件号码
        // $data['weight']          = $Web_Config['Weight'];    // 重量  由于尚未称重，暂时以对应的线路的首重代替
        // $data['freight']         = $Web_Config['Price'];     // 运费  由于尚未称重，暂时以对应的线路的首重的价格代替
        // $data['discount']        = $Web_Config['Discount'];  // 线路对应的折扣
        // $data['charge']          = $Web_Config['Charge'];    // 线路对应的服务费
        // $data[''] = trim($arr['pic_radio']);
        $data['id_img_status'] = $arr['id_img_status'];
        $data['id_no_status'] = $arr['id_no_status'];
        $data['idno_auth'] = $arr['idno_auth'];
        $data['certify_upload_type'] = $arr['certify_upload_type'];
        $data['lib_idcard'] = $arr['lib_idcard'];
        $data['front_id_img'] = $idcard['front_id_img'];
        $data['small_front_img'] = $idcard['small_front_img'];
        $data['back_id_img'] = $idcard['back_id_img'];
        $data['small_back_img'] = $idcard['small_back_img'];
        if(!empty($arr['package_id'])){
            $data['package_id'] = $arr['package_id'];
        }
        

        // 添加订单信息
        $cid = M('TranUlist')->add($data);


        // 生成mkno与key关联的记录
        $str1 = rand(1000,9999);
        $str2 = rand(700,9999);
        M('MknoKey')->add(array(
            'u_id' => $cid,
            'un_key' => $str2 . $cid . $str1,
        ));


        $catch_content = array();
        // 如果选择客人上传，则需要发送短信
        if(($arr['certify_upload_type'] == '2' && ($arr['id_img_status'] == '0') || $data['id_no_status'] == '0')){
            $catch_content['receiver'] = $data['receiver'];
            $catch_content['reTel'] = $data['reTel'];
            $catch_content['MknoKey'] = $str2 . $cid . $str1;
            $catch_content['time'] = time();
        }


        // 添加购物小票图片
        // liao ya di
        if($cid!==false&&!empty($s_data)){
            $s_data['order_id'] = $cid;
            M('ShoppingReceipt')->add($s_data);
        }


        if($cid){
            // 添加订单货品信息
            foreach($pro_list as $item){
                $item['lid'] = $cid;
                if($item['oid'] != ''){
                    M('TranUorder')->where(array('oid'=>$item['oid']))->save($item);
                }else{
                    unset($item['oid']);
                    M('TranUorder')->add($item);
                }
                if(!M('UserOrderGoodsName')->where(array('user_id' => $user_id,'goods_name' => $item['detail'],))->getfield('id')){
                    M('UserOrderGoodsName')->add(array(
                        'user_id' => $user_id,
                        'goods_name' => $item['detail'],
                    ));
                }
            }

            if(!empty($data['idno']) && !empty($data['back_id_img']) && !empty($data['front_id_img']))
            {
                // 写入身份证图片库
                $idcard_cli = new \Api\Controller\IdcardInfoController();
                if(!empty($idcard['front_id_img']) && !empty($idcard['back_id_img'])){
                    $extra_id = $idcard_cli->idno_save($idcard);
                    if(!empty($extra_id)){
                        // 如果写入成功，则将身份证库id回写到此订单中
                        M('TranUlist')->where(array('id'=>$cid))->save(array('lib_idcard'=>$extra_id));
                    }
                }
            }

            // 写入日志
            $logs['order_no']    = $sn;     // 内部订单号
            $logs['content']     = '您提交了订单，请等待称重计费';
            $logs['create_time'] = $ordertime;
            $logs['state']       = '3000';
            M('ULogs')->add($logs);
            return $res = array('state'=>'yes','cid'=>$cid,'uucode'=>$uucode,'sn'=>$sn, 'catch_content'=>$catch_content);
        }
    }


    /**
     * [_step_two 订单信息确认页面]
     * @param  [type] $user_id  [用户ID]
     * @param  [type] $order_no [内部订单号]
     * @param  [type] $uucode   [凭证号]
     * @return [type]           [description]
     */
    public function _step_two($user_id,$order_no,$uucode){
        //根据此内部订单号和账户ID找出一条匹配的数据
        $info = M('TranUlist t')
                ->field('t.*,tc.shop_state,sr.receipt_img')
//                ->field('t.*,e.front_id_img,e.back_id_img,e.front_file_name,e.back_file_name,tc.shop_state,sr.receipt_img')
                // ->join('left join mk_user_extra_info e on e.idno=t.idno and e.true_name=t.receiver')
//                ->join('left join mk_user_extra_info e on e.idno=t.idno and e.true_name=t.receiver and e.user_id='.$user_id)
                ->join('left join mk_transit_center tc on t.TranKd=tc.id')
                ->join('left join mk_shopping_receipt sr on t.id=sr.order_id')
                ->where(array('t.order_no'=>$order_no,'t.user_id'=>$user_id))
                ->find();

        //验证订单
        if(!$info){
            return false;
        }

        //检查订单是否已经支付，如果是，则终止界面显示并跳转页面
        if($info['pay_state'] == '1'){
            return false;
        }

        //查询该线路信息
        $center = M('TransitCenter')->where(array('id'=>$info['TranKd']))->find();

        $tax = 0;//税金总金额
        //检查该线路的 bc_state 是否为1
        if($center['bc_state'] == '1'){
            //订单相关商品信息
            $pro_list = M('TranUorder t')->field('t.oid,t.lid,t.number,t.weight,t.remark,t.auto_Indent1,t.auto_Indent2,t.category_one,t.category_two,p.show_name,p.name as detail,p.brand,p.hs_code,p.hgid,t.price,p.coin,p.unit,p.source_area,p.specifications,p.barcode,p.unit,p.source_area,c.cat_name as catname,c.price as tax_price,t.is_suit as is_suit')->join('left join mk_product_list p on p.id = t.product_id')->join('left join mk_category_list c on c.id = p.cat_id')->where(array('t.lid'=>$info['id']))->select();

            foreach($pro_list as $ko=>$item){
                $tax += floatval($item['number']) * floatval($item['tax_price']);//统计各个商品*其数量的总税金 之和
                $pro_list[$ko]['tax_price'] = sprintf("%.2f", (floatval($item['number']) * floatval($item['tax_price'])));//统计各个商品*其数量的总税金
            }
            
        }else if($center['cc_state'] == '1'){
            //订单相关商品信息
            $pro_list = M('TranUorder t')->field('t.*, c.price as tax_rate')->join('left join mk_category_list c on c.id = t.category_two')->where(array('t.lid'=>$info['id']))->select();

            //tax_rate 为百分比
            if($center['tax_kind'] == '1'){

                //需要根据二级类别的税率计算税金
                foreach($pro_list as $ko=>$item){
                    $tax += floatval($item['number']) * floatval($item['tax_rate']) * floatval($item['price']) / 100;//统计各个商品*其数量的总税金 之和
                    $pro_list[$ko]['tax_price'] = sprintf("%.2f", (floatval($item['number']) * floatval($item['tax_rate']) * floatval($item['price']) / 100));//统计各个商品*其数量的总税金
                }

            }else{//tax_rate 为固定值

                //需要根据二级类别的税值计算税金
                foreach($pro_list as $ko=>$item){
                    $tax += floatval($item['number']) * floatval($item['tax_rate']);//统计各个商品*其数量的总税金 之和
                    $pro_list[$ko]['tax_price'] = sprintf("%.2f", (floatval($item['number']) * floatval($item['tax_rate'])));//统计各个商品*其数量的总税金
                }
            }

        }else{

            //订单相关商品信息
            $pro_list = M('TranUorder')->field('brand,detail,catname,number,price,coin,remark,category_one,category_two,unit,source_area,is_suit')->where(array('lid'=>$info['id']))->select();
        }

        $info['tax']   = sprintf("%.2f", $tax);//总税金
        
        return array('info'=>$info,'pro_list'=>$pro_list, 'center'=>$center);
    }

    /**
     * 编辑
     * @return [type] [description]
     */
    public function _edit($id){
        $info = M('TranUlist t')
                ->field('t.*,sr.receipt_img')
                ->join('left join mk_shopping_receipt sr on t.id=sr.order_id')
                ->where(array('t.id'=>$id))
                ->find();
        /* 去除详细地址中的省市区和空格 */
        $info['reAddr'] = str_replace($info['town'],'',$info['reAddr']);
        $info['reAddr'] = str_replace($info['city'],'',$info['reAddr']);
        $info['reAddr'] = str_replace($info['province'],'',$info['reAddr']);
        $info['reAddr'] = str_replace(' ','',$info['reAddr']);

        return $res = array('info'=>$info);
    }


    /**
     * [_saveEdit 保存修改]
     * @param  [type] $user_id     [注册会员的ID]
     * @param  [type] $order_id    [订单ID]
     * @param  [type] $num         [订单商品总数量]
     * @param  [type] $arr         [订单信息]
     * @param  [type] $pro_list    [订单商品信息]
     * @param  [type] $need_upload [用于判断 是否需要保存上传的证件照]
     * @return [type]              [description]
     */
    public function _saveEdit($user_id,$order_id,$num,$arr,$pro_list,$sr_data,$idcard){

        $check = M('TranUlist')->where(array('id'=>$order_id,'user_id'=>$user_id))->find();     // 根据此随机码找出一条匹配的数据
        
        if(!$check){
             return array('state'=>'no','msg'=>'参数不存在');
        }

        // 当订单是 已支付 或者 已打印、打印中 的时候，不允许修改订单数据
        if($check['pay_state'] == '1'){
            return array('state'=>'no','msg'=>'订单已支付，无法修改');
        }

        $data['sender']           = trim($arr['PostName']);         // 寄件人
        $data['sendCountry']      = 'USA';                          // 寄件人 国家 暂时默认 USA                  //trim($arr['PostCountry']);
        $data['sendState']        = trim($arr['PostState']);        // 寄件人 州
        $data['sendCity']         = trim($arr['PostCity']);         // 寄件人 市
        $data['sendStreet']       = trim($arr['PostStreet']);       // 寄件人 街道
        $data['sendAddr']         = trim($arr['PostStreet'])." ".trim($arr['PostCity'])." ".trim($arr['PostState']);    // 发件人详细地址
        $data['sendTel']          = trim($arr['PostPhone']);        // 发件人电话
        $data['sendcode']         = trim($arr['PostCode']);         // 发件人邮编
        $data['receiver']         = trim($arr['RecName']);          // 收件人
        if(trim($arr['Province']) != '') $data['province']    = trim($arr['Province']);         // 收件人省
        if(trim($arr['City'])     != '') $data['city']        = trim($arr['City']);             // 收件人市
        if(trim($arr['Town'])     != '') $data['town']        = trim($arr['Town']);             // 市、县、区
        $data['reAddr']           = trim($arr['Province'])." ".trim($arr['City'])." ".trim($arr['Town'])." ".trim($arr['RecAddress']);     // 收件人详细地址
        $data['reTel']            = trim($arr['RecPhone']);         // 收件人电话
        $data['postcode']         = trim($arr['RecCode']);          // 收件人邮编
        $data['number']           = trim($arr['number']);           // 数量
        $data['price']            = trim($arr['price']);            // 商品的总价
        // $data['TranKd']           = trim($arr['TransferLine']);  // 中转线路
        $data['idkind']           = trim($arr['Id_tpye']);          // 证件类型
        $data['idno']             = trim($arr['IdNo']);             // 证件号码
        
        if(!empty($arr['id_img_status'])){
            $data['id_img_status'] = $arr['id_img_status'];
        }
        if(!empty($arr['id_no_status'])){
            $data['id_no_status'] = $arr['id_no_status'];
        }
        if(!empty($arr['lib_idcard'])){
            $data['lib_idcard'] = $arr['lib_idcard'];
        }

        // 如果有修改身份证号码
        if(!empty($idcard['front_id_img'])){
            $data['front_id_img'] = $idcard['front_id_img'];
            $data['small_front_img'] =  $idcard['small_front_img'];
        }
        if(!empty($idcard['back_id_img'])){
            $data['back_id_img'] = $idcard['back_id_img'];
            $data['small_back_img'] =  $idcard['small_back_img'];
        }

        $save_data = M('TranUlist')->where(array('id'=>$check['id']))->save($data);

        if($save_data !== false){
            $cid    = $check['id'];
            $uucode = $check['random_code'];
            $sn     = $check['order_no'];

            // 添加购物小票图片
            if(!empty($sr_data)){
                M('ShoppingReceipt')->where(array('order_id'=>$cid))->save($sr_data);
            }

            if(!empty($idcard['front_id_img']) && !empty($idcard['back_id_img']) && $arr['lib_idcard'] == 0){

                // 写入身份证图片库
                $idcard_cli = new \Api\Controller\IdcardInfoController();
                if(!empty($idcard['front_id_img']) && !empty($idcard['back_id_img'])){
                    $extra_id = $idcard_cli->idno_save($idcard);
                    if(!empty($extra_id)){
                        // 如果写入成功，则将身份证库id回写到此订单中
                        M('TranUlist')->where(array('id'=>$check['id']))->save(array('lib_idcard'=>$extra_id));
                    }
                }
            }


            // 按照oid从小到大排序根据tran_ulist.id进行查找， 更新货品列表数据
            $check_two = M('TranUorder')->field('oid')->where(array('lid'=>$cid))->select();

            if(count($check_two) == count($pro_list)){  // 如果已经存在则更新
                foreach($pro_list as $k=>$item){
                    M('TranUorder')->where(array('oid'=>$check_two[$k]['oid']))->save($item);
                    if(!M('UserOrderGoodsName')->where(array('user_id' => $user_id,'goods_name' => $item['detail'],))->getfield('id')){
                        M('UserOrderGoodsName')->add(array(
                            'user_id' => $user_id,
                            'goods_name' => $item['detail'],
                        ));
                    }
                }
            }else{  // 如果没有则插入新数据
                foreach($pro_list as $item){
                    $item['lid'] = $cid;
                    if($item['oid'] != ''){
                        M('TranUorder')->where(array('oid'=>$item['oid']))->save($item);
                    }else{
                        unset($item['oid']);
                        M('TranUorder')->add($item);
                    }
                    if(!M('UserOrderGoodsName')->where(array('user_id' => $user_id,'goods_name' => $item['detail'],))->getfield('id')){
                        M('UserOrderGoodsName')->add(array(
                            'user_id' => $user_id,
                            'goods_name' => $item['detail'],
                        ));
                    }
                    
                }
            }

            return array('state'=>'yes', 'msg'=>'操作成功', 'cid'=>$cid,'uucode'=>$uucode,'sn'=>$sn);
        
        }else{
            return array('state'=>'no', 'msg'=>'操作失败');
        }

        
    }

    /**
     * 将订单完成
     * @param  [type] $user_id [description]
     * @param  [type] $ship    [description]
     * @return [type]          [description]
     */
    public function tofinish($user_id,$id){

        $res = M('TranUlist')->where(array('id'=>$id,'user_id'=>$user_id))->find();

        $pro_list = M('TranUorder')->field('oid,brand,detail,catname,number,price,coin,remark,category_one,category_two,product_id,unit,source_area')->where(array('lid'=>$id))->select();
        
        $res['pro_list'] = $pro_list;

        return $res;
    }

    /**
     * 查找对应身份证号码
     * @param  [type] $where     [description]
     * @return [type]            [description]
     * @description 根据收件人姓名，手机号码，审核状态，查询出对应的身份证号码
     */
    public function search_idno_data($where){

        $res = M('UserExtraInfo')->field('front_id_img,back_id_img,idno,id')->where($where)->select();
        return $res;
    }

    // 获取批量打印的订单信息
    // liaoyadi
    public function get_batch_print_info($user_id,$ids){

        if(empty($user_id)){
            return false;
        }

        $map['user_id'] = $user_id;
        $map['_string'] = "id in (" . $ids . ")";
        $res = M('TranUlist')->field('id,sender,receiver,order_no')->where($map)->select();

        $where['_string'] = "lid in (" . $ids . ")";
        $pro_list = M('TranUorder')->field('lid,oid,brand,detail,catname,number,price,coin,remark,category_one,category_two,product_id,unit,source_area')
                                   ->where($where)->select();

                                   
        $arr = array();
        foreach($pro_list as $k=>$v){
            $arr[$v['lid']][] = $v;
        }

        foreach($res as $k=>$v){
            $res[$k]['pro_list'] = $arr[$v['id']];
        }

        return $res;

    }

    /**
     * 再次下单
     * @return [type] [description]
     */
    public function _newOne($id,$Q_no=''){
        
        
        //liao ya di
        if(empty($Q_no)){
            $MKNO = M('TranList')->where(array('id'=>$id))->getField('MKNO');
            $TranUList_MKNO = M('TranUlist')->where(array('MKNO'=>$MKNO))->find();
            if(!$TranUList_MKNO){
                return false;
            }

            $info = M('TranUlist t')->field('t.*,c.bc_state,c.member_sfpic_state')->join('left join mk_transit_center c on c.id = t.TranKd')->where(array('t.MKNO'=>$MKNO))->find();
        }else{
            
            $info = M('TranUlist t')->field('t.*,c.bc_state,c.member_sfpic_state')->join('left join mk_transit_center c on c.id = t.TranKd')->where(array('t.order_no'=>$Q_no))->find();
        }


        

        /*$info = M('TranList t')->field('t.*,c.bc_state,c.member_sfpic_state')->join('left join mk_transit_center c on c.id = t.TranKd')->where(array('t.id'=>$id))->find();*/
        /* 去除详细地址中的省市区和空格 */
        $info['reAddr'] = str_replace($info['town'],'',$info['reAddr']);
        $info['reAddr'] = str_replace($info['city'],'',$info['reAddr']);
        $info['reAddr'] = str_replace($info['province'],'',$info['reAddr']);
        $info['reAddr'] = str_replace(' ','',$info['reAddr']);

        return $res = array('info'=>$info);
    }

    /**
     * 用于编辑的时候删除某一行货品声明   总数量
     * @return [type] [description]
     */
    public function delete($oid){

        $info = M('TranUorder')->where(array('oid'=>$oid))->find(); //查找这个

        //检查是否只有一条商品声明，如果是，则禁止删除
        $count = M('TranUorder')->where(array('lid'=>$info['lid']))->count();
        if($count <= 1){
            return array('state'=>'0','msg'=>'至少保留一个商品声明','code'=>'GoodsListOne');
        }

        $num = M('TranUlist')->where(array('id'=>$info['lid']))->getField('number');    //查询有多少数量

        $data['number'] = intval($num)-intval($info['number']);     //更新总数量

        $res = M('TranUorder')->where(array('oid'=>$oid))->limit(1)->delete();  //删除用户所选的货品声明
        
        if($res){

            M('TranUlist')->where(array('id'=>$info['lid']))->save($data);   //如果删除成功则执行该订单的货品总数的数量更新

            $result = array('state'=>'1','msg'=>'删除成功','code'=>'DeleteSuccess');

        }else{

            $result = array('state'=>'0','msg'=>'删除失败','code'=>'DeleteFalse');

        }

        //删除购物小票
        M('ShoppingReceipt')->where(array('order_id'=>$oid))->delete();

        return $result;
    }

    // 获取导出订单信息
    function get_export_info($where){

        if(empty($where)){
            return [];
        }

        $res = M('TranUlist')->field('sender,sendTel,sendAddr,sendcode,receiver,reTel,reAddr,postcode,idno,number,price,ordertime,MKNO,order_no,STNO,weight,tax,freight,TranKd,package_id')
                            ->where($where)
                            ->order('ordertime desc')
                            ->select();

        $data = [];
        foreach($res as $k=>$v){
            $data[$k] = $v;
            if(!empty($v['TranKd'])){
                $data[$k]['line_name'] = M('transit_center')->where(array('id'=>$v['TranKd']))->getfield('lngname');
            }
            if(!empty($v['MKNO'])){
                $data[$k]['ex_context'] = M('tran_list')->where(array('MKNO'=>$v['MKNO']))->getfield('ex_context');
            }else{
                $data[$k]['ex_context'] = '';
            }
        }

        // return M('')->getLastSql();

        return $data;

    }

}