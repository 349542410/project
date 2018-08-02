<?php
/**
 * 会员资料 客户端
 */
namespace Admin\Controller;
use Think\Controller;
class MemberController extends AdminbaseController {

    function _initialize() {
        parent::_initialize();
        $client = new \HproseHttpClient(C('RAPIURL').'/Member');        //读取、查询操作
        $this->client = $client;    //全局变量

    }

	/**
	 * 会员资料
	 */
	public function index(){
        $keyword    = trim(I('get.keyword'));
        $searchtype = I('get.searchtype');
        $type       = I('get.type');
        $status     = I('get.status');
        $minamount  = floatval(trim(I('get.minamount')));//trim(I('get.minamount')));        
        $maxamount  = floatval(trim(I('get.maxamount')));  
        
        //分页显示的数量
        $p = (I('p')) ? trim(I('p')) : '1';
        $ePage = (I('get.ePage')) ? trim(I('get.ePage')) : C('EPAGE');

        $this->assign($_GET);
        $this->assign('ePage',$ePage);

        //按用户名、联系人、手机号码进行搜索
        if(!empty($keyword) && !empty($searchtype)){
            $where[$searchtype]=array('like','%'.$keyword.'%');
        }

        //按注册类型搜索
        if($type)$where['type']=$type;
        //按状态搜索
        if($status != ''){
            $where['status']=$status;
        }

        //搜索金额值
        if($minamount && $maxamount){
            $where['amount'] = array('between',array($minamount,$maxamount));
        }else if(!$minamount && $maxamount){
            $where['amount'] = array('elt',$maxamount);
        }else if($minamount && !$maxamount){
            $where['amount'] = array('egt',$minamount);
        }

        $client = $this->client;

        $res = $client->count($where,$p,$ePage);
        $count = $res['count'];
        $list  = $res['list'];

        if(!empty($list)){
            $appConfig = new \Api\Controller\ConfigController();
            $waitTime = $appConfig->getConfig('app.login.wrong_time');
            $wrongNum = $appConfig->getConfig('app.login.wrong_num');
            $waitTime = (empty($waitTime) && $waitTime != '0') ? (C('LOGIN_WRONG_PWD_WAIT_TIME')?:15) : (intval($waitTime) <= 0 ? 0 : intval($waitTime));
            $wrongNum = (empty($wrongNum) && $wrongNum != '0') ? (C('LOGIN_WRONG_PWD_NUM')?:8) : (intval($wrongNum) <= 0 ? 0 : intval($wrongNum));

            foreach ($list as $key=>$val){
                if($waitTime > 0 && $wrongNum>0){
                    if($val['wrong_num'] >= $wrongNum && (time() - $val['wrong_time']) <= $waitTime * 60){
                        $list[$key]['is_lock'] = 1;
                    }else{
                        $list[$key]['is_lock'] = 0;
                    }
                }else{
                    $list[$key]['is_lock'] = 0;
                }
            }
        }

        $page = new \Think\Page($count,$ePage); // 实例化分页类 传入总记录数和每页显示的记录数(20)

        $page->setConfig('prev', "上一页");//上一页
        $page->setConfig('next', '下一页');//下一页
        $page->setConfig('first', '首页');//第一页
        $page->setConfig('last', "末页");//最后一页
        $page -> setConfig ( 'theme', '%FIRST% %UP_PAGE% %LINK_PAGE% %DOWN_PAGE% %END% %HEADER%' );

        $show = $page->show(); // 分页显示输出
        $this->assign('page',$show);// 赋值分页输出

		$this->assign('list',$list);

		$this->display();
	}

