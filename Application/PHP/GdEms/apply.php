<?php
/*
	版本号：V1.0
	创建人：Jie
	创建日期：2017-01-16
	修改日期：2017-02-04
	用途：广东邮政  自动 商品报备  (暂不可用)
	数据表：mk_tran_list  mk_tran_list_notes  mk_il_logs(暂不保存)  mk_logs(暂不保存) mk_trainer_logs(新增字段TaxTotal)
	指导文档：
	编写方案：与ningbo/trainer.php(顺丰报关资料获取)类似
*/
	// require_once('config.php');
	require_once('../db.php');//数据库连接
	require_once('h_config.php');//配置信息
	// require_once('Kdno4.class.php');//加载类
	// require_once('Kdno4.conf.php');//载入配置信息

	$Goods    = $out_load.'\GoodsCustoms.class.php';

	require_once($Goods);//加载类

	$EMS = new \Custom();

	// echo "\r\n".date('Y-m-d H:i:s')."\r\n";

	$node = isset($_GET['node']) ? $_GET['node'] : 'Gd_ems_custom';//默认 Hk_ems 香港E特快

	//主动获取顺丰的物流信息
	$time = time();	//标识码(时间戳) 访问此文件的时候马上生成

	// 查询倒序后的最新的一条数据
	$maxlid_sql = "select id,lid,ctime,state from mk_tran_list_notes where node = '$node' order by id desc limit 1";

	$max = $pdo->query($maxlid_sql);

	$maxinfo = $max->fetch(PDO::FETCH_ASSOC);

	$maxinfo['ctime'] = ($maxinfo['ctime'] == '') ? 0 : $maxinfo['ctime'];

	$tenHour = $time - $maxinfo['ctime'];	//当前时间的10小时前

	// 超过规定时限(10小时) 或者mk_tran_list_notes.max(id).state = 200，则会重新执行物流信息查询
	if($tenHour > $limitHour || $maxinfo['state'] == '200'){
		
		// 如果查询没有任何结果
		if($maxinfo === false){
			$maxlid = $maxinfo['lid'];
		}else{
			$maxlid = 0;
		}
		$ctime = $time;//超过10小时时限或者state=200，需要用 新的时间戳 标记 以示新一轮开始

	}else{
		$maxlid = $maxinfo['lid'];
	}
	/* 线程 End 20160921 */


	//查询mk_apply_list.apply_status=0 的 商品报备资料
	$find_sql = "SELECT * FROM mk_apply_list WHERE apply_status = 0 AND id > $maxlid ORDER BY id ASC LIMIT $limit";
// print_r($find_sql);
// die;
	$find = $pdo->query($find_sql);

	if($find->rowCount() > 0){

		$nu_list = $find->fetchAll(PDO::FETCH_ASSOC);
		// echo '<pre>';
		// print_r($nu_list);
		// die;

		$maxId = $nu_list[count($nu_list)-1]['id'];

		/* Jie 20160921 */
		if($max->rowCount() > 0){
			if($maxinfo['state'] == '200' || $tenHour > $limitHour){
				$note_sql = "UPDATE mk_tran_list_notes SET lid='$maxId', ctime='$ctime', state='0' WHERE id = '$maxinfo[id]'";
			}else{
				$note_sql = "UPDATE mk_tran_list_notes SET lid='$maxId' WHERE id='$maxinfo[id]'";
			}
		}else{
			$note_sql = "INSERT INTO mk_tran_list_notes (lid,ctime,node) VALUES ('$maxId', '$ctime','$node')";
		}

		$pdo->query($note_sql);
		// die;
		/* End 20160921 */

//============= 测试   ========

		// $nu_list =  array('0'=>array('STNO'=>'EK211140145HK'));//新 测试用
		// $nu_list =  array('0'=>array('STNO'=>'080000820051'),'1'=>array('STNO'=>'080001114498'),'2'=>array('STNO'=>'080000819360'));

		// print_r($nu_list);
		// die;
//============= 测试 End  ======== 
		$et = 0;//计算成功获取物流信息的总数
		$msg = '';
		// print_r($nu_list);die;
		// start 用于以后区分SF和其他物流公司
		foreach($nu_list as $kk=>$item){

			$tracking_number = $item['EntGoodsNo'];

			//调用文件中的函数处理
			$res[$kk] = $EMS->isGoods($item, true);
// print_r($res[$kk]);
// die;
			if($res[$kk]['do'] == 'yes'){
				$et++;
			}else{
				$msg .= '单号：'.$tracking_number.'，msg：'.$res[$kk]['title'].'；';
			}
			
		}
		//end
		// echo '<pre>';
		// print_r($res);
		// echo $et++;

		$endtime = time();
		$Ttime = $endtime - $time;
		
		// $et  计算成功获取并保存物流信息的总数
		if($et == 0){
			$backXML = '<Response service="RoutePushService"><Head>ERR</Head><ERROR code="4001">系统发生数据错误或运行时异常；耗时：'.$Ttime.'秒；反馈信息：'.$msg.'</ERROR></Response>';
			
		}else{
			$backXML = '<Response service="RoutePushService"><Head>OK</Head>请求发送总数：'.$limit.'个，实际查询数据：'.count($nu_list).'个；成功保存商品报备信息：'.$et.'个；耗时：'.$Ttime.'秒；反馈信息：'.$msg.'</Response>';
		}
		echo $backXML;

	}else{//当搜索数据表已经没有得到合适数据的时候，就把最大的id的状态标记为200
		echo '运行完成';
		/* Jie 20160921 */
		$note_sql = "UPDATE mk_tran_list_notes SET state='200' WHERE id = '$maxinfo[id]'";

		$pdo->query($note_sql);
		/* End 20160921 */
	}