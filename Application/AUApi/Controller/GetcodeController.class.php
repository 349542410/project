<?php
/**
 * 软件打印中转单号与批号的api,需存放在MK_API中
 * create 20151203
 * Man 20151216 有保存数据的，需要保存成功才能返回。未完成，需修改。  // Jie 20151218 已完成修改
 * 根据 美快中转条码管理PHP_V2.txt 编写
 * Man20160620 在getcode中将返回的code2设为中转记录的id,目的在 前台验证时双重认证确资料准确(未更新)
 */
namespace AUApi\Controller;
use Think\Controller\RestController;
class GetcodeController extends RestController{

	function _initialize(){
		$jn = new \Org\MK\JSON;
		$js = $jn->get();

		//test
		//sleep(2);

		/** 测试
		// $log_str = '{"do":"login","usercode":"a123456","userpwd":"894c925e9616baf4484f6fccbf9013c0","from":"mgsoft"}';
		// $log_str = '{"do":"setAir","dno":"THK151204142530-151204165603","airid":"0","airno":"ST332654421284"}';	//测试
		// $js = json_decode($log_str,true);
		End */
				
		if(!is_array($js)){
			//返回错误
			$backArr = array('code'=>'401','sms'=>'非法数据');
			$this->ajaxReturn($backArr);
			die;
		}
		if(!isset($js['do']) || trim($js['do']) == ''){
			//返回错误
			$backArr = array('code'=>'402','sms'=>'参数为空或不存在');
			$this->ajaxReturn($backArr);
			die;
		}
		$this->jstr 	= $js; //其它function就直接读取这个

		//如果请求为login，则进去登录的验证处理
		if(trim($js['do']) == 'login'){

			$this->login();

		}else{	//如果不是，则分析验证登录状态

			$value = session('auth_item');

			//如果session验证失败
			if(!$value || $value['isLoged'] != 'passed' || empty($value['ccid']) || empty($value['ccname']) || empty($value['cctname'])){
				//$this->redirect('login');
				$backArr = array('code'=>'404','sms'=>'登录超时');
				$this->ajaxReturn($backArr);
				die;
			}
			//151216增加时区
			$this->timezone = 8;
			if(isset($js['timezone'])){
				$tmp = $js['timezone']*1;
				if($tmp>-14 && $tmp<14) $this->timezone = $tmp;
			}

			//$this->$js['do']();	//跳转到相应的请求
			call_user_func(array(self, $js['do']),''); //160815 Man php7 no support ->$
			die;	//必须添加此终结，否则会继续运行到index方法
		}
	}

	public function index(){
		// $this->log_str = $_POST['DATA'];
		// $do ->login();
		
		echo 'Hello';
	}

	/**
	 * 登陆   使用mk_operator_list
	 * @return [type] [description]
	 */
	public function login(){

		//收到
		//$log_str = $this->jstr; //$_POST['DATA'];
		// $log_str = '{"do":"login","usercode":"a123456","userpwd":"894c925e9616baf4484f6fccbf9013c0","from":"mgsoft"}';  //测试
		
		$log_state = $this->jstr; //json_decode($log_str,true);

		// 验证登录方式
		if(trim($log_state['from']) != 'mgsoft' && trim($log_state['do']) != 'login'){
			$result = array('code'=>'400','sms'=>'非法登陆');
			$this->ajaxReturn($result);
		}

		if(trim($log_state['usercode']) == ''){
			$result = array('code'=>'403','sms'=>'用户名不能为空');
			$this->ajaxReturn($result);
		}

		if(trim($log_state['userpwd']) == ''){
			$result = array('code'=>'405','sms'=>'密码不能为空');
			$this->ajaxReturn($result);
		}

		$userpwd = md5(trim($log_state['userpwd']));
		//验证用户是否存在或已被禁用
		$checkLog = M('OperatorList')->where(array('username'=>trim($log_state['usercode'])))->find();

		if($checkLog && $checkLog['status'] == '1'){

			if($userpwd != $checkLog['userpwd']){
				$result = array('code'=>'406','sms'=>'密码错误');
				$this->ajaxReturn($result);
			}

			if($checkLog['usertype'] != '30'){
				$result = array('code'=>'406','sms'=>'您没有中转权限无法登录');
				$this->ajaxReturn($result);
			}

			$result = array('code'=>'1','sms'=>'验证通过');

			//生成session
			$auth_item = array(
					'ccid'     => $checkLog['id'],
					'ccname'   => $checkLog['username'],
					'cctname'  => $checkLog['truename'],
					'isLoged'  => 'passed',
				);
			session('auth_item',$auth_item);	//session  End

			$this->ajaxReturn($result);	//返回
		}else{
			$result = array('code'=>'0','sms'=>'用户不存在或已被禁用');
			$this->ajaxReturn($result);
		}
	}

