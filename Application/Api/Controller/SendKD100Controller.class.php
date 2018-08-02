<?php
/**
 * 发送mk_logs数据表的数据到KD100
 * 20151019 Man 更改计算一天前的方式
 */
namespace Api\Controller;
use Think\Controller;
class SendKD100Controller extends Controller{
	public function _initialize(){

		$allowhost = C('MESSAGE.ALLOWHOST');
		$ser_name = $_SERVER['HTTP_HOST'];
		// dump($ser_name);
		if($allowhost != $ser_name) echo 'network error';die;
	}
//=================================== 发送到快递100 ===================================

    public function toKD100(){
    	// $CID = I('CID','1');
    	$getdtcount = C('KD100.KD_NUM_LITMIT')?C('KD100.KD_NUM_LITMIT'):150; //每次读取的记录数量

		ini_set('memory_limit','4088M');
		ini_set('max_execution_time', 0);
 	
 		//$maxtime = date('Y-m-d H:i:s',time()-86400);	// 计算一天前
 		$maxtime = date('Y-m-d 00:00:00');	// 计算一天前 20151019 Man
 		
		$where['s.status']      = array('eq','20');		//20 为已使用
		$where['s.kd100status'] = array('eq','0');		//0 即为仍未发送到快递100
		$where['l.tranNum']     = array('exp','is not NULL');	//mk_logs 中的 申通号不为NULL
		$where['l.state']       = array('eq','200');		//mk_logs 中的 物流状态为200  已完成
		$where['t.TranKd']      = array('eq','1');			//mk_tran_list 中的 中转方式为申通
		//$where['l.transit']     = array('eq','申通');		//mk_logs 中的 中转方式为申通
		$where['l.optime'] = array('lt',$maxtime);		//mk_logs 中的操作时间；能够执行发送的是当前时间的前一天的所有数据
		//查找状态为已完成但尚未发送的申通号
		
		$st = M('Stnolist s')->field('s.id,s.STNO')
			->join('LEFT JOIN mk_logs l ON l.MKNO = s.MKNO')
			->join('LEFT JOIN mk_tran_list t ON t.MKNO=s.MKNO')
			->where($where)->order('l.optime')->limit($getdtcount)->select();

		/*暂时停用
		
		$where['s.status']      = array('eq','20');			//20 为已使用
		$where['s.kd100status'] = array('eq','0');		//0 即为仍未发送到快递100
		$where['l.STNO']      = array('exp','is not NULL');	//mk_tran_list 中的 申通号不为NULL
		$where['l.TranKd']    = array('eq','1');			//mk_tran_list 中的 中转方式为申通
		$where['l.IL_state']  = array('eq','200');		//mk_tran_list 中的 物流状态为200  已完成

		//查找状态为已完成但尚未发送的申通号
		$st = M('Stnolist s')->field('s.id,s.STNO')
				->join('LEFT JOIN mk_tran_list l ON s.STNO = l.STNO')
				->where($where)->order('l.optime')->limit($getdtcount)->select();
		 */
		// dump($st);
		// die;
		$sti = count($st);
		if($sti < 1){
			// return $tips = array('status'=>'404','msg'=>"没有数据需要发送");
			echo 'none'.date('Y-m-d H:i:s');exit;
		}

		//获取config中的快递100配置信息
		$KD100 		= C('KD100');
		$kd100key 	= $KD100['KD100KEY'];
		$cbackurl 	= $KD100['CALLBACKURL'];
		$url      	= $KD100['POSTURL'];		

		$i = 0;
		$schema = "json";
		$cburl 	= array(
			'callbackurl'=>$cbackurl
		);
		$pars 	= array(
			"company"	=> "shentong",
			"number"	=> "",
			"from"		=> "",
			"to"		=> "",
			"key"		=> $kd100key,
			"parameters"=> $cburl,
		);
		foreach($st as $item){
			$pars['number'] 	= $item['STNO'];
			$param 	= json_encode($pars);
			//return $param;
			$post_data = "schema=".$schema."&param=".$param;	//组合

			//通过curl函数发送
			$ch = curl_init();
				curl_setopt($ch, CURLOPT_POST, 1);
				curl_setopt($ch, CURLOPT_HEADER, 0);
				curl_setopt($ch, CURLOPT_URL,$url);
				curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);

				//当CURLOPT_RETURNTRANSFER设置为1时，如果成功只将结果返回，不自动输出返回的内容。如果失败返回FALSE；
				//若不使用这个选项：如果成功只返回TRUE，自动输出返回的内容。如果失败返回FALSE
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

				$result = curl_exec($ch);
				curl_close($ch);
				$res = json_decode($result,true);

			//200为成功，重复发送的则会返回501，501也表示已经成功，保存的时候改为200后保存
			if($res['returnCode'] == '200' || $res['returnCode'] == '501'){

				//mk_stnolist
				$data_KD['kd100status'] 	= 200;	// 状态标注为200表示已发送
				M('Stnolist')->where(array('id'=>$item['id']))->save($data_KD);	// 更新快递100状态

				//mk_send_record
				$data_Record['username'] 	= 'admin';//$username;
				$data_Record['STNO'] 		= $item['STNO'];
				M('SendRecord')->add($data_Record);	// 保存操作记录
				$i++;
			}
		}
		echo 'Done'.date('Y-m-d H:i:s').' NUM:'.$i."(总：".$sti.")";

    }
    
}