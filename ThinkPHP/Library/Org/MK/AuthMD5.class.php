<?php
namespace Org\MK;
class AuthMD5 {
	/*
		暂时使用appID:65412888   Key:c260d0ed79c711e4b8ae382c4a62e14e
		"MKIL".Key.精确至秒的时间(20141010131010,与JSON中STM相同，转换为+0时间).KD(与JSON中KD相同).appID
		将以上字段生成MD5格式 保存到JSON.CMD5中
	*/
    protected $config = array(
        'appID'           	=> '', //由它处传来
        'Key'         		=> '',
		'PREFIX'			=> 'MKIL',
		'datetime'			=>  '0',  // 0表示读取当前时间，不采用0
		'KD'				=> 'toMM',
		'CID'				=> 0,
    );

    public function __construct($config = array()){
        /* 获取配置 */
        if($config["CID"]>0 && ($config["appID"]=='' || $config["Key"]=='')){
        	//读取传来公司appID,appKey
        	$sys        = new \Org\MK\SYS;
        	$apps       = $sys->getappidkey($config["CID"]); 
        	//print_r($apps);
        	if(is_array($apps)){
        		$config["appID"]	= $apps["appID"];
        		$config["Key"]		= $apps["appKey"];
        	}
        }

        $this->config   =   array_merge($this->config, $config);
        
	}

	public function create(){  //生成MD5
		$stm 		= $this->gettime();
		$this->config['datetime']=$stm;
		$md5sstr	= $this->build();
		return array("STM"=>$stm,"CMD5"=>$md5sstr);
	}

	public function compare($var){ 
		if($this->config['datetime']=='0') return 3;
		//判断时间是否相差在3分钟内
		if(!$this->comparetime($this->config['datetime'],$this->gettime())) return 4;
		$mdb	= $this->build(); //echo '-'.$mdb.'-';
		return ($var==$mdb);
	}

	protected function gettime(){
		$tzone = date_default_timezone_get();
		date_default_timezone_set("UTC");
		$stm= date('YmdHis',time());
		date_default_timezone_set($tzone);
		return $stm;
	}

	protected function comparetime($t1,$t2){
		return abs($t1-$t2)<300; //因为直接数据相减变成了十进，也可以改为60-100为一分钟，300为三分钟,
	}

	protected function build(){ //0为生成MD5 ,1为根据数值生成MD5
		//"MKIL".Key.精确至秒的时间(20141010131010,与JSON中STM相同，转换为+0时间).KD(与JSON中KD相同).appID

		//print_r($this->config);

		$stm 	= $this->config['datetime'];
		$ss 	= $this->config['PREFIX'].
				  $this->config['Key'].
				  $stm.
				  $this->config['KD'].
				  $this->config['appID'];
		//echo "-M:".$ss."-M";
		return  MD5($ss);
	}

}
