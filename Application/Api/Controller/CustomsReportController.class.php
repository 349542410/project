<?php
/**
 * 商品报备 服务器端
 */
namespace Api\Controller;
use Think\Controller\HproseController;
class CustomsReportController extends HproseController{
	protected $crossDomain =	true;
	protected $P3P		   =    true;
	protected $get		   =    true;
	protected $debug 	   =    true;

	public function count($where,$p,$ePage){
		$list = M('Goods')->order('id desc')->where($where)->page($p.','.$ePage)->select();
		$count = M('Goods')->where($where)->count(); // 查询满足要求的总记录数
		return array('count'=>$count, 'list'=>$list);
	}

    /**
     * 获取某行数据详细信息
     * @param  [type] $id [被编辑的id]
     * @return [type]     [description]
     */
    public function getInfo($id){

    	//查询该id的商品报备信息
        $info = M('Goods')->where(array('id'=>$id))->find();

        //查询该商品的报备记录
        $pro_list = M('GoodsLogs')->where(array('goods_id'=>$id))->select();

        return $res = array($info,$pro_list);
    }
}