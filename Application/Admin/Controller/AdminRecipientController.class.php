<?php
namespace Admin\Controller;
use Think\Controller;
class AdminRecipientController extends AdminbaseController{
	public $client;
	//public $writes;

	function _initialize(){
		parent::_initialize();
		//vendor('Hprose.HproseHttpClient');
        $this->client = new \HproseHttpClient(C('RAPIURL').'/AdminRecipient');		//读取、查询操作
        //$this->writes = new \HproseHttpClient(C('WAPIURL').'/AdminCollectPoint');		//添加，修改

	}




	/**
	 * 未处理身份证证件
	 * Enter description here ...
	 */
	public  function ntreated_documents(){
		$data['epage'] = C('EPAGE');
		$data['p'] = I('get.p');
		$name = I('get.name');
		$idcard = I('get.idcard');
		if(!empty($name)){
			$data['name'] = $name;
		}
		if(!empty($idcard)){
			$data['cre_num'] = $idcard;
		}

		$res = $this->client->ntreated_documents($data);
		$list = $res['list'];
		$count = $res['count'];
		$page=new \Think\Page($count,$data['epage']);
		//%FIRST% 表示第一页的链接显示 
		//%UP_PAGE% 表示上一页的链接显示 
		//%LINK_PAGE% 表示分页的链接显示 
		//%DOWN_PAGE% 表示下一页的链接显示 
		//%END% 表示最后一页的链接显示 

		$page->setConfig('theme','%FIRST% %UP_PAGE% %LINK_PAGE% %DOWN_PAGE% %END% %HEADER%');
		$page -> setConfig('prev', '上一页');
		$page -> setConfig('next','下一页');
		$page -> setConfig('last','末页');
		$page -> setConfig('first','首页');

		$show = $page->show();

		//print_r($list);
		//exit;

//		$th = '';
		foreach ($list AS $key => $val){
//			//print_r($val);
//			//exit;
//			
			$list[$key]['status_name'] = C('ID_CARD_STATUS')[ $val['status']];
            $list[$key]['idcard_status_name'] = C('ID_CARD_FAILURE_STATUS')[ $val['idcard_status']];
//			if(strlen($val['cre_num']) > 8 ){
//				$idcard_a 	= substr($val['cre_num'],0,4);
//				$idcard_b 	= substr($val['cre_num'],-4);
//				$idcard_c 	= substr($val['cre_num'], 4, -4);
//				$coumt 		= strlen($idcard_c);
//				for($i = 1; $i <= $coumt; $i++){
//					$th .= '*';
//				}
//				$idcard_d 	= str_replace($val['cre_num'], $idcard_c, $th);
//				$idcard = $idcard_a . $idcard_d . $idcard_b;
//				$list[$key]['idcard'] = $idcard;
//			}else{
//				$list[$key]['idcard'] = $val['cre_num'];
//			}
//			$th = '';
		}
		//print_r($list);
		//exit;
		//echo "<script> alert('不存在该照片！'); </script>";
		//echo "<meta http-equiv='Refresh' content='1;URL=$url'>";
		$this->assign('page',$show);// 赋值分页输出
		$this->assign('list',$list);
		$this->assign('wu_file', WU_FILE);
		$this->assign('admin_file', ADMIN_FILE);
		$this->assign('bucket_url', C('BUCKET_URL'));
		$this->assign('idcard_cos', C('IDCARD_COS'));
		$this->display();

	}



	/**
	 * 获取所有身份证号码信息
	 * Enter description here ...
	 */
	public  function more(){
		$idno = I('get.idno');
		$id   = I('get.id');
		if(empty($idno) || empty($id)){
			$this->error('收件人身份证号码不存在！');
			exit;
		}
		$where['id'] = $id;
		$info = $this->client->user_addressee($where);
		$data['idno'] = $idno;
		$list = $this->client->more($data);
//		print_r($list);
//		exit;
		$res_one = $list['res_one'];
		$res_two = $list['res_two'];
		$res_three = $list['res_three'];
		$this->assign('info', $info);
		$this->assign('res_one', $res_one);
		$this->assign('res_two', $res_two);
		$this->assign('res_three', $res_three);
		$this->assign('wu_file', WU_FILE);
		$this->assign('idno', $idno);
		$this->display();
	}

	/**
	 * 覆盖身份证号码 + 名字相同的照片
	 * Enter description here ...
	 */
	public function morehaddle(){

		//获取提交数据
		$name = I('post.name');
		$idno = I('post.idno');
		$front_id_img = I('post.front_id_img');
		$back_id_img = I('post.back_id_img');
		$small_front_img = I('post.small_front_img');
		$small_back_img = I('post.small_back_img');
		if(empty($front_id_img)){
			$rew['status'] = 0;
    		$rew['data']['strstr'] = '收件人身份证正面图片不存在';
    		$this->ajaxReturn($rew);
		}
		if(empty($back_id_img)){
			$rew['status'] = 0;
    		$rew['data']['strstr'] = '收件人身份证反面图片不存在';
    		$this->ajaxReturn($rew);
		}

		//修改相同身份证号码+ 名字的图片
		$data['name'] = $name;
		$data['idno'] = $idno;
		$data['front_id_img'] = $front_id_img;
		$data['back_id_img'] = $back_id_img;
		$data['small_front_img'] = empty($small_front_img) ? $front_id_img : $small_front_img;
		$data['small_back_img'] = empty($small_back_img) ? $back_id_img : $small_back_img;

		$res = $this->client->morehaddle($data);

		$this->ajaxReturn($res);
	}



	/**
	 * 保存上传合成图片
	 * Enter description here ...
	 */
	public function cardpic(){

		//
		$files = C("UPLOAD_DIR").C('UPLOAD_NAME');

		$status = I('post.status');
		if(!file_exists($files))
		{
		     mkdir ($files,0777,true);
		}

//		$uploadClass = new \Think\Upload();
//		$uploadClass->maxSize=C('UPLOAD_SIZE');
//		$uploadClass->exts=C('UPLOAD_TYPE'); 
//		$uploadClass->rootPath=$files;
//      	$info = $uploadClass->upload();

//			$files = C("UPLOAD_DIR").C('UPLOAD_NAME');
//			if(!file_exists($files))
//			{
//			     mkdir ($files,0777,true);
//			}
			$uploadClass = new \Think\Upload();
			$uploadClass->maxSize=C('UPLOAD_SIZE');
			$uploadClass->exts=C('UPLOAD_TYPE');
			$uploadClass->rootPath=$files;
	      	$info = $uploadClass->upload();


	    //print_r($info);
	    //exit;
      	if(!$info) {// 上传错误提示错误信息
	        $this->error($uploadClass->getError());
	       //$this->error('上传失败');
	    }else{// 上传成功
			$id = I('post.id');
			if(empty($id)){
				//$this->error('收件人信息不存在！');
				$rew['status'] = 0;
	    		$rew['data']['strstr'] = '收件人信息不存在！';
	    		$this->ajaxReturn($rew);
		    	exit;

			}
            $app1 = str_replace($GLOBALS['globalConfig']['UPFILEBASE'], '', C("UPLOAD_DIR"));

	    	$data['id_img'] =  $app1 . C('UPLOAD_NAME') . $info['card_pic']['savepath'].$info['card_pic']['savename'];
            if(C('IDCARD_COS')) {
                //上传图片到服务器	start
                $id_img = C("UPLOAD_DIR") . $data['id_img'];
                $dst = '/' . C('BUCKET_LIST') . $data['id_img'];
                $args = array(
                    'src' => $id_img,         // 待上传的文件，必须是文件在服务器上的路径（可以使用js上传到服务器的临时文件）
                    'dst' => $dst,     // 上传到配置文件中的 bucket 下的 test 文件夹里，存储为 hello.txt（可更改名字）
                );

                //http://test-1251583197.cosgz.myqcloud.com/test/qwe.jpg
                $obj = new \Lib11\TencentCOS\Cos();
                $result = $obj->upload($args);

                if ($result['success']) {
                    $data['id_img'] = $dst;
                    unlink($id_img);
                } else {
                    $rew['status'] = 0;
                    $rew['data']['strstr'] = '上传图片失败,请联系管理员';
                    $this->ajaxReturn($rew);
                    exit;
                }
                //上传图片到服务器	end
            }
	    	//echo $data['id_img'];
	    	//exit;
	    	//$data['addressee_id'] = $id;
	    	$res['id'] = $id;
	    	$row = $this->client->user_addressee($res);
	    	//$data['user_id'] 	= $row['user_id'];
	    	//$data['true_name'] 	= $row['name'];
	    	//$data['idno']		= $row['cre_num'];
//    		if($row['ueid']){
//	    		$data['id'] = $row['ueid'];
//	    		$data['type_h'] = 'edit';
//	    	}else{
//	    		$data['type_h'] = 'add';
//	    	}
			if(empty($row['front_id_img'])){
				$rew['status'] = 0;
	    		$rew['data']['strstr'] = '身份证正面不能为空';
	    		$this->ajaxReturn($rew);
		    	exit;
			}
	    	if(empty($row['back_id_img'])){
				$rew['status'] = 0;
	    		$rew['data']['strstr'] = '身份证反面不能为空';
	    		$this->ajaxReturn($rew);
		    	exit;
			}

	    	$da['name'] = $row['true_name'];
	    	$da['cre_num'] = $row['idno'];
	    	$da['id_card_front'] = $row['front_id_img'];
	    	$da['id_card_back'] = $row['back_id_img'];

	    	$da['id_card_front_small'] = empty($row['small_front_img']) ?  $row['front_id_img'] : $row['small_front_img'];
	    	$da['id_card_back_small'] = empty($row['small_back_img']) ? $row['back_id_img'] : $row['small_back_img'];
	    	$da['id_img'] = $data['id_img'];
	    	$da['status'] = $status;
	    	$da['id'] = $id;
//	    	if(10 == $status){
//	    		$da['address'] = 10;
//	    	}
	    	$rek = $this->client->cardpic($da);
	    	if($rek){
	    		//$this->success('合成图片保存成功！', U('AdminRecipient/ntreated_documents'));
	    		$rew['status'] = 1;
	    		$rew['data']['strstr'] = '合成图片保存成功！';
	    		$rew['data']['url'] = U('AdminRecipient/ntreated_documents');
	    		$this->ajaxReturn($rew);
		    	exit;
	    	}else{
	    		$rew['status'] = 0;
	    		$rew['data']['strstr'] = '合成图片保存失败！';
	    		//$rew['data']['url'] = U('AdminRecipient/ntreated_documents');
	    		$this->ajaxReturn($rew);
		    	exit;

	    		//$this->error('合成图片保存失败！');
	    	}

	    }

	}


