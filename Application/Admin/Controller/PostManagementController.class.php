<?php
/**
 * 快件管理  客户端
 */
namespace Admin\Controller;
use Think\Controller;
class PostManagementController extends AdminbaseController {

    function _initialize() {
        parent::_initialize();
		$client = new \HproseHttpClient(C('RAPIURL').'/PostManagement');	//读取、查询操作
        $this->client = $client;		//全局变量
    }

    /**
     * 快件列表页面
     * @return [type] [description]
     */
	public function index(){

        // 用于修复中文搜索的时候搜索栏的中文正常显示
		foreach ($_GET as $k=>$v){
			if(!is_array($v)){
				if (!mb_check_encoding($v, 'utf-8')){
					$_GET[$k] = iconv('gbk', 'utf-8', $v);
				}
			}else{
				foreach ($_GET['_URL_'] as $key=>$value){
					if (!mb_check_encoding($value, 'utf-8')){
						$_GET['_URL_'][$key] = iconv('gbk', 'utf-8', $value);
					}
				}
			}
		}

		$keyword    = trim(I('get.keyword'));
		$searchtype = I('get.searchtype');
		$type       = I('get.type');
		$noall      = I('get.noall');
		$starttime  = intval(I('starttime'));
		$endtime    = intval(I('endtime'));
		$turnOther  = trim(I('get.turnOther'));// 20161115 jie 筛选出已转其他快递的所有单号
		$tcid       = (I('get.tcid')) ? trim(I('get.tcid')) : '';//20161116 jie   增加 TrandKd = 中转线路.id 的查询

		// 20170221 jie
		if($type == 'void' || $type == 'backup'){
			$this->display();exit;
		}

		//分页显示的数量
		$p = (I('p')) ? trim(I('p')) : '1';
		$ePage = (I('get.ePage')) ? trim(I('get.ePage')) : C('EPAGE');

        $this->assign($_GET);
        $this->assign('ePage',$ePage);

        $map = array();

        //按查询类型搜索
        if(!empty($keyword) && !empty($searchtype)){
            if($searchtype == 'IL_state'){
            	$map['t.'.$searchtype] = array('eq', $keyword);
            }else if($searchtype == 'no'){
            	$map['g.'.$searchtype] = array('like', '%'.$keyword.'%');
            }else{
            	$map['t.'.$searchtype] = array('like', '%'.$keyword.'%');
            }
        }

        //按物流状态 150812 man
        $ilstate = I('get.mkkd');

        //搜索栏中的 物流状态
        $this->ilstarr = C('ilstarr');

        //$ilstarri中的0-9 是与 $ilstarr 中的key对应
        $this->ilstarri = C('ilstarri');
        $this->ilst = $ilstate;

        if($ilstate>0){
        	$map['t.IL_state'] = array('eq', $ilstate);
        }

        // 20161115 jie 筛选出已转其他快递的所有单号
        if($turnOther == 'on'){
        	$map['t.forward_kd'] = array('neq', '');
        }

    	/* 20180307 jie 根据分配的线路权限获取线路相关的订单等信息  */
        $PublicLineData = new \Admin\Controller\PublicLineDataController();
        $PublicLineData->line_id = true;

        $tcids = $PublicLineData->intersect();//一维数组

        // 如果不是 全部线路权限，则根据实际分配的线路权限，读取线路信息
        if($tcids !== true){
        	$map['t.TranKd'] = array('in',$tcids);
        }
        $center_list = $PublicLineData->get_lines();
        /* 20180307 jie  根据分配的线路权限获取线路相关的订单等信息  */

        // 如果是搜索请求，则优先使用此条件  20161116 jie   增加 TrandKd = 中转线路.id 的查询
        if($tcid != ''){
        	$map['t.TranKd'] = array('eq', $tcid);
        }

		//按时间段搜索
		if(!empty($starttime) && !empty($endtime)){
			$starttime = date('Y-m-d H:i:s',$starttime);
			$endtime   = date('Y-m-d H:i:s',$endtime);
			$map['t.ex_time'] = array('between',$starttime.",".$endtime);

		}else if(!$starttime && $endtime){
			$endtime = date('Y-m-d H:i:s',$endtime);
			$map['t.ex_time'] = array('elt',$endtime);

		}else if($starttime && !$endtime){
			$starttime = date('Y-m-d H:i:s',$starttime);
			$map['t.ex_time'] = array('egt',$starttime);
		}

		switch($type){		//IL_state 1003(str) 表示该物流单已经完成; pause_status 0(int) 表示订单正常，未暂停
			case 'index':
		        // if(!$noall || $noall != 'all'){		//2015-08-18 Jie 更改为全部显示，无需区分
		        // 	$map .= "t.IL_state <> '1003' AND t.pause_status = 0";
		        // }else{
		        	$map['t.pause_status'] = array('eq', 0);
		        // }
				break;

			case 'three'://三天未变
				$map['_string'] = "DATEDIFF(NOW(),t.optime)>3 and DATEDIFF(NOW(),t.optime)<=5 AND t.IL_state <> '1003' AND t.pause_status = 0";
				break;

			case 'five'://五天未变
				$map['_string'] = "DATEDIFF(NOW(),t.optime)>5 and DATEDIFF(NOW(),t.optime)<=7 AND t.IL_state <> '1003' AND t.pause_status = 0";
				break;

			case 'seven'://七天未变
				$map['_string'] = "DATEDIFF(NOW(),t.optime)>7 and DATEDIFF(NOW(),t.optime)<=10 AND t.IL_state <> '1003' AND t.pause_status = 0";
				break;

			case 'nine'://疑难件
				$map['_string'] = "DATEDIFF(NOW(),t.optime)>10 AND t.IL_state <> '1003' AND t.pause_status = 0";
				break;

			case 'stop':	//暂停件
		        // if(!$noall || $noall != 'all'){		//2015-08-18 Jie 更改为全部显示，无需区分
		        // 	$map .= "t.IL_state <> '1003' AND t.pause_status = 20";
		        // }else{
		        	$map['t.pause_status'] = array('eq','20');
		        // }
		        break;

		    case 'void':	//作废件

		    	$map['t.IL_state'] = array('eq','1003');
				$map['d.optype'] = array('eq','作废');

		    	break;

		    case 'backup':	//回收站
		    	$map['_string'] = '1';

		    	break;
			default:	//默认
				$map['t.IL_state'] = array('neq','1003');
				$map['t.pause_status'] = array('eq','0');
				break;
		}

 		$this->_list($map,$p,$ePage,$type);

 		$this->assign('center_list',$center_list);

		$this->display($type);

	}

