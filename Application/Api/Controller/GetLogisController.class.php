<?php
/**
 * 获取物流信息 服务器端
 */
namespace Api\Controller;
use Think\Controller\HproseController;
class GetLogisController extends HproseController {

    public function _index($ids,$config){
		ini_set('memory_limit','4088M');
		ini_set('max_execution_time', 0);

    	$ids           = explode(',',$ids);
    	$scount        = count($ids);
		$map['id']     = array('in',$ids);
		$map['TranKd'] = array('eq',$config['TranKd']); //TranKd=4  顺丰单号
    	$nu_list = M('TranList')->field('id,STNO')->where($map)->select();

    	// 筛选出非顺丰单号的id
    	foreach($nu_list as $vi){
    		if(in_array($vi['id'], $ids)){
    			unset($ids[array_search($vi['id'],$ids)]);//array_search 按元素值返回键名。去除后保持索引
    		}
    	}

    	// 非顺丰单号的id集
    	$not_sf_ids = implode(',',$ids);
    	// return $not_sf_ids;
    	if(strlen($not_sf_ids) > 0){
    		$map2['id'] = array('in',$not_sf_ids);
    		$not_sf     = M('TranList')->field('MKNO')->where($map2)->select();
    		$not_sf_arr = array_column($not_sf, 'MKNO');
    		$not_sf_nums  = implode(',',$not_sf_arr);
    		// return $not_sf_nums;//返回非顺丰单号的id以便用作提示具体是哪个单号
    		return $backXML = array('do'=>'no_sf', 'msg'=>'非顺丰单号对应的美快单号：'.$not_sf_nums, 'nid'=>array_values($ids));//返回非顺丰单号的id以便用作提示具体是哪个单号

    		// 注意： 还有一个暂未处理：即 非顺丰单号的id里面还没判断当中是否有个别id是非法传入，即数据表是没有的
    	}
    	
    	// 检验是否存在
    	if(count($nu_list) == 0 || !$nu_list){
    		return $backXML = array('do'=>'no', 'msg'=>'运单号不存在');
    	}

		$et      = 0;//计算成功获取物流信息的总数
		$noexist = 0;//记录单号不存在的总数
		$msg     = '';
    	foreach($nu_list as $kk=>$item){

    		$tracking_number = $item['STNO'];

	    	$result = $this->useSoap($tracking_number,$config);

	    	$reArr = get_object_vars($result);

	     	$reArr['Return'] = str_replace("&", "&amp;", $reArr['Return']);

	    	$reArr = $this->xml_array($reArr);// 返回的XML报文转为数组   	
	    	$reArr = $reArr['Return'];

			if(isset($reArr['Head']) && $reArr['Head'] == 'OK'){

				$WaybillRoute = $reArr['Body']['RouteResponse'];
				$Route = $reArr['Body']['RouteResponse']['Route'];

				$new_a = array();

				// 只执行一条物流数据查询的时候
				if(count($Route) == 1){

					$list = $Route['@attributes'];

					$new_a[0]['BusinessLinkCode'] = $this->MKIL_State($list['opcode']);
					$new_a[0]['TrackingContent']  = "【".$list['accept_address']."】".$list['remark'];
					$new_a[0]['OccurDatetime']    = $list['accept_time'];

				}else{//以下内容为传递多条物流数据的时候
					
					$Route = array_reverse($Route);//返回翻转顺序的数组
					$list  = $Route;
					$ru    = false;//判断用
					//物流信息数组三维转二维数组
					foreach($list as $key => $row){

					    foreach($row as $key2 => $row2){

					        if(isset($row2['remark'])){

								if($row2['opcode'] == '80'){
									$new_a[$key]['BusinessLinkCode'] = $this->MKIL_State($row2['opcode']);
									$new_a[$key]['TrackingContent']  = "【".$row2['accept_address']."】".$row2['remark'];
									$new_a[$key]['OccurDatetime']    = $row2['accept_time'];

									// 额外保存本公司专有信息
									$new_a[$key+1]['BusinessLinkCode'] = '1003';
									$new_a[$key+1]['TrackingContent']  = "【".$row2['accept_address']."】已签收,感谢使用美快国际物流,期待再次为您服务";
									$new_a[$key+1]['OccurDatetime']    = date('Y-m-d H:i:s');
									$ru = true;
								}
								if($row2['opcode'] == '8000'){
									if($ru === true){
										break;
									}else{
										$new_a[$key]['BusinessLinkCode'] = '1003';
										$new_a[$key]['TrackingContent']  = "【".$row2['accept_address']."】已签收,感谢使用美快国际物流,期待再次为您服务";
										$new_a[$key]['OccurDatetime']    = $row2['accept_time'];
									}
								}else{
									$new_a[$key]['BusinessLinkCode'] = $this->MKIL_State($row2['opcode']);
									$new_a[$key]['TrackingContent']  = "【".$row2['accept_address']."】".$row2['remark'];
									$new_a[$key]['OccurDatetime']    = $row2['accept_time'];
								}

					        }

					    }
					}
					$new_a = array_reverse($new_a);//返回翻转顺序的数组
				}

				//将loginfo生成 ./Express100/server.php 可接受的格式，使用 HproseHttp进行保存
				$arr = array();
				$arr['status']                  = '';
				$arr['billstatus']              = 'check';
				$arr['message']                 = '';
				$arr['lastResult']['message']   = 'ok';
				$arr['lastResult']['nu']        = $WaybillRoute['@attributes']['mailno'];	//tran_list.STNO
				$arr['lastResult']['ischeck']   = '1';
				$arr['lastResult']['condition'] = '';
				$arr['lastResult']['com']       = '';
				$arr['lastResult']['status']    = '';
				$arr['lastResult']['state']     = '';
				$arr['lastResult']['data']      = $new_a;

			    // $res[$kk] = $this->save($arr, 'STNO', 'SF');	//直接传入数组形式的数据
				vendor('Hprose.HproseHttpClient');
        		$client = new \HproseHttpClient('http://test3.megao.hk/test/test4.php');        //读取、查询操作
				$res[$kk] = $client->save($arr,$STNO,$SF);//查询，返回的是一个结构体

			    $db[] = $res[$kk];
				if($res[$kk]['do'] == 'yes'){
					$et++;
				}else if($res[$kk]['do'] == 'noexist'){
					$noexist++;
				}else{
					$msg = 'ddddd';
					// $msg .= '单号：'.$item[$no].'，msg：'.$res[$kk]['title'].'；';
				}

			}else{
				$noexist++;
			}  	

    	}
    	return $db;
		// $et  计算成功获取并保存物流信息的总数
		if($et == 0){
			if($noexist > 0){
				return $backXML = array('do'=>'no', 'msg'=>'运单号不存在');
				// $backXML = '单号不存在';
			}else{
				return $backXML = array('do'=>'no', 'msg'=>'操作失败'.$msg);
				// $backXML = '操作失败';
			}
		}else{
			// 批量操作的回复
			if($scount > 1) {
				return $backXML = array('do'=>'yes', 'msg'=>'操作成功，请求总数：'.count($nu_list).'；成功执行：'.$et.'；运单号不存在：'.$noexist);
				// $backXML = '操作成功，请求总数：'.count($nu_list).'；成功执行：'.$et.'；单号不存在：'.$noexist;
			}else{
				return $backXML = array('do'=>'yes', 'msg'=>'操作成功');
				// $backXML = '操作成功';// 单个请求操作的回复
			}
		}

    	// return $backXML;
    }


