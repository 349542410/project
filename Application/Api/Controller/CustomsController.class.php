<?php

/*
2017-03-13 create by Man
gzgoods() ERP商家将商品报备到  广州国检(海关商品报备)

用途：与海关商品报备对接，包含功能：商品报备 专用
*/

namespace Api\Controller;
use Think\Controller;

class CustomsController extends Controller{
	public function gzgoods()
	{
		## code...
		// 校对好必传资料后，
		// 保存到美快数据库中
		// 生成海关$xmlstr
		// $data 	= new \Org\GZP\GZPort();
		// $rs   	= $data->send($xmlstr,'KJ881101','ERP传来商品唯一编号');
		// 把报备结果状态更新到美快数据库中
		// 把报备结果状态使用json输出{"goods_id":"ERP传来的商品唯一编号","result":"true|false"}
		// 此方法 为 ERP 进行商品报备 
		C('SHOW_PAGE_TRACE','');//直接在此关闭SHOW_PAGE_TRACE
		$jn = new \Org\MK\JSON;
		$js = $jn->get();
		if(!is_array($js)){
			echo $jn->respons("","",null,0,L("SYSERROR0"));
			exit;
		}
		$data = $js['toMKIL'][0];
		if(!is_array($data) || empty($js['CID'])){
			echo $jn->respons("","",'',3,L("SYSERROR3"));
			exit;
		}
		$data['CID'] 	= $js['CID'];
		require_once('GoodsCustoms.class.php');
		$GD = new \Custom();

		$res = $GD->isGoods($data);
		// $res = $GD->isGoods($data, false, 1); //调试用
		// 把报备结果状态使用json输出{"goods_id":"ERP传来的商品唯一编号","result":"true|false"}

		echo $jn->respons($js['KD'], $js['CID'], array($res));
	}
	public function index()
	{
		# code...
	}
	//本地测试
	public function test(){
		C('SHOW_PAGE_TRACE','');//直接在此关闭SHOW_PAGE_TRACE
		require_once('GoodsCustoms.class.php');

		// 模拟数据
		$js = '{"CID":"1","Code":"HWG000342","Name":"\u978bD VENERE D34P8P 02166 C6029","Specifications":"\u978b\u5e95\u6750\u6599:EVA\u53d1\u6ce1\u80f6 \u978b\u9762\u6750\u6599:\u7eba\u7ec7 \u6b3e\u5f0f:\u4e2d\u5e2e ","PostTaxCode":"01019900","OrginCountryName":"\u8d8a\u5357","NTWeight":"1.01","RefPrice":"500","Unit":"\u53cc","Currency":"\u4eba\u6c11\u5e01","HsCode":"3658497386","CIQTypeCode":"","ShelfGName":"\u978bD VENERE D34P8P 02166 C6029","Brand":"GEOX","IsNotGift":true,"Quality":"\u5408\u683c","Manufactory":"Made in US","GSWeight":"1.51","CiqGoodsNo":""}';
		$list = json_decode($js,true);
		// End

		$GD = new \Custom();

		$res = $GD->isGoods($list);
		// $res = $GD->isGoods($list, false, 1);
		print_r($res);
		// 
		// $jn = new \Org\MK\JSON;
		// echo $jn->respons('GZGOOD', $list['CID'],$res);
		// 把报备结果状态使用json输出{"goods_id":"ERP传来的商品唯一编号","result":"true|false"}
		// echo json_encode($res);
	}
}