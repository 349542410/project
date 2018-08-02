<?php

    // 收件人管理

    namespace Api\Model;
    use Think\Model;

    class UserAddresseeModel extends Model{

        //检测数据合法性
        private function m_check($data){

            $list = array(
                'name' => 100,
                'tel' => 20,
                'province' => 30,
                'city' => 30,
                'town' => 30,
                'address' => 200,
                'postal_code' => 10,
                'cre_type' => 30,
            );

            $allow = array(
                'cre_num' => 30,
                'address_alias' => 30,
            );

            foreach($list as $k=>$v){
                if(empty($data[$k])||strlen($data[$k])>=$v){
                    $this->error = 'l_data_error';
                    return false;
                }
            }

            foreach($allow as $k=>$v){
                if(strlen($data[$k])>=$v){
                    $this->error = 'l_data_error';
                    return false;
                }
            }

            return true;

        }

        //验证身份证和护照是否重复
        public function check_id_card($user_id, $name, $id_card, $tel){

            if(!empty($id_card) && !empty($user_id) && !empty($name)){
                $res = $this->addr_search(array(
                    'user_id' => $user_id,
                    'cre_num' => $id_card,
                    'name' => $name,
                    'tel' => $tel
                ),'');
                if(empty($res)){
                    return true;
                }else{
                    $this->error = 'l_id_card';
                    return false;
                }
            }else{
                return true;
            }
            
        }

        //删除图片文件
        public function del_img($url){
            if(is_array($url)){
                foreach($url as $k=>$v){
                    unlink($v);
                }
            }else{
                unlink($url);
            }
            return true;
        }

        //搜索
        public function addr_search($where,$limit){

            $where['delete_time'] = array('exp', 'is null');

            if(empty($limit)){
                $result = $this->alias('a')->where($where)->order('sys_time')->select();
            }else{
                $result = $this->where($where)->order('sys_time desc')->limit($limit)
                ->alias('a')
                ->field("a.*,b.lngname")
                ->join("left join mk_transit_center b on a.line_id = b.id")
                ->select();
            }
            
            return $result;

        }

        //添加
        public function addr_insert($data){

            if(!$this->m_check($data)){
                return false;
            }
            // if(!$this->check_id_card($data['user_id'], $data['name'], $data['cre_num'], $data['tel'])){
            //     return false;
            // }
            $result = $this->add($data);
            return (($result===false) ? false : true) ;

        }

        //更新
        public function addr_update($id,$data){

            if(empty($id)){
                $this->error = "l_data_error";
                return false;
            }
            if(!$this->m_check($data)){
                return false;
            }
            // if($checkIdCard){
            //     //需要验证身份证的重复性
            //     if(!$this->check_id_card($data['user_id'], $data['name'], $data['cre_num'], $data['tel'])){
            //         return false;
            //     }
            // }
            
            $data['id'] = $id;
            $result = $this->save($data);

            //删除图片
            // $this->del_img($url_arr);
            //修改到控制器里面了

            return (($result===false) ? false : true) ;

        }

        //删除
        public function addr_delete($id,$img_url){

            // $result = $this->where(array('id'=>$id))->delete();
            $result = $this->where(array('id'=>$id, 'delete_time'=>array('exp', 'is null')))->save(array('delete_time'=>date('Y-m-d H:i:s')));

            //删除图片
            // $this->del_img($img_url);
            //修改到控制器里面了

            return (($result===false) ? false : true) ;

        }

    }