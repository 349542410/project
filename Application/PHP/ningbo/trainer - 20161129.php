<?php
/*
	版本号：V1.0
	创建人：Jie
	创建日期：2016-11-23
	修改日期：2016-11-28
	用途：自动去查询申报单的信息并保存
	数据表：mk_trainer_notes  mk_trainer_logs   mk_trainer

编写思路：
	StartTime和EndTime只有当NextPage为0 的时候才会发生变动，否则保持不变，直至查询完所有的页码数据之后，提示没有下一页。

1. 根据mk_trainer_notes的id倒叙后，获取StartTime(开始时间),EndTime(结束时间),NextPage(当前已查询的页数)；
1.1 当首次运行，mk_trainer_notes没有任何数据的时候，NextPage为1，EndTime为当前时间，StartTime为基于当前时间的前24h；
1.2 如果mk_trainer_notes已有数据，则取最新的数据，分析：若status=0，即该时间段的页码上的数据尚未完全保存/更新成功，需要继续执行该时间段的页码的数据操作；
1.3 当status=200，表示已经完成该时间段的页码的数据操作，则判断NextPage属性；若NextPage > 0，则NextPage+1，开始时间取其StartTime，结束时间取其EndTime；
	若NextPage = 0，则表示已经查询完毕，重新开始执行查询(NextPage从1开始查询)；
2. 根据《报关资料文档》的要求，生成必要的参数(包括xml请求报文)，发送到指定的地址，以获取反馈结果(返回xml报文)；
2.1 将返回的xml报文转为数组，若Header.Result = 'T'，先判断Body.Mft是一维数组还是二维数组，以此分别准备执行数据的保存或更新操作；
2.2 首先，判断Header.NextPage是否为T，若为T，在mk_trainer_notes保存新数据，记录StartTime(开始时间),EndTime(结束时间),NextPage(当前已查询的页数)等信息；
	若为F，则将NextPage改为 = 0(0表示已经查询完毕，没有下一页数据可查了)，保存数据。
3. 无论Mft中的数据是一位数组还是二维数组，都用function save()的方法进行数据的处理；
3.1 首先，检查mk_trainer是否已存在相同的数据，若已存在则更新，否则插入新数据；
3.2 mk_trainer_logs同理；
3.3 不同的是：mk_trainer 的Status只保存最新的状态，CreateTime只保存最新的操作时间；mk_trainer_logs则保存对应mk_trainer.LogisticsNo所有的Status，CreateTime；
	即mk_trainer_logs有且有多条关于此LogisticsNo的数据(类似mk_il_logs)，mk_trainer只有一条对应LogisticsNo的数据(类似mk_tran_list)
完
 */
	require_once('tr_config.php');//加载配置信息
	require_once('tr_connect.php');//数据库连接
	require_once('tr_function.php');//函数

	echo "\n".date('Y-m-d H:i:s')."\n";

	set_time_limit(0);//不限超时时间

	$time = time();//立即生成一个当前时间的时间戳，预备用作结束时间

	// 然后，把已经被这次运行的 $uuid 标识过的数据都找出来
	$maxlid_sql = "select StartTime,EndTime,NextPage,status from mk_trainer_notes order by id desc limit 1";
	$max = $pdo->query($maxlid_sql);

	// mk_trainer_notes没有任何数据 或者 NextPage=0的时候，默认为以下4个配置
	$StartTime = $time - 86400;// 一天前 减去一天的时间(24h)，以此作为开始时间
	$StartTime = date('Y-m-d H:i:s',$StartTime);// 开始时间
	$EndTime   = date('Y-m-d H:i:s',$time);//结束时间
	$NextPage  = 1;

	if($max->rowCount() > 0){
		$maxinfo = $max->fetch(PDO::FETCH_ASSOC);

		// 表示此时间段的该页的数据尚未全部保存成功
		if($maxinfo['status'] == '0'){
			$StartTime = $maxinfo['StartTime'];//开始时间
			$EndTime   = $maxinfo['EndTime'];//结束时间
			$NextPage  = $maxinfo['NextPage'];//再次获取此页的数据并执行保存、更新
		}else{
			// 等于0，表示查询完毕，没有下一页的数据
			if($maxinfo['NextPage'] == '0'){
				$StartTime = $maxinfo['EndTime'];//当NextPage=0，以上次的结束时间作为开始时间

				if($switch == true){
					echo '上一次查询反馈【没有下一页数据】，现在从下一个时间段且从第一页开始查询...<br/>';
				}else{
					echo '上一次查询反馈【没有下一页数据】，操作终止。若要继续进行下一个时间段的查询，请把开关设置为true。<br/>';
					exit;
				}
				
			}else{
				$StartTime = $maxinfo['StartTime'];//开始时间
				$EndTime   = $maxinfo['EndTime'];//结束时间
				$NextPage  = $maxinfo['NextPage'] + 1;//当前已经查询过的页数+1之后，作为查询用的下一页页码
			}
		}

	}

	$sign = md5($userid.$pwd.$EndTime);

	$xmlstr = createXML($StartTime, $EndTime, $NextPage);

	//需要发送的数据
	$form = array(
		'xmlstr'    => $xmlstr,
		'msgtype'   => $msgtype,
		'customs'   => $customs,
		'userid'    => $userid,
		'timestamp' => $EndTime,
		'sign'      => $sign,
	);

	$res = sendXML($url, $form, 1);	// 发送请求，申报状态查询

