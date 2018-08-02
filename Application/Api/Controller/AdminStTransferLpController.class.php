<?php
/**
 * 会员管理---线路优惠
 * 使用数据表：mk_line_discount, mk_user_list, mk_manager_list, mk_transit_center
 */
namespace Api\Controller;
use Think\Controller\HproseController;
class AdminStTransferLpController extends HproseController{

	public function _index($map){
		// $tran = M('TranList')->where($map)->count();

		// $lp = M('ZtTransferLp')->where($map)->count();

		// return array('tran'=>$tran, 'lp'=>$lp);
	}

	public function _add($arr){

		$check = M('TranList')->where(array('STNO'=>$arr['STNO']))->find();
		if(!$check){
			return array('state'=>'no', 'msg'=>'该中通单号不存在');
		}

		$second = M('ZtTransferLp')->where(array('STNO'=>$arr['STNO']))->find();

		//已经有录入记录，直接略过
		if($second){
			$save = M('ZtTransferLp')->where(array('STNO'=>$arr['STNO']))->setField('LPNO', $arr['LPNO']);

			if($save === false){
				return array('state'=>'no', 'msg'=>'更新失败');
			}else if($save == 0){
				return array('state'=>'no', 'msg'=>'没有数据更新或已经被录入');
			}else{
				return array('state'=>'yes', 'msg'=>'更新成功');
			}
		}else{

			$arr['noid'] = $check['noid']; //保存批次号ID
			$save = M('ZtTransferLp')->add($arr);

			if($save !== false){
				return array('state'=>'yes', 'msg'=>"快递号：".$arr['STNO']." LP号：".$arr['STNO'].' 保存成功');
			}else{
				return array('state'=>'no', 'msg'=>'保存失败');
			}
		}

	}
}