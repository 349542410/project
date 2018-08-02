<?php
/*
  【打包中转】(因板位不够，装不下，下次再发) Man 150731
  151201操作时将上一个20 改为21 未测试
*/
namespace Api\Controller;
use Think\Controller\RestController;
/**
 * LogsInController
 *
 * A controller to  receive the JSON sent by ERP software,
 * and put them into datebase then return the results to the ERP.
 *
 * @copyright MeGao 2015 07 31
 */
Class LogsBackController extends RestController {
	Public function index(){
		$log = new \Org\MK\JSON;
        $logs = $log->get();
        //L($logs['LAN']);	//检测语言
        //p($logs);die;
        //检测收到的数据是否有误
        if(!is_array($logs)){
            // $this->response($log->respons("","","",$logs,""),'json');
            echo $log->respons("","",null,0,L("SYSERROR0"));
            exit(0);
        }

        if($logs['KD']!="MKILLOG"){
            // echo $log->respons($logs['KD'],$logs['CID'],"",3,""); //不能使用$logs,因它是数组，3表示JSON类型不对。
            echo $log->respons("","",'',3,L("SYSERROR3"));
            exit(0);
        }

        $arr=$logs['toMKIL'];
        for($i=0;$i<count($arr);$i++){
            if(is_array($arr[$i])){
                foreach ($arr[$i] as $key=>$value ) {
                    $data[$key]=$value;
                }
            }
            //p($data);continue;
            M()->startTrans();//开启事务

            /*
                //20141215重复的判断方法
                1.重复是指在 mk_tran_list中
                2.MKNO与JSON.MKNO相同且IL_state>=20（当前操作状态为20）
                返回错误码 5(无法重复操作)
                3.IL_state=12才能进行本操作，否则返回错误6(未称重的包裹不能进行此操作)
            */
            $condition['MKNO']=$data['MKNO'];

           // $orderNum = M('logs')->lock(true)->where($condition)->select();
            $check = M('tran_list')->field('id,IL_state,CID')->where($condition)->find();
            //dump($check);
            if(!$check){//echo 0;
                $err[$i]	= /*$data['MKNO'].*/L('ONORDER');
                $suc[$i]	= "false";
                $code[$i]	= 25;
                $logstr[$i]	= $err[$i];
            }elseif($check['IL_state']!=20){//echo 1;
                $err[$i]	= L('ErrorBack');
                $suc[$i]	= "false";
                $code[$i]	= 5;
                $logstr[$i]	= $err[$i];
            }else{
                $data['CID']    =$logs['CID'];//p($data);continue;
                $data['state']  = 16; //151231以前没有个，默认为20了，但不知为什么
                D('logs')->create($data);
                $fetch              = D('logs')->add();
                //continue;
                $suc[$i]	        = FALSE;
                $logstr[$i]	        = "NONE";
                if($fetch>0){
                    $con['MKNO']	= $data['MKNO'];
                    //151231增加保存 批次号id 到tran_list.noid中，所以返仓时应重设为0
                    $sfield         = array('IL_state'=>16,'noid'=>0);
                    $insert 		= M('tran_list')-> where($con)->setField($sfield);//返回影响的行数

                    //20151201 将logs中最近的20状态改为21 未测试
                    $elogs  = array(
                        "MKNO"      => $con['MKNO'],
                        "state"     => 20,
                    );
                    M('logs')->where($elogs)->order('id DESC')->limit(1)->setField('state',21);
                    //M()->query("UPDATE");
                    //============

                    //20151231增加 将 il_logs中20改为21
                    $illogs  = array(
                        "MKNO"      => $con['MKNO'],
                        "status"    => 20,
                    );
                    M('il_logs')->where($illogs)->setField('noid',0);


                    $record['MKNO'] 	=  $data['MKNO'];

                    //Man20150504
                    //$record['content'] = "包裹已入库";
                    $record['content'] 	= L('OkBack',array('STNM'=>$logs['SNM']));

                    //150911 Man 增加这个，将field改为 不自动更新时间的方式，方便日后直接更新数据表的数据，前增加状态与发件公司tran_list.CID
                    $record['create_time'] = date('Y-m-d H:i:s');
                    $record['status']      = 16;
                    $record['CID']         = $check['CID'];

                    $add				= M('il_logs')->data($record)->add();//返回增加的记录的id值

                    (($insert!=false) && ($add!=false))? ($suc[$i]="true").($code[$i]=1) : ($suc[$i]="false").(($code[$i]=3));
                    if($code[$i]==3) $err[$i]=L('insert_error');
                    $suc[$i]=="true" ? $logstr[$i]=L('BackSave') : $logstr[$i]=$err[$i];
                }

                if(($fetch>0)&&($insert>0)&&($add>0)) {
                    M()->commit();//事务提交
                    // echo "commit";
                }else{
                    M()->rollback();//事务回滚
                    // echo "rollback";
                }
            }

            $back[$i]=Array("MKNO" => $data['MKNO'],"Success" => $suc[$i],"LOGCODE"=>$code[$i],"LOGSTR" => $logstr[$i]);

        }//END OF for

        echo  $log->respons( $logs['KD'], $logs['CID'],$back);
        exit();
   	}//END OF index function
}