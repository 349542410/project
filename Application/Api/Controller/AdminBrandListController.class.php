<?php
/**
 * 美快后台登录验证  服务器端
 */
namespace Api\Controller;
use Think\Controller\HproseController;
class AdminBrandListController extends HproseController{
	
	/**
	 * 品牌名称列表
	 * Enter description here ...
	 */
	public function index_list($data){
		
    	$epage = $data['epage'];
    	$p = $data['p'];
    	unset($data['epage']);
    	unset($data['p']);
    	
       	$count = M('brand_list')->alias('bl')->where($data)->count();
    
		$list = M('brand_list')->alias('bl')->field('bl.*, ml.name')->join('left join mk_manager_list AS ml ON bl.cre_by = ml.id ')->where($data)->page($p,$epage)->order('bl.id desc')->select();
    	
		return array('count'=>$count, 'list'=>$list);
	}

	/**
	 * 品牌添加处理
	 * Enter description here ...
	 */
	public function addhandle($data){
		//检验品牌名称是否存在

		if(!empty($data['id'])){
			$wh['brand_name']  = $data['brand_name'];
			$wh['id'] = array('neq', $data['id']);
			$rek = M('brand_list')->where($wh)->find();
			if(!empty($rek)){
				$rew['status'] = false;
	    		$rew['data']['strstr'] = '品牌名称已存在！';
	    		//$rew['data']['url'] =U('AdminBrandList/index');
	    		return $rew;
			}			
			$w['id'] = $data['id'];
			$res = M('brand_list')->where($w)->save($data);
			return $res;
			
		}else{
			//return $data;
			$res = M('brand_list')->addAll($data);
			return $res;
			
		}
	
	}
	
	/**
	 * 品牌名称修改
	 * Enter description here ...
	 * @param unknown_type $data
	 */
	public function edit($data){
		$res = M('brand_list')->where($data)->find();
		return $res;
	}
	
	/**
	 * 
	 * Enter description here ...
	 * @param $data
	 */
	public function edit_all($data){
		
		$res = M('brand_list')->field('brand_name')->where($data)->select();
		return $res;
	}
	
	public function delete($data){
		$res = M('brand_list')->where($data)->delete();
		return $res;
		
	}
	
	/**
	 * 上传CSV文件处理
	 * Enter description here ...
	 */
	public function importhandle($data){
		//检验品牌名称是否存在
//		$where['brand_name'] = array('in', $data['wh']);
//		
//		$rek = M('brand_list')->field('brand_name')->where($where)->select();
//		//return $rek;
//		if(!empty($rek)){
//			foreach ($rek as $key => $val){
//				$rex[] = $val['brand_name'];
//			}
//			$rex_str = implode(' ', $rex);
//			$rew['status'] = false;
//    		$rew['data']['strstr'] = $rex_str . ' 品牌名称已存在！';
//    		//$rew['data']['url'] =U('AdminBrandList/index');
//    		return $rew;
//		}
//		$addata = $data['da'];
		$res = M('brand_list')->addAll($data);
		return $res;
	
	}
	
	
	
}	
	