<?php
/**
 * 物流称重、入库、中转 服务器端
 * 服务器放 App3/Api
 */
namespace AUApi\Controller;
use Think\Controller\HproseController;
class MkilController extends HproseController{
	protected $crossDomain =	true;
	protected $P3P		   =    true;
	protected $get		   =    true;
	protected $debug 	   =    true;

	/**
	 * 查询用户信息
	 * @param  [type] $uname [description]
	 * @return [type]        [description]
	 */
	public function get_user($uname,$upass){

		$upass = md5($upass);	//密码加密

		$user = M('OperatorList')->where(array('username'=>$uname))->find();	//查询用户信息

		//检查账户是否被禁用
		if($user['status'] == '0'){
			$data['code']    = 0;
			$data['codestr'] = '账户已被禁用';     //提示信息
		    return $data;
		}

		//如果此用户是存在的
		if($user){

			//校验密码正确性
			if($upass != $user['userpwd']){
				$data['code']    = 0;
				$data['codestr'] = '密码错误';     //提示信息
	            return $data;
			}else{
				$data['code']    = 1;
				$data['codestr'] = '登陆成功';     //提示信息
				$data['user'] = $user;
	            return $data;
			}

		}else{
			$data['code']    = 0;
			$data['codestr'] = '账户不存在或被禁用';     //提示信息
            return $data;
		}

	}
	//20151215分析扫描的新型中转单号+批号，如果正确则返回{物流公司id-name,中转单号id,no,中转批号id,no}
	//20151222使用TransitNo一个数据库，只操作批次号，因不能一板板地确认装货，所以按批处理
	public function anacode_old($code){ //请看后面的
		$code 			 = trim($code);
		$data['code']    = 0;
		$data['codestr'] = '条码不正确'.$code;     //提示信息
		if($code == '') return $data;
		if(substr($code,0,1)<>'T' || strpos($code,'-')<0) return $data;
		$a 				 = explode('-', $code);
		$ac 			 = count($a);
		$dno 			 = trim($a[0]);
		$pno 			 = trim($a[1]);
		if($dno=='' || $pno==''){
			return $data;
		}
		$map  = array('no'=>$dno);
		$list = M('TransitNo')->where($map)->find();
		$data['codestr'] = '条码不存在';
		if(!$list) return $data;
		/*/Man 检查时间是否为当天 ，非当天不能使用
		if(substr($list['date'],0,10)<>date('Y-m-d')){
			$data['codestr'] = '条码已过期,请重新获取';
			return $data;
		}*/
		//Man 分析 TranKd 是否附合
		$tcid 	= $list['tcid']*1;  //tcid应与 tran_list.TranKd相同,应正扫描美快单号时验证
		if($tcid<1){
			$data['codestr'] = '线路基本资料有误';
			return $data;
		}

		//$data['codestr'] = json_encode($list);

		$nid  = $list['id']; // no.id
		$tcid = $list['tcid']; // no.tcid 线路id

		$map  = array('nid'=>$nid,'no'=>$pno);
		$list2= M('TransitNo2')->where($map)->find();
		if(!$list2) return $data;
		$n2id = $list2['id'];

		$map 	= array('id' => $tcid);
		$tcinfo = M('TransitCenter')->where($map)->find();
		if(!$tcinfo) return $data;

		if($tcinfo['status']<>1){
			$data['codestr'] = '线路已被禁用';
			return $data;
		}

		//{线路id-name,中转单号id,no,中转批号id,no}
		$data = array(
			'code'		=> 1,
			'codestr'	=> '000',
			'nid' 		=> $nid,
			'nno' 		=> $list['no'],
			'n2id' 		=> $n2id,
			'n2no'  	=> $list2['no'],
			'tcid' 		=> $tcid, 			//用于与TranKd 比较 要相同，以防止发错货
			'tcto' 		=> $tcinfo['toname'],
			'tctransit' => $tcinfo['transit'],
		);
		return $data;

	}
	//20151222分析扫描的新型批号，如果正确则返回{物流公司id-name,批号id,no}
	public function anacode($code){
		$code 			 = trim($code);
		$data['code']    = 0;
		$data['codestr'] = '批次号不正确'.$code;     //提示信息
		if($code == '') return $data;
		if(substr($code,0,1)<>'T') return $data;
		$map  			 = array('no'=>$code);
		$list 			 = M('TransitNo')->where($map)->find();
		$data['codestr'] = '批次号不存在';
		if(!$list) return $data;

		// //Man 检查时间是否为当天 ，非当天不能使用	20180503 jie 暂时不做时间限制
		// if(substr($list['date'],0,10)<>date('Y-m-d')){
		// 	$data['codestr'] = '批次号已过期,请重新获取';
		// 	return $data;
		// }

		//Man 160115 增加stauts>9则不能再使用
		if($list['status']>9){
			$data['codestr'] = '批次号'.$code.'已完成,请重新获取';
			return $data;
		}
		//Man 分析 TranKd 是否附合
		$tcid 	= $list['tcid']*1;  //tcid应与 tran_list.TranKd相同,应正扫描美快单号时验证
		if($tcid<1){
			$data['codestr'] = '线路基本资料有误';
			return $data;
		}

		$map 	= array('id' => $list['tcid']);
		$tcinfo = M('TransitCenter')->where($map)->find();
		if(!$tcinfo) return $data;

		if($tcinfo['status']<>1){
			$data['codestr'] = '线路已被禁用';
			return $data;
		}

		//{线路id-name,中转单号id,no,中转批号id,no}
		$data = array(
			'code'		=> 1,
			'codestr'	=> '000',
			'id' 		=> $list['id'],			// no.id
			'no' 		=> $list['no'],			// no.no 批次号
			'name'		=> $tcinfo['name'],		// 线路名称
			'tcid' 		=> $list['tcid'], 		// tran_center.id用于与TranKd 比较 要相同，以防止发错货
			'accno'		=> $list['accno'],		// 到货确认号，暂用于 保存到 中转单号中
			//'tcto' 		=> $tcinfo['toname'], //直接显示name即可
			//'tctransit' => $tcinfo['transit'],
		);
		return $data;

	}	

