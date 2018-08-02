<?php
/**
 * 自助打印终端---终端号管理
 */
namespace AUApi\Controller;
use Think\Controller\HproseController;
class MkAutoSelfTerminalController extends HproseController{

	public function _index($r_data){

		$informal  = $r_data['informal'];//终端名称（非正式，暂时使用）
		$true_name = $r_data['true_name'];//终端真实名称（正式）
		$type      = $r_data['type'];//终端机打开的软件类型（print:打印软件，receive：揽收软件）

		//检查该终端名称是否存在
		$check = M('SelfTerminalList')->where(array('computer_name'=>$true_name,'type'=>$type))->find();

		// 该终端名称不存在，没有记录的终端，也要记录在案
		if(!$check){
			$data_s = array();
			$data_s['computer_name'] = $true_name;   //终端真实名称（正式）
			$data_s['type']          = $type;   //终端机打开的软件类型
			$data_s['terminal_name'] = create_guid();		//终端编号
			$data_s['status']        = '1';		//激活状态
			$data_s['point_id']      = '0';		//揽收点ID
			$data_s['create_time']   = date('Y-m-d H:i:s');//创建时间
			$data_s['informal_name'] = $informal;//终端名称（非正式，暂时使用）

			$res = M('SelfTerminalList')->add($data_s);

			//新增成功
			if($res){
				// 由于是新增的终端，所以激活状态为0，终端编号为空
				// return array('state'=>'yes', 'lng'=>'new_one', 'msg'=>'新入设备', 'activation'=>array('status'=>'0', 'machine_code'=>''));

				return array('state'=>'yes', 'lng'=>'new_one', 'msg'=>'新入设备', 'activation'=>array('status'=>$data_s['status'], 'machine_code'=>$data_s['terminal_name']));
			}else{
				return array('state'=>'no', 'lng'=>'failed_to_save', 'msg'=>'数据保存失败', 'activation'=>array('status'=>'400', 'machine_code'=>''));
			}
		}else{
			return array('state'=>'yes', 'lng'=>'its_data', 'msg'=>'查询成功', 'activation'=>array('status'=>$check['status'], 'machine_code'=>$check['terminal_name']));
		}
	}
}