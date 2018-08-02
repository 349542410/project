<?php
/**
 * 自助打印终端---提供会员在ERP软件端
 * 包含：登录、操作订单打印流程、获取账户余额、获取线路价格配置、账户登出
 */

namespace MkAuto\Controller;

class PrintSysController extends PrintSysBaseController
{

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * 公共入口，根据调用的 function 名自动调用对应的 function 进行操作
     * @return [this->type] [调用的 function 名]
     * @return [this->data] [调用函数需要的数据 已经被解析为数组]
     * @return [type] [description]
     */
    function console()
    {
        if (!method_exists($this, $this->type)) {
            $result = array('state' => 'no', 'msg' => $this->type . '函数不存在', 'lng' => 'function_not_exist');
            $this->Language->get_lang($result);
            exit;
        }

        $backArr = call_user_func_array(array($this, $this->type), array($this->data));

        $this->Language->get_lang($backArr);
    }

//============================== 登录 ==================================
    //登录
    public function login($info)
    {
        //验证字段是否为空
        if (trim($info['uname']) == '' || trim($info['ucode']) == '') {
            return array('state' => 'no', 'msg' => '未正确提交相关资料', 'lng' => 'no_info');
        }

        //用户名和密码
        $UserName = trim($info['uname']);
        $UserPwd = trim($info['ucode']);

        $client = $this->client;

        $backInfo = $client->_loginning($UserName, $UserPwd, 'username');//Api数据校验

        $user = $backInfo['user'];
        // $Web_Config = $backInfo['Web_Config'];

        if ($user['state'] == 'no') {

            return $user;

        } else {

            //验证key
            if (!$this->ckey($info['key'], $user['username'], $user['pwd'])) {
                return array('state' => 'no', 'msg' => 'key验证失败', 'lng' => 'verify_failed');
            };

            $author = array(
                'uid' => $user['id'],            //登入的id值
                'username' => $user['username'],        //登入的用户名
                'isLoged' => md5(md5('passed')),
            );

            session('appuser', $author); //session赋值

            // 登录成功
            $token = set_token();
            $data_u['token'] = $token;
            $data_u['time_out'] = time();
            $data_u['status'] = 200;            //token设为正常状态 20170707

            $data_a['user_id'] = $user['id'];

            //保存token相关信息
            $check_print_user = $client->check_print_user($data_a, $data_u, $user, C('Print_Sys_Set.time_out'));

            //如果token已经存在，且尚未过期，则不能登录
            if ($check_print_user == 'already_logined') {
                return array('state' => 'no', 'msg' => '您已通过其他终端登录，请退出后再登陆', 'lng' => 'already_logined');
            }

            if ($check_print_user < 0) {
                return array('state' => 'no', 'msg' => '登录异常请重新登录', 'lng' => 'login_again');
            }

            $return_s = array(
                'uid' => $user['id'],
                'user_name' => $user['username'],
                'user_type' => $user['type'], //用户所属注册类型
                'balance' => $user['amount'], //账户余额
                'sess_id' => session_id(),
                'token' => $token,
                // 'web_config' => $Web_Config, //各线路的价格等配置信息
            );

            return array('state' => 'yes', 'msg' => '验证成功', 'appuser' => $return_s, 'lng' => 'verify_success');

        }

    }

//======================= 打印 =====================

    //查询未打印的订单总数，订单信息，订单相关的商品信息
    public function index($info)
    {
        $user_id = $this->tokenID;//session('appuser.uid');

        $ePage = ($info['ePage']) ? trim($info['ePage']) : 10;//每页显示的数量
        $p = ($info['p']) ? trim($info['p']) : 1;//当前显示页数
        $keyword = ($info['keyword']) ? trim($info['keyword']) : '';//搜索关键字

        $client = $this->client;

        $where = array();
        $where['user_id'] = array('eq', $user_id);
        $where['delete_time'] = array('exp', 'is null');
        $where['print_state'] = array(array('eq', 0), array('eq', 10), 'or'); //打印中，未打印 都列出来

        /*		if(!empty($keyword)){
                    if(is_numeric($keyword)){
                        //暂时只能用收件人手机号或者凭证号进行搜索
                        // if(strlen($keyword) == 11 && preg_match("/13[123569]{1}\d{8}|15[1235689]\d{8}|188\d{8}/", $keyword)){
                        if(strlen($keyword) == 11 || strlen($keyword) == 8){
                            $where['reTel'] = array('eq',$keyword);
                        }else{
                            $where['random_code'] = array('eq',$keyword);
                        }
                    }else if(strpos($keyword, 'Q') == '0' && strlen($keyword) >= 12){
                        $where['order_no'] = array('eq',$keyword);

                    }else{
                        // $where['receiver'] = array('like','%'.$keyword.'%');
                        return array('state'=>'no','msg'=>'查无数据', 'lng'=>'no_data');
                    }

                }*/

        if (!empty($keyword)) {
            $map['reTel'] = array('eq', $keyword); //收件人手机号码
            $map['random_code'] = array('eq', $keyword); //凭证号
            $map['order_no'] = array('eq', $keyword);// Q开头的单号
            $map['_logic'] = 'or';
            $where['_complex'] = $map;
        }

        $count = $client->_count($where);

        //查询结果没有数据
        if ($count == 0) {
            return array('state' => 'no', 'msg' => '查无数据', 'lng' => 'no_data', 'user_id' => $user_id);
        }

        $pages = (ceil(intval($count) / intval($ePage)) == 0) ? 1 : ceil(intval($count) / intval($ePage));// 总页数

        //验证请求的页码是否超出总页数
        if ($p > $pages) {
            return array('state' => 'no', 'msg' => '页码不存在', 'lng' => 'page_not_exist');
        }

        $list = $client->_list($where, $p, $ePage);

        $result = array(
            'num' => $count,
            'ePage' => $ePage,
            'p' => $p,
            'pages' => $pages,
            'data' => $list,
            'keyword' => $keyword
        );

        return $result;

    }

