<?php
/**
 * 申通转EMS流程：
 * 1，根据导入的csv文件中的名单进行逐一推送(分别执行 发送给ERP，发送到快递100)，两者之间的推送互不影响；
 * 
 * 2，发送到ERP，pno = EMS单号，state = 800(表示默认是转EMS)，其他参数不变；
 * 2.1用MKNO，EMSNO 先检查mk_stno_to_ems中的erp_state是否为200；
 * 若是，则跳过当前运单号的ERP推送，执行下一个运单号的推送(不影响KD100的推送)；
 * 若否，则开始执行推送ERP，发送到ERP返回成功(返回200)之后，在数据表mk_stno_to_ems保存发送记录，erp_state = 200(用于区分是否已经成功推送,0未推送，200推送成功);
 * 2.2如果推送ERP返回失败，则马上再次执行当前单号的推送，允许重复推送2次(加上原本的一次总共3次推送)，如果3次重复推送都是返回失败，则抛出失败，终止当前单号的推送，执行下一个单号的推送；
 * 
 * 3，推送快递100，在原来的callbackurl参数后面加上 '?mkno='.$mkno ，company = 可变值(指快递公司类型，默认EMS)，其他参数不变；
 * 3.1用MKNO，EMSNO 先检查mk_stno_to_ems中对应的kd100_state是否为200；
 * 若是，则跳过当前运单号的KD100推送，执行下一个运单号的推送；
 * 若否，则开始执行推送KD100，发送到KD100返回成功之后，返回200(成功)或501(重复推送)，
 * 表示推送KD100成功，(mk_stnolist更新字段kd100status的状态为200，mk_send_record保存推送记录)，在数据表mk_stno_to_ems保存发送记录，kd100_state = 200(用于区分是否已经成功推送,0未推送，200推送成功);
 * 3.2如果推送KD100返回失败，则马上再次执行当前单号的推送，允许重复推送2次(加上原本的一次总共3次推送)，如果3次重复推送都是返回失败，则抛出失败，终止当前单号的推送，执行下一个单号的推送；
 */
namespace Admin\Controller;
use Think\Controller;
class St2EmsController extends AdminbaseController{

	function _initialize() {
		parent::_initialize();

        $client = new \HproseHttpClient(C('RAPIURL').'/St2Ems');		//读取、查询操作
        $this->client = $client;		//全局变量
	}

	public function index(){
		$this->display();
	}

	public function test(){
		// set_time_limit(0);
		echo date('H:i:s');
		sleep(100);
		echo 'hello';
		echo date('H:i:s');
	}
	/**
	 * 导入CSV
	 * @return [type] [description]
	 */
	public function import_csv(){
		
		ini_set('memory_limit','4088M');
		ini_set('max_execution_time', 0);

		G('begin');

		$tran_type   = I('tran_type');//转发快递之后，承接运单号的快递公司
		$sure_post   = I('sure_post') ? I('sure_post') : '';//是否推送给快递100
		$force_kd100 = I('force_kd100') ? I('force_kd100') : '';//强制推送给快递100
		$force_erp   = I('force_erp') ? I('force_erp') : '';//强制推送给ERP

		// 20180109 jie
		$importexcel = new \Libm\MKILExcel\MkilImportMarket;
		$importexcel->inputFileName  = $_FILES['file']['tmp_name'];
		$arr = $importexcel->import();
		// dump($arr);die;
		//如果返回的是false
		if($arr === false){
			$this->ajaxReturn(array('status'=>'0','msg'=>$importexcel->getError()));exit;
		}
		unset($arr[0]);//根据实际情况，去除数组第一个分支
		// 20180109 jie end

		// 判断处理后的数组是否为空
		$len_result = count($arr);
		if($len_result == 0){
			$result = array('status'=>'0','msg'=>'没有任何数据！');
			$this->ajaxReturn($result);exit;
		}
		// dump($result);die;
		$client = $this->client;

		$kind = C('Transit_Type.ST_Transit');//线路ID
		
		$result = $client->_index($arr,$tran_type,$sure_post,$force_kd100,$force_erp,$kind);
		G('end');
		$result['msg'] .= '，耗时：'.G('begin','end').'s';	//耗时时间显示
		$this->ajaxReturn($result);
	}

