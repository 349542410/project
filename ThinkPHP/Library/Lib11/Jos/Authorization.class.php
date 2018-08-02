<?php

	namespace Lib11\Jos;

	class Authorization{

		public static $code_url = 'https://oauth.jd.com/oauth/authorize';
		public static $token_url = 'https://oauth.jd.com/oauth/token';

		//获取授权码
        public static function getToken($parameter){

            // $parameter = array(
            //     'appKey' => '82208D4438AB141463D37882EC42DF11',
            //     'appSecret' => 'b018fb1163144cadb47b33d236c359eb',
            //     'handleUrl' => 'http://pay.loc.megao.cn:891/home/jos/joshandle',
			// 	   'state' => rand(1000,9999) . '-' .uniqid(),
            // );
			
            if(empty($parameter)||empty($parameter['appKey'])||empty($parameter['appSecret'])||empty($parameter['handleUrl'])){
                //error，缺少必要的参数
				return false;
            }
			$parameter['state'] = rand(1000,9999) . '-' .uniqid();

			// 获取授权码
			$para = array(
				'response_type' => 'code',
				'client_id' => $parameter['appKey'],
				'redirect_uri' => $parameter['handleUrl'],
				'state' => $parameter['state'],
				// 'view' => 'wap',
			);

			$tf = new \Lib11\Jos\Jdsdk\ToolsFunction();
			$url = self::$code_url . '?' . $tf->ass_parameters($para);
			// 让浏览器跳转，不能直接在后台用curl请求
			// $result = $tf->postCurl( $url , '' , '' , 'get' );

			return $url;

		}

        //获取token
		public static function token($parameter,$code){
			define('ONE_DAY',60*60*23);
			define('ONE_YEAR',60*60*24*360);

			$para = array(
				'grant_type' => 'authorization_code',
				'code' => $code,
				'redirect_uri' => $parameter['handleUrl'],
				'client_id' => $parameter['appKey'],
				'client_secret' => $parameter['appSecret'],
				'state' => rand(10000,99999),
			);

            $tf = new \Lib11\Jos\Jdsdk\ToolsFunction();
			$url = self::$token_url . "?" . $tf->ass_parameters($para);
			$result = $tf->postCurl( $url , '' , '' , 'post' );
			// dump($result);

			if($result['success']){
				$d = json_decode($result['data'],true);

				//返回token
				return array(
                    'success' => true,
					'data' => array(
						'accessToken' => $d['access_token'],
						'refreshToken' => $d['refresh_token'],
						'time' => time(),
						'deadline' => time() + ONE_DAY,
					),
				);
				
			}else{

				//获取授权失败
				return array(
                    'success' => false,
					'data' => null,
				);

			}

		}

    }