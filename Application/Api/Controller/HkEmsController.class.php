<?php
/**
 * 香港邮政
 * IL_state 字段用作查询的时候，要以数字的形式进行查询 Jie 20160314
 */
namespace Api\Controller;
use Think\Controller\HproseController;
use AUApi\Controller\KdnoConfig\Kdno6;
class HkEmsController extends HproseController{    
    protected $crossDomain =    true;
    protected $P3P         =    true;
    protected $get         =    true;
    protected $debug       =    true;

    // 发送报告 列表页面
    public function _index($map){
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
        $re_time = date('Y-m-d H:i:s',$re_time);
        
    	$where = array();
		$where['noid'] = array('eq',$id);
		$all = M('TranList')->field('auto_Indent1,auto_Indent2,STNO,weight')->where($where)->select();	//总数
		
        if(count($all) == 0){
            return array('Status'=>'false', 'ErrorMessage'=>'该批次号暂无数据录入，禁止操作');
        }
		// 组装ShipmentNumbers属性($list)
		$list = array();
		foreach($all as $key=>$item){
			$list[$key]['BagNo']       = '1';
			$list[$key]['ReferenceId'] = $item['auto_Indent2']."_".$item['auto_Indent1'];
			$list[$key]['TrackingNo']  = $item['STNO'];
			$list[$key]['Weight']      = $item['weight'];
		}

        $EMS = new Kdno6();

    	$res = $EMS->reportOrder($list, $number, $re_time, $country);

    	$arr = json_decode($res,true);

    	// 预报订单 成功，则更新标识码
    	if($arr['Status'] == 'true'){
    		M('TransitNo')->where(array('id'=>$id))->setField('send_report','1');//1 表示已经成功执行

            $data = array();
            $data['WayBillNumber'] = $number;
            $data['ETA']           = $re_time;
            $data['CountryCode']   = $country;
            $data['no']            = $no;
            $data['TranKd']        = $trankd;
            M('EmsReport')->add($data);//由于mk_ems_report用于保存记录，所以插入数据的时候无需检验是否存在
    	}

    	return $arr;
		
    }

    // 报关状态
    public function _count($where,$p,$ePage){
        $list = M('Trainer')->where($where)->order('CreateTime desc')->page($p.','.$ePage)->select();
        $count = M('Trainer')->where($where)->count();
        return array('count'=>$count, 'list'=>$list);
    }

    /**
     * 查看
     * @return [type] [description]
     */
    public function info($map){
        $info = M('Trainer')->where($map)->find();

        $msg = M('TrainerLogs')->where(array('LogisticsNo'=>$info['LogisticsNo']))->order('id desc')->select();
        return array($info,$msg);
    }
}