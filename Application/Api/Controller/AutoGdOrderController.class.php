<?php
/**
	* 版本号：V1.0
	* 创建人：Jie
	* 创建日期：2017-03-20
	* 修改日期：2017-03-26
	* 用途：自动 订单报备  广东邮政   未完
	* 数据表：mk_tran_list  mk_tran_list_notes   mk_tran_order
 */
namespace Api\Controller;
use Think\Controller;
class AutoGdOrderController extends Controller{
	
	// protected $no        = 'STNO';			// STNO：香港E特快运单号; MKNO：客户订单号(即我方的美快单号)
	protected $TranKd    = 7;				// 7：表示广东邮政
	protected $limit     = 1; 				// 批量交易个数(即查询数据表的数据条数)
	protected $limitHour = 36000;			// 程序操作时限(10个小时)


	public function index(){

		require_once('Kdno7.class.php');//加载类

		$EMS = new \Kdno();

		echo "\r\n".date('Y-m-d H:i:s')."\r\n";
		
		$node = (I('node')) ? trim(I('node')) : 'Gd_ems_order';//默认 Hk_ems 香港E特快

		//主动获取顺丰的物流信息
		$time = time();	//标识码(时间戳) 访问此文件的时候马上生成

		// 查询倒序后的最新的一条数据
		$maxinfo = M('TranListNotes')->where(array('node'=>$node))->order('id desc')->find();

		$maxinfo['ctime'] = ($maxinfo['ctime'] == '') ? 0 : $maxinfo['ctime'];

		$tenHour = $time - $maxinfo['ctime'];	//当前时间的10小时前

		// 超过规定时限(10小时) 或者mk_tran_list_notes.max(id).state = 200，则会重新执行物流信息查询
		if($tenHour > $this->limitHour || $maxinfo['state'] == '200'){
			
			// 如果查询没有任何结果
			if($maxinfo === false){
				$maxlid = $maxinfo['lid'];
			}else{
				$maxlid = 0;
			}
			$ctime = $time;//超过10小时时限或者state=200，需要用 新的时间戳 标记 以示新一轮开始

		}else{
			$maxlid = $maxinfo['lid'];
		}
		/* 线程 End 20160921 */

		//查询mk_apply_list.send_report=1&pay_report=1 的 订单资料
		$map['send_report'] = array('eq',1);
		$map['pay_report']  = array('eq',1);
		$map['id']          = array('gt',$maxlid);
		$nu_list = M('TranList')->where($map)->order('id asc')->limit($this->limit)->select();


		if(count($nu_list) > 0){

			$maxId = $nu_list[count($nu_list)-1]['id'];

			$s_data = array();
			/* Jie 20160921 */
			if($maxinfo['ctime'] != 0){

				if($maxinfo['state'] == '200' || $tenHour > $this->limitHour){
					$s_data['lid']   = $maxId;
					$s_data['ctime'] = $ctime;
					$s_data['state'] = 0;
					M('TranListNotes')->where(array('id'=>$maxinfo['id']))->save($s_data);
				}else{

					M('TranListNotes')->where(array('id'=>$maxinfo['id']))->setField('lid',$maxId);
				}
			}else{
				$s_data['lid']   = $maxId;
				$s_data['ctime'] = $ctime;
				$s_data['node']  = $node;
				M('TranListNotes')->add($s_data);
			}

			// die;
			/* End 20160921 */

			$et = 0;//计算成功获取物流信息的总数
			$msg = '';
			// print_r($nu_list);die;
			// start 用于以后区分SF和其他物流公司
			foreach($nu_list as $kk=>$item){

				$tracking_number = $item['EntGoodsNo'];

				//调用文件中的函数处理
				$res[$kk] = $EMS->isGoods($item, true);
// print_r($res[$kk]);
// die;
				if($res[$kk]['result'] == '1'){
					$et++;
				}else{
					$msg .= '单号：'.$tracking_number.'，msg：'.$res[$kk]['description'].'；';
				}
				
			}
			//end

			$endtime = time();
			$Ttime = $endtime - $time;
			
			// $et  计算成功获取并保存物流信息的总数
			if($et == 0){
				$backXML = '<Response service="RoutePushService"><Head>ERR</Head><ERROR code="4001">系统发生数据错误或运行时异常；耗时：'.$Ttime.'秒；反馈信息：'.$msg.'</ERROR></Response>';
				
			}else{
				$backXML = '<Response service="RoutePushService"><Head>OK</Head>请求发送总数：'.$this->limit.'个，实际查询数据：'.count($nu_list).'个；成功保存商品报备信息：'.$et.'个；耗时：'.$Ttime.'秒；反馈信息：'.$msg.'</Response>';
			}
			echo $backXML;

		}else{//当搜索数据表已经没有得到合适数据的时候，就把最大的id的状态标记为200

			echo '运行完成';
			/* Jie 20160921 */
			M('TranListNotes')->where(array('id'=>$maxinfo['id']))->setField('state',200);
			/* End 20160921 */
		}

	}
}