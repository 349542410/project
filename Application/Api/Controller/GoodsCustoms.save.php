<?php

class save{

	/**
	 * 海关商品报备  资料保存
	 * @param  [type] $CID           [CID]
	 * @param  [type] $GoodsRegList  [数据数组  二位数组]
	 * @param  [type] $MessageID_2nd [美快系统申报商品时，系统中生成的]
	 * @param  [type] $ems           [商品报备后返回的数组结果]
	 * @return [type]                [description]
	 */
	public function index($CID='', $GoodsRegList='', $MessageID_2nd='', $ems=''){
        $data = $GoodsRegList[0];
        unset($data['Seq']);

    	// $ems为空的时候，表示报备之前，先执行数据的保存
    	if($ems == 'sys'){// 先把数据保存到美快数据库中

			// 报备商品加入以下字段的必要数据
			$data['CID']           = $CID;	//报文唯一编号
			$data['MessageID_2nd'] = $MessageID_2nd;	//报文编号 美快系统生成的

			$check = M('ApplyList')->where(array('EntGoodsNo'=>$data['EntGoodsNo']))->find(); //检查报备订单是否已经存在

			// 如果美快系统数据库已有此商品报备记录，且报备结果是成功
			if($check['apply_status'] == '1'){
				return 'exist&success';
			}

			//已存在，则更新已有的数据
			if($check){
			
				$save = M('ApplyList')->where(array('id'=>$check['id']))->save($data);
			
			}else{//否则插入新数据
				$save = M('ApplyList')->add($data);
			}


    	}else{//否则表示更新：把报备结果状态  更新  到美快数据库中

			$item['MessageID_1st'] = $ems['MessageID'];	//报文唯一编号 发送后返回的
			$item['SendTime']      = $ems['SendTime'];	//报文生成的系统时间
			$item['description']   = $ems['Data']['description'];	//报备状态描述
			$item['apply_status']  = ($ems['Data']['result'] === true) ? '1' : '0';	//报备状态

			$save = M('ApplyList')->where(array('EntGoodsNo'=>$data['EntGoodsNo']))->save($item);
    	}

		//判断成功保存或更新
		if($save !== false){
			return true;
		}else{
			return false;
		}
	}

	// 订单报备  订单报备状态保存
	public function order_save($MKNO, $arr, $list){
		// return true;
		$state = ($arr['Data']['result'] === true) ? '1' : '0';
		$res = M('TranList')->where(array('MKNO'=>$MKNO))->setField('custom_status',$state);

		$check_trainer = M('Trainer')->where(array('LogisticsNo'=>$list['STNO']))->find();

		$reqtime = date('Y-m-d H:i:s', strtotime($arr['SendTime']));

		$data = array();
		if(!$check_trainer){
			$data['LogisticsNo'] = $list['STNO'];
			$data['Status']      = $state;
			$data['Result']      = ($state == '1') ? '推送成功' : $arr['Data']['description'];
			$data['CreateTime']  = $reqtime;
			$data['TranKd']      = $list['TranKd'];
			M('Trainer')->add($data);

		}else{
			$data['Status']      = $state;
			$data['Result']      = ($state == '1') ? '推送成功' : $arr['Data']['description'];
			$data['CreateTime']  = $reqtime;
			M('Trainer')->where(array('id'=>$check_trainer['id']))->save($data);
		}

		$log = array();
		$log['LogisticsNo'] = $list['STNO'];
		$log['Status']      = $state;
		$log['content']     = ($state == '1') ? '推送成功' : $arr['Data']['description'];
		$log['CreateTime']  = $reqtime;
		M('TrainerLogs')->add($log);

	}

}