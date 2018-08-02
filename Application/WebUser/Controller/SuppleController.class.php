<?php
/**
 * 完善注册资料   20170622 jie  注销不使用
 */
namespace WebUser\Controller;
use Think\Controller;
class SuppleController extends Controller{

	public function _initialize(){
		//验证是否有此session
		$reg_info = $_SESSION['reg_info'];

        if(!isset($reg_info) || $reg_info['id'] == ''){   //如果缓存里面没数据
             $this->redirect('Register/404');
        }else{
	        vendor('Hprose.HproseHttpClient');
	        $client = new \HproseHttpClient(C('WAPIURL').'/Server');
	        $this->client = $client;
        }
	}

	/**
	 * 个人基础资料
	 * @return [type] [description]
	 */
	public function per_base(){
        if(IS_POST){
            //处理方法
            $reg_info = $_SESSION['reg_info'];
            $data = array();
            $data['country']                   = trim(I('post.country'));                //注册国家
            $data['user_id']                   = $reg_info['id'];                        //用户id
            $data['province']                  = trim(I('post.province'));               //省、洲
            $data['city']                      = trim(I('post.city'));                   //市
            $data['certificate_type']          = trim(I('post.IdType'));                 //证件类型
            $data['certificate_number']        = trim(I('post.IdNumber'));               //证件号
            $data['sender_address']            = trim(I('post.address'));                //发件人地址
            $data['sender_zipcode']            = trim(I('post.postCode'));               //发件人邮编
            $data['sender_phone']              = trim(I('post.Phone'));                  //手机(手机、固话必填一个)
            $data['sender_telephone']          = trim(I('post.FixedTelephone'));         //固话
            $data['resident_country']          = trim(I('post.ResidentContry'));         //所在国家
            $data['self_name']                 = trim(I('post.ContactsUserName'));
            $data['self_address']              = trim(I('post.ContactsAddress'));
            $data['zipcode']                   = trim(I('post.ContactsPostCode'));
            $data['self_phone']                = trim(I('post.ContactsMobilePhone'));
            $data['self_telephone']            = trim(I('post.ContactsFixedPhone'));
            $data['contactsQQ']                = trim(I('post.ContactsQQ'));
            $data['register_time']             = time();

            if($data['self_phone'] == '' && $data['self_telephone'] == ''){
                $result = array('status'=>'0', 'info'=>'手机、固话必填一个');
                $this->ajaxReturn($result);
            }
            
            $client = $this->client;
            $result = $client->registerPerson($reg_info['id'],$data);

            if($result == 'success'){

            	if($reg_info['switch_type'] == 'all_info'){	//个人资料+证件图片
            		$url = U('Supple/perpic_view');
            	}else{
            		$url = U('Member/index');
            	}
                $result = array('status'=>'1', 'info'=>'提交成功,正在跳转...','url'=>$url);
                $this->ajaxReturn($result);
            }else{

                $result = array('status'=>'0', 'info'=>'提交失败,请联系客服');
                $this->ajaxReturn($result);
            }

        }else{  //视图

            $reg_info = $_SESSION['reg_info'];

            $this->assign('FirstName',$reg_info['FirstName']);
            $this->assign('LastName',$reg_info['LastName']);

            $this->display();

        }
	}

	/**
	 * 个人证件照上传 视图
	 * @return [type] [description]
	 */
	public function perpic_view(){
		$this->display();
	}

	/**
	 * 个人证件照上传 方法
	 * @return [type] [description]
	 */
	public function per_pic(){
        if(!IS_POST){
            die('非法操作');
        }

        $plist = $_FILES;
        if ($plist['photo2']['size'] == 0) unset($plist['photo2']);

        //验证此两类图片是否已上传
        if($plist['photo1']['size'] == 0){
            $result = array('status'=>'0', 'info'=>'请上传身份证/驾照/护照');
            $this->ajaxReturn($result);
        }
        if($plist['photo3']['size'] == 0){
            $result = array('status'=>'0', 'info'=>'请上传信用卡或银行账单');
            $this->ajaxReturn($result);
        }
        //文件大小是否在4M之内
        foreach($plist as $item){
            if(intval($item['size']) > 4194304){
                // echo '单个图片的大小不可超过4M';
                $result = array('status'=>'0', 'info'=>'单个图片的大小不可超过4M');
                $this->ajaxReturn($result);
            }

            //扩展名是否 in_array array('jpg', 'gif', 'png', 'jpeg')
            $type = explode("/",$item['type']);     //判断文件类型

            if(!in_array($type['1'],array('jpg','gif','png','jpeg'))){
                // echo '文件必须为图片！';
                $result = array('status'=>'0', 'info'=>'文件必须为图片！');
                $this->ajaxReturn($result);
            }
        }
        

        $reg_info = $_SESSION['reg_info'];  //当前id

        // 上传文件     
        $info           = array();
        for($i=1;$i<4;$i++){
            if($_FILES['photo'.$i]['size']==0){
                continue;
            }
            $upload           = new \Think\Upload();// 实例化上传类  Man每上传一个 要实例化一次  
            $upload->maxSize  = 0 ;// 设置附件上传大小    
            $upload->exts     = array('jpg', 'gif', 'png', 'jpeg');// 设置附件上传类型    
            $upload->rootPath = WU_ABS_FILE; //设置文件上传保存的根路径
            $upload->savePath = C('PERSON_PIC'); // 设置文件上传的保存路径（相对于根路径）
            $upload->autoSub  = true; //自动子目录保存文件
            $upload->saveName = array('uniqid',mt_rand()); //设置上传文件名
            $info['photo'.$i] =   $upload->uploadOne($_FILES['photo'.$i]);
            //分析是否上传成功，不成功要退出，重新上传
            if(!$info['photo'.$i]){
                $result = array('status'=>'0', 'info'=>'上传失败，请重新再试');
                $this->ajaxReturn($result); 
            }
        }

        // 上传成功  
        foreach($info as $key=>$v){
            $arr[$key] = $v['savepath'].$v['savename'];
        }
        // dump($arr);die;
        $client = $this->client;
        $result = $client->savepic($reg_info['id'],$arr);

        if($result == 'success'){

        	if($reg_info['switch_type'] == 'all_info' || $reg_info['switch_type'] == 'pic_info'){	//个人资料+证件图片  或者证件图片
        		$url = U('Member/index');
        	}

            $result = array('status'=>'1', 'info'=>'提交成功,正在跳转...','url'=>$url);

            $this->ajaxReturn($result);
        }else{
            $result = array('status'=>'0', 'info'=>'提交失败');
            $this->ajaxReturn($result);
        }
	}

