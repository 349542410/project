<?php
namespace Web\Controller;
use Think\Controller;
class IdcollectionController extends Controller {
	/*
	Man 创建于2018-01-12
	应用于，由客人上传身份证号与照片网址的短分析并跳转

	修改记录如下：
	
	*/
	public function _initialize()
	{
		$ids 	= I('path.1','0');
        //查询物流信息
        if($ids=='g'){
            $id = I('path.1','0');
            $this->logs($id);
            die();
        }

        //上传身份证
        if($ids=='m'){
            $id = I('path.1','0');
        }
        if(substr($ids,0,1)=='I'){
            $id = substr($ids,1);
        }
        $id = trim($id);

//        dump($id);die;
        if(strlen($id)>2){
			$this->toupview($id);
		}
		die();
	}

	public function toupview($id)
	{

		vendor('Hprose.HproseHttpClient');
        $client = new \HproseHttpClient(C('APIURL').'/Piecemeal');
        $res = $client->find_mk_info($id);

		$res = base64_encode(json_encode($res));
		header("Location: ".C('supplement_info_url')."?data=".$res);

	}

	public function logs($id)
	{
		echo $id;

	}
}