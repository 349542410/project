<?php
namespace WebUser\Controller;
use Think\Controller;
class RegisterController extends Controller {

    //预加载
    public function _initialize(){
        vendor('Hprose.HproseHttpClient');
        $client = new \HproseHttpClient(C('WAPIURL').'/Server');
        $this->client = $client;
    }
    /**
    *   选择发件国 视图
    */
   public function index(){
        $this->display('SelectCountry');
    }

    /**
    *   选择注册类型 视图
    */
    public function selectCertCategory(){

        $countryId = I('get.countryId');    //国家id

        if(!$countryId){

            $this->redirect('404');
            // $this->error('页面不存在','index');

        }else if($countryId != '1'){
            $this->error(L("Paved_rc"));
        }

        $this->assign('countryId',$countryId);

        $this->display();
    }
    
    /**
    *   注册类型 视图
    */  
    public function RegisterType(){

        $countryId = I('get.countryId');    //国家id
        $type      = I('get.type');         //注册类型(个人或企业)

        if(!$countryId && !$type){
            $this->error(L('Page_not_rc'),'index');
        }
        // dump($type);
        if($type == 'person'){
            $this->assign('countryId',$countryId);
            $this->assign('type',$type);
            $this->display('RegisterPerson');
        }
        if($type == 'company'){
            $this->assign('countryId',$countryId);
            $this->assign('type',$type);
            $this->display('RegisterCompany');
        }
        
    }

//================================= 以下属于 个人注册 ==============================================//

    /**
    *   个人注册 方法
    */
    public function RegisterPerson(){

        if(!IS_POST){
            die(L('ill_operation_rc'));
        }

        $countryId = I('post.countryId');   //国家id
        $type      = I('post.type');    //注册类型

        if(!$countryId || !$type || $type != 'person'){
            $this->redirect('404');
            // $this->error('页面不存在','index');
        }

        $username  = trim(I('post.username'));      //用户名

        // $pwd       = strtolower(trim(I('post.pwd')));   //密码
        // $repwd     = strtolower(trim(I('post.repwd'))); //确认密码
        $pwd       = (trim(I('post.pwd')));   //密码
        $repwd     = (trim(I('post.repwd'))); //确认密码

        $email     = trim(I('post.email')); //邮箱
        $FirstName = trim(I('post.FirstName')); //姓
        $LastName  = trim(I('post.LastName'));  //名字
        $countryId = trim(I('post.countryId'));  //国家
        $lang      = L('lng');  //注册时使用的语言包种类
        $verify    = trim(I('post.verify'));

        //验证用户名规则
        $msg = get_name_rule($username);
        if($msg != ''){
            $result = array('status'=>'0', 'info'=>$msg);
            $this->ajaxReturn($result);
        }

        //数据校验
        $eamil_match="/^([0-9A-Za-z\\-_\\.]+)@([0-9a-z]+\\.[a-z]{2,3}(\\.[a-z]{2})?)$/i";
        if(empty($username)){
            $result = array('status'=>'0', 'info'=>L("Please_nickname_re"));
            $this->ajaxReturn($result);
        }
        if(empty($pwd)){
            $result = array('status'=>'0', 'info'=>L("Please_password_re"));
            $this->ajaxReturn($result);
        }

        //验证密码强度
        $msg = get_pwd_strength($pwd);
        if($msg != ''){
            $result = array('status'=>'0', 'info'=>$msg);
            $this->ajaxReturn($result);
        }

        if(empty($repwd)){
            $result = array('status'=>'0', 'info'=>L("Please_en_re"));
            $this->ajaxReturn($result);
        }
        if($pwd != $repwd){
            $result = array('status'=>'0', 'info'=>L("You_two_re"));
            $this->ajaxReturn($result);
        }
        if(empty($email)){
            $result = array('status'=>'0', 'info'=>L("Please_e_re"));
            $this->ajaxReturn($result);
        }
        if(preg_match($eamil_match,$email) != true){
            $result = array('status'=>'0', 'info'=>L("Email_ree_re"));
            $this->ajaxReturn($result);
        }
        if(empty($FirstName)){
            $result = array('status'=>'0', 'info'=>L("Pleas_nc_re"));
            $this->ajaxReturn($result);
        }
        if(empty($LastName)){
            $result = array('status'=>'0', 'info'=>L("Please_name_re"));
            $this->ajaxReturn($result);
        }

        $client = $this->client;

        $checkname = $client->checkname($username);
        if($checkname){
            $result = array('status'=>'0', 'info'=>L("User_exists_re"));
            $this->ajaxReturn($result);
        }

        $checkemail = $client->checkemail($email);
        if($checkemail){
            $result = array('status'=>'0', 'info'=>L("This_email_re"));
            $this->ajaxReturn($result);
        }

        // 检查验证码    
        if(empty($verify)){
            $result = array('status'=>'0', 'info'=>L("Please_code_re"));
            $this->ajaxReturn($result);
        }
        if(!check_verify($verify)){
            $result = array('status'=>'0', 'info'=>L("V_code_error_re"));
            $this->ajaxReturn($result);
        }

// $result = array('status'=>'1', 'info'=>'测试成功');
// $this->ajaxReturn($result);
// die;
        $result = $client->person($username,$pwd,$email,$FirstName,$LastName,$countryId,$lang,$type);
        
        if($result['status'] == '1'){
            // 发送邮箱验证码
            $code = rand(100000,999999);

            $reg_info = array(
                'id'        => $result['id'],
                'FirstName' => $FirstName,
                'LastName'  => $LastName,
                'countryId' => $countryId,
                'type'      => $type,
                'code'      => md5(base64_encode($code)),
                'ctime'     => time(),
            );
            session('reg_info',$reg_info);

            $content = create_content($username,$code);

            $this->send_email($email,$content,$username);
            //End

            // 信息的反馈改为在邮件发送后再返回 Jie 20151118
            // $result = array('status'=>'1', 'info'=>'提交成功');
            // $this->ajaxReturn($result);

        }else if($result['status'] == '0'){

            $result = array('status'=>'0', 'info'=>L("Submit_err_re"));
            $this->ajaxReturn($result);

        }else if($result['status'] == '500'){

            $result = array('status'=>'0', 'info'=>L("User_exists_re"));
            $this->ajaxReturn($result);
        }else{
            $this->ajaxReturn($result);
        }
        
    }

