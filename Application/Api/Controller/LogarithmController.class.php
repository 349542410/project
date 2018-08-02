<?php
/**
 * 批号对数
 * IL_state 字段用作查询的时候，要以数字的形式进行查询 Jie 20160314
 * 修改记录：
 * 1.第28-29，93-94 行，查询条件暂时更改。
 */
namespace Api\Controller;
use Think\Controller\HproseController;
class LogarithmController extends HproseController{    
    protected $crossDomain =    true;
    protected $P3P         =    true;
    protected $get         =    true;
    protected $debug       =    true;

    //获取所有线路名称和ID  暂时没用  20180307 jie
    //20180626 xieyiyi 改  添加了$where条件
    public function _center_list()
    {
        $where['status'] = 1;
        $center_list = M('TransitCenter')->where($where)->order('ctime desc')->field('id,name')->select();
        return $center_list;
    }

    // 中转跟踪
    public function _index($map, $request){
		//按时间选后顺序 读取 mk_transit_no.status >9 <31 的no,id
		//统计tran_list.noid=no.id的 IL_state=20和总数量    Jie  20160223  此查询条件不足，下面的函数fresh_count中已做查询条件的详细补充

    	// $map['tn.status'] = array('lt',60);	// 注意  2017-10-30 jie 暂时改为 < 60 即可
		$map['tn.status'] = array(array('gt',9),array('lt',31),'and');  // 注意：2017-10-30 jie 原查询条件
		$list = M('TransitNo tn')->field('tn.id,tn.no,tn.date,tn.airdatetime,tn.airno,tn.status,tc.name,tn.tcid,tn.grossweight_lb,tn.grossweight_kg')
								 ->join('LEFT JOIN mk_transit_center tc ON tn.tcid = tc.id')
								 ->where($map)->order('tn.date desc')->select();

		foreach($list as $key=>$item){
			$res = $this->fresh_count($item['id'],'inside');
			$list[$key]['all'] = $res[0];
			$list[$key]['twn'] = $res[1];
		}

		// 如果$request为true， 则为外部控制器的请求操作，把标签A覆盖为外部传入的数据 20170103 jie
		if($request == true) return $list;
		
		return array('list'=>$list);
    }

	/**
	 * 刷新数量的统计（中转跟踪）  --- 计算未扫描的总数和该批号总数(同时刷新这两个数据)
	 * @return [type] [description]
	 */
    public function fresh_count($id,$type=''){
    	$where = array();
		$where['noid'] = array('eq',$id);
		$all = M('TranList')->where($where)->count();	//总数

		$map2 = array();
		$map2['pause_status'] = array('eq','0');

		$map3 = array();
		$map3['pause_status'] = array('eq','20');
		$map3['candel']       = array('eq','0');

		$map4 = array();
		$map4[]         = $map2;
		$map4[]         = $map3;
		$map4['_logic'] = 'or';
		
		$where['IL_state'] = array('eq','20');
		$where[]           = $map4;
		$where['_logic']   = 'and';

		// $twn 的数量总数查询是根据以下这条sql语句作为条件查询的 20160223 Jie
		// WHERE noid=$id AND IL_State=20  AND (pause_status=0 OR (pause_status=20 AND candel=0))
		$twn = M('TranList')->where($where)->count();	//IL_state = 20的数量
		if($type != 'inside'){
			$backArr = array('status'=>1, 'twn'=>$twn, 'total'=>$all, 'timeout'=>10);	//倒计时为10秒
		}else{
			$backArr = array($all,$twn);
		}
		return $backArr;
    }

    // 统计某个批次号的整批重量
    public function _count_weight($id){
    	$where = array();
		$where['noid'] = array('eq',$id);
		$list = M('TranList')->field('weight')->where($where)->select();	//总数

		$weight_lb = 0;
		foreach($list as $item){
			$weight_lb += $item['weight'];
		}

		$weight_kg = sprintf("%.2f", (0.454 * $weight_lb));
		$weight_lb = sprintf("%.2f", $weight_lb);

		$data = array('grossweight_kg'=>$weight_kg, 'grossweight_lb'=>$weight_lb);

		M('TransitNo')->where(array('id'=>$id))->setField($data);

		return $data;
    }

///////////////////////////////////////////////////