	/**
	 * 企业基础资料
	 * @return [type] [description]
	 */
	public function com_base(){
        if(IS_POST){

            $reg_info = $_SESSION['reg_info'];

            $data = array();
            $data['country']                = trim(I('post.country'));
            $data['user_id']                = $reg_info['id'];
            $data['province']               = trim(I('post.province'));
            $data['city']                   = trim(I('post.city'));
            $data['company_address']        = trim(I('post.CompanyAddress'));
            $data['company_representative'] = trim(I('post.CompanyRepresentative'));
            $data['business_license']       = trim(I('post.BusinessLicense'));
            $data['vat_number']             = trim(I('post.VATNumber'));
            $data['sender_address']         = trim(I('post.Address'));
            $data['sender_zipcode']         = trim(I('post.PostCode'));
            $data['self_name']              = trim(I('CompanyContact'));
            $data['self_phone']             = trim(I('post.Phone'));
            $data['self_telephone']         = trim(I('post.FixedTelephone'));
            $data['contactsQQ']             = trim(I('post.ContactsQQ'));
            $data['register_time']          = time();
            
            if($data['self_phone'] == '' && $data['self_telephone'] == ''){
                $result = array('status'=>'0', 'info'=>'手机、固话必填一个');
                $this->ajaxReturn($result);
            }

            $client = $this->client;
            $result = $client->registerCompany($reg_info['id'],$data);

            if($result == 'success'){

            	if($reg_info['switch_type'] == 'all_info'){
            		$url = U('Supple/compic_view');
            	}else{
            		$url = U('Member/index');
            	}
                $result = array('status'=>'1', 'info'=>'提交成功,正在跳转...','url'=>$url);
                $this->ajaxReturn($result);
            }else{

                $result = array('status'=>'0', 'info'=>'提交失败,请联系客服');
                $this->ajaxReturn($result);
            }

        }else{

            $reg_info = $_SESSION['reg_info'];

            $this->assign('companyname',$reg_info['companyname']);

            $this->display();

        }
	}

	/**
	 * 企业证件照上传 视图
	 * @return [type] [description]
	 */
	public function compic_view(){
		$this->display();
	}

	/**
	 * 企业证件照上传 方法
	 * @return [type] [description]
	 */
	public function com_pic(){
        if(!IS_POST){
            die('非法操作');
        }
        
        $reg_info = $_SESSION['reg_info'];  //当前id

        $plist = $_FILES;
        //验证此两类图片是否已上传
        if($plist['photo1']['size'] == 0){
            $result = array('status'=>'0', 'info'=>'请上传营业执照');
            $this->ajaxReturn($result);
        }

        $upload = new \Think\Upload();// 实例化上传类    
        $upload->maxSize   = 4194304 ;// 设置附件上传大小    
        $upload->exts      = array('jpg', 'gif', 'png', 'pdf');// 设置附件上传类型    
        $upload->rootPath  = WU_ABS_FILE; //设置文件上传保存的根路径
        $upload->savePath  = C('COMPANY_PIC'); // 设置文件上传的保存路径（相对于根路径）
        $upload->autoSub   = false; //自动子目录保存文件
        $upload->saveName  = array('uniqid',mt_rand()); //设置上传文件名
        // 上传文件     
        $info   =   $upload->upload();

        if(!$info) {// 上传错误提示错误信息        
            // $this->error($upload->getError());
            $result = array('status'=>'0', 'info'=>$upload->getError());
            $this->ajaxReturn($result); 

        }else{// 上传成功  
            foreach($info as $key=>$v){
                $arr[$key] = $v['savepath'].$v['savename'];
            }

            $client = $this->client;
            $result = $client->savepic($reg_info['id'],$arr);

            if($result == 'success'){
	        	if($reg_info['switch_type'] == 'all_info' || $reg_info['switch_type'] == 'pic_info'){	//个人资料+证件图片  或者证件图片
	        		$url = U('Member/index');
	        	}

	            $result = array('status'=>'1', 'info'=>'提交成功,正在跳转...','url'=>$url);
                $this->ajaxReturn($result);
            }else{
                $result = array('status'=>'0', 'info'=>'提交失败');
                $this->ajaxReturn($result);
            }
        }
	}

}