	/**
	 * 收件人身份证信息
	 * Enter description here ...
	 */
	public  function info(){
		$id = I('get.id');
		if(empty($id)){
			$this->error('收件人信息不存在！');
			exit;
		}

		$data['id'] = $id;

		$rek = $this->client->user_addressee($data);

		$this->assign('data', $rek);
		$this->assign('admin_file', ADMIN_FILE);
		$this->assign('wu_file', WU_FILE);
		$this->assign('bucket_url', C('BUCKET_URL'));
		$this->assign('idcard_cos', C('IDCARD_COS'));
		$this->display();
	}

	/**
	 * 身份证添加
	 * Enter description here ...
	 */
	public function processed_add(){

		$this->display();
	}

	/**
	 * 身份证添加处理
	 * Enter description here ...
	 */
	public function process_handle(){

        $status = I('post.status');

        $idcard_status = I('post.idcard_status');


        //保存提交过来的数据
        $true_name      = I('post.true_name');
        if(!empty($true_name)){
            $data['true_name']      = $true_name;
        }
        $idno = I('post.idno');
        if(!empty($idno)){
            $data['idno']      = $idno;
        }
        $tel = I('post.tel');
        if(!empty($tel)){
            $data['tel']      = $tel;
        }else{
            $rew['status'] = 0;
            $rew['data']['strstr'] = '收件人手机号码不能为空！';
            //$rew['data']['url'] = U('AdminRecipient/ntreated_documents');
            $this->ajaxReturn($rew);
            exit;
        }
        $sex = I('post.sex');
        if(!empty($sex)){
            $data['sex']      = $sex;
        }
        $nation = I('post.nation');
        if(!empty($nation)){
            $data['nation']      = $nation;
        }
        $birth = I('post.birth');
        if(!empty($birth)){
            $data['birth']      = $birth;
        }
        $address = I('post.address');
        if(!empty($address)){
            $data['address']      = $address;
        }
        $authority = I('post.authority');
        if(!empty($authority)){
            $data['authority']      = $authority;
        }
        $valid_date_start = I('post.valid_date_start');
        if(!empty($valid_date_start)){
            $data['valid_date_start']      = $valid_date_start;
        }
        $valid_date_end = I('post.valid_date_end');
        if(!empty($valid_date_end)){
            $data['valid_date_end']      = $valid_date_end;
        }
        //$data['status']         = I('post.status');
        //$data['idcard_status'] = I('post.idcard_status');

        //检验身份证正反面是否一致
//        if(!empty($data['authority']) && !empty($data['address'])){
//            $security = strrpos($data['authority'], '公安');
//            $city = substr($data['authority'], 0, $security);
//
//            if(strpos($data['address'], $city) === false){
//                //$this->err_info = '身份证头像面与身份证国徽面不一致 请检查上传是否正确';
//                $rew['status'] = 0;
//                $rew['data']['strstr'] = '身份证头像面与身份证国徽面不一致 请检查上传是否正确';
//                //$rew['data']['url'] = U('AdminRecipient/ntreated_documents');
//                $this->ajaxReturn($rew);
//                exit;
//            };
//        }

        //验证是否失效
        $times = date('Ymd', time());
        if(!empty($data['valid_date_end'])){
            $numb = $data['valid_date_end'] - $times;
            //当前结束时间大于14天为未结束时间  2 为失效
            if($numb <= 14 ){
                $rew['status'] = 0;
                $rew['data']['strstr'] = '身份证信息已失效，不能修改';
                //$rew['data']['url'] = U('AdminRecipient/ntreated_documents');
                $this->ajaxReturn($rew);
                exit;
            }
        }

        //echo $GLOBALS['globalConfig']['UPFILEBASE'];

		$files = C("UPLOAD_DIR").C('UPLOAD_NAME');
//		$name = I('post.name');
//		$cre_num = I('post.cre_num');
//		$status = !empty(I('post.status')) ? I('post.status') : 0;
//		$num_status = !empty(I('post.num_status')) ? I('post.num_status') : 0;
//
		if(!file_exists($files))
		{
		     mkdir($files,0777,true);
		}

			$uploadClass = new \Think\Upload();
			$uploadClass->maxSize=C('UPLOAD_SIZE');
			$uploadClass->exts=C('UPLOAD_TYPE');
			$uploadClass->rootPath=$files;
			$uploadClass->autoSub = false;
			$uploadClass->savePath  = 'processed/';
	      	$info = $uploadClass->upload();

      	if(!$info) {// 上传错误提示错误信息
	        //$this->error($uploadClass->getError());
        	$strs = $uploadClass->getError();
	        $rew['status'] = 0;
    		$rew['data']['strstr'] = $strs;
    		//$rew['data']['url'] = U('AdminRecipient/ntreated_documents');
    		$this->ajaxReturn($rew);
	    	exit;

	       //$this->error('上传失败');
	    }else{
            //验证正反面是否上传
            if($_FILES['back_id_img']['size'] <= 0 ||  $_FILES['front_id_img']['size'] <= 0){
                $rew['status'] = 0;
                $rew['data']['strstr'] = '请检验身份证正反面是否已上传';
                //$rew['data']['url'] = U('AdminRecipient/ntreated_documents');
                $this->ajaxReturn($rew);
                exit;
            }

            //生成缩略图     start
            $image = new \Think\Image();
            $app1 = str_replace($GLOBALS['globalConfig']['UPFILEBASE'], '', C("UPLOAD_DIR"));
            // 上传成功
            if (isset($_FILES['id_imgs']['size'])){

                $data['id_img'] = $app1 . C('UPLOAD_NAME') . $info['id_imgs']['savepath'].$info['id_imgs']['savename'];
            }
            if(isset($_FILES['front_id_img']['size'])){
                $front = C('UPLOAD_NAME') . $info['front_id_img']['savepath'].$info['front_id_img']['savename'];
                $data['front_id_img'] 	= $app1 . $front;
                //缩略图
                $id_card_front 			= C("UPLOAD_DIR") . $front;
                $id_card_front_small 	= C("UPLOAD_DIR") . C('UPLOAD_NAME') . $info['front_id_img']['savepath']. 'small_'. $info['front_id_img']['savename'];
                $image->open($id_card_front);//将图片裁剪为400x400并保存为corp.jpg
                $image->thumb(150, 90, 6)->save($id_card_front_small);
                //正面缩略图图片
                //$id_img = C("UPLOAD_DIR") . $data['id_img'];
                $card_front_small = str_replace(C("UPLOAD_DIR"), '', $id_card_front_small);
                $data['small_front_img'] = $app1 . $card_front_small;

            }
            if (isset($_FILES['back_id_img']['size'])){
                $back = C('UPLOAD_NAME') . $info['back_id_img']['savepath'].$info['back_id_img']['savename'];
                $data['back_id_img']   	= $app1 . $back;


                //反面缩略图图片
                $id_card_back 			= C("UPLOAD_DIR") . $back;
                $id_card_back_small 	= C("UPLOAD_DIR") . C('UPLOAD_NAME') . $info['back_id_img']['savepath']. 'small_'. $info['back_id_img']['savename'];

                $image->open($id_card_back);//将图片裁剪为400x400并保存为corp.jpg
                $image->thumb(150, 90, 6)->save($id_card_back_small);
                $card_back_small = str_replace(C("UPLOAD_DIR"), '', $id_card_back_small);
                //生成缩略图     end
                $data['small_back_img'] = $app1 . $card_back_small;
            }


            $user_id = 0;
            $user_name = I('post.user_name');
            if(!empty($user_name)){
                $user_where['username'] = $user_name;
                $user = M('user_list')->field('id')->where($user_where)->find();
                if(!empty($user)){
                    $user_id = $user['id'];
                }
            }
            $orderno = I('post.orderno');
            if(!empty($orderno)){
                //order_no   user_id
                $where['order_no'] = $orderno;
                $order = M('tran_ulist')->field('id, order_no, id_img_status, id_no_status, idno,  user_id, front_id_img, back_id_img, small_front_img, small_back_img')->where($where)->find();
                if(empty($order)){
                    $rew['status'] = 0;
                    $rew['data']['strstr'] = '订单信息不存在！';
                    //$rew['data']['url'] = U('AdminRecipient/ntreated_documents');
                    $this->ajaxReturn($rew);
                    exit;
                }
                $where['id'] = $order['id'];
                $user_id = $order['user_id'];
                if($order['id_no_status'] == 200){
                    $rew['status'] = 0;
                    $rew['data']['strstr'] = '订单身份证号无需填写！';
                    //$rew['data']['url'] = U('AdminRecipient/ntreated_documents');
                    $this->ajaxReturn($rew);
                    exit;
                }
//                if($order['id_img_status'] == 200){
//                    $rew['status'] = 0;
//                    $rew['data']['strstr'] = '订单身份证正反面无需填写！';
//                    //$rew['data']['url'] = U('AdminRecipient/ntreated_documents');
//                    $this->ajaxReturn($rew);
//                    exit;
//                }



                if(I('post.more') == 2){
                    if($order['id_no_status'] == 0){
                        $order_data['idno'] = $data['idno'];
                        $order_data['id_no_status'] = 100; //身份证号已上传
                    }
                    if($order['id_img_status'] == 0){
                        $order_data['id_img_status'] = 100; //身份证号已上传
                    }
                    $order_data['front_id_img']     = $data['front_id_img'];
                    $order_data['small_front_img']  = $data['small_front_img'];
                    $order_data['back_id_img']      = $data['back_id_img'];
                    $order_data['small_back_img']   = $data['small_back_img'];

                    //写入操作记录    start
                    $order_log_file = C("UPLOAD_DIR") . C('ORDER_LOG') . date('Y-m-d', time());
                    if(!file_exists($order_log_file))
                    {
                        mkdir($order_log_file,0777,true);
                    }

                    $order_file_data['order'] = $order;
                    $order_file_data['order_save'] = $order_data;
                    $order_file_data['order_where'] = $where;
                    $order_file_data['order_adid'] = session('admin')['adid'];
                    if(empty($order_file_data['order_adid'])){
                        $rew['status'] = 0;
                        $rew['data']['strstr'] = '非法账号，不能修改！';
                        //$rew['data']['url'] = U('AdminRecipient/ntreated_documents');
                        $this->ajaxReturn($rew);
                        exit;
                    }
                    $save_text2 = print_r($order_file_data, true);
                    $savefile = $order_log_file .'/'.$order['order_no'].'.txt';
                    file_put_contents($savefile, $save_text2, FILE_APPEND);
                    //写入操作记录    end

                    M('tran_ulist')->where($where)->save($order_data);
                }else{
                    if(empty($order['front_id_img']) && empty($order['back_id_img'])){
                        if($order['id_no_status'] == 0){
                            $order_data['idno'] = $data['idno'];
                            $order_data['id_no_status'] = 100; //身份证号已上传
                        }
                        if($order['id_img_status'] == 0){
                            $order_data['id_img_status'] = 100; //身份证号已上传
                        }
                        $order_data['front_id_img']     = $data['front_id_img'];
                        $order_data['small_front_img']  = $data['small_front_img'];
                        $order_data['back_id_img']      = $data['back_id_img'];
                        $order_data['small_back_img']   = $data['small_back_img'];

                        //写入操作记录 start
                        $order_log_file = C("UPLOAD_DIR") . C('ORDER_LOG') . date('Y-m-d', time());
                        if(!file_exists($order_log_file))
                        {
                            mkdir($order_log_file .'/'.$order['order_no'].'.txt',0777,true);
                        }

                        $order_file_data['order'] = $order;
                        $order_file_data['order_save'] = $order_data;
                        $order_file_data['order_where'] = $where;
                        $order_file_data['order_adid'] = session('admin')['adid'];
                        if(empty($order_file_data['order_adid'])){
                            $rew['status'] = 0;
                            $rew['data']['strstr'] = '非法账号，不能修改！';
                            //$rew['data']['url'] = U('AdminRecipient/ntreated_documents');
                            $this->ajaxReturn($rew);
                            exit;
                        }
                        $save_text2 = print_r($order_file_data, true);
                        $savefile = $order_log_file .'/'.$order['order_no'].'.txt';
                        file_put_contents($savefile, $save_text2, FILE_APPEND);
                        //写入操作记录 end
                        M('tran_ulist')->where($where)->save($order_data);
                    }
                }


            }
            $data['user_id'] = $user_id;

	    	$res = $this->client->process($data);

	    	if($res){
		        $rew['status'] = 1;
	    		$rew['data']['strstr'] = '添加成功';
	    		//$rew['data']['url'] = U('AdminRecipient/ntreated_documents');
	    		$this->ajaxReturn($rew);
		    	exit;
	    	}else{
		        $rew['status'] = 0;
	    		$rew['data']['strstr'] = '添加失败';
	    		//$rew['data']['url'] = U('AdminRecipient/ntreated_documents');
	    		$this->ajaxReturn($rew);
		    	exit;

	    	}


	    }

	}