	function save($arr, $sno='MKNO',$toor=''){// 20160818 Jie

		$lastResult = $arr['lastResult'];
		$data       = $lastResult['data'];
		$nu         = $lastResult['nu'];	//单号
		$count      = count($data); //计算总数
		$status     = $arr['status'];   //状态
		$state      = isset($lastResult['state']) ? $lastResult['state'] : 0;    //物流的派送状态

		$map[$sno] = array('eq',$nu);

		$result = $this->logistics($nu,$data,$count,$status,$state,$map,$sno,$toor);// 20160818 Jie

		return $result;

	}

	/**
	 * 通用型处理方法 20160818 Jie V1.1
	 * @param  [type] $nu     [description]
	 * @param  [type] $data   [description]
	 * @param  [type] $count  [description]
	 * @param  [type] $status [description]
	 * @param  [type] $state  [description]
	 * @param  [type] $sql    [description]
	 * @return [type]         [description]
	 */
	function logistics($nu,$data,$count,$status,$state,$map,$sno,$toor){

		$mk = M('TranList')->field('MKNO,CID')->where($map)->find();

		if(count($mk) > 0){
			$mkcid =  isset($mk['CID']) ? $mk['CID'] : 0;
		}else{
			// $mkcid = 0;
			return array('do'=>'noexist', 'title'=>'运单号不存在');
		}

		if($toor == 'SF'){
			// return $data;
			$data = $this->proof_time($mk['MKNO'],$data);
			// return $data;
			$count = count($data);
			// 当时count($data) = 0 的时候，物流信息已经全部更新完
			if(count($data) < 1){
				return $msg = array('do'=>'yes', 'title'=>'该单号的物流信息已经是最新');      //返回信息
			}
		}

		$res1   = 0;
		$res2   = 0;	//测试用，数据比较
		$Model = M();   //实例化
        $Model->startTrans();//开启事务

		// foreach($data as $key=>$item){
		for($key=$count-1;$key>-1; $key--){
			$item = $data[$key];

			// MKIL   先查询 mk_il_logs 是否已经存在某条数据
			$check_mkil[$key] = M('IlLogs')->where(array('MKNO'=>$mk['MKNO'],'content'=>$item['TrackingContent'],'create_time'=>$item['OccurDatetime'],'status'=>$item['BusinessLinkCode']))->find();

			//如果不存在此条信息，则保存
			if(count($check_mkil[$key]) == 0){
				// 将相关资料直接将记录增加到mk_il_log中,150911添加CID
				if($toor == 'SF'){

					// 20161011 //此处只用于针对顺丰，由于在原顺丰物流信息最好添加了一条我方公司的物流信息，所以需要处理
					$check_mk = M('IlLogs')->where(array('MKNO'=>$mk['MKNO'],'content'=>$item['TrackingContent'],'status'=>$item['BusinessLinkCode']))->find();

					// 20161011
					if(count($check_mk) == 0){
						// 20160923
						$sql_mkil['MKNO']        = $mk['MKNO'];
						$sql_mkil['content']     = $item['TrackingContent'];
						$sql_mkil['create_time'] = $item['OccurDatetime'];
						$sql_mkil['status']      = $item['BusinessLinkCode'];
						$sql_mkil['CID']         = $mkcid;
						$sql_mkil['rount_time']  = $item['SFTime'];

					}else{
						$sql_mkil = array();// 20161011
					}

				}else{
					// $sql_mkil = "INSERT INTO mk_il_logs (MKNO,content,create_time,status,CID) VALUES ('$mk[MKNO]', '$item[TrackingContent]', '$item[OccurDatetime]',$item[BusinessLinkCode],$mkcid)";
				}

				if(count($sql_mkil) > 0){//20161011
					//执行保存成功则+1 20160923
					$res_sa = M('IlLogs')->add($sql_mkil);
					if($res_sa !== false){
						$res1++;
					}
				}else{//20161011 Jie
					$res1++;//此处只用于针对顺丰，由于在原顺丰物流信息最好添加了一条我方公司的物流信息，所以需要处理
				}

				$res2++;
				
			}else{	//已经存在也+1
				$res1++;
			}

		}

		if(count($data) == $res1){

			$s_data['IL_state']   = $data['0']['BusinessLinkCode'];
			$s_data['ex_time']    = $data['0']['OccurDatetime'];
			$s_data['ex_context'] = $data['0']['TrackingContent'];

			$tlsql = M('TranList')->where(array('STNO'=>$nu))->save($s_data);

			if($tlsql !== false){
				$Model->commit();//提交事务成功
				return $msg = array('do'=>'yes', 'title'=>'操作成功','n1'=>$res1,'n2'=>$res2);     //返回信息
			}else{
				$Model->rollback();//事务有错回滚
				return $msg = array('do'=>'no', 'title'=>'tranlist数据更新操作失败，事务回滚','n1'=>$res1,'n2'=>$res2);      //返回信息
			}
			
		}else{
			$Model->rollback();//事务有错回滚
			return $msg = array('do'=>'no', 'title'=>'物流信息保存操作失败，事务回滚','n1'=>$res1,'n2'=>$res2);      //返回信息
		}
	}

