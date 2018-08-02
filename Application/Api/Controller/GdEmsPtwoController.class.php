<?php
/**
 * 广东邮政之二
 * 功能包括： 支付通知，报关，报关状态
 * 
 */
namespace Api\Controller;
use Think\Controller\HproseController;
class GdEmsPtwoController extends HproseController{    
    protected $crossDomain =    true;
    protected $P3P         =    true;
    protected $get         =    true;
    protected $debug       =    true;

//====================================================
// 支付通知 ok
//====================================================
    // 支付通知 列表页面
    public function paymentList($map){

		$list = M('TransitNo tn')
			  ->field('tn.id,tn.no,tn.date,tn.airdatetime,tn.airno,tn.status,tc.name,tn.pay_report')
			  ->join('LEFT JOIN mk_transit_center tc ON tn.tcid = tc.id')
			  ->where($map)
			  ->order('tn.date asc')
			  ->select();

		foreach($list as $key=>$item){
			$res = $this->fresh_count($item['id']);
			$list[$key]['all']  = $res['al']?$res['al']:0;	//总数
			$list[$key]['done'] = $res['done']?$res['done']:0;	//已发送
			$list[$key]['not']  = $res['ont']?$res['ont']:0;	//未发送
		}
		
		return $list;
    }

	/**
	 * 支付通知 各个批次号的数量统计
	 * @param  [type] $id [tran_list.noid]
	 * @return [type]     [description]
	 */
    public function fresh_count($id){

		$sql = "SELECT  
				sum(noid = $id) AS al,
				sum(send_pay_status >= '0') AS done, 
				sum(send_pay_status is NULL) AS ont
				FROM `mk_tran_list` WHERE `noid` = ".$id;

		$m = new \Think\Model();

		$arr = $m->query($sql);

		$backArr = $arr[0];

		return $backArr;
    }

    /**
     * 支付通知 各个批次号的不同状态的
     * @param  [type] $id   [tran_list.noid]
     * @param  [type] $tcid [transit_center.id]
     * @return [type]       [description]
     */
    public function _sendlist($id, $tcid){

    	// 检查批次号是否属于 广东邮政 专属的线路ID
    	$check = M('TransitNo')->where(array('id'=>$id))->getField('tcid');
    	if($check != $tcid){
    		return array('do'=>'0', 'msg'=>'非法批次号');
    	}

		$map['noid']            = array('eq',$id);
		$map['send_pay_status'] = array('exp','is NULL');

		// $map['id']            = array('eq','12476');
		// $map['id']            = array('in',array('12476','12448'));

    	$list = M('TranList')->where($map)->select();

    	// 拼合对应的商品列表到订单信息中
    	foreach($list as $key=>$item){
    		$list[$key]['Order'] = M('TranOrder')->where(array('lid'=>$item['id']))->select();
    	}

    	$res = $this->paySend($list, $id);

		return $res;

    }

    /**
     * 支付通知 真正执行推送的方法  公共方法
     * @param  [type] $arr [订单复数 二维数组]
     * @param  [type] $id  [transit_no.id]
     * @return [type]      [description]
     */
    public function paySend($arr, $id){
    	
    	require_once('Kdno7.class.php');
    	require_once('Kdno7.conf.php');

    	$Kdno = new \Kdno();

        $i = 0;
        $msg = '';
        // 逐个单号执行推送
        foreach($arr as $key=>$item){

            $res = $Kdno->SendPaymentInfo($item, $config);

            // 推送成功，则更新推送状态
            if($res['code'] == '1'){
                $i++;//成功推送则+1

            }else{//推送失败则记录信息
            	$msg .= '【'.$item['MKNO'].'】推送失败，原因：【'.$res['err'].'】；';
            }
        }

        // 只要该批次号有一个订单是成功推送支付通知的，则把此批次号视为已发送过 支付通知，因此，更新该批次号的 支付通知状态
        if($i > 0){
        	M('TransitNo')->where(array('id'=>$id))->setField('pay_report','1');
        }

        if($i == 0){
        	$backArr = array('do'=>'no', 'msg'=>'推送失败，'.$msg);

        }else if($i == count($arr)){
        	$backArr = array('do'=>'yes', 'msg'=>'推送成功');
        	
        }else{
        	$backArr = array('do'=>'yes', 'msg'=>'部分订单推送失败，'.$msg);
        	
        }

		return $backArr;
    }

