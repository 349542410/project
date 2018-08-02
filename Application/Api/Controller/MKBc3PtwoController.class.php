<?php
/**
 * 美快优选3(湛江EMS)
 * 湛江快递单号管理 服务器端
 */
namespace Api\Controller;
use Think\Controller\HproseController;
class MKBc3PtwoController extends HproseController{
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

		//获取数据列表
		$list 	= M('Zjnolist s')->field('s.*,l.ex_context as content')->join($l)->where($where)->order('s.id')->page($p.','.$ePage)->select();
		// 翻译成原生 SELECT s.*,l.content FROM mk_stnolist s left join mk_il_logs l ON(l.id=(select max(id) from mk_il_logs where MKNO=s.MKNO)) ORDER BY s.id LIMIT 100
		
		//尚未执行导出CSV 的数据数量
		$map['s.status']   = array('eq',20);	//20 为已使用
		$map['s.cuid']     = array('exp','is NULL');		//标识码 为NULL的 即未导出
		$map['l.STNO']     = array('exp','is not NULL');		//mk_tran_list 中的 申通号不为NULL
		$map['l.TranKd']   = array('eq',1);		//mk_tran_list 中的 中转方式为申通
		$map['l.IL_state'] = array('egt',200);		//mk_tran_list 中的 物流状态大于等于200
		// $map['l.IL_state'] = array('eq','200');		//mk_tran_list 中的 物流状态为200
		$als = M('Zjnolist s')->field('s.id,s.cuid')->join('LEFT JOIN mk_tran_list l ON s.STNO = l.STNO')->where($map)->count();

		//余下电子单数量
		$res 	= M('Zjnolist')->where(array('status'=>'0'))->count();

		$count = M('Zjnolist s')->field('s.*,l.ex_context as content')->join($l)->where($where)->count();

