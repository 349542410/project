<?php
/**
 * 申通号管理 服务器端
 */
namespace Api\Controller;
use Think\Controller\HproseController;
class ExpressController extends HproseController{
	protected $crossDomain =	true;
	protected $P3P		   =    true;
	protected $get		   =    true;
	protected $debug 	   =    true;
	protected $l = "LEFT JOIN mk_tran_list l ON s.MKNO=l.MKNO";	// 20160804 Jie 更改为读取mk_tran_list中的ex_context作为最后一条物流信息
	/**
	 * 统计总数
	 * @param  [type] $where [description]
	 * @return [type]        [description]
	 */
	public function count($where,$p,$ePage){
		$l = $this->l;

	/*	// 20160804 Jie 更改为读取mk_tran_list中的ex_context作为最后一条物流信息
		$l 		= ("LEFT JOIN mk_il_logs l ON(l.id=(select max(id) from mk_il_logs where MKNO=s.MKNO))");
		$list 	= M('Stnolist s')->field('s.*,l.content')->join($l)->where($where)->limit($limit)->order('s.id')->select();   */

		//获取数据列表
		$list 	= M('Stnolist s')->field('s.*,l.ex_context as content')->join($l)->where($where)->order('s.id')->page($p.','.$ePage)->select();
		// 翻译成原生 SELECT s.*,l.content FROM mk_stnolist s left join mk_il_logs l ON(l.id=(select max(id) from mk_il_logs where MKNO=s.MKNO)) ORDER BY s.id LIMIT 100
		
		//尚未执行导出CSV 的数据数量
		$map['s.status']   = array('eq',20);	//20 为已使用
		$map['s.cuid']     = array('exp','is NULL');		//标识码 为NULL的 即未导出
		$map['l.STNO']     = array('exp','is not NULL');		//mk_tran_list 中的 申通号不为NULL
		$map['l.TranKd']   = array('eq',1);		//mk_tran_list 中的 中转方式为申通
		// $map['l.IL_state'] = array('egt',200);		//mk_tran_list 中的 物流状态大于等于200
		$map['l.IL_state'] = array('eq', 200);		//mk_tran_list 中的 物流状态为200
		$als = M('Stnolist s')->field('s.id,s.cuid')->join('LEFT JOIN mk_tran_list l ON s.STNO = l.STNO')->where($map)->count();

		//余下电子单数量
		$rest 	= M('Stnolist')->where(array('status'=>'0'))->count();

		$count = M('Stnolist s')->field('s.*,l.ex_context as content')->join($l)->where($where)->count();
		
		return array('list'=>$list,'warn'=>$rest,'als'=>$als,'count'=>$count);
	}

	
	/**
	 *	添加
	 */
	public function newAdd($count,$left,$start,$rest){
		$result = 0;
		$addTime = time();
		for($i=0;$i<$count;$i++){
			
			if($i==$count){		//如果等于则结束循环
				break;
			}

			$newNum = $left . str_pad( $start, $rest, '0', STR_PAD_LEFT);		//拼接出完整的单号
			$start = $start+1;			//为下一个需要拼接的单号+1
			$data['STNO'] = $newNum;	//装入数组
			$data['add_time'] = $addTime;	//装入数组

			$check = M('Stnolist')->where(array('STNO'=>$newNum))->select();		//检验数据表中是否已经存在此申通单号
			if($check){
				// return;
			}else{
				M('Stnolist')->add($data);		//保存到数据库
				$result++;
			}
			
		}

		return $result;

	}

//======================================== csv文件保存到服务器端 ====================================
	/**
	 * 生成csv文件到服务器端
	 */
	public function getCSV($username){
		
		$where['s.status']   = array('eq',20);	//20 为已使用
		$where['s.cuid']     = array('exp','is NULL');		//标识码 为NULL的 即未导出
		$where['l.STNO']     = array('exp','is not NULL');		//mk_tran_list 中的 申通号不为NULL
		$where['l.TranKd']   = array('eq',1);		//mk_tran_list 中的 中转方式为申通
		// $where['l.IL_state'] = array('egt',200);		//mk_tran_list 中的 物流状态大于等于200
		$where['l.IL_state'] = array('eq',200);		//mk_tran_list 中的 物流状态为200

		//查找已使用但尚未导出的申通号
		$st = M('Stnolist s')->field('s.id,s.cuid')->join('LEFT JOIN mk_tran_list l ON s.STNO = l.STNO')->where($where)->select();

		if(count($st) < 1){
			return $tips = array('status'=>'404','msg'=>"没有新的数据需要导出");
			exit;
		}

		$ctime = date('Y-m-d H:i:s',time());
		$cuid = $this->cuid();			//获取一个标识码

		$data1['user_name'] = $username;
		$data1['time'] = $ctime;
		$data1['cuid'] = $cuid;

		$addId = M('ExportRecord')->add($data1);			//保存信息到表  并获取当次新增主键id的值

		foreach($st as $v){
			$data2['cuid'] = $cuid;
			M('Stnolist')->where(array('id'=>$v['id']))->save($data2);	//更新标识码
		}

		$where['s.cuid'] = array('eq',$cuid);		//标识码 为当前已生成的唯一标识

		//获取数据  GROUP_CONCAT    DISTINCT 去掉重复
    	$list = M('Stnolist s')
    			->field('s.id,o.lid,l.STNO,l.MKNO,GROUP_CONCAT(DISTINCT(o.detail) separator "/") as pro,l.number,l.sender,l.sendTel,l.sendAddr,l.receiver,l.reTel,l.province,l.city,l.town,l.reAddr,l.postcode,l.weight,l.premium,l.idno,l.price,o.brand,o.att1,o.att2,o.att4')
    			->join('LEFT JOIN mk_tran_list l ON s.STNO = l.STNO')
    			->join('LEFT JOIN mk_tran_order o ON l.id = o.lid')
    			->group('l.STNO,o.lid')->where($where)->select();

    	$str = array();

    	//设置csv表头
        $title = '序号,订单号,申通号,物品名称,品牌,品名,款号,码数,数量,寄件方,联系人,电话,寄件方地址,收货人,电话,省份,城市,区,收货地址,邮编,重量(lb),保价金额(￥),申报金额(￥),收件人证件号';

        $arr = array();
        $i = 1;	//初始序号
        //数组重构
        foreach($list as $item){
			$arr[$i]['no']       = $i;					//序号
			$arr[$i]['MKNO']     = $item['MKNO'];		//订单号
			$arr[$i]['STNO']     = "\t".$item['STNO'];		//申通号
			$arr[$i]['detail']   = preg_replace("/(;)|(,)|(\")|(')|(，)/" ,'' ,$item['pro']); //物品名称，把匹配符去除
			$arr[$i]['brand']    = $item['brand'];		//品牌
			$arr[$i]['att1']     = $item['att1'];		//品名
			$arr[$i]['att2']     = $item['att2'];		//款号
			$arr[$i]['att4']     = $item['att4'];		//码数
			$arr[$i]['num']      = $item['number'];		//数量
			$arr[$i]['KD']       = $item['sender'];		//寄件方
			$arr[$i]['sender']   = $item['sender'];		//联系人
			$arr[$i]['sendTel']  = "\t".$item['sendTel'];	//联系人电话
			$arr[$i]['sendAddr'] = preg_replace("/(;)|(,)|(\")|(')|(，)/" ,' ' ,$item['sendAddr']); //寄件方地址，把匹配符改为空格
			$arr[$i]['receiver'] = $item['receiver'];	//收件人
			$arr[$i]['reTel']    = "\t".$item['reTel'];		//收件人电话
			$arr[$i]['province'] = $item['province'];	//省份
			$arr[$i]['city']     = $item['city'];		//城市
			
			$a                   = explode(" ",$item['reAddr']);	//从收件地址中获取 地区 信息
			
			$arr[$i]['area']     = $a[2];				//区
			$arr[$i]['reAddr']   = $item['reAddr'];		//收货地址
			$arr[$i]['postcode'] = $item['postcode'];	//收货邮编
			$arr[$i]['weight']   = $item['weight'];		//重量
			$arr[$i]['premium']  = $item['premium'];	//保价金额
			$arr[$i]['price']    = (floatval($item['price']) > 800) ? rand(750,799) : sprintf("%.2f", $item['price']);//'860';	//申报价值   20170227 jie 改为 超￥800的，按750-799 随机金额
			$arr[$i]['idno']     = "\t".trim($item['idno']);		//收件人证件号   20160912 Jie

        	$i++;
        	
        }

		$warn = M('Stnolist')->where(array('status'=>'0'))->count();

        return $tips = array('str'=>$arr, 'addId'=>$addId, 'i'=>$i, 'ctime'=>$ctime, 'warn'=>$warn, 'title'=>$title);

	}