	/**
	 * 获取中转中心列表  mk_transit_center
	 * @return [type] [description]
	 */
	public function getCodeKd(){

		//接收到
		// $str = $_POST['DATA'];
		// $str = '{"do":"getCodeKd"}';	//测试
		$arr = $this->jstr;//json_decode($str,true);

		if(trim($arr['do']) == 'getCodeKd'){

			//获取中转中心列表
			$list = M('TransitCenter')->field('id,name')->where(array('status'=>'1'))->select();
			$backArr = array(
					'code' =>'1',
					'sms'  =>'成功获得中转列表',
					'data' =>$list
				);
			$this->ajaxReturn($backArr);
		}
	}

	/**
	 * 获取中转单号   mk_transit_no   // Jie 20151222 根据文档：美快中转条码管理PHP_V2.txt  修改
	 * @return [type] [description]
	 */
	public function getOrder(){

		// 接收到
		// $str = $_POST['DATA'];
		// $str = '{"do":"getOrder","id":"2","dno":""}';	//测试
		// $arr = json_decode($str,true);
		$arr = $this->jstr;

		// 如果dno为空
		if((!isset($arr['dno'])) || trim($arr['dno']) == ''){

			//读取当天是否生成过 no (no.status < 10才算  20160108)
			// $map['tn.date']      = array('egt',date('Y-m-d'));			//当前实际时间日期			
			$map['tn.tcid']      = array('eq',trim($arr['id']));		//此处传过来的id = mk_transit_center.id
			$map['tn.status'] 	 = array('lt',10);						//no.status < 10才算  20160108  Jie
			$map['tn.createid']  = array('eq',session('auth_item.ccid'));						//当前操作员  20180404  Jie
			$list 				 = M('TransitNo tn')->field('tn.*,tc.name')->where($map)->join('LEFT JOIN mk_transit_center tc ON tc.id = tn.tcid')->order('date desc')->find();	//获取最新的一个no用作返回,线路名称

			//如果有 则返回
			if(count($list) > 0){
				//151216时区 Man
				$ldate 	 = $this->zonetime($list['date']);
				//20160108Man改为 只有继续 没有是与否选择，如果想打印新的中转批号，先将原有批号进入补录资料后，当天可自动生成新的中转单号。
				$str 	 = "\r\n注意\r\n\r\n系统发现已用批次号\"$list[no]\"！！
							\r\n当地时间 $ldate 打印过批次号 \"$list[no]\" 共 $list[pnum] 张标签
							现在你要打印的标签是否也是这个批次号的？
							\r\n如果选择\"否\"将会生成新的批次号
							\r\n说明：批次号需与航空单号一一对应，并于航空发货后补录到系统中";
				$str 	 = "\r\n注意\r\n\r\n系统发现已用批次号\"$list[no]\"！！".
							"\r\n\r\n当地时间 $ldate 打印过批次号 \"$list[no]\" 共 $list[pnum] 张标签。".
							"\r\n\r\n现在将会继续为您打印的标签\"$list[no]\"。".
							"\r\n\r\n如果您想打印新的批次号，请到‘补充航空资料’中为批次号\"$list[no]\"补充好航空资料后，重新点击左边的线路即可生成新的批次号".
							"";
				$backArr = array(
						'id'   => $arr['id'],
						'lname'=> $list['name'],	//线路名称   20151228
						'dno'  => $list['no'],
						'num'  => $list['pnum'], 	// 已打印的数量
						'kd'   => '1',
						'code' => '1',
						'sms'  => $str,//现在准备打印的是否与'."\r".'你当地时间['.$ldate.']生成的<'.$list['no'].'>同一个航班？'.$list['pnum']
					);
				$this->ajaxReturn($backArr);

			}else{		//如果没有，则新生成一个 再返回
				
				$dno = $this->no1(trim($arr['id']));	//mk_transit_center.id
				$ctime = date('Y-m-d H:i:s');	//no 生成的时间

				$strback = M('TransitCenter')->where(array('id'=>trim($arr['id'])))->find();	//获取线路名称

				// 将新建的no保存到数据表中
				$data['tcid']     = trim($arr['id']);
				$data['date']     = $ctime;
				$data['no']       = $dno;
				$data['createid'] = session('auth_item.ccid');	//当前登录的mk_operator_list.id

				//Jie 20151222,暂时不使用no2,直接将相关资料保存到 no中
				$data['accno']    = '';	
				$data['airid']    = ''; 
				$data['airno']    = ''; 
				$data['pnum']     = ''; 
				$data['bnum']     = ''; 
				//==

				$res = M('TransitNo')->add($data);	//保存到数据表

				if($res){	//保存成功

					$backArr = array(
						'id'   =>$arr['id'],
						'lname'=> $strback['name'],	//线路名称   20151228
						'dno'  =>$dno,	//二维数组
						'kd'   =>'0',
						'code' =>'1',
						'sms'  =>'成功新建一个dno并保存'
					);

				}else{	//保存失败
					$backArr = array(
						'id'   =>$arr['id'],
						'lname'=> $strback['name'],	//线路名称   20151228
						'dno'  =>$dno,	//二维数组
						'kd'   =>'0',
						'code' =>'1',
						'sms'  =>'成功新建一个dno但未能保存'
					);
				}

				$this->ajaxReturn($backArr);
			}
			
		}else{	//如果dno不为空

			$checkIt = M('TransitNo')->where(array('no'=>trim($arr['dno'])))->find();

			//如果不存在 则返回错误
			if(!$checkIt){

				$backArr = array('code'=>'0','sms'=>'参数dno不存在');
				$this->ajaxReturn($backArr);

			}else{	//如果存在，则生成一个 再返回
				
				// $strback = M('TransitNo tn')->field('tn.*,tc.name')->where(array('no'=>trim($arr['dno']))->join('LEFT JOIN mk_transit_center tc ON tn.tcid = tc.id')->order('date desc')->find();	//获取航空公司名称，打印数量

				$dno = $this->no1($arr['id']);	//mk_transit_center.id

				$ctime = date('Y-m-d H:i:s');	//no 生成的时间

				$strback = M('TransitCenter')->where(array('id'=>trim($arr['id'])))->find();	//获取线路名称

				// 将新建的no保存到数据表中
				$data['tcid']     = trim($arr['id']);
				$data['date']     = $ctime;
				$data['no']       = $dno;
				$data['createid'] = session('auth_item.ccid');	//当前登录的mk_operator_list.id

				//Jie 20151222,暂时不使用no2,直接将相关资料保存到 no中
				$data['accno']    = '';
				$data['airid']    = '';
				$data['airno']    = '';
				$data['pnum']     = '';
				$data['bnum']     = '';
				
				$res = M('TransitNo')->add($data);	//保存到数据表

				if($res){	//保存成功

					$backArr = array(
						'id'   =>$arr['id'],
						'lname'=> $strback['name'],	//线路名称   20151228
						'dno'  =>$dno,	//
						'kd'   =>'0',
						'code' =>'1',
						'sms'  =>'成功新建一个dno并保存'
					);

				}else{		//保存失败

					$backArr = array(
						'id'   =>$arr['id'],
						'lname'=> $strback['name'],	//线路名称   20151228
						'dno'  =>$dno,	//
						'kd'   =>'0',
						'code' =>'1',
						'sms'  =>'成功新建一个dno但未能保存'
					);

				}

				$this->ajaxReturn($backArr);
			}
		}
	}