	/**
	 * 数据列表输出
	 * @param  [type] $map [筛选条件]
	 * @return [type]      [description]
	 */
	public function _list($map,$p,$ePage,$type){
		$client = $this->client;

		$res = $client->count($map,$p,$ePage,$type);

		$count       = $res['count'];
		$list        = $res['list'];

		$page  = new \Think\Page($count,$ePage); // 实例化分页类 传入总记录数和每页显示的记录数(20)

		// 过滤空格 20180410 jie
        foreach($page->parameter as $k1=>$v1){
            $page->parameter[$k1] = trim($v1);
        }

		$page->setConfig('prev', "上一页");//上一页
		$page->setConfig('next', '下一页');//下一页
		$page->setConfig('first', '首页');//第一页
		$page->setConfig('last', "末页");//最后一页
		$page -> setConfig ( 'theme', '%FIRST% %UP_PAGE% %LINK_PAGE% %DOWN_PAGE% %END% %HEADER%' );

		$show  = $page->show(); // 分页显示输出

        $this->assign('page',$show);// 赋值分页输出
        $this->assign('list',$list);

	}

	/**
	 * 编辑 视图
	 * @return [type] [description]
	 */
	public function edit(){

		$id = trim(I('get.id'));

		$client = $this->client;
		$info   = $client->getInfo($id);
		
		$this->assign('info',$info[0]);
		$this->display();
	}

