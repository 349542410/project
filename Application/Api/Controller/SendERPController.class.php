<?php
/**
 * 发送mk_il_logs数据表的数据到erp
 *
 * 关联：mk_il_log_notes
 *
 * Jie 2015-09-14 最新版本的发送功能，目前使用中
 *
 * 版本日期 2015-09-14 --- 2016-10-11
 *
 * 20161011 修改：{标注1：去除物流信息中包含的“&”或“=”符号}
 */
namespace Api\Controller;
use Think\Controller;
class SendERPController extends Controller{
	public function _initialize(){
		$allowhost = C('MESSAGE.ALLOWHOST');
		$ser_name = $_SERVER['HTTP_HOST'];
		/*if($allowhost != $ser_name){
			echo 'network error';die;
		}*/
	}

//==================== 发送到ERP =========================

	public function sendERP(){
//		 echo 'hello';exit();
		$CID = I('get.CID','1');
		ini_set('memory_limit','4088M');
		ini_set('max_execution_time', 0);

		$num = C('LOGS_SET.LIMIT')?C('LOGS_SET.LIMIT'):100;		//操作数量限制
		
		//检测mk_il_log_notes 中是否有操作记录
		$maxlid = M('IlLogsNotes')->where(array('state'=>'200','il_CID'=>$CID))->max('lid'); //order('id desc')->getField('lid');//find();	//取最大的id

		if(!$maxlid) $maxlid = 0;
		$where['i.CID'] = array('eq',$CID);

		//如果有记录
		if($maxlid > 0){
			$where['i.id'] = array('gt',$maxlid);
		}

		$logs = M('IlLogs i')->field('i.*,l.mStr1')->join('LEFT JOIN mk_logs l ON l.MKNO=i.MKNO AND l.state=i.status')->where($where)->order('id asc')->limit($num)->select();

		// dump($logs);
		$count = count($logs);
		//如果已经没有数据符合
		if($count < 1){
			echo 'None'.date('Y-m-d H:i:s'); exit;
		}

		$arr = array();

		foreach($logs as $k=>$item){
			$arr1            = array();
			$arr1['MKNO']    = $item['MKNO'];
			$arr1['state']   = $item['status'];//['state']; Man 2015-09-14
			$arr1['ftime']   = $item['create_time'];
			$arr1['pno']     = ($item['status']==20) ? $item['mStr1'] : ''; //返回中转批号
			//Man161011 add array(.....'"'),cancel htmlspecialchars 因为htmlspecialchars会带来&
			//$arr1['context'] = htmlspecialchars(str_replace(array("&","=",'"'), "", $item['content']));//$item['content'];  //标注1 Jie 20161011
			
			// 20170718 jie  使用 strip_tags 剥去字符串中的 HTML 标签
			$arr1['context'] = strip_tags(str_replace(array("&","=",'"',"'"), "", $item['content']));//$item['content'];  //标注1 Jie 20161011

			array_push($arr, $arr1);
		}
		// dump($arr);
		// die;
		$url 	= C('LOGS_SET.URL');  //post url
		$schema = "json";

		$param 	= json_encode($arr);

		$post_data = "schema=".$schema."&pf=mkil&param=".$param;	//组合

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
		// 	curl_close($ch);

		$result = posturl($url,$post_data);


		//$res = trim(json_decode($result,true));		//返回的结果

/*		$data['lid'] 	= $logs[$count-1]['id'];
		$data['il_CID'] = $CID;
		$data['state'] 	= $res;
		M('IlLogsNotes')->add($data);		//保存记录
		echo 'Done'.date('Y-m-d H:i:s').' NUM:'.count($logs);*/

		if($res == '200'){
			$data['lid'] 	= $logs[$count-1]['id'];
			$data['il_CID'] = $logs[$count-1]['CID'];
			$data['state'] 	= $res;
			M('IlLogsNotes')->add($data);		//保存记录
            \Think\Log::write('保存记录', M('IlLogsNotes')->getLastSql());
			echo 'Done';
		}else{
			echo "Error:\r“";
			echo $res."”=================\r";;
			var_dump($arr);
			echo "===========================================\r";
			echo $param;
		}
		echo "\r".date('Y-m-d H:i:s').' NUM:'.count($logs);
		exit();
	}

}