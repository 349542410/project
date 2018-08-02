<?php

    namespace Lib11\Queue;

    class JoinQueue{

        // 加入队列
        // 队列左进右出
        public function join_queue($data = array(), $conf=array()){

            if(empty($data)){
                return array(
                    'status' => false,
                    'info' => 'Data cannot be empty',
                );
            }

            $Host = empty($conf['Host']) ? C('Redis')['Host'] : $conf['Host'] ;
            $Port = empty($conf['Port']) ? C('Redis')['Port'] : $conf['Port'] ;
            $Auth = empty($conf['Auth']) ? C('Redis')['Auth'] : $conf['Auth'] ;
            $overtime = empty($conf['overtime']) ? 1 : $conf['overtime'];

            try{
                $redis = new \Redis();
                $redis->connect($Host,$Port,$overtime);
                $redis->auth($Auth);

                $redis->lPush('send_userinfo_queue',\serialize($data));
            }catch(\RedisException $e){
                return array(
                    'status' => false,
                    'info' => $e->getMessage(),
                );
                // file_put_contents(dirname(__FILE__).'/smsQueueLog.txt', "[ " . $e->getMessage() . " ]\n");
            }

            return array(
                'status' => true,
                'info' => '',
            );

        }

    }