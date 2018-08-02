<?php
/*
  【打包中转】(原物流入库)
*/

namespace Api\Controller;

use Think\Controller\RestController;

/**
 * LogsInController
 *
 * A controller to  receive the JSON sent by ERP software,
 * and put them into datebase then return the results to the ERP.
 *
 * @copyright MeGao 2014
 */
Class LogsInController extends RestController
{

    /**
     * 物流入库数据交换
     * @return [type] [description]
     */
    Public function index()
    {

        /**
         * JSON类已包含MD5校验
         * @return array [数组]/ 错误码 [(默认为0)]
         */
        //sleep(3);
        $log = new \Org\MK\JSON;
        $logs = $log->get();
        \Think\Log::write('test--物流入库数据交换' . json_encode($logs, 320));
        //L($logs['LAN']);	//检测语言
        //p($logs);die;
        //检测收到的数据是否有误

        if (!is_array($logs)) {
            // $this->response($log->respons("","","",$logs,""),'json');
            echo $log->respons("", "", null, 0, L("SYSERROR0"));
            exit(0);
        }

        if ($logs['KD'] != "MKILLOG") {
            // echo $log->respons($logs['KD'],$logs['CID'],"",3,""); //不能使用$logs,因它是数组，3表示JSON类型不对。
            echo $log->respons("", "", '', 3, L("SYSERROR3"));
            exit(0);
        }

        $arr = $logs['toMKIL'];
        //循环遍历接收到的数组
        for ($i = 0; $i < count($arr); $i++) {

            if (is_array($arr[$i])) {
                foreach ($arr[$i] as $key => $value) {
                    $data[$key] = $value;
                }
            }
            //p($data);continue;
            M()->startTrans();//开启事务 

            /**
             * 接收到数据后，先在mk_logs表中检查重复
             * @param array $err 错识提示文字
             * @param array $suc 回传true|false标识是否保存成功
             * @param array $code 错误码（默认为0），1表示成功，2以上，为失败
             * @param array $logstr 保存成功 与失败回传的提示内容
             */

            /*
                //20141215重复的判断方法
                1.重复是指在 mk_tran_list中
                2.MKNO与JSON.MKNO相同且IL_state>=20（当前操作状态为20）
                返回错误码 5(无法重复操作)
                3.IL_state=12才能进行本操作，否则返回错误6(未称重的包裹不能进行此操作)
            */
            $condition['MKNO'] = $data['MKNO'];

            // $orderNum = M('logs')->lock(true)->where($condition)->select();

            //Man150911
            //$check      = M('tran_list')->where($condition)->getField('IL_state'); 后面用到tranlist['CID']
            //151231增加读取TranKd，与传来的tcid(线路id)比较是否相同，不相同不能保存
            $tranlist = M('tran_list')->field('IL_state,CID,TranKd,CID,sfid,reTel,reAddr,id')->where($condition)->find();
            $check = isset($tranlist['IL_state']) ? $tranlist['IL_state'] : false;

            //dump($check);
            if (!$check) {//echo 0;
                $err[$i] = /*$data['MKNO'].*/
                    L('ONORDER');
                $suc[$i] = "false";
                $code[$i] = 25;
                $logstr[$i] = $err[$i];
            } elseif ($check >= 20) {//echo 1;
                $err[$i] = /*$data['MKNO'].*/
                    L('OPERATED');
                $suc[$i] = "false";
                $code[$i] = 5;
                $logstr[$i] = $err[$i];
            } elseif ($check != 12 && $check != 16 && $check != 19) {  //16为新增中转返回仓状态
                //echo 2;
                $err[$i] = /*$data['MKNO'].*/
                    L('NOWEIGH');
                $suc[$i] = "false";
                $code[$i] = 6;
                $logstr[$i] = $err[$i];

            } /*elseif($orderNum>0){
                //echo 3;
            	$err[$i]=$data['MKNO'].L('existed');
                $suc[$i]="false";
                $code[$i]=2;
                $logstr[$i]=$err[$i];
            }*/ else {//echo 4;

                /*20150915Man增加去16（即返仓）后再中转的处理
                  直接把原来20对应的相关资料覆盖就可以了？
                  
                  20151201应不能覆盖，否则会出现 中转信息在返仓的后面
                  是否直接将20改为其它数字？如21，要注意多次中转的问题
                  或者在操作16时，直接将20改为21即可？(->暂按此方法)

                  20160108增加中转扫描面单时检查中转批号是否已完成(补充航空资料为完成)
                */

                //151231增加读取TranKd，与传来的tcid(线路id)比较是否相同，不相同不能保存，放在分析//160114前
                if ($tranlist['TranKd'] - $data['tcid'] <> 0) {
                    $back[$i] = Array(
                        "MKNO" => $data['MKNO'],
                        "Success" => false,
                        "LOGCODE" => 0,
                        "LOGSTR" => L('TransitlinesError')
                    );
                    break;
                }

                //160114增加读取到达中转仓名称
                $sttores = M('TransitCenter')->field('toname,cid')->where(array('id' => $data['tcid']))->find();
                if (!$sttores) {
                    $back[$i] = Array(
                        "MKNO" => $data['MKNO'],
                        "Success" => false,
                        "LOGCODE" => 0,
                        "LOGSTR" => L('TransitError')
                    );
                    break;
                }
                $stto = $sttores['toname'];
                //160114要增加同一批次号里，手机号码，身份证号码，收件人地址分别不能重新
                if ($sttores['cid'] * 1 == 1) {
                    //分析身份证是否正确
                    $strsf = trim($tranlist['sfid']);
                    //if($strsf=='0' || $strsf=='8' || strlen($strsf)<>18 || !certificate($strsf)){
                    if (!certificate($strsf)) {

                        //160115增加保存到il_logs中
                        $_illogs = array(
                            'MKNO' => $data['MKNO'],
                            'content' => L('WaitClientInfo'),
                            'create_time' => date('Y-m-d H:i:s'),
                            'status' => 23,
                            'CID' => $tranlist['CID'],
                            'noid' => 0,
                        );

                        M()->rollback();    // 160115事务回滚 要这样才能添加记录 每个for都有一个startTrans,所以没有影响
                        $_d = M('il_logs')->data($_illogs)->add();

                        $back[$i] = Array(
                            "MKNO" => $data['MKNO'],
                            "Success" => false,
                            "LOGCODE" => 0,
                            "LOGSTR" => L('TransitNoInfoError')
                        );
                        break;
                    }
                    $strtmp = 'sfid=' . $strsf . '&reTel=' . $tranlist['reTel'] . '&reAddr=' . $tranlist['reAddr'] . '&_logic=or';
                    $condition = array(
                        'noid' => $data['Airlineid'],
                        '_query' => $strtmp,
                    );
                    $samelist = M('TranList')->where($condition)->count();
                    if ($samelist > 0) {
                        $back[$i] = Array(
                            "MKNO" => $data['MKNO'],
                            "Success" => false,
                            "LOGCODE" => 0,
                            "LOGSTR" => L('TransitSameError')
                        );
                        break;
                    }
                }


                //160108添加对中转批号的检查，如果no.status>9则不能中转到此批号中
                $pnost = M('TransitNo')->where(array('id' => $data['Airlineid']))->getField('status');
                $pnost *= 1;
                if ($pnost > 9) { //Man160113 不要使用!$pnost因为 =0是也是false的
                    $back[$i] = Array(
                        "MKNO" => $data['MKNO'],
                        "Success" => false,
                        "LOGCODE" => 0,
                        "LOGSTR" => L('TransitNoDone', array('NO' => $data['mStr1'])) . $pnost . $data['Airlineid']
                    );
                    break;
                }

                $data['CID'] = $logs['CID']; //p($data);continue;
                D('logs')->create($data);
                $fetch = D('logs')->add();

                /*20151201取消修改的做法，重用原做法(上述代码)
                $data['CID']=$logs['CID'];//p($data);continue;

                $logsid     = 0;
                if($check==16){
                    $logsid = M('logs')->where(array('MKIL'=>$data['MKNO'],'state'=>20))->getfield('id');
                }
                $logsdata   = D('logs');
                $logsdata->create($data);
                if($logsid>0){
                    $fetch  = $logsdata->where("id=$logsid")->save();
                }else{
                    $fetch  = $logsdata->add();
                }
                */


                //continue;
                $suc[$i] = false;
                $logstr[$i] = "NONE";
                if ($fetch > 0) {
                    $con['MKNO'] = $data['MKNO'];
                    //151231增加保存 批次号id 到tran_list.noid中
                    $sfield = array('IL_state' => 20, 'noid' => $data['Airlineid']);
                    $insert = M('tran_list')->where($con)->setField($sfield);//返回影响的行数

                    $record['MKNO'] = $data['MKNO'];

                    //Man20150504
                    //$record['content'] = "包裹已入库";
                    $record['content'] = L('TranContext', array(
                        'STNM' => $logs['SNM'],
                        'STTO' => $stto,
                        'NM' => $data['Airline'],
                        'NO' => $data['AirNo']
                    ));

                    //150911 Man 增加这个，将field改为 不自动更新时间的方式，方便日后直接更新数据表的数据，前增加状态与发件公司tran_list.CID
                    $record['create_time'] = date('Y-m-d H:i:s');
                    $record['status'] = 20;
                    $record['CID'] = $tranlist['CID'];
                    $record['noid'] = $data['Airlineid'];

                    $add = M('il_logs')->data($record)->add();//返回增加的记录的id值

                    (($insert != false) && ($add != false)) ? ($suc[$i] = "true") . ($code[$i] = 1) : ($suc[$i] = "false") . (($code[$i] = 3));
                    if ($code[$i] == 3) {
                        $err[$i] = L('insert_error');
                    }
                    $suc[$i] == "true" ? $logstr[$i] = L('succeed') : $logstr[$i] = $err[$i];
                }

                if (($fetch > 0) && ($insert > 0) && ($add > 0)) {
                    M()->commit();//事务提交
                    // echo "commit";
                } else {
                    M()->rollback();//事务回滚
                    // echo "rollback";
                }
            }

            $back[$i] = Array(
                "MKNO" => $data['MKNO'],
                "Success" => $suc[$i],
                "LOGCODE" => $code[$i],
                "LOGSTR" => $logstr[$i]
            );

        }//END OF for 
        \Think\Log::write('test--返回数据' . json_encode($log->respons($logs['KD'], $logs['CID'], $back), 320));
        echo $log->respons($logs['KD'], $logs['CID'], $back);
        exit();
    }//END OF index function

}
