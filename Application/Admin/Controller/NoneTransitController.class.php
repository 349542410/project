<?php
/**
 * 美快优选3(没有批次号的订单数据)
 * 功能包括： 列出没有批次号的各个线路的订单总数，还有对应的各自的导出方法
 * 导出的时候也是根据BC、CC或非BC非CC来进行资料导出
 * 
 */
namespace Admin\Controller;
use Think\Controller;
class NoneTransitController extends AdminbaseController{

    function _initialize() {
        parent::_initialize();
        $Nclient = new \HproseHttpClient(C('RAPIURL').'/AdminNoneTransit');     //读取、查询操作
        $this->Nclient = $Nclient;        //全局变量

    }

    /**
     * [index 获取没有批次号的各个线路的订单总数]
     * @param  [type] $data [包含：TranKd 线路id合集]
     * @return [type]       [description]
     */
	public function index($data){

		// 如果线路id合集不是数组形式，则需要转成数组
		if(!is_array($data['TranKd'])){
			$data['TranKd'] = explode(',',$data['TranKd']);
		}

		$map['t.TranKd'] = array('in',$data['TranKd']);
		$map['t.noid'] = array('eq',0);
		$list = $this->Nclient->_index($map);

/*		$ids = $data['TranKd'];  //数组

		$arr = array();
		foreach($ids as $it){
			$i = 1;
			foreach($list as $v){
				if($v['TranKd'] == $it){
					$arr[$it]['num'] = $i++;
					if(!isset($arr[$it]['name'])) $arr[$it]['name'] = $v['name'];
					if(!isset($arr[$it]['tcid'])) $arr[$it]['tcid'] = $v['TranKd'];
				}
			}
		}*/
		return $list;
	}

    /**
     * 清关 --- 资料导出(导出成功后直接提成用户下载)
     * 导出文件，是用于手动形式报关，处理结果跟“发送”形式的报关一致
     * @return [type] [description]
     */
    public function toExport(){
    	$tcid= I('tid');
		$Nclient   = $this->Nclient;
		$res  = $Nclient->_export_file($tcid);

		$getlist   = $res['list'];
		$title     = $res['title'];

		$nos = array();//STNO数组集

		// 设置为文本无科学计数
		foreach($getlist as $key=>$item){

			$nos[$key] = $item['f19']; //STNO数组集
			// unset($getlist[$key]['f20']); //STNO数组集 获取得到STNO之后，清除此项

	        /* 去除详细地址中的省市区和空格 */
	        // $getlist[$key]['f6'] = str_replace($getlist[$key]['f10'],'',$getlist[$key]['f6']);
	        // $getlist[$key]['f6'] = str_replace($getlist[$key]['f9'],'',$getlist[$key]['f6']);
	        // $getlist[$key]['f6'] = str_replace($getlist[$key]['f8'],'',$getlist[$key]['f6']);
	        // $getlist[$key]['f6'] = trim(str_replace(' ','',$getlist[$key]['f6']));

			//20180502注释
			// foreach($item as $k=>$it){
			// 	$getlist[$key][$k] = "\t".$it;	// 符合规则的，则在字段前面添加"\t"
			// }
		}

		$nos = array_filter($nos);//移除数组中的空值，并返回结果为数组
		$nos = array_unique($nos);//移除数组中的重复的值，并返回结果为数组
		
		$client = new \HproseHttpClient(C('RAPIURL').'/MKBc3Ptwo');     //读取、查询操作
		$client -> setTimeout(1200000);//设置 HproseHttpClient 超时时间
		$client->saveState($nos,$tcid,session('admin.adtname'));

		$fpd = 'MkBc3'; // 20160912 jie 文件名前缀
		
		$filename = $fpd."-".date('YmdHis');				//导出的文件名
		// $fileurl  = K(C('CSVURL').'/'.$fpd.'/'.$filename);	//20170220 jie

		$exportexcel  = new \Libm\MKILExcel\ExcelOperation;//上线  使用的时候使用此加载

		$headArr = explode(',',$title);
		$exportexcel ->push($filename,$headArr,$getlist);
		
    }
}