	/**
	 * 已处理收件人证件
	 * Enter description here ...
	 */
	public function info_processed(){
		$id = I('get.id');
		if(empty($id)){
			$this->error('收件人信息不存在！');
			exit;
		}

		$data['id'] = $id;
		$rek = $this->client->info_processed($data);

		$this->assign('data', $rek);
		$this->assign('admin_file', ADMIN_FILE);
		$this->assign('wu_file', WU_FILE);
		$this->assign('bucket_url', C('BUCKET_URL'));
		$this->assign('');
		$this->display();
	}
	public function audits()
	{
		$id 	= I('post.id');
		$status = I('post.status');
		$idcard_status = I('post.idcard_status');
		if(empty($id)){
			//$this->error('收件人信息不存在！');
			$rew['status'] = 0;
			$rew['data']['strstr'] = '收件人信息不存在！';
			//$rew['data']['url'] = U('AdminRecipient/ntreated_documents');
			$this->ajaxReturn($rew);
			exit;
		}

		//保存提交过来的数据
		$true_name      = I('post.true_name');
		if(!empty($true_name)){
			$data['true_name']      = $true_name;
		}
		$idno = I('post.idno');
		if(!empty($idno)){
			$data['idno']      = $idno;
		}
		$tel = I('post.tel');
		if(!empty($tel)){
			$data['tel']      = $tel;
		}
		$sex = I('post.sex');
		if(!empty($sex)){
			$data['sex']      = $sex;
		}
		$nation = I('post.nation');
		if(!empty($nation)){
			$data['nation']      = $nation;
		}
		$birth = I('post.birth');
		if(!empty($birth)){
			$data['birth']      = $birth;
		}
		$address = I('post.address');
		if(!empty($address)){
			$data['address']      = $address;
		}
		$authority = I('post.authority');
		if(!empty($authority)){
			$data['authority']      = $authority;
		}
		$valid_date_start = I('post.valid_date_start');
		if(!empty($valid_date_start)){
			$data['valid_date_start']      = $valid_date_start;
		}
		$valid_date_end = I('post.valid_date_end');
		if(!empty($valid_date_end)){
			$data['valid_date_end']      = $valid_date_end;
		}
		$data['status']         = I('post.status');
		$data['idcard_status'] = I('post.idcard_status');


		//检验身份证正反面是否一致
		if(!empty($data['authority']) && !empty($data['address'])) {
//			$security = stripos($data['authority'], '公安');
//			$city = substr($data['authority'], 0, $security);
//			//检验身份证正反面是否一致
//			if (!empty($data['authority']) && !empty($data['address'])) {
//				$security = strrpos($data['authority'], '公安');
//				$city = substr($data['authority'], 0, $security);
//
//				if (strpos($data['address'], $city) === false) {
//					//$this->err_info = '身份证头像面与身份证国徽面不一致 请检查上传是否正确';
//					$rew['status'] = 0;
//					$rew['data']['strstr'] = '身份证头像面与身份证国徽面不一致 请检查上传是否正确';
//					//$rew['data']['url'] = U('AdminRecipient/ntreated_documents');
//					$this->ajaxReturn($rew);
//					exit;
//				};
//			}

			//验证是否失效
			$times = date('Ymd', time());
			if (!empty($data['valid_date_end'])) {
				$numb = $data['valid_date_end'] - $times;
				//当前结束时间大于14天为未结束时间  2 为失效
				if ($numb <= 14) {
					$rew['status'] = 0;
					$rew['data']['strstr'] = '身份证信息已失效，不能修改';
					//$rew['data']['url'] = U('AdminRecipient/ntreated_documents');
					$this->ajaxReturn($rew);
					exit;
				}
			}

			//验证当前身份证信息是否已审核未失效
			$where['id'] = $id;
			$extra_info = M('user_extra_info')->field('status, idcard_status, valid_date_end')->where($where)->find();
			$extra_numb = $extra_info['valid_date_end'] - $times;
			if ($extra_numb > 14) {
				$date_end = true;
			} else {
				$date_end = false;
			}
			if ($extra_info['status'] == 10 && $extra_info['idcard_status'] == 1 && !empty($extra_info['true_name']) && !empty($extra_info['idno']) && !empty($extra_info['tel']) && $date_end) {
				$rew['status'] = 0;
				$rew['data']['strstr'] = '身份证信息已审核通过，不能修改';
				//$rew['data']['url'] = U('AdminRecipient/ntreated_documents');
				$this->ajaxReturn($rew);
				exit;
			}

			if (!empty($_FILES['id_imgs']['size']) || !empty($_FILES['back_id_img']['size']) || !empty($_FILES['front_id_img']['size'])) {

				$files = C("UPLOAD_DIR") . C('UPLOAD_NAME');

				if (!file_exists($files)) {
					mkdir($files, 0777, true);
				}
				$uploadClass = new \Think\Upload();
				$uploadClass->maxSize = C('UPLOAD_SIZE');
				$uploadClass->exts = C('UPLOAD_TYPE');
				$uploadClass->rootPath = $files;
				$info = $uploadClass->upload();

				if (!$info) {// 上传错误提示错误信息
					$rew['status'] = 0;
					$rew['data']['strstr'] = $uploadClass->getError();
					//$rew['data']['url'] = U('AdminRecipient/ntreated_documents');
					$this->ajaxReturn($rew);
					exit;
					//$this->error($uploadClass->getError());
					//$this->error('上传失败');
				} else {
					//添加语言20180608  start
					//$lang = str_replace('\\', '/', dirname(dirname(dirname(__FILE__))));
					//$lang =  $lang .'/WebUser/Lang/zh-cn.php';
					//$mkl = require_once($lang);

					//生成缩略图     start
					$image = new \Think\Image();

					$app1 = str_replace($GLOBALS['globalConfig']['UPFILEBASE'], '', C("UPLOAD_DIR"));
					//$obj = new \Lib10\Idcardali\AliIdcard();
					// 上传成功
					if (isset($_FILES['id_imgs']['size'])) {
						$data['id_img'] = $app1 . C('UPLOAD_NAME') . $info['id_imgs']['savepath'] . $info['id_imgs']['savename'];
					}
					if (isset($_FILES['front_id_img']['size'])) {
						$front = C('UPLOAD_NAME') . $info['front_id_img']['savepath'] . $info['front_id_img']['savename'];
						$data['front_id_img'] = $app1 . $front;
//                    $national = C("UPLOAD_DIR") . $data['front_id_img'];
//                    $result = $obj->national_emblem($national);
//                    if(!$result){
//                        $rew['status'] = 0;
//                        $rew['data']['strstr'] = $mkl[$obj->getError()];
//                        $this->ajaxReturn($rew);
//                        exit;
//                    }
//                    $data['authority']            = $result['authority'];
//                    $data['valid_date_start']    = $result['valid_date_start'];
//                    $data['valid_date_end']      = $result['valid_date_end'];
						//缩略图
						$id_card_front = C("UPLOAD_DIR") . $front;
						$id_card_front_small = C("UPLOAD_DIR") . C('UPLOAD_NAME') . $info['front_id_img']['savepath'] . 'small_' . $info['front_id_img']['savename'];
						$image->open($id_card_front);//将图片裁剪为400x400并保存为corp.jpg
						$image->thumb(150, 90, 6)->save($id_card_front_small);
						//正面缩略图图片
						//$id_img = C("UPLOAD_DIR") . $data['id_img'];
						$card_front_small = str_replace(C("UPLOAD_DIR"), '', $id_card_front_small);
						$data['small_front_img'] = $app1 . $card_front_small;

					}
					if (isset($_FILES['back_id_img']['size'])) {
						$back = C('UPLOAD_NAME') . $info['back_id_img']['savepath'] . $info['back_id_img']['savename'];
						$data['back_id_img'] = $app1 . $back;
//                    $photo = C("UPLOAD_DIR") . $data['back_id_img'];
//                    $result = $obj->photo($photo);
//                    if(!$result){
//                        $rew['status'] = 0;
//                        $rew['data']['strstr'] = $mkl[$obj->getError()];
//                        $this->ajaxReturn($rew);
//                        exit;
//                    }
//
//                    $data['true_name']  = $result['name'];
//                    $data['sex']        = $result['sex'];
//                    $data['nation']     = $result['nation'];
//                    $data['birth']      = $result['birth'];
//                    $data['address']    = $result['address'];
//                    $data['idno']       = $result['idcard'];

						//反面缩略图图片
						$id_card_back = C("UPLOAD_DIR") . $back;
						$id_card_back_small = C("UPLOAD_DIR") . C('UPLOAD_NAME') . $info['back_id_img']['savepath'] . 'small_' . $info['back_id_img']['savename'];

						$image->open($id_card_back);//将图片裁剪为400x400并保存为corp.jpg
						$image->thumb(150, 90, 6)->save($id_card_back_small);
						$card_back_small = str_replace(C("UPLOAD_DIR"), '', $id_card_back_small);
						//生成缩略图     end
						$data['small_back_img'] = $app1 . $back;
					}
					//$data['front_id_img'] 	= C('UPLOAD_NAME') . $info['front_id_img']['savepath'].$info['front_id_img']['savename'];
					//$data['back_id_img']   	= C('UPLOAD_NAME') . $info['back_id_img']['savepath'].$info['back_id_img']['savename'];


					//复制正反面图片到前端文件  start
//                $initialPath_one = $data['id_card_front'];
//                $initialPath_two = $data['id_card_back'];
//
//                $targetPath_one  = $data['id_card_front'];
//                $targetPath_one  = WU_ABS_FILE . $targetPath_one;
//                $targetPath_two  = $data['id_card_back'];
//                $targetPath_two  = WU_ABS_FILE . $targetPath_two;
//
//                $initialPath_one =  C("UPLOAD_DIR")  . $initialPath_one;
//                $initialPath_two =  C("UPLOAD_DIR")  . $initialPath_two;
//                copy($initialPath_one, $targetPath_one);
//                copy($initialPath_two, $targetPath_two);
//                //复制正反面图片到前端文件  end
//
//                //删除重复图片
//                unlink($initialPath_one);
//                unlink($initialPath_two);

//                if(C('IDCARD_COS')) {
//                    //上传图片到服务器	start
//                    $id_img = C("UPLOAD_DIR") . $data['id_img'];
//                    $dst = '/' . C('BUCKET_LIST') . $data['id_img'];
//                    $args = array(
//                        'src' => $id_img,         // 待上传的文件，必须是文件在服务器上的路径（可以使用js上传到服务器的临时文件）
//                        'dst' => $dst,     // 上传到配置文件中的 bucket 下的 test 文件夹里，存储为 hello.txt（可更改名字）
//                    );
//                    //http://test-1251583197.cosgz.myqcloud.com/test/qwe.jpg
//                    $obj = new \Lib11\TencentCOS\Cos();
//                    $result = $obj->upload($args);
//                    if ($result['success']) {
//                        $data['id_img'] = $dst;
//                        unlink($id_img);
//                    } else {
//                        $rew['status'] = 0;
//                        $rew['data']['strstr'] = '上传图片失败,请联系管理员';
//                        $this->ajaxReturn($rew);
//                        exit;
//                    }
//                    //上传图片到服务器	end
//                }

//		    	//$data['addressee_id'] = $id;
//		    	$res['id'] = $id;
//		    	$row = $this->client->user_addressee($res);
//
//				if(empty($row['front_id_img'])){
//					$rew['status'] = 0;
//		    		$rew['data']['strstr'] = '身份证正面不能为空';
//		    		$this->ajaxReturn($rew);
//			    	exit;
//				}
//		    	if(empty($row['back_id_img'])){
//					$rew['status'] = 0;
//		    		$rew['data']['strstr'] = '身份证反面不能为空';
//		    		$this->ajaxReturn($rew);
//			    	exit;
//				}
//
//		    	$da['name'] = $row['true_name'];
//		    	$da['cre_num'] = $row['idno'];
//		    	$da['id_card_front'] = $row['front_id_img'];
//		    	$da['id_card_back'] = $row['back_id_img'];
//
//		    	$da['id_card_front_small'] = empty($row['small_front_img']) ?  $row['front_id_img'] : $row['small_front_img'];
//		    	$da['id_card_back_small'] = empty($row['small_back_img']) ? $row['back_id_img'] : $row['small_back_img'];
//		    	$da['id_img'] = $data['id_img'];
//                $da['status'] = strlen($status) > 0 ?  $status : $row['status'];
//                $da['id'] = $id;
//		    	$da['idcard_status'] = $idcard_status;
////		    	if(10 == $status){
////		    		$da['address'] = 10;
////		    	}
//                //验证是否失效
//                $times = date('Ymd', time());
//                $numb = $row['valid_date_end'] - $times;
//                //当前结束时间大于14天为未结束时间  2 为失效
//                if($numb > 14 && $idcard_status == 2){
//                    $rew['status'] = 0;
//                    $rew['data']['strstr'] = '身份证信息未失效，不能修改';
//                    //$rew['data']['url'] = U('AdminRecipient/ntreated_documents');
//                    $this->ajaxReturn($rew);
//                    exit;
//                }

					//$rek = $this->client->cardpic($da);
					$wh['id'] = $id;
					$rek = $this->client->extra_info_edit($wh, $data);
					if ($rek) {
						//如果审核不通过  就修改订单身份证图片上传状态  发送短信
						if ($data['status'] == 2) {
							//修改订单身份证图片上传状态
							if (!empty($extra_info['user_id']) && !empty($extra_info['true_name']) && !empty($extra_info['idno']) && !empty($extra_info['tel'])) {
								$where_order['user_id'] = $extra_info['user_id'];
								$where_order['receiver'] = $extra_info['true_name'];
								$where_order['idno'] = $extra_info['idno'];
								$where_order['reTel'] = $extra_info['tel'];
								$order_data['lib_idcard'] = $extra_info['id'];
								$order_data['id_img_status'] = 0;

								$order = M('tran_ulist')->where($where_order)->save($order_data);

							}
						}


						//$this->success('合成图片保存成功！', U('AdminRecipient/ntreated_documents'));
						$rew['status'] = 1;
						$rew['data']['strstr'] = '修改成功';
						//$rew['data']['url'] = U('AdminRecipient/ntreated_documents');
						$this->ajaxReturn($rew);
						exit;
					} else {
						$rew['status'] = 0;
						$rew['data']['strstr'] = '修改失败';
						//$rew['data']['url'] = U('AdminRecipient/ntreated_documents');
						$this->ajaxReturn($rew);
						exit;
					}
				}
			} else {

				//修改收件人信息状态
				$rew['id'] = $id;
				$rew['status'] = $status;
				//检验是否上传合成图片
				$einfo['id'] = $id;
				$extra_info = $this->client->extra_info($einfo);
				//y验证身份证是否存在，存在不能修改
				$wh['true_name'] = $extra_info['true_name'];
				$wh['idno'] = $extra_info['idno'];
				$wh['tel'] = $extra_info['tel'];
				$wh['status'] = 10;
				$wh['idcard_status'] = 1;
				$wh['id'] = array('neq', $id);
				$existence = $this->client->extra_info($wh);
				if (!empty($existence)) {
					$rew['status'] = 0;
					$rew['data']['strstr'] = '该身份证信息已存在，不能修改！';
					//$rew['data']['url'] = U('AdminRecipient/ntreated_documents');
					$this->ajaxReturn($rew);
					exit;
				}
				//检验是否能够将状态改成已审核状态（判断订单是否上传合成图片）
				if (empty($extra_info['id_img']) && 10 == $status) {
					$rew['status'] = 0;
					$rew['data']['strstr'] = '请上传合成图片！';
					//$rew['data']['url'] = U('AdminRecipient/ntreated_documents');
					$this->ajaxReturn($rew);
					exit;
				}
				//$ras = $this->client->audit($rew);

				if (!empty($extra_info['id_img'])) {

					//$da['name'] = $extra_info['true_name'];
					//$da['cre_num'] = $extra_info['idno'];
					if (empty($extra_info['front_id_img'])) {
						$rew['status'] = 0;
						$rew['data']['strstr'] = '身份证正面不能为空';
						$this->ajaxReturn($rew);
						exit;
					}
					if (empty($extra_info['back_id_img'])) {
						$rew['status'] = 0;
						$rew['data']['strstr'] = '身份证反面不能为空';
						$this->ajaxReturn($rew);
						exit;
					}
					//$da['id_card_front'] = $extra_info['front_id_img'];
					//$da['id_card_back'] = $extra_info['back_id_img'];

					$da['id_card_front_small'] = empty($extra_info['small_front_img']) ? $extra_info['front_id_img'] : $extra_info['small_front_img'];
					$da['id_card_back_small'] = empty($extra_info['small_back_img']) ? $extra_info['back_id_img'] : $extra_info['small_back_img'];
					//$da['id_img'] = $extra_info['id_img'];
					$da['status'] = strlen($status) > 0 ? $status : $extra_info['status'];
					$da['id'] = $id;
					//$da['status'] = $status;
					//验证是否失效
					$times = date('Ymd', time());
					$numb = $extra_info['valid_date_end'] - $times;
					//当前结束时间大于14天为未结束时间
					if ($numb > 14 && $idcard_status == 2) {
						$rew['status'] = 0;
						$rew['data']['strstr'] = '身份证信息未失效，不能修改';
						//$rew['data']['url'] = U('AdminRecipient/ntreated_documents');
						$this->ajaxReturn($rew);
						exit;
					}
					$da['idcard_status'] = $idcard_status;
					//if(10 == $status){
					//	$da['address'] = 10;
					//}
					$data['id'] = $da['id'];
					$data['status'] = $da['status'];
					$data['idcard_status'] = $da['idcard_status'];
					$data['small_front_img'] = $da['id_card_front_small'];
					$data['small_back_img'] = $da['id_card_back_small'];

					//$data['id'] = $data['id'];
					//$data['id'] = $data['id'];
					//$data['id'] = $data['id'];

					//$rek = $this->client->cardpic($da);

					$rek = $this->client->card_edit($data);
					if ($rek) {
						//如果审核不通过  就修改订单身份证图片上传状态  发送短信
						if ($data['status'] == 2) {
							//修改订单身份证图片上传状态
							if (!empty($extra_info['user_id']) && !empty($extra_info['true_name']) && !empty($extra_info['idno']) && !empty($extra_info['tel'])) {
								$where_order['user_id'] = $extra_info['user_id'];
								$where_order['receiver'] = $extra_info['true_name'];
								$where_order['idno'] = $extra_info['idno'];
								$where_order['reTel'] = $extra_info['tel'];
								$order_data['lib_idcard'] = $extra_info['id'];
								$order_data['id_img_status'] = 0;

								M('tran_ulist')->where($where_order)->save($order_data);

							}

						}


						//$this->success('合成图片保存成功！', U('AdminRecipient/ntreated_documents'));
						$rew['status'] = 1;
						$rew['data']['strstr'] = '修改成功';
						//$rew['data']['url'] = U('AdminRecipient/ntreated_documents');
						$this->ajaxReturn($rew);
						exit;
					} else {
						$rew['status'] = 0;
						$rew['data']['strstr'] = '修改失败';
						//$rew['data']['url'] = U('AdminRecipient/ntreated_documents');
						$this->ajaxReturn($rew);
						exit;
					}
				} else {
					$rew['id'] = $id;
					$rew['status'] = strlen($status) > 0 ? $status : $extra_info['status'];
					if ($rew['status'] == 10) {
						$rew['status'] = 0;
						$rew['data']['strstr'] = '请上传合成图';
						//$rew['data']['url'] = U('AdminRecipient/ntreated_documents');
						$this->ajaxReturn($rew);
						exit;
					}
					$data['id'] = $rew['id'];
					$data['status'] = $rew['status'];

					//$ras = $this->client->audit($rew);
					$ras = $this->client->card_edit($data);
					if ($ras) {
						//如果审核不通过  就修改订单身份证图片上传状态  发送短信
						if ($data['status'] == 2) {
							//修改订单身份证图片上传状态
							if (!empty($extra_info['user_id']) && !empty($extra_info['true_name']) && !empty($extra_info['idno']) && !empty($extra_info['tel'])) {
								$where_order['user_id'] = $extra_info['user_id'];
								$where_order['receiver'] = $extra_info['true_name'];
								$where_order['idno'] = $extra_info['idno'];
								$where_order['reTel'] = $extra_info['tel'];
								$order_data['lib_idcard'] = $extra_info['id'];
								$order_data['id_img_status'] = 0;

								$order = M('tran_ulist')->where($where_order)->save($order_data);

							}
						}

						//if(empty($extra_info['id_img'])){
						//$this->success('合成图片保存成功！', U('AdminRecipient/ntreated_documents'));
						$rew['status'] = 1;
						$rew['data']['strstr'] = '修改成功！';
						//$rew['data']['url'] = U('AdminRecipient/ntreated_documents');
						$this->ajaxReturn($rew);
						exit;
					} else {
						$rew['status'] = 0;
						$rew['data']['strstr'] = '修改失败';
						//$rew['data']['url'] = U('AdminRecipient/ntreated_documents');
						$this->ajaxReturn($rew);
						exit;
					}
				}

			}

		}
	}
	/**
	 * 审核处理
	 * Enter description here ...
	 */
	public function audit(){
		$id 	= I('post.id');
		$status = I('post.status');
		$idcard_status = I('post.idcard_status');
		if(empty($id)){
			$rew['status'] = 0;
    		$rew['data']['strstr'] = '收件人信息不存在！';
    		$this->ajaxReturn($rew);
	    	exit;
		}

		//保存提交过来的数据
        $true_name      = I('post.true_name');
        if(!empty($true_name)){
            $data['true_name']      = $true_name;
        }
        $idno = I('post.idno');
        if(!empty($idno)){
            $data['idno']      = $idno;
        }
        $tel = I('post.tel');
        if(!empty($tel)){
            $data['tel']      = $tel;
        }
        $sex = I('post.sex');
        if(!empty($sex)){
            $data['sex']      = $sex;
        }
        $nation = I('post.nation');
        if(!empty($nation)){
            $data['nation']      = $nation;
        }
        $birth = I('post.birth');
        if(!empty($birth)){
            $data['birth']      = $birth;
        }
        $address = I('post.address');
        if(!empty($address)){
            $data['address']      = $address;
        }
        $authority = I('post.authority');
        if(!empty($authority)){
            $data['authority']      = $authority;
        }
        $valid_date_start = I('post.valid_date_start');
        if(!empty($valid_date_start)){
            $data['valid_date_start']      = $valid_date_start;
        }
        $valid_date_end = I('post.valid_date_end');
        if(!empty($valid_date_end)){
            $data['valid_date_end']      = $valid_date_end;
        }
        $data['status']         = I('post.status');
        $data['idcard_status'] = I('post.idcard_status');

        //检验身份证正反面是否一致
//        if(!empty($data['authority']) && !empty($data['address'])){
//            $security = strrpos($data['authority'], '公安');
//            $city = substr($data['authority'], 0, $security);
//
//            if(strpos($data['address'], $city) === false){
//                //$this->err_info = '身份证头像面与身份证国徽面不一致 请检查上传是否正确';
//                $rew['status'] = 0;
//                $rew['data']['strstr'] = '身份证头像面与身份证国徽面不一致 请检查上传是否正确';
//                $this->ajaxReturn($rew);
//                exit;
//            };
//        }

        //验证是否失效
//        $times = date('Ymd', time());
//        if(!empty($data['valid_date_end'])){
//            $numb = $data['valid_date_end'] - $times;
//            //当前结束时间大于14天为未结束时间  2 为失效
//            if($numb <= 14 ){
//                $rew['status'] = 0;
//                $rew['data']['strstr'] = '身份证信息已失效，不能修改';
//                $this->ajaxReturn($rew);
//                exit;
//            }
//        }

        //验证当前身份证信息是否已审核未失效
        $where['id'] = $id;
        $extra_info = M('user_extra_info')->where($where)->find();

        if($extra_info['status'] == 10 && $extra_info['idcard_status'] == 1 && !empty($extra_info['true_name']) && !empty($extra_info['idno']) && !empty($extra_info['tel'])){
            $rew['status'] = 0;
            $rew['data']['strstr'] = '身份证信息已审核通过，不能修改';
            $this->ajaxReturn($rew);
            exit;
        }

        if (!empty($_FILES['back_id_img']['size'])  || !empty($_FILES['front_id_img']['size'])) {
			$files = C("UPLOAD_DIR") . C('UPLOAD_NAME');

			if (!file_exists($files)) {
				mkdir($files, 0777, true);
			}
			$uploadClass = new \Think\Upload();
			$uploadClass->maxSize = C('UPLOAD_SIZE');
			$uploadClass->exts = C('UPLOAD_TYPE');
			$uploadClass->rootPath = $files;
			$info = $uploadClass->upload();

			if (!$info) {// 上传错误提示错误信息
				$rew['status'] = 0;
				$rew['data']['strstr'] = $uploadClass->getError();
				$this->ajaxReturn($rew);
				exit;
			} else {

				//生成缩略图     start
				$image = new \Think\Image();

				$app1 = str_replace($GLOBALS['globalConfig']['UPFILEBASE'], '', C("UPLOAD_DIR"));
				// 上传成功
				if (isset($_FILES['id_imgs']['size'])) {
					$data['id_img'] = $app1 . C('UPLOAD_NAME') . $info['id_imgs']['savepath'] . $info['id_imgs']['savename'];
				}
				if (isset($_FILES['front_id_img']['size'])) {
					$front = C('UPLOAD_NAME') . $info['front_id_img']['savepath'] . $info['front_id_img']['savename'];
					$data['front_id_img'] = $app1 . $front;

					//缩略图
					$id_card_front = C("UPLOAD_DIR") . $front;
					$id_card_front_small = C("UPLOAD_DIR") . C('UPLOAD_NAME') . $info['front_id_img']['savepath'] . 'small_' . $info['front_id_img']['savename'];
					$image->open($id_card_front);//将图片裁剪为400x400并保存为corp.jpg
					$image->thumb(150, 90, 6)->save($id_card_front_small);
					//正面缩略图图片
					$card_front_small = str_replace(C("UPLOAD_DIR"), '', $id_card_front_small);
					$data['small_front_img'] = $app1 . $card_front_small;

				}
				if (isset($_FILES['back_id_img']['size'])) {
					$back = C('UPLOAD_NAME') . $info['back_id_img']['savepath'] . $info['back_id_img']['savename'];
					$data['back_id_img'] = $app1 . $back;


					//反面缩略图图片
					$id_card_back = C("UPLOAD_DIR") . $back;
					$id_card_back_small = C("UPLOAD_DIR") . C('UPLOAD_NAME') . $info['back_id_img']['savepath'] . 'small_' . $info['back_id_img']['savename'];

					$image->open($id_card_back);//将图片裁剪为400x400并保存为corp.jpg
					$image->thumb(150, 90, 6)->save($id_card_back_small);
					$card_back_small = str_replace(C("UPLOAD_DIR"), '', $id_card_back_small);
					//生成缩略图     end
					$data['small_back_img'] = $app1 . $back;
				}
				$wh['id'] = $id;
				$rek = $this->client->extra_info_edit($wh, $data);
				if ($rek) {
					//如果审核不通过  就修改订单身份证图片上传状态  发送短信
				$this->inspect($id,$data['status']);

		    	}else{
		    		$rew['status'] = 0;
		    		$rew['data']['strstr'] = '修改失败';
		    		$this->ajaxReturn($rew);
			    	exit;
		    	}
		    }
		}else{

		    //修改收件人信息状态
    		$rew['id'] = $id;
    		$rew['status'] = $status;
    		//检验是否上传合成图片
    		$einfo['id'] = $id;
    		$extra_info = $this->client->extra_info($einfo);
            //y验证身份证是否存在，存在不能修改
            $wh['true_name']        = $extra_info['true_name'];
            $wh['idno']              = $extra_info['idno'];
            $wh['tel']               = $extra_info['tel'];
            $wh['status']            = 10;
            $wh['idcard_status']    = 1;
            $wh['id']                 = array('neq', $id);
            $existence = $this->client->extra_info($wh);
            if(!empty($existence)){
                $rew['status'] = 0;
                $rew['data']['strstr'] = '该身份证信息已存在，不能修改！';
                $this->ajaxReturn($rew);
                exit;
            }
            //检验是否能够将状态改成已审核状态（判断订单是否上传合成图片）
    		if(empty($extra_info['id_img']) && 10 == $status){
		    		$rew['status'] = 0;
		    		$rew['data']['strstr'] = '请上传合成图片！';
		    		$this->ajaxReturn($rew);
			    	exit;
    		}

    		if (!empty($extra_info['id_img'])){
//    			if(empty($extra_info['front_id_img'])){
//					$rew['status'] = 0;
//		    		$rew['data']['strstr'] = '身份证正面不能为空';
//		    		$this->ajaxReturn($rew);
//			    	exit;
//				}
//		    	if(empty($extra_info['back_id_img'])){
//					$rew['status'] = 0;
//		    		$rew['data']['strstr'] = '身份证反面不能为空';
//		    		$this->ajaxReturn($rew);
//			    	exit;
//				}
		    	$da['id_card_front_small'] = empty($extra_info['small_front_img']) ?  $extra_info['front_id_img'] : $extra_info['small_front_img'];
		    	$da['id_card_back_small'] = empty($extra_info['small_back_img']) ? $extra_info['back_id_img'] : $extra_info['small_back_img'];
		    	$da['status'] = strlen($status) > 0 ?  $status : $extra_info['status'];
                $da['id'] = $id;
                //验证是否失效
                $times = date('Ymd', time());
                $numb = $extra_info['valid_date_end'] - $times;
                //当前结束时间大于14天为未结束时间
                if($numb > 14 && $idcard_status == 2){
                    $rew['status'] = 0;
                    $rew['data']['strstr'] = '身份证信息未失效，不能修改';
                    $this->ajaxReturn($rew);
                    exit;
                }
                $da['idcard_status'] = $idcard_status;
                $data['id'] = $da['id'];
                $data['status'] = $da['status'];
                $data['idcard_status'] = $da['idcard_status'];
                $data['small_front_img'] = $da['id_card_front_small'];
                $data['small_back_img'] = $da['id_card_back_small'];


                $rek = $this->client->card_edit($data);
		    	if($rek){

				$this->inspect($id,$data['status']);
				//  return $data;
		    	}else{
		    		$rew['status'] = 0;
		    		$rew['data']['strstr'] = '修改失败';
		    		$this->ajaxReturn($rew);
			    	exit;
		    	}
    		}else{
                $rew['id']       =    $id;
                $rew['status']  = strlen($status) > 0 ?  $status : $extra_info['status'];
                if($rew['status'] == 10){
                    $rew['status'] = 0;
                    $rew['data']['strstr'] = '请上传合成图';
                    $this->ajaxReturn($rew);
                    exit;
                }
                $data['id'] = $rew['id'];
                $data['status'] = $rew['status'];
                $ras = $this->client->card_edit($data);
                if($ras){
//					dump($data);exit;
					$this->inspect($id,$data['status']);
		    	}else{
		    		$rew['status'] = 0;
		    		$rew['data']['strstr'] = '修改失败';
		    		$this->ajaxReturn($rew);
			    	exit;
		    	}
    		}

		}
		
	}