    /**
     * 解锁用户
     */
	public function unLock()
    {
        $user_id = I('get.user_id');
        $res = $this->client->unLock($user_id);
        $result = $res !== false ? array('state' => 'yes','msg'=>'解锁成功') : array('state' =>'no','msg'=>'解锁失败');
        return  $this->ajaxReturn($result);
    }
	/**
	 * 编辑 视图
	 * @return [type] [description]
	 */
	public function edit(){

        $id   = I('get.id');
        $type = I('get.type');

        $client = $this->client;
        $result = $client->edit($id);

        $user = $result[0];

        //如果不存在
        if(!$user){
            $this->display('Public/msg');
            exit;
        }

		$this->assign('user',$user);

		if($type == 'person'){
			$this->display('editPerson');
		}else{
			$this->display('editCompany');
		}

	}

	/**
	 * 更新个人信息
	 * @return [type] [description]
	 */
	public function updatePerson(){
        $id = trim(I('post.id'));
        if(!IS_POST || empty($id)){
            die('非法操作');
        }



        $client = $this->client;
		$getone = $client->getone($id);

        $list['pwd']                       = !empty(I('post.pwd')) ? md5(trim(I('post.pwd', ' '))) : $getone['pwd'];
        $list['email']                     = trim(I('post.email'));
        $list['FirstName']                 = trim(I('post.FirstName'));
        $list['LastName']                  = trim(I('post.LastName'));
        $list['decimal_recharge_status']    = I('post.rstatus');
        
        $info['country']                   = trim(I('country'));
        $info['user_id']                   = $id;
        $info['province']                  = trim(I('province'));        //这还没有数据，待添加
        $info['city']                      = trim(I('city'));                //这还没有数据，待添加
        $info['certificate_type']          = trim(I('IdType'));
        $info['certificate_number']        = trim(I('IdNumber'));
        $info['sender_address']            = trim(I('address'));
        $info['sender_zipcode']            = trim(I('postCode'));
        // $info['PhoneAreaCode']          = trim(I('PhoneAreaCode'));
        $info['sender_phone']              = trim(I('Phone'));
        //  $info['FixedPhoneAreaCode']    = trim(I('FixedPhoneAreaCode'));
        $info['sender_telephone']          = trim(I('FixedTelephone'));
        $info['resident_country']          = trim(I('ResidentContry'));
        $info['self_name']                 = trim(I('ContactsUserName'));
        $info['self_address']              = trim(I('ContactsAddress'));
        $info['zipcode']                   = trim(I('ContactsPostCode'));
        // $data['ContactsMobileAreaCode'] = trim(I('ContactsMobileAreaCode'));
        $info['self_phone']                = trim(I('ContactsMobilePhone'));
        // $data['ContactsFixedAreaCode']  = trim(I('ContactsFixedAreaCode'));
        $info['self_telephone']            = trim(I('ContactsFixedPhone'));
        $info['contactsQQ']                = trim(I('ContactsQQ'));
        // $info['change_time']               = time();  // 数据表上已设置自动更新

        $result = $client->_update($id,$list,$info);

        $this->ajaxReturn($result);

	}

	/**
	 * 更新企业信息
	 * @return [type] [description]
	 */
	public function updateCompany(){

        if(!IS_POST){
            die('非法操作');
        }

		$id = trim(I('post.id'));

        $client = $this->client;
		$getone = $client->getone($id);
        
        $list['pwd']                    = trim(I('post.pwd')) ? md5(trim(I('post.pwd'))) : $getone['pwd'];
        $list['email']                  = trim(I('post.email'));
        $list['CompanyName']            = trim(I('post.companyname'));
        
        $info['country']                = trim(I('country'));
        // $info['companyname']         = trim(I('CompanyName'));
        $info['user_id']                = $id;
        $info['province']               = trim(I('province'));        //这还没有数据，待添加
        $info['city']                   = trim(I('city'));                //这还没有数据，待添加
        $info['company_address']        = trim(I('CompanyAddress'));
        $info['company_representative'] = trim(I('CompanyRepresentative'));
        $info['business_license']       = trim(I('BusinessLicense'));
        $info['vat_number']             = trim(I('VATNumber'));
        $info['sender_address']         = trim(I('Address'));
        $info['sender_zipcode']         = trim(I('PostCode'));
        $info['self_name']              = trim(I('CompanyContact'));
        //$data['']                     = trim(I('PhoneAreaCode'));
        $info['self_phone']             = trim(I('Phone'));
        //$data['']                     = trim(I('FixedPhoneAreaCode'));
        $info['self_telephone']         = trim(I('FixedTelephone'));
        $info['contactsQQ']             = trim(I('ContactsQQ'));
        // $info['change_time']            = time();

        $result = $client->_update($id,$list,$info);

        $this->ajaxReturn($result);

	}

