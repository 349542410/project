<?php
/**
 * 美快官网 前台登陆、用户注册、物流信息查询、会员个人中心  服务器端
 */
namespace Api\Controller;
use Think\Controller\HproseController;
class ServerController extends HproseController{
    protected $crossDomain =    true;
    protected $P3P         =    true;
    protected $get         =    true;
    protected $debug       =    true;

    //更新用户注册流程运作到具体哪一步
    public function whichStep($id, $step){

        //先检查用户信息是否存在
        $check = M('UserList')->where(array('id'=>$id))->find();

        if(!$check){
            return $result = array('status' => '0', 'info' => '账户信息不存在！', 'code' => 'user_not_exist');
        }else{
            $res = M('UserList')->where(array('id'=>$id))->setField('step',$step);

            if($res === false){
                return $result = array('status' => '0', 'info' => '认证出错！请重试或联系客服', 'code' => 'auth_failed');
            }else{
                //如果检查到账号是已经审核过，则可以判断为，现在用户是在重新完善资料，所以需要恢复审核状态为0，以便再次人工审核
                if($check['status'] > 1){
                    M('UserList')->where(array('id'=>$id))->setField('status',0);
                }
                return $result = array('status' => '1', 'info' => 'auth_success');
            }
        }

    }

//========================================= 以下是个人注册部分 =============================================//
    /**
     * 验证个人注册信息
     * @param  [type] $username  [用户名]
     * @param  [type] $pwd       [密码]
     * @param  [type] $email     [邮箱]
     * @param  [type] $FirstName [姓]
     * @param  [type] $LastName  [名]
     * @param  [type] $type      [注册类型]
     */
    public function person($username,$pwd,$email,$FirstName,$LastName,$countryId,$lang,$type){   //模拟服务器端

/*        $checkname = M('UserList')->where(array('username'=>$username))->find();
        if($checkname){
            return $result = array('status' => '500', 'msg' => '用户名已存在');
        }
*/
        $data['username']  = $username;
        $data['pwd']       = md5($pwd);
        $data['email']     = $email;
        $data['FirstName'] = $FirstName;
        $data['LastName']  = $LastName;
        $data['countryId'] = $countryId;
        $data['reg_time']  = time();
        $data['type']      = $type;
        $data['step']      = 1;
        $data['lang']      = $lang;

        if($res = M('UserList')->add($data)){ //把插入的数据所属的id拿过来
            $result = array('status' => '1', 'msg' => 'success','id'=>$res);

        }else{
            $result = array('status' => '0', 'msg' => 'error');

        }

        return $result;
    }

    /**
     * 保存个人详细信息
     * @param  [type] $id   [要存储的id]
     * @param  [type] $data [数据]
     */
    public function registerPerson($id,$data,$datalist){
        //如果数据库中已经有$id这个值
        if(M('UserInfo')->where(array('user_id'=>$id))->getField('user_id')){
            $add = M('UserInfo')->where(array('user_id'=>$id))->save($data);
        }else{//如果没有
            $add = M('UserInfo')->add($data);
        }

         M('UserList')->where(array('id'=>$id))->save($datalist);

        if($add){ //把插入的数据所属的id拿过来

            return $result = 'success';
        }else{

            return $result = 'error';
        }

    }

//===================================== 以下是企业注册部分 ==========================================//

    /**
     * 验证企业注册信息
     * @param  [type] $username    [用户名]
     * @param  [type] $pwd         [密码]
     * @param  [type] $email       [邮箱]
     * @param  [type] $companyname [公司名称]
     * @param  [type] $type        [注册类型]
     */
    public function company($username,$pwd,$email,$companyname,$countryId,$lang,$type){

/*        $checkname = M('UserList')->where(array('username'=>$username))->find();
        if($checkname){
            return $result = array('status' => '500', 'msg' => '用户名已存在');
        }*/

        $data['username']    = $username;
        $data['pwd']         = md5($pwd);
        $data['email']       = $email;
        $data['CompanyName'] = $companyname;
        $data['countryId']   = $countryId;
        $data['reg_time']    = time();
        $data['type']        = $type;
        $data['step']        = 1;
        $data['lang']        = $lang;

        if($res = M('UserList')->add($data)){ //把插入的数据所属的id拿过来
            $result = array('status' => '1', 'msg' => 'success','id'=>$res);

        }else{
            $result = array('status' => '0', 'msg' => 'error');

        }

        return $result;
    }