    //快递跟踪界面
    public function pro_two($map, $url, $sto, $request){

    	//当$sto为1的时候才列出数据
    	if($sto == '1'){
			//按时间选后顺序 读取 mk_transit_no.status >29 <60 的no,id
			//统计tran_list.noid=no.id的 IL_state为各种状态的数量和总数量
			// $map['tn.status'] = array('lt',60);	// 注意  2017-10-30 jie 暂时改为 < 60 即可
			$map['tn.status'] = array(array('gt',29),array('lt',60),'and'); // 注意：2017-10-30 jie 原查询条件
			$list = M('TransitNo tn')->field('tn.id,tn.no,tn.ldate,tn.airdatetime,tn.airno,tn.status,tc.name,tn.tcid')
									 ->join('LEFT JOIN mk_transit_center tc ON tn.tcid = tc.id')
									 ->where($map)->order('tn.date asc')->select();

			// 这里的$i用于判断当前计算的总数是否已达到20个总数，当已经等于20的时候，则停止执行后续的批号的数量计算
			$i = 0;
			foreach($list as $key=>$item){
				
				$res = $this->fresh_other($item['id'],'inside',$url,$i,$item['tcid']);

				$list[$key]['all'] = $res[0];
				$list[$key]['twn'] = $res[1];
				$i++;
			}
    	}else{
    		$list = '';
    	}

    	// 如果$request为true， 则为外部控制器的请求操作，把标签A覆盖为外部传入的数据 20170103 jie
    	if($request == true) return $list;

		return array('list'=>$list);
    }

	/**
	 * 刷新各种快递状态的数量总数（快递跟踪）
	 * @return [type] [description]
	 */
    public function fresh_other($id, $type='', $url='', $n=0, $tcid=''){

    	$arr3 = array(
            '1000' =>'在途',
            '1001' =>'揽件',
            '1002' =>'疑难',
            '1003' =>'签收',
            '1005' =>'派件中',
            '1004' =>'退回',
            '1006' =>'拒收',
            '1012' =>'延迟',//20161013 Jie
            '1400' =>'清关中',//20161209 Jie
            '1410' =>'已出关',//20161209 Jie

            //增加丢失件状态
            '2001'  => '异常',
            '2012'	=> '异常',
            '2014'	=> '异常',
            '2011'	=> '异常',
            '2002'  => '异常',
            '2003'  => '异常',
            '2015'  => '异常',
            '2007'  => '异常',
            '2004'  => '异常',
            '2009'  => '异常',
            '2013'	=> '异常',
            '2005'  => '异常',
            '2006'  => '异常',
            '2008'  => '异常',
            '2000'  => '异常',
            '2010'  => '异常',
        );

    	$where = array();
    	$where['noid'] = array('eq',$id);
    	$where['pause_status'] = array('eq','0');		//不计算暂停件在内的总数
    	// $where['IL_state'] = array('gt',19);  // 20160309 Jie 如果总数的计算有误，请添加此查询条件再试
    	$all = M('TranList')->where($where)->count();	//总数

    	// 根据函数pro_two上面传入的$i(这里为$n)，如果传入的值等于20，那么久不再去执行计算(即只执行前20个批号的数量计算)  20160224 Jie
    	if($n >= 20){
    		$twn = 0;	//默认返回0，等待用户点击刷新再进行计算
    		$backArr = array($all,$twn);
    		return $backArr;
    		exit;
    	}else{
	    	$twn = '';
			$where['IL_state'] = array('lt',1000);
	    	$lost = M('TranList')->where($where)->count();	//缺件

            if($lost != 0) $twn .= '<a style="color:red;" target="_blank" href="'.U($url,array('IL_state'=>'1000','stype'=>'lost','noid'=>$id,'tcid'=>$tcid)).'"">缺件:'.$lost.'</a> ; ';

            $where20180713['noid'] = array('eq',$id);
            $where20180713['IL_state'] = 1000;
            $where20180713['_string'] = ' (ex_context like "%海关查验%")  OR ( ex_context like "%清关中") ';
            $haiguan = M('TranList')->where($where20180713)->count();	//未清关

            if($haiguan > 0){
                $twn .= '<a style="color:red;" target="_blank" href="'.U($url,array('IL_state'=>'1000','stype'=>'customs','noid'=>$id,'tcid'=>$tcid)).'"">未清关:'.$haiguan.'</a> ; ';
            }

    	}

    	/* new 优化 统计$arr3中各个状态的总数的方法  2017-12-19 jie */
    	$map_arr = array_keys($arr3);
		$where['IL_state'] = array('in', $map_arr);
    	$tolist = M('TranList')->where($where)->select();

    	$tx = array();
    	foreach($tolist as $to){
    		if(in_array($to['IL_state'], $map_arr)){
    			if(!isset($tx[$to['IL_state']])){
    				$tx[$to['IL_state']] = 1;
    			}else{
    				$tx[$to['IL_state']]++;
    			}
    		}
    	}
    	ksort($tx);
    	foreach($tx as $key=>$it){
    		$twn .= '<a target="_blank" href="'.U($url,array('IL_state'=>$key,'stype'=>'KD','noid'=>$id,'tcid'=>$tcid)).'"">'.$arr3[$key].":".$it."</a> ; ";
    	}
    	/* end 2017-12-19 jie */

/*    	// 由于此方法速度过慢，所以不再使用  2017-12-19 jie
		//统计其他状态的各自的总数
    	foreach($arr3 as $key=>$it){
			$where['IL_state']     = array('eq',$key);
    		$acount = M('TranList')->where($where)->count();
    		if($acount != 0) $twn .= '<a target="_blank" href="'.U($url,array('IL_state'=>$key,'stype'=>'KD','noid'=>$id,'tcid'=>$tcid)).'"">'.$it.":".$acount."</a> ; ";
    	}*/

    	if($type != 'inside'){
    		if($twn == '') $twn =0;
			$backArr = array('status'=>1, 'twn'=>$twn, 'timeout'=>10);	//倒计时为10秒
		}else{
			if($twn == '') $twn =0;
			$backArr = array($all,$twn);
		}
    	
		return $backArr;
	}

