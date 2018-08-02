<?php
/**
 * 物流接收海关返回回执
 使用 
 index() .get 将能得到以下数组:
 [MESSAGE_TYPE] => SKU_INFO //这个不同，处理方法不同
 [WORK_NO] => MG000002   //这个就是发送报备的sku
 [OP_DATE] => 2016-05-19T11:29:44 
 [SUCCESS] => 0  // 1 为成功报备，0 为失败
 [MEMO] => 商品货号为人工待审批状态，不可以再次申报
 160615与ERP联调sku_info成功
 */
namespace Api\Controller;
use Think\Controller\RestController;
class CustomsCallbackController extends RestController{

	function _initialize(){
	}

	public function index(){
		$jn 	= new \Org\MK\Customs;
		$js 	= $jn->get();
		
		/* $js = array(
		 	'MESSAGE_TYPE' => 'SKU_INFO',
		 	'WORK_NO' => 'MK00000321646545884',
		 	'OP_DATE' => '2016-05-19T11:29:44',
		 	'SUCCESS' => '1',
		 	'MEMO' => '反馈的文字说明',
		 );*/

		if(!is_array($js) || !array_key_exists('MESSAGE_TYPE',$js)){
			//保存到mk_cumstom_error_logs中,暂无需处理
			die();
		}
		$func 	= $js['MESSAGE_TYPE'];
		$func 	= strtolower($func);
		call_user_func_array(array($this,$func), array($js));	//根据传入的MESSAGE_TYPE的值去调用相应的函数
	}

	private function callback($url,$data){
		$jn 	= new \Org\MK\Customs;
		$res 	= $jn->putdata($url,$data);

		//$res = array('code'=>'0', 'success'=>'0');
		//$res 格式 为 array("code"=>1,'success'=>1),如CustomsReady一样
		////当code!=1 && success!=1时保存到到mk_put_data表中
		//var_dump($res);

		if($res['code'] != 1 && $res['success'] != 1){
			
			$savedata['datastr'] 	= $data;		// 数据内容
			$savedata['puturl']  	= $url;			// put的网址
			$savedata['errorstr']  	= $res['success'] ;	// 对方返回的错误信息 Man1606150
			//dump($savedata);
			$unionPutData = M('UnionPutData')->add($savedata);//保存到到mk_union_put_data表中
			
			if($unionPutData){
				return true;
			}else{
				return false;
			}
		}
		return true;
	}

	//处理商品报备回执
	private function sku_info($js){
		$Model = M();   //实例化
		$Model->startTrans();//开启事务

		$sku 		= $js['WORK_NO'];	//这个就是发送报备的sku
		$status 	= $js['SUCCESS'];	// 1 为成功报备，0 为失败
		$statusstr 	= $js['MEMO'];		//商品货号为人工待审批状态，不可以再次申报

		$goods = M('Goods')->where(array('sku'=>$sku))->find();

		//保存到goods_logs中
		$data['goods_id']  = $goods['id'];
		$data['status']    = $status;
		$data['statusstr'] = $statusstr;
		$data['remarks']   = '';
		$logs_add = M('GoodsLogs')->add($data);

		if($logs_add){
			//如果goods.status=1的不用更改，否则更改goods.status,statusstr
			if($goods['status'] != '1'){
				$data2['status']    = $status;
				$data2['statusstr'] = $statusstr;

				$goods_save = M('Goods')->where(array('id'=>$goods['id']))->save($data2);

				if($goods_save){
					//$url从mk_union_url中读取相对应uid的CUSTOMS的URL存入本函数
					$url = M('Union u')->join('left join mk_union_url l on l.uid = u.id')->where(array('u.id'=>$goods['cid']))->getField('l.urlstr');

					//处理原SKU的格式，去除前两个字符，保留长度为18位
					$no = substr($sku, 2);
					
					//LOG = {'kd':'SKU_INFO','no':'原SKU','code':'','codestr':''}
					$logs 			= array();
					//使用二维数组返回 Man160615
					$logs['kd']       	= 'SKU_INFO';
					$logs['no']       	= $no;
					$logs['code']     	= $status;
					$logs['codestr']  	= $statusstr;

					//$data使用 new \Org\MK\JSON; 生成，并base64加密
					$jn = new \Org\MK\JSON;
					$data = $jn->respons('toCST', $goods['cid'], array($logs));
					// dump(json_decode($data,true));
					// die;
					$Model->commit();//提交事务成功
					$this->callback($url,$data);

				}else{
					echo '资料保存或更新失败，操作驳回';
					$Model->rollback();//事务有错回滚
				}

			}else{
				echo '该资料的状态不需要处理';
				exit;
			}

		}else{
			echo '资料保存失败，请检查';
			exit;
		}

	}

	//订单回执
	private function order_info($js)
	{
		$js['WORK_NO'];//为订单单号
	}

	//支付单回执
	private function payment_info($js)
	{
		$js['WORK_NO'];//为支付单单号
	}

	//清单
	private function list_info($js)
	{
		$js['WORK_NO'];//为订单单号
	}
	//将发送不成功的，发送三次，二次之间需间隔5分钟
	public function callbackagent()
	{
		# code...
	}

}