    /**
     * 保存企业注册信息
     * @param  [type] $id   [要存储的id]
     * @param  [type] $data [数据]
     */
    public function registerCompany($id,$data){
        //如果数据库中已经有$id这个值
        if(M('UserInfo')->where(array('user_id'=>$id))->getField('user_id')){
          $add = M('UserInfo')->where(array('user_id'=>$id))->save($data);
        }else{//如果没有
          $add = M('UserInfo')->add($data);
        }

        if($add){ //把插入的数据所属的id拿过来

            return $result = 'success';
        }else{

            return $result = 'error';
        }

    }

    /**
     * 注册确认 jie 20151119 注销停用
     */
    // public function makesure($id,$data){
    //     $result = M('UserList')->where(array('id'=>$id))->save($data);
    //     return $result;
    // }
//==================================== 以下是登陆验证部分 ============================================//

    /**
     * 登陆验证
     * @param  [type] $name [用户输入的账号名]
     * @param  [type] $pwd  [用户输入的密码]
     * @param  [type] $type [账号登录类型：用户名，邮箱]
     * @param  [type] $out  [判断是否为外部访问的登陆方式]
     */
    public function is_login($name, $pwd, $type='username', $out=false){

        $map[$type] = array('eq',$name);

        $res = M('UserList')->where($map)->find();

        if(!$res){
            $user = array('do' => 'no', 'code' => 'login_01','msg'=>'账户不存在');
            return $user;
        }

        // do 2018-7-30 fa 将限制时间 错误次数 做成配置  key 时间 app.login.wrong_time 次数 app.login.wrong_num 不限制默认 8次 等待15分钟 其中一个为零就不限制
        $appConfig = new ConfigController();
        $waitTime = $appConfig->getConfig('app.login.wrong_time');
        $wrongNum = $appConfig->getConfig('app.login.wrong_num');
        $waitTime = (empty($waitTime) && $waitTime != '0') ? (C('LOGIN_WRONG_PWD_WAIT_TIME')?:15) : (intval($waitTime) <= 0 ? 0 : intval($waitTime));
        $wrongNum = (empty($wrongNum) && $wrongNum != '0') ? (C('LOGIN_WRONG_PWD_NUM')?:8) : (intval($wrongNum) <= 0 ? 0 : intval($wrongNum));

        if($waitTime > 0 && $wrongNum > 0 ){
            //当错误次数达到上限，且恢复时间尚未过去15分钟
            if($res['wrong_num'] >= $wrongNum && ((time() - intval($res['wrong_time'])) <= $waitTime * 60)){
                $user = array('do' => 'no', 'code' => 'login_02','time'=>$waitTime);
                return $user;
            }
        }

        if($res['pwd'] != md5($pwd)){
            if($waitTime > 0 && $wrongNum > 0 ){
                //密码输入错误次数+1
                M('UserList')->where(array('id'=>$res['id']))->setInc('wrong_num',1);
                //错误次数 >= $wrongNum - 2 & < $wrongNum的时候就开始提示
                if(($res['wrong_num']+1) >= $wrongNum - 2 && ($res['wrong_num']+1) < $wrongNum){

                    $user = array('do' => 'no', 'code' => 'login_03', 'num'=>($res['wrong_num']+1),'num_total' => $wrongNum);

                }else if(($res['wrong_num']+1) >= $wrongNum){

                    //错误次数达到5次，则锁定账户
                    M('UserList')->where(array('id'=>$res['id']))->setField('wrong_time',time());//错误次数达到上限，则记录限制时间
                    $user = array('do' => 'no', 'code' => 'login_04','num_total'=>$wrongNum);
                }else{
                    $user = array('do' => 'no', 'code' => 'login_05');
                }
            }else{
                $user = array('do' => 'no', 'code' => 'login_05');
            }

            return $user;
        }

        $data['login_time'] = time();
        $data['wrong_num']  = 0;  //成功登陆，则清空错误次数
        $data['wrong_time'] = '';   //成功登陆，则清空错误次数的时间记录

        M('UserList')->where($map)->save($data);

        $user = array('do' => 'yes', 'msg' => '', 'res' => $res);
        return $user;
    }



