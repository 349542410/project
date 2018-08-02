<?php
/**
 * 2016-11-30 jie
 * 发送到ERP的类
 */
class SendERP{

	//默认导入文件信息为空白数组
	private $config = array(
		'MKNO'    => '',
		'state'   => '',
		'ftime'   => '',
		'pno'     => '',
		'context' => '',
		'url'     => '',
	);

  /**
   * 使用 $this->name 获取配置
   * @access public     
   * @param  string $name 配置名称
   * @return multitype    配置值
   */
  public function __get($name) {
    return $this->config[$name];
  }

  /**
   * 设置配置
   * @access public     
   * @param  string $name 配置名称
   * @param  string $value 配置值     
   * @return void
   */
  public function __set($name,$value){
    if(isset($this->config[$name])) {
      $this->config[$name] = $value;
    }
  }

  /**
   * 检查配置
   * @access public     
   * @param  string $name 配置名称
   * @return bool
   */
  public function __isset($name){
    return isset($this->config[$name]);
  }

	public function send(){
		// echo '<pre>';
		// MKNO必需
		if($this->config['MKNO'] == ''){
			return false;
		}

		$larr = $this->config;
		unset($larr['url']);

		$arr = array();
		array_push($arr, $larr);//组成二维数组

		$schema = "json";
		$param 	= json_encode($arr);  

		$post_data = "schema=".$schema."&pf=mkil&param=".$param;	//组合

		$result = $this->posturl($this->url,$post_data);

		$res = trim(json_decode($result,true));		//返回的结果
		if($res == '200'){
			// echo '成功';
			return true;
		}else{
			// echo '失败';
			return false;
		}
	}

	/**
	 * curl函数发送数据到ERP
	 * @param  [type] $url       [description]
	 * @param  [type] $post_data [description]
	 * @return [type]            [description]
	 */
	public function posturl($url,$post_data){
		//通过curl函数发送
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_URL,$url);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);

		//当CURLOPT_RETURNTRANSFER设置为1时，如果成功只将结果返回，不自动输出返回的内容。如果失败返回FALSE；
		//若不使用这个选项：如果成功只返回TRUE，自动输出返回的内容。如果失败返回FALSE
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

		$result = curl_exec($ch);
		curl_close($ch);
		return $result;
	}
}