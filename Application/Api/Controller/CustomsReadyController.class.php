<?php
/**
 * 物流接收外来商品海关备案
 *收到的array的JSON原形
 {
  "KD":"toCST", 
  "CID":"1",//为mk_union中的ID,美快为1;
  "STM":"20141010131010",发送时间
  "LAN":"zh-cn|zh-tw|en", //操作语言，方便返回提示时显示
  "toCST":       //每次数量不多于20条,最小为1条
  [
	{
		'sku':'商品SKU', //最多20位，
		'hscode':'海关代码',//一定为10位数
		'goods_name':'商品名称',
		'goods_spec':'商品说明',
		'declare_unit':'主单位代码',
		'legal_unit':'法定单位代码', 
		'conv_legal_unit_num':'1', //法定计量单位折算
		'in_area_unit':'入区计量单位'
		'conv_in_area_unit_num':'', //入区计量单位折算
		'is_experiment_goods':'1',//试点 1是 0否
	}
  ]
}
========
  流程：
========
1.保存到 mk_goods中,如果sku已经存在，则无需保存，只需将状态status,statusstr回传即可
mk_goods:
	id,cid,sku(str20 *),goods_name(str200*),goods_spec(text),declare_unit(var5*),legal_unit(var5 *),conv_legal_unit_num(int3 def 1),hs_code(str10 *),in_area_unit(str5 *),conv_in_area_unit_num(int3 def 1),is_experiment_goods(int1 def 1),status(int3 def 9),statusstr(str 100 def NULL)
	sku需参考index()
	如果legal_unit,in_area_unit不传入请=declare_unit
2.保存到mk_goods_logs,status=9 statusstr='新增',goods状态也一样
mk_goods_logs:
	id,goods_id,stauts(int 3 def 9),statusstr,remarks(text)
	status ：9
3.管理后台“快件管理”前面加“商品报备”可查到goods内容，点击logs后，可列出logs的内容
	查询：按cid,sku,goods_name
  *===================
	MK回传
{
	"KD":"toCST",      //与传入的相同
	"CID":"",           //为发送资料方在MK中的ID，与传入相同
	"CMD5":"",          //加密的认证资料
	"Code":"1为成功，其它为错误",
	"Error":"当Code不等于1时，这里显示出错的文字说明",
	"STM":"20141010131010",   //发送当前时间
	"LOG":[{
	  "sku":"",      // 发过来的一致
	  "hscode":"",      // 发过来的一致
	  "Success":"true|false", // 回传是否保存成功
	  "LOGCODE":"1,2,3,9",
	  "LOGSTR":"",
	},
	{...}
	]
}
LOGCODE && LOGSTR
9 报备中
8 已报备
0 报备失败
3 误报 (说明,海关返回报备不成功)
1 报备成功

///////////////////20160608 Man
保存后，或已存在但 status=9的，进行以下操作
生成以下内容，需去除 <前，>后的空格与回车键
$xml = 
		<ESHOP_ENT_CODE>%SID%</ESHOP_ENT_CODE>
		<ESHOP_ENT_NAME>%SNM%</ESHOP_ENT_NAME>
		<SKU></SKU>
		<GOODS_NAME></GOODS_NAME>
		<GOODS_SPEC></GOODS_SPEC>
		<DECLARE_UNIT></DECLARE_UNIT>
		<LEGAL_UNIT></LEGAL_UNIT>
		<CONV_LEGAL_UNIT_NUM></CONV_LEGAL_UNIT_NUM>
		<HS_CODE></HS_CODE>
		<IN_AREA_UNIT></IN_AREA_UNIT>
		<CONV_IN_AREA_UNIT_NUM></CONV_IN_AREA_UNIT_NUM>
		<IS_EXPERIMENT_GOODS></IS_EXPERIMENT_GOODS>
		<IS_CNCA_POR_DOC>0</IS_CNCA_POR_DOC>
		<IS_ORIGIN_PLACE_CERT>0</IS_ORIGIN_PLACE_CERT>
		<IS_TEST_REPORT>0</IS_TEST_REPORT>
		<IS_LEGAL_TICKET>0</IS_LEGAL_TICKET>
		<IS_MARK_EXCHANGE>0</IS_MARK_EXCHANGE>
	   -----
	   生成一次 $cust = new \Org\MK\Customs();
		$cust->MessageType = 'SKU_INFO';
		$cust->MessageId = logs.id;
		$cust->MessageTime = logs中的日期单，使用date("Y-m-d\TH:i:s")格式;
		$cust->MessageBody = $xml(上面生成的xml);
		$res = $com->post();
			$res 格式 为 array("code"=>1,'success'=>1)
			如果code=0 表示网络问题，success返回的是网络错误的代码
			如果code=1 表示已经将资料发送，如果success=1显示对方成功接收，=0显示对方未成功接收，可能xml有问题
			当code=1 && success=1时，将该goods.status=8,goods.statusstr='发送成功',保存到logs中，返回时也是返回这个状态
			code=1 && success=0 返回status=3 str=资料可能有误，请检查
			code=0 按goods状态文字返回,logs增加 status=3 str网络错误+success内容
		

 */