    //登录后，如果邮箱还未验证，就会进行此步操作
    public function supple_eamil(){
        $mkuser = session('mkuser');

        $map['id'] = array('eq',$mkuser['uid']);

        $client = $this->client;
        $userlist = $client->userlist($map);

        if(!$userlist){
            die(L('LAY_MesPar'));
        }

        $code = rand(100000,999999);
        $content = create_content($userlist['username'],$code);

        $this->send_email($userlist['email'],$content,$userlist['username'],true);

        session('reg_info.ctime',time());//验证码过期时间
        session('reg_info.code',md5(base64_encode($code)));//验证码
    }

    //邮箱认证 视图
    public function ensure(){
        $reg_info = session('reg_info');

        if(!isset($reg_info) || $reg_info['id'] == ''){   //如果缓存里面没数据
            $this->redirect('404');
        }

        if($reg_info['type'] == 'person'){
            $this->meta_title = L("personage_rc");
        }else{
            $this->meta_title = L('enterprise_rc');
        }

        $this->display();
    }

    //邮箱认证 方法
    public function authEmail(){
        if(!IS_POST){
            $this->redirect('404');
        }

        $code = I('post.uucode');   //邮箱验证码
        $value = session('reg_info');

        if($code == ''){
            $result = array('status'=>'0', 'info'=>L('Please_code_re'));
            $this->ajaxReturn($result);
        }

        $time = time();
        if(intval($time) - intval($value['ctime']) > 1800){
            $result = array('status'=>'0', 'info'=>L('Verification_c_h_rc'));
            $this->ajaxReturn($result);
        }

        if(md5(base64_encode($code)) != $value['code']){
            $result = array('status'=>'0', 'info'=>L("V_code_error_re"));
            $this->ajaxReturn($result);
        }else{

            $client = $this->client;
            //更新注册流程到邮箱认证
            $res = $client->whichStep(session('reg_info.id'), 2);

            //用户注册流程的认证错误提示
            if($res['status'] == '0'){
                $res['info'] = L($res['code']);
                $this->ajaxReturn($res); 
            }

            //根据$type判断
            if($value['type'] == 'person'){
                $url = U(CONTROLLER_NAME.'/CertStep1ForPerson');
            }else{
                $url = U(CONTROLLER_NAME.'/CertStep1ForCompany');
            }
            $result = array('status'=>'1', 'info'=>L('Validation_is_su_rc'),'url'=>$url);
            $this->ajaxReturn($result); 
        }
    }

