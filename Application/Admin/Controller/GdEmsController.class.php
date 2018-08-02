<?php
/**
 * 广东邮政之一
 * 功能包括： 发货通知，商品报备，批号对数
 * 
 */
namespace Admin\Controller;
use Think\Controller;
class GdEmsController extends AdminbaseController{

    function _initialize() {
        parent::_initialize();
        $client = new \HproseHttpClient(C('RAPIURL').'/GdEms');		//读取、查询操作
        $this->client = $client;		//全局变量

    }

    /**
     * 发货通知
     * @return [type] [description]
     */
    public function index(){

        $keyword    = trim(I('get.keyword'));
        $searchtype = I('get.searchtype');
        $tcid       = (C('Transit_Type.GdEms_Transit')) ? trim(C('Transit_Type.GdEms_Transit')) : '';//20161116 jie   增加 TrandKd = 中转线路.id 的查询
        $starttime  = intval(I('starttime'));
        $endtime    = intval(I('get.endtime'));

        //按查询类型搜索
        if(!empty($keyword) && !empty($searchtype)){
            $map['tn.'.$searchtype] = array('like','%'.$keyword.'%');
        }else{
            $map['tn.send_report'] = array('eq','0');   //只列出尚未执行过预报订单的数据
        }

        if($tcid != ''){
            $map['tn.tcid'] = array('eq',$tcid);//标签A
        }

        //按时间段搜索
        if(!empty($starttime) && !empty($endtime)){
            $starttime = date('Y-m-d H:i:s',$starttime);
            $endtime   = date('Y-m-d H:i:s',$endtime);
            $map['tn.date'] = array('between',$starttime.",".$endtime);

        }else if(!$starttime && $endtime){
            $endtime = date('Y-m-d H:i:s',$endtime);
            $map['tn.date'] = array('elt',$endtime);

        }else if($starttime && !$endtime){
            $starttime = date('Y-m-d H:i:s',$starttime);
            $map['tn.date'] = array('egt',$starttime);
        }

        $client = $this->client;
        $list = $client->getList($map);

        $this->assign('list',$list);
        $this->assign($_GET);
        $this->display();
    }

    /**
     * 预报订单 视图
     * @return [type] [description]
     */
    public function predict(){

        $no = trim(I('mstr'));

        $client = $this->client;
        $res = $client->getInfo($no);

        $this->assign($_GET);
        $this->assign('info',$res);
        $this->display();
    }

    /**
     * 预报订单 方法
     * 思路：与香港E特快相似
     * @return [type] [description]
     */
    public function sendReport(){

        // 20170315 jie 新增操作频率判断
        $now_time = time();
        $se_time  = session('se_time');

        if((intval($now_time) - intval($se_time)) <= 5){
            $result = array('Status'=>'false','Message'=>'操作过于频繁，请5秒后再试');
            $this->ajaxReturn($result);
        }
        // End
       
        //必要数据
        $id       = trim(I('id'));//批次号id
        $no       = trim(I('no'));//批次号
        $searched = trim(I('searched'));//是否通过搜索栏搜索出此批次号
        $number   = trim(I('number'));//空运提单号码/交货车辆号码
        $re_time  = trim(I('re_time'));//预计到达时间
        $country  = strtoupper(trim(I('country')));//起运国国家二字编码 统一转换成大写
        
        //不为空即表示是通过搜索查出此批次号进行当前操作的，则需要验证权限---是否可以再次进行预报订单操作
        if($searched != ''){

            // 验证权限---是否可以再次进行预报订单操作
            if($power['send_again'] != 'on'){
                $result = array('Status'=>'false','Message'=>'没有权限去再次预报此订单');
                $this->ajaxReturn($result);
            }
        }

        // 校验数据
        if($number == '') $this->ajaxReturn(array('Status'=>'false', 'Message'=>'空运提单号码/交货车辆号码不能为空'));
        if($re_time == '') $this->ajaxReturn(array('Status'=>'false', 'Message'=>'预计到达时间不能为空'));
        if($country == '') $this->ajaxReturn(array('Status'=>'false', 'Message'=>'起运国国家二字编码不能为空'));


/*      // $re_time = str_replace('+', ' ', $re_time);
        //正则验证日期时间
        $regexp = "/^(?:(?!0000)[0-9]{4}-(?:(?:0[1-9]|1[0-2])-(?:0[1-9]|1[0-9]|2[0-8])|(?:0[13-9]|1[0-2])-(?:29|30)|(?:0[13578]|1[02])-31)|(?:[0-9]{2}(?:0[48]|[2468][048]|[13579][26])|(?:0[48]|[2468][048]|[13579][26])00)-02-29)\s+([01][0-9]|2[0-3]):[0-5][0-9]:[0-5][0-9]$/";

        if(!preg_match($regexp,$re_time)){
            $result = array('Status'=>'false','Message'=>'时间格式不正确');
            $this->ajaxReturn($result);
        }*/

        $trankd = (C('Transit_Type.GdEms_Transit')) ? trim(C('Transit_Type.GdEms_Transit')) : '';//20170308 jie

        $client = $this->client;
        $res = $client->_report($id, $no, $number, $re_time, $country, $trankd);
        // dump($res);die;
        
        session('se_time',time()); //用于操作频率的间隔判断 20170315 jie

        $this->ajaxReturn($res);

    }

