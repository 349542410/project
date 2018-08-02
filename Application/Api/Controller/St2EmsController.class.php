<?php
/**
 * 申通物流管理 服务器端
 */
namespace Api\Controller;
use Think\Controller\HproseController;
class St2EmsController extends HproseController{

	/**
	 * 发送到快递100(订阅)  包含运行_index,toERP,toData，统一返回结果
	 * @param  [type] $st      [导入的csv处理后的数组]
	 * @param  [type] $company [承接快递的公司类型，默认EMS]
	 * @return [type]          [description]
	 */
	public function _index($st,$company,$sure_post,$force_kd100,$force_erp,$kind){
		ini_set('memory_limit','4088M');
		ini_set('max_execution_time', 0);

		$sti = count($st);
		if($sti < 1){
			return $tips = array('status'=>'404','msg'=>"没有数据需要发送");
			// echo 'none'.date('Y-m-d H:i:s');exit;
		}

		//获取config中的快递100配置信息
		$KD100 	= C('KD100');
		$kd100key 	= $KD100['KD100KEY'];
		$cbackurl 	= $KD100['CALLBACKURL'];
		$url      	= $KD100['POSTURL'];		

		
		$schema = "json";
		$cburl 	= array(
			'callbackurl'=>$cbackurl
		);
		$pars 	= array(
			"company"	=> $company,//可变值
			"number"	=> "",
			"from"		=> "",
			"to"		=> "",
			"key"		=> $kd100key,
			"parameters"=> $cburl,
			"code"=> 200,
		);

		$i         = 0;//推送到KD100成功总数
		$erp_num   = 0;//推送ERP成功总数
		$pu_num    = 0;//已执行过推送ERP，已推送过的就不再次推送，同样视为已推送
		$lo_num    = 0;//已执行过推送KD100，已推送过的就不再次推送，同样视为已推送
		$no_had_no = 0;//申通号没有对应的MKNO
		foreach($st as $item){
			$pars['number'] = $item[1];

			$mkno = M('Stnolist')->where(array('STNO'=>$item[0]))->getField('MKNO');

			// 如果此申通单号没有对应的美快单号，则此不执行，执行下一个
			if(!$mkno){
				$no_had_no++;
				continue;
			}

			//第一件事，先马上把此数据写入数据表，默认两个推送状态都为0
			$this->toData($mkno,$item[1],'','first',$kind);

			$check = M('StnoToEms')->where(array('MKNO'=>$mkno,'EMSNO'=>$item[1]))->find();

			// $sendERP = array('status'=>'2', 'msg'=>'已执行过推送ERP');//测试

			$sendERP = $this->toERP($mkno,$item[1],'800',$check,$force_erp,$kind);//详细说明请看下面的toERP函数

			// 20161115 jie
			// $sure_post是否推送给快递100，当on的时候执行推送；$force_kd100为on的时候，也需要执行推送
			if($sure_post == 'on' || $force_kd100 == 'on'){
				$sendKD100 = $this->toKD100($mkno,$item[1],$pars,$schema,$url,$check,$force_kd100,$kind);
			}else{
				$sendKD100['status'] = '0';
			}

			// return $sendKD100;

			// 当两个参数返回都为0的时候，则终止当前单号的发送，执行下一个单号发送
			if($sendERP['status'] == '0' && $sendKD100['status'] == '0'){
				continue;

			}else if($sendERP['status'] != '0' && $sendKD100['status'] == '0'){//以下判断用作最后的总结语句的返回

				$erp_num++;

				if($sendERP['status'] == '2') $pu_num++;

			}else if($sendERP['status'] == '0' && $sendKD100['status'] != '0'){

				$i++;
				if($sendKD100['status'] == '2') $lo_num++;

			}else if($sendERP['status'] != '0' && $sendKD100['status'] != '0'){

				$erp_num++;
				if($sendERP['status'] == '2') $pu_num++;
				$i++;
				if($sendKD100['status'] == '2') $lo_num++;
			}

		}
		// echo 'Done '.date('Y-m-d H:i:s').'， 成功：'.$i."(总：".$sti.")";
		// 总结语句
		return $backArr = array('status'=>'1', 'msg'=>'操作成功，总数量：'.$sti.'。成功推送到KD100：'.$i."(已执行过推送：".$lo_num.")；成功推送到ERP：".$erp_num."(已执行过推送：".$pu_num.")");
	}