	/**
	 * @param $extra_info //上一次修改数据
	 * @param $id  //身份证表id
	 * @param $status //状态
	 */
     public function inspect($id,$status)
	 {
		 //根据id 查出更新之后的数据库信息
		 $where['id'] = $id;
		 $extraInfo = M('user_extra_info')->where($where)->find();
		 //未审核
		 if($status == 0){
			 //拿上一次修改的数据 来判断 更新新的数据
			 if(!empty($extraInfo['user_id']) && !empty($extraInfo['true_name'])  && !empty($extraInfo['idno']) && !empty($extraInfo['tel'])){
				 $map['user_id'] = $extraInfo['user_id'];
				 $map['receiver'] = $extraInfo['true_name'];
				 $map['idno'] = $extraInfo['idno'];
				 //查出MKNO信息
				 $tranUlist = M('tran_ulist')->where($map)->getField('MKNO');
				 $maps = $tranUlist;
				 //查出已打印并且状态为20的信息
				 $tranList = M('tran_list')->where(array('MKNO'=>$maps))->getField('IL_state');
				if(empty($tranUlist))
				{
					$where_order['user_id']   = $extraInfo['user_id'];
					$where_order['receiver'] = $extraInfo['true_name'];
					$where_order['idno']       = $extraInfo['idno'];
					$order_data['small_back_img'] = $extraInfo['small_back_img'];
					$order_data['back_id_img'] = $extraInfo['back_id_img'];
					$order_data['small_front_img'] = $extraInfo['small_front_img'];
					$order_data['front_id_img'] = $extraInfo['front_id_img'];
					M('tran_ulist')->where($where_order)->save($order_data);
				}else if(!empty($tranUlist) && $tranList < 20){
					$tranUlist = M('tran_ulist')->where($map)->getField('MKNO');
					$maps = $tranUlist;
					//dump($maps);exit;
					$where_order['user_id']   = $extraInfo['user_id'];
					$where_order['receiver'] = $extraInfo['true_name'];
					$where_order['idno']       = $extraInfo['idno'];
					$order_data['small_back_img'] = $extraInfo['small_back_img'];
					$order_data['back_id_img'] = $extraInfo['back_id_img'];
					$order_data['small_front_img'] = $extraInfo['small_front_img'];
					$order_data['front_id_img'] = $extraInfo['front_id_img'];
					M('tran_ulist')->where($where_order)->save($order_data);
					M('tran_list')->where(array('MKNO'=>$maps))->save($order_data);

				}

			 }
		//审核通过
		 }else if($status == 10){
			 //修改订单身份证图片上传状态
			 if (!empty($extraInfo['true_name']) && !empty($extraInfo['idno']) && !empty($extraInfo['tel'])) {
				 $map['receiver'] = $extraInfo['true_name'];
				 $map['idno'] = $extraInfo['idno'];
				 $tranUlist = M('tran_ulist')->where($map)->getField('MKNO');
				 $maps = $tranUlist;
				 $tranList = M('tran_list')->where(array('MKNO'=>$maps))->getField('IL_state');
				 if(empty($tranUlist))
				 {
					 $where_order['user_id']   = $extraInfo['user_id'];
					 $where_order['receiver'] = $extraInfo['true_name'];
					 $where_order['idno']       = $extraInfo['idno'];
					 $order_data['small_back_img'] = $extraInfo['small_back_img'];
					 $order_data['back_id_img'] = $extraInfo['back_id_img'];
					 $order_data['small_front_img'] = $extraInfo['small_front_img'];
					 $order_data['front_id_img'] = $extraInfo['front_id_img'];
					 M('tran_ulist')->where($where_order)->save($order_data);
				 }else if(!empty($tranUlist) && $tranList < 20){
					 $tranUlist = M('tran_ulist')->where($map)->getField('MKNO');
					 $maps = $tranUlist;
					 $where_order['user_id']   = $extraInfo['user_id'];
					 $where_order['receiver'] = $extraInfo['true_name'];
					 $where_order['idno']       = $extraInfo['idno'];
					 $order_data['small_back_img'] = $extraInfo['small_back_img'];
					 $order_data['back_id_img'] = $extraInfo['back_id_img'];
					 $order_data['small_front_img'] = $extraInfo['small_front_img'];
					 $order_data['front_id_img'] = $extraInfo['front_id_img'];
					 M('tran_ulist')->where($where_order)->save($order_data);
					 M('tran_list')->where(array('MKNO'=>$maps))->save($order_data);

				 }
			 }


		 }else{
			 if (!empty($extraInfo['true_name']) && !empty($extraInfo['idno']) && !empty($extraInfo['tel'])) {
				 $map['receiver'] = $extraInfo['true_name'];
				 $map['idno'] = $extraInfo['idno'];
				 $tranUlist = M('tran_ulist')->where($map)->getField('MKNO');
				 $maps = $tranUlist;
				 $tranList = M('tran_list')->where(array('MKNO'=>$maps))->getField('IL_state');
				 $tranUlists = M('tran_ulist')->where($map)->getField('certify_upload_type');
				//dump($tranUlist);exit;
				 if(empty($tranUlist))
				 {
					 $where_order['user_id']   = $extraInfo['user_id'];
					 $where_order['receiver'] = $extraInfo['true_name'];
					 $where_order['idno']       = $extraInfo['idno'];
					 $order_data['id_img_status'] = 0;
                     $order_data['lib_idcard'] = 0;
					 M('tran_ulist')->where($where_order)->save($order_data);
					 if($tranUlists ==2)
					 {
						 $tranId = M('tran_ulist')->where($map)->getField('id');
						 $rand_code = M('mkno_key')->where(array('u_id'=>$tranId))->getField('un_key');
						 $catch_content['receiver'] = $extraInfo['true_name'];
						 $catch_content['reTel'] = $extraInfo['tel'];
						 $catch_content['MknoKey'] = $rand_code;
						 $catch_content['time'] = time();
						 $queue = new \Lib11\Queue\JoinQueue();
						 $queue->join_queue($catch_content);
					 }

				 }else if($tranList < 20){
					 $where_order['user_id']   = $extraInfo['user_id'];
					 $where_order['receiver'] = $extraInfo['true_name'];
					 $where_order['idno']       = $extraInfo['idno'];
					 $order_data['id_img_status'] = 0;
                     $order_data['lib_idcard'] = 0;
					 M('tran_ulist')->where($where_order)->save($order_data);
					 if($tranUlists ==2)
					 {
						 $tranId = M('tran_ulist')->where($map)->getField('id');
						 $rand_code = M('mkno_key')->where(array('u_id'=>$tranId))->getField('un_key');
						 $catch_content['receiver'] = $extraInfo['true_name'];
						 $catch_content['reTel'] = $extraInfo['tel'];
						 $catch_content['MknoKey'] = $rand_code;
						 $catch_content['time'] = time();
						 $queue = new \Lib11\Queue\JoinQueue();
						 $queue->join_queue($catch_content);
					 }

				 }

			 }
		 }
		 $rew['status'] = 1;
		 $rew['data']['strstr'] = '修改成功';
		 $this->ajaxReturn($rew);
		 exit;
	 }

