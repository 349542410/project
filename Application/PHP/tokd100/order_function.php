<?php
	/**
	 * [Tjson description]
	 * @param [type] $result     [description]
	 * @param [type] $returnCode [description]
	 * @param [type] $message    [description]
	 */
	function Tjson($result,$returnCode,$message,$noechoyn=false){

		$backStr = array(
			'result'     => $result,
			'returnCode' => $returnCode,
			'message'    => $message,
		);
		if($noechoyn == true){
			return json_encode($backStr);
		}else{
			echo json_encode($backStr);
		}
	}