	/**
	 * 某个详情
	 * @return [type] [description]
	 */
    public function info($id){

		$map['tn.id'] = array('eq',$id);
		$info = M('TransitNo tn')->field('tn.*,tc.name,tc.creater,ec.company_name,ec.ilurl')
				->join('LEFT JOIN mk_transit_center tc ON tn.tcid = tc.id')
				->join('LEFT JOIN mk_express_company ec ON tn.airid = ec.id')
				->where($map)->find();

		//mk_express_company.ilurl,替换%s为航空号airno
		$info['ilurl'] = str_replace("%s", $info['airno'], $info['ilurl']);

		$msg = M('TransitNo tn')->join('LEFT JOIN mk_air_logs l ON l.airid = tn.airid AND l.airno = tn.airno')->where($map)->order('ctime desc')->select();

		return $arr = array($info,$msg);
    }

    public function showList($noid,$stype,$Istate,$p){
		
		if($stype == 'lost'){
			// $map['IL_state']     = array('lt',intval($Istate));
			// $map['pause_status'] = array('eq',0);
			//$list = "SELECT * FROM `mk_tran_list` WHERE `noid` = {$noid} AND `IL_state` < {$Istate} AND `pause_status` = 0 LIMIT 0,30";
			$map 	= "noid = $noid AND IL_state < $Istate AND pause_status = 0";

		}else if($stype == 'all'){// 批号对数/快递跟踪/all  20160914 Jie

	    	$map['noid'] = array('eq',$noid);
	    	$map['pause_status'] = array('eq','0');		//不计算暂停件在内的总数

		}else if($stype == 'customs'){// 未清关的订单  20180713 wangpeichun

            $map['noid'] = array('eq',$noid);
            $map['IL_state'] = 1000;
            $map['_string'] = ' (ex_context like "%海关查验%")  OR ( ex_context like "%清关中") ';

        }else{
			$map['noid'] = array('eq',$noid);
			$map['IL_state'] = array('eq',$Istate);
		}
		
		$list  = M('TranList')->where($map)->page($p.',30')->select();	//数据列表

		$count = M('TranList')->where($map)->count();		//总数
		$mstr  = M('TransitNo')->where(array('id'=>$noid))->getField('no');	//批次号
		//每个中转批号对应的备注信息
        $record_list = M('AccountRecord')->where(array('mStr1'=>$mstr))->order('op_time asc')->select();
		return $res = array($list,$count,$mstr,$record_list);
    }

