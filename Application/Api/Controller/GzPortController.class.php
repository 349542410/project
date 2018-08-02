<?php
/*
2017-03-27 create by Man
getpayment() 获取支付推送结果的查询
$send   = new \Org\GZP\Customs();
$rs   = $send->send($data);
$data['type_operation'] = 'query'
其中$data的内容格式，主参考《美快推送支付-附件1》
index(),海关推送回执的处理
*/
namespace Api\Controller;
use Think\Controller;
class GzPortController extends Controller{
	
	protected $config = array(
		'limit' => '1',
	);

	public function getpayment(){
		$tcid = '7';
		
		$Customs = new \Org\GZP\SendCustoms();

		$config = $this->config;

		// 读取报关状态<>2的记录 且 是属于广东邮政的订单  进行查询
		$map['TranKd']          = array('eq',$tcid);
		$map['send_pay_status'] = array(array('neq',2), array('exp','is NULL'),'or');

		$list = M('TranList')->where($map)->limit($config['limit'])->select();
// dump($list);die;

		// 拼合对应的商品列表到订单信息中  wx的时候才需要此部分
    	foreach($list as $key=>$item){
    		$list[$key]['Order'] = M('TranOrder')->where(array('lid'=>$item['id']))->select();
    	}

    	$i = 0;
    	$msg = '';
    	foreach($list as $arr){

    		$res = $this->act($arr, $Customs);
    		
    		if($res['code'] == '1'){
    			$i++;
    		}else{
    			$msg .= '【'.$arr['MKNO'].'】推送失败，原因：【'.$res['err'].'】；';
    		}
    	}

        if($i == 0){
        	$backArr = array('do'=>'no', 'msg'=>'推送失败，'.$msg);

        }else if($i == count($arr)){
        	$backArr = array('do'=>'yes', 'msg'=>'推送成功');
        	
        }else{
        	$backArr = array('do'=>'yes', 'msg'=>'部分订单推送失败，'.$msg);
        	
        }

        return $backArr;

	}

	/**
	 * 查询支付状态  暂时无法测试
	 * @param [type] $arr     [订单数据  一维数组]
	 * @param [type] $Customs [查询状态的方法]
	 */
	public function act($arr, $Customs){
        if(preg_match("/^支付宝/", $arr['paykind'])){
            $type = 'ali';

        }else if(preg_match("/^微信支付/", $arr['paykind'])){
            $type = 'wx';

        }else if(preg_match("/^美快支付/", $arr['paykind'])){
        	$type = 'yl';

        }else{//默认值
        	$type= 'yl';
        }

		//存在wx微信子订单号 必填    20170330 jie 暂时是不需要用到
		$sub_order = array();
		if(in_array($type, array('wx'))){
			foreach($order as $key=>$item){
				$sub_order[$key]['sub_order_no']  = $item['auto_Indent2']; 	//子订单号
			}
		}

		$data = array(
			'type'           => $type, 	//ali,wx 必填，暂不支持yl
			'type_operation' => 'query',	//必填   固定为query
			'customs'        => '海关', //海关(详细看说明参数	customs)	wx 必填
			'order'          => array( 		//必填
				'out_trade_no'   => $arr['MKNO'], //商家订单号 必填
			),
			'sub_order'      => (in_array($type, array('wx'))) ? $sub_order : '',  //存在wx微信子订单号必填
		);

/*		
		$res = $Customs->send($data);
		*/
	
		// 模拟
		$res = array(
			'code'      => '1',
			'err'       => '当code=0时显示出错原因',//（即报关接口的'错误代码'+'错误代码描述'）
			'state'     => '2',
			'state_des' => '海关返回受理成功',
		);
		
		if($res['code'] == '1'){
			$this->index($res, $arr['MKNO'], $type);
		}
		return $res;
	}

	/**
	 * [index 支付通知 数据保存]
	 * @param  [type] $arr  [支付通知返回的数组结果]
	 * @param  [type] $MKNO [美快单号]
	 * @param  [type] $type [类型:ali,wx,yl]
	 * @return [type]       [description]
	 */
	public function index($arr, $MKNO, $type){
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