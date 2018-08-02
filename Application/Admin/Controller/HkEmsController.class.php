<?php
/**
 * 香港邮政
 * 指导文档：香港E特快Userapi_1.2.pdf
 */
namespace Admin\Controller;
use Think\Controller;
class HkEmsController extends AdminbaseController{

    function _initialize() {
        parent::_initialize();
        $client = new \HproseHttpClient(C('RAPIURL').'/HkEms');		//读取、查询操作
        $this->client = $client;		//全局变量
    }	

    /**
     * 发送报告 列表页面
     * @return [type] [description]
     */
	public function index(){

		$keyword    = trim(I('get.keyword'));
		$searchtype = I('get.searchtype');
		$tcid       = (C('Transit_Type.HkEms_Transit')) ? trim(C('Transit_Type.HkEms_Transit')) : '';//20161116 jie   增加 TrandKd = 中转线路.id 的查询
		$starttime  = intval(I('starttime'));
		$endtime    = intval(I('get.endtime'));

        //按查询类型搜索
        if(!empty($keyword) && !empty($searchtype)){
            $map['tn.'.$searchtype] = array('like','%'.$keyword.'%');
        }else{
        	$map['tn.send_report'] = array('eq','0');//只列出尚未执行过预报订单的数据
        }

        if($tcid != ''){
        	$map['tn.tcid'] = array('eq',$tcid);//标签A
        }

		//按时间段搜索
		if(!empty($starttime) && !empty($endtime)){
			$starttime = date('Y-m-d H:i:s',$starttime);
			$endtime   = date('Y-m-d H:i:s',$endtime);
			$map['tn.date'] = array('between',$starttime.",".$endtime);

		}else if(!$starttime && $endtime){
			$endtime = date('Y-m-d H:i:s',$endtime);
			$map['tn.date'] = array('elt',$endtime);

		}else if($starttime && !$endtime){
			$starttime = date('Y-m-d H:i:s',$starttime);
			$map['tn.date'] = array('egt',$starttime);
		}

		$client = $this->client;
		$list = $client->_index($map);

		$this->assign('list',$list);
		$this->assign($_GET);
		$this->display();
    }

    /**
     * 预报订单 视图
     * @return [type] [description]
     */
    public function report(){
        $no = trim(I('mstr'));

        $client = $this->client;
		$res = $client->getInfo($no);

    	$this->assign($_GET);
    	$this->assign('info',$res);
    	$this->display();
    }

