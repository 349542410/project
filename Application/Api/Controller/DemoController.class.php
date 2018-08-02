<?php
/**
 * 测试用，没其他用途
 */
namespace Api\Controller;
use Think\Controller;
class DemoController extends Controller{

    //判断小数点第三位是否大于0，若是，则进一，否则不变
    private function num_to_change_for_zt($n){
        $num = floatval($n) * 1000;
        $str = substr($num,(strlen($num)-1),1);

        if($str > 0){
            $num = floatval($num) - floatval($str);
            $num = floatval($num) + 10;
            $num = sprintf("%.2f", floatval($num)/1000);
            return $num;
        }else{
            return sprintf("%.2f", floatval($n));
        }
    }
    
	public function index(){
		echo dirname(__FILE__);
		echo '<br>';
		echo C('Kdno_Path');
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
        // dump($Kdno->SubmitTracking($arr)) ;
    	dump($Kdno->test()) ;
    }
}