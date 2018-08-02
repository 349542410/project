<?php

    // excel导入订单
    // liao ya di
    // create time : 2017-11-30
    // API CONTROLLER

    namespace Api\Controller;
    use Think\Controller\HproseController;

    class OrderExcelController extends HproseController{

        //验证规则 - order_excel
        public $rules_1 = array(
            'package_id'    =>array(true,100),
		    'sender'        =>array(true,50),
		    'sendTel'       =>array(true,40),
		    'sendCountry'   =>array(false,50),
		    'sendState'     =>array(true,50),
		    'sendCity'      =>array(true,50),
		    'sendStreet'    =>array(true,100),
		    'sendAddr'      =>array(false,100),
		    'sendcode'      =>array(true,10),
		    'receiver'      =>array(true,50),
		    'cre_type'      =>array(true,30),
		    'cre_num'       =>array(false,30),
		    'reTel'         =>array(true,40),
            'province'      =>array(false,50),  //省市区是智能解析的，所以不能限制为非空
		    'city'          =>array(false,50),
		    'town'          =>array(false,50),
		    'reAddr'        =>array(false,100),
		    'postcode'      =>array(false,10),
		    'line'          =>array(false,50),
        );

        // //验证规则 - order_goods_excel
        // protected $rules_2_BC = array(
		//     'brand'         =>array(false,200),
		//     'detail'        =>array(false,200),
		//     'catname'       =>array(true,50),
		//     'category_one'  =>array(true,50),
		//     'category_two'  =>array(true,50),
		//     'category_three'=>array(false,50),
        //     'number'        =>array(true,6),
        //     'unit'          =>array(false,10),
        //     'source_area'   =>array(false,50),
        //     'price'         =>array(true,8),
        //     'note'          =>array(false,100),
        // );
        protected $rules_2_CC = array(
		    'brand'         =>array(true,200),
		    'detail'        =>array(true,200),
            'catname'       =>array(false,50),   //规格容量
            'spec_unit'     =>array(true,15),   //规格单位
		    'category_one'  =>array(true,50),
		    'category_two'  =>array(true,50),
		    'category_three'=>array(false,50),
            'number'        =>array(true,6),    //数量
            // 'num_unit'      =>array(true,15),   //数量单位
            'is_suit'      =>array(true,3),   //是否套装
            'source_area'   =>array(false,50),
            'price'         =>array(true,8),
            'note'          =>array(false,100),
        );
        // protected $rules_2_NO = array(
		//     'brand'         =>array(true,200),
		//     'detail'        =>array(true,200),
		//     'catname'       =>array(true,50),
		//     'category_one'  =>array(true,50),
		//     'category_two'  =>array(true,50),
		//     'category_three'=>array(false,50),
        //     'number'        =>array(true,6),
        //     'unit'          =>array(true,10),
        //     'source_area'   =>array(true,50),
        //     'price'         =>array(true,8),
        //     'note'          =>array(false,100),
        // );
        
        


        //检测字段是否符合要求
        public function inspect($data,$line_type, $line_id){

            $order_m = M('OrderExcel');
            $order_goods_m = M('OrderGoodsExcel');

            //创建 order_excel 表的数据，并进行验证
            $order_data = $order_m->create($data[0]);
            $check_res = $this->check_data($order_data,$this->rules_1);
            if(!$check_res['success']){
                return $check_res;
            }

            // // 包裹名不能有特殊字符
            // if(!preg_match('/^[a-zA-Z0-9\[\]-_]+$/',$order_data['package_id'])){
            //     return array(
            //         'success' => false,
            //         'error' => array('包裹名', '包含非法字符'),
            //     );
            // }
            // 包裹名不能有中文
            if(preg_match('/[\x{4e00}-\x{9fa5}]+/u',$order_data['package_id'])){
                return array(
                    'success' => false,
                    'error' => array('包裹名', '包含非法字符'),
                );
            }


            // 验证地址不能出现敏感字符
            $adt = new \Lib10\LineAmount\AddressDetails();
            if(!$adt->filterName($order_data['reAddr'])){
                return array(
                    'success' => false,
                    'error' => array('reAddr','包含非法字符'),
                );
            }

            //收件人姓名必须为中文
            if(!empty($order_data['receiver']) && !preg_match('/^[\x{4e00}-\x{9fa5}]+$/u',$order_data['receiver'])){
                return array(
                    'success' => false,
                    'error' => array('receiver','l_rec_chi'),
                );
            }

            //如果线路要求必填身份证，则验证身份证是否为空
            // $line_info = M('TransitCenter')->where(array('id'=>$line_id))->find();
            // if($line_info['input_idno'] == '1' && empty($order_data['cre_num'])){
            //     return array(
            //         'success' => false,
            //         'error' => array('cre_num','l_empty'),
            //     );
            // }
            //目前没有护照选择，所以必须验证身份证是否正确，这里为了方便，将类型直接设置为了身份证
            //以后如果恢复护照，则删除此行
            $order_data['cre_type'] = 'ID';


            //如果证件类型为身份证，则验证身份证是否正确
            if(!empty($order_data['cre_num']) && $order_data['cre_type'] == 'ID' && !certificate($order_data['cre_num'])){
                return array(
                    'success' => false,
                    'error' => array('cre_num','l_crenum_format'),
                );
            }

            //手机号码必须为大陆或香港手机号码
            if(!empty($order_data['reTel']) && !checkPhoneNum($order_data['reTel'])){
                return array(
                    'success' => false,
                    'error' => array('reTel','l_crenum_format'),
                );
            }

            //创建 order_goods_excel 表的数据，并进行验证
            $rules = 'rules_2_' . $line_type;
            $check_res = array();
            foreach($data as $k=>$v){
                $order_goods_data[$k] = $order_goods_m->create($v);
                $order_goods_data[$k]['is_suit'] = $v['is_suit'];
                $check_res[] = $this->check_data($order_goods_data[$k],$this->$rules);
            }
            foreach($check_res as $k=>$v){
                if(!$v['success']){
                    return $v;
                }
            }
            foreach($order_goods_data as $k=>$v){
                //数量必须为数值
                if(!empty($v['number']) && !is_numeric($v['number'])){
                    return array(
                        'success' => false,
                        'error' => array('number_numeric','l_mb_num'),
                    );
                }
                //价格必须为数值
                if(!empty($v['price']) && !is_numeric($v['price'])){
                    return array(
                        'success' => false,
                        'error' => array('price_numeric','l_mb_num'),
                    );
                }
                //数量必须大于零
                if($v['number'] <= 0){
                    return array(
                        'success' => false,
                        'error' => array('number_numeric','l_eq_o'),
                    );
                }
                //价格必须大于零
                if($v['price'] <= 0){
                    return array(
                        'success' => false,
                        'error' => array('price_numeric','l_eq_o'),
                    );
                }
                //品牌必须为英文或者符号
                if(!empty($v['brand']) && !preg_match('/^[\w\/\\\ \^\%\(\)\*\+\'\:\.\?\[\]{}]+$/',$v['brand'])){
                    return array(
                        'success' => false,
                        'error' => array('brand','l_brand_eng'),
                    );
                }
                //货品名称必须为中文
                //货品名称必须包含中文
                // if(!empty($v['detail']) && !preg_match('/^[\x{4e00}-\x{9fa5}]+$/u',$v['detail'])){
                if(!empty($v['detail']) && !preg_match('/[\x{4e00}-\x{9fa5}]+/u',$v['detail'])){
                    return array(
                        'success' => false,
                        'error' => array('detail','l_rec_chi'),
                    );
                }
            }

            //验证通过
            return array(
                'success' => true,
                'error' => '',
            );

        }


        // 验证是否能够直接下单成功，并计算税金，用于统计信息
        public function check_add_order($all,$user_id,$line_id){

            error_reporting(E_ALL ^ E_NOTICE);

            $total = count($all);
            $successful = 0;
            $total_tax = 0;

            if(empty($user_id) || empty($line_id)){
                return array(
                    'success' => false,
                    'error' => 'user_id is null',
                );
            }

            // 收集错误信息
            $err_all = array();

            $order_m = M('OrderExcel');
            $order_goods_m = M('OrderGoodsExcel');
            $TaxCount = new \Libm\TaxCount\TaxCountController;

            $order_check = new \Api\Check\OrderCheck();
            $order_goods_check = new \Api\Check\OrderGoodsCheck('CC');

            foreach($all as $order_pkid=>$data){

                $order_data = array();
                $order_goods_data = array();

                $order_data = $order_m->create($data[0]);
                foreach($data as $k=>$v){
                    $order_goods_data[$k] = $order_goods_m->create($v);
                    $order_goods_data[$k]['is_suit'] = $v['is_suit'];
                }


                $order_data['user_id'] = $user_id;
                $order_data['line'] = $line_id;
                $order_data['user_name'] = M('UserList')->where(array('id'=>$user_id))->getField('username');

                $sum_number = 0;
                $sum_price = 0;
                foreach($order_goods_data as $k=>$v){
                    $sum_number += $v['number'];
                    $sum_price += $v['price'];
                }
                $order_data['number_sum'] = $sum_number;
                $order_data['price_sum'] = $sum_price;
                $order_data['id_img_status'] = 0;

                // 验证数据
                $o_data = $order_check->mapped($order_data);
                $og_data = $order_goods_check->mapped($order_goods_data, $line_id, true, array(
                    $o_data['province'], $o_data['city'], $o_data['town'], $o_data['reAddr'],
                ));

                // return $og_data;


                if($o_data && $og_data){

                    $successful++;

                    $order_goods_info_tax = array();
                    foreach($og_data as $k=>$v){
                        $order_goods_info_tax[$k]['uuid'] = $k;
                        $order_goods_info_tax[$k]['cid'] = $v['category_two'];
                        $order_goods_info_tax[$k]['number'] = $v['number'];
                        $order_goods_info_tax[$k]['price'] = $v['price'];
                    }
                    $total_tax += $TaxCount->caltax2($order_goods_info_tax, $line_id)['total'];
                    $total_tax = sprintf('%.2f', $total_tax);

                }


                // $err_all[$order_pkid][] = $o_data;
                // $err_all[$order_pkid][] = $og_data;

                if(!$o_data){
                    $err_all[] = array((string)$order_pkid, $order_check->getError());
                }

                if(!$og_data){
                    $err_all[] = array((string)$order_pkid, $order_goods_check->getError());
                }

            }


            return array(
                'total' => $total,
                'successful' =>$successful,
                'total_tax' => $total_tax,
                'err_all' => $err_all,
            );

        }



        //添加数据
        public function insert_order($all,$user_id,$line_id){

            error_reporting(E_ALL ^ E_NOTICE);

            if(empty($user_id)){
                return array(
                    'success' => false,
                    'error' => 'user_id is null',
                );
            }

            $order_m = M('OrderExcel');
            $order_goods_m = M('OrderGoodsExcel');

            $model = M('');
            $model->startTrans();

            $idcard_cli = new \Api\Controller\IdcardInfoController();

            // $last_sql = array();

            
            // 需要添加redis缓存
            $order_key = 0;
            $catch_content = array();

            foreach($all as $data){

                $order_data = array();
                $order_goods_data = array();

                    $order_data = $order_m->create($data[0]);
                    foreach($data as $k=>$v){
                        $order_goods_data[$k] = $order_goods_m->create($v);
                        $order_goods_data[$k]['is_suit'] = ($v['is_suit'] == '是' || $v['is_suit'] == 'yes' || $v['is_suit'] == 'y') ? '1' : '0';
                    }


                    /* 尝试直接生成订单 */

                    $order_data['user_id'] = $user_id;
                    $order_data['line'] = $line_id;
                    $order_data['user_name'] = M('UserList')->where(array('id'=>$user_id))->getField('username');
                    $sum_number = 0;
                    $sum_price = 0;
                    foreach($order_goods_data as $k=>$v){
                        $sum_number += $v['number'];
                        $sum_price += $v['price'];
                    }
                    $order_data['number_sum'] = $sum_number;
                    $order_data['price_sum'] = $sum_price;
                    // $order_data['id_img_status'] = 0;

                    $order_check = new \Api\Check\OrderCheck();
                    $order_goods_check = new \Api\Check\OrderGoodsCheck('CC');

                    $o_data = $order_check->mapped($order_data);
                    $og_data = $order_goods_check->mapped($order_goods_data, $line_id, true, array(
                        $o_data['province'], $o_data['city'], $o_data['town'], $o_data['reAddr']
                    ));


                    if($o_data){
                        // 身份证库里是否有已审核的图片
                        $idno_res = $idcard_cli->get_idcard_info($order_data['receiver'], $order_data['reTel'], $order_data['idno'], $user_id)[0];
                        $line_info = $idcard_cli->get_line_info_idcard($line_id);
                        $message = false;   //是否需要发送短信

                        if($line_info['idno_isset'] == '0'){

                            $o_data['id_no_status'] = 200;
                            $o_data['id_img_status'] = 200;

                        }else if($line_info['idno_isset'] == '1' && $line_info['idcard_isset'] == '0'){

                            $o_data['id_img_status'] = 200;
                            if(!empty($o_data['idno'])){
                                $o_data['id_no_status'] = 100;
                            }else{
                                $o_data['id_no_status'] = 0;
                                $message = true;
                            }

                        }else if($line_info['idno_isset'] == '1' && $line_info['idcard_isset'] == '1'){
                            if(!empty($o_data['idno'])){
                                $o_data['id_no_status'] = 100;

                                if(empty($idno_res)){
                                    // 需要补填身份证 - 即需要发送短信
                                    $o_data['id_img_status'] = 0;
                                    $message = true;
                                }else{
                                    // 身份证库里有已审核的图片
                                    $o_data['id_img_status'] = 100;

                                    $o_data['certify_upload_type'] = 1;     // 如果匹配成功，则当做寄件人上传

                                    $o_data['front_id_img'] = $idno_res['front_id_img'];
                                    $o_data['small_front_img'] = $idno_res['small_front_img'];
                                    $o_data['back_id_img'] = $idno_res['back_id_img'];
                                    $o_data['small_back_img'] = $idno_res['small_back_img'];
                                    $o_data['lib_idcard'] = $idno_res['id'];
                                }

                            }else{
                                $o_data['id_no_status'] = 0;
                                $o_data['id_img_status'] = 0;
                                $message = true;
                            }
                            
                        }


                        // 存储包裹号
                        $o_data['package_id'] = $order_data['package_id'];
                    }
                    

                    // 是否添加成功
                    $success = true;

                    if($o_data && $og_data) {
                        $lid = M('TranUlist')->add($o_data);
                        if ($lid === false) {
                            $model->rollback();
                            $success = false;   // 添加失败
                        } else {
                            foreach ($og_data as $k => $v) {
                                $v['lid'] = $lid;
                                $ogid = M('TranUorder')->add($v);
                                if ($ogid === false) {
                                    $model->rollback();
                                    $success = false;   // 添加失败
                                    break;
                                }
                            }

                            // 写入日志
                            $logs['order_no'] = $o_data['order_no'];     // 内部订单号
                            $logs['content'] = '您提交了订单，请等待称重计费';
                            $logs['create_time'] = $o_data['ordertime'];
                            $logs['state'] = '3000';
                            M('ULogs')->add($logs);

                            // 购物小票
                            M('ShoppingReceipt')->add(array('order_id'=>$lid, 'receipt_img'=>'none'));

                            // 验证身份证图片是否存在
                            $img_info = M('UserExtraInfo')->field('front_id_img,back_id_img')->where(array(
                                'user_id' => $user_id,
                                'true_name' => $order_data['receiver'],
                                'idno' => $order_data['cre_num'],
                            ))->find();

                            // $img_info_all[] = array(
                            //     'user_id' => $user_id,
                            //     'true_name' => $order_data['receiver'],
                            //     'idno' => $order_data['cre_num'],
                            // );

                            // 生成mkno与key关联的记录
                            $str1 = rand(1000, 9999);
                            $str2 = rand(700, 9999);
                            M('MknoKey')->add(array(
                                'u_id' => $lid,
                                'un_key' => $str2 . $lid . $str1,
                            ));

                            if (empty($order_data['cre_num']) || empty($img_info) || (empty($img_info['front_id_img']) && empty($img_info['back_id_img']))) {
                                // 需要补填身份证
                                if ($message) {
                                    // 需要添加redis短信发送队列
                                    $catch_content[$order_key]['receiver'] = $order_data['receiver'];
                                    $catch_content[$order_key]['reTel'] = $order_data['reTel'];
                                    $catch_content[$order_key]['MknoKey'] = $str2 . $lid . $str1;
                                    $catch_content[$order_key]['time'] = time();
                                    $order_key++;
                                }
                            }
                        }

                        // $model->commit();
                        // return array(
                        //     'success' => true,
                        //     'error' => '',
                        // );
                        if ($success) {
                            // 如果添加成功，则无需再加入到excel表里了
                            continue;
                        }
                    }


                    /* 无法生成，添加到批量下单表里面 */

                    unset($order_data['user_name']);
                    unset($order_data['number_sum']);
                    unset($order_data['price_sum']);
                    unset($order_data['id_img_status']);
                    $order_excel_id = $order_m->add($order_data);
                    if ($order_excel_id === false) {
                        $model->rollback();
                        return array(
                            'success' => false,
                            'error' => $order_m->getError(),
                        );
                    }

                    $package_id = $order_data['package_id'] . ' [' . rand(1000, 9999) . $order_excel_id . $user_id . rand(1000, 9999) . ']';
                    if (($order_m->save(array('package_id' => $package_id, 'id' => $order_excel_id))) === false) {
                        $model->rollback();
                        return array(
                            'success' => false,
                            'error' => $order_m->getError(),
                        );
                    }

                    // $last_sql[1][] = $model->getLastSql();

                    foreach ($order_goods_data as $k => $v) {
                        $v['order_id'] = $order_excel_id;
                        $order_goods_id = $order_goods_m->add($v);
                        if ($order_goods_id === false) {
                            $model->rollback();
                            return array(
                                'success' => false,
                                'error' => $order_goods_m->getError(),
                            );
                        }
                    }

                    // $last_sql[2][] = $model->getLastSql();
                    
            }
            

            // return $img_info_all;

            $model->commit();
            return array(
                'success' => true,
                'error' => '',
                'catch_content' => $catch_content,
                // 'last_sql' => $last_sql,
                // 'all' => $all,
            );

        }



        //查询数据
        public function query_data($where=array(),$limit='',$is_all=false){
            if(empty($where['user_id'])){
                return array(
                    'success' => false,
                    'error' => 'user_id is null',
                );
            }

            $order_m = M('OrderExcel');
            // $order_goods_m = M('OrderGoodsExcel');

            $order_data = $order_m->where($where)->order('create_time desc')->limit($limit)->alias('a')
                        ->field("a.*,b.lngname")
                        ->join("left join mk_transit_center b on a.line = b.id")
                        ->select();
            return array(
                'order_data' => $order_data,
                'order_goods_data' => '',
            );

        }


        //查询一条数据
        public function find_data($where=array()){

            $order_m = M('OrderExcel');
            $order_goods_m = M('OrderGoodsExcel');
            
            $order_data = $order_m->where($where)->find();

            $condition = array( 'order_id' => $order_data['id'] );
            $order_goods_data = $order_goods_m->where($condition)->select();
            return array(
                'order_data' => $order_data,
                'order_goods_data' => $order_goods_data,
            );

        }


        // 获取需要导出的数据
        public function get_derivation_data($ids){
            if(empty($ids)){
                return array();
            }

            $res = M('OrderExcel')->where(array('a.id'=>array('in', $ids)))
                                ->alias('a')
                                ->join('left join mk_order_goods_excel b on a.id=b.order_id')
                                ->order('create_time desc')
                                ->select();

            return $res;
        }

        // 导出次数加一
        public function export_count_inc($ids){
            if(empty($ids)){
                return false;
            }

            return M('OrderExcel')->where(array('id'=>array('in', $ids)))->setInc('export_count');
        }


        public function delete_data($id){
            
            $order_data = M('OrderExcel')->where(array('id'=>$id))->delete();
            $order_goods_data = M('OrderGoodsExcel')->where(array('order_id' => $id))->delete();

        }

        // 批量删除
        public function delete_data_all($ids){
            
            $order_data = M('OrderExcel')->where(array('id'=>array('in', $ids)))->delete();
            $order_goods_data = M('OrderGoodsExcel')->where(array('order_id'=>array('in', $ids)))->delete();

        }


        //查询线路信息
        public function get_line_info($line_id=''){

            if(!empty($line_id)){
                $where = array('id'=>$line_id);
            }else{
                $where = '';
            }

            if(empty($where)){
                return M('TransitCenter')->select();
            }else{
                return M('TransitCenter')->where($where)->select();
            }

        }




        //允许导入的线路
        public function show_line(){
            
            $transit_center_m = M('TransitCenter');
            $result = $transit_center_m->field('id,lngname,lngremark')
                                       ->where(array('status'=>1,'optional'=>1,'allow_import_order'=>1))
                                       ->order('id desc')
                                       ->select();
            return $result;

        }

        // 查询线路是否需要填写身份证号码
        // 如果必须要上传则返回true，否则返回false
        public function line_idno_find($line_id){

            if(empty($line_id)){
                return false;
            }

            $line_info = M('TransitCenter')->where(array('id'=>$line_id))->find();
            if($line_info['input_idno'] == '1'){
                return true;
            }else{
                return false;
            }

        }



        //检测
        public function check_data($data,$rules){
            
            foreach($rules as $k=>$v){

                // if($v[0] && empty($data[$k]) && $data[$k]!==0 && $data[$k]!=='0'){
                if($v[0] && empty($data[$k])){
                    //必须存在
                    return array(
                        'success' => false,
                        'error' => array($k,'l_empty'),
                    );
                }

                if(!empty($data[$k]) && mb_strlen($data[$k])>$v[1]){
                    //长度不符合要求
                    return array(
                        'success' => false,
                        'error' => array($k,'l_max_len'),
                    );
                }
                
            }

            return array(
                'success' => true,
                'error' => '',
            );

        }


        // 获取导出订单的信息
        public function get_export_orderinfo($where){
            return M('OrderExcel')->alias('a')
                                ->field("a.*, b.*")
                                ->where($where)
                                ->join("left join mk_order_goods_excel b on a.id=b.order_id")
                                ->select();
        }


        // 根据地区和线路获取邮编
        public function get_zcode($province, $city, $town, $line_id){
            if(empty($province) || empty($city) || empty($line_id)){
                return '';
            }

            $province_id = M('zcode_line')->where(array(
                'name' => array('like', $province . '%'),
                'line_id' => array('eq', $line_id),
                'status' => array('eq', 1),
            ))->getfield('id');

            if(empty($province_id)){
                return '';
            }

            $city_zcode = M('zcode_line')->where(array(
                'name' => array('like', $city . '%'),
                'line_id' => array('eq', $line_id),
                'pid' => array('eq', $province_id),
                'status' => array('eq', 1),
            ))->getfield('zipcode');

            if(empty($town)){
                return empty($city_zcode) ? '' : $city_zcode;
            }

            $city_id = M('zcode_line')->where(array(
                'name' => array('like', $city . '%'),
                'line_id' => array('eq', $line_id),
                'pid' => array('eq', $province_id),
                'status' => array('eq', 1),
            ))->getfield('id');

            if(empty($city_id)){
                return '';
            }

            $zcode = M('zcode_line')->where(array(
                'name' => array('like', $town . '%'),
                'line_id' => array('eq', $line_id),
                'pid' => array('eq', $city_id),
                'status' => array('eq', 1),
            ))->getfield('zipcode');
            return empty($zcode) ? $city_zcode : $zcode;
        }

    }