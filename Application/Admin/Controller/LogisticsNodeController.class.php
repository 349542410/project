<?php
/**
 * 美快优选3(中通)
 * 功能包括： 各个节点推送，补录菜鸟单号，补录航空号
 * 
 */
namespace Admin\Controller;
use Think\Controller;
class LogisticsNodeController extends AdminbaseController{

    function _initialize() {
        parent::_initialize();
        ini_set('memory_limit','500M');
        set_time_limit(0);
        vendor('Hprose.HproseHttpClient');
        $client = new \HproseHttpClient(C('RAPIURL').'/AdminLogisticsNode');     //读取、查询操作
        $client -> setTimeout(1200000);//设置 HproseHttpClient 超时时间

        $this->client = $client;        //全局变量
    }

	public function index(){

        $MKBc3_Transit = C('Logistics_Node_Set.MKBc3_Transit');
        $tcid          = ($MKBc3_Transit) ? $MKBc3_Transit : '';//20161116 jie   增加 TrandKd = 中转线路.id 的查询
        $keyword       = trim(I('get.keyword'));
        $searchtype    = I('get.searchtype');
        $starttime     = intval(I('starttime'));
        $endtime       = intval(I('get.endtime'));
        $tid           = (I('get.tid')) ? trim(I('get.tid')) : '';//搜索传入的ID

        //按查询类型搜索
        if(!empty($keyword) && !empty($searchtype)){
            $map['tn.'.$searchtype] = array('like','%'.$keyword.'%');
        }

        if($tcid != ''){
            // Transit_Typ.MKBc3_Transit 比较特殊，是同时设置了多条线路，有别于其他设置
            $tcids = explode(",",$tcid);
            $map['tn.tcid'] = array('in',$tcids);//配置中定义的id集
        }

        //如果是搜索请求，则使用此条件
        if($tid != ''){
            $map['tn.tcid'] = array('eq',$tid);//搜索栏传入的线路ID
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
        $res = $client->_index($map, $tcids);

        $ZT_Node_Point    = C('ZT_Node_Point'); // 需要推送的节点  数组
        $ZT_Node_Point_CN = array_values($ZT_Node_Point);

        $this->assign('ZT_Node_Point_CN', $ZT_Node_Point_CN);//数据
        $this->assign('list', $res['list']);//数据
        $this->assign('center_list', $res['center_list']);//搜索栏的 线路列表
        $this->assign('rest_num', $res['rest_num']);// 中通单号 剩余数量
        $this->assign($_GET);
        $this->display();
	}

	/**
     * 节点推送
     * 1.节点之间有时间限制，请看 C('ZT_Node_Point_Time_Limit')
     * @return [type] [description]
     */
	public function toPush(){

        $noid    = I('noid');//批次号
        
        $arr     = C('ZT_Node_Point'); // 需要推送的节点  数组
        $timeout = C('ZT_Node_Point_Time_Limit');//时间间隔，数组
        
        $arr = array_keys($arr);
        $client = $this->client;
        $res = $client->_node_push($noid, $arr, $timeout, session('admin')['adid']);

        $this->ajaxReturn($res);

	}

    /**
     * 补录航空号 方法
     */
    public function add_method(){
        if(IS_AJAX){
            $noid  = trim(I('noid'));
            $airno = trim(I('fname'));

            if($noid == '') $this->ajaxReturn(array('state'=>'no','msg'=>'参数不能空'));
            if($airno == '') $this->ajaxReturn(array('state'=>'no','msg'=>'航空号不能为空'));

            $client = $this->client;

            $res = $client->_add_method($noid, $airno);

            $this->ajaxReturn($res);

        }else{
            die('参数错误');
        }
    }

    // 刷新 中通号剩余数量
    public function reloadNums(){
        $client = $this->client;

        $res = $client->_reloadNums();

        $this->ajaxReturn(array('nums'=>$res));
    }
}