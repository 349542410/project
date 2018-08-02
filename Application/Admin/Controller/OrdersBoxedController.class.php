<?php

/**
 * 西安物流
*/

namespace Admin\Controller;
use Think\Controller;

class OrdersBoxedController extends AdminbaseController{

	function _initialize(){

		parent::_initialize();

		$client = new \HproseHttpClient(C('RAPIURL').'/OrdersBoxed');//读取、查询操作

		$this->client = $client;		//全局变量
	}

	//订单装板
	public function index(){

		$this->display();
	}

	public function form_index(){

		if(!IS_POST && !IS_AJAX){

			$this->ajaxReturn(array('state'=>'no','msg'=>'非法提交'));
		}

		$no = trim(I('post.no'));//要查询的单号

		$searchtype = I('post.searchtype',0);//查询的类型

		if(empty($no)){

			$this->ajaxReturn(array('state'=>'no','msg'=>'请输入单号'));
		}

		switch($searchtype){
			case 'MKNO':
			$type = 1;
			break;

			case 'STNO':
			$type = 2;
			break;

			case 'PNO':
			$type = 3;
			break;

			default:
			$this->ajaxReturn(array('state'=>'no','msg'=>'请选择正确的查询类型'));
			break;
		}

		// 把空格、换行符、中文逗号 等 替换成英文逗号
		$nos = preg_replace("/(\n)|(\s)|(\t)|(\')|(')|(，)|(\.)|(\/)|(、)|(；)|(;)/",',',$no);

		//分割成数组
		$no_arr = explode(',',$nos);

		//去除空元素
		$f_arr = array_filter($no_arr);

		//去重
		$arr = array_unique($f_arr);

		//数组要重新排序
		sort($arr);

		$count = count($arr);

		//批次号只能查询一个
		if($type == 3){

			if($count > 1){

				$arr = array_slice($arr,0,1);
			}

		}else{

			if($count > 1000){

				//切割数组
				$arr = array_slice($arr,0,1000);
			}
		}

		$client = new \HproseHttpClient(C('RAPIURL').'/AdminOrdersBoxed');//读取、查询操作

		$res = $client->_postNo($arr,$searchtype);

		$this->ajaxReturn($res);
	}

	//装板记录  订单
	public function info(){

		$keyword = trim(I('get.keyword'));//查询内容

		$searchtype = I('get.searchtype');//查询类型

		$starttime = intval(I('get.starttime'));//开始时间

		$endtime = intval(I('get.endtime'));//结束时间

		$ePage = (I('get.ePage')) ? trim(I('get.ePage')) : C('EPAGE');

		$p = (I('p')) ? trim(I('p')) : '1';

		$where['s.xa_bnum'] = array('NEQ','');

		if(!empty($keyword)){

			switch ($searchtype) {

				case 'STNO':
					$where['l.STNO'] = $keyword;
					break;

				case 'MKNO':
					$where['l.MKNO'] = $keyword;
					break;

				case 'BNUM':
					$where['s.xa_bnum'] = $keyword;
					break;

				default:
					$this->error('请选择正确的查询类型',U('OrdersBoxed/info'),1);
					break;
			}
		}

		$start = date('Y-m-d H:i:s',$starttime);

		$end = date('Y-m-d H:i:s',$endtime);

		if($starttime && $endtime){

			$where['s.xa_btime'] = array('between',array($start,$end));

		}else if(!$starttime && $endtime){

			$where['s.xa_btime'] = array('elt',$end);

		}else if($starttime && !$endtime){

			$where['s.xa_btime'] = array('egt',$start);
		}

		$this->assign($_GET);

		$this->assign('ePage',$ePage);

		$res = $this->client->tran_info($where,$p,$ePage);

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

	//装板记录  批次号
	public function info_pno(){

		$keyword = trim(I('get.keyword'));//查询内容

		$searchtype = I('get.searchtype');//查询类型

		$starttime = intval(I('get.starttime'));//开始时间

		$endtime = intval(I('get.endtime'));//结束时间

		$ePage = (I('get.ePage')) ? trim(I('get.ePage')) : C('EPAGE');

		$p = (I('p')) ? trim(I('p')) : '1';

		$where['s.xa_bnum'] = array('NEQ','');

		if(!empty($keyword)){

			switch ($searchtype) {

				case 'PNO':
					$where['l.no'] = $keyword;
					break;

				case 'BNUM':
					$where['s.xa_bnum'] = $keyword;
					break;

				default:
					$this->error('请选择正确的查询类型',U('OrdersBoxed/info_pno'),1);
					break;
			}
		}

		$start = date('Y-m-d H:i:s',$starttime);

		$end = date('Y-m-d H:i:s',$endtime);

		if($starttime && $endtime){

			$where['s.xa_btime'] = array('between',array($start,$end));

		}else if(!$starttime && $endtime){

			$where['s.xa_btime'] = array('elt',$end);

		}else if($starttime && !$endtime){

			$where['s.xa_btime'] = array('egt',$start);
		}

		$this->assign($_GET);

		$this->assign('ePage',$ePage);

		//20180417  改为在数据库中进行分页操作
		$res = $this->client->tran_info_pno($where,$p,$ePage);

		$count = $res['count'];

		$list  = $res['list'];

		$page = new \Think\Page($count,$ePage); // 实例化分页类 传入总记录数和每页显示的记录数(20)

		$page->setConfig('prev', "上一页");//上一页
		$page->setConfig('next', '下一页');//下一页
		$page->setConfig('first', '首页');//第一页
		$page->setConfig('last', "末页");//最后一页
		$page->setConfig ( 'theme','%FIRST% %UP_PAGE% %LINK_PAGE% %DOWN_PAGE% %END% %HEADER%');

		$show = $page->show(); // 分页显示输出

		$this->assign('page',$show);// 赋值分页输出

		$this->assign('list',$list);

		// $list = $this->client->tran_info_pno($where);

		// $count = count($list);

		// $Page = new \Think\Page($count,$ePage);// 实例化分页类 传入总记录数和每页显示的记录数

		// $array = array_slice($list,$Page->firstRow,$Page->listRows);

		// $Page->setConfig('prev', "上一页");//上一页
		// $Page->setConfig('next', '下一页');//下一页
		// $Page->setConfig('first', '首页');//第一页
		// $Page->setConfig('last', "末页");//最后一页
		// $Page->setConfig ('theme', '%FIRST% %UP_PAGE% %LINK_PAGE% %DOWN_PAGE% %END% %HEADER%');

		// $show = $Page->show();// 分页显示输出

		// $this->assign('page',$show);// 赋值分页输出

		// $this->assign('list',$array);

		$this->display();
	}
}
