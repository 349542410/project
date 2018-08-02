<?php
/**
 * 物流称重、入库、中转 服务器端
 * 服务器放 App3/Api
 */
namespace Api\Controller;
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

		$user = M('OperatorList')->where(array('username'=>$uname,'userpwd'=>$upass))->find();	//查询用户信息

		//如果此用户是存在的
		if($user){

			//校验密码正确性
			if($upass != $user['userpwd']){
				$data['code']    = 0;
				$data['codestr'] = '密码错误';     //提示信息
	            return $data;
	            exit;
			}else if($upass == $user['userpwd']){

				$data['code']    = 1;
				$data['codestr'] = '登陆成功';     //提示信息
				$data['user'] = $user;
	            return $data;
	            exit;

			}

		}else{
			$data['code']    = 0;
			$data['codestr'] = '账户不存在或被禁用';     //提示信息
            return $data;
            exit;
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
		//Man 检查时间是否为当天 ，非当天不能使用
		if(substr($list['date'],0,10)<>date('Y-m-d')){
			$data['codestr'] = '批次号已过期,请重新获取';
			return $data;
		}
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

	// 2017-08-08 jie  检查是属于MKNO还是STNO，如果是STNO，需要根据STNO查到MKNO，返回MKNO以供使用
	public function check_MKNO($MKNO){
        //验证订单号是否MK开头  20170707 jie
        if(!preg_match("/^(MK)\w{0,}$/",$MKNO)){
            //先从tran_list表中通过STNO查出MK号码后，再查物流信息
            if(strlen($MKNO) > 10){
                $MKNO = M('TranList')->where(array('STNO'=>$MKNO))->getField('MKNO');
            }else{
                return false;//既不是MKNO也不是STNO，无法查询数据，因此直接返回false
            }
        }

        return $MKNO;
	}
}