    //用户手动点击获取验证邮件
    public function sendEmail(){
        if(!IS_AJAX){
            $this->redirect('404');
        }

        $pid = trim(I('post.pid'));
        $client = $this->client;
        $res = $client->authID($pid);
        
        if(!$res){
            $this->redirect('404');
        }

        $code = rand(100000,999999);
        //加入session
        session('reg_info.code',md5(base64_encode($code)));
        session('reg_info.ctime',time());

        $content = create_content($res['username'],$code);

        $this->send_email($res['email'],$content,$res['username']);

    }

    /**
     * 邮件发送  请注意发送邮件的邮箱是否开启了SMTP功能，未开启则无法使用第三方发送功能
     * @param  [type] $receiver [收件人]
     * @param  [type] $content  [发送的内容]
     * @param  [type] $address  [收件邮箱]
     * @param  [type] $ty       [默认为false]
     */
    protected function send_email($address,$content,$receiver,$ty=false){

        $args = array(
            'to' => array(
                $address,
            ),
            'title' => '[' . L('re_Meikuai') . ']' . L('re_Email_v'),
            'content' => $content,
            'Subject' => '[' . L('re_Meikuai') . ']' . L('re_Email_v'),
            'type' => 'html',
            'FromName' => '[' . L('re_Meikuai') . ']' . L('re_Email_v'),
        );
        
        $phpmail = new \Lib11\PHPMailer\PHPMailerTools();
        $res = $phpmail->sendMail($args);
        if(ACTION_NAME == 'sendEmail'){
            if(!$res['success']){
                $result = array('status'=>false,'msg'=>"Mailer Error: " . $res['info']);
            }else{
                $result = array('status'=>true,'msg'=>"done",'email'=>$address);
            }
        }else{
            if(!$res['success']){
                $result = array('status'=>false,'msg'=>"Mailer Error: " . $res['info']);
            }else{
                $result = array('status'=>true, 'info'=>L('Submit_su_rc'));
            }
        }

        if($ty == false){
           $this->ajaxReturn($result); 
        }else{
            return $result;
        }

    }

