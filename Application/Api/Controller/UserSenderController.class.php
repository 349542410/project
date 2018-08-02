<?php

    // 寄件人管理
    // liao ya di
    // create time : 2017-10-24
    // API CONTROLLER

    namespace Api\Controller;
    use Think\Controller\HproseController;

    class UserSenderController extends HproseController{

        // 设置为默认
        public function set_default($id, $user_id){

            $model = M('');
            $model->startTrans();

            $res = M('UserSender')->where(array('user_id'=>$user_id, 'is_default'=>'1'))->save(array('is_default'=>'0'));
            if($res === false){
                $model->rollback();
                return false;
            }

            $res = M('UserSender')->where(array('user_id'=>$user_id, 'id'=>$id))->save(array('is_default'=>'1'));
            if($res === false){
                $model->rollback();
                return false;
            }

            $model->commit();
            return true;

        }

        //查找全部
        public function s_search($where,$limit='',$field=''){
            $model = new \Api\Model\UserSenderModel();
            $res = $model->send_search($where,$limit,$field);

            if($res===false){
                return array(
                    'success' => false,
                    'info' => $model->getError(),
                );
            }else{
                return array(
                    'success' => true,
                    'info' => $res,
                );
            }
        }


        //查找一个
        public function s_find($where){
            $model = new \Api\Model\UserSenderModel();
            $res = $model->send_find($where);

            if($res===false){
                return array(
                    'success' => false,
                    'info' => $model->getError(),
                );
            }else{
                return array(
                    'success' => true,
                    'info' => $res,
                );
            }
        }


        //添加
        public function s_insert($data){

            $model = new \Api\Model\UserSenderModel();
            $res = $model->send_insert($data);
            if($res===false){
                return array(
                    'success' => false,
                    'info' => $model->getError(),
                );
            }else{
                return array(
                    'success' => true,
                    'info' => '',
                );
            }
        }


        //更新
        public function s_update($data){

            $model = new \Api\Model\UserSenderModel();
            $res = $model->send_update($data);
            if($res===false){
                return array(
                    'success' => false,
                    'info' => $model->getError(),
                );
            }else{
                return array(
                    'success' => true,
                    'info' => '',
                );
            }
        }


        //删除
        public function s_delete($where){
            $model = new \Api\Model\UserSenderModel();
            $res = $model->send_del($where);

            if($res===false){
                return array(
                    'success' => false,
                    'info' => $model->getError(),
                );
            }else{
                return array(
                    'success' => true,
                    'info' => $res,
                );
            }
        }



        //特殊用途 - 实时搜索使用缓存
        public function real_time_search($where){
            $model = new \Api\Model\UserSenderModel();
            $res = $model->cache('user_id',300)->field('id,s_name')->where($where)->select();

            if($res===false){
                return array(
                    'success' => false,
                    'info' => $model->getError(),
                );
            }else{
                return array(
                    'success' => true,
                    'info' => $res,
                );
            }
        }
    
    }