    /**
     * 审核视图(个人或企业)
     * @return [type] [description]
     */
    public function show(){

        $id   = I('get.id');
        $type = I('get.type');

        $client = $this->client;
        $result = $client->edit($id);

        $user = $result[0];
        //print_r($user);
        //exit;

        //如果不存在
        if(!$user){
            $this->display('Public/msg');
            exit;
        }
        $this->assign('user',$user);

        $exlist = C('WrongType');

        self::assign('exlist',$exlist);
        if($type == 'person'){

            $this->display('showPerson');
        }else{

            $this->display('showCompany');
        }
    }


	/**
	 * 单个删除 方法
	 * @return [type] [description]
	 */
	public function delete(){

		if(IS_AJAX){
			$id = I('post.id');

            $client = $this->client;
			$result = $client->delete($id);

            $this->ajaxReturn($result);

		}else{
            die('非法操作');
        }
	}


    /**
     * 20151119 Jie
     * 后台系统人工审核通过 方法
     */
    public function examine(){
        if(IS_AJAX){

            $id = I('post.pid');

            $client = $this->client;
            $res = $client->_examine($id);

            if($res['status'] == 'yes'){

                $check = $res['data'];

                $db = new \Admin\Controller\MemberExamineController();

                $str = ($check['lang'] == 'zh-cn') ? $db->arr['examine_success_content_cn'] : $db->arr['examine_success_content_en'];
                $etitle = ($check['lang'] == 'zh-cn') ? '资料审核已通过' : 'Information has passed verification';
                $content = create_success_content($str, $check['username']);  //创建邮件内容

                // 发送邮件
                $result = $this->send_email($check['email'], $content, $etitle, $check['lang']);

                $this->ajaxReturn($result);
            }else{

                $this->ajaxReturn($res);
            }

        }else{
            echo '非法操作';
        }
    }

    /**
     * 20151120 Jie
     * 后台系统人工审核不通过  方法
     * @param  [type] $id [会员id]
     * @param  [type] $msg [邮件内容]
     * @param  [type] $mid [审核不通过的类型编号  从2至9]
     * @return [type] [description]
     */
    public function not_examine(){
        if(!IS_AJAX){
            echo '非法操作';
        }

        $id  = trim(I('post.pid')); //会员id
        $mid = trim(I('post.mid')); //接收到的审核不通过的类型编号
/*        $person  = C('ExPerson');
        $company = C('ExCompany');
        $exlist = $person + $company;   //数组合拼*/
        $WrongType = C('WrongType');

        $tips = $WrongType[$mid] ? $WrongType[$mid] : '资料未完善';
        $msg = '“'.$tips.'” ';

        $client = $this->client;
        $res = $client->_not_examine($id,$msg,$mid);

        if($res['status'] == 'yes'){

            $check = $res['data'];
            
            $db = new \Admin\Controller\MemberExamineController();

            $str = ($check['lang'] == 'zh-cn') ? $db->arr['examine_fail_content_cn'] : $db->arr['examine_fail_content_en'];
            $etitle = ($check['lang'] == 'zh-cn') ? '资料审核未通过' : 'Information has not passed verification';
            $content = create_fail_content($str, $check['username'], $msg);  //创建邮件内容

            // 发送邮件
            $result = $this->send_email($check['email'], $content, $etitle, $check['lang']);

            $this->ajaxReturn($result);
        }else{

            $this->ajaxReturn($res);
        }

    }


