<?php

    // liao ya di
    // create time 2018-04-26

    namespace Api\Check;

    class OrderGoodsCheck extends BaseCheck{

        private $error = '';
        // private $brand;
        
        public function __construct($line_type){

            parent::__construct();

            error_reporting(E_ALL ^ E_NOTICE);


            // $line_type = 'BC';
            // $line_type = 'CC';
            // $line_type = 'NO';

            // 定义最大商品条数
            define(MAX_ORDERNO, 31);

            $allow_arr = array('BC','CC','NO');
            if(!in_array($line_type,$allow_arr)){
                // 出错
                die;
            }

            $rules = 'rules_' . $line_type;
            $this->allow_list = $this->$rules;

            // $brand = M('BrandList')->field('brand_name')->select();
            // foreach($brand as $k=>$v){
            //     $this->brand[] = $v['brand_name'];
            // }

        }

        public function getError(){
            return $this->error;
        }



        // 将 POST字段 映射为 数据库字段
        public function mapped($argv, $line_id, $is_name=false, $addr){

            // 创建数据
            $data = array();
            
            foreach($argv as $k=>$v){
                $data[$k]['brand'] = $v['brand'];
                $data[$k]['detail'] = $v['detail'];
                $data[$k]['catname'] = $v['catname'];
                $data[$k]['spec_unit'] = $v['spec_unit'];
                if($is_name){
                    // 分类是名字，需要查找id
                    $data[$k]['category_one'] = M('CategoryList')->where(array('cat_name'=>array('eq', $v['category_one']), 'TranKd'=>array('like', '%,'.$line_id.',%'), 'fid' => array('eq',0)))->getField('id');
                    $data[$k]['category_two'] = M('CategoryList')->where(array('cat_name'=>array('eq', $v['category_two']), 'TranKd'=>array('like', '%,'.$line_id.',%'), 'fid' => array('neq',0)))->getField('id');
                }else{
                    // 分类是id
                    $data[$k]['category_one'] = $v['category_one'];
                    $data[$k]['category_two'] = $v['category_two'];
                }
                $data[$k]['number'] = $v['number'];
                $data[$k]['is_suit'] = $v['is_suit'];
                // $data[$k]['num_unit'] = $v['num_unit'];
                $data[$k]['price'] = $v['price'];
                $data[$k]['remark'] = $v['note'];

                if($data[$k]['is_suit'] == 1){
                    $data[$k]['num_unit'] = '套';
                    $data[$k]['unit'] = '套';
                }else{
                    $data[$k]['num_unit'] = '件';
                    $data[$k]['unit'] = '件';
                }

                $data[$k]['source_area'] = '美国';
                $data[$k]['product_id'] = 0;
                $data[$k]['coin'] = 'USD';
            }


            // return $data;
            // return M('')->getLastSql();
            


            // 至少有一行数据
            if(count($data)<1){
                $this->error = array(0, 'GoodsListMsg');    // 0类型的错误
                return false;   
            }


            
            // 最大行数不能超过 MAX_ORDERNO
            if(count($data)>MAX_ORDERNO){
                $this->error = array(1, array('{**}', MAX_ORDERNO, 'almost_in_ten'));   // 1类型的错误
                return false;
            }

            
            // 验证数据是否正确
            foreach($data as $k=>$v){
                if(!$this->specific_testing($v)){
                    // $this->error = '第' . ($k+1) . '行 ' . $this->error;
                    return false;
                }
            }


            // 验证价格 start
            $LineCostObj = new \Lib10\LineAmount\LineCost();
            $l_line_id = $line_id;
            $l_recipient_arr = array('province'=>$addr[0],'city'=>$addr[1],'town'=>$addr[2],'address'=>$addr[3]);
            $l_price_arr = array();
            foreach($data as $lk=>$lv){
                $l_price_arr[$lk]['price'] = $lv['price'];          //单价
                $l_price_arr[$lk]['number'] = $lv['number'];        //数量
                $l_price_arr[$lk]['cid'] = $lv['category_two'];     //可以查找税金的分类id
            }
            if( ! $LineCostObj->cost($l_line_id, $l_price_arr, $l_recipient_arr) ){
                // $this->error = $LineCostObj->getError();
                $this->error = array(3, array($LineCostObj->parameter()[0], $LineCostObj->parameter()[1]));     // 3类型的错误
                return false;
            }
            
            //end
            
            
            return $data;

        }




        // 验证
        public function specific_testing(&$data){

            if(empty($data)){
                $this->error = array(0, "Can't be empty");  // 0类型的错误
                return false;
            }

            
            // 常规验证
            $res = $this->check_data($data);
            if(!$res['status']){
                $this->error = array(2, array('l__' . $res['info'][0], L($res['info'][1])));    // 2类型的错误
                return false;
            }
            
            // 一级分类与二级分类不对应
            if(!empty($data['category_one']) && !empty($data['category_two'])){
                $fid = M('CategoryList')->where(array('id'=>$data['category_two']))->getField('fid');
                if($data['category_one'] != $fid){
                    $this->error = array(0, 'l_yijierji');    // 0类型的错误
                    return false;
                }

            }


            //数量必须为数值
            if(!empty($data['number']) && !is_numeric($data['number'])){
                $this->error = array(2, array('l__number_numeric', 'l_mb_num'));    // 2类型的错误
                return false;
            }
            //价格必须为数值
            if(!empty($data['price']) && !is_numeric($data['price'])){
                $this->error = array(2, array('l__price_numeric', 'l_mb_num'));    // 2类型的错误
                return false;
            }
            //数量必须大于零
            if($data['number'] <= 0){
                $this->error = array(2, array('l__number_numeric', 'l_eq_o'));    // 2类型的错误
                return false;
            }
            //价格必须大于零
            if($data['price'] <= 0){
                $this->error = array(2, array('l__price_numeric', 'l_eq_o'));    // 2类型的错误
                return false;
            }
            //品牌必须为英文或者符号
            if(!empty($data['brand']) && !preg_match('/^[\w\/\\\ \^\%\(\)\*\+\'\:\.\?\[\]{}]+$/',$data['brand'])){
                $this->error = array(2, array('l__brand', 'l_brand_eng'));    // 2类型的错误
                return false;
            }
            //货品名称必须包含中文
            if(!empty($data['detail']) && !preg_match('/[\x{4e00}-\x{9fa5}]+/u',$data['detail'])){
                $this->error = array(2, array('l__detail', 'l_rec_chi'));    // 2类型的错误
                return false;
            }
            
            // // 品牌必须允许的选项内
            // if(!in_array($data['brand'], $this->brand)){
            //     return false;
            // }

            

            // 单位必须在允许的选项内
            // $unit = M('CategoryList')->alias('a')
            //                         ->field('b.specifications as spec_unit,b.number as num_unit')
            //                         ->where(array('a.id'=>$data['category_two']))
            //                         ->join('left join mk_tax_rules_class b on a.hs_code=b.hs_code')
            //                         ->find();
            
            // if($unit['num_unit'] == ''){
            //     $this->error = array(2, array('l_num_unit', 'l_empty'));    // 2类型的错误
            //     return false;
            // }
            // if($unit['spec_unit'] == ''){
            //     $this->error = array(2, array('l_guige_unit', 'l_empty'));    // 2类型的错误
            //     return false;
            // }
            // $spec_unit = \explode(',', $unit['spec_unit']);
            // $num_unit = \explode(',', $unit['num_unit']);
            // if(!in_array($data['spec_unit'], $spec_unit)){
            //     // $this->error = array(2, array('l_guige_unit', 'l_yxfw'));    // 2类型的错误
            //     // return false;
            //     // $data['spec_unit'] = '';
            // }
            // if(!in_array($data['num_unit'], $num_unit)){
            //     // $this->error = array(2, array('l_num_unit', 'l_yxfw'));    // 2类型的错误
            //     // return false;
            //     // $data['num_unit'] = '';
            // }
            // if($data['is_suit'] == '是' || $data['is_suit'] == 'yes' || $data['is_suit'] == 'y'){
            //     $data['is_suit'] = 1;
            // }else{
            //     $data['is_suit'] = 0;
            // }



            /*
                其它任意检测
            */

            return true;

        }



        // private $rules_BC = array(
        //     'brand'         =>array(false,200),     // 品牌
        //     'detail'        =>array(false,200),     // 商品详细描述（中文货品名称）
        //     'catname'       =>array(true,50),       // 规格 - 货品类别
        //     'category_one'  =>array(true,50),       // 一级分类
        //     'category_two'  =>array(true,50),       // 二级分类
        //     'number'        =>array(true,6),        // 数量
        //     'price'         =>array(true,8),        // 单价
        //     'remark'        =>array(false,100),     // 备注
        //     'source_area'   =>array(true,50),       // 产地，自动设置为美国，因此无影响
        //     'coin'          =>array(true,6),        // 货币类型，自动设置为USD，无影响
        // );
        private $rules_CC = array(
            'brand'         =>array(true,200),
            'detail'        =>array(true,200),
            'catname'       =>array(false,50),
            'spec_unit'     =>array(true,15),       // 规格单位
            'category_one'  =>array(true,50),
            'category_two'  =>array(true,50),
            'number'        =>array(true,6),
            // 'num_unit'      =>array(false,15),      // 数量单位
            'is_suit'      =>array(true,3),   //是否套装
            'price'         =>array(true,8),
            'remark'        =>array(false,100),
            'source_area'   =>array(true,50),
            'coin'          =>array(true,6),
        );
        // private $rules_NO = array(
        //     'brand'         =>array(true,200),
        //     'detail'        =>array(true,200),
        //     'catname'       =>array(true,50),
        //     'category_one'  =>array(false,50),
        //     'category_two'  =>array(false,50),
        //     'number'        =>array(true,6),
        //     'price'         =>array(true,8),
        //     'unit'          =>array(true,10),
        //     'remark'        =>array(false,100),
        //     'source_area'   =>array(true,50),
        //     'coin'          =>array(true,6),
        // );


    }