    /**
    *   完善个人资料
    */
    public function CertStep1ForPerson(){

        if(IS_POST){

            //处理方法
            // $reg_info = $_SESSION['reg_info'];
            $reg_info = session('reg_info');
            $data = array();
            $datalist['FirstName']               =trim(I('post.FirstName'));
            $datalist['LastName']                =trim(I('post.LastName'));

            $data['country']                   = trim(I('post.country'));          //注册国家
            $data['user_id']                   = $reg_info['id'];                        //用户id
            $data['province']                  = trim(I('post.province'));    //省、洲
            $data['city']                      = trim(I('post.city'));            //市
            $data['certificate_type']          = trim(I('post.IdType'));    //证件类型
            $data['certificate_number']        = trim(I('post.IdNumber'));  //证件号
            $data['sender_address']            = trim(I('post.address'));       //发件人地址
            $data['sender_zipcode']            = trim(I('post.postCode'));      //发件人邮编
            // $data['PhoneAreaCode']          = trim(I('post.PhoneAreaCode'));
            $data['sender_phone']              = trim(I('post.Phone'));               //手机(手机、固话必填一个)
            //  $data['FixedPhoneAreaCode']    = trim(I('post.FixedPhoneAreaCode'));
            $data['sender_telephone']          = trim(I('post.FixedTelephone'));        //固话
            $data['resident_country']          = trim(I('post.ResidentContry'));        //所在国家
            $data['self_name']                 = trim(I('post.ContactsUserName'));
            $data['self_address']              = trim(I('post.ContactsAddress'));
            $data['zipcode']                   = trim(I('post.ContactsPostCode'));
            // $data['ContactsMobileAreaCode'] = trim(I('post.ContactsMobileAreaCode'));
            $data['self_phone']                = trim(I('post.ContactsMobilePhone'));
            // $data['ContactsFixedAreaCode']  = trim(I('post.ContactsFixedAreaCode'));
            $data['self_telephone']            = trim(I('post.ContactsFixedPhone'));
            $data['contactsQQ']                = trim(I('post.ContactsQQ'));
            $data['register_time']             = time();
            // $data['mAreaCodeHidden']        = trim(I('post.mAreaCodeHidden'));
            // $data['fAreaCodeHidden']        = trim(I('post.fAreaCodeHidden'));
            
            $arr = I('post.');  //用数组装载post过来的所有数据

            /* 验证数据是否为空 */
            $chelist = array(
                'province'         => L('provinceMsg'),
                'city'             => L('cityMsg'),
                'IdType'           => L('IdTypeMsg'),
                'IdNumber'         => L('IdNumberMsg'),
                'address'          => L('addressMsg'),
                'postCode'         => L('postCodeMsg'),
                'ResidentContry'   => L('ResidentContryMsg'),
                'ContactsUserName' => L('ContactsUserNameMsg'),
                'ContactsAddress'  => L('ContactsAddressMsg'),
                'ContactsPostCode' => L('ContactsPostCodeMsg'),
            );

            // 验证姓名是否为空
            if($datalist['FirstName'] == '' && $datalist['LastName'] == ''){
                $result = array('status'=>'0', 'info'=>L('Please_name_re'));
                $this->ajaxReturn($result);
            }

            //验证字段是否为空
            foreach($chelist as $k=>$dis){
                if(trim($arr[$k]) == '' || trim($arr[$k]) == '请选择'){
                    $result = array('info'=>$chelist[$k],'status'=>'0');
                    $this->ajaxReturn($result);
                }
            }

            if($data['sender_phone'] == '' && $data['sender_telephone'] == ''){
                $result = array('status'=>'0', 'info'=>L('phone_is_required'));
                $this->ajaxReturn($result);
            }

            if($data['self_phone'] == '' && $data['self_telephone'] == ''){
                $result = array('status'=>'0', 'info'=>L('phone_is_required'));
                $this->ajaxReturn($result);
            }

            if(strlen($data['self_phone'])>16||strlen($data['self_telephone'])>16){
                $result = array('status'=>'0', 'info'=>L('l_tel_err'));
                $this->ajaxReturn($result);
            }

            $client = $this->client;
            $result = $client->registerPerson($reg_info['id'],$data,$datalist);

            //更新注册流程到完善个人信息
            $res = $client->whichStep(session('reg_info.id'), 3);

            //用户注册流程的认证错误提示
            if($res['status'] == '0'){
                $res['info'] = L($res['code']);
                $this->ajaxReturn($res); 
            }

            if($result == 'success'){

                $result = array('status'=>'1', 'info'=>L('Submit_su_rc'));
                $this->ajaxReturn($result);
            }else{

                $result = array('status'=>'0', 'info'=>L('Submit_err_re'));
                $this->ajaxReturn($result);
            }

        }else{  //视图


            // $reg_info = $_SESSION['reg_info'];
            $reg_info = session('reg_info');

            if(!isset($reg_info) || $reg_info['id'] == ''){   //如果缓存里面没数据

                $this->redirect('404');

            }else{
                $this->assign('FirstName',$reg_info['FirstName']);
                $this->assign('LastName',$reg_info['LastName']);
                
                // if($reg_info['type'] == 'person'){
                //     $this->meta_title = L("personage_rc");
                // }else{
                //     $this->meta_title = L('enterprise_rc');
                // }
                $this->display();
            }
        }

    }

    /**
     * 上传个人证明文件 视图
     */
    public function CertUploadForPerson(){

        // $reg_info = $_SESSION['reg_info'];
        $reg_info = session('reg_info');

        if(!isset($reg_info) || $reg_info['id'] == ''){   //如果缓存里面没数据
             $this->redirect('404');
        }


        $this->display();
    }