/*	// 测试数据
	$res = 
	'<Message>
	<Header>
		<Result>T</Result>
		<ResultMsg>ffsdfsdfsd</ResultMsg>
		<NextPage>T</NextPage>
	</Header>
	<Body>
			<Mft>
				<MftNo>4444444444444</MftNo>
				<OrderNo>55555555555555</OrderNo>
				<LogisticsNo>6666666666666</LogisticsNo>
				<CheckFlg>1</CheckFlg>
				<CheckMsg>加肥加大时看风景上岛咖啡</CheckMsg>
				<Status>00</Status>
				<Result>发斯蒂芬斯蒂芬</Result>
				<PaySource>支付宝</PaySource>
				<LogisticsName>顺丰</LogisticsName>
				<CreateTime>2016-11-23 12:30:55</CreateTime>
			</Mft>
			<Mft>
				<MftNo>4242342</MftNo>
				<OrderNo>432423432</OrderNo>
				<LogisticsNo>6546587666</LogisticsNo>
				<CheckFlg>1</CheckFlg>
				<CheckMsg>加肥加大时看风景上岛咖啡</CheckMsg>
				<Status>00</Status>
				<Result>发斯蒂芬斯蒂芬</Result>
				<PaySource>支付宝</PaySource>
				<LogisticsName>顺丰</LogisticsName>
				<CreateTime>2016-11-23 12:30:55</CreateTime>
			</Mft>

	</Body>
	</Message>';*/

	// $result = json_decode(json_encode((array) simplexml_load_string($res)), true);// 返回的XML报文转为数组
	// xml转数组
	$result = xml_to_array($res);

	// 返回的数据，开始执行保存
	if(isset($result['Message']['Header']['Result']) && $result['Message']['Header']['Result'] == 'T'){

		$pdo->beginTransaction();	//开启事务

		//第一步，先在 mk_trainer_notes 插入新数据记录当前查询之后返回的查询页码等必要的数据
		$Page = ($result['Message']['Header']['NextPage'] == 'T') ? $NextPage : 0;

		$check_note = "SELECT * FROM mk_trainer_notes ORDER BY id DESC LIMIT 1";

		$res_note = $pdo->query($check_note);

		if($res_note->rowCount() > 0){
			$ninfo = $res_note->fetch(PDO::FETCH_ASSOC);
			$note_sql = "UPDATE mk_trainer_notes SET StartTime='$StartTime',EndTime='$EndTime', NextPage='$Page', status='0' WHERE id = '$ninfo[id]'";
		}else{
			$note_sql = "INSERT INTO mk_trainer_notes (StartTime,EndTime,NextPage) VALUES ('$StartTime','$EndTime','$Page')";
		}
		
		// 登记是否插入数据成功
		$note_res = ($pdo->exec($note_sql) !== false) ? true : false;

		if($note_res === true){
			//mk_trainer_notes第一次插入数据的则取插入的id，否则用已有的id
			$ninfo['id'] = ($pdo->lastInsertId()) ? $pdo->lastInsertId() : $ninfo['id'];
		}else{
			echo 'trainer_notes数据更新或插入失败<br/>';exit;
		}
		// End One

		// 第二部，开始处理 mk_trainer 的数据更新或插入新数据
		$data = $result['Message']['Body']['Mft'];//返回的物流信息
		
		// 验证数组是一维还是二维
		$check_arr = ck_array($data);

		// 二维数组
		if($check_arr > 1){

			$count = count($data);
			$sus = 0;

			foreach($data as $key=>$item){

				$save = save($item,$StartTime,$EndTime);

				if($save !== false){
					$sus++;
				}
			}

			//只有当$sus(成功保存或更新) = $count(请求得到的数据总数)，才完成操作
			if($sus == $count){
				$note_sql = "UPDATE mk_trainer_notes SET status='200' WHERE id = '$ninfo[id]'";
				if($pdo->exec($note_sql) !== false){
					$pdo->commit();      //事务确认
					echo '操作成功！返回数据总数为：'.$count.'条；成功保存/更新：'.$sus.'条。';
				}else{
					$pdo->rollback();    //事务回滚
					echo 'trainer_notes.status状态变更失败！返回数据总数为：'.$count.'条；成功保存/更新：'.$sus.'条。';
				}
			}else{
				$pdo->rollback();    //事务回滚
				echo '操作失败！返回数据总数为：'.$count.'条；成功保存/更新：'.$sus.'条。';
			}

		}else{//返回的数据结果为一位数组

			$save = save($data,$StartTime,$EndTime);

			if($save !== false){
				$note_sql = "UPDATE mk_trainer_notes SET status='200' WHERE id = '$ninfo[id]'";
				if($pdo->exec($note_sql) !== false){
					$pdo->commit();      //事务确认
					echo '操作成功！';
				}else{
					$pdo->rollback();    //事务回滚
					echo 'trainer_notes.status状态变更失败！';
				}
			}else{
				$pdo->rollback();    //事务回滚
				echo '操作失败！';
			}
		}

		// F 表示没有下一页数据，则终止继续操作
        if($result['Message']['Header']['NextPage'] == 'F'){
        	echo '没有下一页，查询完毕！';
        }

	}else{
		if(isset($result['Message']['Header']['ResultMsg'])){
			echo $result['Message']['Header']['Result'].' '.$result['Message']['Header']['ResultMsg'];
		}
		
	}

	/*
		数据保存或更新
	 */
	function save($data,$StartTime,$EndTime){
		require('tr_connect.php');//数据库连接

		$pdo->beginTransaction();	//开启事务

		// 用于更新数据表的时候需要的字段(无值的时候是需要默认为空)
		$pr_arr = array('MftNo','OrderNo','LogisticsNo','CheckMsg','Result','PaySource','LogisticsName','CreateTime','StartTime','EndTime');
		// 用于更新数据表的时候需要的字段(无值的时候是需要默认为0)
		$nu_arr = array('CheckFlg','Status');

		$check_trainer = "SELECT * FROM mk_trainer WHERE `MftNo` = '$data[MftNo]' AND `OrderNo` = '$data[OrderNo]' AND `LogisticsNo` = '$data[LogisticsNo]'";

		$res_mkil = $pdo->query($check_trainer);

		$set = '';
		$val = '';

		// 检查是否已经存在此申报单的资料
		if($res_mkil->rowCount() > 0){
			$cinfo = $res_mkil->fetch(PDO::FETCH_ASSOC);


			foreach($data as $pey=>$it){
				if(in_array($pey,$pr_arr) || in_array($pey,$nu_arr)){
					$$pey = ((isset($it) ? count($it) : 0) == 0) ? ((in_array($pey,$nu_arr)) ? '0' : '') : htmlspecialchars($it);
					$set .= $pey."='".$$pey."',";
				}
			}

			$set .= 'StartTime='."'".$StartTime."',";
			$set .= 'EndTime='."'".$EndTime."',";
			$set = rtrim($set, ',');//清除最右侧的英文逗号

			$add_sql = "UPDATE mk_trainer SET ".$set." WHERE id = '$cinfo[id]'";

		}else{//不存在此申报单，则查询新数据

			foreach($data as $pey=>$it){
				if(in_array($pey,$pr_arr) || in_array($pey,$nu_arr)){
					$$pey = ((isset($it) ? count($it) : 0) == 0) ? ((in_array($pey,$nu_arr)) ? '0' : '') : htmlspecialchars($it);
					$set .= $pey.",";
					$val .= "'".$$pey."',";
				}
			}

			$set .= 'StartTime,EndTime';
			$val .= "'".$StartTime."','".$EndTime."'";//记录当前查询的页数和结束时间
			$set = rtrim($set, ',');//清除最右侧的英文逗号
			$val = rtrim($val, ',');//清除最右侧的英文逗号

			$add_sql = "INSERT INTO mk_trainer (".$set.") VALUES (".$val.")";

		}

		$check_logs = "SELECT * FROM mk_trainer_logs WHERE `LogisticsNo` = '$data[LogisticsNo]' AND `Status` = '$data[Status]' AND `CreateTime` = '$data[CreateTime]'";

		$res_logs = $pdo->query($check_logs);

		$content = state_turn($data['Status']);

		// 已存在记录
		if($res_logs->rowCount() > 0){
			$linfo = $res_logs->fetch(PDO::FETCH_ASSOC);
			$add_logs = "UPDATE mk_trainer_logs SET Status='$data[Status]',content='$content', CreateTime='$data[CreateTime]' WHERE id = '$linfo[id]'";

		}else{//未有记录
			$add_logs = "INSERT INTO mk_trainer_logs (LogisticsNo,Status,content,CreateTime) VALUES ('$data[LogisticsNo]','$data[Status]','$content','$data[CreateTime]')";
		}
		// echo $add_logs;
		if($pdo->exec($add_sql) !== false && $pdo->exec($add_logs) !== false){
			$pdo->commit();      //事务确认
			return true;
        }else{
        	$pdo->rollback();    //事务回滚
        	return false;
        }
	}

	// 状态说明
    function state_turn($code){
    	$arr = array(
    		'00' => '未申报',
    		'01' => '库存不足',
    		'02' => '发仓库配货',
    		'03' => '仓库已配货',
    		'11' => '已报国检',
    		'12' => '国检放行',
    		'13' => '国检审核未过',
    		'14' => '国检抽检',
    		'21' => '已报海关',
    		'22' => '海关单证放行',
    		'23' => '海关单证审核未过',
    		'24' => '海关货物放行',
    		'25' => '海关查验未过',
    		'99' => '已关闭',
    	);

    	$res = isset($arr[$code]) ? $arr[$code] : '未知';
    	return $res;
    }