    /**
     * 上传身份证头像面识别
     */
    public  function BackDistinguish(){
        $more = I('post.more');
        $id = I('post.id');
        //没有再次识别或者不再次识别 要验证身份证信息是否识别, 识别过不能再次识别 , $more = 1 表示 不再次识别  $more = 2 表示要再次识别
        if(empty($more) || $more == 1){
            if (!empty($id)){
                $where['id'] = $id;
                $res = M('user_extra_info')->where($where)->find();
                if(!empty($res['true_name']) && !empty($res['idno']) && !empty($res['valid_date_end'])){
                    $rew['status'] = 0;
                    $rew['data']['strstr'] = '身份证已识别,不能再次识别';
                    $rew['data']['emptys'] = 1; //作用：清空文本值

                    //$rew['data']['url'] = U('AdminRecipient/ntreated_documents');
                    $this->ajaxReturn($rew);
                    exit;
                }
            }
        }

        $files = C("UPLOAD_DIR").C('UPLOAD_NAME');

        if(!file_exists($files))
        {
            mkdir ($files,0777,true);
        }
        $uploadClass = new \Think\Upload();
        $uploadClass->maxSize=C('UPLOAD_SIZE');
        $uploadClass->exts=C('UPLOAD_TYPE');
        $uploadClass->rootPath=$files;
        $info = $uploadClass->upload();
        if(!$info) {
            // 上传错误提示错误信息
            $rew['status'] = 0;
            $rew['data']['strstr'] = $uploadClass->getError();
            //$rew['data']['url'] = U('AdminRecipient/ntreated_documents');
            $this->ajaxReturn($rew);
            exit;
        }
        //添加语言20180608  start
        $lang = str_replace('\\', '/', dirname(dirname(dirname(__FILE__))));
        $lang =  $lang .'/WebUser/Lang/zh-cn.php';
        $mkl = require_once($lang);

        $obj = new \Lib10\Idcardali\AliIdcard();
        $back_id_img   	= C("UPLOAD_DIR").C('UPLOAD_NAME') . $info['back_id_img']['savepath'].$info['back_id_img']['savename'];
        $result = $obj->photo($back_id_img);
        if(!$result){
            $rew['status'] = 0;
            $rew['data']['strstr'] = $mkl[$obj->getError()];
            unlink($back_id_img);
            $this->ajaxReturn($rew);
            exit;
        }
        $data['true_name']  = $result['name'];
        $data['sex']        = $result['sex'];
        $data['nation']     = $result['nation'];
        $data['birth']      = $result['birth'];
        $data['address']    = $result['address'];
        $data['idno']       = $result['idcard'];
		$where['id'] = $id;
		if (!empty($where['id'])) {
            $res = M('user_extra_info')->where($where)->find();
            if ($data['true_name'] == $res['true_name'] && $data['idno'] == $res['idno']) {
                $rew['status'] = 1;
                $rew['data']['strstr'] = '身份证头像面识别成功！';
                $rew['data']['distinguish'] = $data;
                unlink($back_id_img);
                $this->ajaxReturn($rew);
                exit;
            } else {
                $rew['status'] = 0;
                $rew['data']['strstr'] = '识别的身份证号与姓名与显示身份证号和姓名不相符';
                $this->ajaxReturn($rew);
                exit;
            }
        }else{
            $rew['status'] = 1;
            $rew['data']['strstr'] = '身份证头像面识别成功！';
            $rew['data']['distinguish'] = $data;
            unlink($back_id_img);
            $this->ajaxReturn($rew);
            exit;

        }

    }