    /**
     * 图片上传并保存路径到数据表 方法  / Jie 20151125 改用单个文件上传的方法，因为多文件上传方法会有上传文件丢失的现象
     * Jie 此次修改只准对个人注册的上传，企业注册的上传没有进行更改
     */
    public function UploadForPerson(){

        if(!IS_POST){
            die(L('ill_operation_rc'));
        }

        $plist = $_FILES;
        if ($plist['photo2']['size'] == 0) unset($plist['photo2']);

        //验证此两类图片是否已上传
        if($plist['photo1']['size'] == 0){
            $result = array('status'=>'0', 'info'=>L('Please_upload_rc'));
            $this->ajaxReturn($result,"JSON2");
        }
        // if($plist['photo3']['size'] == 0){
        //     $result = array('status'=>'0', 'info'=>L('Please_upload_2_rc'));
        //     $this->ajaxReturn($result,"JSON2");
        // }

        //文件大小是否在4M之内
        foreach($plist as $item){
            //如果有图片上传
            if(intval($item['size']) > 0){
                if(intval($item['size']) > 4194304){
                    // echo '单个图片的大小不可超过4M';
                    $result = array('status'=>'0', 'info'=>L('The_s_4_rc'));
                    $this->ajaxReturn($result,"JSON2");
                }

                //扩展名是否 in_array array('jpg', 'gif', 'png', 'jpeg')
                $type = explode("/",$item['type']);     //判断文件类型

                if(!in_array($type['1'],array('jpg','gif','png','jpeg'))){
                    // echo '文件必须为图片！';
                    $result = array('status'=>'0', 'info'=>L('Documents_m_rc'));
                    $this->ajaxReturn($result,"JSON2");
                }
            }
        }
        
        // $reg_info = $_SESSION['reg_info'];  //当前id
        $reg_info = session('reg_info');  //当前id

        // 上传文件     
        $info           = array();
        for($i=1;$i<4;$i++){
            if($_FILES['photo'.$i]['size']==0){
                continue;
            }
            $upload           = new \Think\Upload();// 实例化上传类  Man每上传一个 要实例化一次  
            $upload->maxSize  = 4194304;// 设置附件上传大小    
            $upload->exts     = array('jpg', 'gif', 'png', 'jpeg');// 设置附件上传类型    
            $upload->rootPath = WU_ABS_FILE; //设置文件上传保存的根路径
            $upload->savePath = C('PERSON_PIC'); // 设置文件上传的保存路径（相对于根路径）
            $upload->autoSub  = true; //自动子目录保存文件
            $upload->saveName = array('uniqid',mt_rand()); //设置上传文件名
            $info['photo'.$i] =   $upload->uploadOne($_FILES['photo'.$i]);

            //分析是否上传成功，不成功要退出，重新上传
            if(!$info['photo'.$i]){
                $result = array('status'=>'0', 'info'=>L('Upload_file_rc'));
                $this->ajaxReturn($result,"JSON2");
            }
        }

        // 上传成功  
        foreach($info as $key=>$v){
            $arr[$key] = $v['savepath'].$v['savename'];
        }

        $client = $this->client;
        $result = $client->savepic($reg_info['id'],$arr);
        
        //更新注册流程到上传证件
        $res = $client->whichStep(session('reg_info.id'), 4);

        //用户注册流程的认证错误提示
        if($res['status'] == '0'){
            $res['info'] = L($res['code']);
            $this->ajaxReturn($res,"JSON2");
        }

        if($result == 'success'){
            $result = array('status'=>'1', 'info'=>L('Submit_su_1_rc'));
            // $this->ajaxReturn($result,"JSON2");
            echo json_encode($result);die;
        }else{
            $result = array('status'=>'0', 'info'=>L('Submit_er_2_rc'));
            $this->ajaxReturn($result,"JSON2");
        }

    }

    /**
     * 最后确认书 视图
     */
    public function CertificationAuthorizePerson(){

        // $reg_info = $_SESSION['reg_info'];
        $reg_info = session('reg_info');

        if(!isset($reg_info) || $reg_info['id'] == ''){   //如果缓存里面没数据
             $this->redirect('404');
        }

        $client = $this->client;
        $info = $client->getInfo($reg_info['id']);

        $this->assign('info',$info);
        $this->display();

    }
//======================================= 以下属于 企业注册 ============================================//

