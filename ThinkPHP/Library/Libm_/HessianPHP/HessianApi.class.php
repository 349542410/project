<?php
/**
 * 2017-07-13 jie
 * 类名: HessianApi
 * 用途：调用Hessian 发送身份证图片给顺丰
 */
namespace Libm\HessianPHP;
class HessianApi
{
	public $url;
	public $data;
	public $validateStr;
	public $customerCode;

	public function post(){
		require_once ("HessianClient.php");

		$http = new \HessianClient($this->url);

		$result = $http->uploadIdentity($this->data, $this->validateStr, $this->customerCode);

		return $result;
	}
}