	/**
	 * 推送到KD100
	 * @param  [type] $mkno      [MKNO]
	 * @param  [type] $EMSNO     [默认为 EMS单号。以后可以根据情况改变为其他物流]
	 * @param  [type] $pars      [组建参数之一]
	 * @param  [type] $schema     [待发送参数之一]
	 * @param  [type] $url       [curl post用的地址]
	 * @param  [type] $check     [默认为 EMS单号。以后可以根据情况改变为其他物流]
	 * @param  [type] $k         [重复发送次数；当此次推送返回失败的时候，允许反复发送3次，超过3次仍然为失败，则不再执行此次操作并返回“推送失败”]
	 * @return [type] [description]
	 */
	public function toKD100($mkno,$EMSNO,$pars,$schema,$url,$check,$force_kd100,$kind,$k=0){

		//不等于on的时候执行重复发送的过滤
		if($force_kd100 != 'on'){
			if($check && $check['kd100_state'] == '200'){
				return $backArr = array('status'=>'2', 'msg'=>'已执行过推送KD100');
			}
		}

		$pars['parameters']['callbackurl'] .= '?mkno='.$mkno;//$mkno 为可变值
		// dump($pars);die;
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
			// return $res;
		//200为成功，重复发送的则会返回501，501也表示已经成功，保存的时候改为200后保存
		if($res['returnCode'] == '200' || $res['returnCode'] == '501'){

			//mk_stnolist
			$data_KD['kd100status'] 	= 200;	// 状态标注为200表示已发送
			M('Stnolist')->where(array('MKNO'=>$check['MKNO']))->save($data_KD);	// 更新快递100状态

			//mk_send_record
			$data_Record['username'] 	= 'admin';//$username;
			$data_Record['STNO'] 		= $EMSNO;
			M('SendRecord')->add($data_Record);	// 保存操作记录
			
			$this->toData($mkno,$EMSNO,200,'kd100_state');
			// return $backArr = array('status'=>'1', 'msg'=>'推送成功');

		}else{

			$k++;//重复发送次数
			
			// 当此次推送返回失败的时候，允许反复发送3次，超过3次仍然为失败，则不再执行此次操作并返回“推送失败”
			if($k <= 3){
				$this->toKD100($mkno,$EMSNO,$pars,$schema,$url,$check,$force_kd100,$kind,$k);
			}else{
				return $backArr = array('status'=>'0', 'msg'=>'推送KD100失败');
			}
		}
	}
	
