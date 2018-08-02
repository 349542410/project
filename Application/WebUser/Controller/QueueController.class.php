<?php

    // liao ya di
    // 发送短信队列

    namespace WebUser\Controller;

    class QueueController extends BaseController{

        // 发送用户补填身份证信息的队列
        // 队列左进右出
        public function send_userinfo_queue(/*$num=100, $second=60*60*/){

            $num = I('get.num', 100);
            $second = I('get.second', 60*60);

            try{

                $redis = new \Redis();

                // dump(C('Redis'));
                // die;

                $redis->connect(C('Redis')['Host'],C('Redis')['Port'],1);
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

            }catch(\RedisException $e){
                echo "[ " . $e->getMessage() . " ]";
                die;
            }

            

            $res = array();
            $ra = null;
            // 循环数组，发送短信
            $ra = new \Org\MK\MPSM();
            foreach($info as $k=>$v){
                $res[$k] = $ra->send(array(
                    'data'=>array($v['receiver'],$v['MknoKey']),
                    'no'=>$v['reTel'],
                ));
            }

            // return $info;

            // dump($res);
            echo "<br /><br />count => " . count($info);

            // return array(
            //     'count' => count($info),
            // );

        }

    }