    /**
     * 上传身份证国徽面识别
     */
    public function FrontDistinguish(){
        $more = I('post.more');
        $id = I('post.id');
        //没有再次识别或者不再次识别 要验证身份证信息是否识别, 识别过不能再次识别 , $more = 1 表示 不再次识别  $more = 2 表示要再次识别
        if(empty($more) || $more == 1){
            if (!empty($id)){
                $where['id'] = $id;
                $res = M('user_extra_info')->where($where)->find();
                if(!empty($res['true_name']) && !empty($res['idno']) && !empty($res['valid_date_end'])){
                    $rew['status'] = 0;
                    $rew['data']['strstr'] = '身份证已识别,不能再次识别';
                    $rew['data']['emptys'] = 1;

                    //$rew['data']['url'] = U('AdminRecipient/ntreated_documents');
                    $this->ajaxReturn($rew);
                    exit;
                }
            }
        }
        $files = C("UPLOAD_DIR").C('UPLOAD_NAME');

        if(!file_exists($files))
        {
            mkdir ($files,0777,true);
        }
        $uploadClass = new \Think\Upload();
        $uploadClass->maxSize=C('UPLOAD_SIZE');
        $uploadClass->exts=C('UPLOAD_TYPE');
        $uploadClass->rootPath=$files;
        $info = $uploadClass->upload();
        if(!$info) {
            // 上传错误提示错误信息
            $rew['status'] = 0;
            $rew['data']['strstr'] = $uploadClass->getError();
            //$rew['data']['url'] = U('AdminRecipient/ntreated_documents');
            $this->ajaxReturn($rew);
            exit;
        }
        //添加语言20180608  start
        $lang = str_replace('\\', '/', dirname(dirname(dirname(__FILE__))));
        $lang =  $lang .'/WebUser/Lang/zh-cn.php';
        $mkl = require_once($lang);

        $obj = new \Lib10\Idcardali\AliIdcard();
        $front_id_img   = C("UPLOAD_DIR").C('UPLOAD_NAME') . $info['front_id_img']['savepath'].$info['front_id_img']['savename'];
        $result = $obj->national_emblem($front_id_img);
        if(!$result){
            $rew['status'] = 0;
            $rew['data']['strstr'] = $mkl[$obj->getError()];
            unlink($front_id_img);
            $this->ajaxReturn($rew);
            exit;
        }

        $data['authority']          = $result['authority'];
        $data['valid_date_start']  = $result['valid_date_start'];
        $data['valid_date_end']    = $result['valid_date_end'];

        $rew['status'] = 1;
        $rew['data']['strstr'] = '身份证国徽面识别成功！';
        $rew['data']['distinguish'] = $data;
        unlink($front_id_img);
        $this->ajaxReturn($rew);
        exit;
    }