	/**
	 * 获取详细信息
	 * @return [type] [description]
	 */
	public function info(){

		$MKNO = trim(I('get.MKNO'));
		$id   = trim(I('get.id'));
		// $sim  = trim(I('get.sim')) ? trim(I('get.sim')) : '';	// $sim 用来区别只在批号对数的时候使用以下功能 20160315 Jie

		$client = $this->client;

		$res = $client->getInfo($id,$MKNO);
		$msg = $client->getMsg($res[0],$MKNO);

/*		//判断该单的操作时间是否距离当前时间已经大于12天 20160316 Jie
		if(time() - strtotime($res[0]['optime']) > 1036800){
			$this->assign('timeout','on');
		}*/

		// 判断该单的操作时间距离当前时间大于5天的才显示“完成此单”按钮 20160316 Jie
		if(time() - strtotime($res[0]['optime']) > 432000){
			$this->assign('fivedays','on');
		}

		// 20161229 jie 新增海关信息列表和海关错误信息
		$this->assign('hg_info',$msg[1]);
		$this->assign('hg_msg',$msg[2]);

		$this->assign('list',$msg[0]);
		$this->assign('info',$res[0]);
		$this->assign('id',$id);	//20160316 Jie
		// $this->assign('sim',$sim);	//20160316 Jie

		$sel_list = C('LOGARTHM_SELECT');
		ksort($sel_list);

		$key_list = array_keys($sel_list);

		$this->assign('sel_list',$sel_list);	//20160316 Jie
		$this->assign('key_list',$key_list);	//20180716 Jie
		$this->pro_list = $res[1];
		$this->display();
	}

	/**
	 * 更新 方法
	 * @return [type] [description]
	 */
	public function update(){

    	if(!IS_POST){
    		die('非法操作~！');
    	}

        $value = session('admin');
        $username = $value['adname'];		//当前登陆的管理员

		$address  = trim(I('post.reAddr'));	//收件地址  未完整
		// if($address == ''){
		// 	$result = array('state'=>'0','msg'=>'新详细收件地址不能为空');
		// 	$this->ajaxReturn($result);
		// }

		$province = trim(I('post.province'));	//省份
		$city     = trim(I('post.city'));	//城市
		$town     = trim(I('post.town'));	//区/县

		$id               = trim(I('post.id'));		//mk_tran_list 的id
		$data['receiver'] = trim(I('post.receiver'));	//收件人
		$data['reTel']    = trim(I('post.reTel'));	//收件人电话
		$data['province'] = $province;	//省份
		$data['city']     = $city;	//城市
		$data['town']     = $town;	//区/县
		$data['reAddr']   = $province." ".$city." ".$town." ".$address;	//收件地址  完整拼接
		$MKNO             = trim(I('post.MKNO'));		//美快单号
		$auto_Indent2     = trim(I('post.auto_Indent2'));		//自定义号2
		$remark           = trim(I('post.remark'));		//备注

		$client = $this->client;

		$result = $client->update($id,$data,$MKNO,$auto_Indent2,$remark,$username,$address);

		$this->ajaxReturn($result);

	}

	/**
	 * 订单删除 视图
	 * @return [type] [description]
	 */
	public function toDel(){

		$id = trim(I('get.id'));

		$client = $this->client;

		$info = $client->getInfo($id);

		$this->assign('info',$info[0]);
		$this->display();
	}

	/**
	 * 订单删除 方法
	 * @return [type] [description]
	 */
	public function delete(){

    	if(!IS_POST){
    		die('非法操作~！');
    	}

        $username = session('admin.adname');		//当前登陆的管理员

		$id           = trim(I('post.id'));		//mk_tran_list 的id
		$MKNO         = trim(I('post.MKNO'));		//美快单号
		$auto_Indent2 = trim(I('post.auto_Indent2'));		//自定义号2
		$remark       = trim(I('post.remark'));		//备注

		$client = $this->client;

		$result = $client->delete($id,$MKNO,$auto_Indent2,$remark,$username);

		$this->ajaxReturn($result);

	}

	/**
	 * 疑难件删除 方法
	 * @return [type] [description]
	 */
	public function delete_nine(){

    	if(!IS_POST){
    		die('非法操作~！');
    	}

        $username = session('admin.adname');		//当前登陆的管理员

		$id           = trim(I('post.id'));		//mk_tran_list 的id

		$client = $this->client;

		$result = $client->delete_nine($id,$username);

		$this->ajaxReturn($result);

	}

	/**
	 * 订单暂停 视图
	 * @return [type] [description]
	 */
	public function toPause(){

		$id = trim(I('get.id'));

		$client = $this->client;

		$info = $client->getInfo($id);

		$this->assign('info',$info[0]);
		$this->display();
	}