	// 顺丰专用 物流时间统一转为中国时间  20161010 Jie
	function proof_time($MKNO,$arr){
		$arr  = array_reverse($arr);//返回翻转顺序的数组

		$info = M('IlLogs')->field('status,create_time,rount_time')->where(array('MKNO'=>$MKNO))->order('id DESC')->find();

		foreach($arr as $key=>$item){

			if(count($info) > 0){

				if($info['status'] >= 20){

					$info['rount_time'] = ($info['rount_time'] == '') ? $info['create_time'] : $info['rount_time'];

					// 顺丰的物流信息时间要+16h
					$add_time = intval(strtotime($item['OccurDatetime']))+57600;//+16h
					//判断+16h后是否会大于服务器当前时间
					$now_time = time();

					$add_time = ($add_time <= $now_time) ? date('Y-m-d H:i:s',$add_time) : date('Y-m-d H:i:s',$now_time);
					$arr[$key]['SFTime'] = $item['OccurDatetime'];

					// 数据表最新物流的原始物流时间(rount_time) >= 顺丰物流时间
					if(strtotime($info['rount_time']) >= strtotime($item['OccurDatetime'])){
						// $arr[$key]['SFTime'] = $item['OccurDatetime'];

						if($info['status'] < 1000){
							$arr[$key]['OccurDatetime'] = $add_time;
							$info['rount_time']  = $item['OccurDatetime'];
							$info['create_time'] = $add_time;
						}else{
							unset($arr[$key]);
						}

					}else{// 顺丰物流时间 > 数据表最新物流的原始物流时间(rount_time) (条件1)

						// 满足 条件1 的前提下，顺丰物流时间 <= 数据表最新物流的物流创建时间(create_time)
						if(strtotime($info['create_time']) > strtotime($item['OccurDatetime'])){

							$arr[$key]['OccurDatetime'] = $add_time;

							// 替换最新的物流信息
							$info['rount_time']  = $item['OccurDatetime'];
							$info['create_time'] = $add_time;

						}else{// 满足 条件1 的前提下，顺丰物流时间 > 数据表最新物流的物流创建时间(create_time)

							// 替换最新的物流信息 // 顺丰的物流信息时间不需要+16h
							$info['create_time'] = $item['OccurDatetime'];
							$info['rount_time']  = $item['OccurDatetime'];
						}
						// $arr[$key]['SFTime'] = $item['OccurDatetime'];
					}
					$info['status'] = $item['BusinessLinkCode'];
				}

			}else{
				// $info['create_time'] = $item['OccurDatetime'];
			}

		}

		$arr = array_reverse($arr);//返回翻转顺序的数组
		return $arr;
	}

//=========================== 公用函数 ========================================

