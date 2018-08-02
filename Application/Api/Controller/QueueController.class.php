<?php

    // liao ya di
    // API CONTROLLER

    namespace Api\Controller;
    use Think\Controller;

    class QueueController extends Controller{

        // 发送用户补填身份证信息的队列
        // 队列左进右出
        public function send_userinfo_queue($num=100, $second=60*60){

            $redis = new \Redis();
            $redis->connect(C('Redis')['Host'],C('Redis')['Port']);
            $redis->auth(C('Redis')['Auth']);

            $i = 0;
            for(; $i<$num; $i++){

                $info[$i] = $redis->rPop('send_userinfo_queue');
                if(!$info[$i]){
                    unset($info[$i]);
                    break;
                }

                $info[$i] = unserialize($info[$i]);
                // 如果时间小于间隔 $second 秒，则将元素重新放入队列右边
                if(time()-$info[$i]['time'] < $second){
                    $redis->rpush('send_userinfo_queue', \serialize($info[$i]));
                    unset($info[$i]);
                    break;
                }
                
            }

            $res = null;
            $ra = null;
            // 循环数组，发送短信
            foreach($info as $k=>$v){
                $ra = new \Org\MK\MPSM();
                $res = $ra->send(array(
                    'data'=>array($v['receiver'],$v['MknoKey']),
                    'no'=>$v['reTel'],
                ));
            }

            // return $info;

            return array(
                'count' => count($info),
            );

        }

    }