		return array('list'=>$list,'warn'=>$res,'als'=>$als,'count'=>$count);
	}

	/**
	 * 获取数据列表  注销
	 * @param  [type] $where [description]
	 * @param  [type] $limit [description]
	 * @return [type]        [description]
	 */
	// public function getList($where,$limit){
	// 	$l = $this->l;

	// 	//获取数据列表
	// 	$list 	= M('Zjnolist s')->field('s.*,l.ex_context as content')->join($l)->where($where)->limit($limit)->order('s.id')->select();
	// 	// 翻译成原生 SELECT s.*,l.content FROM mk_stnolist s left join mk_il_logs l ON(l.id=(select max(id) from mk_il_logs where MKNO=s.MKNO)) ORDER BY s.id LIMIT 100
		
	// 	//尚未执行导出CSV 的数据数量
	// 	$map['s.status']   = array('eq',20);	//20 为已使用
	// 	$map['s.cuid']     = array('exp','is NULL');		//标识码 为NULL的 即未导出
	// 	$map['l.STNO']     = array('exp','is not NULL');		//mk_tran_list 中的 申通号不为NULL
	// 	$map['l.TranKd']   = array('eq',1);		//mk_tran_list 中的 中转方式为申通
	// 	$map['l.IL_state'] = array('egt',200);		//mk_tran_list 中的 物流状态大于等于200
	// 	// $map['l.IL_state'] = array('eq','200');		//mk_tran_list 中的 物流状态为200
	// 	$als = M('Zjnolist s')->field('s.id,s.cuid')->join('LEFT JOIN mk_tran_list l ON s.STNO = l.STNO')->where($map)->count();

	// 	//余下电子单数量
	// 	$res 	= M('Zjnolist')->where(array('status'=>'0'))->count();

	// 	return array($list,$res,$als);
	// }
	
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

			$check = M('Zjnolist')->where(array('STNO'=>$newNum))->select();		//检验数据表中是否已经存在此申通单号
			if($check){
				// return;
			}else{
				M('Zjnolist')->add($data);		//保存到数据库
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
		$where['l.IL_state'] = array('egt',200);		//mk_tran_list 中的 物流状态大于等于200
		// $where['l.IL_state'] = array('eq','200');		//mk_tran_list 中的 物流状态为200

		//查找已使用但尚未导出的申通号
		$st = M('Zjnolist s')->field('s.id,s.cuid')->join('LEFT JOIN mk_tran_list l ON s.STNO = l.STNO')->where($where)->select();

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
			M('Zjnolist')->where(array('id'=>$v['id']))->save($data2);	//更新标识码
		}

		$where['s.cuid'] = array('eq',$cuid);		//标识码 为当前已生成的唯一标识

		//获取数据  GROUP_CONCAT    DISTINCT 去掉重复
    	$list = M('Zjnolist s')
    			->field('s.id,o.lid,l.STNO,l.MKNO,GROUP_CONCAT(DISTINCT(o.detail) separator "/") as pro,l.number,l.sender,l.sendTel,l.sendAddr,l.receiver,l.reTel,l.province,l.city,l.reAddr,l.postcode,l.weight,l.premium,l.idno,l.price')
    			->join('LEFT JOIN mk_tran_list l ON s.STNO = l.STNO')
    			->join('LEFT JOIN mk_tran_order o ON l.id = o.lid')
    			->group('l.STNO,o.lid')->where($where)->select();

    	$str = array();

    	//设置csv表头
        $str[] = '序号,订单号,申通号,物品名称,数量,寄件方,联系人,电话,寄件方地址,收货人,电话,省份,城市,区,收货地址,邮编,重量(KG),保价金额(￥),申报金额(￥),收件人证件号';

        $arr = array();
        $i = 1;	//初始序号
        //数组重构
        foreach($list as $item){
			$arr[$i]['no']       = $i;					//序号
			$arr[$i]['MKNO']     = $item['MKNO'];		//订单号
			$arr[$i]['STNO']     = "\t".$item['STNO'];		//申通号
			$arr[$i]['detail']   = preg_replace("/(;)|(,)|(\")|(')|(，)/" ,'' ,$item['pro']); //物品名称，把匹配符去除
			$arr[$i]['num']      = $item['number'];		//数量
			$arr[$i]['KD']       = $item['sender'];		//寄件方
			$arr[$i]['sender']   = $item['sender'];		//联系人
			$arr[$i]['sendTel']  = "\t".$item['sendTel'];	//联系人电话
			$arr[$i]['sendAddr'] = preg_replace("/(;)|(,)|(\")|(')|(，)/" ,' ' ,$item['sendAddr']); //寄件方地址，把匹配符改为空格
			$arr[$i]['receiver'] = $item['receiver'];	//收件人
			$arr[$i]['reTel']    = "\t".$item['reTel'];		//收件人电话
			$arr[$i]['province'] = $item['province'];	//省份
			$arr[$i]['city']     = $item['city'];		//城市

        	$a = explode(" ",$item['reAddr']);	//从收件地址中获取 地区 信息

			$arr[$i]['area']     = $a[2];				//区
			$arr[$i]['reAddr']   = $item['reAddr'];		//收货地址
			$arr[$i]['postcode'] = $item['postcode'];	//收货邮编
			$arr[$i]['weight']   = $item['weight'];		//重量
			$arr[$i]['premium']  = $item['premium'];	//保价金额
			$arr[$i]['price']    = (floatval($item['price']) > 800) ? rand(750,799) : sprintf("%.2f", $item['price']);//'860';	//申报价值   20170227 jie 改为 超￥800的，按750-799 随机金额
			$arr[$i]['idno']     = "\t".trim($item['idno']);		//收件人证件号   20160912 Jie

        	$i++;
        	
        }

        //数组转为字符串 以一维数组装载
        foreach($arr as $v){
        	$str[] = implode(",",$v); 
        }

        foreach ($str as $j => $v) { 
		// CSV的Excel支持GBK编码，一定要转换，否则乱码 
			$str[$j] = iconv('utf-8', 'gbk//IGNORE', $v); 
		}

		$warn = M('Zjnolist')->where(array('status'=>'0'))->count();

        return $tips = array('str'=>$str,'addId'=>$addId,'i'=>$i,'ctime'=>$ctime,'warn'=>$warn);

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
		$st = M('Zjnolist s')->field('s.id,s.STNO')->join('LEFT JOIN mk_tran_list l ON s.STNO = l.STNO')->where($where)->order('l.optime')->limit($getdtcount)->select();
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
				M('Zjnolist')->where(array('id'=>$item['id']))->save($data_KD);	// 更新快递100状态

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

