<?php
/*
	将邮政发来的号码保存到我数据库中
	每次最多100个号码
*/
namespace Api\Controller;
use Think\Controller\RestController;
class EmscqController extends RestController{
	function _initialize(){

	}

	public function index()
	{
		
	}

	public function getems(){
		$jn = new \Org\MK\CQEMS;
		$js = $jn->get();
		// dump($js);
		if($js['code']==1){
			ini_set('max_execution_time', 0);
			ini_set('memory_limit','4088M');

			$res = array();
			// dump($js['ids']);

			foreach ($js['ids'] as $key=>$item) {

				// $billno = $item['billno'];
				// echo $billno.'<br/>';

				//按申通号码导入的方式写入mk_emscqnolist中
				$val['POSTNO']   = trim($item['billno']);
				$val['add_time'] = time();
				// $res[$key] = $val;
				$res[$key] = M('Emscqnolist')->add($val);
			}
			
			// $count = count($res);
			// dump($res);
		}
	}

}