<?php

	require_once('./../../hprose_php5/HproseHttpServer.php');

	/**
	 * [save description]
	 * @param  [type]  $arr     [物流信息数组]
	 * @param  string  $sno     [查询单号，默认MKNO]
	 * @return [type]           [description]
	 */
	function save($arr, $sno='MKNO',$toor=''){// 20160818 Jie

		

		return 'hello';

	}

	$server = new HproseHttpServer();
	$server->setErrorTypes(E_ALL);
	$server->setDebugEnabled();
	$server->addFunction('save');
	$server->start();