    /**
     * 企业注册 方法
     */
    public function RegisterCompany(){
        if(!IS_POST){
            die(L('ill_operation_rc'));
        }

        $countryId = I('post.countryId');
        $type      = I('post.type');
        if(!$countryId || !$type || $type != 'company'){
            $this->redirect('404');

        }

        $username    = trim(I('post.username'));                    //用户名

        // $pwd         = strtolower(trim(I('post.pwd')));             //密码
        // $repwd       = strtolower(trim(I('post.repwd')));           //确认密码
        $pwd         = (trim(I('post.pwd')));             //密码
        $repwd       = (trim(I('post.repwd')));           //确认密码

        $email       = trim(I('post.email'));                       //邮箱
        $companyname = trim(I('post.companyname'));                 //公司名字
        $countryId   = trim(I('post.countryId'));               //国家
        $lang        = L('lng');                                 //注册时使用的语言包种类
        $verify      = trim(I('post.verify'));

        //数据校验
        $eamil_match="/^([0-9A-Za-z\\-_\\.]+)@([0-9a-z]+\\.[a-z]{2,3}(\\.[a-z]{2})?)$/i";
        if(empty($username)){
            $result = array('status'=>'0', 'info'=>L('Please_nickname_re'));
            $this->ajaxReturn($result);
        }

        //验证用户名规则
        $msg = get_name_rule($username);
        if($msg != ''){
            $result = array('status'=>'0', 'info'=>$msg);
            $this->ajaxReturn($result);
        }
        
        if(empty($pwd)){
            $result = array('status'=>'0', 'info'=>L('Please_password_re'));
            $this->ajaxReturn($result);
        }

        //验证密码强度
        $msg = get_pwd_strength($pwd);
        if($msg != ''){
            $result = array('status'=>'0', 'info'=>$msg);
            $this->ajaxReturn($result);
        }

        if(empty($repwd)){
            $result = array('status'=>'0', 'info'=>L('Please_en_re'));
            $this->ajaxReturn($result);
        }
        if($pwd != $repwd){
            $result = array('status'=>'0', 'info'=>L('You_two_re'));
            $this->ajaxReturn($result);
        }
        if(empty($email)){
            $result = array('status'=>'0', 'info'=>L('Please_e_re'));
            $this->ajaxReturn($result);
        }
        if(preg_match($eamil_match,$email) != true){
            $result = array('status'=>'0', 'info'=>L('Email_ree_re'));
            $this->ajaxReturn($result);
        }
        if(empty($companyname)){
            $result = array('status'=>'0', 'info'=>L('Please_company_re'));
            $this->ajaxReturn($result);
        }

        $client = $this->client;

        $checkname = $client->checkname($username);
        if($checkname){
            $result = array('status'=>'0', 'info'=>L('User_exists_re'));
            $this->ajaxReturn($result);
        }
        
        $checkemail = $client->checkemail($email);
        if($checkemail){
            $result = array('status'=>'0', 'info'=>L('This_email_re'));
            $this->ajaxReturn($result);
        }

        // 检查验证码  
        if(empty($verify)){
            $result = array('status'=>'0', 'info'=>L('Please_code_re'));
            $this->ajaxReturn($result);
        }
        if(!check_verify($verify)){
            $result = array('status'=>'0', 'info'=>L('V_code_error_re'));
            $this->ajaxReturn($result);
        }

        $result = $client->company($username,$pwd,$email,$companyname,$countryId,$lang,$type);

        if($result['status'] == '1'){

            // 发送邮箱验证码
            $code = rand(100000,999999);

            $reg_info = array(
                'id'          =>$result['id'],
                'companyname' =>$companyname,
                'countryId'   =>$countryId,
                'type'        =>$type,
                'code'        =>md5(base64_encode($code)),
                'ctime'       =>time(),
            );
            session('reg_info',$reg_info);

            $content = create_content($username,$code);

            $this->send_email($email,$content,$username);
            //End

            // $result = array('status'=>'1', 'info'=>'提交成功');
            // $this->ajaxReturn($result);

        }else if($result['status'] == '0'){

            $result = array('status'=>'0', 'info'=>L('Submit_err_re'));
            $this->ajaxReturn($result);
        }else if($result['status'] == '500'){

            $result = array('status'=>'0', 'info'=>L('User_exists_re'));
            $this->ajaxReturn($result);
        }
        
    }

    /**
    *   完善企业资料 方法
    */
    public function CertStep1ForCompany(){

        if(IS_POST){

            // $reg_info = $_SESSION['reg_info'];
            $reg_info = session('reg_info');

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
            //$data['']                     = trim(I('post.PhoneAreaCode'));
            $data['self_phone']             = trim(I('post.Phone'));
            //$data['']                     = trim(I('post.FixedPhoneAreaCode'));
            $data['self_telephone']         = trim(I('post.FixedTelephone'));
            $data['contactsQQ']             = trim(I('post.ContactsQQ'));
            $data['register_time']          = time();
            // $data['mAreaCodeHidden']     = trim(I('post.mAreaCodeHidden'));
            // $data['fAreaCodeHidden']     = trim(I('post.fAreaCodeHidden'));

            $arr = I('post.');  //用数组装载post过来的所有数据

            /* 验证数据是否为空 */
            $chelist = array(
                'province'              => L('provinceMsg'),
                'city'                  => L('cityMsg'),
                'CompanyAddress'        => L('CompanyAddressMsg'),
                'CompanyRepresentative' => L('CompanyRepresentativeMsg'),
                'BusinessLicense'       => L('BusinessLicenseMsg'),
                'VATNumber'             => L('VATNumberMsg'),
                'Address'               => L('AddressMsg'),
                'PostCode'              => L('PostCodeMsg'),
                'CompanyContact'        => L('CompanyContactMsg'),
            );

            //验证字段是否为空
            foreach($chelist as $k=>$dis){
                if(trim($arr[$k]) == ''  || trim($arr[$k]) == '请选择'){
                    $result = array('info'=>$chelist[$k],'status'=>'0');
                    $this->ajaxReturn($result);
                }
            }
            $msg = get_name_rules($data['vat_number']);
            if($msg != ''){
                $result = array('status'=>'0', 'info'=>$msg);
                $this->ajaxReturn($result);
            }

            if($data['self_phone'] == '' && $data['self_telephone'] == ''){
                $result = array('status'=>'0', 'info'=>L('phone_is_required'));
                $this->ajaxReturn($result);
            }

            $client = $this->client;
            $result = $client->registerCompany($reg_info['id'],$data);

            //更新注册流程到完善企业信息
            $res = $client->whichStep(session('reg_info.id'), 3);

            //用户注册流程的认证错误提示
            if($res['status'] == '0'){
                $res['info'] = L($res['code']);
                $this->ajaxReturn($res); 
            }

            if($result == 'success'){

                $result = array('status'=>'1', 'info'=>L('Submit_su_rc'));
                $this->ajaxReturn($result);
            }else{

                $result = array('status'=>'0', 'info'=>L('Submit_err_re'));
                $this->ajaxReturn($result);
            }

        }else{


            // $reg_info = $_SESSION['reg_info'];
            $reg_info = session('reg_info');

            if(!isset($reg_info) || $reg_info['id'] == ''){   //如果缓存里面没数据

                $this->redirect('404');
            }else{
                $this->assign('companyname',$reg_info['companyname']);

                $this->display();
            }
        }

    }

