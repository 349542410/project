<?php
/**
 * 包裹列表
 */
namespace WebUser\Controller;
use Think\Controller;
use Think\Log;

class MemberController extends BaseController {

    public function _initialize() {
        parent::_initialize();

        // vendor('Hprose.HproseHttpClient');
        $client = new \HproseHttpClient(C('RAPIURL').'/Web');      //读取、查询操作
        $this->client = $client;
        $Wclient = new \HproseHttpClient(C('WAPIURL').'/Web');      //增删改操作
		$this->Wclient = $Wclient;
		
		$this->pie = new \HproseHttpClient(C('RAPIURL').'/Piecemeal');

        $client->cleanEmptyOrder();	//进入包裹列表界面前 先执行清理货品声明为空的订单
    }

    /**
     * 包裹列表
     * @return [type] [description]
     */
    public function index(){
		ini_set('date.timezone','Asia/Shanghai');
        set_time_limit(0);
        // 用于修复中文搜索的时候搜索栏的中文正常显示
		foreach ($_GET as $k=>$v){
			if(!is_array($v)){
				if (!mb_check_encoding($v, 'utf-8')){
					$_GET[$k] = iconv('gbk', 'utf-8', $v);
				}
			}else{
				foreach ($_GET['_URL_'] as $key=>$value){
					if (!mb_check_encoding($value, 'utf-8')){
						$_GET['_URL_'][$key] = iconv('gbk', 'utf-8', $value);
					}
				}
			}
		}

		// dump($_GET);
		// die;
		
		$mkuser    = session('mkuser');
		$mkno      = trim(I('mkno'));		//单号
		$rec       = trim(I('rec'));		//收件人
		$send       = trim(I('send'));		//寄件人
		$phone     = trim(I('phone'));		//电话

		$idno     = trim(I('idno'));		//身份证号码状态
		$idno_stat     = trim(I('idno_stat'));		//身份证图片状态

		$line_id	= trim(I('line'));
		$starttime = I('starttime');
		$endtime   = I('endtime');
	
		$status    = trim(I('status','0'));  //受理状态

		$print_status = trim(I('get.print_status', '0'));

		$package_id = trim(I('get.package_id'));


		//没有填写身份证号码但上传了身份证图片的记录不存在
		if($idno==2 && $idno_stat==1){
			$type = I('get.type','index');
			self::assign($_GET);
			self::assign('alist',array());
			self::assign('page','');
			$this->display($type);
			die;
		}


		if($rec) $map['t.receiver'] = array('eq', $rec);		//收件人
		if($phone) $map['t.reTel']  = array('eq', $phone);		//收件人电话
		$map['t.user_id']           = array('eq', (int)$mkuser['uid']);	//当前登陆的用户id

		//寄件人
		if(!empty($send)){
			$map['t.sender'] 			= array('eq',$send);
		}

		//线路
		if(!empty($line_id)){
			$map['t.TranKd'] 			= array('eq',$line_id);
		}

		if(!empty($idno)){
			if($idno == 1){
				$map['t.idno'] 			= array('neq','');
			}else{
				$map['t.idno'] 			= array('eq','');
			}
			
		}

		$type = I('get.type','index');	//必须

		if(!empty($idno_stat)){
			if($idno_stat == 1){
				if($type == 'index'){
					$map['t.id_img_status'] = array('neq',0);
				}else{
					$map['_string'] = "'ul.id_img_status' <> 0";
				}
			}else{
				if($type == 'index'){
					$map['t.id_img_status'] = array('eq',0);
				}else{
					$map['_string'] = "'ul.id_img_status' = 0";
				}
			}
		}
		

		//按时间段搜索
		if($starttime && $endtime){
			$map['t.ctime'] = array('between',array(date('Y-m-d H:i:s', strtotime($starttime)),date('Y-m-d H:i:s', strtotime($endtime))));
		}else if(!$starttime && $endtime){
			$map['t.ctime'] = array('elt',date('Y-m-d H:i:s', strtotime($endtime)));
		}else if($starttime && !$endtime){
			$map['t.ctime'] = array('egt',date('Y-m-d H:i:s', strtotime($starttime)));
		}

    	$p = I('get.p')?I('get.p'):1;	//当前页数，如果没有则默认显示第一页
    	//分页显示的数量
		$ePage = I('ePage');
		$ePage = $ePage?$ePage:C('EPAGE');

    	
    	self::assign($_GET);

    	/* 未完成页面读取tran_ulist */
    	if($type == 'index'){

    		$TranList = 'TranUlist';
    		/*
    		根据l.auto_Indent1 = t.id AND l.auto_indent2 = t.random_code此条件查出所有数据，然后根据l.auto_Indent1和auto_Indent2是否 is NULL来判断订单的状态
    		 */
			$l = '';
			$l2 = "left join mk_transit_center mc on t.TranKd=mc.id";
			$l3 = '';
			$l4 = "";
			$files = "t.*,t.id as u_id, mc.transit,mc.input_idno,mc.member_sfpic_state";
            
            //2015-11-09 新增 根据订单的状态查询数据,status=200(订单已经打印成功出单)，表示已受理，0是未受理
            switch ($status) {
            	case '1':
            		$map['t.print_state'] = array(array('eq', 0),array('eq', 10),'or');
            		break;

            	case '2'://已受理
            		$map['t.print_state'] = array('eq', 200);
            		break;

            	default:
            		# code...
            		break;
			}

			// 未删除
			$map['t.delete_time'] = array('exp', 'is null');

			if(!empty($package_id)){
				$map['t.package_id'] = array('eq', $package_id);
			}

			if(!empty($mkno)){
				$map['t.order_no'] = array('eq',$mkno);
			}
			
			if($print_status == 1){
				$map['t.is_print'] = 0;
			}else if($print_status == 2){
				$map['t.is_print'] = 1;
			}
			

    	}else{		/* 运输中、已完成页面读取tran_list */
    		$TranList = 'TranList';

			$l  = "";
			$l2 = "left join mk_transit_center mc on t.TranKd=mc.id";
			$l3 = "";
			$l4 = "";
            $files = "t.*,t.ex_context as content,t.ex_time as create_time,mc.transit,mc.input_idno,mc.member_sfpic_state,t.auto_Indent2 as order_no";

			// if($mkno) $map['t.MKNO'] = array('like',$mkno.'%');
			// if($mkno) $map['ul.order_no'] = array('eq',$mkno);
			
			if(!empty($mkno)){
				$mkno = $this->pie->get_MKNO_by_Qno($mkno);
				if(!empty($mkno)){
					$map['t.MKNO'] = array('eq',$mkno);
				}
			}

            // 外部订单号搜索
            if(!empty($package_id)){
                $map['t.package_id'] = array('eq', $package_id);
            }

//			if($print_status == 1){
//				$map['ul.is_print'] = 0;
//			}else if($print_status == 2){
//				$map['ul.is_print'] = 1;
//			}

			// dump($mkno);
			// die;
    		if($type == 'transport'){
				$map['t.IL_state'] = array('neq','1003');	//运输中
    		}else if($type == 'finished'){
    			$map['t.IL_state'] = array('eq','1003');	//已完成
    		}
		}
		

	    $client = $this->client;
		$list = $client->_list($TranList,$map,$p,$ePage,$l,$files,$l2,$l3,$l4);

        // 查询身份证照片上传状态
        if($type != 'index'){
            $mknoArray = [];
            foreach ($list as $key => $value){
                if($value['MKNO']){
                    $mknoArray[] = $value['MKNO'];
                }
            }
            $mknoArray = array_values(array_unique($mknoArray));
            $tranUlist = [];
            if($mknoArray){
                $mknoArray = join("','" , $mknoArray);
                $tranUlist = M('tran_ulist')->field('mkno, id_img_status, id_no_status, id')->where("mkno in ('$mknoArray')")->select();
            }

            $tranUlistData = [];
            foreach ($tranUlist as $key => $v){
                $tranUlistData[$v['id']] = $v;
            }
            foreach ($list as $key => &$v2){
                $v2['id_img_status'] =     $tranUlistData[ $v2['auto_Indent1']]['id_img_status']?:0 ;
                $v2['id_no_status'] =    $tranUlistData[$v2['auto_Indent1']]['id_no_status'] ?:0;
                $v2['u_id'] =           $tranUlistData[$v2['auto_Indent1']]['id'] ?:0;
            }
        }

    	self::assign('list',$list);// 赋值数据集

		$count = $client->count($TranList,$map,$l,$l3);
		// $count = $TranList->field('t.*,l.content,l.create_time')->join($l)->where($map)->count();// 查询满足要求的总记录数
		$page  = new \Think\Page($count,$ePage);// 实例化分页类 传入总记录数和每页显示的记录数
		$page->setConfig('prev', L('PrevPage'));//上一页
		$page->setConfig('next', L('NextPage'));//下一页
		$page->setConfig('first', L('FirstPage'));//第一页
		$page->setConfig('last', L('LastPage'));//最后一页
		$page->setConfig('header', '<span class="rows">'.L('TotalPage',array('n'=>'%TOTAL_ROW%')).'</span>');
		$page -> setConfig ( 'theme', '%FIRST% %UP_PAGE% %LINK_PAGE% %DOWN_PAGE% %END% %HEADER%' );

		// //分页跳转的时候保持查询条件
		// if(!empty($mkno)) $page -> parameter['mkno']           = $mkno;
		// if(!empty($rec)) $page -> parameter['rec']             = $rec;
		// if(!empty($phone)) $page -> parameter['phone']         = $phone;
		// if(!empty($starttime)) $page -> parameter['starttime'] = $starttime;
		// if(!empty($endtime)) $page -> parameter['endtime']     = $endtime;
		// if(!empty($ePage)) $page -> parameter['ePage']         = $ePage;
		// if(!empty($status)) $page -> parameter['status']       = $status;

		$show = $page->show();// 分页显示输出

        //加载 线路种类
        $all_line = $this->all_line;

        $alist = array();
        //以id=>lngname的方式整理数组
        foreach($all_line as $item){
            $alist[$item['id']] = $item['lngname'];
        }

        self::assign('alist',$alist);   //中转线路
    	self::assign('page',$show);// 赋值分页输出
		self::assign('wapurl',WAP_URL);// 赋值分页输出
        $this->display($type);
    }

