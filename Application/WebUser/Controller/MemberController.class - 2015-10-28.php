<?php
/**
 * 包裹列表
 */
namespace Web\Controller;
use Think\Controller;
class MemberController extends BaseController {

    public function _initialize() {
        parent::_initialize();

        vendor('Hprose.HproseHttpClient');
        $client = new \HproseHttpClient(C('RAPIURL').'/Web');      //读取、查询操作
        $this->client = $client;
        $Wclient = new \HproseHttpClient(C('WAPIURL').'/Web');      //增删改操作
        $this->Wclient = $Wclient;
    }
    
    /**
     * 包裹列表
     * @return [type] [description]
     */
    public function index(){

		$mkuser    = session('mkuser');
		$mkno      = trim(I('post.mkno'));		//单号
		$rec       = trim(I('post.rec'));		//收件人
		$phone     = trim(I('post.phone'));		//电话
		$starttime = intval(I('post.starttime'));
		$endtime   = intval(I('post.endtime'));

		if($rec) $map['t.receiver'] = array('like',$rec.'%');		//收件人
		if($phone) $map['t.reTel']  = array('like',$phone.'%');		//收件人电话
		$map['user_id']             = array('eq',$mkuser['uid']);	//当前登陆的用户id

		//按时间段搜索
		if($starttime && $endtime){
			$map['optime'] = array('between',array(date('Y-m-d H:i:s',$starttime),date('Y-m-d H:i:s',$endtime)));
		}else if(!$starttime && $endtime){
			$map['optime'] = array('elt',date('Y-m-d H:i:s',$endtime));
		}else if($starttime && !$endtime){
			$map['optime'] = array('egt',date('Y-m-d H:i:s',$starttime));
		}

    	$p = I('get.p')?I('get.p'):1;	//当前页数，如果没有则默认显示第一页
    	//分页显示的数量
		$ePage = I('post.ePage');
		$ePage = $ePage?$ePage:C('EPAGE');

    	$type = I('get.type','index');	//必须
    	self::assign(I('post.'));

    	/* 未完成页面读取tran_ulist */
    	if($type == 'index'){
    		$TranList = 'TranUlist';
            $l = '';
            $files = "t.*";
    	}else{		/* 运输中、已完成页面读取tran_list */
    		$TranList = 'TranList';

            $l = ("left join mk_il_logs l ON(l.id=(select max(id) from mk_il_logs where MKNO=t.MKNO))");
            $files = "t.*,l.content,l.create_time";

    		if($mkno) $map['t.MKNO'] = array('like',$mkno.'%');

    		if($type == 'transport'){
				$map['t.IL_state'] = array('neq','1003');	//运输中
    		}else if($type == 'finished'){
    			$map['t.IL_state'] = array('eq','1003');	//已完成
    		}
    	}

	    $client = $this->client;

	    $list = $client->_list($TranList,$map,$p,$ePage,$l,$files);
    	
    	// $list = $TranList->field($files)->join($l)->where($map)->order('t.optime desc')->page($p.','.$ePage)->select();
    	// dump($list);
    	self::assign('list',$list);// 赋值数据集

    	$count = $client->count($TranList,$map,$l,$files);
		// $count = $TranList->field('t.*,l.content,l.create_time')->join($l)->where($map)->count();// 查询满足要求的总记录数
		$page  = new \Think\Page($count,$ePage);// 实例化分页类 传入总记录数和每页显示的记录数
		$page->setConfig('prev', "上一页");//上一页
		$page->setConfig('next', '下一页');//下一页
		$page->setConfig('first', '首页');//第一页
		$page->setConfig('last', "末页");//最后一页
		$page -> setConfig ( 'theme', '%FIRST% %UP_PAGE% %LINK_PAGE% %DOWN_PAGE% %END% %HEADER%' );

// $parameter = array();
// $parameter['mkno']      = $mkno;
// $parameter['rec']       = $rec;
// $parameter['phone']     = $phone;
// $parameter['starttime'] = $starttime;
// $parameter['endtime']   = $endtime;
// $parameter['ePage']     = $ePage;
// // $parameter['p']     = $p;
// $page->parameter = U ('/', $parameter);

		$show = $page->show();// 分页显示输出
    	self::assign('page',$show);// 赋值分页输出

        $this->display($type);
    }