	/**
	 * 更新export_record
	 * @param  [type] $filename [description]
	 * @param  [type] $addId    [description]
	 * @param  [type] $i        [description]
	 * @return [type]           [description]
	 */
	public function reflash($filename,$addId,$i){
        //更新导出状态
        $dat['filename'] = $filename;	//csv文件名
        $dat['nums'] = $i-1;			//数据数量
        $dat['export_status'] = 10;		//导出状态更改为 10 表示导出完成
        M('ExportRecord')->where(array('id'=>$addId))->save($dat);
	}


	/**
	 * 标识码生成方法
	 * @return [type] [description]
	 */
	public function cuid(){
	    if (function_exists('com_create_guid')){
	        return com_create_guid();
	    }else{
	        mt_srand((double)microtime()*10000);//optional for php 4.2.0 and up.
	        $charid = strtoupper(md5(uniqid(rand(), true)));
	        $hyphen = chr(45);// "-"
	        $uuid = //chr(123)// "{"
	                substr($charid, 0, 8).$hyphen
	                .substr($charid, 8, 4).$hyphen
	                .substr($charid,12, 4).$hyphen
	                .substr($charid,16, 4).$hyphen
	                .substr($charid,20,12);
	                //.chr(125);// "}"
	        return $uuid;
	    }
	}

//=================================== 发送到快递100 ===================================
	public function KD100($where,$username){
		ini_set('max_execution_time', 0);
		ini_set('memory_limit','4088M');
		//查找状态为已完成但尚未发送的申通号
		$getdtcount = 300; //每次读取的记录数量
		$st = M('Stnolist s')->field('s.id,s.STNO')->join('LEFT JOIN mk_tran_list l ON s.STNO = l.STNO')->where($where)->order('l.optime')->limit($getdtcount)->select();
		$sti = count($st);
		if($sti < 1){
			return $tips = array('status'=>'404','msg'=>"没有数据需要发送");
			exit;
		}

		//获取config中的快递100配置信息
		$KD100 		= C('KD100');
		$kd100key 	= $KD100['KD100KEY'];
		$cbackurl 	= $KD100['CALLBACKURL'];
		$url      	= $KD100['POSTURL'];		

		$i = 0;
		$schema = "json";
		$cburl 	= array(
						'callbackurl'=>$cbackurl
		);
		$pars 	= array(
					"company"	=> "shentong",
					"number"	=> "",
					"from"		=> "",
					"to"		=> "",
					"key"		=> $kd100key,
					"parameters"=> $cburl,
		);
		foreach($st as $item){
			$pars['number'] 	= $item['STNO'];
			$param 	= json_encode($pars);
			//return $param;
			$post_data = "schema=".$schema."&param=".$param;	//组合

			//通过curl函数发送
			$ch = curl_init();
				curl_setopt($ch, CURLOPT_POST, 1);
				curl_setopt($ch, CURLOPT_HEADER, 0);
				curl_setopt($ch, CURLOPT_URL,$url);
				curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);

				//当CURLOPT_RETURNTRANSFER设置为1时，如果成功只将结果返回，不自动输出返回的内容。如果失败返回FALSE；
				//若不使用这个选项：如果成功只返回TRUE，自动输出返回的内容。如果失败返回FALSE
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

				$result = curl_exec($ch);
				curl_close($ch);
				$res = json_decode($result,true);

			//200为成功，重复发送的则会返回501，501也表示已经成功，保存的时候改为200后保存
			if($res['returnCode'] == '200' || $res['returnCode'] == '501'){

				//mk_stnolist
				$data_KD['kd100status'] 	= 200;	// 状态标注为200表示已发送
				M('Stnolist')->where(array('id'=>$item['id']))->save($data_KD);	// 更新快递100状态

				//mk_send_record
				$data_Record['username'] 	= $username;
				$data_Record['STNO'] 		= $item['STNO'];
				M('SendRecord')->add($data_Record);	// 保存操作记录
				$i++;
			}
		}

