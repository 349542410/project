<?php
/*
	将邮政发来的物流信息
*/
namespace Api\Controller;
use Think\Controller\RestController;
class EmscqCallbackController extends RestController{
	function _initialize(){

	}

	public function index()
	{
		
	}

	public function savecontrail(){

		//接收到JSON后，直接将JSON保存到数据表中
		$data = $_POST['data']; //已base64加密
		//保存到mk_emscq_contrail中 id,content(text=$data),时间,status(int1 def0),errstr
		//保存成功返回{'code':1}，否则返回false{'code':0}
		//暂不这样处理 Man
	}

	public function anacontrail(){
/*		// 从上面保存的数据表mk_emscq_contrail中读取status=0 limit 20 的json进行分析，保存到 il_logs中
		// 保存后，如果成功直接删除数据库的该条记录，不成功，请status=2,errstr保存不成功的说明
		{"listexpressmail": [
			  {
				"serialnumber": "0000000000000000001",	//顺序号，无实际意义
				"mailnum": "LK434266003CN",	//邮件号
				"procdate": "20130702", //日期
				"proctime": "000100",  //时间
				"orgfullname": "所在地名称",
				"action": "00",		//action :=== 00：收寄、10：妥投、20: 未妥投、
									30：经转过程中、40：离开处理中心、41：到达处理中心、
									50安排投递、51：正在投递、60：揽收
									//按申通状态的处理办法进行转换
				"description": "物流描述信息",	//长度不能超过512字节
				"effect": "",	//1有效/0无效，判断依据邮件号、日期、时间、动作
				"properdelivery": "",	//只有当action=10的时候才有文字说明
				"notproperdelivery": "",	//只有当action=20的时候才有文字说明
			  }
			]}
		//保存成功后，直接删除数据表记录即可*/
		$js = '{"listexpressmail": [{
				"serialnumber": "0000000000000000001",
				"mailnum": "LK434266003CN",
				"procdate": "20130702",
				"proctime": "000100",
				"orgfullname": "广州",
				"action": "60",
				"description": "物流描述信息",
				"effect": "1",
				"properdelivery": "",
				"notproperdelivery": ""}]
			}';
		$arr = json_decode($js,true);
		// dump($arr['listexpressmail']);

		$res = array();
		$failmailnums = '';
		foreach($arr['listexpressmail'] as $key=>$item){

			// 先检查此条物流信息是否已经存在(根据MKNO，)	20160616 Jie 待处理Man 
			//Man160622 改为 根据 物流信息表 STNO=mailnum来获得MKNO
			//获取后将 MKNO 保存到 session('MKNO'.$STNO,$MKNO),
			//取MKNO前先读session没有的 再查询数据库

			// $check_first = M()->where()->select();

			//接收到的物流信息时间
			$protime = $this->change_form($item['procdate'],$item['proctime']);

			$data = array();
			$data['MKNO']        = $item['mailnum'];	//此单号应该改为MKNO   20160616 Jie 待处理
			$data['content']     = '【'.$item['orgfullname'].'】 '.$item['description'];
			$data['create_time'] = $protime;
			$data['status']      = $this->change_status($item['action']);//$item['action'];

			$res[$key] = M('IlLogs')->add($data);	//保存物流信息
			
			if(!$res[$key]){
				$failmailnums .= $item['mailnum'].',';
			}
		}
		// dump($res);

		$failmailnums = rtrim($failmailnums, ",");	//去除右边的逗号
		
		// 回执，以json形式返回给邮政
		if(trim($failmailnums) != ''){
			$this->Tjson(0,$remark,$failmailnums);
		}else{
			$this->Tjson(1,$remark,$failmailnums);
		}
		
	}

	//时间格式处理
	protected function change_form($date,$time){

		$date = substr_replace($date,'-',6,0);
		$date = substr_replace($date,'-',4,0);
		// echo $date;
		$time = substr_replace($time,':',4,0);
		$time = substr_replace($time,':',2,0);

		$res = $date.' '.$time;
		
		return $res;

	}


/*			//邮政状态编码
			00：收寄、10：妥投、20: 未妥投、
			30：经转过程中、40：离开处理中心、41：到达处理中心、
			50: 安排投递、51：正在投递、60：揽收
			//按申通状态的处理办法进行转换

			//以下为申通状态编码
			'1000' =>'在途',		30,40,41
			'1001' =>'揽件',		60,00
			'1002' =>'疑难',		20
			'1003' =>'签收',		10
			'1005' =>'派件中',		50,51
			'1004' =>'退回',		20
			'1006' =>'拒收',		20*/
	/**
	 * 转换成申通状态
	 * @param  [type] $code [接收的邮政状态编码]
	 * @return [type]       [description]
	 */
	protected function change_status($code){
		$sta_list = array(
			'00' => '1001',
			'10' => '1003',
			'20' => '1002',
			'30' => '1000',
			'40' => '1000',
			'41' => '1000',
			'50' => '1005',
			'51' => '1005',
			'60' => '1001',
		);

		// $res = $sta_list[$code];
		return $sta_list[$code];
	}

	function Tjson($code,$remark,$data='',$noechoyn=false){
		$backStr = array(
			'success' => $msg,
			'failmailnums'  => $data,
			'remark'   => $remark,
		);
		if($noechoyn == true){
			return json_encode(array('response'=>$backStr));
		}else{
			echo json_encode(array('response'=>$backStr));
		}
		
	}


}