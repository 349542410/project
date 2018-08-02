<?php
/*
	160617增加取emscqno()功能，即重庆邮政运单号功能
	//未测试
*/
namespace Org\MK;
use Think\Model;
class Tracking {
	//默认配置
	protected $config = array(
		'numlen'           	=> 6,                      // 顺序号长度
		'tbnm'         		=> 'mkno',                 // 数据库名称。
		'PREFIX1'		=> 'MK',
		'PREFIX2'		=> '88',
		'PREFIX3'		=> '3',
		'ENDFIX'		=> 'US',
	);
	protected $inMKNO = '';
	public function __construct($config = array()){
		/* 获取配置 */
		$this->config   =   array_merge($this->config, $config);
	}
/*	//生成快递物流单号
	public function run() {
		$Model 	= new Model();
		$sql 	= "SELECT UUID() as ID";
		$voList = $Model->query($sql);
		$id		= $voList[0]['ID'];
		if(strlen($id)<10){
			return 0;
		}
		$tm		= time();
		
		$mkno	= M($this->config['tbnm']);
		$data	= array('uid'=>$id,'cre_time'=>$tm);
		$mkno->data($data)->add();
		$rd		= $mkno->where("uid='$id' AND cre_time='$tm'")->getField('id');
		$str	= $this->config['PREFIX1'].
					$this->config['PREFIX2'].
					$this->config['PREFIX3'].
					str_pad($rd,$this->config['numlen'],'0',STR_PAD_LEFT).
					$this->config['ENDFIX'];
		$this->inMKNO = $str;
		return $str;
	}*/
	//生成快递物流单号，版本2,2016-10-18，Man
	public function run() {
		$rd 	= -9;
		$Model = new Model();
		$field 	= 'MKNO';
		$tbpre 	= (isset($this->config['PREFIX2'])?$this->config['PREFIX2']:'88').(isset($this->config['PREFIX3'])?$this->config['PREFIX3']:'1');
		$tb 	= 'mknolist_'.$tbpre;
		$sql 	= "SELECT UUID() as ID";
		$voList = $Model->query($sql);
		$id	= $voList[0]['ID'];
		$tm	= microtime();
		$utm    = time();
		$data	= array('uuid'=>$id,'uuidtime'=>$tm,'usetime'=>$utm,'status'=>10);
		$M 	= M($tb);
		$_n     	= $M->data($data)->where(array('status'=>0))->limit(1)->save();
		//echo $M->_sql();exit;
		if($_n>0){
			$D = $M->where($data)->Field('id,'.$field)->find();
			$rd = 0;
			if(is_array($D)){
				$rd 	= $D[$field];
				$sid 	= $D['id'];
				if($rd>0){
					$_n 		= $M->where("id=$sid")->data(array('status'=>20))->save();
					if($_n<1) $rd 	= -1;
				}else{
					$rd = -2;
				}
			}else{
				$rd = -5;
			}
		}else{
			$rd = -6;
		}
		if($rd>0){
			$rd	= $this->config['PREFIX1'].
				$this->config['PREFIX2'].
				$this->config['PREFIX3'].
				str_pad($rd,$this->config['numlen'],'0',STR_PAD_LEFT).
				$this->config['ENDFIX'];
		}
		return $rd;		
	}
	
	/**
	 * 使用 $this->name 获取配置
	 * @param  string $name 配置名称
	 * @return multitype    配置值
	 */
	public function __get($name) {
		return $this->config[$name];
	}

	public function __set($name,$value){
		if(isset($this->config[$name])) {
			$this->config[$name] = $value;
		}
	}

	public function __isset($name){
		return isset($this->config[$name]);
	}
	//获取申通电子单号20160617
	public function stno($mo='')
	{
		return $this->getexno($mo,'stnolist','STNO');
	}
	//获取重庆电子单号20160617
	public function emscqno($mo='')
	{
		return $this->getexno($mo,'emscqnolist','POSTNO');
	}