		if(count($st) == $i){
			return $tips = array('status'=>'200','msg'=>$sti."条全部发送成功，请继续点击发送，直至显示没有数据为止");
		}else{
			return $tips = array('status'=>'400','msg'=>"成功发送".$i."条(共$sti)数据");
		}

	}

	/**
	 * 导入CSV
	 * @return [type] [description]
	 */
    public function _import_csv($arr){
		ini_set('max_execution_time', 0);
		ini_set('memory_limit','4088M');

		$data_values = array();
		$not_belong  = array();	//不符合 格式规范的数量
		foreach($arr as $key=>$item){
			foreach($item as $k=>$v){

				if($key == 0){	//检查读取得到的第一个单号的长度格式
					if(strlen($item[$k]) < 10){
						$backArr = array('status'=>'0','msg'=>'单号长度格式不正确！');
						return $backArr;
					}
				}

				if(strlen($v) < 10){	//检查长度，长度少于10的则记录下来但不归纳到$data_values中
					$not_belong[] = $v;
				}else{
					$data_values[$key]['STNO'] = $v;	//此处保留原有的键值$key
				}		
			}
		}

		$i = 0;	//保存失败数量
		$j = 0;	//保存成功数量
		$repeat       = array();	//重复的数据 数据表中已存在
		$before_count = count($data_values);	//符合格式的总数

		// 单号保存
		foreach($data_values as $kk=>$val){

			//检查是否已经存在此单号
			$check[$kk] = M('Stnolist')->where($val)->find();

			if($check[$kk]){	//检查重复，如果已经存在此单号
				$repeat[] = $val['STNO'];
			}else{	//如果没有，则保存
				$val['add_time'] = time();
				$res[$kk] = M('Stnolist')->add($val);
				
				if($res[$kk] !== false){
					$j++;
				}else{
					$i++;
				}
			}
		}
		$msg = '成功：'.$j.'个，失败：'.$i.'个，不符合：'.count($not_belong).'个，重复：'.count($repeat).'个';
		//保存的数量 > 0，则成功
		if($j > 0){
			$backArr = array('status'=>'1', 'msg'=>'导入成功，'.$msg, 'not_belong'=>$not_belong, 'repeat'=>$repeat);
			return $backArr;
		}else{
			$backArr = array('status'=>'0', 'msg'=>'导入失败，'.$msg);
			return $backArr;
		}
    }

