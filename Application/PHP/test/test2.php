<?php
	$a = '-234972222600_3855331313';
	// $a = str_replace("-","_",$a);
	// echo $a."<br>";
	// // $b = substr($a,strrpos($a,"_"));
	// // echo (stripos($a,"_")) ? str_replace(substr($a,stripos($a,"_")),'',$a) : $a;
	// echo (stripos($a,"_")) ? str_replace(substr($a,stripos($a,"_")),'',$a) : $a;

	$b = 'fsfsjf-jsdfjl_';
	$dstr = array('-','_');
	$res = str_replace($dstr,'',$a.$b);
	print_r($a.$b);echo '<br>';
	print_r($res);