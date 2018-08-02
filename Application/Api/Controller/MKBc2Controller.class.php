<?php
/**
 * 美快BC优选2
 * 功能包括： 订单操作
 * 
 */
namespace Api\Controller;
use Think\Controller\HproseController;
class MKBc2Controller extends HproseController{    
    protected $crossDomain =    true;
    protected $P3P         =    true;
    protected $get         =    true;
    protected $debug       =    true;

    // 订单操作 列表页面
    public function getList($map){

		$list = M('TransitNo tn')->field('tn.id,tn.no,tn.date,tn.airdatetime,tn.airno,tn.status,tc.name,tn.tcid')
								 ->join('LEFT JOIN mk_transit_center tc ON tn.tcid = tc.id')
								 ->where($map)->order('tn.date asc')->select();

		foreach($list as $key=>$item){
			$res = $this->fresh_count($item['id']);
            $list[$key]['all']  = $res['all'];  //总数
            $list[$key]['done'] = $res['done']; //已发送
            $list[$key]['not']  = $res['not']; //未发送
		}
		
		return $list;
    }

	/**
	 * 
	 * @return [type] [description]
	 */
    public function fresh_count($id){
    	$where = array();
		$where['noid'] = array('eq',$id);

        $all = M('TranList')->where($where)->count();   //批次号对应的总数

        //ifnull   判断它为null时给一个默认值 0
        $where['e.order_opt_state'] = array('eq',0);
        $not = M('TranList t')->field('t.*,ifnull(e.order_opt_state,0) as order_opt_state')->join('left join mk_tran_list_state e on e.lid = t.id')->where($where)->count();   //未发送

        $where['e.order_opt_state'] = array('egt',1);
        $done = M('TranList t')->field('t.*,ifnull(e.order_opt_state,0) as order_opt_state')->join('left join mk_tran_list_state e on e.lid = t.id')->where($where)->count();  //已发送

        $backArr = array('all'=>$all, 'not'=>$not, 'done'=>$done);

        return $backArr;

    }

    /**
     * 某个批次号对应的所有 未进行订单操作  的订单数据
     * @param  string  $noid [tran_list.noid]
     * @param  [type]  $kind [类型：done已发送， not未发送]
     * @param  integer $p    [分页页数]
     * @return [type]        [description]
     */
    public function _predict($noid='', $kind, $p=1){

        if($kind == 'done'){
            $map['e.order_opt_state'] = array('gt',0);   //美快BC优选2  对单对接状态为1
        }else if($kind == 'not'){
            $map['e.order_opt_state'] = array('eq',0);   //美快BC优选2  对单对接状态为0
        }

        $map['t.noid']        = array('eq',$noid);   //中转批号

        //ifnull   判断它为null时给一个默认值 0
        $list = M('TranList t')->field('t.*,l.no,l.tcid,ifnull(e.order_opt_state,0) as order_opt_state')->join('LEFT JOIN mk_transit_no l ON l.id = t.noid')->join('left join mk_tran_list_state e on e.lid = t.id')->where($map)->page($p.',50')->select();
        $count = M('TranList t')->join('LEFT JOIN mk_transit_no l ON l.id = t.noid')->join('left join mk_tran_list_state e on e.lid = t.id')->where($map)->count();

        return $res = array($list,$count);
    }

    /**
     * 单个或多个订单
     * @param  [type] $MKNO [description]
     * @return [type]       [description]
     */
    public function getData($MKNO){
        //ifnull   判断它为null时给一个默认值 0
        $info = M('TranList t')->field('t.*,d.detail,d.weight as dweight,d.price as dprice,d.number as dnumber,ifnull(e.order_opt_state,0) as order_opt_state')->join('left join mk_tran_order d on d.lid = t.id')->join('left join mk_tran_list_state e on e.lid = t.id')->where(array('t.MKNO'=>$MKNO))->select();
        return $info;
    }

    /**
     * 更新美快BC优选2  对单对接状态为1   !!!!
     * @param  [type] $arr [MKNO的二维数组]
     * @return [type]      [description]
     */
    public function saveStatus($arr,$state,$type='more'){
        if($type == 'more'){
            foreach($arr as $item){

                //检查是否已经存在
                $check = M('TranListState')->where(array('lid'=>$item))->find();
                if($check){
                   // M('TranList')->where(array('MKNO'=>$item))->setField('order_opt_state',$state);
                    M('TranListState')->where(array('lid'=>$item))->setField('order_opt_state',$state); 
                }else{
                    $data['lid']             = $item;
                    $data['order_opt_state'] = $state;
                    M('TranListState')->add($data);
                }
            }
        }else{

            //检查是否已经存在
            $check = M('TranListState')->where(array('lid'=>$arr))->find();
            if($check){
                // M('TranList')->where(array('MKNO'=>$arr))->setField('order_opt_state',$state);
                M('TranListState')->where(array('lid'=>$arr))->setField('order_opt_state',$state);
            }else{
                $data['lid']             = $arr;
                $data['order_opt_state'] = $state;
                M('TranListState')->add($data);
            }
        }
    }
    /**
     * 取消订单 所需的资料
     * @param  [type] $MKNO [description]
     * @return [type]       [description]
     */
    public function cel_info($MKNO){
        $info = M('TranList t')->field('t.id,t.MKNO,t.STNO,ifnull(e.order_opt_state,0) as order_opt_state')->join('left join mk_tran_list_state e on e.lid = t.id')->where(array('t.MKNO'=>$MKNO))->find();

        return $info;
    }

    /**
     * 更改新的运单号
     * @param  [type] $MKNO [description]
     * @param  [type] $no   [新STNO]
     * @return [type]       [description]
     */
    public function newNUM($MKNO, $no){
        M('TranList')->where(array('MKNO'=>$MKNO))->setField('STNO',$no);
    }

//=============
/*    // 根据批次号进行统一发送   暂时不可用
    public function _readyPost($id){
        $list = M('TranList')->field('MKNO')->where(array('noid'=>$id))->select();
        return $list;
    }*/
//============
}