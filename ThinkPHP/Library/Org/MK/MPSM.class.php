<?php
/*
create by Man 20180223
1.通过腾讯云发送短信
*/
namespace Org\MK;
class MPSM {
	//默认配置
	protected $config = array(
		'sign'        	=> '美快国际物流',// 签名
		'nationcode'  	=> '86',            // 接收的手机号国家号
		'tpl_id'	 	=> 89678,		  //短信内容模板
		'appid'			=> '1400022526',
		'appkey'		=> '2b7eafcb033d10f83434848e20ea489b',
		//'url'			=> 'https://yun.tim.qq.com/v5/tlssmssvr/sendmultisms2?',		
		'url'			=> 'https://yun.tim.qq.com/v5/tlssmssvr/sendisms?',	
		'ext'			=> 'mkil',	
	);
	protected $HTTP 	= null;
	public function __construct($config = array()){
		/* 获取配置 */
		$this->config   =   array_merge($this->config, $config);
		$this->HTTP = new \Org\MK\HTTP();
	}	
	public function send($data)
	{
		$time 	= time();
		$random = rand(100000,500000);
		$tel 	= '+'.$this->config['nationcode'].$data['no'];

		$sig 	= hash('sha256','appkey='.$this->config['appkey'].'&random='.$random.'&time='.$time.'&tel='.$tel,false);
		//180326增加C配置发送模板 Man
		$tplid 	= C('SPMSTPLID');
		if(!$tplid || $tplid=='' || $tplid==0){
			$tplid 	= $this->config['tpl_id'];
		}
		$sdata  = array(
			"tel"		=> $tel,
    		"ext"		=> $this->config['ext'],
    		"extend"	=> "",
    		"params"	=> $data['data'],
    		"sig"		=> $sig,
    		"sign"		=> $this->config['sign'],
        	"time"		=> $time,
    		"tpl_id"	=> $tplid,
    	);
    	$Url 		= $this->config['url'].'sdkappid='.$this->config['appid'].'&random='.$random;
    	
    	return $this->HTTP->posts($Url,json_encode($sdata),30,0);
	}

}