	/**
	 * 获取详细信息
	 * @return [type] [description]
	 */
	public function info(){
        
		$id       = trim(I('get.id'));
		$order_no = (I('get.order_no')) ? trim(I('get.order_no')) : '';
		$type     = I('get.type','index');

		$client = $this->client;

		// dump($_GET);die;

		//订单信息
		$res = $client->getInfo($id, $type, $order_no, session('user_id'));


		//如果未填写身份证，则身份证图片不可能存在（如果存在，其实那是一个错误的记录）
		if(empty($res['info']['idno'])){
			$res['info']['front_file_name'] = '';
			$res['info']['back_file_name'] = '';
			$res['info']['front_id_img'] = '';
			$res['info']['back_id_img'] = '';
		}


//		 dump($res);die;
		// echo json_encode(array('res'=>$res));
		// die;

		//liao ya di
		if(!$res){
			echo '<h3>该单在打单时出现问题，请与店员或客服联系，给你带来不便，敬请原谅</h3>';
			die;
		}


		// dump($res);
		$res['info']['weight'] = sprintf("%.2f", $res['info']['weight']);

		// dump($res['pro_list']);

        /* 显示已上传的证件照正反面图片 */
        //证件照正面文件名不为空
        if($res['info']['id_img_status'] == 200) {
            $this->assign('front_id_img', C('TMPL_PARSE_STRING.__MEMBER__') . '/images/notupload_f.png');   // 显示默认国徽图片
            $this->assign('back_id_img', C('TMPL_PARSE_STRING.__MEMBER__') . '/images/notupload_b.png'); // 显示默认照片图片
        }else{
            if($res['info']['lib_idcard'] != 0){
                $this->assign('front_id_img', C('TMPL_PARSE_STRING.__MEMBER__').'/images/upload_success_f.png');   // 显示默认国徽图片
                $this->assign('back_id_img', C('TMPL_PARSE_STRING.__MEMBER__').'/images/upload_success_b.png'); // 显示默认照片图片
                $res['info']['idno'] = idcard_format($res['info']['idno']);
            }else{
                if($res['info']['front_id_img'] != ''){
                    $this->assign('ID_front_img',$res['info']['front_file_name']);          // 把证件照正面文件名字保存到session
                    $this->assign('front_id_img', WU_FILE.$res['info']['front_id_img']);    // 显示证件照正面图片
                }else{
                    $this->assign('ID_front_img','');       // 把证件照正面文件名字从session中移除
                    $this->assign('front_id_img', C('TMPL_PARSE_STRING.__MEMBER__').'/images/pho_front.png');   // 显示默认图片
                }
                if($res['info']['back_id_img'] != ''){
                    $this->assign('ID_back_img',$res['info']['back_file_name']);            // 把证件照反面文件名字保存到session
                    $this->assign('back_id_img', WU_FILE.$res['info']['back_id_img']);      // 显示证件照反面图片
                }else{
                    $this->assign('ID_back_img','');        // 把证件照反面文件名字从session中移除
                    $this->assign('back_id_img', C('TMPL_PARSE_STRING.__MEMBER__').'/images/pho_back.png'); // 显示默认图片
                }
            }
        }


        /* 显示已上传的证件照正反面图片 end */
        
		$this->assign('list',$res['msg']);//订单物流信息
		$this->assign('info',$res['info']);//订单信息
//        dump($res['info']);
        $this->pro_list = $res['pro_list'];//订单商品信息
		$this->type = $type;

		// dump($res);
		
        //加载 线路种类
        $tranline = $this->all_line;

        $alist = array();
        //以id=>lngname的方式整理数组
        foreach($tranline as $item){
            $alist[$item['id']] = $item['lngname'];
        }

        if($res['center']['cc_state'] == '1' && $res['center']['tax_kind'] == '1'){
	        //根据汇率计算出美元免税的额度
	        //$free_duty = sprintf("%.2f", floatval(C('RMB_Free_Duty')) / floatval(C('US_TO_RMB_RATE')));
            if(floor($res['center']['taxthreshold']) > 0){
                $free_duty = sprintf("%.2f", $res['center']['taxthreshold'] / floatval(C('US_TO_RMB_RATE')));
            }else{
                $free_duty = sprintf("%.2f", floatval(C('RMB_Free_Duty')) / floatval(C('US_TO_RMB_RATE')));
            }
            $free_duty = sprintf("%.2f", $free_duty);
        }else{
        	$free_duty = '';
        }

        self::assign('free_duty',$free_duty);
        self::assign('alist',$alist);   //中转线路

		$this->display();
	}

	

