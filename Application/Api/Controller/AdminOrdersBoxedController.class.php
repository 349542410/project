<?php
/**
 * 美快后台登录验证  服务器端
 */
namespace Api\Controller;
use Think\Controller\HproseController;
use AUApi\Controller\KdnoConfig\Kdno19;
class AdminOrdersBoxedController extends HproseController{

	public function _postNo($arr, $type){

		// 批次号
		if($type == 'PNO'){

            $no = $arr['0'];

			$noid = M('TransitNo')->where(array('no'=>$no))->getField('id');

			if(!$noid){
				return array('state'=>'no', 'msg'=>'该中转号不存在');
			}

			$list = M('TranList')->field('id,STNO')->where(array('noid'=>$noid))->select();

			if(count($list) == 0){
				return array('state'=>'no', 'msg'=>'该中转号没有对应的订单数据');
			}

			$arr = array_column($list, 'STNO');
			$ids = array_column($list, 'id');

			$NumberType = 2;//EMS 跟踪号
		}else{
			$map[$type] = array('in', $arr);
			$list = M('TranList')->field('id')->where($map)->select();

			if(count($list) == 0){
				return array('state'=>'no', 'msg'=>'您输入的单号，全部未能与数据库匹配');
			}

			$ids = array_column($list, 'id');

			$NumberType = ($type == 'MKNO') ? 1 : 2;//1-客户订单号,2-EMS 跟踪号
			$noid = '';
		}

		$Kdno = new Kdno19();
        $res = $Kdno->OrderesBoxed($arr, $NumberType);

        if(isset($res['state']) && $res['state'] == '0'){
			return $res;
		}

		if(isset($res['OrderesBoxedResult'])){
			$OrderesBoxedResult = $res['OrderesBoxedResult'];
		}

		if(isset($OrderesBoxedResult['ResponseResult'])){
			if($OrderesBoxedResult['ResponseResult'] == 'Failure'){
				return array('state'=>'no', 'msg'=>'返回错误：'.$OrderesBoxedResult['ResponseError']['LongMessage']);
				// return array('state'=>'no', 'msg'=>json_encode($res,JSON_UNESCAPED_UNICODE));
			}else{

				$this->save($ids, $OrderesBoxedResult['Data'], $type, $noid);
				return array('state'=>'yes', 'msg'=>'装板号：'.$OrderesBoxedResult['Data']);
				// return array('state'=>'yes', 'msg'=>json_encode($res,JSON_UNESCAPED_UNICODE));
			}
		}
	}

	public function save($ids, $xa_bnum, $type, $noid){
		if(!is_dir(UPFILEBASE . '/ordersboxed/')) mkdir(UPFILEBASE . '/ordersboxed/', 0777, true);//第三个参数设置true 设置递归模式自动创建必需的目录文件夹

        $file_name = 'save_'.date('Ymd').'.txt';	//文件名

		$content = "===================== ".date('Y-m-d H:i:s')." =====================\r\n\r\n-------- data --------\r\n\r\n".json_encode($ids)."\r\n\r\n-------- bnum --------\r\n\r\n".$xa_bnum."\r\n\r\n"."-------- type --------\r\n\r\n".$type."\r\n\r\n"."-------- noid --------\r\n\r\n".$noid."\r\n\r\n";

        file_put_contents(UPFILEBASE . '/ordersboxed/'.$file_name, $content, FILE_APPEND);

		if(count($ids) > 0){

			$time = date('Y-m-d H:i:s');

			// 批次号
			if($type == 'PNO'){
				//检查是否已有记录
				$check_no = M('transit_no_state')->where(array('noid'=>$noid))->find();
				if(!$check_no){
					$data_no = array();
					$data_no['noid']     = $noid;  //批次号id
					$data_no['xa_bnum']  = $xa_bnum;//西安订单装板号
					$data_no['xa_btime'] = $time;//西安订单装板推送时间
					M('transit_no_state')->add($data_no);
				}else{
					$data_no = array();
					$data_no['xa_bnum']  = $xa_bnum;//西安订单装板号
					$data_no['xa_btime'] = $time;//西安订单装板推送时间
					M('transit_no_state')->where(array('id'=>$check_no['id']))->save($data_no);
				}

				$transit_sql = M()->getLastSql().';'.PHP_EOL;

			}

			$tran_sql = array();
			foreach($ids as $key=>$v){

				//检查是否已有记录
				$check = M('tran_list_state')->where(array('lid'=>$v))->find();

				if(!$check){
					$data = array();
					$data['lid']      = $v;
					$data['xa_bnum']  = $xa_bnum;//西安订单装板号
					$data['xa_btime'] = $time;//西安订单装板推送时间
					$data['ems_state'] = 0;//
					$data['ems_return'] = '';//
					M('tran_list_state')->add($data);
				}else{
					$data = array();
					$data['xa_bnum']  = $xa_bnum;//西安订单装板号
					$data['xa_btime'] = $time;//西安订单装板推送时间
					M('tran_list_state')->where(array('id'=>$check['id']))->save($data);
				}

				$tran_sql[$key] = M()->getLastSql().';';

			}

			$content = "===================== ".date('Y-m-d H:i:s')." =====================\r\n\r\n-------- transit_no_sql --------\r\n\r\n".$transit_sql."\r\n\r\n-------- tran_list_sql --------\r\n\r\n".json_encode($tran_sql)."\r\n\r\n";

			file_put_contents(UPFILEBASE . '/ordersboxed/'.$file_name, $content, FILE_APPEND);
		}
	}

    //查询订单的装板信息
    public function tran_info($where,$p,$ePage){

        $j = 'LEFT JOIN mk_tran_list l ON s.lid = l.id';

        $list = M('TranListState s')
                ->field('s.xa_bnum,s.xa_btime,l.MKNO,l.STNO')
                ->join($j)
                ->where($where)
                ->order('s.xa_btime desc')
                ->page($p.','.$ePage)
                ->select();

        $count = M('TranListState s')->field('s.xa_bnum,s.xa_btime,l.MKNO,l.STNO')->join($j)->where($where)->count();

        return array('list'=>$list,'count'=>$count);
    }

    public function tran_info_pno($where,$p,$ePage){

        $j = 'LEFT JOIN mk_transit_no l ON s.noid = l.id';

        $list = M('TransitNoState s')
                ->field('s.xa_bnum,s.xa_btime,l.no')
                ->join($j)
                ->where($where)
                ->order('s.xa_btime desc')
                ->page($p.','.$ePage)
                ->select();

        $count = M('TransitNoState s')->field('s.xa_bnum,s.xa_btime,l.MKNO,l.STNO')->join($j)->where($where)->count();

        return array('list'=>$list,'count'=>$count);
    }
}