	/**
	 * 打印批次号
	 * @return [type] [description]
	 */
	public function getCode(){

		//收到
		// $str = '{"do":"getCode","dno":"THK063","num":"3"}';		//测试
		// $arr = json_decode($str,true);
		$arr = $this->jstr;

		if(trim($arr['dno']) == ''){
			$backArr = array('code'=>'402','sms'=>'参数dno不能为空');
			$this->ajaxReturn($backArr);
		}

		if(trim($arr['num']) == '' || trim($arr['num']) == 0 || !is_numeric(trim($arr['num']))){
			$backArr = array('code'=>'402','sms'=>'请输入数字num');
			$this->ajaxReturn($backArr);
		}

		$checkIt = M('TransitNo')->where(array('no'=>trim($arr['dno'])))->find();

		//先检查dno(mk_transit_no.no)是否存在,不存在则返回错误
		if(!$checkIt){
			$backArr 		= array('code'=>'0','sms'=>'参数dno不存在');
			$this->ajaxReturn($backArr);
		}else{	//如果存在

			$info = M('TransitCenter')->where(array('id'=>$checkIt['tcid']))->find();

			$cur_time 		= date('Y-m-d H:i:s');

			//返回多时区 20151216 Man
			$tz 			= $this->timezone;
			$cur_time_b	 	= 'UTC '.$tz.' : '.$this->zonetime($cur_time).(($tz==8)?'':(',中国 : '.$cur_time));

			$num = intval(trim($arr['num']));	//json.num
			$increase = M('TransitNo')->where(array('id'=>$checkIt['id']))->setInc('pnum',$num); // 更新打印数量  打印数量加json.num

			//更新打印数量成功
			if($increase !== false){
				
				$airname = M('ExpressCompany')->where(array('id'=>$info['airid']))->getField('company_name');	//航空公司名称
				$list 	 = array();

				//如果accno不为空，则取原有的accno作返回 20160107 Jie
				if(strlen($checkIt['accno']) > 2){
					$accno 		= $checkIt['accno'];
					$result 	= true;
				}else{
					$accno 	 	= $this->accno();	//获取
					//保存accno字段信息  Jie 20151222
					$data['accno'] = $accno;
					// $data['airid'] = $info['airid'];	//航空公司id
					$result = M('TransitNo')->where(array('id'=>$checkIt['id']))->save($data);
				}

				for($i=0;$i<$arr['num'];$i++){
					//生成ajax需要返回的数组
					$list[$i]['code1']   	= trim($arr['dno']);
					// $list[$i]['code2']   = $no2; //160620补充:这个原来的板号，后来板号取消了，就不用了
					$list[$i]['code2']   	= $checkIt['id']; //160620改为批号的记录的id,批量到货时通过id与dno双重方法确保传来资料的准确性
					$list[$i]['code3']   	= $accno;
					$list[$i]['time']    	= $cur_time_b; 			//返回详细时间
					$list[$i]['bqty']    	= intval($checkIt['pnum'])+$i+1; //上次pnum+1 到 pnum+json.num
					$list[$i]['to']      	= $info['toname'];
					$list[$i]['airname'] 	= $airname;//'国泰';	//航空公司
					$list[$i]['transit'] 	= $info['transit'];
					$list[$i]['lname'] 		= $info['name'];		//线路名称
				}
				
				if($result !== false){
					$backArr = array(
						'code' =>'1',
						'sms'  =>'成功打印'.$num.'个批次号并记录数据',
						'data' =>$list,
					);
				}else{
					$backArr = array(
						'code' =>'0',
						'sms'  =>'成功打印'.$num.'个批次号但未能更新保存accno信息',
						'data' =>$list,
					);
				}

				// $data = array();	Jie 20151222

				//20151216 Man
				// $no2a = $this->no2($arr['num'],$info['prename']);	//Jie 20151222
				// for($i=0;$i<$arr['num'];$i++){
					// $no2   = $no2a[$i];	//获取 151216 Man   // Jie  20151222
					// $accno = $this->accno();	//获取
					//生成ajax需要返回的数组
					// $list[$i]['code1']   = trim($arr['dno']);
					// $list[$i]['code2']   = $no2;
					// $list[$i]['code3']   = $accno;
					// $list[$i]['time']    = $cur_time_b; //返回详细时间
					// $list[$i]['bqty']    = intval($checkIt['pnum'])+1."--".intval($checkIt['pnum'])+$num; //上次pnum+1 到 pnum+json.num
					// $list[$i]['to']      = $info['toname'];
					// $list[$i]['airname'] = '国泰';
					// $list[$i]['transit'] = $info['transit'];

					// //生成数据表需要保存的数组  Jie 20151222
					// $data[$i]['tcid']     = $checkIt['tcid'];
					// $data[$i]['nid']      = $checkIt['id'];
					// $data[$i]['date']     = $cur_time;
					// $data[$i]['no']       = $no2;
					// $data[$i]['accno']    = $accno;
					// $data[$i]['createid'] = session('auth_item.ccid');
					// $data[$i]['airid']    = '';
					// $data[$i]['airno']    = '';

				// }

				/* Jie 20151222 */
				// // 保存生成的数据到 mk_transit_no2
				// $j = 0;
				// foreach($data as $v){
				// 	$res = M('TransitNo2')->add($v);
				// 	if($res){
				// 		$j++;	//成功保存一条数据则$j+1
				// 	}
				// }

				// if($j == count($data)){	//如果 实际成功保存的数量 等于 目标数量

					// $backArr = array(
					// 	'code' =>'1',
					// 	'sms'  =>'成功打印'.$num.'个批次号并记录数据',
					// 	'data' =>$list,
					// );

				// }else{	//如果 实际成功保存的数量 不等于 目标数量

				// 	$backArr = array(
				// 		'code' =>'1',
				// 		'sms'  =>'成功生成'.$j.'个批次号但有'.count($data)-intval($j).'个未能保存',
				// 		'data' =>$list,
				// 	);

				// }

			}else{	//更新打印数量失败
				$backArr = array(
					'code' =>'0',
					'sms'  =>'批次号打印失败',
					'dno'  =>$arr['dno'],
					// 'data' =>$list,
				);
			}


			$this->ajaxReturn($backArr);
		}
	}