    /**
     * payList页面  视图  数据查询
     * @param  [type] $noid  [tran_list.noid]
     * @param  [type] $stype [done 已发送； not 未发送]
     * @param  [type] $p     [分页]
     * @return [type]        [description]
     */
    public function _payList($noid, $stype, $p){
    	$map['noid'] = array('eq',$noid);

		if($stype == 'done'){// 批号对数/快递跟踪/all  20160914 Jie

	    	$map['send_pay_status'] = array('exp','is not NULL');

		}else{
			
			$map['send_pay_status'] = array('exp','is NULL');
		}
		
		$list  = M('TranList')->where($map)->page($p.',30')->select();	//数据列表

		$count = M('TranList')->where($map)->count();		//总数
		$mstr  = M('TransitNo')->where(array('id'=>$noid))->getField('no');	//批次号

		return $res = array($list,$count,$mstr);
    }

    /**
     * 支付通知  分别是 已发送/未发送 的 单个或多个订单 进行推送
     * @param  [type] $nos  [tran_list.id集]
     * @param  [type] $nid  [transit_no.id]
     * @param  [type] $tcid [transit_center.id]
     * @return [type] [description]
     */
    public function _post_pay($nos, $nid, $tcid){
    	// 检查批次号是否属于 广东邮政 专属的线路ID
    	$check = M('TransitNo')->where(array('id'=>$nid))->getField('tcid');
    	if($check != $tcid){
    		return array('do'=>'0', 'msg'=>'非法批次号');
    	}

		$map['id']              = array('in',$nos);
		// $map['send_pay_status'] = array('exp','is NULL');

		$list = M('TranList')->where($map)->select();

    	// 拼合对应的商品列表到订单信息中
    	foreach($list as $key=>$item){
    		$list[$key]['Order'] = M('TranOrder')->where(array('lid'=>$item['id']))->select();
    	}

    	$res = $this->paySend($list, $nid);

		return $res;
    }

//====================================================
// 报关(订单报备)
//====================================================
    /**
     * 报关  批次号列表 视图
     * @param  [type] $map [description]
     * @return [type]      [description]
     */
    public function customsList($map){

		$list = M('TransitNo tn')->field('tn.id,tn.no,tn.date,tn.airdatetime,tn.airno,tn.status,tc.name')
								 ->join('LEFT JOIN mk_transit_center tc ON tn.tcid = tc.id')
								 ->where($map)->order('tn.date asc')->select();

		foreach($list as $key=>$item){
			$res = $this->each_count($item['id']);
			$list[$key]['all']  = $res['all']; //总数
			$list[$key]['not']  = $res['not']; //未发送
			$list[$key]['done'] = $res['done']; //已发送
			$list[$key]['two']  = $res['two']; //已审核
			$list[$key]['four'] = $res['four']; //有误
		}
		
		return $list;
    }

	/**
	 * 报关各状态 查询  
     * @param  [type] $id [tran_list.id]
	 * @return [type] [description]
	 */
    public function each_count($id){
    	$where = array();
		$where['noid'] = array('eq',$id);

		$all = M('TranList')->where($where)->count();	//批次号对应的总数

		$where['custom_status'] = array('eq',0);
		$not = M('TranList')->where($where)->count();	//未发送

		$where['custom_status'] = array('eq',1);
		$done = M('TranList')->where($where)->count();	//已发送

		$where['custom_status'] = array('eq',200);
		$two = M('TranList')->where($where)->count();	//已审核

		$where['custom_status'] = array('eq',400);
		$four = M('TranList')->where($where)->count();	//有误

		$backArr = array('all'=>$all, 'not'=>$not, 'done'=>$done, 'two'=>$two, 'four'=>$four);
		return $backArr;

    }

    /**
     * 获取报关状态
     * @param  [type] $id   [transit_no.id]
     * @param  [type] $tcid [transit_center.id]
     * @return [type]       [description]
     */
    public function _getStatus($id, $tcid){
    	// 检查批次号是否属于 广东邮政 专属的线路ID
    	$check = M('TransitNo')->where(array('id'=>$id))->getField('tcid');
    	if($check != $tcid){
    		return array('do'=>'0', 'msg'=>'非法批次号');
    	}

		$map['noid']          = array('eq',$id);
		$map['custom_status'] = array('eq',0);
// $map['id']            = array('in',array('12544','12471'));
		// $map['id']            = array('eq','12544');
		// $map['id']            = array('in',array('12544','12471','12548','12558','12570','12571','12573'));

    	$list = M('TranList')->where($map)->select();

    	// 拼合对应的商品列表到订单信息中
    	foreach($list as $key=>$item){
    		$list[$key]['Order'] = M('TranOrder')->where(array('lid'=>$item['id']))->select();
    	}
// return $list;
		$res = $this->customSend($list);

		return $res;
    }