	/**
	 * 订单暂停 方法
	 * @return [type] [description]
	 */
	public function pause(){
		if(!IS_POST){
			die('非法操作');
		}

        $username = session('admin.adname');		//当前登陆的管理员

		$id           = trim(I('post.id'));		//mk_tran_list 的id
		$MKNO         = trim(I('post.MKNO'));		//美快单号
		$auto_Indent2 = trim(I('post.auto_Indent2'));		//自定义号2
		$remark       = trim(I('post.remark'));		//备注

		$client = $this->client;
		$result = $client->toPause($id,$MKNO,$auto_Indent2,$remark,$username);

		$this->ajaxReturn($result);


	}

//========================== 暂停件 ============================
	/**
	 * 订单恢复 视图
	 * @return [type] [description]
	 */
	public function toRecover(){

		$id = trim(I('get.id'));

		$client = $this->client;

		$info = $client->getInfo($id);

		$this->assign('info',$info[0]);
		$this->display();
	}

	/**
	 * 订单恢复 方法
	 * @return [type] [description]
	 */
	public function recover(){
		if(!IS_AJAX){
			die('非法操作');
		}

        $username = session('admin.adname');

		$id           = trim(I('post.id'));		//mk_tran_list 的id
		$MKNO         = trim(I('post.MKNO'));		//美快单号
		$auto_Indent2 = trim(I('post.auto_Indent2'));		//自定义号2
		$remark       = trim(I('post.remark'));		//备注

		$client = $this->client;
		$result = $client->toRecover($id,$MKNO,$auto_Indent2,$remark,$username);

		$this->ajaxReturn($result);

	}

//======================== 操作日志 ===========================
	/**
	 * 操作日志 视图
	 * @return [type] [description]
	 */
	public function log(){

        $keyword = trim(I('get.keyword'));
        $searchtype = I('get.searchtype');
		$optype = I('get.optype');
		$starttime  = intval(I('get.starttime'));
		$endtime    = intval(I('get.endtime'));

		//分页显示的数量
		$ePage = I('get.ePage');
		$ePage = $ePage?$ePage:C('EPAGE');

		$this->assign($_GET);

		//按用户名、邮箱、电话搜索
		if(!empty($keyword) && !empty($searchtype)){
			$where[$searchtype]=array('like','%'.$keyword.'%');
		}

        // if($type)$where['type']=$type;
        // 按状态搜索
        if($optype != ''){
            $where['optype'] = $optype;
        }

		//按时间段搜索
		if($starttime && $endtime){
			$starttime = date('Y-m-d H:i:s',$starttime);
			$endtime = date('Y-m-d H:i:s',$endtime);
			$where['operate_time'] = array('between',array($starttime,$endtime));
		}else if(!$starttime && $endtime){
			$endtime = date('Y-m-d H:i:s',$endtime);
			$where['operate_time'] = array('elt',$endtime);
		}else if($starttime && !$endtime){
			$starttime = date('Y-m-d H:i:s',$starttime);
			$where['operate_time'] = array('egt',$starttime);
		}

		$client = $this->client;

        // $count = M('DailyRecord')->where($where)->count(); // 查询满足要求的总记录数
		$count = $client->countLog($where);

		$page = new \Think\Page($count,$ePage); // 实例化分页类 传入总记录数和每页显示的记录数(25)

    $page->setConfig('prev', "上一页");//上一页
    $page->setConfig('next', '下一页');//下一页
    $page->setConfig('first', '首页');//第一页
    $page->setConfig('last', "末页");//最后一页
    $page -> setConfig ( 'theme', '%FIRST% %UP_PAGE% %LINK_PAGE% %DOWN_PAGE% %END% %HEADER%' );

		$show = $page->show(); // 分页显示输出
        $this->assign('page',$show);// 赋值分页输出
        $limit = $page->firstRow.','.$page->listRows;

		$list = $client->getLog($limit,$where);
		// $list = M('DailyRecord')->order('id')->where($where)->limit($page->firstRow.','.$page->listRows)->select();

		$this->assign('list',$list);

		$this->display();
	}

//======================= 回收站 ==========================
	/**
	 * 订单还原 视图
	 * @return [type] [description]
	 */
	public function toFallback(){

		$tid = trim(I('get.tid'));

		// $client = $this->client;

		// $info = $client->getInfo($id);

		$this->assign('tid',$tid);
		$this->display();
	}

