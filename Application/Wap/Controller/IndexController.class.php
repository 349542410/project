<?php
namespace Wap\Controller;
use Think\Controller;
class IndexController extends Controller {

	//首页
    public function index(){
       	$this->display();
    }

    public function confirm(){
    	$mkno = I('mkno','0');
    	$mkno = trim($mkno);
    	if($mkno == '0' || strlen($mkno) < 7){
    		echo '资料有误，无法查询。请咨询客服。';die;
    	}

		$astr = array('','a','b','c','d','e','f','g','h','i','j','k','l','m','n');
		$sstr = $mkno[0];//取第一个字符
		$astr = array_flip($astr);//反转数组
		$bstr = $astr[$sstr];

		$number = str_replace($sstr ,'' ,$mkno); //把符合数组中的数据替换为空
		$mkno   = 'MK88'.$bstr.$number."US";//拼接完整的MKNO
        // dump($mkno);
        
        // 补录资料
        if($sstr == 'z'){
        	// $this->redirect('Index/search', array('MKNO' => $mkno));
        	
        	$arr = M('TranUlist')->field('order_no,receiver,reTel')->where(array('MKNO'=>$mkno))->find();
        	$res = base64_encode(json_encode($arr));
        	header("Location: ".C('supplement_info_url')."?data=".$res);

        }else{//查询物流信息
        	// $this->redirect('Index/search', array('MKNO' => $mkno));
        	header("Location: /Index/search/?MKNO=".$mkno);
        }
    }

    //搜索
    public function search(){
		$MKNO = I('MKNO');
		$order_no = I('order_no');

		if(!empty($order_no)){
			$MKNO = $order_no;
		}

		if(!empty($MKNO)){
			// $match 	= "/^MK[a-zA-Z0-9]{10,12}$/";			


	    	// if(!preg_match($match,$MKNO)){
	    	// 	die('请输入正确美快单号，例如：MK881000987US');
	    	// }

			vendor('Hprose.HproseHttpClient');
			$client = new \HproseHttpClient(C('RAPIURL').'/Server');
			//$list = $client->query($MKNO);   // 放在erpsms下，方便不同环境显示不同

			//$str = '[{"time":"2015-10-10 10:10:10","kd":"创建订单","by":"管理员"}]';
			//echo $str .'<br/>';
			//echo base64_encode($str);//exit;

			/*
				Man 20151201 当ERP打开时 传来 SMS(内容为与该单号相关联的ERP数据),但在官网是不显示的
			*/
			$this->esms = '';
			$this->esmc = 0;
			$erpsms 	= I('SMS','');
			$erpsms 	= str_replace(" ","+",$erpsms);
			//echo $erpsms;
			if($erpsms<>''){
				$list = $client->query($MKNO,2);
				if($erpsms = base64_decode($erpsms)){
					if($erpsms = json_decode($erpsms,true)){
						$this->esms = $erpsms;
						$this->esmc = count($this->esms);
					}
				}
			}else{
				$list = $client->query($MKNO,1);
			}
			//============
			$this->assign('MKNO',$MKNO);
			$this->assign('list',$list);

			//exit;
		}
    	$this->display();
    }

    //补录资料及税费 视图
    public function record(){
		vendor('Hprose.HproseHttpClient');
		$client = new \HproseHttpClient(C('RAPIURL').'/Certificates');
    	//如传来 MKNO时
    	// $mkno = 'MK81000053US';//测试
    	$order_no = trim(I('order_no'));
    	$data = I('get.data');
    	$data = json_decode(base64_decode($data),true);
    	$this->assign('res',$data);
    	//如果$mkno不为空
    	if($order_no != ''){
    		//检查MKNO是否存在
    		$check = $client->check($mkno);
	    	//存在
	    	if($check && $check['IL_state'] < 200){

	    		//检查对应的tran_list.TranKd对应的线路是否要求身份证(1即为申通的，需要填写身份证号码)
	    		// if($check['cid'] == '1'){

	    			//if(empty($check['idno']) || $check['idno'] == '8'){
	    				$this->assign('order_no',$order_no);
	    			//}else{
	    				//$this->assign('tips','无需补录资料');
	    			//}
	    			
	    		// }else{
	    		// 	//不要求上传身份证时显示：无需补录资料
		    	// 	$this->assign('tips','无需补录资料');
		    	// 	$this->assign('url',U('Index/search',array('MKNO'=>$check['MKNO'])));
		    	// 	$this->assign('swit',1);
	    		// }
	    	
	    	}else if($check && $check['IL_state'] >= 200){
	    		$this->assign('tips','无需补录资料');
	    		$this->assign('url',U('Index/search',array('order_no'=>$check['order_no'])));
	    		$this->assign('swit',1);
	    	}else if(!$check){
	    		$this->assign('tips','美快单号['.$order_no.']不存在');
	    		$this->assign('url',U('Index/search',array('order_no'=>$check['order_no'])));
	    		$this->assign('swit',2);
	    	}
    	}
    	$this->display();
    }

	public function explainImg()
	{
		$this->display();
	}

    //第一步 快件信息
    public function step_one(){
    	if(IS_AJAX){
    		// sleep(1);
	    	vendor('Hprose.HproseHttpClient');
			$client = new \HproseHttpClient(C('RAPIURL').'/Certificates');

			$arr = array(
				'order_no'     => trim(I('order_no')),
				'receiver'     => trim(I('rec')),
				'reTel'        => trim(I('phone')),
			);
			$backArr = $client->add_one($arr);
			$backArr['url'] = U('Index/search',array('order_no'=>$arr['order_no']));
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
		if(empty($sup_info['order_no']) && empty($sup_info['mkno'])){
			echo json_encode(array(
				'status' => '0',
				'err_info' => '缺少参数',
			));
			die;
		}else if(empty($sup_info['order_no'])){
			$no = $sup_info['mkno'];
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
                    'err_info' => '缺少参数',
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
                $file['front_id_img'] = $info['front_id_img'];
                $file['small_front_img'] = $info['small_front_img'];
            }
            if($file_two['success'] && !empty($_FILES['file_two']) && $_FILES['file_two']['error']!=4){
                // 有上传，则使用上传的
                $file['back_id_img'] = $file_two['info'];
                $file['small_back_img'] = $file_two['small'];
            }else{
                // 没有上传，则使用以前的（以前可能为空，因此下面需要判断）
                $file['back_id_img'] = $info['back_id_img'];
                $file['small_back_img'] = $info['small_back_img'];
            }
        }else{
            $file['front_id_img'] = $info['front_id_img'];
            $file['small_front_img'] = $info['small_front_img'];
            $file['back_id_img'] = $info['back_id_img'];
            $file['small_back_img'] = $info['small_back_img'];
        }

		// 缺少正面照片或者背面照片，则报错
		if($need_upload && (empty($file['front_id_img']) || empty($file['back_id_img']))){
			echo json_encode(array('msg'=>L('身份证照片不能为空'),'state'=>'no'));
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
			'msg' => '您已成功提交资料',
			'url' => U('Index/search',array('order_no'=>$sup_info['order_no'])),
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