<?php

class save{

	/**
	 * EMS商品报备(广东邮政商品报备) 资料保存
	 * 注意：只有先进行海关商品报备之后，才能进行EMS商品报备
	 * @param  [type] $id           [CID]
	 * @param  [type] $data  		[数据数组]
	 * @param  [type] $outside 		[区分ERP和美快后台]
	 * @return [type]                [description]
	 */
	public function index($id, $data, $outside=false){

		// ERP
		if($outside === false){

			// ERP访问的话，则需要在此处验证该商品报备是否已经执行了海关报备
			$check = M('ApplyList')->where(array('EntGoodsNo'=>$id))->find();

			// 验证该商品的海关报备信息是否存在
	        if(!$check){
	            return array('IsSuccess'=>'2', 'Message'=>'【'.$id.'】请先执行海关报备再操作！');
	        }

	        // 验证该商品的海关报备 状态 是否 通过
	        if($check['apply_status'] == '0'){
	            return array('IsSuccess'=>'3', 'Message'=>'【'.$check['EntGoodsNo'].'】海关报备不通过，不允许EMS报备！');
	        }
		}

        $item = array();
		// 报备商品加入以下字段的必要数据
		$item['ems_status'] = ($data['IsSuccess'] == true) ? '1' : '0';	//报备状态
		$item['ems_notes']  = $data['Message'];	//报备状态描述
		$item['ems_time']   = date('Y-m-d H:i:s');	//报备结果返回的时候，记录时间
// return $id;
		
		$save = M('ApplyList')->where(array('id'=>$id))->save($item);

		// 保存或更新成功，则+1
		if($save !== false){
			return true;
		}else{
			return false;
		}

	}

	/**
	 * [save_pay 支付通知 数据保存]
	 * @param  [type] $arr  [支付通知返回的数组结果]
	 * @param  [type] $MKNO [美快单号]
	 * @param  [type] $type [类型:ali,wx,yl]
	 * @return [type]       [description]
	 */
	public function save_pay($arr, $MKNO, $type){

		// 当$data['type']=yl时,则报关状态为2
		if($type == 'yl'){
			$state = '2';
		}else{
			$state = $arr['state'];
		}

		// 更新mk_tran_list的 支付通知状态
		$res = M('TranList')->where(array('MKNO'=>$MKNO))->setField('send_pay_status',$state);

	}
}