    //获取打印资料
    public function getInfo($info)
    {
        $id = ($info['id']) ? trim($info['id']) : '';

        if ($id == '') {
            return array('state' => 'no', 'msg' => '缺少必要参数', 'lng' => 'lack_paramer');
        }

        $client = $this->client;

        $info = $client->_info($id, C('RMB_Free_Duty'), C('US_TO_RMB_RATE'));
        return $info;
    }

    //页面返回相关资料（含称重重量）
    public function step_one($info)
    {
        $id = $info['id'];        //订单ID
        $weight = $info['weight'];    //称重重量
        $time = date("Y-m-d H:i:s");//$info['time'];    //称重时间
        $terminalCode = $info['terminal_code'];// 终端编号
        if ($id == '' || $weight == '' || $time == '' || empty($terminalCode)) {
            return array('state' => 'no', 'msg' => '缺少必要参数', 'lng' => 'lack_paramer');
        }

        $client = $this->client;

        return $client->_step_one($id, $weight, $time, C('RMB_Free_Duty'), C('US_TO_RMB_RATE'), $terminalCode);

    }

    //接收 扣费 指令，执行订单的支付并扣费
    public function step_two($info)
    {
        $id = $info['id'];        //订单ID
        $user_id = $this->tokenID;//session('appuser.uid'); // 用户ID
        $terminal_code = $info['terminal_code'];// 终端编号  20171030 jie

        if ($id == '') {
            return array('state' => 'no', 'msg' => '缺少必要参数', 'lng' => 'lack_paramer');
        }

        $client = $this->client;

        $res = $client->_step_two($id, $user_id, $terminal_code);

        if ($res['state'] == 'yes') {

            $data = $res['redata'];//支付单号
            $data = array('id' => $id, 'user_id' => $user_id);
            $data['payno'] = $res['redata']['payno'];//支付单号
            $data['paytime'] = $res['redata']['paytime'];//支付时间
            $data['paykind'] = $res['redata']['paykind'];//支付方式
            $data['balance'] = $res['redata']['balance'];//支付方式

            //扣费成功后，返回
            return array('state' => 'yes', 'rdata' => $data, 'msg' => '支付成功', 'lng' => 'pay_success');
        } else {
            return $res;
        }
    }

    //打印成功后，保存打印状态
    public function step_three($info)
    {

        $id = $info['id'];        //订单ID
        $status = $info['status'];    //打印状态
        $time = $info['time'];    //打印时间
        $MKNO = $info['MKNO'];    //MKNO
        $STNO = $info['STNO'];    //STNO
        $terminal_code = $info['terminal_code'];// 终端编号  20171030 jie

        if ($id == '' || $status == '' || $time == '' || $MKNO == '' || $STNO == '') {
            return array('state' => 'no', 'msg' => '缺少必要参数', 'lng' => 'lack_paramer');
        }

        $client = $this->client;

        return $client->_step_three($id, $status, $time, $MKNO, $STNO, $terminal_code);

    }

    //账户登出
    public function login_out($info)
    {
        //验证字段是否为空
        if (trim($info['uname']) == '' || trim($info['dictate']) == '') {
            return array('state' => 'no', 'msg' => '未正确提交相关资料', 'lng' => 'no_info');
        }

        if ($info['dictate'] != md5('user_want_login_out')) {
            return array('state' => 'no', 'msg' => '指令验证失败', 'lng' => 'no_info');
        }

        $client = $this->client;

        $header = get_all_headers();

        return $client->_login_out($header['token']);

    }

//==============================
    // 获取账户余额
    public function get_user_balance($info)
    {
        $user_id = $this->tokenID; // 用户ID

        if ($user_id == '') {
            return array('state' => 'no', 'msg' => '缺少必要参数', 'lng' => 'lack_paramer');
        }

        $client = $this->client;

        $res = $client->_get_user_balance($user_id);

        if ($res !== false) {
            //扣费成功后，返回
            return array('state' => 'yes', 'balance' => $res, 'msg' => '成功获取余额', 'lng' => 'get_balance_success');
        } else {
            return array('state' => 'no', 'msg' => '获取余额失败', 'lng' => 'get_balance_falied');
        }
    }

    // 获取某条（或全部）线路的价格配置信息
    public function get_lines_configure($info)
    {

        //线路id
        $line_id = (isset($info['line_id']) && trim($info['line_id']) != '') ? trim($info['line_id']) : '';

        // 验证是否为数值
        if (!is_numeric($line_id)) {
            return array('state' => 'no', 'msg' => '缺少必要参数', 'lng' => 'lack_paramer');
        }

        // 如果为0，直接当空白处理
        $line_id = ($line_id == '0') ? '' : $line_id;

        $client = $this->client;
        $res = $client->_get_lines_configure($line_id);

        if ($res !== false) {
            //扣费成功后，返回
            return array(
                'state' => 'yes',
                'Web_Config' => $res,
                'msg' => '成功获取线路配置',
                'lng' => 'get_lines_configure_success'
            );
        } else {
            return array('state' => 'no', 'msg' => '获取线路配置失败', 'lng' => 'get_lines_configure_falied');
        }
    }

//========================= 自定义函数 ======================
    //验证密匙key
    private function ckey($str, $uname, $ucode)
    {
        $_md5 = md5(base64_encode($uname . $ucode . C('Print_Sys_Set.MkWl2Key')));
        return $str == $_md5;
    }

}