	/**
	 * 2017-08-08 jie  检查是属于MKNO还是STNO，如果是STNO，需要根据STNO查到MKNO，返回MKNO以供使用
	 * @param  [type] $MKNO     [description]
	 * @param  string $usertype [当是 30（中转） 的时候，则需要额外的两步验证]
	 * @param  string $noid     [批次号id]
	 * @param  string $tcid     [线路id]
	 * @return [type]           [description]
	 */
	public function check_MKNO($MKNO, $usertype='', $noid='', $tcid=''){
        //验证订单号是否MK开头  20170707 jie
        if(!preg_match("/^(MK)\w{0,}$/",$MKNO)){
            //先从tran_list表中通过STNO查出MK号码后，再查物流信息
            if(strlen($MKNO) > 10){
            	$where['STNO']  = array('eq', $MKNO);
				$where['traceCode']  = array('eq',$MKNO);
				$where['_logic'] = 'or';
                $MKNO = M('TranList')->where($where)->getField('MKNO');

                if(!$MKNO){
                	return array('state'=>'no', 'msg'=>'错误的运单号或美快单号');//无法查询数据
                }
            }else{
                return array('state'=>'no', 'msg'=>'运单号格式或长度有误');//既不是MKNO也不是STNO，无法查询数据
            }
        }

		//验证重复运单
		$stnoWhere['STNO']  = array('eq', $MKNO);
		$stnoWhere['traceCode']  = array('eq',$MKNO);
		$stnoWhere['_logic'] = 'or';
		$stnoIf = M('TranList')->where(array('MKNO'=>$MKNO))->field('STNO')->find();
		$stnoCount = M('TranList')->where(['STNO' => $stnoIf['STNO']])->field('STNO')->select();
		if(count($stnoCount) > 1){
			return array('state'=>'no', 'msg'=>'该运单重复');
		}

        // 中转操作 的时候，还需要以下2个校验
        if($usertype == 30){

	        //读取本快递单号对应的收件人信息 1.身份证号 2.地址 3.电话
	        //分析所在批次号中，是否存在与上述3项中任意一项相同的。
	        $check_info_same = $this->_check_info_same($noid, $MKNO, $tcid);
	        if($check_info_same !== true){
	        	return $check_info_same;
	        }

			//验证身份证号，及身份证照片
			$info = M('TranUlist')->where(array('MKNO'=>$MKNO))->field('TranKd,id_img_status,id_no_status,id')->find();
			$center = M('transit_center')->field('taxthreshold,input_idno,member_sfpic_state')->where(['id' => $info['TranKd']])->field('input_idno,member_sfpic_state,id')->find();
			if(($center['input_idno'] == 1 || $center['member_sfpic_state'] == 1) &&
				((int)$info['id_img_status'] < 100 || (int)$info['id_no_status'] < 100)){
				return array('state'=>'no','msg'=>'请上传身份证信息', 'lng'=>'identity_id');
			}

	        // 分析 如果当前扫描件为收件人上传证照，但未上传的
	        $order_id_img = $this->_order_id_img($MKNO);
	        if($order_id_img !== true){
	        	$order_id_img['msg'] = '等待收件人上传证照';
	        	return $order_id_img;
	        }
        }

        return $MKNO;
	}