	/**
	 * 订单还原 方法  Jie 2015-08-27 暂时停用
	 * @return [type] [description]
	 */
	public function fallback(){
		if(!IS_AJAX){
			die('非法操作');
		}

        $username = session('admin.adname');

		$tid          = trim(I('post.tid'));		//mk_tran_list 的id
		$MKNO         = trim(I('post.MKNO'));		//美快单号
		$auto_Indent2 = trim(I('post.auto_Indent2'));		//自定义号2
		$remark       = trim(I('post.remark'));		//备注

		$client = $this->client;
		$result = $client->reduction($tid,$MKNO,$auto_Indent2,$remark,$username);

		$this->ajaxReturn($result);
	}

//==================== 对账单 =====================

    /**
     * 父页 数据列表
     * @param  [type] $map   [description]
     * @param  [type] $limit [description]
     * @return [type]        [description]
     */
	public function account(){

        $client = $this->client;

       	ini_set('max_execution_time', 0);
        $p = I('get.p')?I('get.p'):1;	//当前页数，如果没有则默认显示第一页
		//分页显示的数量
		$ePage = I('get.ePage');
		$ePage = $ePage?$ePage:C('EPAGE');	//分页显示数量
		$this->assign('ePage',$ePage);

        $sto = I('get.sto');
        if($sto == '1'){

			$map['state']  = array('eq','20');	//物流状态要等于20

			//$maxtime = date('Y-m-d H:i:s',time()-15552000);		//查找的是距离当前时间的半年内的中转批号
			//$map['optime'] = array('gt',$maxtime); //150814 Man 取消，分页就不再做时间限制

			$keyword    = trim(I('get.keyword'));	//输入搜索的关键字

			$this->assign($_GET);

			//按关键字搜索
			if(!empty($keyword)){
				$map['mStr1'] = array('like',$keyword.'%');
			}

			$res = $client->account_list($map,$p,$ePage,U('PostManagement/showList'),U('Logarithm/showList'));

			$page = new \Think\Page($res[0],$ePage); // 实例化分页类 传入总记录数和每页显示的记录数(20)
			$page->setConfig('prev', "上一页");//上一页
			$page->setConfig('next', '下一页');//下一页
			$page->setConfig('first', '首页');//第一页
			$page->setConfig('last', "末页");//最后一页
			// $page->setConfig ( 'theme', '%FIRST% %UP_PAGE% %LINK_PAGE% %DOWN_PAGE% %END% %HEADER%' );
			$page->setConfig ( 'theme', '%FIRST% %UP_PAGE% %LINK_PAGE% %DOWN_PAGE% %END%' );

			$show = $page->show(); // 分页显示输出
			$this->assign('page',$show);// 赋值分页输出
			$limit = $page->firstRow.','.$page->listRows;

			$this->assign('list',$res[1]);

        }

        $lost_count = $client->lost_count();
        $this->assign('lost_count',$lost_count);

		$this->display();
	}

    /**
     * 子页 根据mStr1，IL_state获取对应的数据列表
     * @param  [type] $map [description]
     * @return [type]      [description]
     */
	public function showList(){

		$p = I('get.p')?I('get.p'):1;	//当前页数，如果没有则默认显示第一页

		$id     = I('get.id')?I('get.id'):"";
		$mstr   = I('get.mstr')?I('get.mstr'):"";
		$stype  = I('get.stype')?I('get.stype'):"";
		$Istate = I('get.Istate')?I('get.Istate'):"";

		$client = $this->client;
		$res = $client->showList($mstr,$stype,$Istate,$id,$p);

        $this->ilstarr = C('ilstarr');

        //$ilstarri中的0-9 是与 $ilstarr 中的key对应
        $this->ilstarri = C('ilstarri');

        $this->assign('record_list',$res[1]);	//每个中转批号对应的备注信息
		$this->assign('list',$res[0]);	//数据列表

		$page = new \Think\Page($res[2],50); // 实例化分页类 传入总记录数和每页显示的记录数(20)
		$page->setConfig('prev', "上一页");//上一页
		$page->setConfig('next', '下一页');//下一页
		$page->setConfig('first', '首页');//第一页
		$page->setConfig('last', "末页");//最后一页
		$page->setConfig ( 'theme', '%FIRST% %UP_PAGE% %LINK_PAGE% %DOWN_PAGE% %END% %HEADER%' );

		$show = $page->show(); // 分页显示输出
		$this->assign('page',$show);// 赋值分页输出
		$this->assign('tcid',trim(I('get.tcid'))); //20170119 jie

		$sel_list = C('LOGARTHM_SELECT');
		ksort($sel_list);
		$this->assign('sel_list',$sel_list);	//20160316 Jie

		$limit = $page->firstRow.','.$page->listRows;

		$this->display();
	}

