<?php
/**
 * 
 */
namespace Api\Controller;
use Think\Controller;
class Kdno22Controller extends Controller{
	public function read()
	{
		$jn 	= new \Org\MK\JSON;
		$data 	= $jn->get();
		if(!is_array($data)){
			echo $jn->respons("","",null,0,L("SYSERROR0"));
			exit;
		}
		//将物流号设置到$data2中
		$data2 		= $data['toMKIL'];
		require_once(dirname(__FILE__).'/Kdno21.class.php');
		$Kdno 		= new \Kdno();
		//直接读取$data['ToMK']拆成单个数据
		$i = 0;
		foreach ($data2 as $key => $value) {
			$value['MKNO']	 	= 'MK' . time();
			$Kdno->data($value);
			$STEXT  = $Kdno->get(); 	// 返回快递其它内容
			$STNO 	= $Kdno->no();  	// 返回快递号码	
			$back[$i]=Array(
				"auto_Indent1" => $value['auto_Indent1'],
				"auto_Indent2" => $value['auto_Indent2'],
				"MKNO"         => $value['MKNO'],
				"STNO"         => $STNO,
				'LineName'     => 'lname',
				"STEXT"        => isset($STEXT)?$STEXT:'',
				'sfinfo'       => isset($sfinfo)?$sfinfo:'',
				'jdate'        => date('Y-m-d'),
				"Success"      => true,
				"LOGSTR"       => '',
				'CID'          => $value['CID'], 
			);
			$i++;
		}
		echo $jn->respons( $data['KD'], $data['CID'],$back);
	}

	// 测试用
	public function test(){
		G('begin'); // 记录开始标记位
			$js = '{"KD":"toMKIL","CID":"2","SID":"20","CMD5":"b409c0ad00441679049dc32103dcc7ea","STM":"20180608015848","LAN":"zh-cn","toMKIL":[{"weight":"1.9","sender":"Ying ALD","sendAddr":"40559 Encyclopedia Cir, Fremont, CA 94538","sendTel":"510-676-3267","sendcode":"094538","sfid":"110105198209297722","shopType":"Shop","shopName":"ALD","receiver":"刘峥","province":"北京市","city":"北京市","town":"朝阳区","reAddr":"北京市 北京市 朝阳区 慧忠里320号","postcode":"100000","reTel":"13661247113","notes":"","premium":0,"MKNO":"MK81000068US","email":"89088668@qq.com","category1st":"配件","category2nd":"男装钱包","category3rd":"","CID":"1","coin":"CNY","paykind":"支付宝","payno":"2018053115351021900107814","paytime":"2018-05-31 15:36:23","idkind":"ID","idno":"110105198209297722","buyers_nickname":"13693512857","TranKd":"22","auto_Indent1":360183,"auto_Indent2":"1806080926740","number":1.0,"price":"480.00","discount":"0.00","Order":[{"detail":"GUESS男装钱包 皮质0091-0849/08 CHARCOAL","hgid":"MK3028","unit":"个","specifications":"1个","source_area":"美国","barcode":null,"number":"1","catname":"男装钱包","price":"480.00","weight":"0.32","coin":"CNY","brand":"GUESS","hs_code":"4202310090","CategoryId":null,"att1":"钱包","att2":"0091-0849/08 CHARCOAL","att3":"灰","att4":"通用","att5":"0.3"}]}]}';

			$arr = json_decode($js,true);

			$list = $arr['toMKIL'][0];
			// echo '<pre>';
			// print_r($list);die;
			require('Kdno22.class.php');

			// $mothod = '\AUApi\Controller\KdnoConfig\Kdno22';
			// $EMS = new $mothod();
			$EMS = new \Kdno();

			// $res = $EMS->eExpress_Login();
			// dump($res);die;
			$res = $EMS->data($list);
			G('end'); // 记录结束标签位
			// dump($res);die;
			$STEXT  = $EMS->get(); 	// 返回快递其它内容
			$STNO 	= $EMS->no();  	// 返回快递号码
			dump($res);
			dump($STNO);
			dump(json_decode(base64_decode($STEXT)));

			echo '<hr>';
			echo G('begin','end',6); // 统计区间运行时间 精确到小数后6位
			echo G('begin','end','m'); // 统计区间内存使用情况
		}

	public function test2(){
		require('Kdno21.class.php');
		$EMS = new \Kdno();
		$res = $EMS->catalogue();
		dump($res);
	}

	public function get_labels(){
		require('Kdno21.class.php');
		$EMS = new \Kdno();
		$res = $EMS->get_labels(I('no'));
		echo $res;
		// return $res;
		// $arr = json_decode($res, true);
		// dump($arr);
	}

	public function getExcel(){
		require('Kdno21.class.php');
		$EMS = new \Kdno();
		$res = $EMS->catalogue();
		$arr = json_decode($res, true);

		$data = $arr['Result']['Categorys'];
		
		$getlist = array();
		foreach($data as $key=>$v){
			$getlist[$key]['f1'] = $v['CategoryID'];
			$getlist[$key]['f2'] = $v['CategoryCnName'];
			$getlist[$key]['f3'] = $v['CategoryEnName'];
			$getlist[$key]['f4'] = $v['CategoryLevel'];
			$getlist[$key]['f5'] = $v['CategoryParentId'];
			$getlist[$key]['f6'] = '';//$v['fMinWeight'];
			$getlist[$key]['f7'] = '';//$v['fMaxWeight'];
			$getlist[$key]['f8'] = '';//$v['HsCode'];
		}
		// dump($getlist);die;

		if(count($data) > 0){

			$title = 'id,中文品名,英文品名,类别级别,父级类别id,fMinWeight,fMaxWeight,HsCode';

	        $exportexcel  = new \Libm\MKILExcel\MkilExportMarket;
			$filename = "Categorys-".date('YmdHis');//'Categorys-'.date('YmdHis')."(".count($data).")";				//导出的文件名，无需后缀
			$exportexcel->SaveName   = $filename;	//包含路径+文件名;
			$exportexcel->Title      = $title;		//单元格表头
			$exportexcel->Data       = $getlist;		//导出数据数组
			$exportexcel->Format     = '2003';   	// 导出类型：csv, 2003表示excel2003, 2007表示excel2007
			$exportexcel->Model_Type = '1';   	// 是否进行省略操作
			$exportexcel->Sort       = false;   	// 是否带序号
			$exportexcel->OutPut     = true;   	// 是否直接输出文件
			$exportexcel->Title_Style   = true;   	// 单元格表头是否需要样式设计
			$exportexcel->export();  				// 返回true,false

		}else{
			return array('state'=>'no', 'msg'=>'no data');
		}
		// dump($data);
	}

	public function demo(){
		$str = 'eyJkZXN0Y29kZSI6IiIsIm1haWxubyI6IjEwMDAwMDA0MDcxNiIsIm9yaWdpbmNvZGUiOiIiLCJvcmRlcmlkIjoiTUs4ODMwMDg3NzhVUyIsImN1c3RpZCI6IiIsInRyYWNlQ29kZSI6Ijk4MTcwMDAwMTg4MSIsInRpdGxlIjoiODAwLSAzNy0wNSAyMCIsInBhY2thZ2UiOiJcdTUzMTdcdTRlYWMifQ==';
		dump(base64_decode($str));
	}
}