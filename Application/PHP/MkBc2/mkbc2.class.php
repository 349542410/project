<?php
/**
 * 美快BC优选2   查询物流信息/查最新物流状态
 * 创建时间：2017-04-17
 * 修改时间：2017-04-19
 * created by Jie
 * 指导文档：指调给据邮件跟踪查询系统接口规范（电商）-国内给据(1).docx
 * 编写方案：参考顺丰物流
 */
class bc2{

	protected $config = array(
		'state_url' => 'http://211.156.198.97/zdxtJkServer/zhddws/MailCsService_Gn?wsdl', 	//查最新的物流状态(即最新的一条物流信息)
		'all_url'   => 'http://211.156.198.97/zdxtJkServer/zhddws/MailTtService_Gn?wsdl',	//查全部物流信息
		'serKind'   => '6',	//服务点进入方式
		'serSign'   => 'cf3fd5ccc42d4303',	//查询方标识
	);

	/**
	 * [transit 查询物流信息/查最新物流状态]
	 * @param  [type]  $no   [运单号]
	 * @param  boolean $part [区分：false=查询全部物流信息(默认)，true=查最新物流状态]
	 * @return [type]        [description]
	 */
	public function transit($no, $part=false){

		$url = ($part == true) ? $this->config['state_url'] : $this->config['all_url'];
		$soap = new \SoapClient($url);//网络服务请求地址

		$serKind = $this->config['serKind'];	//服务点进入方式
		$serSign = $this->config['serSign'];  	//查询方标识
		$mailId  = $no;  						//邮件号码

/*		echo ("SOAP服务器提供的开放函数:");
		echo ('<pre>');
		var_dump ( $soap->__getFunctions () );//获取服务器上提供的方法
		echo ('</pre>');
		echo ("SOAP服务器提供的Type:");
		echo ('<pre>');
		var_dump ( $soap->__getTypes () );//获取服务器上数据类型
		echo ('</pre>');
		echo ("执行的结果:<br/>");
		$result = $soap->getMails(array('in0'=>$serKind,'in1'=>$serSign,'in2'=>$mailId));//查询，返回的是一个结构体
		//显示结果
		var_dump($result);*/

		$result = $soap->getMails(array('in0'=>$serKind,'in1'=>$serSign,'in2'=>$mailId));//查询，返回的是一个结构体
		return $result; //以对象的方式返回
	}



}