    /**
     * 商品报备 列表
     * @return [type] [description]
     */
    public function apply_goods(){

        $keyword    = trim(I('get.keyword'));       //搜索关键字
        $searchtype = trim(I('get.searchtype'));    //搜索类型
        $status     = trim(I('get.status'));        //海关报备状态
        $state      = trim(I('get.state'));         //EMS报备状态
        $CID        = trim(I('get.CID'));           //公司名称ID

        $p = (I('p')) ? trim(I('p')) : '1';
        $ePage = (I('get.ePage')) ? trim(I('get.ePage')) : C('EPAGE');

        //按查询类型搜索
        if(!empty($keyword) && !empty($searchtype)){
            $where[$searchtype] = array('like','%'.$keyword.'%');
        }

        if($status != ''){
            $where['apply_status'] = $status;//海关报备状态
        }
        if($state != ''){
            $where['ems_status'] = $state;//EMS报备状态
        }
        if($CID != ''){
            $where['CID'] = $CID;//公司名称ID
        }

        $client = $this->client;

        $res = $client->_allpy_count($where,$p,$ePage);
        $count = $res['count'];
        $list  = $res['list'];
        $union  = $res['union'];

        $page = new \Think\Page($count,$ePage); // 实例化分页类 传入总记录数和每页显示的记录数(25)

    $page->setConfig('prev', "上一页");//上一页
    $page->setConfig('next', '下一页');//下一页
    $page->setConfig('first', '首页');//第一页
    $page->setConfig('last', "末页");//最后一页
    $page -> setConfig ( 'theme', '%FIRST% %UP_PAGE% %LINK_PAGE% %DOWN_PAGE% %END% %HEADER%' );
    
        $show = $page->show(); // 分页显示输出
        $this->assign('page',$show);// 赋值分页输出
        
        $this->assign('list',$list);
        $this->assign('union',$union);
        $this->assign($_GET);
        $this->display();

    }

    /**
     * 查看商品报备资料 视图
     * @return [type] [description]
     */
    public function viewGood(){
        $id = I('id');

        $client = $this->client;

        $info = $client->getGoodInfo($id);

        $this->assign('info',$info);
        $this->display();
    }

    /**
     * 修改商品报备资料 视图
     * @return [type] [description]
     */
    public function editGood(){
        $id = I('id');

        $client = $this->client;

        $info = $client->getGoodInfo($id);

        $unit_arr    = unit_code('', true);
        $country_arr = country_code('', true);

        $this->assign('info',$info);
        $this->assign('unit_arr',$unit_arr);
        $this->assign('country_arr',$country_arr);
        $this->display();
    }

    /**
     * 修改商品报备资料 方法
     * @return [type] [description]
     */
    public function editGoodMethod(){
        $arr = I('post.');

        foreach($arr as $key=>$item){
            if(trim($item) == ''){

                // 除了 “第二法定计量单位”，“备注” 不需要验证是否为空，其他必需验证
                if(!in_array($key, array('SecUnit','Notes'))){
                    $backArr = array('state'=>'no', 'msg'=>$key.'不能为空');
                    $this->ajaxReturn($backArr);
                }
            }else{
                $arr[$key] = trim($item);
            }
        }
        $client = $this->client;

        $res = $client->editGoodInfo($arr);

        $this->ajaxReturn($res);
    }