	/**
	 * 订单删除 方法
	 * @return [type] [description]
	 */
	public function delete(){

    	if(!IS_POST){
    		$this->redirect('Public/404');
    		exit;
    		// die('非法操作~！');
    	}

        $username = session('admin.adname');		//当前登陆的管理员

		$id           = trim(I('post.id'));		//mk_tran_list 的id
		$MKNO         = trim(I('post.MKNO'));		//美快单号

		$Wclient = $this->Wclient;

		$result = $Wclient->_delete($id,$MKNO,$username);

		//根据多语言输出错误信息
		$result['msg'] = L($result['code']);

		$this->ajaxReturn($result);

	}

	
	/**
	 * 列表页面 - 补填身份证信息
	*/
	public function idcard_update(){

//		$idno_check = new \HproseHttpClient(C('RAPIURL').'/IdentityImgVerify');
		$idno_save = new \HproseHttpClient(C('RAPIURL').'/IdcardInfo');

		$idno = trim(I('post.idno'));

		// 身份证号码不能为空
		$order_id = I('post.order_id');
		if(empty($idno)){
			echo json_encode(array(
				'status' => false,
				//'err_info' => '身份证号码不能为空',
                'err_info' => L('not_idcard'),
			));
			die;
		}

		// 订单号不能为空
		if(empty($order_id)){
			echo json_encode(array(
				'status' => false,
				//'err_info' => '此订单不存在',
                'err_info' => L('not_order_exist'),
			));
			die;
		}

		//验证身份证格式
		if(!certificate($idno)){
			echo json_encode(array(
				'status' => false,
				//'err_info' => '身份证号码格式不正确',
                'err_info' => L('idcard_format'),
			));
			die;
		}

		// 查询原订单信息
		$info = $idno_save->getInfoByOrderId($order_id);
		if(!$info){
			echo json_encode(array(
				'status' => false,
				//'err_info' => '订单信息有误',
                'err_info' => L('order_info_mistaken'),
			));
			die;
		}
		$receiver = $info['receiver'];


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


		if($info['id_img_status'] == '100' && $info['id_no_status'] == '100'){
            echo json_encode(array('err_info'=>L('order_in_idcard'),'status'=>false));
            exit;
        }

		if(!empty($info['front_id_img']) && !empty($info['back_id_img']) && $info['id_img_status'] == '100'){
            // 上传身份证图片
            $need_upload = false;
        }else{
            // 上传身份证图片
            $file_one = \Lib11\IdcardUpload\IdcardUpload::file_upload($_FILES['file_one']);
            $file_two = \Lib11\IdcardUpload\IdcardUpload::file_upload($_FILES['file_two']);
            $need_upload = true;
        }

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
                // 现在改为必须上传才行
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
			echo json_encode(array('err_info'=>L('idcard_photos'),'status'=>false));
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
//				'status'=>false,
//				'err_info' => L($idno_check_res['err_info']),
//			));
//			die;
//		}

		// 开始添加身份证信息
		$res = $idno_save->idno_save_order($order_id, array(
			'receiver' => $receiver,
			'idno' => $idno,
			'file' => $file,
//			'idcard_info' => $idcard_info_merge,
		));

		echo json_encode(array(
			'status' => true,
			'err_info' => '',
		));
		die;

	}

