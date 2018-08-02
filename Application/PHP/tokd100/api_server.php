<?php
/**
 * 服务器端
 */
	require_once('../../hprose_php5/HproseHttpServer.php');

	function info($gid,$com,$nu,$show,$muti,$order){
		$noechoyn = true;
		include('./api.php');
		return (isset($bstr)?$bstr:'');
	}

	$server = new HproseHttpServer();
	$server->setErrorTypes(E_ALL);
	$server->setDebugEnabled();
	$server->addFunction('info');
	$server->start();
?>