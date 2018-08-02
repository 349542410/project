<?php
/**
 * 服务器端
 */
	require_once('../../hprose_php5/HproseHttpServer.php');

	function info($sign,$gcustomer,$param,$param_arr,$company){
		$noechoyn = true;
		include('order.php');
		return (isset($bstr)?$bstr:'');
	}

	$server = new HproseHttpServer();
	$server->setErrorTypes(E_ALL);
	$server->setDebugEnabled();
	$server->addFunction('info');
	$server->start();
?>