//=================================================
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
			$check[$kk] = M('Zjnolist')->where($val)->find();

			if($check[$kk]){	//检查重复，如果已经存在此单号
				$repeat[] = $val['STNO'];
			}else{	//如果没有，则保存
				$val['add_time'] = time();
				$res[$kk] = M('Zjnolist')->add($val);
				
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

//======================================
    /**
     * 清关 --- 资料导出(导出成功后直接提成用户下载)
     * 导出文件，是用于手动形式报关，处理结果跟“发送”形式的报关一致
     * @return [type] [description]
     */
    public function _export_file($noid){

    	$title = "原始单号,证件号码,总费用,订单时间,收货人,收货地址,收货人电话,省,市,县,邮编,备注,店铺货号,数量,单价,支付单号,订单号,订购人姓名,STNO,重量(lb),商品名称,海关商品报备编码,条形码,行邮税则号,货品名称,货品重量,时间,自定义单号1,自定义单号2,品牌,货币类型,计量单位,原厂地国别,备注,规格型号";

    	$tcid = M('TransitNo')->where(array('id'=>$noid))->getField('tcid');

    	$line = M('transit_center')->field('bc_state,cc_state')->where(array('id'=>$tcid))->find();

    	// 找出该线路所含有的 类别
    	$map_c['TranKd'] = array('like','%,'.$tcid.',%');
    	$cate_list = M('category_list')->where($map_c)->field('id,cat_name')->select();

    	if($line['bc_state'] == '1'){
	        //因为导出的资料不需要带序号，所以从f1开始记数
	        $fit = "l.MKNO as f1,l.idno as f2,l.price as f3,ifnull(l.paytime,l.optime) as f4,l.receiver as f5,l.reAddr as f6,l.reTel as f7,l.province as f8,l.city as f9,l.town as f10,l.postcode as f11,l.notes as f12,p.hgid as f13,o.number as f14,o.price as f15,l.payno as f16,concat_ws('',l.auto_Indent2,l.auto_Indent1) as f17,l.receiver as f18,l.STNO as f19,l.weight as f20,p.detail as f21,p.hs_code as f22,p.barcode as f23,ifnull(p.tariff_no,p.hs_code) as f24,p.name as f25,p.weight as f26,p.sys_time as f27,o.auto_Indent1 as f28,o.auto_Indent2 as f29,p.brand as f30,p.coin as f31,p.unit as f32,p.remark as f33,p.source_area as f34,o.specifications as f35,p.price as f36,p.show_name as f37,p.net_weight as f38,p.rough_weight as f39,p.parameter_one as f40,p.parameter_two as f41,p.parameter_three as f42,p.parameter_four as f43,p.parameter_five as f44,o.category_one as f45,o.category_two as f46,order.att1 as f47,order.att2 as f48,order.att3 as f49,order.att4 as f50,order.att5 as f51,order.att6 as f52,order.att7 as f53,order.att8 as f54,order.att9 as f55,order.att10 as f56";
	        
	        /* 20160123 */
	        $list = M('TranList l')
	        ->field($fit)
	        ->join('LEFT JOIN mk_tran_ulist u ON u.MKNO = l.MKNO')
	        ->join('LEFT JOIN mk_tran_uorder o ON o.lid = u.id')
			->join('LEFT JOIN mk_product_list p ON p.id = o.product_id')
			->join('LEFT JOIN mk_tran_order order ON order.lid = l.id')
	        ->where(array('noid'=>$noid))->select();

	        $title .= ",价格,名称,净重,毛重,参数一,参数二,参数三,参数四,参数五,一级类别,二级类别,att1,att2,att3,att4,att5,att6,att7,att8,att9,att10";

	        foreach($cate_list as $k1=>$v1){
	        	foreach($list as $k2=>$v2){
	        		if($v1['id'] == $v2['f45']){
	        			$list[$k2]['f45'] = $cate_list[$k1]['cat_name'];
	        		}

	        		if($v1['id'] == $v2['f46']){
	        			$list[$k2]['f46'] = $cate_list[$k1]['cat_name'];
	        		}
	        	}
	        }

    	}else if($line['cc_state'] == '1'){
	        //因为导出的资料不需要带序号，所以从f1开始记数
	        $fit = "l.MKNO as f1,l.idno as f2,l.price as f3,ifnull(l.paytime,l.optime) as f4,l.receiver as f5,l.reAddr as f6,l.reTel as f7,l.province as f8,l.city as f9,l.town as f10,l.postcode as f11,l.notes as f12,o.hgid as f13,o.number as f14,o.price as f15,l.payno as f16,concat_ws('',l.auto_Indent2,l.auto_Indent1) as f17,l.receiver as f18,l.STNO as f19,l.weight as f20,o.detail as f21,o.hs_code as f22,o.barcode as f23,o.tariff_no as f24,o.catname as f25,o.weight as f26,o.time as f27,o.auto_Indent1 as f28,o.auto_Indent2 as f29,o.brand as f30,o.coin as f31,ifnull(o.unit,o.num_unit) as f32,o.source_area as f33,o.remark as f34,o.specifications as f35,o.spec_unit as f36,o.num_unit as f37,r.category_one as f38,r.category_two as f39,o.att1 as f40,o.att2 as f41,o.att3 as f42,o.att4 as f43,o.att5 as f44,o.att6 as f45,o.att7 as f46,o.att8 as f47,o.att9 as f48,o.att10 as f49";
	        
	        /* 20160123 */
	        $list = M('TranList l')
	        ->field($fit)
	        ->join('LEFT JOIN mk_tran_ulist u ON u.MKNO = l.MKNO')
	        ->join('LEFT JOIN mk_tran_order o ON o.lid = l.id')
	        ->join('LEFT JOIN mk_tran_uorder r ON o.lid = u.id')
	        ->where(array('noid'=>$noid))->select();

	        $title .= ",规格单位,数量单位,一级类别,二级类别,att1,att2,att3,att4,att5,att6,att7,att8,att9,att10";

	        foreach($cate_list as $k1=>$v1){
	        	foreach($list as $k2=>$v2){
	        		if($v1['id'] == $v2['f38']){
	        			$list[$k2]['f38'] = $cate_list[$k1]['cat_name'];
	        		}

	        		if($v1['id'] == $v2['f39']){
	        			$list[$k2]['f39'] = $cate_list[$k1]['cat_name'];
	        		}

	        	}
	        }

    	}else if($line['bc_state'] == '0' && $line['cc_state'] == '0'){

    		// 22 卓志物流
    		if($tcid == 22){

    			$title = "序号,订单编号,总运单号,快件单号,发件人,发件人城市,发件人地址,发件人电话,发货人国别代码,收件人,身份证件号码,收件人电话,收件人省市区代码,省,市,区/县,详细地址,收件人地址,内件名称,总数量,币制代码,总价,重量(Kg*),商品行邮税号,商品平台货号,物品名称,品牌,规格型号,商品数量,单价,原产国代码,商品HS编码,单位代码,袋号托号,订单网址,支付单号,支付企业,支付日期时间,运费,税金,订单网站名称,电商企业备案名称,电商企业备案号,订购人姓名,订购人证件号码,订购人电话号码,订购人账户名,保价费,非现金抵扣金额,电商平台代码,商品序号,法定计量单位,法定数量,第二法定数量,总净重,商检商品备案号,关区代码,电商企业单一窗口编号,支付企业编号";

    			$field = 'l.*,d.lading_no,o.hgid,o.detail,o.number as o_number,o.price as o_price,o.brand,o.unit as o_unit,o.specifications,o.weight as o_weight,o.hs_code';

		        $arr = M('TranList l')
		        ->field($field)
		        ->join('LEFT JOIN mk_tran_order o ON o.lid = l.id')
		        ->join('LEFT JOIN mk_transit_no n ON n.id = l.noid')
		        ->join('LEFT JOIN mk_transit_lading d ON d.id = n.lading_id')
		        ->where(array('noid'=>$noid))
		        ->select();

				$list = array();
				$i = 1;	//初始序号
				foreach($arr as $k=>$v){
			        /* 去除详细地址中的省市区和空格 */
			        $v['reAddr'] = str_replace($v['town'],'',$v['reAddr']);
			        $v['reAddr'] = str_replace($v['city'],'',$v['reAddr']);
			        $v['reAddr'] = str_replace($v['province'],'',$v['reAddr']);
			        $v['reAddr'] = trim($v['reAddr'],' ');//清除两侧的空格

					$list[$k]['f0'] = $i;
					$list[$k]['f1'] = $v['MKNO'];
					$list[$k]['f2'] = $v['lading_no'];
					$list[$k]['f3'] = $v['traceCode'];//要用中通号，非卓志单号
					$list[$k]['f4'] = $v['sender'];
					$list[$k]['f5'] = '美国';
					$list[$k]['f6'] = $v['sendAddr'];
					$list[$k]['f7'] = $v['sendTel'];
					$list[$k]['f8'] = '502';
					$list[$k]['f9'] = $v['receiver'];
					$list[$k]['f10'] = $v['idno'];
					$list[$k]['f11'] = $v['reTel'];
					$list[$k]['f12'] = '';//收件人省市区代码
					$list[$k]['f13'] = $v['province'];
					$list[$k]['f14'] = $v['city'];
					$list[$k]['f15'] = $v['town'];
					$list[$k]['f16'] = $v['reAddr'];
					$list[$k]['f17'] = '';//收件人地址
					$list[$k]['f18'] = $v['detail'];
					$list[$k]['f19'] = $v['o_number'];
					$list[$k]['f20'] = '142';//币制代码
					$list[$k]['f21'] = sprintf("%.2f",($v['o_number'] * $v['o_price']));
					$list[$k]['f22'] = sprintf("%.3f",($v['o_number'] * $v['o_weight']));
					$list[$k]['f23'] = '';//商品行邮税号
					$list[$k]['f24'] = $v['hgid'];
					$list[$k]['f25'] = $v['detail'];
					$list[$k]['f26'] = $v['brand'];
					$list[$k]['f27'] = $v['specifications'];
					$list[$k]['f28'] = $v['o_number'];
					$list[$k]['f29'] = $v['o_price'];
					$list[$k]['f30'] = '502';//原产国代码
					$list[$k]['f31'] = $v['hs_code'];
					$list[$k]['f32'] = unit_code($v['o_unit']);
					$i++;
				}
    		}else{

	    		$fit = "l.MKNO as f1,l.idno as f2,l.price as f3,ifnull(l.paytime,l.optime) as f4,l.receiver as f5,l.reAddr as f6,l.reTel as f7,l.province as f8,l.city as f9,l.town as f10,l.postcode as f11,l.notes as f12,o.hgid as f13,o.number as f14,o.price as f15,l.payno as f16,concat_ws('',l.auto_Indent2,l.auto_Indent1) as f17,l.receiver as f18,l.STNO as f19,l.weight as f20,o.detail as f21,o.hs_code as f22,o.barcode as f23,o.tariff_no as f24,o.catname as f25,o.weight as f26,o.time as f27,o.auto_Indent1 as f28,o.auto_Indent2 as f29,o.brand as f30,o.coin as f31,o.unit as f32,o.remark as f33,o.source_area as f34,o.specifications as f35,o.att1 as f36,o.att2 as f37,o.att3 as f38,o.att4 as f39,o.att5 as f40,o.att6 as f41,o.att7 as f42,o.att8 as f43,o.att9 as f44,o.att10 as f45";
		        $list = M('TranList l')
		        ->field($fit)
		        ->join('LEFT JOIN mk_tran_order o ON o.lid = l.id')
				->where(array('noid'=>$noid))->select();
				
		        $title .= ",att1,att2,att3,att4,att5,att6,att7,att8,att9,att10";
    		}
			
    	}
        
        return array('list'=>$list, 'tcid'=>$tcid, 'title'=>$title);
    }

    /**
     * [saveState description]
     * @param  [type] $arr    [STNO数组]
     * @param  [type] $TranKd [TranKd]
     * @param  [type] $username [操作员真实姓名]	 * 
     * @return [type]         [description]
     */
    public function saveState($arr, $TranKd ,$username){
        foreach($arr as $item){

            //更新报关状态
            $res = M('TranList')->where(array('STNO'=>$item))->setField('custom_status',1);

            //检查报关信息是否已存在
            $check_trainer = M('Trainer')->where(array('LogisticsNo'=>$item))->find();

            // 新增或更新报关信息
            $data = array();
            if(!$check_trainer){
                $data['LogisticsNo'] = $item;
                $data['Status']      = 1;
                $data['Result']      = '已导出';
                $data['CreateTime']  = date('Y-m-d H:i:s');
                $data['TranKd']      = $TranKd;
                M('Trainer')->add($data);

            }else{//更新
                $data['Status']      = 1;
                $data['Result']      = '已导出';
                $data['CreateTime']  = date('Y-m-d H:i:s');
                M('Trainer')->where(array('id'=>$check_trainer['id']))->save($data);
            }

            // 保存报关操作记录
            $log = array();
            $log['LogisticsNo'] = $item;
            $log['Status']      = 1;
            $log['content']     = '导出成功【'.$username.'】';
            $log['CreateTime']  = date('Y-m-d H:i:s');
            M('TrainerLogs')->add($log);

        }
    }

}