	/**
	 * 中转批号添加备注
	 */
	public function addNew(){
		if(!IS_AJAX){
			die('非法操作');
		}
		$mstr    = I('post.mstr');
		$content = I('post.content');

		if($content == ''){
			$result = array('state'=>'no', 'msg'=>'备注内容不能为空');
			$this->ajaxReturn($result);
		}

		$value    = session('admin');
		$username = $value['adtname'];

		$client = $this->client;

		$result = $client->addRecord($mstr,$username,$content);

		$this->ajaxReturn($result);
	}

//====================== 订单作废 未完成 =========================

	/**
	 * 订单作废 视图
	 * @return [type] [description]
	 */
	public function toVoid(){

		$id = trim(I('get.id'));

		$client = $this->client;

		$info = $client->getInfo($id);

		$this->assign('info',$info[0]);
		$this->display();
	}

	/**
	 * 订单作废 方法
	 * @return [type] [description]
	 */
	public function beVoid(){
		if(!IS_POST){
			die('非法操作');
		}

        $username = session('admin.adname');		//当前登陆的管理员

		$id           = trim(I('post.id'));					//mk_tran_list 的id
		$MKNO         = trim(I('post.MKNO'));				//美快单号
		$auto_Indent2 = trim(I('post.auto_Indent2'));		//自定义号2
		$remark       = trim(I('post.remark'));				//备注

		$client = $this->client;
		$result = $client->toVoid($id,$MKNO,$auto_Indent2,$remark,$username);

		$this->ajaxReturn($result);

	}

	/**
	 * 撤销作废订单 视图
	 * @return [type] [description]
	 */
	public function cancelVoid(){

		$id = trim(I('get.id'));

		$client = $this->client;

		$info = $client->getInfo($id);

		$this->assign('info',$info[0]);
		$this->display();
	}

	/**
	 * 取消作废订单 方法
	 */
	public function abolish(){
		if(!IS_POST){
			die('非法操作');
		}

        $username = session('admin.adname');		//当前登陆的管理员

		$id           = trim(I('post.id'));					//mk_tran_list 的id
		$MKNO         = trim(I('post.MKNO'));				//美快单号
		$auto_Indent2 = trim(I('post.auto_Indent2'));		//自定义号2
		$remark       = trim(I('post.remark'));				//备注

		$client = $this->client;
		$result = $client->cancel_void($id,$MKNO,$auto_Indent2,$remark,$username);

		$this->ajaxReturn($result);
	}

	/**
	 * 获取详细信息
	 * @return [type] [description]
	 */
	public function delInfo(){

		$MKNO = trim(I('get.MKNO'));
		$id   = trim(I('get.id'));

		$client = $this->client;

		$res = $client->getInfo_del($id);

		// $info = M('TranList')->where(array('id'=>$id))->find();

		$msg = $client->getMsg_del($res[0]);
		//$msg = M('il_logs')->where(array('MKNO'=>$info['MKNO']))->select();

		$this->assign('list',$msg);
		$this->assign('info',$res[0]);
		$this->pro_list = $res[1];
		$this->display();
	}

    /**
     * 删除某个订单的手动加入的物流信息
     * @return [type] [description]
     */
    public function del_its_msg(){
    	if(!IS_AJAX){
    		die('非法访问');
    	}

    	$id = I('id');
    	$res = $this->client->_del_its_msg($id);

    	$this->ajaxReturn($res);
    }
}