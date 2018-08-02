<?php
/**
 * 补录身份证明
 */
namespace Web\Controller;
use Think\Controller;
class ProfileController extends Controller {

    
    //补录资料及税费 视图
    public function index(){
    	$data = I('get.data'); // 获取地址上的数据
    	$data = json_decode(base64_decode($data),ture);  // 解析数据
    	$this->assign('res',$data); 	// 赋值到模板
    	$this->display();
    	
    }


    //第一步 快件信息
    public function step_one(){
    	if(IS_AJAX){
    		// sleep(1);
	    	vendor('Hprose.HproseHttpClient');
			$client = new \HproseHttpClient(C('APIURL').'/Certificates');

			$arr = array(
				'order_no' =>trim(I('order_no')), // 美快Q单号
				'receiver' =>trim(I('rec')),	  // 收件人姓名
				'reTel'    =>trim(I('phone')),    // 收件人手机号码
			);

			$backArr = $client->add_one($arr);


			$backArr['url'] = U('Logistics/index',array('order_no'=>$arr['order_no'])); // 生成跳转到第二步地址
			//160119税金暂用以下表示
			$backArr['taxstr']	= L("Pr_Pay_by_sender");
			$this->ajaxReturn($backArr);
    	}
    }
	



	//第二步 身份证信息
	public function step_two(){

		$sup_info = I('post.');
        $order_no = trim(I('order_no'));	//order_no
		foreach($sup_info as $k=>$v){
			$sup_info[$k] = trim($v);
		}
		if(empty($sup_info['order_no']) && empty($sup_info['MKNO'])){
			echo json_encode(array(
				'status' => '0',
				'err_info' => L('lack_of_parameters'),
			));
			die;
		}else if(empty($sup_info['order_no'])){
			$no = $sup_info['MKNO'];
		}else{
			$no = $sup_info['order_no'];
		}
		// 必须要有的参数
		$must = array(
			'receiver', 'cnumber'
		);
		foreach($must as $k=>$v){
			if(empty($sup_info[$v])){
				echo json_encode(array(
					'status' => '0',
					'err_info' => L('lack_of_parameters'),
				));
				die;
			}
		}

		// 提交过来的身份证号码
        $idno = $sup_info['cnumber'];

        vendor('Hprose.HproseHttpClient');
//        $idno_check = new \HproseHttpClient(C('RAPIURL').'/IdentityImgVerify');
        $idno_save = new \HproseHttpClient(C('RAPIURL').'/IdcardInfo');

        // 查询原订单信息
        $info = $idno_save->getInfoByOrderNo($order_no);

        // if(!empty($info) && !empty($info['front_id_img']) && !empty($info['back_id_img']) && !empty($info['idno'])){
        //     echo json_encode(array('err_info'=>L('order_in_idcard'),'status'=>false));
        //     exit;
        // }
        if($info['id_img_status'] == '100' && $info['id_no_status'] == '100'){
            echo json_encode(array('err_info'=>L('order_in_idcard'),'status'=>false));
            exit;
        }

        if($info['idno_auth'] == 1){
            // 已经实名认证，则忽略新的的身份证号码，使用原来的身份证号码
            $idno = $info['idno'];
        }else{
            // 没有实名认证，则需要进行实名认证
            $obj = new \Lib10\Idcardno\AliIdcardno();
            $idnoauth = $obj->IdentificationCard($info['receiver'], $idno);
            if(!$idnoauth){
                echo json_encode(array('err_info'=>L($obj->getError()),'status'=>false));
                exit;
            }
        }


        if(!empty($info['front_id_img']) && !empty($info['back_id_img']) && $info['id_img_status'] == '100'){
            // 上传身份证图片
            $need_upload = false;
        }else{
            // 进行上传操作
            $file_one = \Lib11\IdcardUpload\IdcardUpload::file_upload($_FILES['file_one']);
            $file_two = \Lib11\IdcardUpload\IdcardUpload::file_upload($_FILES['file_two']);
            $need_upload = true;
        }




		// 获取原订单信息
//		$info['front_id_img'] = base64_decode($sup_info['f']);
//		$info['small_front_img'] = base64_decode($sup_info['sf']);
//		$info['back_id_img'] = base64_decode($sup_info['b']);
//		$info['small_back_img'] = base64_decode($sup_info['sb']);
		$receiver = $sup_info['receiver'];


        $file = [];


        if($need_upload){
            // 得到身份证图片的地址
            if($file_one['success'] && !empty($_FILES['file_one']) && $_FILES['file_one']['error']!=4){
                // 有上传，则使用上传的
                $file['front_id_img'] = $file_one['info'];
                $file['small_front_img'] = $file_one['small'];
            }else{
                // 没有上传，则使用以前的（以前可能为空，因此下面需要判断）
                // $file['front_id_img'] = $info['front_id_img'];
                // $file['small_front_img'] = $info['small_front_img'];
                $file['front_id_img'] = '';
                $file['small_front_img'] = '';
            }
            if($file_two['success'] && !empty($_FILES['file_two']) && $_FILES['file_two']['error']!=4){
                // 有上传，则使用上传的
                $file['back_id_img'] = $file_two['info'];
                $file['small_back_img'] = $file_two['small'];
            }else{
                // 没有上传，则使用以前的（以前可能为空，因此下面需要判断）
                // $file['back_id_img'] = $info['back_id_img'];
                // $file['small_back_img'] = $info['small_back_img'];
                $file['back_id_img'] = '';
                $file['small_back_img'] = '';
            }
        }else{
            $file['front_id_img'] = $info['front_id_img'];
            $file['small_front_img'] = $info['small_front_img'];
            $file['back_id_img'] = $info['back_id_img'];
            $file['small_back_img'] = $info['small_back_img'];
        }


		// 缺少正面照片或者背面照片，则报错
		if($need_upload && (empty($file['front_id_img']) || empty($file['back_id_img']))){
			echo json_encode(array('msg'=>L('idcard_photos'),'state'=>'no'));
			exit;
		}


//		$back_idcard_info = $this->idcard_photo( WU_ABS_FILE . $file['back_id_img']);
//		$front_idcard_info = $this->idcard_national_emblem( WU_ABS_FILE .  $file['front_id_img']);
//		$idcard_info_merge = array_merge($front_idcard_info['info'], $back_idcard_info['info']);
//
//		// 验证身份证图片和号码是否正确
//		$idno_check_res = $idno_check->check_idno($idcard_info_merge, $receiver, $idno);
//		if(!$idno_check_res['status']){
//			echo json_encode(array(
//				'state'=>'no',
//				'msg' => L($idno_check_res['err_info']),
//			));
//			die;
//		}

		// 开始添加身份证信息
		$res = $idno_save->idno_save_mkno_or_orderno($no, array(
			'receiver' => $receiver,
			'idno' => $idno,
			'file' => $file,
//			'idcard_info' => $idcard_info_merge,
		));

		echo json_encode(array(
			'status' => '1',
			'msg' => L('add_submit'),
			'url' => U('Logistics/index',array('order_no'=>$sup_info['order_no'])),
		));
		die;

	}

//	// 识别正面图片
//    private function idcard_photo($url){
//        $obj = new \Lib10\Idcardali\AliIdcard();
//        $res = $obj->photo($url);
//        if($res){
//            return array(
//                'status' => true,
//                'info' => $obj->photo($url)
//            );
//        }else{
//            return array(
//                'status' => false,
//                'info' => $obj->getError()
//            );
//        }
//    }
//
//    // 识别反面图片
//    private function idcard_national_emblem($url){
//        $obj = new \Lib10\Idcardali\AliIdcard();
//        $res = $obj->national_emblem($url);
//        if($res){
//            return array(
//                'status' => true,
//                'info' => $obj->national_emblem($url)
//            );
//        }else{
//            return array(
//                'status' => false,
//                'info' => $obj->getError()
//            );
//        }
//    }
	
}