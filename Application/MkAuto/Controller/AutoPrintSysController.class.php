<?php
/**
 * 自助打印终端---提供会员在ERP软件端（无需会员登录）
 * 包含：操作订单打印流程、获取线路价格配置
 */
namespace MkAuto\Controller;
use Think\Controller;
class AutoPrintSysController extends AutoSysBaseController{

	public function __construct(){
		parent::__construct();

		$this->client = new \HproseHttpClient(C('RAPIURL').'/AutoPrintSys');
		$this->client -> setTimeout(1200000);//设置 HproseHttpClient 超时时间
	}

	public function console(){
		if(!method_exists($this, $this->type)){
			$result = array('state'=>'no','msg'=>$this->type.'函数不存在', 'lng'=>'function_not_exist');
			$this->Language->get_lang($result);exit;
		}

		$backArr = call_user_func_array(array($this, $this->type), array($this->data));

		$this->Language->get_lang($backArr);
	}

	//获取打印资料
	public function getInfo($info){

		$no = ($info['no']) ? trim($info['no']) : '';

		if($no == ''){
			return array('state'=>'no','msg'=>'缺少必要参数', 'lng'=>'lack_paramer');
		}

		$client = $this->client;

		$info = $client->_info($no, C('RMB_Free_Duty'), C('US_TO_RMB_RATE'));

		return $info;
	}

	//页面返回相关资料（含称重重量）
	public function step_one($info){

		$id          = $info['id'];		//订单ID
		$weight      = $info['weight'];	//称重重量
		$time        = date("Y-m-d H:i:s");//$info['time'];	//称重时间
		$operator_id = $info['uid'];// 操作人id
		$terminalCode = $info['terminal_code'];// 终端编号
		if(empty($id) || empty($weight) || empty($time) || empty($terminalCode)){
			return array('state'=>'no','msg'=>'缺少必要参数', 'lng'=>'lack_paramer');
		}

		$client = $this->client;

		return $client->_step_one($id, $weight, $time, $operator_id, C('RMB_Free_Duty'), C('US_TO_RMB_RATE'), $terminalCode);

	}

	//接收 扣费 指令，执行订单的支付并扣费
	public function step_two($info){
		$id            = $info['id'];//订单ID
		$operator_id   = $info['uid'];// 操作人id
		$terminal_code = $info['terminal_code'];// 终端编号  20171030 jie

		if($id == ''){
			return array('state'=>'no','msg'=>'缺少必要参数', 'lng'=>'lack_paramer');
		}

		$client = $this->client;

		$res = $client->_step_two($id, $operator_id, $terminal_code);

		if($res['state'] == 'yes'){

			$data = $res['redata'];//支付单号
			$data = array('id'=>$id, 'operator_id' => $operator_id);
			$data['payno']   = $res['redata']['payno'];//支付单号
			$data['paytime'] = $res['redata']['paytime'];//支付时间
			$data['paykind'] = $res['redata']['paykind'];//支付方式
			$data['balance'] = $res['redata']['balance'];//支付方式

			//扣费成功后，返回
			return array('state'=>'yes', 'rdata'=>$data, 'msg'=>'支付成功', 'lng'=>'pay_success');
		}else{
			return $res;
		}
	}

	//打印成功后，保存打印状态
	public function step_three($info){

		$id            = $info['id'];		//订单ID
		$status        = $info['status'];	//打印状态
		$time          = $info['time'];	//打印时间
		$MKNO          = $info['MKNO'];	//MKNO
		$STNO          = $info['STNO'];	//STNO
		$terminal_code = $info['terminal_code'];// 终端编号  20171030 jie
		$operator_id   = $info['uid'];// 操作人id

		if($id == '' || $status == '' || $time == '' || $MKNO == '' || $STNO == ''){
			return array('state'=>'no','msg'=>'缺少必要参数', 'lng'=>'lack_paramer');
		}

		$client = $this->client;

		return $client->_step_three($id, $status, $time, $MKNO, $STNO, $terminal_code, $operator_id);
	}

//==============================

	// 获取某条（或全部）线路的价格配置信息
	public function get_lines_configure($info){

		//线路id
		$line_id = (isset($info['line_id']) && trim($info['line_id']) != '') ? trim($info['line_id']) : '';

		// 验证是否为数值
		if(!is_numeric($line_id)){
			return array('state'=>'no','msg'=>'缺少必要参数', 'lng'=>'lack_paramer');
		}

		// 如果为0，直接当空白处理
		$line_id = ($line_id == '0') ? '' : $line_id;
		
		$client = $this->client;
		$res = $client->_get_lines_configure($line_id);

		if($res !== false){
			//扣费成功后，返回
			return array('state'=>'yes', 'Web_Config'=>$res, 'msg'=>'成功获取线路配置', 'lng'=>'get_lines_configure_success');
		}else{
			return array('state'=>'no', 'msg'=>'获取线路配置失败', 'lng'=>'get_lines_configure_falied');
		}
	}
}