	/**
	 * 获取航空公司列表
	 * @return [type] [description]
	 */
	public function getAir(){

		//收到
		// $str = $_POST['DATA'];
		// $str = '{"do":"getAir"}';	//测试
		$arr = $this->jstr;//json_decode($str,true);

		if(trim($arr['do']) == 'getAir'){

			//获取航空公司列表
			$list = M('ExpressCompany')->field('id,contact_person as name')->where(array('status'=>'1'))->select();
			$backArr = array(
					'code' =>'1',
					'sms'  =>'成功获得航空公司列表',
					'data' =>$list
				);
			$this->ajaxReturn($backArr);
		}
	}

	/**
	 * 保存航空单号
	 */
	public function setAir(){

		//收到
		// $str = $_POST['DATA'];
		// $str = '{"do":"setAir","dno":"TTJ151222028","airid":"0","airno":"ST332654421284"}';	//测试
		// $arr = json_decode($str,true);
		$arr = $this->jstr;//json_decode($str,true);

		if(trim($arr['dno']) == ''){
			$backArr = array('code'=>'402','sms'=>'参数dno不能为空');
			$this->ajaxReturn($backArr);
		}

		if(trim($arr['airid']) == '' || !is_numeric(trim($arr['airid']))){
			$backArr = array('code'=>'402','sms'=>'请输入数字airid');
			$this->ajaxReturn($backArr);
		}

		if(trim($arr['airno']) == ''){
			$backArr = array('code'=>'402','sms'=>'参数airno不能为空');
			$this->ajaxReturn($backArr);
		}

		//检查dno首字母开头是否为T 20151214 Jie
		if($arr['dno'][0] != 'T'){
			$backArr = array('code'=>'402','sms'=>'参数dno错误');
			$this->ajaxReturn($backArr);
		}

		// $dno  = explode("-", trim($arr['dno']));	//分割
		// $dnoc = count($dno);
		
		// dump($dno);

		$checkOne = M('TransitNo')->where(array('no'=>trim($arr['dno'])))->find();

		//如果存在，则将airid,airno保存到mk_transit_no中
		if($checkOne){

			$center = M('TransitCenter')->where(array('id'=>$checkOne['tcid']))->find();

			//检查传过来的airid是否与数据表mk_transit_no.tcid => mk_transit_center.id 关系取得的mk_transit_center.airid 一致
			if(trim($arr['airid']) != $center['airid']){	//如果不一致
				$backArr = array('code'=>'0','sms'=>'所选的航空公司有误，请认真核查后再保存');
				$this->ajaxReturn($backArr);exit;
			}

			// Jie 20151228 如果请求的bnum传来的板数 > 该单的打印次数pnum 
			if(trim($arr['bnum']) > $checkOne['pnum']){
				$backArr = array(
					'code' => '0',
					'sms'  => '"上板总数"有误，它不能大于已打印标签的数量',
					'num'  => $checkOne['pnum'], 	// 打印数量
				);
				$this->ajaxReturn($backArr);	//返回
				exit;
			}

			// Jie 20151229 如果该单的mk_transit_no.status > 10 但可以=10 ，则抛出错误，终止操作
			if($checkOne['status'] > '10'){
				$backArr = array(
					'code' => '0',
					'sms'  => '参数错误，操作无效',
				);
				$this->ajaxReturn($backArr);	//返回
				exit;
			}

			//统计tran_list.noid=no.id的总数量保存到no.lnum中

			$map 				 = array();
			$map['noid'] 		 = array('eq',$checkOne['id']);
			$lnum 				 = M('TranList')->where($map)->count();

			//160117Man 获取该批中转最后时间保存到bdate中
			$lasttime = M('Logs')->where(array('tranid'=>$checkOne['id']))->order('id desc')->getField('optime');

			$data['airid']       = trim($arr['airid']);	// 航空公司id
			$data['airno']       = trim($arr['airno']);	// 航空单号
			$data['airdatetime'] = date('Y-m-d H:i:s');	// 保存航空号时间  20151229 Jie
			$data['bnum']        = trim($arr['bnum']);	// 该单上板总数
			$data['lnum']        = $lnum;				// 统计tran_list.noid=no.id的总数量
			$data['bdate']		 = $lasttime; 			// 160117 Man
			$data['status']		 = 10 ; 				// Man 151224将状态改为10 方便读取记录进行抓取航空信息
			$res = M('TransitNo')->where(array('id'=>$checkOne['id']))->save($data);	//保存更新

			if($res){	//保存成功

				$backArr = array(
					'code'  =>'1',
					'sms'   =>'保存成功',
					'dno'   =>$arr['dno'],
					'airid' =>$arr['airid'],
					'airno' =>$arr['airno'],
				);
			
			}else{	//保存失败

				$backArr = array(
					'code'  =>'400',
					'sms'   =>'保存失败',
					'dno'   =>$arr['dno'],
					'airid' =>$arr['airid'],
					'airno' =>$arr['airno'],
				);

			}

			$this->ajaxReturn($backArr);	//返回处理结果

		}else{	//不存在 返回错误
			$backArr = array('code'=>'0','sms'=>'参数不存在');
			$this->ajaxReturn($backArr);
		}
	}
//==============================================================
	/**
	 * session分析登录状态
	 * @return [type] [description]
	 */
	protected function auth(){
		$value = session('auth_item');
		if(!$value || $value['isLoged'] != 'passed' || empty($value['ccid']) || empty($value['ccname']) || empty($value['cctname'])){
			//$this->redirect('login');
			$backArr = array('code'=>'404','sms'=>'登录超时');
			$this->ajaxReturn($backArr);
			die();
		}
	}

