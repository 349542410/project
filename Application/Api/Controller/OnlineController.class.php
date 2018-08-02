<?php
/**
 * 网上下单物流信息交互V2  2015-11-09  即官网后台与ERP交互数据
 */
namespace Api\Controller;
use Think\Controller;
class OnlineController extends Controller{

	public function index(){
		$jn = new \Org\MK\JSON;
		$js = $jn->get();

		// 测试用  李四
		// $js = '{
		//     "KD": "toMKIL",
		//     "CID": "1",
		//     "SID": "20",
		//     "CMD5": "0ec741792d7e7cafe9bc3e783df3f0bb",
		//     "STM": "20151030064656",
		//     "LAN": "zh-cn",
		//     "toMKIL": [
		//         {
		//             "code": "120151030133151",
		//             "receiverTel": "18665629352"
		//         }
		//     ]
		// }';
		// $js = json_decode($js,true); //json转成数组形式
		//测试End

		/*
			错误码
			0表示：没收到JSON或JSON内容为空
			1表示 成功
			2表示，MD5码不正确
			3为数据类型不对。即json.kd不对
		 */
		$arrmsg = array(
			'0' => 'IsEmpty',	//内容为空
			'2' => 'IsWrong',	//MD5码不正确
			'3' => 'WrongType',	//数据类型错误
		);
		if(!is_array($js)){
			//如有错误，即返回错误码
			// $errCode = $js; // 错误码(默认为0) 没收到JSON或JSON内容为空
			// $type    = "false";   //这说明操作是正常的
			// $logstr  = $arrmsg[$js];

				// LOG数组
				$log = array(
					array(
				        "code"=>$js['toMKIL'][0]['code'],
				        "receiverTel" => $js['toMKIL'][0]['receiverTel'],
				        "Success" => "false",   //这说明操作是不正常的
				        "LOGSTR" => $arrmsg[$js],
					)
				);

				// 返回JSON格式：
				$ja = $jn->respons($js['KD'],$js['CID'],$log,$js,$arrmsg[$js]);
				//直接生成string输出 echo $ja;
				echo $ja;
				return;

		}

		$code = $js['toMKIL'][0]['code'];	//随机码

		$res = M('TranUlist')->where(array('random_code'=>$code))->find();	//根据获取的随机码查询对应的数据
		$order = M('TranUorder')->field('brand,detail,number,catname,price,weight,coin')->where(array('lid'=>$res['id']))->select();	//订单中的商品详细
		$email = M('UserList')->where(array('id'=>$res['user_id']))->getField('email');

		$prices = 0;
		$arr = array();
		$orderList = array();
		foreach($order as $v){
			$prices += $v['price'];
			$arr['detail']  = $v['brand']." ".$v['detail'];
			$arr['number']  = $v['number'];
			$arr['catname'] = !empty($v['catname'])?$v['catname']:"";
			$arr['price']   = $v['price'];
			$arr['weight']  = $v['weight'];
			$arr['coin']    = $v['coin'];
			$orderList[] = $arr;
		}

		//如果数据不存在
		if(!$res){
			// LOG数组
			$log = array(
				array(
			        "code"=>$js['toMKIL'][0]['code'],
			        "receiverTel" => $js['toMKIL'][0]['receiverTel'],
			        "Success" => "false",   //这说明操作是不正常的
			        "LOGSTR" => 'NotExist',	//查无此项
				)
			);

			// 返回JSON格式：
			$ja = $jn->respons($js['KD'],$js['CID'],$log,4,L('NotExist'));
			//直接生成string输出 echo $ja;
			echo $ja;
			return;

		}

		// LOG数组
		$log = array();
		// foreach($order as $item){
			$info = array(
				"weight"       => '1.00',
				"user_id"      => $res['user_id'],
				// "code"         => $js['toMKIL'][0]['code'],	//随机码	原样返回
				// "receiverTel"  => $js['toMKIL'][0]['receiverTel'],	//收件人电话 原样返回
				// "Success"      => "true",   //这说明操作是正常的
				"sender"       => $res['sender'],	//发件人
				"sendAddr"     => $res['sendAddr'],	//发件人地址
				"sendTel"      => $res['sendTel'],	//发件人电话
				"sendcode"     => $res['postcode'],	//发件人邮编
				"shopType"     => 'MK',	//固定MK
				"shopName"     => 'Meiquick',	//固定Meiquick
				"receiver"     => $res['receiver'],	//收件人
				"reAddr"       => $res['reAddr'],	//收件人详细地址
				"province"     => $res['province'],	//省
				"city"         => $res['city'],	//市
				"town"         => $res['town'],	//区
				"postcode"     => $res['recode'],	//收件人邮编
				"reTel"        => $res['reTel'],	//收件人电话
				"notes"        => !empty($res['notes'])?$res['notes']:"",	//备注
				"premium"      => $res['premium'],	//保险金额
				"MKNO"         => "",//$mkno,	//美快运单号
				"email"        => $email,	//国内收件人Email
				"category1st"  => $order[0]['brand'],	//第一条order的品牌
				"category2nd"  => !empty($order[0]['catname'])?$order[0]['catname']:"",	//第一条order的类别
				"category3rd"  => "",
				"CID"          => "1",
				"TranKd"       => $res['TranKd'],
				"auto_Indent1" => $res['id'],	//ulist.id
				"auto_Indent2" => $res['random_code'],	//ulist.随机码
				"number"       => $res['number'],	//总数量
				"price"        => $prices,	//总价值
				"ctime"        => $res['ctime'],	//创建时间
				"Order"        => $orderList,	//商品详细
			);
			$log[] = $info;
		// }


		$ja = $jn->respons($js['KD'],$js['CID'],$log,1,'Done');	//Done  完成
		//直接生成string输出 echo $ja;
		echo $ja;exit;

	}

}