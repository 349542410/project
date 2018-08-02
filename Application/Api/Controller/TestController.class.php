<?php
/**
 * 测试用，没其他用途
 */
namespace Api\Controller;
use Think\Controller\HproseController;
class TestController extends HproseController{

	public function postInfo($mkno){
		$info = M('TranList')->where(array('MKNO'=>$mkno))->find();

		if(!$info) return array('state'=>'no', 'msg'=>'MKNO不存在');
		$order = M('TranOrder')->where(array('lid'=>$info['id']))->select();

		if(!$order) return array('state'=>'no', 'msg'=>'订单对应的商品不存在');

		$info['Order'] = $order;

		require('Kdno17.class.php');

		$Kd = new \Kdno();

		$res = $Kd->SubmitOrder($info);
		return $res;
	}

	public function test(){

		/*$js = '{"KD":"toMKIL","CID":"2","SID":"20","CMD5":"d38704931ee1b2ad32c9d30eb6057e70","STM":"20161219033459","LAN":"zh-cn","toMKIL":[{"weight":"0.01","sender":"Ying JD","sendAddr":"40559 Encyclopedia,Fremont,CA","sendTel":"510-509-8478","sendcode":"94538","sfid":"370781197708283029","shopType":"Shop","shopName":"JD","receiver":"谢春","reAddr":"湖北省 武汉市 江夏区 金融港二路6号","province":"湖北省","city":"武汉市","town":"江夏区","postcode":"430200","reTel":"13720348560","notes":"","premium":"0","MKNO":"MK215487744","STNO","9973510790848","email":"89088668@qq.com","category1st":"成人鞋類","category2nd":"女鞋","category3rd":"","CID":"1","coin":"CNY","paykind":"支付宝","payno":"2525215245225225","paytime":"2016-12-14 12:23:34","idkind":"ID","idno":"370781197708283029","buyers_nickname":"sd23","TranKd":"5","auto_Indent1":113,"auto_Indent2":"1612141124153","number":2.0,"price":"1900.00","discount":"0.00","Order":[{"detail":"鞋D VENERE D34P8P 02166 C6029","hgid":"310916629630000022","unit":"双","specifications":"鞋底材料:EVA发泡胶 鞋面材料:纺织 款式:中帮 ","source_area":"越南","barcode":"1111102864","number":1,"catname":"女鞋","price":500.00,"weight":"0.01","coin":"CNY","brand":"GEOX","hs_code":""},{"detail":"男鞋BASIC ROLL-TOP 6634A","hgid":"310916629630000021","unit":"打","specifications":"类别:女式 款式:背心 织造方式:机织 成分含量:含丝70%及以上 ","source_area":"越南","barcode":"1111110121","number":1,"catname":"男鞋","price":700.00,"weight":"0.01","coin":"CNY","brand":"TIMBERLAND","hs_code":""}]}]}
	';
		$arr = json_decode($js,true);

		$list = $arr['toMKIL'][0];
		// echo '<pre>';
		print_r($arr);die;*/


		$js = '{"KD":"toMKIL","CID":"2","SID":"20","CMD5":"d38704931ee1b2ad32c9d30eb6057e70","STM":"20161219033459","LAN":"zh-cn","toMKIL":[{"weight":"1.01","sender":"Ying JD","sendAddr":"40559 Encyclopedia,Fremont,CA","sendTel":"510-509-8478","sendcode":"94538","sfid":"370781197708283029","shopType":"Shop","shopName":"JD","receiver":"谢春","reAddr":"湖北省 武汉市 江夏区 金融港二路6号","province":"湖北省","city":"武汉市","town":"江夏区","postcode":"430200","reTel":"13720348560","notes":"","premium":0,"MKNO":"MK81000053US","email":"89088668@qq.com","category1st":"成人鞋類","category2nd":"女鞋","category3rd":"","CID":"1","coin":"CNY","paykind":"支付宝","payno":"2525215245225225","paytime":"2016-12-14 12:23:34","idkind":"ID","idno":"370781197708283029","buyers_nickname":"sd23","TranKd":"5","auto_Indent1":113,"auto_Indent2":"1612141124153","number":2,"price":"1900.00","discount":"0.00","Order":[{"detail":"鞋D VENERE D34P8P 02166 C6029","hgid":"06010100","unit":"双","specifications":"鞋底材料:EVA发泡胶 鞋面材料:纺织 款式:中帮 ","source_area":"越南","barcode":"1111102864","number":1,"catname":"女鞋","price":"1900.00","weight":"1.01","coin":"CNY","brand":"GEOX","hs_code":"6598982","auto_Indent2":"test1"},{"detail":"鞋D VENERE D34P8P 02166 C6029","hgid":"06010100","unit":"双","specifications":"鞋底材料:EVA发泡胶 鞋面材料:纺织 款式:中帮 ","source_area":"越南","barcode":"1111102864","number":1,"catname":"女鞋","price":"1900.00","weight":"1.01","coin":"CNY","brand":"GEOX","hs_code":"6598982","auto_Indent2":"test1"}]}]}';

		$arr = json_decode($js,true);

		$list = $arr['toMKIL'][0];

		require('MkBc2Customs.class.php');

		$Kd = new \MKBc2CustomsApi();

		$res = $Kd->request($list);
		dump($res);

	}


