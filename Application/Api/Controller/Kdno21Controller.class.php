<?php
/**
 * 
 */
namespace Api\Controller;
use Think\Controller;
class Kdno21Controller extends Controller{
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
		$no = I('no');
		G('begin'); // 记录开始标记位
		// if($_SERVER['HTTP_HOST'] == 'mkapi.app.megao.hk:83'){
			$js = '{"KD":"toMKIL","CID":"2","SID":"20","CMD5":"a323dd502ba895a04e5a50f3b51fefa6","STM":"20180512062503","LAN":"zh-cn","toMKIL":[{"weight":"0.3","sender":"Ying CPFS","sendAddr":"40559 Encyclopedia Cir, Fremont, CA 94538","sendTel":"510-676-3267","sendcode":"094538","sfid":"132202198010150418","shopType":"Shop","shopName":"CPFS","receiver":"魏婉榕","province":"甘肃省","city":"兰州市","town":"城关区","reAddr":"甘肃省 兰州市 城关区 靖远路街道 九州海亮和园三号楼一单元1903","postcode":"730030","reTel":"18693066265","notes":"","premium":0,"MKNO":"'.$no.'","email":"89088668@qq.com","category1st":"配件","category2nd":"男装钱包","category3rd":"","CID":"1","coin":"CNY","paykind":"支付宝","payno":"2342342342","paytime":"2018-05-12 00:00:00","idkind":"ID","idno":"132202198010150418","buyers_nickname":"252","TranKd":"21","auto_Indent1":360166,"auto_Indent2":"12312312312","number":1.0,"price":"102.00","discount":"0.00","Order":[{"detail":"NAUTICA 31NU22X024 200 BROWN","hgid":"04010300","unit":"只","specifications":"品名:31NU22X024 200 BROWN;类别:男装钱包;品牌:NAUTICA;款号:0091-6291/02 BROWN","source_area":"越南","barcode":"","number":"1","catname":"男装钱包","price":"102.00","weight":"0.3","coin":"CNY","brand":"NAUTICA","hs_code":"4202310090","CategoryId":"173","att1":"31NU22X024 200 BROWN","att2":"0091-6291/02 BROWN","att3":"褐色","att4":"通用"}]}]}';

			$arr = json_decode($js,true);

			$list = $arr['toMKIL'][0];
			// echo '<pre>';
			// print_r($list);die;
			require('Kdno21.class.php');

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
		// }
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
}