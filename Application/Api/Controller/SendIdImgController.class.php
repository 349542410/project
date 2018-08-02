<?php
/**
 * 推送身份证照片给顺丰 服务器端
 */
namespace Api\Controller;
use Think\Controller\HproseController;
use AUApi\Controller\KdnoConfig\Kdno12;
class SendIdImgController extends HproseController {

    public function _index($no,$type){
		$map['MKNO']   = array('eq', $no);
		$map['STNO']   = array('eq', $no);
		$map['_logic'] = 'or';

    	$info = M('TranList t')->field('t.receiver,t.reTel,t.idno,t.STNO,e.id_img,e.img_state,e.id as eid')->join('left join mk_user_extra_info e on e.idno=t.idno')->where($map)->find();

    	// 当$scount > 1，表示批量执行推送，此时需要筛选出已经推送过的单号，不执行推送;
    	// 当$scount = 1，无论是否已经推送过，也继续执行推送
    	if($type == 'more'){
    		if($info['img_state'] == '1'){
	    		return array('result'=>'false','message'=>'重复推送(不再推送)');
	    	}
    	}

		$arr['name']   = $info['receiver'];
		$arr['phone']  = $info['reTel'];
		$arr['cardId'] = $info['idno'];
		$arr['bno']    = $info['STNO'];

		$img_url = ADMIN_ABS_FILE.$info['id_img'];
		//return array('result'=>'false','message'=>base64_encode($img_url));
		//return $img_url;
		/*
		$img = file_get_contents($img_url);//待修改， 暂定用后台的目录储存，读取方式用网址路径

		$bm  = unpack('C*',$img);
		$str = call_user_func_array('pack',array_merge(array('C*'),$bm));
		$str = base64_encode($str);//图片需经过base64加密处理
		*/
		$str = base64_encode(file_get_contents($img_url));
		//return array('result'=>'false','message'=>$str);
		$arr['image']  = $str;
		//$arr['image']  = $this->base64EncodeImage($img_url);//图片需经过base64加密处理

		$SF = new Kdno12();

		$res = $SF->uploadIMG($arr,$img_url);
		//推送成功，则更新推送状态
		if($res['result'] != 'false'){
			M('UserExtraInfo')->where(array('id'=>$info['eid']))->setField('img_state',1);
		}

		return $res;
	}
	private function base64EncodeImage($image_file){
		$base64_image	= '';
		$image_info		= getimagesize($image_file);
		$image_data		= fread(fopen($image_file, 'r'), filesize($image_file));
		$base64_image	= chunk_split(base64_encode($image_data));
		return $base64_image;
	}
}