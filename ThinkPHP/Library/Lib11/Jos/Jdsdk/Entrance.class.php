<?php

	namespace Lib11\Jos\Jdsdk;

	class Entrance{

		private $tf;

		private $appKey;
		private $appSecret;
		private $accessToken;

		private $method;		//接口名
		private $url = 'https://api.jd.com/routerjson';
		private $data;

		public function __construct(){

			$this->tf = new ToolsFunction();

		}

		//初始化
		public function init($authentication,$method,$data=array()){

			$this->appKey = ( $authentication['app_key'] ? $authentication['app_key'] : '82208D4438AB141463D37882EC42DF11');
			$this->appSecret =  ( $authentication['app_secret'] ? $authentication['app_secret'] : 'b018fb1163144cadb47b33d236c359eb');
			$this->accessToken = $authentication['access_token'];

			$this->method = $method;
			$this->data = $data;
			
		}

		//执行
		public function execute(){

			// $map = array(
			// 	'jingdong.sku.read.searchSkuList' => 'SkuReadSearchSkuListRequest',
			// 	'jingdong.sku.read.findSkuById' => 'SkuReadFindSkuByIdRequest',
			// 	'jingdong.ware.read.findWareById' => 'WareReadFindWareByIdRequest',
			// );
			$map = explode('.',$this->method);
			unset($map[0]);
			$objstr = '';
			foreach($map as $k=>$v){
				$objstr .= ucfirst($v);
			}
			$objstr .= 'Request';

			$info = array(
				'appKey' => $this->appKey,
				'appSecret' => $this->appSecret,
				'accessToken' => $this->accessToken,
				'serverUrl' => $this->url,
				'method' => $objstr,
				'data' => $this->data,
			);

			$url =  dirname(__FILE__) . "\JdSdk.php";
			$result = include_once $url;
			return $result;

		}


	}