    /**
     * 快递跟踪 --- 手动完成某个订单
     * @param  [type] $id            [tran_list.id]
     * @param  [type] $status_select [select标签的val]
     * @param  [type] $status_custom [自定义原因]
     * @param  [type] $befinished    [完成此单 checkbox]
     * @param  [type] $username      [当前登录的真实用户名]
     * @param  [type] $arr_message   [手动更新信息数组]
     * @param  [type] $tname         [操作员真实姓名全显示 20161103 Jie]
     * @param  [type] $any           [用于区分操作多个订单 20170222 Jie]
     * @return [type]                [description]
     */
    public function _toPost($id,$status_select,$status_custom,$befinished,$username,$arr_message,$tname,$any=false){
    		
    		if($any === false){
    			$g_map = array('id'=>$id);
    		}else{
    			$g_map = array('STNO'=>$id);
    		}

			$info = M('TranList')->field('noid,MKNO,CID,IL_state,optime')->where($g_map)->find();

			//如果客户端提交过来的是需要操作“完成此单”的，则需要进行以下验证
			if($befinished == 'on'){
				//判断该单的操作时间是否距离当前时间 已经大于5天，如果 少于等于5天，则不能执行 “完成此单”
				if(time() - strtotime($info['optime']) <= 432000){
					$backArr = array('status'=>2, 'msg'=>'该单的操作时间不在操作范围内');
					return $backArr;
				}
			}

/*	//20170607 Jie 临时改为，所有状态下都可以添加物流信息
		//检查该单的IL_state状态是否为1003，如果是，则不能继续以下操作
			if($info['IL_state'] == '1003'){
				// // 检查是否已经存在该单对应的物流信息
				// $check_logs = M('IlLogs')->where(array('MKNO'=>$info['MKNO'],'status'=>$data['status']))->count();

				// // 如果mk_tran_list中该单的状态已经为1003，但是mk_il_logs中没有对应的物流信息记录，则进行补充
				// if($check_logs == 0){
				// 	$res = M('IlLogs')->add($data);
				// }
				$backArr = array('status'=>3, 'msg'=>'该单已被签收');
				return $backArr;
			}
*/
			// 增加一个“已完成”，功能：在物流记录mk_il_logs中，不作任何操作，但将mk_tran_list状态改为1003，ex_context(time)要更改内容为“XXX”执行完成操作
			// 20160728 Jie 新增以下操作，当此步骤运行就会自动终止继续运行
			if($status_select == '2010' && $befinished == 'on'){

				$Tran_data['IL_state']   = 1003;
				$Tran_data['ex_time']    = date('Y-m-d H:i:s');
				$Tran_data['ex_context'] = '【'.$username.'】 执行完成操作';

				$save = M('TranList')->where($g_map)->save($Tran_data);

				if($save == 0){
					$backArr = array('status' => 0, 'msg' => '没有数据更新');
				}else if($save === false){
					$backArr = array('status' => 0, 'msg' => '操作失败');
				}else{
					$backArr = array('status' => 1, 'msg' => '操作成功');
				}
				return $backArr;exit;//终止继续往下执行
			}

			//如果自定义原因为空，则取select标签的val  PS:当程序没有进行以下3种情况的运行时(即$data['content'] 不存在时)，前端脚本运行->请求失败提示
			if($status_select != '2000' && $status_custom == ''){
				
				// $data['content'] = $arr_message[$status_select].'('.$username.')';
				$data['content'] = $arr_message[$status_select];
				
			}else if($status_select == '2000' && $status_custom != ''){//如果自定义原因为空，则取$status_custom的值

				// $data['content'] = $status_custom.'('.$username.')';	//自定义原因
				$data['content'] = $status_custom;	//自定义原因

			}else if($status_select != '2000' && $status_custom != ''){

				// $data['content'] = $arr_message[$status_select].'('.$username.')';
				$data['content'] = $arr_message[$status_select];

			}

			// 20161114 Jie 除了“已完成”以外，其他任意一个select的情况下，一旦勾选了“完成此单”，则mk_il_logs的物流状态必须为1003，物流信息则用select对应的文字说明；
			$data['status'] = ($befinished == 'on') ? 1003 : $status_select;

			$data['MKNO']        = $info['MKNO'];
			// $data['noid']     = $info['noid'];	// 20160316 Jie 不保存
			$data['create_time'] = date('Y-m-d H:i:s');
			$data['CID']         = $info['CID'];
			$data['opt_tname']   = $tname;//操作员真实姓名全显示 20161103 Jie

			$Model = M();   //实例化
        	$Model->startTrans();//开启事务

        	// $status_select是自定义的时候，无需更改tran_list.IL_state
			if($status_select != '2000') $Tran_data['IL_state']   = $data['status'];
			
			$Tran_data['ex_time']    = $data['create_time'];
			$Tran_data['ex_context'] = $data['content'];
				
			if(isset($data['content'])){
				$res = M('IlLogs')->add($data);	// 如果该单属于可操作范围，则进行新的数据记录保存
				$save = M('TranList')->where($g_map)->save($Tran_data);
			}

			//如果成功添加新记录
			if($res && $save !== false){

				//如果勾选了“完成此单”，则需要更改状态为1003
				if($befinished == 'on') $update = M('TranList')->where($g_map)->setField('IL_state','1003');	//将IL_state 更新为 已签收状态(1003)

				$Model->commit();//提交事务成功
				$backArr = array('status'=>1, 'msg'=>'操作成功');
				return $backArr;
			}else{
				$Model->rollback();//事务有错回滚
				$backArr = array('status'=>0, 'msg'=>'操作失败');
				return $backArr;
			}

    }