	/**
	 * 获取详细信息
	 * @return [type] [description]
	 */
	public function info(){
        
		$id   = trim(I('get.id'));
    	$type = I('get.type','index');

    	if($type == 'index'){
    		$TranList = 'TranUlist';
            $l = "LEFT JOIN mk_tran_uorder o ON l.id = o.lid";
    	}else{
    		$TranList = 'TranList';
            $l = "LEFT JOIN mk_tran_order o ON l.id = o.lid";
    	}
		$client = $this->client;

		$res = $client->getInfo($id,$TranList,$l);
		
		// $info = $TranList->where(array('id'=>$id))->find();

		// $pro_list = $TranList->field('o.detail,o.number,o.price')->join('LEFT JOIN mk_tran_order o ON l.id = o.lid')->where(array('l.id'=>$id))->select();

		// 如果是未处理的订单，则不需要查询物流信息
		if($type != 'index'){
			// $msg = M('il_logs')->where(array('MKNO'=>$info['MKNO']))->select();
			$msg = $client->getMsg($res[0]);
			$this->assign('list',$msg);
		}

		$this->assign('info',$res[0]);
        $this->pro_list = $res[1];
		$this->type = $type;

		$this->display();
	}

	/**
	 * 编辑 视图
	 * @return [type] [description]
	 */
	public function edit(){

		$id = trim(I('get.id'));
		$TranList = 'TranUlist';
        $l = "LEFT JOIN mk_tran_uorder o ON l.id = o.lid";
		$client = $this->client;
		$info   = $client->getInfo($id,$TranList,$l);
		// $info = M('TranUlist')->where(array('id'=>$id))->find();

		$this->assign('info',$info[0]);
		$this->display();
	}

	/**
	 * 编辑 方法
	 * @return [type] [description]
	 */
	public function update(){

    	if(!IS_POST){
    		die('非法操作~！');
    	}

        $value = session();
        $username = $value['mkuser']['username'];		//当前登陆的会员

		$id               = trim(I('post.id'));		//mk_tran_list 的id
		$data['receiver'] = trim(I('post.receiver'));	//收件人
		$data['reTel']    = trim(I('post.reTel'));	//收件人电话
		$data['reAddr']   = trim(I('post.reAddr'));	//收件地址

		$Wclient = $this->Wclient;
		
		$result = $Wclient->_update($id,$data,$username);

		$this->ajaxReturn($result);
	}

	/**
	 * 订单删除 方法
	 * @return [type] [description]
	 */
	public function delete(){

    	if(!IS_POST){
    		die('非法操作~！');
    	}

        $value = session();
        $username = $value['admin']['adname'];		//当前登陆的管理员

		$id           = trim(I('post.id'));		//mk_tran_list 的id
		$MKNO         = trim(I('post.MKNO'));		//美快单号

		$Wclient = $this->Wclient;

		$result = $Wclient->_delete($id,$MKNO,$username);

		$this->ajaxReturn($result);

	}



////////////////////////////////////  2015-10-20 搁置
	/**
	 * 添加订单
	 * @return [type] [description]
	 */
	public function newPay(){

		if($_POST){
	        vendor('Hprose.HproseHttpClient');
	        $client = new \HproseHttpClient('http://u3.megao.hk/Api/Server');
	        
			$payMoney = trim(I('post.payMoney'));
			$oddNumbers = trim(I('post.oddNumbers'));
			
			if($payMoney == ''){
				$this->error('请输入支付金额');
			}
			if($oddNumbers == ''){
				$this->error('请输入单号');
			}

			if(is_numeric($payMoney) == false){
				$this->error('请输入数字');
			}

	        $value = session();    
	        $mkuser = $value['mkuser'];

			$res = $client->addOne($payMoney,$oddNumbers,$mkuser);

			if($res['do'] == 'yes'){
				$this->success($res['msg']);
			}else if($res['do'] == 'no'){
				$this->error($res['msg']);
			}

		}else{
			$this->error('页面不存在',U('Member/index'));
		}

	}


}