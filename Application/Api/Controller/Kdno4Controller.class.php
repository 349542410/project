<?php
/**
 * 海关订单报备(广东邮政用) 端口测试用  \
 * 丢弃  20170328 jie
 */
namespace Api\Controller;
use Think\Controller;
class Kdno4Controller extends Controller{

	//配合测试用的数据
	function _initialize(){
		// echo $_SERVER['HTTP_HOST'];
		if($_SERVER['HTTP_HOST'] == 'mkapi.megao.hk:83'){
			$js = '{"KD":"toMKIL","CID":"1","SID":"20","CMD5":"d38704931ee1b2ad32c9d30eb6057e70","STM":"20161219033459","LAN":"zh-cn","toMKIL":[{"weight":"1.01","sender":"Ying JD","sendAddr":"40559 Encyclopedia,Fremont,CA","sendTel":"510-509-8478","sendcode":"94538","sfid":"370781197708283029","shopType":"Shop","shopName":"JD","receiver":"谢春","reAddr":"湖北省 武汉市 江夏区 金融港二路6号","province":"湖北省","city":"武汉市","town":"江夏区","postcode":"430200","reTel":"13720348560","notes":"","premium":0,"MKNO":"MK81000053US","email":"89088668@qq.com","category1st":"成人鞋類","category2nd":"女鞋","category3rd":"","CID":"1","coin":"CNY","paykind":"支付宝","payno":"2525215245225225","paytime":"2016-12-14 12:23:34","idkind":"ID","idno":"370781197708283029","buyers_nickname":"sd23","TranKd":"5","auto_Indent1":113,"auto_Indent2":"1612141124153","number":2,"price":"1900.00","discount":"0.00","Order":[{"detail":"鞋D VENERE D34P8P 02166 C6029","hgid":"06010100","unit":"双","specifications":"鞋底材料:EVA发泡胶 鞋面材料:纺织 款式:中帮 ","source_area":"越南","barcode":"1111102864","number":1,"catname":"女鞋","price":500,"weight":"1.01","coin":"CNY","brand":"GEOX","hs_code":""},{"detail":"男鞋BASIC ROLL-TOP 6634A","hgid":"06010100","unit":"打","specifications":"类别:女式 款式:背心 织造方式:机织 成分含量:含丝70%及以上 ","source_area":"越南","barcode":"1111110121","number":1,"catname":"男鞋","price":"130.01","weight":"1.01","coin":"CNY","brand":"TIMBERLAND","hs_code":""}]}]}';

			$list = json_decode($js,true);

			$this->list = $list;
		}else{
			die('error');
		}
		
	}

	//订单报备 海关
	public function index(){
		C('SHOW_PAGE_TRACE','');//直接在此关闭SHOW_PAGE_TRACE
		require_once('GoodsCustoms.class.php');

		$js = $this->list;
		
		//暂定
		$data = $js['toMKIL'][0];
		$data['CID'] = $js['CID'];

		// dump($data);die;
		$GD = new \Custom();

		$res = $GD->isOrder($data);
		print_r($res);
	}

}