    /**
     * orderList页面  视图  数据查询
     * @param  [type] $noid  [tran_list.noid]
     * @param  [type] $stype [done 已发送； not 未发送]
     * @param  [type] $p     [分页]
     * @return [type]        [description]
     */
    public function _orderList($noid, $stype, $p){

    	$map['noid'] = array('eq',$noid);

		if($stype == 'done'){
	    	$map['custom_status'] = array('eq','1');//已发送

		}else if($stype == 'not'){

			$map['custom_status'] = array('eq','0');//未发送
		}else if($stype == 'two'){

			$map['custom_status'] = array('eq','200');//已审核
		}else if($stype == 'four'){

			$map['custom_status'] = array('eq','400');//有误
		}
		
		$list  = M('TranList')->where($map)->page($p.',30')->select();	//数据列表

		$count = M('TranList')->where($map)->count();		//总数
		$mstr  = M('TransitNo')->where(array('id'=>$noid))->getField('no');	//批次号

		return $res = array($list,$count,$mstr);
    }

    /**
     * 报关推送(列出数据以便发送)  分别是 已发送/未发送 的 单个或多个订单 进行推送
     * @param  [type] $nos  [tran_list.id集]
     * @param  [type] $nid  [transit_no.id]
     * @param  [type] $tcid [transit_center.id]
     * @return [type] [description]
     */
    public function _post_order($nos, $nid, $tcid){
    	// 检查批次号是否属于 广东邮政 专属的线路ID
    	$check = M('TransitNo')->where(array('id'=>$nid))->getField('tcid');
    	if($check != $tcid){
    		return array('do'=>'0', 'msg'=>'非法批次号');
    	}

		$map['id']              = array('in',$nos);

		$list = M('TranList')->where($map)->select();

    	// 拼合对应的商品列表到订单信息中
    	foreach($list as $key=>$item){
    		$list[$key]['Order'] = M('TranOrder')->where(array('lid'=>$item['id']))->select();
    	}
// return $list;
    	$res = $this->customSend($list);

		return $res;

    }

    /**
     * 报关推送  方法
     * @param  [type] $arr [订单数组  二维数组]
     * @return [type]      [description]
     */
    public function customSend($arr){
    	require_once('GoodsCustoms.class.php');
    	require_once('GoodsCustoms.conf.php');
// return $arr;
		$GD = new \Custom();
		$GZPort = new \Org\GZP\GZPort();

        $i = 0;
        $msg = '';
        // $tt = array();
        // 逐个单号执行推送
        foreach($arr as $key=>$item){

            $res = $GD->isOrder($item, $Head, $OrderHead, $Elec_order, $GZPort);
            // $res[$key] = $GD->isOrder($item, $Head, $OrderHead, $Elec_order, $GZPort);
            // $tt[$key] = $res[$key];
            
            // 推送成功，则更新推送状态
            if($res['result'] == '1'){
                $i++;
            }else if($res['result'] == '0'){
                $msg .= '【'.$item['MKNO'].'】失败原因：【'.$res['description'].'】；';
            }else{
                $msg .= '【'.$item['MKNO'].'】失败原因：【请检查参数是否填写完整】；';
            }

        }

// return $tt;
        if($i == 0){
        	$backArr = array('do'=>'no', 'msg'=>'推送失败，'.$msg);

        }else if($i == count($arr)){
        	$backArr = array('do'=>'yes', 'msg'=>'推送成功');
        	
        }else{
        	$backArr = array('do'=>'yes', 'msg'=>'部分订单推送失败，'.$msg);
        	
        }

		return $backArr;
    }

//========================================================
//报关状态 列表
//========================================================
    public function state_list($map,$p,$ePage){
    	
		$count = M('TranList')->where($map)->count();		//总数
		$list  = M('TranList')->where($map)->page($p.','.$ePage)->select();	//数据列表
		return array('list'=>$list, 'count'=>$count);

    }
}