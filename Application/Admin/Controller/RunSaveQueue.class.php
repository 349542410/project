<?php
namespace Api\Controller;
use Think\Controller;
class RunSaveQueueController extends Controller{

	public function run(){
        $Host = empty($config['Host']) ? C('Redis')['Host'] : $config['Host'] ;
        $Port = empty($config['Port']) ? C('Redis')['Port'] : $config['Port'] ;
        $Auth = empty($config['Auth']) ? C('Redis')['Auth'] : $config['Auth'] ;

		$redis = new \Redis();
		$redis->connect($Host, $Port);
		$redis->auth($Auth);

		$num = 100
		for($i=0; $i<$num; $i++){

			$info[$i] = $redis->rPop('save_tran_list_state_queue');

			$info[$i] = unserialize($info[$i]);
		}







	}
}