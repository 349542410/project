<?php
/**
 * 发送mk_logs数据表的数据到erp
 *
 * Jie 2015-09-14 旧版发送功能，已暂停使用
 *
 * 版本日期 2015-08-03
 */
namespace Api\Controller;
use Think\Controller;
class SendERP222Controller extends Controller{


//==================== 发送到ERP =========================

	public function sendERP(){
		$CID = I('get.CID','1');
		ini_set('memory_limit','4088M');
		ini_set('max_execution_time', 0);

		$num = C('LOGS_SET.LIMIT')?C('LOGS_SET.LIMIT'):100;		//操作数量限制
		
		//检测mk_log_notes 中是否有时间操作记录
		$notes = M('LogsNotes')->where(array('state'=>'200'))->order('id desc')->find();	//取最大的时间
		$where['t.CID'] = array('eq',$CID);
		//如果有记录
		if($notes){
			$where['l.id'] = array('neq',$notes['lid']);		 //Man 保留，否则每次都会有一个记录，不
			$where['l.optime'] = array('egt',$notes['optime']);	 //大于等于这个记录的时间,Man需使用大于和等于，因同一时候可能会有多条记录Limit可能会漏掉一些
			//依照 $where 条件再加上按照时间从小到大的顺序排列查找
			// 特殊情况：同一个时间中有多个数据符合但是由于limit限制，所以取出来的时候是按照“随机”原则取的
			$logs = M('Logs l')->field('l.*')->join('LEFT JOIN mk_tran_list t ON t.MKNO=l.MKNO')->where($where)->order('l.optime asc')->limit($num)->select();

		}else{	//如果没有，则直接从mk_logs表中按照时间从小到大的顺序找出*条记录进行操作
			
			// 获取limit(*)条数据中的最大时间值  备用
			// $Model = M();
			// $maxtime = $Model->query("select max(optime) from ( SELECT optime FROM mk_logs ORDER BY optime asc limit $num ) as a");
			// Man 150813 Left join mk_tran_list原因是 从该表中 选取 CID 作为查询条件
			$logs = M('Logs l')->field('l.*')->join('LEFT JOIN mk_tran_list t ON t.MKNO=l.MKNO')->where($where)->order('l.optime asc')->limit($num)->select();
		}

		//dump($logs);
		$count = count($logs);
		//如果已经没有数据符合
		if($count < 1){
			echo 'none'.date('Y-m-d H:i:s'); exit;
		}
		/*
		$arr = array();
		foreach($logs as $k=>$item){
			$arr[$k]['MKNO'] = $item['MKNO'];
			$arr[$k]['state'] = $item['state'];
			$arr[$k]['ftime'] = $item['optime'];
			$cona = array(
					'c8' => '生成面单',
					'c12' => '已揽收',
					'c20' => '已中转',
					'c60' => '打印快递单',
					'c100' => '再中转',
					'c200' => '已发快递',
				);
			$arr[$k]['context'] = isset($cona['c'.$item['state']])?$cona['c'.$item['state']]:'NULL';
		}*/
		$arr = array();
		foreach($logs as $k=>$item){
			$arr1 			= array();
			$arr1['MKNO'] 	= $item['MKNO'];
			$arr1['state'] 	= $item['state'];
			$arr1['ftime'] 	= $item['optime'];
			$cona = array(
					'c8' 	=> '生成面单',
					'c12' 	=> '已揽收',
					'c20' 	=> '已中转',
					'c60' 	=> '打印快递单',
					'c100' 	=> '再中转',
					'c200' 	=> '已发快递',
			);
			$arr1['pno']	 = ($item['state']==20) ? $item['mStr1'] : ''; //20150813 Man 返回中转批号
			$arr1['context'] = isset($cona['c'.$item['state']])?$cona['c'.$item['state']]:'NULL';
			array_push($arr, $arr1);
		}
		// dump($arr);die;
		$url 	= C('LOGS_SET.URL');  //post url
		$schema = "json";
		$param 	= json_encode($arr);  
		// echo $param;
		$post_data = "schema=".$schema."&pf=mkil&param=".$param;	//组合

		/* Jie 2015-09-14 
		// //通过curl函数发送
		// $ch = curl_init();
		// 	curl_setopt($ch, CURLOPT_POST, 1);
		// 	curl_setopt($ch, CURLOPT_HEADER, 0);
		// 	curl_setopt($ch, CURLOPT_URL,$url);
		// 	curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);

		// 	//当CURLOPT_RETURNTRANSFER设置为1时，如果成功只将结果返回，不自动输出返回的内容。如果失败返回FALSE；
		// 	//若不使用这个选项：如果成功只返回TRUE，自动输出返回的内容。如果失败返回FALSE
		// 	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

		// 	$result = curl_exec($ch);
		 	curl_close($ch); */
		
		// Jie 2015-09-14
		$result = posturl($url,$post_data);

		$res = trim(json_decode($result,true));		//返回的结果

		//dump($res);
		$data['lid'] 	= $logs[$count-1]['id'];
		$data['optime'] = $logs[$count-1]['optime'];
		$data['state'] 	= $res;
		M('LogsNotes')->add($data);		//保存记录
		echo 'Done'.date('Y-m-d H:i:s').' NUM:'.count($logs);
	}

}