<?php
/**
 * 自助、揽收系统用
 */
namespace MkAuto\Controller;
use Think\Controller;
class LanguageController extends Controller{

	/**
	 * [get_lang 自助/揽收系统生成的数组，根据多语言选择对应的语言提示]
	 * @param  [type] $arr     [自助/揽收系统生成的数组 必须]
	 * @return [type]          [description]
	 */
	public function get_lang($arr){

		// 如果包含线路名的多语言关键字  20180201 jie
		if(isset($arr['lng_line_name'])){
			$arr['lng_line_name'] = L('MKLINES')[trim($arr['lng_line_name'])];
		}
		
		// 如果没有这个参数，就是参数数据的直接返回，所以不需要经过处理
		if(!isset($arr['lng'])){
			echo json_encode($arr);exit;
		}

		$arr['msg'] = L(trim($arr['lng']));
		
		// $arr['msg'] = (!isset($langArr[$arr['lng']])) ? ((isset($arr['msg'])) ? $arr['msg'] : '') : trim($langArr[$arr['lng']]);

		echo json_encode($arr);exit;
	}

}