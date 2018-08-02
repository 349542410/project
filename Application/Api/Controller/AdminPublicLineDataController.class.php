<?php
/**
 * 公共数据的获取
 * 功能包括：状态可用且账户有所属权限的线路
 * 
 */
namespace Api\Controller;
use Think\Controller\HproseController;
class AdminPublicLineDataController extends HproseController{

	public function _get_lines($map){

		return M('TransitCenter')->field('id,name')->where($map)->select();
	}
}