    /**
     * 上传企业证明文件 视图
     */
    public function CertUploadForCompany(){
        // $reg_info = $_SESSION['reg_info'];
        $reg_info = session('reg_info');

        if(!isset($reg_info) || $reg_info['id'] == ''){   //如果缓存里面没数据
            $this->redirect('404');
        }
        $this->display();
    }

    /**
     * 图片上传并保存路径到数据表 方法
     */
    public function UploadForCompany(){
        if(!IS_POST){
            die(L('ill_operation_rc'));
        }
        
        $plist = $_FILES;
        //验证 营业执照 图片是否已上传
        if($plist['photo1']['size'] == 0){
            $result = array('status'=>'0', 'info'=>L('Please_upload_bu_rc'));
            $this->ajaxReturn($result,"JSON2");
        }

        //文件大小是否在4M之内
        foreach($plist as $key=>$item){
            //如果有图片上传
            if(intval($item['size']) > 0){
                if(intval($item['size']) > 4194304){
                    // echo '单个图片的大小不可超过4M';
                    $result = array('status'=>'0', 'info'=>L('The_s_4_rc'));
                    $this->ajaxReturn($result,"JSON2");
                }
                //扩展名是否 in_array array('jpg', 'gif', 'png', 'jpeg')
                $type = explode("/",$item['type']);     //判断文件类型
                if(!in_array($type['1'],array('jpg','gif','png','jpeg'))){
                    // echo '文件必须为图片！';
                    $result = array('status'=>'0', 'info'=>L('Documents_m_rc'));
                    $this->ajaxReturn($result,"JSON2");
                }
            }
        }

        // $reg_info = $_SESSION['reg_info'];  //当前id
        $reg_info = session('reg_info');  //当前id
        // 上传文件     
        $info           = array();
        for($i=1;$i<4;$i++){
            if($_FILES['photo'.$i]['size']==0){
                continue;
            }
            $upload           = new \Think\Upload();// 实例化上传类  Man每上传一个 要实例化一次  
            $upload->maxSize  = 4194304;// 设置附件上传大小    
            $upload->exts     = array('jpg', 'gif', 'png', 'jpeg');// 设置附件上传类型    
            $upload->rootPath = WU_ABS_FILE; //设置文件上传保存的根路径
            $upload->savePath = C('COMPANY_PIC'); // 设置文件上传的保存路径（相对于根路径）
            $upload->autoSub  = true; //自动子目录保存文件
            $upload->saveName = array('uniqid',mt_rand()); //设置上传文件名
            $info['photo'.$i] =   $upload->uploadOne($_FILES['photo'.$i]);

            //分析是否上传成功，不成功要退出，重新上传
            if(!$info['photo'.$i]){
                $result = array('status'=>'0', 'info'=>L('Upload_file_rc'));
                $this->ajaxReturn($result,"JSON2");
            }
        }

        // 上传成功

        foreach($info as $key=>$v){
            $arr[$key] = $v['savepath'].$v['savename'];
        }
        $client = $this->client;
        $result = $client->savepic($reg_info['id'],$arr);
        
        //更新注册流程到上传证件
        $res = $client->whichStep(session('reg_info.id'), 4);
        //用户注册流程的认证错误提示
        if($res['status'] == '0'){
            $res['info'] = L($res['code']);
            $this->ajaxReturn($res,"JSON2");
        }
        if($result == 'success'){
            $result = array('status'=>'1', 'info'=>L('Submit_su_1_rc'));
            $this->ajaxReturn($result);
        }else{
            $result = array('status'=>'0', 'info'=>L('Submit_er_2_rc'));
            $this->ajaxReturn($result);
        }

    }

