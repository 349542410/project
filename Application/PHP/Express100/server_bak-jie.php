<?php
/**
 * 服务器端  20161114已经不作使用
 */
    
    //require_once('./../../hprose2/Hprose.php');
    require_once('./../../hprose_php5/HproseHttpServer.php');
    // //例子
    // function hello($name) {
    //     return 'Hello ' . $name;
    // }

    /**
     * 服务器端  快递100回调数据处理
     * @param  [type] $res [description]
     * @return [type]      [description]
     */
    function info($arr){
        include('config.php');
        // $res        = json_decode($resjson,true);
        //print_r($res);
        //exit;
        //return $msg = array('do' => 'yes', 'title' => 'abort');

        $lastResult = $arr['lastResult'];
        $STNO       = $lastResult['nu'];   //申通单号
        $data       = $lastResult['data'];
        $count      = count($data); //计算总数
        $status     = $arr['status'];   //状态
        $state      = isset($lastResult['state'])?$lastResult['state']:0;    //物流的派送状态
        
        //Man150911 更改为使用 tran_list并读取CID加到 il_logs中
        //$sql        = "select * from mk_stnolist where `STNO` = '$lastResult[nu]' LIMIT 1"; //找到相对应的MKNO，
        $sql        = "select MKNO,CID from mk_tran_list where `STNO` = '$STNO' LIMIT 1";

        $first      = $pdo->query($sql);
        // $mk         = array();
        
        // if(!$first){
        //     return array('do'=>'yes', 'title'=>'yes');
        //     exit;
        // }
        //if($mk = mysql_fetch_assoc($query)){    //如果数据存在并可以转为数组
        if($first->rowCount() > 0){

            $mk = $first->fetch(PDO::FETCH_ASSOC);
            //Man 150910 读取所属公司，保存到 logs il_logs中，方便向其返回相关的logs
            $mkcid =  isset($mk['CID']) ? $mk['CID'] : 0;

            $pdo->beginTransaction();   //开启事务

            $lsql    ="select * from mk_logs where `MKNO` = '$mk[MKNO]'"; //找到相对应的MKNO，
            $second  = $pdo->query($lsql);
            // return $second;

            if($second->rowCount() > 0){

                $it = $second->fetchAll(PDO::FETCH_ASSOC);

                // 为abort时，不处理mk_oi_log,只更新mk_logs中的相应记录state更改为404 (前提为state=200)
                if($status == 'abort'){
                    foreach($it as $k=>$item){
                        //将mk_logs中的相应记录state更改为404 (前提为state=200)
                        if($item['state'] == 200){
                            $Upsql[$k] = "UPDATE mk_logs SET state = '404' WHERE id = '$item[id]'";
                            $pdo->query($Upsql[$k]);
                        }
                    }

                    $pdo->commit();      //事务确认
                    //操作执行完毕，返回信息
                    return $msg = array('do' => 'yes', 'title' => $status);exit;
                    //即 return $msg = array('do' => 'yes', 'title' => 'abort');
                }

                // 为shutdown:结束
                if($status == 'shutdown'){

                    foreach($it as $k=>$item){
                        //将mk_logs中的相应记录state更改为400 (前提为state=200)
                        if($item['state'] == 200){
                            $Upsql[$k] = "UPDATE mk_logs SET state = '400' WHERE id = '$item[id]'";
                            $pdo->query($Upsql[$k]);
                        }
                    }

                    //如果$data为空，则添加一条完成的信息
                    if($count < 1){
                        $data[] = array(
                            'context' => '已完成',
                            'ftime' => date('Y-m-d H:i:s',time()),
                        );  
                    }
                }

            }

            //如果$data依然为空
            // if($count < 1){
            //      $data = array(
                        
            //      );  
            // }
            //数据处理
            $count  = count($data);  //再次计算总数
            $rcount = count($data);  // 用于计算真实有限的物流记录数
            $res1   = 0;
            $res2   = 0;    //测试用，数据比较

            //mk_logs MKNO 最早时间 20151113
            $early      = "SELECT optime FROM mk_logs WHERE `MKNO` = '$mk[MKNO]'";
            $ear        = $pdo->query($early);
            /*
            //如果不存在此条信息，则保存
            if(mysql_num_rows($ear) == '0'){
                return $msg = array('do'=>'yes', 'title'=>$status);     //返回信息
            }else{
                $eartime = mysql_fetch_array($ear);
            }*/
        
            if($ear->rowCount() == 0){
                return $msg = array('do'=>'yes', 'title'=>$status);     //返回信息
            }

            $eartime = $ear->fetch(PDO::FETCH_ASSOC);
            // return $eartime;

            for($key=$count-1;$key>-1; $key--){
                $did = $data[$key];
                /*查询此行数据是否已经存入数据表*/
                if($status != 'abort'){
                    //Man 150818 更改 tran_list中的IL_state 将申通所得的0,1,2,3,4,5 + 1000 后保存 3为签收
                    $ILst   = $state + 1000;

                    // MKIL   先查询 mk_il_logs 是否已经存在某条数据
                    $check_mkil[$key] = "SELECT * FROM mk_il_logs WHERE `MKNO` = '$mk[MKNO]' AND `content` = '$did[context]' AND `create_time` = '$did[ftime]'";

                    $res_mkil[$key] = $pdo->query($check_mkil[$key]);// or die ("SQL: {$check_mkil}<br>Error:".mysql_error());

                    $count_item[$key] = $res_mkil[$key]->fetchColumn(); //获取查询结果总数

                    //如果物流时间 < mk_logs最早时间  20151113*
                    if(strtotime($did['ftime']) < strtotime($eartime['optime'])){
                        $res1++;
                        $rcount--;
                        continue;
                    }
                    
                    //如果不存在此条信息，则保存
                    if($count_item[$key] == 0){
                        // 将相关资料直接将记录增加到mk_il_log中,150911添加CID
                        $sql_mkil[$key] = "INSERT INTO mk_il_logs (MKNO,content,create_time,status,CID) VALUES ('$mk[MKNO]', '$did[context]', '$did[ftime]',$ILst,$mkcid)";
                        
                        if($pdo->exec($sql_mkil[$key]) !== false){
                            $res1++;
                        }
                        $res2++;
                    }else if($res_mkil[$key]){  //已经存在也+1
                        $res1++;
                    }

                    //Man 150818 更改 tran_list中的IL_state 将申通所得的0,1,2,3,4,5 + 1000 后保存
                    //$ILst   = $state + 1000; Man150910放在上方，让mk_il_logs一起用这个状态
                    //150911增加 tms+1,以在更新时更新最新时间，因为在途状态很久，会一直不更新 暂未更新这个,tms=tms+1，到9月底再更新，因今天早上出现更新il_logs 乱了时间
                    //$tlsql  = "UPDATE mk_tran_list SET IL_state=$ILst WHERE MKNO='$mk[MKNO]' LIMIT 1";
                    //160315 增加 如果 tranlist的IL_state=1003则不进行状态更新
                    $ex_time    = $data['0']['ftime'];
                    $ex_context = $data['0']['context'];
                    $tlsql  = "UPDATE mk_tran_list SET IL_state=$ILst, ex_time='$ex_time', ex_context='$ex_context'  WHERE MKNO='$mk[MKNO]' AND IL_state<>1003 LIMIT 1";

                    $pdo->query($tlsql);
                    
                    //$res1++;    //每次成功后+1
                }

                // // ERP 先查询 MIS_mk_logs 是否已经存在某条数据
                // $check_erp="select * from MIS_mk_logs where `MKNO` = '$mk[MKNO]' and `context` = '$data[$i][context]' and `ftime` = '$data[$i][ftime]'";

                // $res_erp = mysql_query($check_erp);

                // //如果不存在此条信息，则保存
                // if(mysql_num_rows($res_erp) == '0'){
                //  $sql_erp = "INSERT INTO MIS_mk_logs (MKNO,context,ftime,status) VALUES ('$mk[MKNO]', '$data[$i][context]', '$data[$i][ftime]', '$lastResult[state]')";

                //  $res2 = mysql_query($sql_erp);
                // }

            }

            //20151113真实有效的物流记录是否只有一个 如果是而且$ILst<>1001
            if($rcount == 1 && $ILst <> 1001){
                $tlsql  = "UPDATE mk_tran_list SET IL_state='1001' WHERE MKNO='$mk[MKNO]' LIMIT 1";
                $pdo->query($tlsql);
            }

            /*/Man返回ERP 使用 SendERP.class单独发送 物流的所有状态20150915
            $canbackErp = isset($canbackErp)?$canbackErp:true;
            if($canbackErp)
                @back2erp($mk['MKNO'],$state,$data);
            */
                
            if($count == $res1){
                $pdo->commit();      //事务确认
                return $msg = array('do'=>'yes', 'title'=>$status,'n1'=>$res1,'n2'=>$res2,'count'=>$rcount);     //返回信息
                // echo $str_s;exit;
            }else{
                $pdo->rollback();    //事务回滚
                return $msg = array('do'=>'no', 'title'=>$status,'n1'=>$res1,'n2'=>$res2,'count'=>$rcount);      //返回信息
                //echo $str_f;exit;
            }
        }else{
            //echo 'Error：没有与此对应的美快单号';
            return array('do'=>'yes', 'title'=>$status);
        }
    }

    //160315补充：不再使用以下方式返回ERP
    function back2erp($mkno,$state,$data){
        $resb       = json_encode(array('MKNO'=>$mkno,'state'=>$state,'data'=>$data));
        $post_data  = "pf=mkil&param=".$resb;
        $url        = 'http://s.mk.vip8801.com/Other/GetMKState.ashx';
        $ch         = curl_init();
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_TIMEOUT,10); //超时6秒
        curl_setopt($ch, CURLOPT_URL,$url);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
        //当CURLOPT_RETURNTRANSFER设置为1时，如果成功只将结果返回，不自动输出返回的内容。如果失败返回FALSE；
        //若不使用这个选项：如果成功只返回TRUE，自动输出返回的内容。如果失败返回FALSE
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_exec($ch);
        curl_close($ch);
    }

    $server = new HproseHttpServer();
    $server->setErrorTypes(E_ALL);
    $server->setDebugEnabled();
    $server->addFunction('info');
    $server->start();
?>