	//中转到旧金山
	public function transferSanFrancisco($mkno, $tname){
		// 订单信息
		$info = M('TranList')->field('noid,idno,reTel,reAddr,TranKd,IL_state,receiver,pause_status')->where(array('MKNO'=>$mkno))->find();
		//检测是否已经停运
		if((int)$info['pause_status'] == 20){
			return array('state'=>'no','status_content' => '已停运', 'msg'=>'该包裹已停运');
		}
		$order_id_img = $this->_order_id_img($mkno);
		if($order_id_img !== true){
			return array('state'=>'no','status_content' => '等待收件人上传证照', 'msg'=>'等待收件人上传证照');
		}
		//更改
		$where['MKNO'] = $mkno;
		$data['IL_state'] = 19;
		$data['ex_time'] = date("Y-m-d H:i:s");
		$data['ex_time'] = '已离开  '.$tname.' ，发往 旧金山';
		M('TranList')->where($where)->data($data)->save();
		//增加物流信息
		$dataAdd['MKNO'] = $mkno;
		$dataAdd['content'] = '已离开  '.$tname.' ，发往 旧金山';
		$dataAdd['create_time'] = date("Y-m-d H:i:s");
		$dataAdd['status'] = 19;
		$dataAdd['mantime'] = date("Y-m-d H:i:s");
		$result = M('il_logs')->add($dataAdd);
		if($result){
			return array('state'=>'yes','status_content' => '发往 旧金山 成功', 'msg'=>'发往 旧金山 成功');
		}else{
			return array('state'=>'no','status_content' => '发往 旧金山 失败', 'msg'=>'发往 旧金山 失败');
		}
	}

	/**
	 * [_check_info_same 校验 本快递单号(美快单号)中对应的收件人信息 1.身份证号 2.地址 3.电话 3种资料信息是否存在重复]
	 * 其中，1.身份证号   是根据线路配置的实际情况 来加入 判断
	 * @param  [type] $MKNO [美快单号]
	 * @return [type]       [description]
	 */
	public function _check_info_same($noid, $MKNO, $tcid){

		// 线路配置信息
		$center = M('TransitCenter')->field('input_idno,member_sfpic_state,repeatsnum,status')->where(array('id'=>$tcid))->find();

		// 订单信息
        $info = M('TranList')->field('noid,idno,reTel,reAddr,TranKd,IL_state,receiver,pause_status')->where(array('MKNO'=>$MKNO))->find();

        if($center['status'] == '0'){
        	return array('state'=>'no', 'msg'=>'该中转线路已被禁用');
        }

        // 验证中转线路 和 订单中的 线路 是否一致
        if($info['TranKd'] != $tcid){
        	return array('state'=>'no', 'msg'=>'中转线路不一致');
        }

		//检测是否已经停运
		if((int)$info['pause_status'] == 20){
			return array('state'=>'no','status_content' => '已停运', 'msg'=>'该包裹已停运');
		}

/*        // 判断是否已经在其他中转号执行了中转
        if($info['noid'] != '0'){
        	return array('state'=>'no', 'msg'=>'已被其他中转号收纳');
        }*/

        //检测是否已经执行了中转
        if($info['IL_state'] == '20'){
            $noName = M('transit_no')->field('no')->where(['id' => $noid])->find();
        	return array('state'=>'no','status_content' => '已中转', 'msg'=>'已中转到' . $noName['no'] . '批次');
        }

        // 如果【允许重复件数】 > 0，则需要按照具体设定的数值进行检验； =0 或者 <0  暂时都按照 不作限制 处理
        if($center['repeatsnum'] > 0){

	        $map = array();
	        // 身份证号码必填 或者 身份证图片必须上传的时候，则需要验证身份证号码
	        if(((int)$center['input_idno'] == 1) || ((int)$center['member_sfpic_state'] == 1)) $map['idno']   = array('eq',$info['idno']);
            $map['idno']  = array('eq',$info['idno']);//验证身份证号
            $map['receiver']  = array('eq',$info['receiver']);//收件人姓名
			$map['reTel']  = array('eq',$info['reTel']);//收件人电话
			$map['reAddr'] = array('eq',$info['reAddr']);//收件人地址
			$map['_logic'] = 'or';

	        $where['_complex'] = $map;
			$where['idno']   = array('gt',8);//除了erp单
            $where['MKNO']   = array('neq',$MKNO);//除了此单以外
			$where['noid']   = array('eq',$noid);//同一批次号
            //"select * from mk_tran_list where idno>8 and MKNO='MK883595276US'";

            \Think\Log::write('身份证号码条件' . json_encode($where, 320));
			$check = M('TranList')->where($where)->count();
            \Think\Log::write('身份证号码必填' . M('TranList')->getLastSql());
			// return array('state'=>'no', 'msg'=>$check);

			if($check >= $center['repeatsnum']) return array('state'=>'no', 'status_content' => '重复件', 'msg'=>'资料重复数超过【'.$center['repeatsnum'].'】限制');
        }



		return true;
	}

