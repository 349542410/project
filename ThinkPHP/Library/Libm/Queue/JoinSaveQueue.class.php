<?php
namespace Libm\Queue;
class JoinSaveQueue{

	public function join_queue($data = array(), $config = array()){
		if(empty($data)){
            return array(
                'status' => false,
                'info' => 'Data must be array',
            );
		}

		if(count($data) == 0){
			return array(
				'status' => false,
				'info' => 'Data cannot be empty',
			);
		}

        $Host = empty($config['Host']) ? C('Redis')['Host'] : $config['Host'] ;
        $Port = empty($config['Port']) ? C('Redis')['Port'] : $config['Port'] ;
        $Auth = empty($config['Auth']) ? C('Redis')['Auth'] : $config['Auth'] ;
        $overtime = empty($config['overtime']) ? 0 : $config['overtime'];

        try{
        	$redis = new \Redis();
        	$redis = connect($Host, $Port, $overtime);
        	$redis->auth($Auth);

        	$redis->lPush('save_tran_list_state_queue', \serialize($data));
        }catch(\RedisException $e){
        	return array(
        		'status' => false,
        		'info' => $e->getMessage();
        	);
        }

        return array(
        	'status' => true,
        	'info' => 'success',
        );
	}
}