	/**
	 * 已处理身份证证件
	 * Enter description here ...
	 */
	public function processed(){
		$data['epage'] = C('EPAGE');
		$data['p'] = I('get.p');
		
		$name = I('get.name');
		$idcard = I('get.idcard');
		if(!empty($name)){
			$data['name'] = $name;
		}
		if(!empty($idcard)){
			$data['cre_num'] = $idcard;
		}
		
		$res = $this->client->processed($data);
		
		//echo ADMIN_FILE;
		//exit;
		
		$list = $res['list'];
		$count = $res['count'];
		
		$page=new \Think\Page($count,$data['epage']);
		//%FIRST% 表示第一页的链接显示 
		//%UP_PAGE% 表示上一页的链接显示 
		//%LINK_PAGE% 表示分页的链接显示 
		//%DOWN_PAGE% 表示下一页的链接显示 
		//%END% 表示最后一页的链接显示 
		
		$page->setConfig('theme','%FIRST% %UP_PAGE% %LINK_PAGE% %DOWN_PAGE% %END% %HEADER%');
		$page -> setConfig('prev', '上一页');
		$page -> setConfig('next','下一页');
		$page -> setConfig('last','末页');
		$page -> setConfig('first','首页');
		
		$show = $page->show();
		
		
		
		
		
		foreach ($list AS $key => $val){
			$list[$key]['status_name'] = C('ID_CARD_STATUS')[ $val['status']];
			$list[$key]['num_status_name'] = C('ID_CARD_STATUS')[ $val['num_status']];
//			if(strlen($val['cre_num']) > 8 ){
//				$idcard_a 	= substr($val['cre_num'],0,4);
//				$idcard_b 	= substr($val['cre_num'],-4);
//				$idcard_c 	= substr($val['cre_num'], 4, -4);
//				$coumt 		= strlen($idcard_c);
//				for($i = 1; $i <= $coumt; $i++){
//					$th .= '*';
//				}
//				$idcard_d 	= str_replace($val['cre_num'], $idcard_c, $th);
//				$idcard = $idcard_a . $idcard_d . $idcard_b;
//				$list[$key]['idcard'] = $idcard;
//			}else{
//				$list[$key]['idcard'] = $val['cre_num'];
//			}
//			$th = '';			
//			
		}
		$this->assign('type', 'processeds');
		$this->assign('page',$show);// 赋值分页输出
		$this->assign('list',$list);
		$this->assign('wu_file', WU_FILE);
		$this->assign('admin_file', ADMIN_FILE);
		$this->assign('bucket_url', C('BUCKET_URL'));
        $this->assign('idcard_cos', C('IDCARD_COS'));


		$this->display('add_document');
	}