    /**
     * 最后确认书 视图
     */
    public function CertificationAuthorizeCompany(){

        // $reg_info = $_SESSION['reg_info'];
        $reg_info = session('reg_info');
        if(!isset($reg_info) || $reg_info['id'] == ''){   //如果缓存里面没数据
            $this->redirect('404');
        }

        $client = $this->client;
        $info = $client->getInfo($reg_info['id']);
       // dump($info);exit;
        $this->assign('info',$info);

        $this->display();

    }

    /**
     * 最后确认书 方法
     */
    public function CertificationAuthorize(){

        if(!IS_POST){
            die(L('ill_operation_rc'));
        }

        // $reg_info = $_SESSION['reg_info'];  //当前id
        $reg_info = session('reg_info');  //当前id
        $check    = I('post.check');

        if($check == 'on'){

            $client = $this->client;
            //更新注册流程到完善个人信息
            $res = $client->whichStep(session('reg_info.id'), 5);
            
            //用户注册流程的认证错误提示
            if($res['status'] == '0'){
                $res['info'] = L($res['code']);
                $this->ajaxReturn($res); 
            }

            session('reg_info',null); // 销毁session
            $result = array('status'=>'1', 'info'=>L('Registered_successfully_rc'));
            $this->ajaxReturn($result);

        }else{

            $result = array('status'=>'0', 'info'=>L('Registration_failed_rc'));
            $this->ajaxReturn($result);
        }

        /*  jie 20151119 注销停用  改为后台人工审核资料再判定为激活状态
        if($check == 'on'){
            $data['status'] = '1';

            $client = $this->client;
            $result = $client->makesure($reg_info['id'],$data);

            session('reg_info',null); // 销毁session

            if($result){

                $result = array('status'=>'1', 'info'=>'注册成功');
                $this->ajaxReturn($result);
            }else{

                $result = array('status'=>'0', 'info'=>'注册失败');
                $this->ajaxReturn($result);
            }
        }
         */

    }

//======================================= 这是华丽的分割线 ===============================================//
    /** 
     *  
     * 验证码生成 
     */  
    public function verify_c(){

        verify_c(); 
    }

    /**
     * 异步验证用户名
     * @return [type] [description]
     */
    public function checkname(){
        if (!IS_AJAX) {
            die(L('ill_operation_rc'));
        }else{
            $client = $this->client;
            $username   = trim(I('post.param'));
            
            $data['info']   = '';
            $data['status'] = "n";

            if ($username == '') {

                $data['info']  = L('Tuser_name_m_rc');
                $this->ajaxReturn($data); 

            }else if (get_name_rule($username) != '') {
                
                $data['info']  = L('Please_fill2_rc');
                $this->ajaxReturn($data); 

            }else{

                $result = $client->checkname($username);
                if($result){

                    $data['info']  = L("User_exists_re");
                    $this->ajaxReturn($data);

                }else{
                    $data['info'] = L('have_access_to_rc');
                    $data['status'] = "y";
                    $this->ajaxReturn($data);
                }                
            }

        }
    }

    /**
     * 异步验证邮箱
     * @return [type] [description]
     */
    public function checkemail(){
        if (!IS_AJAX) {
            die(L('ill_operation_rc'));
        }else{

            $client = $this->client;
            $email = I('post.param');
            $eamil_match="/^([0-9A-Za-z\\-_\\.]+)@([0-9a-z]+\\.[a-z]{2,3}(\\.[a-z]{2})?)$/i";
            $data['info']  = '';
            $data['status']  = "n";
            if($email == ''){

                $data['info']  = L('Please_e_re');
                $this->ajaxReturn($data);

            }else if(preg_match($eamil_match,$email) != true){

                $data['info']  = L('Email_ree_re');
                $this->ajaxReturn($data);

            }else{

                $result = $client->checkemail($email);
                if($result){

                    $data['info']  = L('This_email_re');
                    $this->ajaxReturn($data);

                }else{
                    $data['info'] = L('have_access_to_rc');
                    $data['status'] = "y";
                    $this->ajaxReturn($data);
                }                
            }
        }

    }



}