namespace Api\Controller;
use Think\Controller\RestController;
class CustomsReadyController extends RestController{
	function _initialize(){

	}

	public function index(){

		$jn 	= new \Org\MK\JSON;
		$arr 	= $jn->get();
		// dump($js);
/*
// $Gjson = '{
//     "KD": "toCST",
//     "CID": "1",
//     "STM": "20141010131010",
//     "LAN": "zh-cn",
//     "toCST": [
//         {
//             "sku": "56354536535454",
//             "hscode": "2013562014",
//             "goods_name": "衣服",
//             "goods_spec": "美国生产的衣服",
//             "declare_unit": "dolls",
//             "legal_unit": "dolls",
//             "conv_legal_unit_num": "1",
//             "in_area_unit": "RMB",
//             "conv_in_area_unit_num": "1",
//             "is_experiment_goods": "1"
//         },
//         {
//             "sku": "2365654654515",
//             "hscode": "2013524689",
//             "goods_name": "裤子",
//             "goods_spec": "美国生产的裤子",
//             "declare_unit": "dolls",
//             "legal_unit": "dolls",
//             "conv_legal_unit_num": "1",
//             "in_area_unit": "RMB",
//             "conv_in_area_unit_num": "1",
//             "is_experiment_goods": "1"
//         }
//     ]
// }';

		//dump($Gjson);
		$arr = json_decode($Gjson,true);
*/
		// dump($arr);
		//js为一个数组，参考收到JSON格式
		//$prefix=通过CID 读取unit_key 取hscodeprefix值
		$prefix = M('UnionKey')->where(array('uid'=>$arr['CID']))->getField('hscodeprefix');
		
		// 若查询没有结果
		if(!$prefix){

			$ja = $jn->respons($arr['KD'],$arr['CID'],'','400','不存在此CID:01');
			//直接生成string输出 echo $ja;
			echo $ja;exit;
		}

		$list = $arr['toCST'];	//因为toCST里面的数据每次数量不多于20条,最小为1条，二维数组
		$logs = array();
		
		foreach($list as $key=>$item){
			$sku = $item['sku'];

			//验证海关代码长度是否为10
			if(strlen($item['hscode']) != 10){
				$logs[$key]['sku']     = $item['sku'];
				$logs[$key]['hscode']  = $item['hscode'];
				$logs[$key]['Success'] = 'false';
				$logs[$key]['LOGCODE'] = 3;
				$logs[$key]['LOGSTR']  = '误报！海关代码必须为10位';
				continue;
			}

			//验证接收的sku 长度最多18位
			// if(strlen($sku) <= 18){ 	//20160614 Jie 取消该验证需求
				//sku = $prefix + (传入的17位sku，不足则在前面补0)
				//str_pad()函数把字符串填充为指定的长度
				// $mk_sku = $prefix.str_pad($sku,17,0,STR_PAD_LEFT);	//20160614 Jie 取消在原有的号码前面补0的操作

				$mk_sku = $prefix.$sku;

				//再次检验合拼后的长度是否超过20
				if(strlen($mk_sku) <= 20){

					$check_sku = M('Goods')->where(array('sku'=>$mk_sku))->find();

					//若不存在此sku，则保存
					if(!$check_sku){

						$res = $this->save($arr['CID'],$mk_sku,$item);

						$logs[$key]['sku']     = $item['sku'];
						$logs[$key]['hscode']  = $item['hscode'];
						$logs[$key]['Success'] = 'true';
						$logs[$key]['LOGCODE'] = 9;
						$logs[$key]['LOGSTR']  = '新增';

						if($res !== false){	//保存成功执行，则执行以下

							$logs[$key]['Success'] = 'true';

							//保存成功，则执行一次cre_xml()
							$log_ctime = M('GoodsLogs')->where(array('id'=>$res['lid']))->getField('ctime');
							$xml_res = $this->cre_xml($res['gid'],$res['lid'],$log_ctime);

							$logs[$key]['LOGCODE'] = $xml_res['status'];
							$logs[$key]['LOGSTR']  = $xml_res['str'];

						}else{

							$logs[$key]['Success'] = 'false';

						}

					}else{	//若 已存在 此sku，则无需保存，只需将状态status,statusstr回传即可
						$logs[$key]['sku']     = $item['sku'];
						$logs[$key]['hscode']  = $item['hscode'];
						$logs[$key]['Success'] = 'true';
						$logs[$key]['LOGCODE'] = $check_sku['status'];
						$logs[$key]['LOGSTR']  = $check_sku['statusstr'];

						//已存在但status=9，则执行一次cre_xml()
						if($check_sku['status'] == '9'){
							/*获取多个id
							$logs_id = M('GoodsLogs')->where(array('goods_id'=>$check_sku['id']))->getField('id',true);
							$lids = implode(',', $logs_id);*/
							$log_info = M('GoodsLogs')->field('id,ctime')->where(array('goods_id'=>$check_sku['id']))->order('id desc')->find();

							$xml_res = $this->cre_xml($check_sku['id'],$log_info['id'],$log_info['ctime']);//goods.id, goods_logs.id, goods_logs.ctime

							$logs[$key]['LOGCODE'] = $xml_res['status'];
							$logs[$key]['LOGSTR']  = $xml_res['str'];
						}
					}

				}else{
					$logs[$key]['sku']     = $item['sku'];
					$logs[$key]['hscode']  = $item['hscode'];
					$logs[$key]['Success'] = 'false';
					$logs[$key]['LOGCODE'] = 3;
					$logs[$key]['LOGSTR']  = '误报';
				}

			// }else{
			// 	$logs[$key]['sku']     = $item['sku'];
			// 	$logs[$key]['hscode']  = $item['hscode'];
			// 	$logs[$key]['Success'] = 'false';
			// 	$logs[$key]['LOGCODE'] = 3;
			// 	$logs[$key]['LOGSTR']  = '误报';
			// }


		}

		$ja = $jn->respons($arr['KD'],$arr['CID'],$logs,'1','成功');
		//直接生成string输出 echo $ja;
		echo $ja;exit;
		// dump(json_decode($ja,true));exit;
	}

