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

	// 充值中心 用户概况 视图
	public function index(){
		$user_id = session('mkuser.uid');
		session('amount_sign',null);

		// echo "<br><br><br><br><br><br><br><br><br><br><br>";
		// dump($_GET);
		// die;

		$w_where = array();
		$pay_starttime = I('get.pay_starttime');
		$pay_endtime = I('get.pay_endtime');
		if(!empty($pay_starttime) && !empty($pay_endtime)){
			$w_where['_string'] = "a.paytime > '" . date("Y-m-d H:i:s",$pay_starttime) . "' and a.paytime < '" . date("Y-m-d H:i:s",$pay_endtime) . "'";
		}else if(!empty($pay_starttime)){
			$w_where['_string'] = "a.paytime > '" . date("Y-m-d H:i:s",$pay_starttime) . "'";
		}else if(!empty($pay_endtime)){
			$w_where['_string'] = "a.paytime < '" . date("Y-m-d H:i:s",$pay_endtime) . "'";
		}

		$r_where = array();
		$recharge_starttime = I('get.recharge_starttime');
		$recharge_endtime = I('get.recharge_endtime');
		if(!empty($recharge_starttime) && !empty($recharge_endtime)){
			$r_where['_string'] = "paytime > '" . date("Y-m-d H:i:s",$recharge_starttime) . "' and paytime < '" . date("Y-m-d H:i:s",$recharge_endtime) . "'";
		}else if(!empty($recharge_starttime)){
			$r_where['_string'] = "paytime > '" . date("Y-m-d H:i:s",$recharge_starttime) . "'";
		}else if(!empty($recharge_endtime)){
			$r_where['_string'] = "paytime < '" . date("Y-m-d H:i:s",$recharge_endtime) . "'";
		}


		$this->assign('pay_starttime',$pay_starttime);
		$this->assign('pay_endtime',$pay_endtime);
		$this->assign('recharge_starttime',$recharge_starttime);
		$this->assign('recharge_endtime',$recharge_endtime);
		

		$ePage = 12;
		
		$Wclient = $this->Wclient;

		$first = $Wclient->firstCount($user_id,$w_where,$r_where);
		
		// dump($w_where);
		// dump($r_where);
		// dump($first);
		// die;
    	
		$amount = $first['amount'];  //账户余额
		$count  = $first['count'];	 //充值记录总数
		$scount = $first['scount'];	//订单记录总数

		$page  = new \Libm\Common\Page($count,$ePage,"p1");// 实例化分页类 传入总记录数和每页显示的记录数

		$page->setConfig('prev', L('PrevPage'));//上一页
		$page->setConfig('next', L('NextPage'));//下一页
		$page->setConfig('first', L('FirstPage'));//第一页
		$page->setConfig('last', L('LastPage'));//最后一页
		$page->setConfig('header', '<span class="rows">'.L('TotalPage',array('n'=>'%TOTAL_ROW%')).'</span>');
		$page->setConfig ( 'theme', '%FIRST% %UP_PAGE% %LINK_PAGE% %DOWN_PAGE% %END% %HEADER%' );

		// dump($where);

		$limit = $page->firstRow.','.$page->listRows;
		$list = $Wclient->rechargeList($user_id,$limit,$r_where);
		
		$show = $page->show();// 分页显示输出
    	self::assign('page',$show);// 赋值分页输出
    	self::assign('list',$list);// 充值记录明细
    	self::assign('count',$count);// 充值记录总数
    	self::assign('amount',sprintf("%.2f", $amount));// 账户余额

    	//订单记录
		$page2  = new \Libm\Common\Page($scount,$ePage,"p2");// 实例化分页类 传入总记录数和每页显示的记录数

		$limit2 = $page2->firstRow.','.$page->listRows;
		$order_list = $Wclient->orderList($user_id,$limit2,$w_where);


		//求和附加费
		foreach($order_list as $k1=>$v1){
			$order_list[$k1]['line_name'] = L('MKLINES')[$v1['lngname']];
			$sum = 0;
			foreach($v1['extra_fee'] as $v2){
				$sum += $v2['extra_fee'];
			}
			$order_list[$k1]['extra_fee'] = $sum;
		}

		// dump($order_list);

		$page2->setConfig('prev', L('PrevPage'));//上一页
		$page2->setConfig('next', L('NextPage'));//下一页
		$page2->setConfig('first', L('FirstPage'));//第一页
		$page2->setConfig('last', L('LastPage'));//最后一页
		$page2->setConfig('header', '<span class="rows">'.L('TotalPage',array('n'=>'%TOTAL_ROW%')).'</span>');
		$page2->setConfig ( 'theme', '%FIRST% %UP_PAGE% %LINK_PAGE% %DOWN_PAGE% %END% %HEADER%' );

		$show2 = $page2->show();// 分页显示输出
    	self::assign('page2',$show2);// 赋值分页输出
    	self::assign('slist',$order_list);// 充值记录明细
    	self::assign('scount',$scount);// 充值记录总数
    	self::assign('US_TO_RMB_RATE',C('US_TO_RMB_RATE'));// 汇率


        $decimal_recharge_statu = $Wclient->get_decimal_recharge_statu($user_id);
        self::assign('decimal_recharge_statu',$decimal_recharge_statu);// 查询出是否允许小数充值

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


	// 导出到excel
	public function export_excel(){

        $ids = I('get.ids');
        $user_id = session('user_id');

        $result = $this->Wclient->get_export_data($ids, $user_id);

        $sf = [
            ['package_id', '外部订单号'],
            ['order_no', '美快订单号'],
            ['MKNO', '美快运单号'],
            ['STNO', '快递单号'],
            ['line_name', '线路'],
            ['transit', '使用快递'],
            ['paytime', '支付时间'],
            ['cost_type', '支付类型'],
            ['tax', '总税金'],
            ['freight', '运费'],
            ['sender', '寄件人姓名'],
            ['receiver', '收件人姓名'],
        ];

        foreach($result as $k=>$v){
            $result[$k]['line_name'] = L('MKLINES')[$v['line_name']];
            $result[$k]['cost_type'] = ($v['cost_type']==0) ? '消费' : (($v['cost_type']==1) ? '补扣' : (($v['cost_type']==2) ? '退款' : '未知'));
        }

        $header = [];
        $body = [];

        foreach($sf as $k=>$v){
            $header[$k] = $v[1];
        }

        foreach($result as $k1=>$v1){
            foreach($sf as $k2=>$v2){
                $body[$k1][$k2] = $v1[$v2[0]];
            }
        }

        foreach($body as $k1=>$v1){
            foreach($v1 as $k2=>$v2){
                $body[$k1][$k2] = ' ' . (string)$v2;
            }
        }

//        dump($header); dump($body); die;

        $excel = new \WebUser\PHPExcel\PHPExcel();
        $excel->write_empty($header, $body, '消费记录' . date("Ymd"));

    }

}