	//20160617由原获取申通单号改为统一,170413由 private 改为 public.
	public function getexno($mo,$tb,$field){
		$rd = -9;
		$mkno2 = trim($mo);
		if($mkno2=='')
			$mkno2 = $this->inMKNO;

		if(trim($mkno2)=='' || trim($tb)=='' || trim($field)=='' ) return $rd;
		$Model 	= new Model();
		$sql 	= "SELECT UUID() as ID";
		$voList = $Model->query($sql);
		$id		= $voList[0]['ID'];
		$tm		= microtime();
		$utm    = time();
		$data	= array('uuid'=>$id,'uuidtime'=>$tm,'usetime'=>$utm,'MKNO'=>$mkno2,'status'=>10);
		$M 		= M($tb);
		$_n     = $M->data($data)->where(array('status'=>0))->order('id')->limit(1)->save();
		//echo $M->_sql();exit;
		if($_n>0){
			$D = $M->where($data)->Field('id,'.$field)->find();
			$rd = '0';
			//dump($D);
			if(is_array($D)){
				$rd 	= $D[$field];
				$sid 	= $D['id'];
				if(strlen($rd)>10){
					$_n 		  = $M->where("id=$sid")->data(array('status'=>20))->save();
					if($_n<1) $rd = '-1';
				}else{
					$rd = '-2';
				}
			}else{
				$rd = -5;
			}
		}else{
			$rd = -6;
		}
		return $rd;
	}
	//发送资料给顺丰并获取顺丰单号 151212
	public function sfno($sfkd,$rs){
		$sf = new sfClass();
		//发送资料
		$req = $sf->query($sfkd,$rs);
		//如果返回重复则通过美快单号读取顺丰单号
		if($req==99){
			$res = Array(
				'OrderSearch'=>Array(
					'orderid'=>$rs['Order']['orderid'], //读取发来的orderid
				)
			);
			$req = $sf->query('OrderSearchService',$res);
		}
		return $req;
	}
	//获取中转单号批号
	public function Transfer($kd,$num,$pre){
		$tb 	= array('no'=>'NoTransitNo','no2'=>'NoTransitNo2');
		$ln 	= array('no'=>3,'no2'=>3);
		$np 	= array('no'=>'T','no2'=>'B');
		$Model 	= new Model();
		$num 	= (isset($num)?$num:1)*1;
		if($num<1) $num=1;
		$sql 	= "SELECT UUID() as ID";
		$voList = $Model->query($sql);
		$id		= $voList[0]['ID'];
		if(strlen($id)<10){
			return 0;
		}
		$tm		= time();
		
		$mkno	= M($tb[$kd]);
		$data	= array('uid'=>$id,'cre_time'=>$tm);
		for($i=0;$i<$num;$i++){
			$mkno->data($data)->add();
		}
		$rd		= $mkno->where("uid='$id' AND cre_time='$tm'")->getField('id',true);
		$rs 	= array();
		if($rd){
			foreach ($rd as $v) {
				$stmp = str_pad($v,$ln[$kd],'0',STR_PAD_LEFT);
				array_push($rs, $np[$kd].$pre.$stmp);
			}
		}
		return $rs;
	}
}
class sfClass{
	var $head 			= '';
	var $checkword 		= ''; 
	var $host 			= '';

	public function __construct(){
		require('sfconfig.php');
		/*
		$this->head 	= C('sf.head');
		$this->checkword= C('sf.checkword');
		$this->host 	= C('sf.host');
		*/
		$this->head 	= $sfconfig['head'];
		$this->checkword= $sfconfig['checkword'];
		$this->host 	= $sfconfig['host'];        
	}
	
	//CURL方式
	public function curl($xml){
		//$xml = mb_convert_encoding($xml, 'UTF-8', 'HTML-ENTITIES');//联调的时候打开这行(让中文可视化)，方便她们审核
		$param	= array('xml'=>$xml,'verifyCode'=>base64_encode(md5($xml.$this->checkword,true)));
		$ch 	= curl_init();
		if (stripos($this->host, "https://") !== FALSE){
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
		}
		curl_setopt($ch, CURLOPT_URL,$this->host);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($param));
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		$result 	= curl_exec($ch);
		$xml 		= simplexml_load_string($result);
		curl_close($ch);
		return $xml->Head=='OK'?$xml->Body:$xml->ERROR;
	}
	
	//数组方式
	public function query($service,$param){

		$xml = new \SimpleXMLElement('<Request lang="zh-CN" service="'.$service.'"><Head>'.$this->head .'</Head><Body/></Request>');
		foreach($param as $child=>$attributes){
			$child = $xml->Body->addChild($child);
			foreach($attributes as $attribute=>$value){
				$child->addAttribute($attribute,$value);
			}
		}
		$rs 		= $this->curl($xml->asXML());
		$rs 		= json_decode(json_encode($rs), true);
		//print_r($rs);
		switch ($service){
			case 'OrderService':		//下单
				/*
				Array
				(
					[OrderResponse] => Array
						(
							[@attributes] => Array
								(
									[filter_result] => 2
									[destcode] => 020
									[mailno] => 444501530323
									[origincode] => 020
									[orderid] => MK881000258US
								)
						)
				)*/
				//分析收到的是否附合要求
				if(isset($rs['OrderResponse']['@attributes'])){
					return $this->backarray($rs['OrderResponse']['@attributes']);
				}

				//如果上述不附合，则分析是否是重复下单
				if(isset($rs['@attributes']['code'])){
					if($rs['@attributes']['code']==8016){
						//已下单，改为读取
						return 99;
						/*改在 sfno中，方便 获取 orderid
						$res = [
							'OrderSearch'=>[
								'orderid'=>'', //读取发来的orderid
							]
						];
						return $this->query('OrderSearchService',$res);
						*/
					}
				}
				//dump($rs); //如果有错误就会在这里显示
				//如果不附合又不是重复复下单，则返回空，让操作员重新操作
				return null;
				break;
			case 'OrderSearchService': //查询顺丰单号与地区号
				/*(
					[OrderResponse] => Array
						(
							[@attributes] => Array
								(
									[filter_result] => 2
									[destcode] => 020
									[mailno] => 444501480112
									[origincode] => 020
									[orderid] => MK881000241US
								)

						)

				)
				//===============
				(
					[@attributes] => Array
						(
							[code] => 6150
						)

					[0] => 找不到该订单
				)
				*/
				//分析是否找到该单
				if(isset($rs['@attributes']['code'])){
					if($rs['@attributes']['code']==6150){
						return null;
					}
				}
				//分析收到的是否附合要求
				if(isset($rs['OrderResponse']['@attributes'])){
					return $this->backarray($rs['OrderResponse']['@attributes']);
				}
				//如果不附合又不是重复复下单，则返回空，让操作员重新操作
				return null;
				break;
			default:
				//
				break;
		}
		return $rs;
	}
	function backarray($rs){
		$mailno = isset($rs['mailno'])?$rs['mailno']:'';
		//$rs['man'] = '188';
		if(strlen($mailno)>6){
			return $rs;
		}
		return null;
	}
}