    /**
     * 登陆用户列表信息验证
     * @param  [type] $map [查找条件]
     */
    public function userlist($map){
        $userlist = M('UserList')->field('id,type,FirstName,LastName,CompanyName,step,email,username,countryId,status,reg_time')->where($map)->find();
        //将注册日期是2017-08-01 11:29:00 的账号，其 countryId 如果为空的，则补充为美国注册ID
        if($userlist['countryId'] == ''){
            if(intval($userlist['reg_time']) < 1501558210){
                M('UserList')->where(array('id'=>$userlist['id']))->setField('countryId', '1');
                $userlist['countryId'] = 1;
            }
        }
        return $userlist;
    }

    /**
     * 登陆用户详细信息验证  20170621 jie 注销
     * @param  [type] $userlist [用户列表信息]
     * @param  [type] $map2     [查找条件]
     */
/*    public function userinfo($userlist,$map2){
        if($userlist['type'] == 'person'){
            // $userinfo = M('UserInfo')->field('sender_address,self_name,photo1,photo3')->where($map2)->find();
            $userinfo = M('UserInfo u')->field('u.sender_address,u.self_name,u.photo1,u.photo3,l.status')->join('LEFT JOIN mk_user_list l ON l.id = u.user_id')->where($map2)->find();
        }else if($userlist['type'] == 'company'){
            // $userinfo = M('UserInfo')->field('sender_address,self_name,photo1,photo3')->where($map2)->find();
            $userinfo = M('UserInfo u')->field('u.sender_address,u.self_name,u.photo1,u.photo3,l.status')->join('LEFT JOIN mk_user_list l ON l.id = u.user_id')->where($map2)->find();
        }

       return $userinfo;
    }*/

//===================================== 以下是登陆首页（作废） =====================================//
    /**
     * 用户账户总金额
     * @param  [type] $map [description]
     * @return [type]      [description]
     */
    public function amount($map){
        $amount = M('UserList')->where($map)->getfield('amount');
        return $amount;
    }


//===================================== 以下是异步验证注册 ==========================================//
    /**
     * 异步验证用户名
     * @param  [type] $username [description]
     * @return [type]           [description]
     */
    public function checkname($username){
        $result = M('UserList')->where(array('username'=>$username))->find();
        return $result;
    }

    /**
     * 异步验证邮箱
     * @param  [type] $email [description]
     * @return [type]        [description]
     */
    public function checkemail($email){
        $result = M('UserList')->where(array('email'=>$email))->find();
        return $result;
    }

    /**
     * 查询注册信息
     * @param  [type] $id [description]
     * @return [type]     [description]
     */
    public function getInfo($id){
        $res = M('UserList l')->join('LEFT JOIN mk_user_info i ON i.user_id=l.id')->where(array('l.id'=>$id))->find();
        return $res;
    }

    /**
     * 查询id是否存在
     */
    public function authID($pid){
        $res = M('UserList')->where(array('id'=>$pid))->find();
        return $res;
    }
//========================================== 公共部分 ==============================================//

    /**
     * 保存图片路径入数据表
     * @param  [type] $id  [要存储的id]
     * @param  [type] $arr [数据]
     */
    public function savepic($id,$arr){
        //$result = M('user_list')->field('username')->where(array('id'=>$id))->find(); //数组形式
        $res = M('UserInfo')->where(array('user_id'=>$id))->save($arr);   //字符串
        if($res){
            return $result = 'success';
        }else{
            return $result = 'error';
        }
    }


//========================================= 单号状态查询 ===========================================//

