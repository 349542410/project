<?php
/**
 * 美快优选3(湛江EMS)
 * 功能包括： 支付清关，快递号导入，批号对数
 * 
 */
namespace Api\Controller;
use Think\Controller\HproseController;
class AdminCeShiController extends HproseController{

    /**
	 * 20180503 MKBc3\index 不用该方法
     * 报关  批次号列表 视图
     * @param  [type] $map [description]
     * @return [type]      [description]
     */
    public function customsList($map, $ids){

		$list = M('TransitNo tn')
			  ->field('tn.id,tn.no,tn.date,tn.airdatetime,tn.airno,tn.status,tc.name,tn.tcid')
			  ->join('LEFT JOIN mk_transit_center tc ON tn.tcid = tc.id')
			  ->where($map)
			  ->order('tn.date desc')
			  ->select();// 20171226 按date时间倒序排序

		foreach($list as $key=>$item){
			$res = $this->each_count($item['id']);
			$list[$key]['all']  = $res['al']?$res['al']:0; //总数
			$list[$key]['not']  = $res['one']?$res['one']:0; //未发送
			$list[$key]['done'] = $res['done']?$res['done']:0; //已发送
			$list[$key]['two']  = $res['two']?$res['two']:0; //已审核
			$list[$key]['four'] = $res['four']?$res['four']:0; //有误
		}
		return array('list'=>$list);		
	}
	
	/**
	 * 报关各状态 查询  
     * @param  [type] $id [tran_list.id]
	 * @return [type] [description]
	 */
    public function each_count($id){

		$sql = "SELECT  
				sum(noid = $id) AS al,
				sum(custom_status = '0') AS one, 
				sum(custom_status = '1') AS done, 
				sum(custom_status = '200')AS two, 
				sum(custom_status = '400')AS four 
				FROM `mk_tran_list` WHERE `noid` = ".$id;

		$m = new \Think\Model();

		$arr = $m->query($sql);

		$backArr = $arr[0];

		return $backArr;
    }

    //根据ID集查询线路名  暂时没用  20180307 jie
    public function _center_list($ids){
    	$where = array();
    	$where['id'] = array('in',$ids);

    	$center_list =  M('TransitCenter')->field('id,name')->where($where)->select();
    	return $center_list;
    }

	

//=============== 导入csv  ====================
	/**
	 * 查总数
	 * @param  [type] $where [description]
	 * @return [type]        [description]
	 */
    public function count($where,$p,$ePage){
    	$list = M('StnoToEms t')->field('t.*')->join('left join mk_tran_list l on l.MKNO=t.MKNO')->where($where)->order('ctime desc')->page($p.','.$ePage)->select();
    	$count = M('StnoToEms t')->join('left join mk_tran_list l on l.MKNO=t.MKNO')->where($where)->count();
    	return array('count'=>$count, 'list'=>$list);
    }