	/**
	 * 用soap 发送请求
	 * @param  [type] $data        [base64加密之后的报文]
	 * @param  [type] $validateStr [校验码]
	 * @return [type]              [description]
	 */
	public function useSoap($tracking_number,$config){
		set_time_limit(0);

		$cXml = $this->createXML($config['customerCode'], $config['tracking_type'], $tracking_number, $config['lang']);

		$data         = base64_encode($cXml);//xml报文加密
		$validateStr  = base64_encode(md5(utf8_encode($cXml).$config['checkword'], false));
		$customerCode = $config['customerCode'];//客户编码

		$soap = new \SoapClient($config['pmsLoginAction']);//网络服务请求地址

		$result = $soap->sfexpressService(array('data'=>$data, 'validateStr'=>$validateStr, 'customerCode'=>$customerCode));//查询，返回的是一个结构体

		return $result;
	}

	// 创建xml报文
	/**
	 * [createXML description]
	 * @param  [type] $customerCode    [客户编码]
	 * @param  string $tracking_type   [1.根据顺丰运单号查询; 2.根据客户订单号查询; 3.在IBS查询，不区分运单号和订单号]
	 * @param  [type] $tracking_number [查询号]
	 * @param  string $lang            [语言]
	 * @return [type]                  [description]
	 */
	protected function createXML($customerCode, $tracking_type='3', $tracking_number, $lang='zh-CN'){

		$xml = '<?xml version="1.0"?>
				<Request service="RouteService" lang="'.$lang.'">
					<Head>'.$customerCode.'</Head>
					<Body>
						<Route tracking_type="'.$tracking_type.'" tracking_number="'.$tracking_number.'"/>
					</Body>
				</Request>';

		return $xml;
	}

	//对象转数组
	protected function xml_array($array) {
	    if(is_object($array)) {
	        $array = (array)$array;
	    } if(is_array($array)) {
	        foreach($array as $key=>$value) {
	            $array[$key] = $this->object_array($value);
	        }
	    }
	    return $array;
	}

	//xml转数组
	protected function object_array($str) {
	    return json_decode(json_encode((array) simplexml_load_string($str)),true);
	}

	//将BusinessLinkCode转换为我们(美快物流)的物流状态	
	protected function MKIL_State($str){

		if($str == '44'){

			$IL_state = 1005;	//快递派件

		}else if($str == '130' || $str == '607'  || $str == '80'){

			$IL_state = 1003;	//快递签收

		}else if($str == '50' || $str == '51'){

			$IL_state = 1001;	//快递揽件

		}else if($str == '70' || $str == '611'){

			$IL_state = 1002;	//快递疑难

		}else if(in_array($str, array('33','612','613'))){

			$IL_state = 1012;	//延迟

		}else{

			$IL_state = 1000;	//在途

		}

		return $IL_state;
	}
}