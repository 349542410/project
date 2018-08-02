<?php
/**
 * 自助打印终端---终端号管理
 */
namespace MkAuto\Controller;
use Think\Controller;
class SelfTerminalController extends Controller {

	public function index(){

        $Language = new \MkAuto\Controller\LanguageController();//载入多语言控制器

        $json = (I('info')) ? trim(I('info')) : '';

        //验证是否 info 为空
        if($json == ''){
            $result = array('state'=>'no','msg'=>'缺少必要的相关资料', 'lng'=>'miss_parameter');
            $Language->get_lang($result);exit;
        }

        $arr = json_decode(urldecode(base64_decode($json)),true);

        if($arr['type'] != 'check_terminal'){
            $result = array('state'=>'no','msg'=>'指令校对失败', 'lng'=>'order_is_wrong');
            $Language->get_lang($result);exit;
        }

        //收到的数据
        $informal  = trim($arr['data']['terminal_name']); //终端名称（非正式，暂时使用）
        $true_name = trim($arr['data']['computer']); //终端真实名称（正式）
        $type      = trim($arr['data']['request_type']); //终端机打开的软件类型（print:打印软件，receive：揽收软件）

        if(!isset($true_name) || $true_name == ''){
        	$result = array('state'=>'no','msg'=>'缺少必要的终端名称', 'lng'=>'miss_parameter');
            $Language->get_lang($result);exit;
        }

        if(!isset($type) || $type == ''){
            $result = array('state'=>'no','msg'=>'缺少必要的请求类型', 'lng'=>'miss_parameter');
            $Language->get_lang($result);exit;
        }

        // 检查请求类型是否符合我方定义的规则
        if(!in_array($type, C('Request_Type'))){
            $result = array('state'=>'no','msg'=>'非法请求类型', 'lng'=>'illegal_request');
            $Language->get_lang($result);exit;
        }

        vendor('Hprose.HproseHttpClient');
        $client = new \HproseHttpClient(C('RAPIURL').'/MkAutoSelfTerminal');

        $r_data['informal']  = $informal;
        $r_data['true_name'] = $true_name;
        $r_data['type']      = $type;

        $result = $client->_index($r_data);

        $Language->get_lang($result);exit;
	}
}