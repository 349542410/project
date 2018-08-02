<?php
/*
create by Man 20180222
1.读取数据库中的汇率
2.获取接口返回的最新汇率
*/
namespace Org\MK;
class CRT {
	/*读取数据库中的汇率*/
	public function get($from,$to,$defaultrate){
		$cfrom 	= strtoupper(trim($from));
		$cto 	= strtoupper(trim($to));
		//code=1 表示成功读取
		$rs 	= array('code'=>0,'from'=>$cfrom,'to'=>$cto,'rate'=>$defaultrate,'rtime'=>0);
		
		if(strlen($cfrom)<2 || strlen($cto)<2){
			return $rs;
		}

		$rate 	= M("currency_rate");
		$where	= array(
			"cfrom"	=> ':cfrom',
			"cto"	=> ':cto',
			);
		$bind	= array(
			":cfrom"	=> array($cfrom,\PDO::PARAM_STR),
			":cto"		=> array($cto,\PDO::PARAM_STR),
			);
		$row 	= $rate->where($where)->bind($bind)->find();
		if($row){
			$rs['code']		= 1;
			$rs['rate']		= $row['rate'];
			$rs['rtime']	= $row['ctime'];
		}
		return $rs;
	}
	public function update($from,$to,$addrate=0,$low=0)  //low表示最低值
	{
	    $cfrom 		= strtoupper(trim($from));
	    $cto 		= strtoupper(trim($to));		
		if(strlen($cfrom)<2 || strlen($cto)<2 || !is_numeric($addrate)){
			return $cfrom.'-'.$cto.'-'.$addrate;
		}

	    $host 		= "https://ali-waihui.showapi.com";
	    $path 		= "/waihui-transform";
	    $method 	= "GET";
	    $appcode 	= "0ac402c73aa24ac596c1f9da494a926b";

	    $headers 	= array();
	    array_push($headers, "Authorization:APPCODE " . $appcode);
	    $querys 	= "fromCode={$cfrom}&money=1&toCode={$cto}";
	    $bodys 		= "";
	    $url 		= $host . $path . "?" . $querys;

	    $curl 		= curl_init();
	    curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $method);
	    curl_setopt($curl, CURLOPT_URL, $url);
	    curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
	    curl_setopt($curl, CURLOPT_FAILONERROR, false);
	    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
	    curl_setopt($curl, CURLOPT_HEADER, false);
	    if (1 == strpos("$".$host, "https://"))
	    {
	        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
	        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
	    }
	    $rs 			= curl_exec($curl);
	    $httpStatusCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
		$httpErr 		= curl_error($curl);
		curl_close($ch);
		/*$res 			= array(
			"showapi_res_code"=> 0,
			"showapi_res_error"=> "",
			"showapi_res_body"=>array(
				"ret_code"=> 0,
				"money"=> "6.3591"
			)
		);*/
		if($httpStatusCode==200){
			$res 	= json_decode($rs,true);
			if(isset($res['showapi_res_code']) && $res['showapi_res_code']=='0' 
				&& isset($res['showapi_res_body']['money'])){

				//$rate = floatval($res['showapi_res_body']['money']);
				$rate = $res['showapi_res_body']['money'];
				if(is_numeric($rate) && $rate>$low){
					//保存到数据库
					$rate  += $addrate;
					$rate 	= sprintf("%.4f", $rate);
					$r 		= M("currency_rate");
					$where	= array(
						"cfrom"	=> $cfrom,
						"cto"	=> $cto,
					);
					$data 		= array('rate' => $rate);
					$row 		= $r->where($where)->data($data)->save();
					$drate 		= $r->where($where)->getField('rate');
					return 'DONE!'.$cfrom.'-'.$cto.':'.$rate.' data rate:'.$drate;
				}
			}
		}
		$rs = json_encode($res);
		return 'ERROR,httpstatus:'.$httpStatusCode.' return '.$rs;
	}
}