	//清除session 测试用
	public function clean(){
		session('auth_item',NULL);
	}


	public function no1($tcid){  //需传入mk_transit_center.id
		//检查该线路是否存在，是否可用 Man
		$tc = M('TransitCenter')->where(array('id'=>$tcid,'status'=>1))->find();
		if(!$tc){
			$backArr = array('code'=>'0','sms'=>'该线路不存在或已停用');
			$this->ajaxReturn($backArr);
		}
		$pre 	= strtoupper(trim($tc['prename']));
		if($pre=='') $pre = 'MK';
		//$num 	= date('ymdHis');
		//return 'T'.$tca['tc'.$tcid].$num;
		$no 	= new \Org\MK\Tracking; 
		//$noa 	= $no->Transfer('no',1,$pre.date('ymd'));
		$noa 	= $no->Transfer('no',1,$pre);
		return $noa[0];
	}
	public function no2($num,$pre){
		//$num 	= date('ymdHisu');
		//return $num; Man151216
		$no 	= new \Org\MK\Tracking; 
		$noa 	= $no->Transfer('no2',$num,$pre);
		if(count($no2a)<>$arr['num']){
			$backArr = array('code'=>'0','sms'=>'获取批号时出错，请重试');
			$this->ajaxReturn($backArr);
		}
		return $noa;
	}
	public function accno(){
		$num 	= (string)rand(100,999).date('Hs');
		return $num;
	}
	public function zonetime($v){
		$tz 		= $this->timezone;
		return ($tz==8)?$v:(date('Y-m-d H:i:s',strtotime($v)+(($tz-8)*60*60)));
	}

	public function toTimeZone($src, $from_tz = 'RPC', $to_tz = 'America/Los_Angeles', $fm = 'Y-m-d H:i:s') {
	    $datetime = new \DateTime($src, new \DateTimeZone($from_tz));
	    $datetime->setTimezone(new \DateTimeZone($to_tz));
	    return $datetime->format($fm);
	}

}