	public function index(){
		echo date('Ymd').substr(implode(NULL, array_map('ord', str_split(substr(uniqid(), 7, 13), 1))), 0, 12);
	}

	public function demo(){
		$res = M('WlorderRecord r')->field('r.*,a.MKNO,a.STNO,u.username,a.IL_state,a.ex_context,a.ex_time,a.mode,a.optime')->join('left join mk_tran_ulist t on t.order_no = r.order_no')->join('left join mk_user_list u on u.id = r.UID')->join('left join mk_tran_list a on a.MKNO = t.MKNO')->where(array('r.id'=>'501'))->order('ordertime desc')->select();
		dump($res);

		$list = M('TranUlist')->where(array('order_no'=>'255501926135'))->find();
		dump($list);

	}

	public function demo2(){
		// return M('NodePushLogs')->where(array('noid'=>'978'))->select();
		return M('TranList t')->field('e.node_push_state')->join('LEFT JOIN mk_tran_list_state e ON e.lid = t.id')->where(array('t.noid'=>'978'))->select();

	}

	public function _demo3($no){
		return M('TransitNo')->where(array('no'=>$no))->find();
	}

	public function _demo4($MKNO){
		return M('TranList')->where(array('MKNO'=>$MKNO))->find();
	}

	public function _demo5($MKNO){
		return M('TranUlist')->where(array('order_no'=>$MKNO))->find();
	}

	public function _demo6($id){
		return M('TranOrder')->where(array('lid'=>$id))->select();
	}

	public function _demo7($id){
		return M('TranUorder')->where(array('lid'=>$id))->select();
	}

	public function _demo8(){
		return $goods = M('TranUorder t')->field('t.*,t.num_unit as unit,t.catname as specifications,c.cat_name as catname, c.price as tax_rate')->join('left join mk_category_list c on c.id = t.category_two')->where(array('t.lid'=>'1140'))->select();
	}

    // 根据运单号 (审单)推送节点 给中通
    public function toPush(){

    	$arr = array(
    		'STNO'       => '120279190930',
    		'push_state' => 'Verified',
    		'airno'      => '',
    		'data'       => array('MKNO'=>'MK883393310US','STNO'=>'120279190930'),
    	);
    	require_once('Kdno17.class.php');
    	$Kdno = new \Kdno();
    	$res = $Kdno->SubmitTracking($arr);
    	// $res['Kdno_Path'] = C('Kdno_Path');

    	return $res;
    }
}