    /**
     * 查询单号的物流状态信息
     * @param  [type] $MKNO [查询的单号]
     * @return [type]       [description]
     *
     */
    public function query($order_no,$shAir=0){
        //验证订单号是否MK开头  20170707 jie

        //验证
        // if(preg_match("/^Q\w+$/",$order_no)){
        //     $MKNO = M('TranUlist')->where(array('order_no'=>$order_no))->getField('MKNO');
        //     if(empty($MKNO)){
        //         return '';
        //     }
        // }else if(preg_match("/^MK\w+$/",$order_no)){
        //     $MKNO = $order_no;
        // }else{
        //     $MKNO = M('TranList')->where(array('STNO'=>$order_no))->getField('MKNO');
        //     if(empty($MKNO)){
        //         return '';
        //     }

        // }

        $order_no = trim($order_no);
        if(strlen($order_no) < 9){
            return '';
        }

        $where['STNO'] = $order_no;
        $where['auto_Indent2'] = $order_no;
        $where['_logic'] = 'OR';

        // $where['_string'] = "STNO='" . $order_no . "' or auto_Indent2='" . $order_no . "'";

        if(!preg_match("/^MK\w+$/",$order_no)){
            $MKNO = M('TranList')->where($where)->getField('MKNO');
        }else{
            $MKNO = $order_no;
        }

        if(empty($MKNO)){
            return '';
        }




        //150911 Man add order(id desc),为时间相同时desc不是asc排序
        //$list = M('IlLogs')->order('create_time desc,id desc')->where(array('MKNO'=>$MKNO))->select();
        // 151224按正序读取，方便列出航空信息，最后再倒序返回
        // $shAir 0 不显示航空信息，1显示航空信息，但不显示航空内部信息，2显示所有信息
        $list = M('IlLogs')->order('create_time desc,id desc')->where(array('MKNO'=>$MKNO))->select();

        // return $list;

        //151224增加显示航空信息
        //历遍 $list,当status=20 noid>0时 读取 no.airno->air_logs 倒序排列
        $res    = array();
        foreach ($list as $v) {
            array_push($res, $v);
            if($shAir>0 && $v['status']>19 && $v['noid']>0){
                $airinfo    = M("TransitNo")->field('airno,pnum,bnum,tfrom,tto,apcs,no,date')->where('id='.$v['noid'])->find();
                if($airinfo){
                    $airno      = trim($airinfo['airno']);
                    if($airno<>'' && $airno<>'0'){
                        $map    = array('airno'=>$airno);
                        $air    = M("AirLogs")->field('concat(ctime," *") as create_time,remark as content')->where($map)->order('ctime,id')->select();
                        //array_push($res, $air);
                        //$res   += $air;
                        if($air){
                            $res    = array_merge($res,$air);
                        }
                        //
                        $ainfo  = '';
                        $ahead  = '';
                        if($shAir>1){
                            $tmp    = ($airinfo['apcs']-$airinfo['bnum']==0)?$airinfo['apcs']:($airinfo['apcs'].'/'.$airinfo['bnum']);
                            $pdate  = substr($airinfo['date'],0,10);
                            $airurl = "$airno&nbsp;<a target='_blank' href='http://monitor.geodiswilson.com/gensearch/search.aspx?DocumentType=HWB&DocumentNumber=$airno'>[查看]</a>";
                            $ainfo  = "批次号:$airinfo[no]&nbsp;(<u>$pdate</u>)<br/>航空号:$airurl&nbsp;板数:$tmp<br/>发出:$airinfo[tfrom]&nbsp;<br/>预计:$airinfo[tto]";
                            $ahead  = '以下内容仅ERP可见,航空物流暂未在官网显示';
                            //$res    = array_merge($res,array(array('create_time'=>'航空信息','content'=>$ainfo)));
                        }else if($air){  //Man 20160115没有航空记录时不显示
                            $ainfo  = '<span style="font-size:13px;line-height:120%;color:#afafaf">* 物流状态发生时间横跨多个时区，虽已按特定算法力求准确地转换为中国时间，亦难免有误。<span><br/><br/>';
                            //$ahead  = '说明:本面单为内部测试数据,未必准确。<br/>以下英文内容由航空公司提供';
                            $ahead  = '说明:以下英文内容由航空物流提供';
                        }
                        if($ainfo<>''){
                            $res    = array_merge($res,array(array('create_time'=>$ahead,'content'=>$ainfo)));
                        }

                        //array_push($res, $air);
                    }
                }

            }
        }

        //return $list;
        return array_reverse($res); //数组倒过来
    }

//============================== 个人中心  ===============================
    /**
     * 验证当前登录会员的账号是否通过审核，只有审核通过的会员才能下单 Jie 20151130
     * @param  [type] $mkuser [description]
     * @return [type]         [description]
     */
    public function authInfo($mkuser){
        $res = M('UserList')->where(array('id'=>$mkuser['uid'],'username'=>$mkuser['username']))->getField('status');
        return $res;
    }

    /**
     * 获取中转线路列表
     * @return [type] [description]
     */
    public function getline(){
        $list = M('TransitCenter')->where(array('status'=>'1','optional'=>'1'))->order('id desc')->select();
        return $list;
    }

    /**
     * 获取所有中转线路列表
     * @return [type] [description]
     */
    public function all_line(){
        $list = M('TransitCenter')->order('id desc')->select();
        return $list;
    }
}