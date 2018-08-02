<?php
/**
 * Wap --- Work  服务器端
 */
namespace Api\Controller;
use Think\Controller\HproseController;
class WapWorkController extends HproseController{    
    protected $crossDomain =    true;
    protected $P3P         =    true;
    protected $get         =    true;
    protected $debug       =    true;

    /**
     * 通过接受到的code1,code2查询中转批号对应的线路名称和板数
     * @param  [type] $arr [description]
     * @return [type]      [description]
     */
    public function getInfo($arr){
    	$info = M('TransitNo')->field('id,tcid,bnum')->where(array('id'=>$arr['code2'],'no'=>$arr['code1']))->find();

    	$tc = M('TransitCenter')->field('name,id')->where(array('id'=>$info['tcid']))->find();

    	return array('bnum'=>$info['bnum'],'name'=>$tc['name'],'id'=>$info['id']);
    }

    /**
     * [_save description]
     * @param  [type] $id           [description]
     * @param  [type] $confirm_code [description]
     * @param  [type] $license      [description]
     * @return [type]               [description]
     */
    public function _save($id,$confirm_code,$license){
        $Model = M();   //实例化
        $Model->startTrans();//开启事务

		$tranNo    = M('TransitNo')->field('id,tcid,accno')->where(array('id'=>$id))->find();
		$auth_code = M('AuthCodeList')->where(array('tcid'=>$tranNo['tcid']))->getField('auth_code');

        //分析单号对应的 到达确认码是否正确
        if($confirm_code != $tranNo['accno']){
            return $backArr = array('status'=>'0','msg'=>'到达确认码不正确');
        }
    	// 分析单号对应的 授权码是否正确
    	if(md5($license) != $auth_code){
    		return $backArr = array('status'=>'0','msg'=>'授权码不正确');
    	}


    	//将该批中转的所有快递单进行到货转发快递处理
		$map['noid']     = array('eq',$tranNo['id']);
		// $map['IL_state'] = array('neq',200);
    	$reflash = M('TranList')->where($map)->setField('IL_state','200');

    	// return $backArr = array('status'=>'1','msg'=>$reflash);
    	if($reflash === false){

            $Model->rollback();//事务有错回滚
            return $backArr = array('status'=>'0','msg'=>'操作失败','nums'=>$reflash);
    		
    	}else if($reflash == 0){

            $Model->commit();//提交事务成功
            return $backArr = array('status'=>'0','msg'=>'没有数据被更新','nums'=>$reflash);

        }else{
            $map['IL_state'] = array('eq',200);
            $list = M('TranList')->field('MKNO,STNO')->where($map)->select();

            $center = M('TransitCenter')->field('id,toname,transit')->where(array('id'=>$tranNo['tcid']))->find();

            $log_msg = "已离开".$center['toname']."，发往 ".$center['transit']."，面单号：";

            $i = 0;
            foreach($list as $key=>$item){
                //保存物流信息到mk_il_logs
                $log_data['content']     = $log_msg.$item['STNO'];
                $log_data['MKNO']        = $item['MKNO'];
                $log_data['create_time'] = date('Y-m-d H:i:s');
                $log_data['status']      = 200;
                $log_data['noid']        = $tranNo['id'];
                $log_data['state']       = 0;
                $log_data['CID']         = $center['id'];

                $logs[$key] = M('IlLogs')->add($log_data);
                if($logs[$key] !== false) $i++;
            }

            if(intval($i) == intval($reflash)){
                $Model->commit();//提交事务成功
                return $backArr = array('status'=>'1','msg'=>'操作成功','nums'=>$reflash);
            }else{
                $Model->rollback();//事务有错回滚
                return $backArr = array('status'=>'0','msg'=>'操作失败，本次所有操作撤销','nums'=>$reflash);
            }
        }


    }



}