    /**
     * [_anyMore 快递跟踪 -- 手动完成多个订单 20160511 Jie]
     * @param  [type] $ids_arr       [数组]
     * @param  [type] $status_select [description]
     * @param  [type] $status_custom [description]
     * @param  [type] $befinished    [description]
     * @param  [type] $username      [description]
     * @param  [type] $arr_message   [description]
     * @param  [type] $tname         [操作员真实姓名全显示 20161103 Jie]
     * @param  [type] $any           [用于区分操作多个订单 20170222 Jie]
     * @return [type]                [description]
     */
    public function _anyMore($ids_arr,$status_select,$status_custom,$befinished,$username,$arr_message,$tname,$any=false){
    	$i = 0;	//记录执行成功个数
    	// $m = 0;	//记录不符合操作时间的个数
    	// $n = 0;	//记录已被执行完成的个数，即IL_state = 1003
    	foreach($ids_arr as $k=>$id){

    		$res[$k] = $this->_toPost($id,$status_select,$status_custom,$befinished,$username,$arr_message,$tname,$any);

    		if($res[$k]['status'] == 1) $i++;

    	}

    	if($i == 0){
    		$msg = '操作失败，'.(count($ids_arr)-$i).'个不满足操作要求';
    		$backArr = array('status'=>0, 'msg'=>$msg);
			return $backArr;
    	}else{
    		$msg = '操作成功，执行结果：'.$i.'个成功，<font style="color:red">'.(count($ids_arr)-$i).'个失败</font>';
    		$backArr = array('status'=>1, 'msg'=>$msg);
			return $backArr;
    	}
    	
    }