    /**
     * 预报订单 方法
     * 思路：将收到的批次号id和其他必要的数据
     * 1.先进行数据检验，还有时间日期的验证；
     * 2.根据批次号id，搜索出mk_tran_list中符合的所有数据，将这些数据 组装成ShipmentNumbers必需的数组形式，然后把其他必要的数据和ShipmentNumbers数组传入
     * 	  Kdno6.class.php类中，调用reportOrder()方法；
     * 3. Kdno6类 将这些传入的数据组合成数组并json格式化，再将此json发送到请求地址，收到反馈原路返回结果；如果反馈的结果为成功，则更新此批次号id对应的字段send_report
     * 为1(表示已经执行预报订单操作)；
     * 4.在此，反馈给前端页面之前，需要对反馈信息进行加工或改进处理再进行返回
     * 指导文档：香港E特快Userapi_1.2.pdf
     * @return [type] [description]
     */
    public function sendReport(){
        // 20170315 jie 新增操作频率判断
        $now_time = time();
        $se_time  = session('se_time');

        if((intval($now_time) - intval($se_time)) <= 10){
            $result = array('Status'=>'false','ErrorMessage'=>'操作过于频繁，请10秒后再试');
            $this->ajaxReturn($result);
        }
        // End

    	//必要数据
		$id       = trim(I('id'));//批次号id
		$no       = trim(I('no'));//批次号
		$searched = trim(I('searched'));//是否通过搜索栏搜索出此批次号
		$number   = trim(I('number'));//空运提单号码/交货车辆号码
		$re_time  = trim(I('re_time'));//预计到达时间
		$country  = strtoupper(trim(I('country')));//起运国国家二字编码 统一转换成大写

		//不为空即表示是通过搜索查出此批次号进行当前操作的，则需要验证权限---是否可以再次进行预报订单操作
		if($searched != ''){
	        if($power['send_again'] != 'on'){
	           $result = array('Status'=>'false','ErrorMessage'=>'没有权限去再次预报此订单');
			$this->ajaxReturn($result);
	        }
		}

		// 校验数据
		if($number == '') $this->ajaxReturn(array('Status'=>'false', 'ErrorMessage'=>'空运提单号码/交货车辆号码不能为空'));
		if($re_time == '') $this->ajaxReturn(array('Status'=>'false', 'ErrorMessage'=>'预计到达时间不能为空'));
		if($country == '') $this->ajaxReturn(array('Status'=>'false', 'ErrorMessage'=>'起运国国家二字编码不能为空'));


/*		// $re_time = str_replace('+', ' ', $re_time);
		//正则验证日期时间
		$regexp = "/^(?:(?!0000)[0-9]{4}-(?:(?:0[1-9]|1[0-2])-(?:0[1-9]|1[0-9]|2[0-8])|(?:0[13-9]|1[0-2])-(?:29|30)|(?:0[13578]|1[02])-31)|(?:[0-9]{2}(?:0[48]|[2468][048]|[13579][26])|(?:0[48]|[2468][048]|[13579][26])00)-02-29)\s+([01][0-9]|2[0-3]):[0-5][0-9]:[0-5][0-9]$/";

		if(!preg_match($regexp,$re_time)){
			$result = array('Status'=>'false','ErrorMessage'=>'时间格式不正确');
			$this->ajaxReturn($result);
		}*/

		$trankd = (C('Transit_Type.HkEms_Transit')) ? trim(C('Transit_Type.HkEms_Transit')) : '';//20170308 jie

		$client = $this->client;
		$res = $client->_report($id, $no, $number, $re_time, $country, $trankd);

		session('se_time',time()); //用于操作频率的间隔判断 20170315 jie

		$this->ajaxReturn($res);

    }

	/**
	 * 中转跟踪
	 * @return [type] [description]
	 */
	public function tran_track(){
        
		$type = (C('Transit_Type.HkEms_Transit')) ? C('Transit_Type.HkEms_Transit') : '';

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

		$type = (C('Transit_Type.HkEms_Transit')) ? C('Transit_Type.HkEms_Transit') : '';
		
		$list = R('Logarithm/pro_two',array('request'=>true, 'type'=>$type));

		$this->assign('list',$list);
		$this->assign($_GET);
		$this->display('Public:logistics_strack');
	}

	/**
	 * 报关状态
	 * @return [type] [description]
	 */
    public function customs(){
		$keyword    = trim(I('get.keyword'));
		$searchtype = I('get.searchtype');
		$status     = I('get.status');
		$problem    = I('get.problem');//只显示问题件

		$p = (I('p')) ? trim(I('p')) : '1';
		$ePage = (I('get.ePage')) ? trim(I('get.ePage')) : C('EPAGE');
		$this->assign($_GET);
		$this->assign('ePage',$ePage);

		$map = array();
		if(!empty($keyword) && !empty($searchtype)){
			$map[$searchtype]=array('like','%'.$keyword.'%');
		}

        if($status != ''){
            $map['Status'] = $status;
        }

        if($problem == 'on'){
        	//不通过
        	if($status == ''){
        		$map['Status'] = array('in','20,30');
        	}else{
        		$map['Status'] = array(array('in','20,30'),array('eq',$status), 'and');
        	}
        }

        $map['TranKd'] = array('eq',C('Transit_Type.HkEms_Transit'));//服务器上面的是6

    	$client = $this->client;
    	$res = $client->_count($map,$p,$ePage);	//统计总数
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

    	// echo THINK_VERSION;
    	$this->assign('count',$count);
    	$this->assign('list',$list);
    	$this->assign('state_arr',C('state_Hkems'));
    	$this->display();
    }

    public function info(){
		$id = I('get.id');
		$map['id'] = array('eq',$id);

		$client = $this->client;
		$info   = $client->info($map);
		
		$this->assign('info',$info[0]);
		$this->assign('list',$info[1]);


    	$this->assign('state_arr',C('state_Hkems'));
		$this->display();
    }
}