	/**
	 * [save 保存]
	 * @param  [type] $CID    [description]
	 * @param  [type] $mk_sku [description]
	 * @param  [type] $item   [description]
	 * @return [type]         [description]
	 */
	private function save($CID,$mk_sku,$item){
		$Model = M();   //实例化
		$Model->startTrans();//开启事务
		
		$data['cid']                   = trim($CID);
		$data['sku']                   = trim($mk_sku);
		$data['hs_code']               = trim($item['hscode']);
		$data['goods_name']            = trim($item['goods_name']);
		$data['goods_spec']            = trim($item['goods_spec']);
		$data['declare_unit']          = trim($item['declare_unit']);
		$data['legal_unit']            = (trim($item['legal_unit']) != '') ? trim($item['legal_unit']) : trim($item['declare_unit']);
		$data['conv_legal_unit_num']   = $item['conv_legal_unit_num'];
		$data['in_area_unit']          = (trim($item['in_area_unit']) != '') ? trim($item['in_area_unit']) : trim($item['declare_unit']);
		$data['conv_in_area_unit_num'] = trim($item['conv_in_area_unit_num']);
		$data['is_experiment_goods']   = trim($item['is_experiment_goods']);
		$data['is_experiment_goods']   = trim($item['is_experiment_goods']);
		$data['status']                = 9;
		$data['statusstr']             = '新增';
		
		$res = M('Goods')->add($data);
		if($res){
			$data2['goods_id']  = $res;
			$data2['status']    = 9;
			$data2['statusstr'] = '新增';
			$data2['remarks']   = '';

			$res2 = M('GoodsLogs')->add($data2);	//$res2默认为保存成功后的id

			if($res2){
				$Model->commit();//提交事务成功

				return array('gid'=>$res,'lid'=>$res2);
			}else{
				$Model->rollback();//事务有错回滚
				return false;
			}
		}else{
			$Model->rollback();//事务有错回滚
			return false;
		}
	}

