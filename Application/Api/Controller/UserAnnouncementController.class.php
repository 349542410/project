<?php

    // 通告
    // liao ya di
    // create time : 2018-1-19
    // API CONTROLLER
    

    namespace Api\Controller;
    use Think\Controller\HproseController;

    class UserAnnouncementController extends HproseController{

        //验证规则
        public $rules = array(
		    'ant_id'                =>array(true,9),
		    'view_to_feeback'       =>array(true,2),
		    'uid'                   =>array(true,9),
		    'uname'                 =>array(true,30),
		    'feeback_content'       =>array(true,150),
		    'pid'                   =>array(true,9),
        );

        
        // new_ann 天以内发布的通告为最新通告
        private $new_ann = 1;
        
        
        //获取通知（新闻）列表
        public function getAnnTitle($user_id='', $where=array(), $limit=''){

            //登陆和未登陆显示的不同
            if(empty($user_id)){
                $where['_string'] = "(find_in_set('3',view_to))";
            }else{
                $where['_string'] = "(find_in_set('2',view_to) or find_in_set('3',view_to))";
            }

            //构造时间条件
            // date_default_timezone_set('UTC');
            $curr_time = time();
            $where['_string'] .= (' and start_time<' . $curr_time . ' and (end_time>' . $curr_time . ' or end_time=0)');

            // return $where;

            $model = M('AnnouncementTitle');
            $res = $model->field('a.id,bulletin_title,start_time,feeback,b.content')
                         ->alias('a')
                         ->join('left join mk_announcement_content b on a.id=b.ant_id')
                         ->where($where)
                         ->limit($limit)
                         ->order('start_time desc')
                         ->select();

            if($res === false){
                return array(
                    'status' => false,
                    'data' => $model->getError(),
                );
            }else{
                return array(
                    'status' => true,
                    'data' => $res,
                );
            }

        }


        //获取通告详细内容
        public function getContent($user_id, $id){

            if(empty($id)){
                return array(
                    'status' => false,
                    'data' => "id can't be empty",
                );
            }

            //登陆和未登陆显示的不同
            if(empty($user_id)){
                $where['_string'] = "(find_in_set('3',view_to))";
            }else{
                $where['_string'] = "(find_in_set('2',view_to) or find_in_set('3',view_to))";
            }
            $where['a.ant_id'] = $id;

            $model = M('AnnouncementContent');
            $res = $model->alias('a')
                         ->where($where)
                         ->join('left join mk_announcement_title b on a.ant_id=b.id')
                         ->find();
            
            if($res === false){
                return array(
                    'status' => false,
                    'data' => $model->getError(),
                );
            }else{
                return array(
                    'status' => true,
                    'data' => $res,
                );
            }

        }


        //检测是否有新通告
        public function checkNewAnn($user_id, $where){
            //登陆和未登陆显示的不同
            if(empty($user_id)){
                $where['_string'] = "(find_in_set('3',view_to))";
            }else{
                $where['_string'] = "(find_in_set('2',view_to) or find_in_set('3',view_to))";
            }

            //构造时间条件
            // date_default_timezone_set('UTC');
            $curr_time = time();
            $old_time = $curr_time-(24*60*60*$this->new_ann);
            $where['_string'] .= (' and start_time<' . $curr_time . ' and start_time>' . $old_time . ' and (end_time>' . $curr_time . ' or end_time=0)');
            
            // return $where;
            
            $res = M('AnnouncementTitle')->where($where)->count('id');
            return $res;
        }


        //获取新闻的权限
        public function getAuth($id){

            if(!empty($id)){
                return M('AnnouncementTitle')->where(array('id'=>$id))->getField('view_to');
            }else{
                return '';
            }

        }




        // //获取评论
        // public function getFeeback($id){

        //     if(empty($id)){
        //         return array(
        //             'status' => false,
        //             'data' => "id can't be empty",
        //         );
        //     }

        //     $model = M('AnnouncementFeeback');
        //     $res = $model->where(array('ant_id' => $id))->select();

        //     if($res === false){
        //         return array(
        //             'status' => false,
        //             'data' => $model->getError(),
        //         );
        //     }else{
        //         return array(
        //             'status' => true,
        //             'data' => $res,
        //         );
        //     }

        // }


        // //验证评论是否符合要求
        // public function checkFeeback($data){

        //     //常规验证
        //     $check_res = $this->check_data($data,$this->rules);
        //     if(!$check_res['success']){
        //         return $check_res;
        //     }

        //     if($data['view_to_feeback'] != '1' && $data['view_to_feeback'] != '2' && $data['view_to_feeback'] != '3'){
        //         //view_to_feeback 数据不符合要求
        //         return array(
        //             'success' => false,
        //             'error' => array('view_to_feeback','数据不符合要求'),
        //         );
        //     }

        //     //验证成功
        //     return $check_res;

        // }
        
        // //添加评论
        // public function addFeeback($info){

        //     //创建数据
        //     $model = M('AnnouncementFeeback');
        //     $data = $model->create($info);

        //     //验证数据
        //     $check_res = $this->checkFeeback($data);
        //     if(!$check_res['success']){
        //         return $check_res;
        //     }

        //     $res = $model->add($data);
        //     if($res === false){
        //         return array(
        //             'status' => false,
        //             'data' => array('model', $model->getError()),
        //         );
        //     }else{
        //         return array(
        //             'status' => true,
        //             'data' => $res,
        //         );
        //     }

        // }




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

                if(!empty($data[$k]) && strlen((string)$data[$k])>$v[1]){
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
    
    }