    /**
     * 海关商品报备 方法(单个或多个)
     * @return [type] [description]
     */
    public function applyHG(){
        if(!IS_AJAX){
            echo '非法访问';die;
        }

        ini_set('memory_limit','4088M');
        ini_set('max_execution_time', 0);

        $nos    = trim(I('id'));
        $ids    = explode(',',$nos);
        $scount = count($ids);

        $client = $this->client;

        $et  = 0;// 计算成功获取物流信息的总数
        $msg = '';// 用于记录 返回的失败的单号的各自的单号及其对应的错误信息

        foreach($ids as $k=>$item){

            $res[$k] = $client->_applyHG($item);
// dump($res[$k]);die;
            if($res[$k]['status'] == '1'){
                $et++;
                $msg .= $res[$k]['msg'].' ；';
            }else{
                $msg .= $res[$k]['msg'].' ；'; // 拼接返回的原信息
            }

        }

        // $et  计算成功获取并保存物流信息的总数
        if($et == 0){
            $backXML = array('do'=>'no', 'msg'=>'操作失败 ；'.$msg);
        }else{
            // 批量操作的回复
            if($scount > 1) {
                $backXML = array('do'=>'yes', 'msg'=>'操作成功，请求总数：'.$scount.'个 ；成功执行：'.$et.'个 ，操作失败：'.($scount-$et).'个 。( '.$msg.' )');
            }else{
                $backXML = array('do'=>'yes', 'msg'=>'操作成功');
            }
        }

        $this->ajaxReturn($backXML);

    }

    /**
     * EMS商品报备 方法(单个或多个)
     * @return [type] [description]
     */
    public function applyEMS(){
        if(!IS_AJAX){
            echo '非法访问';die;
        }

        ini_set('memory_limit','4088M');
        ini_set('max_execution_time', 0);

        $nos    = trim(I('id'));
        $ids    = explode(',',$nos);
        $scount = count($ids);

        $client = $this->client;
// dump($ids);die;
        $et  = 0;// 计算成功获取物流信息的总数
        $msg = '';// 用于记录 返回的失败的单号的各自的单号及其对应的错误信息

        foreach($ids as $k=>$item){

            $res[$k] = $client->_applyEMS($item);
// dump($res[$k]);die;
            if($res[$k]['status'] == '1'){
                $et++;
                // $msg .= $res[$k]['msg'].' ；';
            }else{
                $msg .= $res[$k]['msg'].' ；'; // 拼接返回的原信息
            }

        }

        // $et  计算成功获取并保存物流信息的总数
        if($et == 0){
            $backXML = array('do'=>'no', 'msg'=>'操作失败 ；'.$msg);
        }else{
            // 批量操作的回复
            if($scount > 1) {
                $backXML = array('do'=>'yes', 'msg'=>'操作成功，请求总数：'.$scount.'个 ；成功执行：'.$et.'个 ，操作失败：'.($scount-$et).'个 。( '.$msg.' )');
            }else{
                $backXML = array('do'=>'yes', 'msg'=>'操作成功');
            }
        }

        $this->ajaxReturn($backXML);
    }

//=============== 批号对数  完成 ============
    /**
     * 中转跟踪
     * @return [type] [description]
     */
    public function tran_track(){
        
        $type = (C('Transit_Type.GdEms_Transit')) ? C('Transit_Type.GdEms_Transit') : '';

        $list = R('Logarithm/index',array('request'=>true, 'type'=>$type));

        $this->assign('list',$list);
        $this->assign($_GET);
        $this->display('Public:logistics_strack');
    }

    /**
     * 快递跟踪
     * @return [type] [description]
     */
    public function kd_track(){

        $type = (C('Transit_Type.GdEms_Transit')) ? C('Transit_Type.GdEms_Transit') : '';
        
        $list = R('Logarithm/pro_two',array('request'=>true, 'type'=>$type));

        $this->assign('list',$list);
        $this->assign($_GET);
        $this->display('Public:logistics_strack');
    }

}