    /**
     * 邮件发送  请注意发送邮件的邮箱是否开启了SMTP功能，未开启则无法使用第三方发送功能
     * @param  [type] $receiver [收件人]
     * @param  [type] $content  [发送的内容]
     * @param  [type] $address  [收件邮箱]
     */
    protected function send_email($receiver, $content, $etitle, $lang){
        $TITLE    = ($lang == 'zh-cn') ? '[审核结果] ' : '[Audit Result] ';
        $FROMNAME = ($lang == 'zh-cn') ? '美快国际物流' : 'Meiquick International Logistics';

        $args = array(
            'to' => array(
                $receiver,     //收件人地址，可填写多个
            ),
            // 'CC' => array(
            //     'xxx@mial.com',        //抄送，可选
            // ),
            // 'BCC' => array(
            //                             //密送，可选
            // ),
            'FromName'   => $FROMNAME,    //发件人姓名，可选
            'title'      => $TITLE.$etitle,
            'content'    => $content,
            'type'       => 'html',
            'attachment' => array(
                // dirname(__FILE__) . '/attachment/001.txt',      //附件的路径，可选
                // dirname(__FILE__) . '/attachment/002.txt',
            ),
        );

        $phpmail = new \Lib11\PHPMailer\PHPMailerTools();
        $result = $phpmail->sendMail($args);
        //发送
        if($result['success'] === false) {
            return array('status'=>'0','info'=>"操作成功，通知邮件发送失败");

        } else {
            return array('status'=>'1', 'info'=>'操作成功，通知邮件已发送');
        }

        // $EMAIL_SET = C('EMAIL_SET');

        // $mail  = new \Libm\Common\PHPMailer;
        // $mail->Host     = $EMAIL_SET['HOST']; //SMTP服务器
        // $mail->Port     = $EMAIL_SET['PORT'];  //邮件发送端口
        // $mail->Username = $EMAIL_SET['EMAIL_USER'];  //你的邮箱
        // $mail->Password = $EMAIL_SET['EMAIL_PWD'];  //你的密码
        // $mail->From     = $EMAIL_SET['EMAIL_USER'];     //发件人地址（也就是你的邮箱）
        // $mail->Subject  = $TITLE.$etitle;      //邮件标题
        // $mail->FromName = $FROMNAME;       //发件人姓名
        
        // $mail->Body     = $content;
        // $mail->Address  = $address;//收件人（地址）
        // $mail->Receiver = $receiver;//收件人（昵称）

        // //发送
        // if(!$mail->Send()) {
        //     $result = array('status'=>'1','info'=>"操作成功，通知邮件发送失败");

        // } else {
        //     $result = array('status'=>'1', 'info'=>'操作成功，通知邮件已发送');

        //     //echo "邮件已发送！";
        // }
        // return $result;

    }

//====================== 设置 会员优惠 ======================
    /**
     * 某个会员各个线路优惠列表
     * @return [type] [description]
     */
    public function member_of_discount(){

        $user_id = I('id'); //会员ID
        $client = $this->client;
        $res = $client->_member_of_discount($user_id);

        $this->assign('list',$res);
        $this->display();
    }

    /**
     * 设置某个会员各个线路优惠
     * @return [type] [description]
     */
    public function edit_discount(){
        // dump(I('post.'));
        $id       = trim(I('id'));//线路ID
        $user_id  = trim(I('uid'));//会员ID
        $name     = trim(I('name'));//编辑的字段
        $val      = trim(I('val'));//编辑的值
        $operator = session('admin')['adid'];//操作人ID

        $ex = explode('.', $val);
        if(strlen($ex[1]) > 2){
            $this->ajaxReturn(array('state'=>'no','msg'=>'只可输入2位小数'));exit;
        }
        $client = $this->client;
        $res = $client->_edit_discount($id, $user_id, $name, $val, $operator);

        $this->ajaxReturn($res);
    }
}