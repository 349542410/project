<?php
/*
	测试美国跟中国时区
 */
	// $arr =  array(array('0'=>'080000819333'),array('0'=>'080000819333'),array('0'=>'080000819333'));
	$time = date('Y-m-d H:i:s');//'2016-09-19 15:17:05';
	echo '美国时间：'.$time;
	echo '<br>';
	$ctime = toTimeZone($time);
	echo '北京时间：'.$ctime;


	/*
	 * 时区转换
	 */
	function toTimeZone($src, $from_tz = 'America/Los_Angeles', $to_tz = 'PRC', $fm = 'Y-m-d H:i:s') {
	    $datetime = new DateTime($src, new DateTimeZone($from_tz));
	    $datetime->setTimezone(new DateTimeZone($to_tz));
	    return $datetime->format($fm);
	}