    /**
     * 导出文件 获取需要的数据
     * @param  [type] $noid [mk_tran_list.noid]
     * @return [type]       [description]
     */
    public function _export_file($noid, $type){

    	/* 检查一天前是否已经执行过文件导出  20160126 */
    	$oneDay = date('Y-m-d H:i:s',time()-86400);	//当前时间的一天前
    	$where['export_time'] = array('gt',$oneDay);	//距离当前时间1天之内
    	$check = M('ExportNotes')->where($where)->order('export_time desc')->find();

    	/* 20160123 */
    	//获取导出csv的模板类型，1为申通csv模式，2为顺丰csv模式
		$tranline = M('TransitNo n')->field('c.csv_type,c.email,n.no,c.id')->join('LEFT JOIN mk_transit_center c ON n.tcid = c.id')->where(array('n.id'=>$noid))->find();

    	/* 检查一天前是否已经执行过文件导出  20160126 */
    	if($check){
    		//如果导出文件类型是相同；否则执行一次完整的导出过程
			if($check['export_type'] == $type){
	    		$count = M('TranList l')->join('RIGHT JOIN mk_tran_order o ON o.lid = l.id')->where(array('noid'=>$noid))->count();
	    		//如果记录的数量 等于 实时查询的实际数量，则不再执行导出，直接发送原有的导出文件；否则，执行一次完整的导出过程
	    		if($check['export_nums'] == $count){
	    			return $arr = array($check['file_url'],'exist',$tranline);
	    			exit;
	    		}
			}

    	}

		// $fit = "0 as f0,l.id as f1,l.MKNO as f2,l.STNO as f3,o.detail as f4,o.catname as f5,o.price as f6,o.number as f7,(o.number * o.price) as f8,l.weight as f9,round((l.weight / 2.2046),4) as f10,l.receiver as f11,l.idno as f12,l.reAddr as f13,l.reTel as f14,l.postcode as f15,l.city as f16,l.auto_Indent1 as f17,l.auto_Indent2 as f18,l.sender as f19";

    	// 20170220 jie 去除---身份证号、收货地址、收件人电话、邮政编码
    	$fit = "0 as f0,l.id as f1,l.MKNO as f2,l.STNO as f3,o.detail as f4,o.catname as f5,o.price as f6,o.number as f7,(o.number * o.price) as f8,l.weight as f9,round((l.weight / 2.2046),4) as f10,l.receiver as f11,l.city as f12,l.auto_Indent1 as f13,l.auto_Indent2 as f14,l.sender as f15";
    	
    	/* 20160123 */
		$list = M('TranList l')
		->field($fit)
		->join('RIGHT JOIN mk_tran_order o ON o.lid = l.id')
		->where(array('noid'=>$noid))->select();
		
		return $arr = array($list,$tranline);
    }

    /**
     * 保存导出文件记录到mk_export_notes
     * @param  [type] $fileurl [description]
     * @param  [type] $extime  [description]
     * @param  [type] $count   [description]
     * @return [type]          [description]
     */
    public function export_notes_add($fileurl,$extime,$count,$type){
		$data['file_url']    = $fileurl;
		$data['export_time'] = $extime;
		$data['export_nums'] = $count;
		$data['export_type'] = $type;
    	M('ExportNotes')->add($data);
    }

    //以下方法是补录提单信息功能的 20180625xieyiyi

    //获取全部的提单信息
    public function ladingList($where,$p,$ePage)
    {
        $model = M('TransitLading');

        $list = $model->where($where)->order('created_time desc')->page($p.','.$ePage)->field('id,lading_no,take_off_time,arrive_time,gross_weight,net_weight,number,price,transport_no,zzState')->select();

        $count = $model->where($where)->count();

        return array('list'=>$list,'count'=>$count);
    }

    //获取所有可补录的批次号
    //$id(transit_lading表id)有值表示是编辑状态  要获取该提单信息下的批次号信息
    public function transitNos($line,$id='')
    {
        $no_model  = M('TransitNo');
        $lad_model = M('TransitLading');

        if($id){
            $where['_string'] = 'lading_id is null OR lading_id = '.$id;
        }else{
            $where['_string'] = 'lading_id is null';
        }

        $where['status'] = array(array('EGT',10),array('LT',60)) ;
        $where['tcid'] = $line;

        //先获取所有符合条件的批次号
        $nos_arr = $no_model->where($where)->order('date desc')->field('id,no,lading_id')->select();

        return $nos_arr;
    }

    //判断提单号是否存在
    //有$id传入  代表是编辑  要判断是否是原来的提单号  不是 再去判断是否重复
    public function lading_nos($lading_no,$id='')
    {
        $model = M('TransitLading');

        if($id){
            $yuan_lading_no = $model->where(array('id'=>$id))->getField('lading_no');
            if($yuan_lading_no == $lading_no){
                return true;
            }
        }

        $where['lading_no'] = $lading_no;
        $info = $model->where($where)->find();

        if(empty($info)){
            return true;
        }else{
            return false;
        }
    }

