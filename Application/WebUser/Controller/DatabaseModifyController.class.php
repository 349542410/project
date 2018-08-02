<?php


    namespace WebUser\Controller;

    class DatabaseModifyController extends BaseController{

        // 将订单表的电话号码拷贝到身份证库表里面
        public function user_info_table(){
            // dump(M(''));
            // dump(M('user_extra_info')->select());

            // echo "开始处理<br />";

            $count = 0;
            // while(1){

                $res = M('tran_ulist')->field('user_id,receiver as true_name,reTel as tel,idno')->where(array(
                    'user_id' => array('neq', ''),
                    'receiver' => array('neq', ''),
                    'reTel' => array('neq', ''),
                    'idno' => array('neq', ''),
                ))->select();
    
                // dump($res);

                if(!$res){
                    // break;
                }

                foreach($res as $k=>$v){
                    $ures = M('user_extra_info')->where(array(
                        'user_id' => array('eq', $v['user_id']),
                        'true_name' => array('eq', $v['true_name']),
                        'idno' => array('eq', $v['idno']),
                        'tel' => array('eq', $v['tel']),
                        '_string' => 'front_id_img <> "" or back_id_img <> ""',
                    ))->find();
                    if($ures){
                        continue;
                    }

                    $ures = M('user_extra_info')->where(array(
                        'user_id' => array('eq', $v['user_id']),
                        'true_name' => array('eq', $v['true_name']),
                        'idno' => array('eq', $v['idno']),
                        '_string' => 'front_id_img <> "" or back_id_img <> ""',
                    ))->find();
                    if(!empty($ures)){
                        // dump($ures);
                        unset($ures['id']);
                        unset($ures['status']);
                        $ures['sys_time'] = date("Y-m-d H:i:s");
                        $ures['tel'] = $v['tel'];
                        $upres = M('user_extra_info')->add($ures);

                        if($upres){
                            $count++;
                        }
                    }

                    // dump(M('')->getLastSql());
                }

            // }

            echo '已处理' . $count . '条数据<br />';
            
        }


        // 将身份证库里面的图片放到订单表里
        public function ulist_update(){

            $res = M('user_extra_info')->field('user_id,true_name,idno,front_id_img,small_front_img,back_id_img,small_back_img')->where(array(
                'true_name' => array('neq', ''),
                'idno' => array('neq', ''),
                'front_id_img' => array('neq', ''),
                'back_id_img' => array('neq', ''),
                'user_id' => array('neq', ''),
            ))->select();

            $count = 0;

            // dump($res);
            foreach($res as $k=>$v){
                if(!empty($v['true_name']) && !empty($v['idno']) && !empty($v['front_id_img']) && !empty($v['back_id_img']) && !empty($v['user_id'])){
                    $u = M('tran_ulist')->where(array(
                        'user_id' => $v['user_id'],
                        'idno' => $v['idno'],
                        'receiver' => $v['true_name'],
                    ))->save(array(
                        'front_id_img' => $v['front_id_img'],
                        'small_front_img' => $v['small_front_img'],
                        'back_id_img' => $v['back_id_img'],
                        'small_back_img' => $v['small_back_img'],
                    ));
                    if($u){
                        $count++;
                    }
                }
            }

            echo '修改' . $count . '条';

        }

        // 更新订单表的身份证上传状态
        public function order_idcard_update(){

            $res = M('tran_ulist')->alias('ul')
                                    ->field('ul.id')
                                    ->join('left join mk_transit_center as tc on ul.TranKd=tc.id')
                                    ->where(array(
                                        'member_sfpic_state' => array('eq', 1),
                                        'front_id_img' => array('eq', ''),
                                        'back_id_img' => array('eq', ''),
                                    ))
                                    ->select();
            
            $ids = '';
            foreach($res as $k=>$v){
                $ids .= $v['id'] . ',';
            }

            $ids = substr($ids,0,strlen($ids)-1);

            $count = 0;

            // dump($ids);
            if(!empty($ids)){
                $count = M('tran_ulist')->where(array('id'=>array('in', $ids)))->save(array('id_img_status' => 0));
            }

            echo '有' . $count . '条数据被设置为未上传<br />';

            $count = 0;

            $count = M('tran_ulist')->where(array(
                'idno' => array('neq', ''),
                'front_id_img' => array('neq', ''),
                'back_id_img' => array('neq', ''),
            ))->save(array(
                'id_img_status' => 100,
            ));

            echo '有' . $count . '条数据被设置为已上传<br />';

            $idno_res = M('tran_ulist')->where(array('idno'=>array('neq', '')))->save(array('id_no_status'=>'100'));

            echo '有' . $idno_res . '条数据被设置为已填写身份证号码<br />';

        }

        
        // 修改收件人地址表
        public function update_addressee(){

            $count1 = M('user_addressee')->where(array('id_card_front'=>'none'))->save(array('id_card_front'=>null));

            $count2 = M('user_addressee')->where(array('id_card_back'=>'none'))->save(array('id_card_back'=>null));

            $count3 = M('user_addressee')->where(array('id_card_front_small'=>'none'))->save(array('id_card_front_small'=>null));

            $count4 = M('user_addressee')->where(array('id_card_back_small'=>'none'))->save(array('id_card_back_small'=>null));

            $count5 = M('user_addressee')->where(array('status'=>array('neq', 0)))->save(array('status'=>0));

            echo '共修改' . $count1+$count2+$count3+$count4+$count5 . '条数据';

        }


    }    