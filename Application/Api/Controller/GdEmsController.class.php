<?php
/**
 * 广东邮政之一
 * 功能包括： 发货通知，商品报备，批号对数
 * 
 */
namespace Api\Controller;
use Think\Controller\HproseController;
class GdEmsController extends HproseController{    
    protected $crossDomain =    true;
    protected $P3P         =    true;
    protected $get         =    true;
    protected $debug       =    true;

    // 发送报告 列表页面
    public function getList($map){

		$list = M('TransitNo tn')->field('tn.id,tn.no,tn.date,tn.airdatetime,tn.airno,tn.status,tc.name')
								 ->join('LEFT JOIN mk_transit_center tc ON tn.tcid = tc.id')
								 ->where($map)->order('tn.date asc')->select();

		foreach($list as $key=>$item){
			$res = $this->fresh_count($item['id']);
			$list[$key]['all'] = $res;
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
		$all = M('TranList')->where($where)->count();	//总数

		$backArr = $all;
		return $backArr;

    }

    public function getInfo($no){
    	//取最新的一条数据
        $res = M('EmsReport')->where(array('no'=>$no))->order('id desc')->find();
        return $res;
    }

    /**
     * 预报订单 方法
     * 思路：讲收到的批次号id和其他必要的数据
     * 1.先进行数据检验，还有时间日期的验证；
     * 2.根据批次号id，搜索出mk_tran_list中符合的所有数据，将这些数据 组装成ShipmentNumbers必需的数组形式，然后把其他必要的数据和ShipmentNumbers数组传入
     * 	  Kdno6.class.php类中，调用reportOrder()方法；
     * 3. Kdno6类 讲这些传入的数据组合成数组并json格式化，再将此json发送到请求地址，收到反馈原路返回结果；如果反馈的结果为成功，则更新此批次号id对应的字段send_report
     * 为1(表示已经执行预报订单操作)；
     * 4.在此，反馈给前端页面之前，需要对反馈信息进行加工或改进处理再进行返回
     * @return [type] [description]
     */
    public function _report($id, $no, $number, $re_time, $country, $trankd){
        // $re_time = date('Y-m-d H:i:s',$re_time);
        
    	$where = array();
		$where['noid'] = array('eq',$id);
		$all = M('TranList')->field('auto_Indent1,auto_Indent2,STNO,weight')->where($where)->select();	//总数
		// return $all;
        if(count($all) == 0){
            return array('Status'=>'false', 'Message'=>'该批次号暂无数据录入，禁止操作');
        }

        $check = M('TransitNo')->where(array('id'=>$id))->getField('send_report');
        if($check == '1'){
            return array('Status'=>'false', 'Message'=>'该批次号暂无数据录入，禁止操作');
        }

        $total_weight = 0;
		// 组装ShipmentNumbers属性($list)
		$list = array();
		foreach($all as $key=>$item){
			// $list[$key]['BagNo']       = '1';
			// $list[$key]['ReferenceId'] = $item['auto_Indent2']."_".$item['auto_Indent1'];
			// $list[$key]['TrackingNo']  = $item['STNO'];
			// $list[$key]['Weight']      = $item['weight'];
			$list[$key] = $item['STNO'];//子运单号集合
			$total_weight += floatval($item['weight']);//总重量
		}
		// return $list;
		// return sprintf("%.2f", $total_weight);

		$total_weight = sprintf("%.2f", $total_weight);

    	require_once(dirname(__FILE__).'\Kdno7.class.php');
    	$EMS = new \Kdno();

    	$res = $EMS->UpLoadTotalRelation($list, $number, $re_time, $country, $total_weight);

    	// 预报订单 成功，则更新标识码
    	if($res['IsSuccess'] === true){
    		M('TransitNo')->where(array('id'=>$id))->setField('send_report','1');//1 表示已经成功执行

            $data = array();
			$data['WayBillNumber'] = $number;
			$data['ETA']           = date('Y-m-d H:i:s',$re_time);
			$data['CountryCode']   = $country;
			$data['no']            = $no;
			$data['TranKd']        = $trankd;
            M('EmsReport')->add($data);//由于mk_ems_report用于保存记录，所以插入数据的时候无需检验是否存在
    	}

    	return $res;
		
    }

    /**
     * 商品报备列表 count
     * @param  [type] $map [description]
     * @return [type]      [description]
     */
    public function _allpy_count($map,$p,$ePage){
        $list = M('ApplyList a')->field('a.*, u.unname')->join('left join mk_union u on a.CID=u.id')->where($map)->order('id')->page($p.','.$ePage)->select();

        $union = M('Union')->field('id,unname')->select();

        $count = M('ApplyList')->where($map)->count(); // 查询满足要求的总记录数
        
        return array('list'=>$list, 'union'=>$union, 'count'=>$count);
    }

    /**
     * 商品报备 单个商品info
     * @param  [type] $map [description]
     * @return [type]      [description]
     */
    public function getGoodInfo($id){
        $res = M('ApplyList')->where(array('id'=>$id))->find();
        return $res;
    }

    /**
     * 修改商品报备信息
     * @param  [type] $map [description]
     * @return [type]      [description]
     */
    public function editGoodInfo($arr){
        $id = $arr['id'];
        
        $check = M('ApplyList')->where(array('id'=>$id))->find();

        if($check['apply_status'] == '1'){
            return array('state'=>'no', 'msg'=>'该商品已经成功执行海关报备，为保证报备资料一致，不可修改！');
        }

        $save = M('ApplyList')->where(array('id'=>$id))->save($arr);

        if($save === false){
            $res = array('state'=>'no', 'msg'=>'更新失败');
        }else if($save == 0){
           $res = array('state'=>'no', 'msg'=>'您没有修改任何数据');
        }else{
            $res = array('state'=>'yes', 'msg'=>'更新成功');
        }

        return $res;
    }

    /**
     * 海关商品报备
     * @param  [type] $id [description]
     * @return [type]     [description]
     */
    public function _applyHG($id){
        // C('SHOW_PAGE_TRACE','');//直接在此关闭SHOW_PAGE_TRACE
        require_once('GoodsCustoms.class.php');

        $info = M('ApplyList')->where(array('id'=>$id))->find();

        if($info['apply_status'] == '1'){
            $backArr = array(
                'status' => '0',
                'msg' => '【'.$info['EntGoodsNo'].'】已通过申报',
            );
            return $backArr;
        }

        $GD = new \Custom();
        $res = $GD->isGoods($info, true);

        // 返回数据格式：Array ( [description] => 申报【KJ881101_TEST17_20170320100200411883】成功！ [result] => 1 ) 
        if($res['result'] == '1'){
            $status = '1';
            $msg = '成功';
        }else{
            $status = '0';
            $msg = '失败';
        }

        $backArr = array(
            'status' => $status,
            'msg' => '【'.$info['EntGoodsNo'].'】申报'.$msg.'，海关反馈：'.$res['description'],
        );
        return $backArr;
    }

    /**
     * EMS商品报备
     * @param  [type] $id [description]
     * @return [type]     [description]
     */
    public function _applyEMS($id){
        require_once('Kdno7.class.php');

        $info = M('ApplyList')->where(array('id'=>$id))->find();

        if($info['apply_status'] == '0'){
            return array('state'=>'no', 'msg'=>'【'.$info['EntGoodsNo'].'】海关报备不通过，不允许EMS报备！');
        }

        if($info['ems_status'] == '1'){
            $backArr = array(
                'status' => '0',
                'msg' => '【'.$info['EntGoodsNo'].'】已通过申报',
            );
            return $backArr;
        }

        $EMS = new \Kdno();
        $res = $EMS->ApplyGoodsRecord($info,true);

        // 返回数据格式：Array ( [IsSuccess] => [Message] => 海关HsCode(校验前8位)不在正面清单内 ) 
        if($res['IsSuccess'] == '1'){
            $status = '1';
            $msg = '成功';
        }else{
            $status = '0';
            $msg = '失败';
        }

        $backArr = array(
            'status' => $status,
            'msg' => '【'.$info['EntGoodsNo'].'】申报'.$msg.'，EMS反馈：'.$res['Message'],
        );
        return $backArr;
    }
}