	/**
	 * 列表页面 - 补填身份证信息 - 只补填身份证号码
	*/
	public function idno_update(){

		// 身份证号码不能为空
		$idno = trim(I('post.idno'));
		if(empty($idno)){
			echo json_encode(array(
				'status' => false,
				'err_info' => L('not_idcard'),
			));
			die;
		}

		$line_id = trim(I('post.line_id'));
		if(empty($line_id)){
			echo json_encode(array(
				'status' => false,
				'err_info' => L('line_not_empty'),
			));
			die;
		}

		// 验证身份证格式
		if(!certificate($idno)){
			echo json_encode(array(
				'status' => false,
				'err_info' => L('id_not_correct'),
			));
			die;
		}

		$order_id = I('post.order_id');
		if(empty($order_id)){
			echo json_encode(array(
				'status' => false,
				'err_info' => L('not_order_exist'),
			));
			die;
		}

        $idno_save = new \HproseHttpClient(C('RAPIURL').'/IdcardInfo');

        // 查询原订单信息
        $info = $idno_save->getInfoByOrderId($order_id);
        if(!$info){
            echo json_encode(array(
                'status' => false,
                'err_info' => L('order_info_mistaken'),
            ));
            die;
        }

        if($info['idno_auth'] == 1){
            // 已经实名认证，则忽略新的的身份证号码，使用原来的身份证号码
            echo json_encode(array(
                'status' => true,
                'err_info' => '',
            ));
            die;
        }else{
            // 没有实名认证，则需要进行实名认证
            $obj = new \Lib10\Idcardno\AliIdcardno();
            $idnoauth = $obj->IdentificationCard($info['receiver'], $idno);
            if(!$idnoauth){
                echo json_encode(array('err_info'=>L($obj->getError()),'status'=>false));
                exit;
            }
        }

		// 验证线路是否是无需上传身份证图片


		// 修改身份证号码
		$idno_save->idno_update_by_id($order_id, $idno);


		echo json_encode(array(
			'status' => true,
			'err_info' => '',
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
    /**
     * 查询订单key
     */
    public function getKey(){
	    $id = I('get.id');
        $key = M('mkno_key')->where("u_id = $id")->getField('un_key');
        echo $key;
    }
}