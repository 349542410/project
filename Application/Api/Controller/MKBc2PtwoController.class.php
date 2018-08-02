<?php
/**
 * 广东邮政之二
 * 功能包括： 支付通知，报关，报关状态
 * 
 */
namespace Api\Controller;
use Think\Controller\HproseController;
class MKBc2PtwoController extends HproseController{
    protected $crossDomain =    true;
    protected $P3P         =    true;
    protected $get         =    true;
    protected $debug       =    true;

//====================================================
// 报关(订单报备)
//====================================================
    /**
     * 20180503 MKBc2Ptwo\apply_customs不用该方法
     * 报关  批次号列表 视图
     * @param  [type] $map [description]
     * @return [type]      [description]
     */
    public function customsList($map){

        $list = M('TransitNo tn')
              ->field('tn.id,tn.no,tn.date,tn.airdatetime,tn.airno,tn.status,tc.name')
              ->join('LEFT JOIN mk_transit_center tc ON tn.tcid = tc.id')
              ->where($map)
              ->order('tn.date asc')
              ->select();

		foreach($list as $key=>$item){
            $res = $this->each_count($item['id']);
            $list[$key]['all']  = $res['al']?$res['al']:0; //总数
			$list[$key]['not']  = $res['one']?$res['one']:0; //未发送
			$list[$key]['done'] = $res['done']?$res['done']:0; //已发送
			$list[$key]['two']  = $res['two']?$res['two']:0; //已审核
			$list[$key]['four'] = $res['four']?$res['four']:0; //有误
		}
		
		return $list;
    }

	/**
	 * 报关各状态 查询  
     * @param  [type] $id [tran_list.id]
	 * @return [type] [description]
	 */
    public function each_count($id){

        $sql = "SELECT  
                sum(noid = $id) AS al,
                sum(custom_status = '0') AS one, 
                sum(custom_status = '1') AS done, 
                sum(custom_status = '200')AS two, 
                sum(custom_status = '400')AS four 
                FROM `mk_tran_list` WHERE `noid` = ".$id;

        $m = new \Think\Model();

        $arr = $m->query($sql);

        $backArr = $arr[0];

        return $backArr;
    }

    /**
     * 获取报关状态
     * @param  [type] $id   [transit_no.id]
     * @param  [type] $tcid [transit_center.id]
     * @return [type]       [description]
     */
    public function _getStatus($id, $tcid){
    	// 检查批次号是否属于 MkBc2 专属的线路ID
    	$check = M('TransitNo')->where(array('id'=>$id))->getField('tcid');
    	if($check != $tcid){
    		return array('do'=>'0', 'msg'=>'非法批次号');
    	}

		$map['noid']          = array('eq',$id);
		$map['custom_status'] = array('eq',0);

        // 测试用
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
    	require_once('MkBc2Customs.class.php');
// return $arr;
		$MkBc = new \MKBc2CustomsApi();

        $i = 0;
        $msg = '';
        // $tt = array();
        // 逐个单号执行推送
        foreach($arr as $key=>$item){

            $res = $MkBc->request($item);
/*            $res[$key] = $MkBc->request($item);
            $tt[$key] = $res[$key];*/
            
            // 推送成功，则更新推送状态
            if($res['Result'] == '1'){
                $i++;
            }else if($res['Result'] == '0'){
                $msg .= '【'.$item['MKNO'].'】失败原因：【'.$res['Error']['LongMessage'].'】；';
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

    /**
     * 报关 --- 资料导出(导出成功后直接提成用户下载)
     * 导出文件，是用于手动形式报关，处理结果跟“发送”形式的报关一致
     * @return [type] [description]
     */
    public function _export_file($noid){
        //因为导出的资料不需要带序号，所以从f1开始记数
        $fit = "l.id as f1,l.MKNO as f2,l.idno as f3,CAST( o.number * o.price AS DECIMAL(10,2)) as f4,ifnull(l.paytime,l.optime) as f5,l.receiver as f6,l.reAddr as f7,l.reTel as f8,l.province as f9,l.city as f10,l.town as f11,l.postcode as f12,l.notes as f13,o.hgid as f14,o.number as f15,o.price as f16,l.payno as f17,concat_ws('',l.auto_Indent2,l.auto_Indent1) as f18,l.receiver as f19,l.STNO as f20,l.weight as f21,o.detail as f22,o.hs_code as f23";
        
        /* 20160123 */
        $list = M('TranList l')
        ->field($fit)
        ->join('LEFT JOIN mk_tran_order o ON o.lid = l.id')
        ->where(array('noid'=>$noid))->select();

        return $list;
    }

    /**
     * [saveState description]
     * @param  [type] $arr    [STNO数组]
     * @param  [type] $TranKd [TranKd]
     * @return [type]         [description]
     */
    public function saveState($arr, $TranKd){
        foreach($arr as $item){

            //更新报关状态
            $res = M('TranList')->where(array('STNO'=>$item))->setField('custom_status',1);

            //检查报关信息是否已存在
            $check_trainer = M('Trainer')->where(array('LogisticsNo'=>$item))->find();

            // 新增或更新报关信息
            $data = array();
            if(!$check_trainer){
                $data['LogisticsNo'] = $item;
                $data['Status']      = 1;
                $data['Result']      = '已导出';
                $data['CreateTime']  = date('Y-m-d H:i:s');
                $data['TranKd']      = $TranKd;
                M('Trainer')->add($data);

            }else{//更新
                $data['Status']      = 1;
                $data['Result']      = '已导出';
                $data['CreateTime']  = date('Y-m-d H:i:s');
                M('Trainer')->where(array('id'=>$check_trainer['id']))->save($data);
            }

            // 保存报关操作记录
            $log = array();
            $log['LogisticsNo'] = $item;
            $log['Status']      = 1;
            $log['content']     = '导出成功';
            $log['CreateTime']  = date('Y-m-d H:i:s');
            M('TrainerLogs')->add($log);

        }
    }
}