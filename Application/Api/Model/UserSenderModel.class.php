<?php

    // 寄件人管理
    // liao ya di
    // create time : 2017-10-24
    // API MODEL

    namespace Api\Model;
    use Think\Model;

    class UserSenderModel extends Model{

        //自动验证
        protected $_validate = array(
            array('s_name', 'require', 'l_data_error', 1, 'regex', 3),
		    array('s_name', '1,30', 'l_data_error', 1, 'length', 3),

            array('s_street', 'require', 'l_data_error', 1, 'regex', 3),
		    array('s_street', '1,300', 'l_data_error', 1, 'length', 3),

            array('s_country', 'require', 'l_data_error', 1, 'regex', 3),
		    array('s_country', '1,50', 'l_data_error', 1, 'length', 3),

            array('s_state', 'require', 'l_data_error', 1, 'regex', 3),
		    array('s_state', '1,50', 'l_data_error', 1, 'length', 3),

            array('s_city', 'require', 'l_data_error', 1, 'regex', 3),
		    array('s_city', '1,50', 'l_data_error', 1, 'length', 3),

            array('s_tel', 'require', 'l_data_error', 1, 'regex', 3),
		    array('s_tel', '1,30', 'l_data_error', 1, 'length', 3),

            array('s_code', 'require', 'l_data_error', 1, 'regex', 3),
		    array('s_code', '1,30', 'l_data_error', 1, 'length', 3),
        );

        // protected $_validate = array(
        //     array('s_name', 'require', 's_name字段必须存在', 1, 'regex', 3),
		//     array('s_name', '1,30', 's_name字段长度不符合要求', 1, 'length', 3),

        //     array('s_street', 'require', 's_street字段必须存在', 1, 'regex', 3),
		//     array('s_street', '1,300', 's_street字段长度不符合要求', 1, 'length', 3),

        //     array('s_country', 'require', 's_country字段必须存在', 1, 'regex', 3),
		//     array('s_country', '1,50', 's_country字段长度不符合要求', 1, 'length', 3),

        //     array('s_state', 'require', 's_state字段必须存在', 1, 'regex', 3),
		//     array('s_state', '1,50', 's_state字段长度不符合要求', 1, 'length', 3),

        //     array('s_city', 'require', 's_city字段必须存在', 1, 'regex', 3),
		//     array('s_city', '1,50', 's_city字段长度不符合要求', 1, 'length', 3),

        //     array('s_tel', 'require', 's_tel字段必须存在', 1, 'regex', 3),
		//     array('s_tel', '1,30', 's_tel字段长度不符合要求', 1, 'length', 3),

        //     array('s_code', 'require', 's_code字段必须存在', 1, 'regex', 3),
		//     array('s_code', '1,30', 's_code字段长度不符合要求', 1, 'length', 3),
        // );


        //查找全部
        public function send_search($where,$limit='',$field=''){

            if(empty($where)){
                $this->error = 'l_query_missing';
                return false;
            }

            $where['delete_time'] = array('exp', 'is null');

            if(empty($limit)){
                if(empty($field)){
                    return $this->where($where)->order('create_time desc')->select();
                }else{
                    return $this->field($field)->where($where)->order('create_time desc')->select();
                }
                
            }else{
                if(empty($field)){
                    return $this->where($where)->order('create_time desc')->limit($limit)->select();
                }else{
                    return $this->field($field)->where($where)->order('create_time desc')->limit($limit)->select();
                }
                
            }

        }


        //查找一个
        public function send_find($where){
            if(empty($where)){
                $this->error = 'l_query_missing';
                return false;
            }
            $where['delete_time'] = array('exp', 'is null');
            return $this->where($where)->find();
        }


        //插入
        public function send_insert($data){
            $data = $this->create($data);
            $res = $this->add($data);
            return (($res===false) ? false : true) ;
        }


        //更新
        public function send_update($data){
            $data = $this->create($data);
            $res = $this->save($data);
            return (($res===false) ? false : true) ;
        }

        //删除
        public function send_del($where){
            if(empty($where)){
                $this->error = 'l_query_missing';
                return false;
            }
            // $result = $this->where($where)->delete();
            $where['delete_time'] = array('exp', 'is null');
            $result = $this->where($where)->save(array('delete_time'=>date('Y-m-d H:i:s')));
            return (($result===false) ? false : true) ;

        }

    }