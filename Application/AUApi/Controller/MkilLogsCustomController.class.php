<?php
/**
 * PDA设备用
 * 清关处理 服务器端  暂时未被使用
 */
namespace AUApi\Controller;
use Think\Controller\HproseController;
class MkilLogsCustomController extends HproseController{

    public function _save($info){

        $MKIL = $info['MKIL'];

        $arr = json_decode($MKIL,true);

        $LOG = array(
            '0'=> array(
                'MKNO'    =>$arr['toMKIL'][0]['MKNO'],
                'Success' =>'false',
                'LOGCODE' =>'0',
                'TranKd'  =>'',
                'STNO'    =>'',
                'LOGSTR'  =>'',
            ),
        );

        // 验证资料
        if($arr['CID'] != '2' || $arr['Operate'] != '60'){
            return array('Code'=>'0', 'CID'=>$arr['CID'], 'KD'=>$arr['KD'], 'Error'=>'资料校验不符', 'LOG'=>$LOG);
        }

        if($arr['KD'] != 'MKILSHIP'){
            return array('Code'=>'3', 'CID'=>$arr['CID'], 'KD'=>$arr['KD'], 'Error'=>'KD编码验证错误', 'LOG'=>$LOG);
        }

        if(!isset($arr['toMKIL'][0]['MKNO']) || $arr['toMKIL'][0]['MKNO'] == ''){
            return array('Code'=>'0', 'CID'=>$arr['CID'], 'KD'=>$arr['KD'], 'Error'=>'美快单号缺失或不正确', 'LOG'=>$LOG);
        }

        $MKNO = $arr['toMKIL'][0]['MKNO'];

        $check_mkno = M('TranList')->where(array('MKNO'=>$MKNO))->find();
        
        if(!$check_mkno){
            return array('Code'=>'0', 'CID'=>$arr['CID'], 'KD'=>$arr['KD'], 'Error'=>'美快单号不存在或错误', 'LOG'=>$LOG);
        }

        $center = M('TransitCenter')->field('transit,toname')->where(array('id'=>$check_mkno['TranKd']))->find();

        $ct_time = date('Y-m-d H:i:s',(time()-rand(20,300)));

        $Model = M();   //实例化
        $Model->startTrans();//开启事务

        $logs_data = array();
        $logs_data['CID']     = $arr['CID'];//$checkNo['tcid'];
        $logs_data['tranid']  = $check_mkno['noid']; //transit_no.id
        $logs_data['transit'] = $center['transit'];
        $logs_data['tranNum'] = $check_mkno['STNO'];
        $logs_data['mStr1']   = '清关';
        $logs_data['MKNO']    = $MKNO;
        $logs_data['weight']  = 0;
        $logs_data['state']   = 1400;//清关中
        
        // 检查mk_logs是否已经存在此条数据
        $check_logs = M('Logs')->where(array('CID'=>$arr['CID'],'tranid'=>$check_mkno['noid'],'transit'=>$center['transit'],'tranNum'=>$check_mkno['STNO'],'mStr1'=>'清关','MKNO'=>$MKNO,'weight'=>'0','state'=>'1400'))->count();
        
        // 已存在，则不作任何操作，标记为0
        if($check_logs > 0){
            $logs = 0;
        }else{// 不存在则新增
            $logs = M('Logs')->add($logs_data);
        }

        $il_data = array();
        $il_data['MKNO']        = $MKNO;
        $il_data['content']     = '正在清关中';
        $il_data['create_time'] = $ct_time;
        $il_data['status']      = 1400;//清关中
        $il_data['noid']        = $check_mkno['noid']; //transit_no.id
        $il_data['CID']         = $arr['CID'];
        $il_data['rount_time']  = date('Y-m-d H:i:s');

        // 检查是否已经存在
        $check_il = M('IlLogs')->where(array('MKNO'=>$MKNO,'content'=>'正在清关中','status'=>'1400','noid'=>$check_mkno['noid'],'CID'=>$arr['CID']))->count();

        // 已存在，则不作任何操作，标记为0
        if($check_il > 0){
            $ilLogs = 0;
        }else{// 不存在则新增
            $ilLogs = M('IlLogs')->add($il_data);

            $t_data['IL_state']   = 1400;//清关中
            $t_data['ex_time']    = $ct_time;
            $t_data['ex_context'] = '正在清关中';
            $tlist = M('TranList')->where(array('MKNO'=>$MKNO))->save($t_data);
        }

        if($tlist !== false && $logs !== false && $ilLogs !== false){

            $Model->commit();//提交事务成功
            $LOG = array(
                '0'=> array(
                    'MKNO'    =>$MKNO,
                    'Success' =>'true',
                    'LOGCODE' =>'1',
                    'TranKd'  =>$check_mkno['TranKd'],
                    'STNO'    =>$check_mkno['STNO'],
                    'LOGSTR'  =>'数据保存成功',
                ),
            );

            return array('Code'=>'1', 'CID'=>$arr['CID'], 'KD'=>$arr['KD'], 'Error'=>'操作成功', 'LOG'=>$LOG);
        }else{
            $Model->rollback();//事务有错回滚
            $LOG = array(
                '0'=> array(
                    'MKNO'    =>$MKNO,
                    'Success' =>'false',
                    'LOGCODE' =>'0',
                    'TranKd'  =>$check_mkno['TranKd'],
                    'STNO'    =>$check_mkno['STNO'],
                    'LOGSTR'  =>'数据保存失败',
                ),
            );

            return array('Code'=>'0', 'CID'=>$arr['CID'], 'KD'=>$arr['KD'], 'Error'=>'操作失败，请检查再试', 'LOG'=>$LOG);
        }

    }


}