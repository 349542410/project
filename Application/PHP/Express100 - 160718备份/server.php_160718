<?php
/**
 * 服务器端
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
    function info($resjson){
        include('config.php');
        $res        = json_decode($resjson,true);
        //print_r($res);
        //exit;
        //return $msg = array('do' => 'yes', 'title' => 'abort');

        $lastResult = $res['lastResult'];
        $data       = $lastResult['data'];
        $count      = count($data); //计算总数
        $status     = $res['status'];   //状态
        $state      = isset($lastResult['state'])?$lastResult['state']:0;    //物流的派送状态
        
        //Man150911 更改为使用 tran_list并读取CID加到 il_logs中
        //$sql        = "select * from mk_stnolist where `STNO` = '$lastResult[nu]' LIMIT 1"; //找到相对应的MKNO，
        $sql        = "select MKNO,CID from mk_tran_list where `STNO` = '$lastResult[nu]' LIMIT 1";

        $query      = mysql_query($sql);
        $mk         = array();
        
        if(!$query){
            return array('do'=>'yes', 'title'=>'yes');
            exit;
        }
        //if($mk = mysql_fetch_assoc($query)){    //如果数据存在并可以转为数组
        if($mk = mysql_fetch_array($query)){

            //Man 150910 读取所属公司，保存到 logs il_logs中，方便向其返回相关的logs
            $mkcid =  isset($mk['CID'])?$mk['CID']:0;

            mysql_query('START TRANSACTION');   //开启一个事务

            if($status == 'abort'){

                //将mk_logs中的相应记录state更改为404 (前提为state=200)
                $sql    ="select * from mk_logs where `MKNO` = $mk[MKNO]"; //找到相对应的MKNO，
                $query  =mysql_query($sql);
                if($query && $it  = mysql_fetch_array($query)){
                    if($it['state'] == '200'){
                        $sql = "update mk_logs set `state` = '404' where `id` = '$it[id]'";
                        mysql_query($sql);
                    }
                }
                //mysql_query("COMMIT");      //事务确认

                //返回信息
                return $msg = array('do' => 'yes', 'title' => 'abort');
                // echo $str_s;
                // exit;
            }

            if($status == 'shutdown'){
                //将mk_logs中的相应记录state更改为400 (前提为state=200)
                $sql="select * from mk_logs where `MKNO` = $mk[MKNO]"; //找到相对应的MKNO，
                $query=mysql_query($sql);
                //$it = mysql_fetch_assoc($query);
                if($query && $it=mysql_fetch_array($query)){
                    if($it['state'] == '200'){
                        $sql = "update mk_logs set `state` = '400' where `id` = '$it[id]'";
                        mysql_query($sql);
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

            //如果$data依然为空
            // if($count < 1){
            //      $data = array(
                        
            //      );  
            // }
            //数据处理
            $count  = count($data);  //再次计算总数
            $rcount = count($data);  // 用于计算真实有限的物流记录数
            $res1   = 0;

            //mk_logs MKNO 最早时间 20151113
            $early      = "select optime from mk_logs where `MKNO` = '$mk[MKNO]'";
            $ear        = mysql_query($early);
            /*
            //如果不存在此条信息，则保存
            if(mysql_num_rows($ear) == '0'){
                return $msg = array('do'=>'yes', 'title'=>$status);     //返回信息
            }else{
                $eartime = mysql_fetch_array($ear);
            }*/
        
            if(!$eartime = mysql_fetch_array($ear)){
                return $msg = array('do'=>'yes', 'title'=>$status);     //返回信息
            }

            for($i=$count-1;$i>-1; $i--){
                $did = $data[$i];
                /*查询此行数据是否已经存入数据表*/
                if($status != 'abort'){
                    //Man 150818 更改 tran_list中的IL_state 将申通所得的0,1,2,3,4,5 + 1000 后保存 3为签收
                    $ILst   = $state + 1000;

                    // MKIL   先查询 mk_il_logs 是否已经存在某条数据
                    $check_mkil = "SELECT * FROM mk_il_logs WHERE `MKNO` = '$mk[MKNO]' AND `content` = '$did[context]' AND `create_time` = '$did[ftime]'";

                    $res_mkil = mysql_query($check_mkil);// or die ("SQL: {$check_mkil}<br>Error:".mysql_error());

                    //如果物流时间 < mk_logs最早时间  20151113*
                    if(strtotime($did['ftime']) < strtotime($eartime['optime'])){
                        $res1++;
                        $rcount--;
                        continue;
                    }
                    
                    //如果不存在此条信息，则保存
                    if(mysql_num_rows($res_mkil) == '0'){
                        // 将相关资料直接将记录增加到mk_il_log中,150911添加CID
                        $sql_mkil = "INSERT INTO mk_il_logs (MKNO,content,create_time,status,CID) VALUES ('$mk[MKNO]', '$did[context]', '$did[ftime]',$ILst,$mkcid)";
                        if(mysql_query($sql_mkil)){
                            $res1++;
                        }
                    }else if($res_mkil){
                        $res1++;
                    }

                    //Man 150818 更改 tran_list中的IL_state 将申通所得的0,1,2,3,4,5 + 1000 后保存
                    //$ILst   = $state + 1000; Man150910放在上方，让mk_il_logs一起用这个状态
                    //150911增加 tms+1,以在更新时更新最新时间，因为在途状态很久，会一直不更新 暂未更新这个,tms=tms+1，到9月底再更新，因今天早上出现更新il_logs 乱了时间
					//$tlsql  = "UPDATE mk_tran_list SET IL_state=$ILst WHERE MKNO='$mk[MKNO]' LIMIT 1";
					//160315 增加 如果 tranlist的IL_state=1003则不进行状态更新
					$tlsql  = "UPDATE mk_tran_list SET IL_state=$ILst WHERE MKNO='$mk[MKNO]' AND IL_state<>1003 LIMIT 1";

                    mysql_query($tlsql);
                    
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
                mysql_query($tlsql);
            }

            /*/Man返回ERP 使用 SendERP.class单独发送 物流的所有状态20150915
            $canbackErp = isset($canbackErp)?$canbackErp:true;
            if($canbackErp)
                @back2erp($mk['MKNO'],$state,$data);
            */
                
            if($count == $res1){
                mysql_query("COMMIT");      //事务确认
                return $msg = array('do'=>'yes', 'title'=>$status);     //返回信息
                // echo $str_s;exit;
            }else{
                mysql_query("ROLLBACK");    //事务回滚
                return $msg = array('do'=>'no', 'title'=>$status);      //返回信息
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
        $url      	= 'http://s.mk.vip8801.com/Other/GetMKState.ashx';
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
    // $server->addFunction('hello');
    $server->addFunction('info');
    $server->start();
?>