//========================================
	public function _st_csv($noid){
		ini_set('max_execution_time', 0);
		ini_set('memory_limit','4088M');
		// $where['s.status']   = array('eq',20);	//20 为已使用
		$where['l.noid']   = array('eq',$noid);	//20 为已使用
		// $where['s.cuid']     = array('exp','is NULL');		//标识码 为NULL的 即未导出
		// $where['l.STNO']     = array('exp','is not NULL');		//mk_tran_list 中的 申通号不为NULL
		// $where['l.IL_state'] = array('egt',200);		//mk_tran_list 中的 物流状态大于等于200
		// $where['l.IL_state'] = array('eq','200');		//mk_tran_list 中的 物流状态为200

		//查找已使用但尚未导出的申通号
		// $st = M('Stnolist s')->field('s.id,s.cuid')->join('LEFT JOIN mk_tran_list l ON s.STNO = l.STNO')->where($where)->select();

		// if(count($st) < 1){
		// 	return $tips = array('status'=>'404','msg'=>"没有新的数据需要导出");
		// }

		//获取数据  GROUP_CONCAT    DISTINCT 去掉重复
    	$list = M('Stnolist s')
    			->field('s.id,o.lid,l.STNO,l.MKNO,GROUP_CONCAT(DISTINCT(o.detail) separator "/") as pro,l.number,l.sender,l.sendTel,l.sendAddr,l.receiver,l.reTel,l.province,l.city,l.town,l.reAddr,l.postcode,l.weight,l.premium,l.idno,l.price,o.brand,o.att1,o.att2,o.att4')
    			->join('LEFT JOIN mk_tran_list l ON s.STNO = l.STNO')
    			->join('LEFT JOIN mk_tran_order o ON l.id = o.lid')
    			->group('l.STNO,o.lid')->where($where)->select();

    	$str = array();

    	//设置csv表头
        $title = '序号,订单号,申通号,物品名称,品牌,品名,款号,码数,数量,寄件方,联系人,电话,寄件方地址,收货人,电话,省份,城市,区,收货地址,邮编,重量(lb),保价金额(￥),申报金额(￥),收件人证件号';

        $arr = array();
        $i = 1;	//初始序号
        //数组重构
        foreach($list as $item){
			$arr[$i]['no']       = $i;					//序号
			$arr[$i]['MKNO']     = $item['MKNO'];		//订单号
			$arr[$i]['STNO']     = "\t".$item['STNO'];		//申通号
			$arr[$i]['detail']   = preg_replace("/(;)|(,)|(\")|(')|(，)/" ,'' ,$item['pro']); //物品名称，把匹配符去除
			$arr[$i]['brand']    = $item['brand'];		//品牌
			$arr[$i]['att1']     = $item['att1'];		//品名
			$arr[$i]['att2']     = $item['att2'];		//款号
			$arr[$i]['att4']     = $item['att4'];		//码数
			$arr[$i]['num']      = $item['number'];		//数量
			$arr[$i]['KD']       = $item['sender'];		//寄件方
			$arr[$i]['sender']   = $item['sender'];		//联系人
			$arr[$i]['sendTel']  = "\t".$item['sendTel'];	//联系人电话
			$arr[$i]['sendAddr'] = preg_replace("/(;)|(,)|(\")|(')|(，)/" ,' ' ,$item['sendAddr']); //寄件方地址，把匹配符改为空格
			$arr[$i]['receiver'] = $item['receiver'];	//收件人
			$arr[$i]['reTel']    = "\t".$item['reTel'];		//收件人电话
			$arr[$i]['province'] = $item['province'];	//省份
			$arr[$i]['city']     = $item['city'];		//城市
			
			$a                   = explode(" ",$item['reAddr']);	//从收件地址中获取 地区 信息
			
			$arr[$i]['area']     = $a[2];				//区
			$arr[$i]['reAddr']   = $item['reAddr'];		//收货地址
			$arr[$i]['postcode'] = $item['postcode'];	//收货邮编
			$arr[$i]['weight']   = $item['weight'];		//重量
			$arr[$i]['premium']  = $item['premium'];	//保价金额
			$arr[$i]['price']    = (floatval($item['price']) > 800) ? rand(750,799) : sprintf("%.2f", $item['price']);//'860';	//申报价值   20170227 jie 改为 超￥800的，按750-799 随机金额
			$arr[$i]['idno']     = "\t".trim($item['idno']);		//收件人证件号   20160912 Jie

        	$i++;
        	
        }

		// $warn = M('Stnolist')->where(array('status'=>'0'))->count();

        return $tips = array('str'=>$arr, 'i'=>$i, 'title'=>$title);

	}



}