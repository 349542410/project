<?php
/**
 * 获取物流信息 客户端
 */
namespace Admin\Controller;
use Think\Controller;
class GetLogisController extends AdminbaseController {

    function _initialize() {
        parent::_initialize();
    }

    /**
     * 手动获取物流信息(单个或多个)
     * 注意：由快件管理--对数进入的查询，暂不提供此操作
     * @return [type] [description]
     */
    public function index(){
    	if(!IS_AJAX){
    		echo '非法访问';die;
    	}

		ini_set('memory_limit','4088M');
		ini_set('max_execution_time', 0);

		$nos    = trim(I('num'));
		$tcid   = trim(I('tcid'));
		$nos    = explode(',',$nos);
		$scount = count($nos);

    	// 20170119 jie 根据线路id判断需要访问的物流网址
		$line_arr = C('Get_Logis');
		$url      = $line_arr['url'];
		$ids_arr  = $line_arr['ids'];

		if(!isset($ids_arr[$tcid])){
    		$this->ajaxReturn(array('do'=>'no', 'msg'=>'该线路的访问尚未开通'));
		}
		
    	$url = sprintf($url, $ids_arr[$tcid]);
    	// 20170119 End

		$et  = 0;// 计算成功获取物流信息的总数
		$msg = '';// 用于记录 返回的失败的单号的各自的单号及其对应的错误信息

    	foreach($nos as $k=>$item){
    		// dump($item);die;
    		$post_data = array('no'=>$item);//组成数组发送数据
    		// $post_data ='no=' . $nos;//组成数组发送数据

	    	// 调用 自定义的curl方法进行发送
            $HTTP = new \Org\MK\HTTP();
            $result = $HTTP->post($url, $post_data);
            // 截取返回的json 从 { 到 } 之间的数据(截取的时候需包括 {} 这两个符号)
            //$result = substr($result, strpos($result,'{'), strpos($result,'}')+1);
            $res[$k] = json_decode($result,true);
            // dump($res[$k]);die;
			if($res[$k]['do'] == 'yes'){
				$et++;
			}else{
				$msg .= '单号：'.$item.'(ERROR：'.$res[$k]['title'].') ；';
			}
			// dump($res);die;
    	}

    	// dump($et);die;
		// $et  计算成功获取并保存物流信息的总数
		if($et == 0){
			$backXML = array('do'=>'no', 'msg'=>'操作失败 ；'.$msg);
		}else{
			// 批量操作的回复
			if($scount > 1) {
				$backXML = array('do'=>'yes', 'msg'=>'操作成功，请求总数：'.$scount.'个 ；成功执行：'.$et.'个 。操作失败：'.($scount-$et).'个 【'.$msg.'】');
			}else{
				$backXML = array('do'=>'yes', 'msg'=>'操作成功');
			}
		}

		// dump($backXML);die;
		$this->ajaxReturn($backXML);
    }





//===================== 下面的都不要的了 ===========================
    public function Oldindex(){
    	if(!IS_AJAX){
    		echo '非法访问';die;
    	}

		ini_set('memory_limit','4088M');
		ini_set('max_execution_time', 0);

		$ids    = trim(I('post.id'));
		$config = C('GetLogis_Config');//加载默认配置

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
    		$backXML = array('do'=>'no_sf', 'msg'=>'非顺丰单号对应的美快单号：'.$not_sf_nums, 'nid'=>array_values($ids));//返回非顺丰单号的id以便用作提示具体是哪个单号
    		$this->ajaxReturn($backXML);

    		// 注意： 还有一个暂未处理：即 非顺丰单号的id里面还没判断当中是否有个别id是非法传入，即数据表是没有的
    	}
    	
    	// 检验是否存在
    	if(count($nu_list) == 0 || !$nu_list){
    		$backXML = array('do'=>'no', 'msg'=>'运单号不存在');
    		$this->ajaxReturn($backXML);
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

				$client = $this->client;
				$res[$kk] = $client->save($arr, 'STNO', 'SF');//查询，返回的是一个结构体

				if($res[$kk]['do'] == 'yes'){
					$et++;
				}else if($res[$kk]['do'] == 'noexist'){
					$noexist++;
				}else{
					$msg .= '单号：'.$item[$no].'，msg：'.$res[$kk]['title'].'；';
				}

			}else{
				$noexist++;
			}  	

    	}

		// $et  计算成功获取并保存物流信息的总数
		if($et == 0){
			if($noexist > 0){
				$backXML = array('do'=>'no', 'msg'=>'运单号不存在');
			}else{
				$backXML = array('do'=>'no', 'msg'=>'操作失败'.$msg);
			}
		}else{
			// 批量操作的回复
			if($scount > 1) {
				$backXML = array('do'=>'yes', 'msg'=>'操作成功，请求总数：'.count($nu_list).'；成功执行：'.$et.'；运单号不存在：'.$noexist);
			}else{
				$backXML = array('do'=>'yes', 'msg'=>'操作成功');
			}
		}

    	$this->ajaxReturn($backXML);
    }
}