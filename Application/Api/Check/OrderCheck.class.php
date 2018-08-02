<?php

    // liao ya di
    // create time 2018-04-26

    namespace Api\Check;

    class OrderCheck extends BaseCheck{

        private $error = '';
        
        public function __construct(){

            parent::__construct();

            $this->allow_list = array(
                'sender'        => array(true, 50),
                'sendState'     => array(true, 50),
                'sendCity'      => array(true, 50),
                'sendStreet'    => array(true, 100),
                'sendTel'       => array(true, 40),
                'sendcode'      => array(true, 10),
                'receiver'      => array(true, 50),
                'province'      => array(true, 50),
                'city'          => array(true, 50),
                'town'          => array(false, 50),
                'reTel'         => array(true, 40),
                'postcode'      => array(true, 10),
                'idkind'        => array(true, 10),
                'idno'          => array(false, 20),
                'number'        => array(true, 11),
                'price'         => array(true, 10),
            );

        }

        public function getError(){
            return $this->error;
        }


        // 将 POST字段 映射为 数据库字段
        public function mapped($arguments){

            $data = array();

            $data['sender']          = trim($arguments['sender']);          //发件人
            $data['sendState']       = trim($arguments['sendState']);       //寄件人 州
            $data['sendCity']        = trim($arguments['sendCity']);        //寄件人 市
            $data['sendStreet']      = trim($arguments['sendStreet']);      //寄件人 街道
            $data['sendTel']         = trim($arguments['sendTel']);         //发件人电话
            $data['sendcode']        = trim($arguments['sendcode']);        //发件人邮编

            $data['receiver']        = trim($arguments['receiver']);        //收件人
            $data['province']        = trim($arguments['province']);        //省
            $data['city']            = trim($arguments['city']);            //地级市
            $data['town']            = trim($arguments['town']);            //市、县、区
            $data['reTel']           = (string)trim($arguments['reTel']);            //收件人电话
            $data['postcode']        = (string)trim($arguments['postcode']);         //收件人邮编
            
            // $data['idkind']          = (string)trim($arguments['cre_type']);         //证件类型
            $data['idkind']          = 'ID';                                         //证件类型目前固定为 ID
            $data['idno']            = (string)trim($arguments['cre_num']);          //证件号码

            // 以下两条数据从商品列表中求和获取
            $data['number']          = (string)trim($arguments['number_sum']);                  //数量
            $data['price']           = sprintf("%.2f", trim($arguments['price_sum']));          //商品的总价

            // 中转线路id
            $data['TranKd']          = trim($arguments['line']);            //中转线路

            
            if(!$this->specific_testing($data)){
                return false;
            }

            //如果线路要求必填身份证，则验证身份证是否为空
            $line_id = $data['TranKd'];
            $line_info = M('TransitCenter')->where(array('id'=>$line_id))->find();
            // if($line_info['input_idno'] == '1' && empty($data['idno'])){
            //     $this->error = array(2, array('cre_num','l_empty'));    // 2类型的错误
            //     return false;
            // }
            
            
            $data['user_id']         = $arguments['user_id'];
            $data['buyers_nickname'] = $arguments['user_name'];
            $data['random_code']     = strtoupper(randomCode($data['user_id']));            //随机码，在API内生成
            $data['order_no']        = StrOrderOne($data['user_id']);                       //下单单号，在API内生成
            $data['sendCountry']     = 'USA';                                       //寄件人 国家 暂时默认 USA
            $data['email']           = '';                                          //国内收件人email
            $data['ctime']           = date('Y-m-d H:i:s');                         //创建时间
            $data['ordertime']       = date('Y-m-d H:i:s');                         //下单时间
            $data['premium']         = 0;                                           //保险金额
            $data['sendAddr']        = $data['sendStreet']." ".$data['sendCity']." ".$data['sendState'];                            //发件人详细地址
            $data['reAddr']          = $data['province']." ".$data['city']." ".$data['town']." ".trim($arguments['reAddr']);        //收件人详细地址
            $data['id_img_status']   = $arguments['id_img_status'];                 //身份证状态标记


            return $data;

        }



        public function specific_testing($data){

            if(empty($data)){
                $this->error = array(0, "Can't be empty");      // 0类型的错误
                return false;
            }

            
            
            // 常规验证
            $res = $this->check_data($data);
            if(!$res['status']){
                $this->error = array(2, array('l__' . $res['info'][0], L($res['info'][1])));    // 2类型的错误
                return false;
            }
            

            // 验证线路id是否存在
            $line_info = M('TransitCenter')->where(array('id'=>$data['TranKd']))->find();
            if(empty($line_info)){
                $this->error = array(2, array('TranLine', 'l_empty'));    // 2类型的错误
                return false;
            }

            // 验证该省是否在此线路存在
            $pi_where = array(
                'line_id' => $data['TranKd'],
                'name'    => $data['province'],
                'pid'     => 0
            );
            $province_info = M('ZcodeLine')->where($pi_where)->find();
            if(empty($province_info)){
                $this->error = array(2, array('l__province', 'l_empty_province'));    // 2类型的错误
                return false;
            }

            // 验证该市是否在此线路存在
            $ci_where = array(
                'line_id' => $data['TranKd'],
                'name'    => $data['city'],
                'pid'     => $province_info['id']
            );
            $city_info = M('ZcodeLine')->where($ci_where)->find();
            if(empty($city_info)){
                $this->error = array(2, array('l__city', 'l_empty_city'));    // 2类型的错误
                return false;
            }

            //验证该区是否在此线路存在
            if(!empty($data['town']) && false){
                $ti_where = array(
                    'line_id' => $data['TranKd'],
                    'name'    => $data['town'],
                    'pid'     => $city_info['id']
                );
                $town_info = M('ZcodeLine')->where($ti_where)->find();
                if(empty($town_info)){
                    $this->error = array(2, array('l__town', 'l_empty_town'));    // 2类型的错误
                    return false;
                }
            }


            //如果线路要求必填身份证，则验证身份证是否为空
            // if($line_info['input_idno'] == '1' && empty($data['idno'])){
            //     $this->error = array(2, array('l__idno', 'l_empty'));    // 2类型的错误
            //     return false;
            // }

            //如果证件类型为身份证，则验证身份证是否正确
            if(!empty($data['idno']) && $data['idkind'] == 'ID' && !certificate($data['idno'])){
                $this->error = array(2, array('l__idno', 'l_crenum_format'));    // 2类型的错误
                return false;
            }

            //收件人姓名必须为中文
            if(!empty($data['receiver']) && !preg_match('/^[\x{4e00}-\x{9fa5}]+$/u',$data['receiver'])){
                $this->error = array(2, array('l__receiver', 'l_rec_chi'));    // 2类型的错误
                return false;
            }

            //发件人邮编必须为数值
            if(!empty($data['sendcode']) && !preg_match('/^[0-9]{4,6}$/',$data['sendcode'])){
                $this->error = array(2, array('l__sendcode', 'l_code_format'));    // 2类型的错误
                return false;
            }

            //收件人邮编必须为数值
            if(!empty($data['postcode']) && !preg_match('/^[0-9]{4,6}$/',$data['postcode'])){
                $this->error = array(2, array('l__postcode', 'l_code_format'));    // 2类型的错误
                return false;
            }
            
            //手机号码必须为大陆或香港手机号码
            if(!empty($data['reTel']) && !checkPhoneNum($data['reTel'])){
                $this->error = array(2, array('l__reTel', 'l_crenum_format'));    // 2类型的错误
                return false;
            }


            /*
                其它任意检测
            */

            return true;

        }


    }