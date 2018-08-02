<?php
/**
 * 充值中心 用户概况：账户余额，充值记录，订单记录
 */
namespace WebUser\Controller;
use Think\Controller;
class WebRechargeController extends BaseController {
	public function _initialize(){
		parent::_initialize();
        $Wclient = new \HproseHttpClient(C('WAPIURL').'/WebRecharge');
        $this->Wclient = $Wclient;

	}

	//测试支付功能  视图
	public function test(){
		$a = '3';
		$b = '6';
		echo $b;
		$a & $b = $a;
		echo $b;
		$this->display();
	}

	// 充值中心 用户概况 视图
	public function index(){
		$user_id = session('mkuser.uid');

		$ePage = 15;
		
		$Wclient = $this->Wclient;

    	$first = $Wclient->firstCount($user_id);
    	
		$amount = $first['amount'];  //账户余额
		$count  = $first['count'];	 //充值记录总数
		$scount = $first['scount'];	//订单记录总数

		$page  = new \Libm\WebUser\MyPage($count,$ePage,"p1");// 实例化分页类 传入总记录数和每页显示的记录数

		$page->setConfig('prev', L('PrevPage'));//上一页
		$page->setConfig('next', L('NextPage'));//下一页
		$page->setConfig('first', L('FirstPage'));//第一页
		$page->setConfig('last', L('LastPage'));//最后一页
		$page->setConfig ( 'theme', '%FIRST% %UP_PAGE% %LINK_PAGE% %DOWN_PAGE% %END% %HEADER%' );

		$limit = $page->firstRow.','.$page->listRows;
		$list = $Wclient->rechargeList($user_id,$limit);
		
		$show = $page->show();// 分页显示输出
    	self::assign('page',$show);// 赋值分页输出
    	self::assign('list',$list);// 充值记录明细
    	self::assign('count',$count);// 充值记录总数
    	self::assign('amount',sprintf("%.2f", $amount));// 账户余额

    	//订单记录
		$page2  = new \Libm\WebUser\MyPage($scount,$ePage,"p2");// 实例化分页类 传入总记录数和每页显示的记录数

		$limit2 = $page2->firstRow.','.$page->listRows;
		$order_list = $Wclient->orderList($user_id,$limit2);

		$page2->setConfig('prev', L('PrevPage'));//上一页
		$page2->setConfig('next', L('NextPage'));//下一页
		$page2->setConfig('first', L('FirstPage'));//第一页
		$page2->setConfig('last', L('LastPage'));//最后一页
		$page2->setConfig ( 'theme', '%FIRST% %UP_PAGE% %LINK_PAGE% %DOWN_PAGE% %END% %HEADER%' );

		$show2 = $page2->show();// 分页显示输出
    	self::assign('page2',$show2);// 赋值分页输出
    	self::assign('slist',$order_list);// 充值记录明细
    	self::assign('scount',$scount);// 充值记录总数

        $this->display();
	}

	public function error(){
		$this->display();
	}
	public function success(){
		$this->display();
	}
	public function payment(){
		$this->display();
	}
	public function recharge(){
		$this->display();
	}
	public function recharge_error(){
		$this->display();
	}
	public function recharge_success(){
		$this->display();
	}

}