	/**
	 * [_order_id_img 校验 本快递单号(美快单号)中对应的收件人身份证照片状态是否正常]
	 * @param  [type] $MKNO [美快单号]
	 * @return [type]       [description]
	 */
	public function _order_id_img($MKNO){
        $info = M('tran_ulist l')->field('l.idno,l.id_img_status,t.member_sfpic_state,t.input_idno')->join('mk_transit_center t on t.id = l.TranKd')->where(array('MKNO'=>$MKNO))->find();

        if(!$info){
        	return true;
        }
        
        $CheckIdInfo = new \AUApi\Controller\CheckIdInfoController();

        return $CheckIdInfo->check_id($info);

	}

	/**
	 * 必须先中转才能转发快递
	 * IL_state 包含：
	 * 中转：16,20
	 * 转发快递：200
	 * @param  [type] $MKNO [description]
	 * @return [type]       [description]
	 */
	public function _check_state($MKNO){
		$info = M('TranList')->field('IL_state,pause_status,id')->where(array('MKNO'=>$MKNO))->find();

		//验证物流状态是否为60转发网络问题状态
		if((int)$info['IL_state'] == 60){
		    $data['IL_state'] = 20;
            M('TranList')->where(array('MKNO'=>$MKNO))->save($data);
            \Think\Log::write('更改60转发快递网络状态' . M('TranList')->getLastSql());
            $info = M('TranList')->field('IL_state,pause_status,id')->where(array('MKNO'=>$MKNO))->find();
        }
        //停运状态
        if((int)$info['pause_status'] == 20){
            $log = M('il_logs')->where(array('MKNO'=>$MKNO, 'status'=>400))->find();
            if((int)$log['status'] == 400){
                $saveData['ex_time'] = $log['create_time'];
                $saveData['ex_context'] = $log['content'];
                $saveData['IL_state'] = 400;
                M('TranList')->where('id=' . $info['id'])->data($saveData)->save();
                \Think\Log::write('停运=======记录状态：ID' . $info['id'] . json_encode($saveData, 320));
            }
            return array('state'=>'no', 'msg'=>'该件已停止转运，请退回发货人');
        }

		if($info['IL_state'] < 200){
			if($info['IL_state'] == '16' || $info['IL_state'] == '20' || $info['IL_state'] == 16 || $info['IL_state'] == 20){
				return array('state'=>'yes', 'msg'=>'通过');
			}else{
				return array('state'=>'no', 'msg'=>'需先中转才能转发快递');
			}
		}else{
			return array('state'=>'no', 'msg'=>'该单已被快递公司承运');
		}
	}

}