	/**
	 * 身份证号码状态审核
	 * Enter description here ...
	 */
	public function audit_processed(){
		$id = I('post.id');
		$num_status = I('post.num_status');
		$data['id'] = $id;
		$data['num_status'] = $num_status;
		$res = $this->client->audit_processed($data);
		
		if($res['status']){
			$rw = array('state' => 'yes', 'msg' => $res['strstr']);
		}else{
			$rw =  array('state' => 'no', 'msg' => $res['errorstr']);
		}
		
		$this->ajaxReturn($rw); 
	}
	
	/**
	 * 所有身份证证件
	 * Enter description here ...
	 */
	public function add_documents(){
		$data['epage'] = C('EPAGE');
		$data['p'] = I('get.p');

		$name = I('get.name');
		$idcard = I('get.idcard');
		if(!empty($name)){
			$data['name'] = $name;
		}
		if(!empty($idcard)){
			$data['cre_num'] = $idcard;
		}
		
		$res = $this->client->add_documents($data);
		//echo ADMIN_FILE;
		//exit;
		//print_r($res);
		//exit;
		$list = $res['list'];
		$count = $res['count'];
		$page=new \Think\Page($count,$data['epage']);
		//%FIRST% 表示第一页的链接显示 
		//%UP_PAGE% 表示上一页的链接显示 
		//%LINK_PAGE% 表示分页的链接显示 
		//%DOWN_PAGE% 表示下一页的链接显示 
		//%END% 表示最后一页的链接显示 
		
		$page->setConfig('theme','%FIRST% %UP_PAGE% %LINK_PAGE% %DOWN_PAGE% %END% %HEADER%');
		$page -> setConfig('prev', '上一页');
		$page -> setConfig('next','下一页');
		$page -> setConfig('last','末页');
		$page -> setConfig('first','首页');
		
		$show = $page->show();		
		
		
//		$th = '';
		foreach ($list AS $key => $val){
			$list[$key]['status_name'] = C('ID_CARD_STATUS')[ $val['status']];
//			if(strlen($val['cre_num']) > 8 ){
//				
//				$idcard_a 	= substr($val['cre_num'],0,4);
//				$idcard_b 	= substr($val['cre_num'],-4);
//				$idcard_c 	= substr($val['cre_num'], 4, -4);
//				$coumt 		= strlen($idcard_c);
//				for($i = 1; $i <= $coumt; $i++){
//					$th .= '*';
//				}
//				$idcard_d 	= str_replace($val['cre_num'], $idcard_c, $th);
//				$idcard = $idcard_a . $idcard_d . $idcard_b;
//				$list[$key]['idcard'] = $idcard;
//			}else{
//				$list[$key]['idcard'] = $val['cre_num'];
//			}
//			$th = '';			
//			
		}
		
		$this->assign('page',$show);// 赋值分页输出
		$this->assign('list',$list);
		$this->assign('wu_file', WU_FILE);		
		$this->assign('admin_file', ADMIN_FILE);
		$this->assign('bucket_url', C('BUCKET_URL'));
        $this->assign('idcard_cos', C('IDCARD_COS'));
		$this->display('add_document');			
		
	}
	
	/**
	 * 身份证图片下载
	 * Enter description here ...
	 */
	
	public function download(){
		$id = I('get.id');
		$file_type = I('get.file_type');
		if(empty($id)){
			$this->error('参数出错');
			exit;
		}
		$data['id'] = $id;
		$rw = $this->client->download($data);

		$file_name = $rw[$file_type];
		
		if('id_img' == $file_type){
			$file_path = ADMIN_FILE . $file_name;
		}else{
			$file_path = WU_FILE . $file_name;
		}

		//获取图片大小
		$file = file_get_contents($file_path);
		$file_size = strlen($file);
		if($file_size > 0){
		    $file_name = pathinfo($file_path, PATHINFO_BASENAME);
		   	$arr_name = explode('.', $file_name);
		    $leng = count($arr_name) - 1;
		    if ($file_type == 'front_id_img'){
		    	$name_type = 1;
		    }else if($file_type == 'back_id_img'){
		    	$name_type = 2;
		    }
		    $name = $rw['idno'];
		    $fty = $arr_name[$leng];

		    if('id_img' == $file_type){
		    	$file_name = $name  . '.' . $fty;
		    }else{
		    	$file_name = $name . '_' . $name_type . '.' . $fty;
		    }
		   
		   	$fp=fopen($file_path,"r");
		 
		    //http 下载需要的响应头 
		    header("Content-type: application/octet-stream"); //返回的文件 
		    header("Accept-Ranges: bytes");   //按照字节大小返回
		    header("Accept-Length: $file_size"); //返回文件大小
		    header("Content-Disposition: attachment; filename=".$file_name);//这里客户端的弹出对话框，对应的文件名
		    //向客户端返回数据
		    //设置大小输出
		    $buffer=1024 * 3;
		    //为了下载安全，我们最好做一个文件字节读取计数器
		    $file_count=0;
		    //判断文件指针是否到了文件结束的位置(读取文件是否结束)
		    while(!feof($fp) && ($file_size-$file_count)>0){
		    $file_data=fread($fp,$buffer);
		    //统计读取多少个字节数
		    $file_count+=$buffer;
		    //把部分数据返回给浏览器
		    echo $file_data;
		    }
		    //关闭文件
		    fclose($fp);		
				
		}else{
			$url = $_SERVER["HTTP_REFERER"];
			
			echo "<script> alert('不存在该照片！'); </script>";
			echo "<meta http-equiv='Refresh' content='1;URL=$url'>"; 
			exit;	
		}
		
	}
	
	
	
	/**
	 * 已处理身份证图片下载
	 * Enter description here ...
	 */
	
	public function download_proc(){
		$id = I('get.id');
		$file_type = I('get.file_type');
		if(empty($id)){
			$this->error('参数出错');
			exit;
		}
		$data['id'] = $id;
		$rw = $this->client->download_proc($data);
		
		$file_name = $rw[$file_type];
		if('id_img' == $file_type){
			$file_path = ADMIN_FILE . $file_name;
		}else{
			$file_path = WU_FILE . $file_name;
		}
		//获取图片大小
		$file = file_get_contents($file_path);
		$file_size = strlen($file);
		if($file_size > 0){
		    $file_name = pathinfo($file_path, PATHINFO_BASENAME);
		   	$arr_name = explode('.', $file_name);
		    $leng = count($arr_name) - 1;
		    if ($file_type == 'id_card_front'){
		    	$name_type = 1;
		    }else if($file_type == 'id_card_back'){
		    	$name_type = 2;
		    }
		    $name = $rw['cre_num'];
		    $fty = $arr_name[$leng];
		    if('id_img' == $file_type){
		    	$file_name = $name  . '.' . $fty;
		    }else{
		    	$file_name = $name . '_' . $name_type . '.' . $fty;
		    }
		   
		   	$fp=fopen($file_path,"r");
		 
		    //http 下载需要的响应头 
		    header("Content-type: application/octet-stream"); //返回的文件 
		    header("Accept-Ranges: bytes");   //按照字节大小返回
		    header("Accept-Length: $file_size"); //返回文件大小
		    header("Content-Disposition: attachment; filename=".$file_name);//这里客户端的弹出对话框，对应的文件名
		    //向客户端返回数据
		    //设置大小输出
		    $buffer=1024 * 3;
		    //为了下载安全，我们最好做一个文件字节读取计数器
		    $file_count=0;
		    //判断文件指针是否到了文件结束的位置(读取文件是否结束)
		    while(!feof($fp) && ($file_size-$file_count)>0){
		    $file_data=fread($fp,$buffer);
		    //统计读取多少个字节数
		    $file_count+=$buffer;
		    //把部分数据返回给浏览器
		    echo $file_data;
		    }
		    //关闭文件
		    fclose($fp);		
				
		}else{
			$url = $_SERVER["HTTP_REFERER"];
			
			echo "<script> alert('不存在该照片！'); </script>";
			echo "<meta http-equiv='Refresh' content='1;URL=$url'>"; 
			exit;	
		}
		
	}

	

	
	
	
}	
	