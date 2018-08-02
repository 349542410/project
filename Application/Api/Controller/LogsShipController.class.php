<?php

namespace Api\Controller;

use Think\Controller\RestController;

/**
 * LogsShipController
 *
 * A controller to operate the transit
 *
 * @copyright MeGao 2014
 * 增加状态检查，连没有称重的都到这里来了20150505,
 * 150703允许称完后，未进行中转也可单发，如香港收到货后，就直接单发
 * 20150706 Man 增加 是否停发货 检查 0721个性停发方式
 */
Class LogsShipController extends RestController
{
    public $ship2 = null;

    public function index()
    {
        /**
         * JSON类已包含MD5校验
         * @return array [数组]/ 错误码 [(默认为0)]
         */
        $ship = new \Org\MK\JSON;
        $ships = $ship->get();
        $this->ship2 = $ship;
        //L($ships['LAN']);	//检测语言
        //检测收到的数据是否有误
        if (!is_array($ships)) {
            // echo "isn't array";
            //$ship->respons("","",$ships,0,L("SYSERROR0"));
            echo $ship->respons("", "", null, 0, L("SYSERROR0"));
            exit(0);
        }
        //echo $ship->respons("","",null,0,L("SYSERROR0"));exit; 

        if ($ships['KD'] != "MKILSHIP") {
            //echo "error_meg";
            //$ship->respons("","",$ships,3,L("SYSERROR3"));
            echo $ship->respons("", "", '', 3, L("SYSERROR3"));
            exit(0);
        }

        $operate = $ships['Operate'];
        //可使用in_array(),或使用switch Man20141211
        $opers = array(40, 100, 200, 300);
        if (in_array($operate, $opers)) {
            //if($operate==40||$operate==100||$operate==200||$operate==300){
            $this->operate_s($ships, $operate);
        } elseif ($operate == 60) {
            $this->operate_l($ships, $operate);
        } else {//echo "unknown";
            echo $ship->respons("", "", $ships, 21, L("error_meg"));
        }

        exit();
    }

    /**
     * 更改mk_tran_list.IL_state
     * 生成mk_logs记录
     * 并生成mk_il_logs表记录
     * @param  array $ships 接收的JSON转换而成的数组
     * @param  int $operate 操作数
     * @return [type]          [description]
     */
    private function operate_s($ships, $operate)
    {

        $arr = $ships['toMKIL'];

        //循环遍历接收到的数组
        for ($i = 0; $i < count($arr); $i++) {
            $toMKIL['MKNO'] = $arr[$i]['MKNO'];

            //回传默认值
            $log[$i]['MKNO'] = $toMKIL['MKNO'];
            $log[$i]['Success'] = "false";
            $log[$i]['LOGCODE'] = "5";
            $log[$i]['LOGSTR'] = L("OPERATED");

            //检查OPERATE的值
            //man 150911 
            //$check = M('tran_list')->where($toMKIL)->getField('IL_state');后面用到tranlist['CID']
            $tranlist = M('tran_list')->field('IL_state,CID')->where($toMKIL)->find();
            $check = isset($tranlist['IL_state']) ? $tranlist['IL_state'] : 0;
            if ($check == 100 && $check <= $operate) {
                //echo "operated_oprate_s1";
                //echo $this->ship2->respons("","",$log);
                continue;
            }
            /*20141231为测试打印中转快递单，暂时不进行此状态*/
            if (($check != 100) && ($check >= $operate)) {//echo "operated_oprate_s2";
                echo $this->ship2->respons("", "", $log);
                continue;
            }/**/

            M()->startTrans();//开启事务 

            //更改mk_tran_list.IL_state
            $insert = M('tran_list')->where($toMKIL)->setField('IL_state', $operate);//返回影响的记录数

            //生成mk_logs记录
            $data['CID'] = $ships['CID'];
            $data['transit'] = $arr[$i]['EXPNM'];
            $data['tranNum'] = $arr[$i]['EXPNO'];
            $data['MKNO'] = $arr[$i]['MKNO'];
            $data['mStr1'] = $ships['SNM'];
            $data['state'] = $operate;
            $fetch = M('logs')->data($data)->add();

            //判断提示类型
            switch ($operate) {
                case '40':
                    $prompt = L('SHIPIN', array('STNM' => $ships['SNM']));
                    break;
                case '100':
                    $prompt = L('SHIPOUT',
                        array('STNM' => $ships['SNM'], 'EXPNM' => $arr[$i]['EXPNM'], 'EXPNO' => $arr[$i]['EXPNO']));
                    break;
                case '200':
                    $prompt = L('SHIPSEND',
                        array('STNM' => $ships['SNM'], 'EXPNM' => $arr[$i]['EXPNM'], 'EXPNO' => $arr[$i]['EXPNO']));
                    break;
                case '300':
                    $prompt = L('SHIPOVER', array('STNM' => $ships['SNM']));
                    break;
                default:
                    //echo $this->ship2->respons("","",$arr[$i],27,L("error_meg"));
                    $log[$i]['LOGCODE'] = "27";
                    $log[$i]['LOGSTR'] = L("error_meg");
                    break;
            }


            //生成mk_il_logs表记录
            $record['MKNO'] = $arr[$i]['MKNO'];
            $record['content'] = $prompt;

            //150911 Man 增加这个，将fiel改为 不自动的方式，前增加状态与发件公司tran_list.CID
            $record['create_time'] = date('Y-m-d H:i:s');
            $record['status'] = $operate;
            $record['CID'] = $tranlist['CID'];

            $add = M('il_logs')->data($record)->add();


            if (($insert > 0) && ($fetch > 0) && ($add > 0)) {
                M()->commit();//事务提交
                $log[$i]['MKNO'] = $toMKIL['MKNO'];
                $log[$i]['Success'] = "true";
                $log[$i]['LOGCODE'] = "1";
                $log[$i]['LOGSTR'] = L("succeed");

            } else {
                M()->rollback();//事务回滚
                $log[$i]['LOGCODE'] = "28";
                $log[$i]['LOGSTR'] = L("insert_error");
            }
        }
        echo $this->ship2->respons($ships['KD'], $ships['CID'], $log);
        exit();
    }


    /**
     * 更改mk_tran_list.IL_state=60
     * 并返回相关记录
     * @param  array $ships 接收的JSON转换而成的数组
     * @param  int $operate 操作数（即60）仅针对 60 进行的操作
     * @return JSON            订单相关信息
     * 20150706 增加停止发货，转到仓库功能 Man
     */
    private function operate_l($ships, $operate)
    {

        $arr = $ships['toMKIL'];
        //var_dump($arr);exit;
        //循环遍历接收到的数组
        for ($i = 0; $i < count($arr); $i++) {
            $toMKIL['MKNO'] = $arr[$i]['MKNO'];

            //20150706 Man 增加 是否停发货 检查 0721改为直接使用tran_list.pause_status=20为停发
            $_mkno = $arr[$i]['MKNO'];

            $_mkps = M('tran_list')->where($toMKIL)->getField('pause_status');
            //$_stid  = true; //默认true，即记录不存在时也当暂停
            //if($_mkps){
            //$_stid  = ($_mkps[0] == 20);
            //}
            $_stid = ($_mkps == 20);

            /*
            $_mkid  = M('tran_list')->where($toMKIL)->getField('id');
            $_stid  = M('tran_list_stop')->where(array('tid'=>$_mkid,'MKNO'=>$_mkno,'status'=>1))->find();
            */

            if ($_stid) {
                $log[0] = array(
                    "MKNO" => $arr[$i]['MKNO'],
                    "Success" => 'false',
                    "LOGCODE" => '0',
                    "LOGSTR" => L("Stoped"),
                );
                /*20150731 已在admin中添加了
                //增加到物流信息中,生成mk_il_logs表记录
                $record['MKNO']     = $arr[$i]['MKNO'];
                $record['content']  = $prompt;
                M('il_logs')->data($record)->add();
                */
                //150731增加可删除标记，因设定只有IL_state=8才可删除，可能这里没有用，但先做
                M('tran_list')->where($toMKIL)->setField('candel', 1);

                echo $this->ship2->respons($ships['KD'], $ships['CID'], $log);
                die();
            }


            //检查CHECK的值
            $check = M('tran_list')->where($toMKIL)->getField('IL_state');

            if (!$this->checkrecord($toMKIL['MKNO'], 60, $ships['KD'], $ships['CID'])) {
                exit;
            }
            /*20141231 暂不检查
            20150601无需检查，因仅针对 60 进行的操作
            if(($check!=100)&&($check>=$operate)){
                //echo "operated_oprate_l1";
                //echo $this->ship2->respons("","",$log);
                $log[$i]['MKNO']    = $toMKIL['MKNO'];
                $log[$i]['Success'] = "false";
                $log[$i]['LOGCODE'] = "5";
                $log[$i]['LOGSTR']  = L("OPERATED");
                echo $this->ship2->respons($ships['KD'],$ships['CID'],$log);
                exit(0);
            }*/

            M('tran_list')->where($toMKIL)->setField('IL_state', $operate);
            //$insert=M('tran_list')-> where($toMKIL)->setField('IL_state',60);
            //$data = M('tran_list')->where($toMKIL)->getField('sender,sendAddr,sendTel,shopType,shopName,auto_Indent1,auto_Indent2');
            $data = M('tran_list')->where($toMKIL)->find();
            $From[$i] = array(
                'sender' => $data['sender'],
                'sendAddr' => $data['sendAddr'],
                'sendTel' => $data['sendTel'],
                'shopType' => $data['shopType'],
                'shopName' => $data['shopName'],
                'auto_Indent1' => $data['auto_Indent1'],
                'auto_Indent2' => $data['auto_Indent2']
            );
            $To[$i] = array(
                'receiver' => $data['receiver'],
                'reAddr' => $data['reAddr'],
                'province' => $data['province'],
                'city' => $data['city'],
                'postcode' => $data['postcode'],
                'reTel' => $data['reTel'],
                'notes' => $data['notes']
            );
            //p($data);


            $Model = new \Think\Model();// 实例化一个model对象 没有对应任何数据表
            //M 20150521 增加catname(报关,打印货品列表的名称)的返回
            $list[$i] = $Model->query("select detail,number,price,weight,catname from __TRAN_ORDER__ where lid=" . $data['id']);

            $log[$i] = array(
                "MKNO" => $arr[$i]['MKNO'],
                "Success" => 'true',
                "LOGCODE" => '1',
                "LOGSTR" => L("succeed"),
                "TranKd" => $data['TranKd'],
                //Man20150429增加申通号显示
                'STNO' => $data['STNO'],
                "From" => $From[$i],
                "To" => $To[$i],
                "Order" => $list[$i]
            );
        }//END OF FOR
        // p($From);p($To);p($list);
        //p($big);
        echo $this->ship2->respons($ships['KD'], $ships['CID'], $log);

        /* $info=array(
             "KD"     =>   $ships['KD'],
             "CID"    =>   $ships['CID'],
             "CMD5"   =>   $ships['CMD5'],
             "STM"    =>   $ships['STM'],
             "LAN"    =>   $ships['LAN'],
             "toMKIL" =>   $big
             );
         $json_post=json_encode($info);
         p($json_post);
     */
        exit();
    }

    //20150506检查是否可进行本操作
    private function checkrecord($mno, $stn, $kd, $cid)
    {
        $state = M('tran_list')->where(array('MKNO' => $mno))->getField('IL_state');
        $state = trim($state);
        $rb = false;
        if ($state == '') {
            $i = 0;
            $log[$i]['MKNO'] = $mno;
            $log[$i]['Success'] = "false";
            $log[$i]['LOGCODE'] = "8";
            $log[$i]['LOGSTR'] = L('ONORDER');
            echo $this->ship2->respons($kd, $cid, $log);
            return false;
        }
        /*//20150731 中转后退仓的，不作处理，可再次中转或直接转发快递 Man
        if($stn==60 && $state==16){

        }*/

        if (($check != 100) && $state > $stn) {
            //if(($state==$stn) && ($state>60)){
            $i = 0;
            $log[$i]['MKNO'] = $mno;
            $log[$i]['Success'] = "false";
            $log[$i]['LOGCODE'] = "50";
            $log[$i]['LOGSTR'] = L("OPERATED");
            echo $this->ship2->respons($kd, $cid, $log);
            return false;
        }

        switch ($stn) {
            case 60:
                //150706允许称完后，未进行中转也可单发，如香港收到货后，就直接单发
                $rb = $state > 11;//>=20;
                //进行更精准提示 150706 Man
                if ($rb < 12) {
                    $i = 0;
                    $log[$i]['MKNO'] = $mno;
                    $log[$i]['Success'] = "false";
                    $log[$i]['LOGCODE'] = "7";
                    $log[$i]['LOGSTR'] = L('NOWEIGH') . ':' . L('NothisTime');
                    echo $this->ship2->respons($kd, $cid, $log);
                    return false;
                }
                break;
        }
        if (!$rb) {
            $i = 0;
            $log[$i]['MKNO'] = $mno;
            $log[$i]['Success'] = "false";
            $log[$i]['LOGCODE'] = "7";
            $log[$i]['LOGSTR'] = L('NothisTime');
            echo $this->ship2->respons($kd, $cid, $log);
        }
        return $rb;
        exit();
    }

}