	/**
	 * 推送到ERP系统 20161101 Jie
	 * @param  [type] $mkno      [MKNO]
	 * @param  [type] $EMSNO     [默认为 EMS单号。以后可以根据情况改变为其他物流]
	 * @param  string $condition [已转其他物流的代号编码。默认为800，表示默认是转EMS]
	 * @param  [type] $check     [默认为 EMS单号。以后可以根据情况改变为其他物流]
	 * @param  [type] $k         [重复发送次数；当此次推送返回失败的时候，允许反复发送3次，超过3次仍然为失败，则不再执行此次操作并返回“推送失败”]
	 * @return [type]            [description]
	 */
	public function toERP($mkno,$EMSNO,$condition='800',$check,$force_erp,$kind,$k=0){
		/*
			161101Man
			取MKNO,与EMS单号
			生成的$arr1 为 MKNO=,pno='EMS单号',context=''，state=800,ftime=''
		*/
		// $check = M('StnoToEms')->where(array('MKNO'=>$mkno,'EMSNO'=>$EMSNO))->find();
		if($force_erp != 'on'){
			if($check && $check['erp_state'] == '200'){
				return $backArr = array('status'=>'2', 'msg'=>'已执行过推送ERP');
			}
		}

		// 一个订单号下所有的物流信息一起推送
		$arr = array();
		$arr1            = array();
		$arr1['MKNO']    = $mkno;// MKNO
		$arr1['pno']     = $EMSNO;// 默认为 EMS单号
		$arr1['state']   = $condition; //Man 2015-09-14
		$arr1['ftime']   = '';
		$arr1['context'] = '';

		array_push($arr, $arr1);//合成二维数组

		$url 	= C('LOGS_SET.URL');  //post url
		$schema = "json";
		$param 	= json_encode($arr);  

		$post_data = "schema=".$schema."&pf=mkil&param=".$param;	//组合
		
		$result = posturl($url,$post_data);

		$res = trim(json_decode($result,true));		//返回的结果

		if($res == '200'){
			// return $backArr = array('status'=>'1', 'msg'=>'操作成功');
			$this->toData($mkno,$EMSNO,200,'erp_state');
		}else{
			$k++;//重复发送次数
			// 当此次推送返回失败的时候，允许反复发送3次，超过3次仍然为失败，则不再执行此次操作并返回“推送失败”
			if($k <= 3){
				$this->toERP($mkno,$EMSNO,$condition='800',$check,$force_erp,$kind,$k=0);
			}else{
				return $backArr = array('status'=>'0', 'msg'=>'推送ERP失败');
			}
		}
	}

	/**
	 * 数据保存mk_stno_to_ems(申通号转发EMS)
	 * @param  [type] $mkno  [MKNO]
	 * @param  [type] $EMSNO [默认为 EMS单号。以后可以根据情况改变为其他物流]
	 * @param  [type] $code  [推送到ERP后返回的成功值 200]
	 * @return [type]        [description]
	 */
	public function toData($mkno,$EMSNO,$code,$at_state,$kind=''){

		//检查是否存在
		$check = M('StnoToEms')->where(array('MKNO'=>$mkno,'EMSNO'=>$EMSNO))->find();
			
		// 第一件事是先把数据保存到数据表
		if($code == '' && $at_state == 'first'){

			// 已存在的则不再保存
			if($check){
				return true;
			}else{
				$data['MKNO']        = $mkno;
				$data['EMSNO']       = $EMSNO;
				$data['ctime']       = date('Y-m-d H:i:s');
				$data['erp_state']   = 0;
				$data['kd100_state'] = 0;
				$data['tcid']        = $kind;
				$res = M('StnoToEms')->add($data);		//保存记录

				if($res){
					return true;
				}else{
					return false;
				}
			}

		}else{

			if($check){
				$data1[$at_state] = $code;
				$res = M('StnoToEms')->where(array('id'=>$check['id']))->save($data1);		//保存记录
			}else{
				$data['MKNO']    = $mkno;
				$data['EMSNO']   = $EMSNO;
				$data['ctime']   = date('Y-m-d H:i:s');
				$data[$at_state] = $code;
				$data['tcid']    = $kind;
				$res = M('StnoToEms')->add($data);		//保存记录
			}

			if($res){
				return $backArr = array('status'=>'1', 'msg'=>'操作成功');
			}else{
				return $backArr = array('status'=>'0', 'msg'=>'操作失败');
			}
		}

	}

	/**
	 * 查总数
	 * @param  [type] $where [description]
	 * @return [type]        [description]
	 */
    public function count($where,$p,$ePage){
    	$list = M('StnoToEms t')->field('t.*,s.STNO')->join('left join mk_stnolist s on s.MKNO=t.MKNO')->where($where)->order('ctime desc')->page($p.','.$ePage)->select();
    	
    	$count = M('StnoToEms t')->join('left join mk_stnolist s on s.MKNO=t.MKNO')->where($where)->count();
    	return array('count'=>$count, 'list'=>$list);
    }

}