	//goods.id, goods_logs.id, goods_logs.ctime
	private function cre_xml($gid,$lid,$ltime){
		//根据$gid获取该商品报备的信息
		$info = M('Goods')->where(array('id'=>$gid))->find();

		$xml = '<ESHOP_ENT_CODE>%SID%</ESHOP_ENT_CODE>
		<ESHOP_ENT_NAME>%SNM%</ESHOP_ENT_NAME>
		<SKU>'.$info['sku'].'</SKU>
		<GOODS_NAME>'.$info['goods_name'].'</GOODS_NAME>
		<GOODS_SPEC>'.$info['goods_spec'].'</GOODS_SPEC>
		<DECLARE_UNIT>'.$info['declare_unit'].'</DECLARE_UNIT>
		<LEGAL_UNIT>'.$info['legal_unit'].'</LEGAL_UNIT>
		<CONV_LEGAL_UNIT_NUM>'.$info['conv_legal_unit_num'].'</CONV_LEGAL_UNIT_NUM>
		<HS_CODE>'.$info['hs_code'].'</HS_CODE>
		<IN_AREA_UNIT>'.$info['in_area_unit'].'</IN_AREA_UNIT>
		<CONV_IN_AREA_UNIT_NUM>'.$info['conv_in_area_unit_num'].'</CONV_IN_AREA_UNIT_NUM>
		<IS_EXPERIMENT_GOODS>'.$info['is_experiment_goods'].'</IS_EXPERIMENT_GOODS>
		<IS_CNCA_POR_DOC>0</IS_CNCA_POR_DOC>
		<IS_ORIGIN_PLACE_CERT>0</IS_ORIGIN_PLACE_CERT>
		<IS_TEST_REPORT>0</IS_TEST_REPORT>
		<IS_LEGAL_TICKET>0</IS_LEGAL_TICKET>
		<IS_MARK_EXCHANGE>0</IS_MARK_EXCHANGE>';
		// dump($xml);
		$xl = $this->DeleteHtml($xml);
		// dump($xl);

		$cust = new \Org\MK\Customs();
		$cust->MessageType = 'SKU_INFO';
		$cust->MessageId   = $lid;//logs.id;
		$cust->MessageTime = date("Y-m-d\TH:i:s",strtotime($ltime));//logs中的日期单，使用date("Y-m-d\TH:i:s")格式;
		$cust->MessageBody = $xml;//(上面生成的xml);
		$res = $cust->post();
		// $res = array("code"=>0,'success'=>0);	//模拟测试

		//当code=1 && success=1时，将该goods.status=8,goods.statusstr='发送成功',保存到logs中，返回时也是返回这个状态
		if($res['code'] == 1 && $res['success'] == 1){
			$Model = M();   //实例化
			$Model->startTrans();//开启事务

			$data['status']    = 8;
			$data['statusstr'] = '发送成功';

			$save_goods = M('Goods')->where(array('id'=>$gid))->save($data);

			if($save_goods){
				$data['goods_id'] = $gid;
				$data['remarks']  = '';

				$add_logs = M('GoodsLogs')->add($data);

				if($add_logs){

					$Model->commit();//提交事务成功
					return array('status' => '8', 'str' => '发送成功');

				}else{

					$Model->rollback();//事务有错回滚
					return array('status' => '3', 'str' => '资料更新或保存失败，请检查');

				}
			}

		}else if($res['code'] == 1 && $res['success'] == 0){	//返回status=3 str=资料可能有误，请检查
			
			return array('status' => '3', 'str' => '资料可能有误，请检查');

		}else if($res['code'] == 0){	//按goods状态文字返回,logs增加 status=3 str=网络错误+success内容

			return array('status' => '3', 'str' => '网络错误，'.$res['success']);

		}
	}

	/**
	 * 去除 <前，>后的空格与回车键，去除空格
	 * @param [type] $str [description]
	 */
	protected function DeleteHtml($str) {
		$str = trim($str); //清除字符串两边的空格
		$str = preg_replace("/\t/","",$str); //使用正则表达式替换内容，如：空格，换行，并将替换为空
		$str = preg_replace("/\r\n/","",$str);
		$str = preg_replace("/\r/","",$str);
		$str = preg_replace("/\n/","",$str);
		$str = preg_replace("/ /","",$str);	//半角空格
		$str = preg_replace("/	/","",$str);  //匹配html中的空格(全角)
		return trim($str); //返回字符串
	}

}