	/**
	 * 发送到快递100(订阅)  包含运行_index,toERP,toData，统一返回结果
	 * @param  [type] $st          [导入的csv处理后的数组]
	 * @param  [type] $company     [承接快递的公司类型，默认EMS]
	 * @param  [type] $sure_post   [是否推送给快递100]
	 * @param  [type] $force_kd100 [强制推送给快递100]
	 * @param  [type] $force_erp   [强制推送给ERP]
	 * @param  [type] $kind        [线路ID]
	 * @return [type]              [description]
	 */
	public function _index($st,$company,$sure_post,$force_kd100,$force_erp,$kind,$first_run){
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

		$i             = 0;//推送到KD100成功总数
		$erp_num       = 0;//推送ERP成功总数
		$pu_num        = 0;//已执行过推送ERP，已推送过的就不再次推送，同样视为已推送
		$lo_num        = 0;//已执行过推送KD100，已推送过的就不再次推送，同样视为已推送
		$no_had_no     = 0;//申通号没有对应的MKNO
		$no_had_arr    = array();//申通号没有对应的MKNO 数组
		$not_this_line = 0;//该MKNO是否属于所选的线路
		$not_this_arr  = array();//该MKNO是否属于所选的线路 数组

		foreach($st as $item){

			$pars['number'] = $item[1];

			//检查此MKNO是否属于选中的线路
			$mkno_info = M('TranList')->field('MKNO,TranKd')->where(array('MKNO'=>$item[0]))->find();
			$mkno = $mkno_info['MKNO'];

			// 如果此美快单号不存在，则此不执行，执行下一个
			if(!$mkno_info){
				$no_had_no++;
				$no_had_arr[] = $item[0];
				continue;
			}

			// 该MKNO是否属于所选的线路
			if($mkno_info['TranKd'] != $kind){
				$not_this_line++;
				$not_this_arr[] = $item[0];
				continue;
			}


			if($first_run != 'first'){


				//第一件事，先马上把此数据写入数据表，默认两个推送状态都为0
				$this->toData($mkno,$item[1],'','first',$kind);//美快单号,

				$check = M('StnoToEms')->where(array('MKNO'=>$mkno,'EMSNO'=>$item[1]))->find();

				// $sendERP = array('status'=>'2', 'msg'=>'已执行过推送ERP');//测试

				$sendERP = $this->toERP($mkno,$item[1],'800',$check,$force_erp,$kind);//详细说明请看下面的toERP函数
                //return $sendERP;
				// 20161115 jie
				// $sure_post是否推送给快递100，当on的时候执行推送；$force_kd100为on的时候，也需要执行推送
				if($sure_post == 'on' || $force_kd100 == 'on'){
					$sendKD100 = $this->toKD100($mkno,$item[1],$pars,$schema,$url,$check,$force_kd100,$kind,__FUNCTION__);
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


		}

		if($first_run == 'first'){
			// if($no_had_no > 0){
			// 	$no_had_str = "<br/>以下美快单号不存在(".$no_had_no.")：".implode('，',$no_had_arr).'；';
			// }
			// if($not_this_line > 0){
			// 	$not_this_str = "<br/>以下美快单号不属于所属线路(".$not_this_line.")：".implode('，',$not_this_arr);
			// }
			return $backArr = array('status'=>'0', 'opt'=>'submit_again', 'msg'=>'请确认以下信息后再次点击【提交】按钮。<br/>总数量：'.$sti.'。'."<br/>以下美快单号不存在(".$no_had_no.")：".implode('，',$no_had_arr).'；'."<br/>以下美快单号不属于所属线路(".$not_this_line.")：".implode('，',$not_this_arr));
		}

		// echo 'Done '.date('Y-m-d H:i:s').'， 成功：'.$i."(总：".$sti.")";
		// 总结语句
		return $backArr = array('status'=>'1', 'opt'=>'first', 'msg'=>'操作成功，总数量：'.$sti.'。成功推送到KD100：'.$i."(已执行过推送：".$lo_num.")；成功推送到ERP：".$erp_num."(已执行过推送：".$pu_num.")");
	}

	/**
	 * 推送到KD100
	 * @param  [type] $mkno      [MKNO]
	 * @param  [type] $EMSNO     [默认为 EMS单号。以后可以根据情况改变为其他物流]
	 * @param  [type] $pars      [组建参数之一]
	 * @param  [type] $schema     [待发送参数之一]
	 * @param  [type] $url       [curl post用的地址]
	 * @param  [type] $check     [默认为 EMS单号。以后可以根据情况改变为其他物流]
	 * @param  [type] $force_kd100     [是否强制发送到物流服务商]
	 * @param  [type] $kind      [mk_transit_center.id]
	 * @param  [type] $funcy      [是否为根据批次号执行的推送]
	 * @param  [type] $k         [重复发送次数；当此次推送返回失败的时候，允许反复发送3次，超过3次仍然为失败，则不再执行此次操作并返回“推送失败”]
	 * @return [type] [description]
	 */
	public function toKD100($mkno,$EMSNO,$pars,$schema,$url,$check,$force_kd100,$kind,$funcy,$k=0){

		//不等于on的时候表示过滤重复发送的
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
			// $res['returnCode'] = 400;
		//200为成功，重复发送的则会返回501，501也表示已经成功，保存的时候改为200后保存
		if($res['returnCode'] == '200' || $res['returnCode'] == '501'){

			//mk_send_record
			$data_Record['username'] 	= 'admin';//$username;
			$data_Record['STNO'] 		= $EMSNO;
			M('SendRecord')->add($data_Record);	// 保存操作记录
			
			if($funcy == 'post_by_noid'){
				return $this->toData($mkno,$EMSNO,200,'kd100_state',$kind);
			}else{
				$this->toData($mkno,$EMSNO,200,'kd100_state',$kind);
			}
			// return $backArr = array('status'=>'1', 'msg'=>'推送成功');

		}else{

			$k++;//重复发送次数
			
			// 当此次推送返回失败的时候，允许反复发送3次，超过3次仍然为失败，则不再执行此次操作并返回“推送失败”
			if($k <= 3){
				
				if($funcy == 'post_by_noid'){
					return $this->toKD100($mkno,$EMSNO,$pars,$schema,$url,$check,$force_kd100,$kind,$funcy,$k);
				}else{
					$this->toKD100($mkno,$EMSNO,$pars,$schema,$url,$check,$force_kd100,$kind,$funcy,$k);
				}
			}else{
				return array('status'=>'0', 'msg'=>'推送KD100失败');
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
		//return $post_data;
        //推送数据到ERP
        $result = posturl($url,$post_data);

		$res = trim(json_decode($result,true));		//返回的结果   //原来的20180526 hua

        //if($result === true){
		if($res == '200'){    //原来的20180526 hua
			// return $backArr = array('status'=>'1', 'msg'=>'操作成功');
			$this->toData($mkno,$EMSNO,200,'erp_state',$kind);
            //$a = $this->toData($mkno,$EMSNO,200,'erp_state',$kind);
            //return $a;
		}else{
            //原函数
			$k++;//重复发送次数
			 //当此次推送返回失败的时候，允许反复发送3次，超过3次仍然为失败，则不再执行此次操作并返回“推送失败”
			if($k <= 3){
			   $this->toERP($mkno,$EMSNO,$condition='800',$check,$force_erp,$kind,$k);
			}else{
				return $backArr = array('status'=>'0', 'msg'=>'推送ERP失败');
			}

		}
	}



	/**
	 * 数据保存mk_stno_to_ems(申通号转发EMS)
	 * @param  [type] $mkno      [MKNO]
	 * @param  [type] $EMSNO     [默认为 EMS单号。以后可以根据情况改变为其他物流]
	 * @param  [type] $code      [推送到ERP后返回的成功值 200]
	 * @param  [type] $at_state  [保存的字段]
	 * @param  [type] $kind      [线路ID]
	 * @param  [type] $funcy     [函数名：_index, post_by_noid]
	 * @return [type]            [description]
	 */
	public function toData($mkno,$EMSNO,$code,$at_state,$kind='',$funcy=''){

		//检查mk_stno_to_ems是否已存在推送记录
		$check = M('StnoToEms')->where(array('MKNO'=>$mkno,'EMSNO'=>$EMSNO))->find();

		// 第一件事是先把数据保存到数据表
		if($code == '' && $at_state == 'first'){

			// 推送记录已存在的则不再保存
			if($check){
				return true;
			}else{
				$data['MKNO']        = $mkno;
				$data['EMSNO']       = $EMSNO;
				$data['ctime']       = date('Y-m-d H:i:s');
				$data['erp_state']   = ($funcy == 'post_by_noid') ? 200 : 0;
				$data['kd100_state'] = 0;
				$data['tcid']        = $kind;
				$res = M('StnoToEms')->add($data);		//保存记录

				//只有当 推送记录 是新增的时候，才执行 运单号 的覆盖
				if($res){
					//EMS单号直接覆盖到tran_list.STNO中
					M('TranList')->where(array('MKNO'=>$mkno))->setField('STNO',$EMSNO);
					return true;
				}else{
					return false;
				}
			}

		}else{

			// 推送记录已存在的则不再保存
			if($check){
				$data1[$at_state] = $code;
				$res = M('StnoToEms')->where(array('id'=>$check['id']))->save($data1);		//保存记录
				$td = false;
			}else{
				$data['MKNO']    = $mkno;
				$data['EMSNO']   = $EMSNO;
				$data['ctime']   = date('Y-m-d H:i:s');
				$data[$at_state] = $code;
				$data['tcid']    = $kind;
				$res = M('StnoToEms')->add($data);		//保存记录

				$td = true;
			}

			if($res || $res == 0){

				//只有当 推送记录 是新增的时候，才执行 运单号 的覆盖
				if($td === true){
					//EMS单号直接覆盖到tran_list.STNO中
					M('TranList')->where(array('MKNO'=>$mkno))->setField('STNO',$EMSNO);
				}
				
				return $backArr = array('status'=>'1', 'msg'=>'操作成功');
			}else{
				return $backArr = array('status'=>'0', 'msg'=>'操作失败');
			}
		}

	}

    /**
     * 按批次号进行推送，推送到物流服务商
     * @param  [type] $noid        [批次号ID]
     * @param  [type] $kind        [线路ID]
     * @param  string $force_kd100 [强制推送给物流信息服务商]
     * @param  string $company     [转接快递]
     * @param  string $sure_post   [是否推送给物流信息服务商]
     * @return [type]              [description]
     */
	public function post_by_noid($noid, $kind, $force_kd100='on', $company='ems', $sure_post='on'){
		ini_set('memory_limit','4088M');
		ini_set('max_execution_time', 0);

		$list = M('TranList')->where(array('noid'=>$noid))->select();

		//检查批次号中是否有数据
		if(count($list) == 0){
			return $tips = array('status'=>'400','msg'=>"没有数据需要发送");
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
		$lo_num    = 0;//已执行过推送KD100，已推送过的就不再次推送，同样视为已推送

		foreach($list as $item){
			$pars['number'] = $item['STNO'];

			//第一件事，先马上把此数据写入数据表，默认两个推送状态都为0
			$this->toData($item['MKNO'],$item['STNO'],'','first',$kind,__FUNCTION__);//美快单号,运单号，推送到ERP后返回的成功值，保存的字段，线路ID，函数名

			$check = M('StnoToEms')->where(array('MKNO'=>$item['MKNO'],'EMSNO'=>$item['STNO']))->find();

			// $sure_post是否推送给快递100，当on的时候执行推送；$force_kd100为on的时候，也需要执行推送
			if($sure_post == 'on' || $force_kd100 == 'on'){
				$sendKD100 = $this->toKD100($item['MKNO'],$item['STNO'],$pars,$schema,$url,$check,$force_kd100,$kind,__FUNCTION__);
			}else{
				$sendKD100['status'] = '0';
			}

			// 当参数返回为0的时候，则终止当前单号的发送，执行下一个单号发送
			if($sendKD100['status'] == '0'){
				continue;

			}else if($sendKD100['status'] == '2'){

				$i++;
				$lo_num++;

			}else if($sendKD100['status'] == '1'){

				$i++;
			}


		}

		return array('status'=>'1', 'msg'=>'操作成功，总数量：'.count($list).'。成功推送到KD100：'.$i."(已执行过推送：".$lo_num.")");
	}

	//检查该批次号的节点推送状态，是否至少成功完成  第一步 的节点推送 20171106
	public function check_allow($id, $tcid){

		return M('TransitNo t')->field('n.status,n.sort')->join('left join mk_node_push_logs n on n.noid = t.id')->where(array('t.id'=>$id,'t.tcid'=>$tcid))->find();
	}
}