    /**
     * 添加提单
     * @param  array  $lad_data  要写入transit_lading表的内容
     * @param  array  $log_data  要写入transit_lading_log表的内容
     * @param  array  $nos_arr   批次号id数组
    */
    public function lading_add($lad_data,$log_data,$nos_arr)
    {
        $lad_model = M('TransitLading');
        $log_model = M('TransitLadingLog');
        $no_model  = M('TransitNo');

        $str = '';
        foreach ($lad_data as $k=>$v){
            $str .= $k.'：'.$v.'；';
        }

        $str .= '批次号：';
        foreach ($nos_arr as $v) {
            $str .= $v.'/';
        }

        $log_data['content'] = $str;
        $log_data['type'] = '添加';

        //TransitNo表的更新条件
        $nos_str = implode(',',$nos_arr);
        $nos_where['id']  = array('in',$nos_str);

        M()->startTrans();
        $lad_add = $lad_model->add($lad_data);
        $log_add = $log_model->add($log_data);
        $no_save = $no_model->where($nos_where)->save(array('lading_id'=>$lad_add));

        if($lad_add && $log_add && $no_save){
            M()->commit();
            return array('state'=>'yes','msg'=>'补录成功');
        }else{
            M()->rollback();
            return array('state'=>'no','msg'=>'补录失败');
        }
    }

    /**
     * 编辑提单
     * @param  int    $id        transit_lading表id
     * @param  array  $lad_data  要写入transit_lading表的内容
     * @param  array  $log_data  要写入transit_lading_log表的内容
     * @param  array  $nos_arr   批次号id数组
     */
    public function lading_edit($id,$lad_data,$log_data,$nos_arr)
    {
        $where['id'] = $id;

        //判断提单id是否正确
        $judge_res = $this->lading_info($where);
        if($judge_res === false){
            return array('state'=>'no','msg'=>'查询数据失败');
        }else if($judge_res['zzState'] > 0){
            return array('state'=>'no','msg'=>'该提单不可编辑');
        }

        $lad_model = M('TransitLading');
        $log_model = M('TransitLadingLog');
        $no_model  = M('TransitNo');

        $lad_data['updated_time'] = date('Y-m-d H:i:s',time());

        $str = '';
        foreach ($lad_data as $k=>$v){
            $str .= $k.'：'.$v.'；';
        }

        $str .= '批次号：';
        foreach ($nos_arr as $v) {
            $str .= $v.'/';
        }

        $log_data['content'] = $str;
        $log_data['type'] = '编辑';

        //TransitNo表的更新条件
        $nos_str = implode(',',$nos_arr);
        $nos_where['id']  = array('in',$nos_str);

        M()->startTrans();
        $lad_save = $lad_model->where($where)->save($lad_data);
        $log_add  = $log_model->add($log_data);
        $no_save1 = $no_model->where(array('lading_id'=>$id))->save(array('lading_id'=>null));
        $no_save2 = $no_model->where($nos_where)->save(array('lading_id'=>$id));

        if($lad_save && $log_add && $no_save1 && $no_save2){
            M()->commit();
            return array('state'=>'yes','msg'=>'补录成功');
        }else{
            M()->rollback();
            return array('state'=>'no','msg'=>'补录失败');
        }
    }

    //获取一条提单详情
    public function lading_info($where)
    {
        $lad_model = M('TransitLading');
        $no_model  = M('TransitNo');

        $info = $lad_model->where($where)->find();
        if(! empty($info)){
            $info['no'] = $no_model->where(array('lading_id'=>$where['id']))->field('id,no')->select();
            $info['no_id'] = array_column($info['no'], 'id');
            return $info;
        }else{
            return false;
        }
    }

    //判断线路
    public function TranKd($id)
    {
        $where['id'] = $id;
        $where['status'] = 1;
        $center = M('TransitCenter')->where($where)->find();
        if(empty($center)){
            return false;
        }else{
            return $center;
        }
    }
}