	public function nos(){

		$keyword     = trim(I('get.keyword'));
		$searchtype  = I('get.searchtype');
		$erp_state   = I('get.erp_state');
		$kd100_state = I('get.kd100_state');
		$starttime   = intval(I('get.starttime'));
		$endtime     = intval(I('get.endtime'));

		$p = (I('p')) ? trim(I('p')) : '1';
		$ePage = (I('get.ePage')) ? trim(I('get.ePage')) : C('EPAGE');
		$this->assign($_GET);

		$this->assign('ePage',$ePage);
		
		if(!empty($keyword) && !empty($searchtype)){
			if($searchtype == 'STNO'){
				$where['s.'.$searchtype]=array('like','%'.$keyword.'%');
			}else{
				$where['t.'.$searchtype]=array('like','%'.$keyword.'%');
			}
		}

        if($starttime && $endtime){
            $where['t.ctime'] = array('between',array(date('Y-m-d H:i:s',$starttime),date('Y-m-d H:i:s',$endtime)));
        }else if(!$starttime && $endtime){
            $where['t.ctime'] = array('elt',date('Y-m-d H:i:s',$endtime));
        }else if($starttime && !$endtime){
            $where['t.ctime'] = array('egt',date('Y-m-d H:i:s',$starttime));
        }

        if($erp_state != ''){
            if($erp_state == '0'){
            	$where['t.erp_state'] = array(array('eq',$erp_state),array('exp','is NULL'), 'or') ;
            }else{
            	$where['t.erp_state'] = array('eq',$erp_state);
            }
        }

        if($kd100_state != ''){
            if($kd100_state == '0'){
            	$where['t.kd100_state'] = array(array('eq',$kd100_state),array('exp','is NULL'), 'or') ;
            }else{
            	$where['t.kd100_state'] = array('eq',$kd100_state);
            }
        }

        $where['tcid'] = array('eq',C('Transit_Type.ST_Transit'));
        
        $client = $this->client;
        
		$res = $client->count($where,$p,$ePage);
		$count = $res['count'];
		$list  = $res['list'];

		$page = new \Think\Page($count,$ePage); // 实例化分页类 传入总记录数和每页显示的记录数(20)

	$page->setConfig('prev', "上一页");//上一页
	$page->setConfig('next', '下一页');//下一页
	$page->setConfig('first', '首页');//第一页
	$page->setConfig('last', "末页");//最后一页
	$page->setConfig ( 'theme', '%FIRST% %UP_PAGE% %LINK_PAGE% %DOWN_PAGE% %END% %HEADER%' );
    		
		$show = $page->show(); // 分页显示输出
		$this->assign('page',$show);// 赋值分页输出

		$this->assign('list',$list);

		$this->display();
	}

	/**
	 * 中转跟踪
	 * @return [type] [description]
	 */
	public function tran_track(){

		$type = (C('Transit_Type.ST_Transit')) ? C('Transit_Type.ST_Transit') : '';

		$list = R('Logarithm/index',array('request'=>true, 'type'=>$type));

		$this->assign('list',$list);
		$this->assign($_GET);
		$this->display('Public:logistics_strack');
	}

	/**
	 * 快递跟踪
	 * @return [type] [description]
	 */
	public function kd_track(){
        
		$type = (C('Transit_Type.ST_Transit')) ? C('Transit_Type.ST_Transit') : '';

		$list = R('Logarithm/pro_two',array('request'=>true, 'type'=>$type));

		$this->assign('list',$list);
		$this->assign($_GET);
		$this->display('Public:logistics_strack');
	}
}