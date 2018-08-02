<?php
/**
 * 广东邮政之二
 * 功能包括： 支付通知，报关，报关状态
 * 
 */
namespace Admin\Controller;
use Think\Controller;
class GdEmsPtwoController extends AdminbaseController{

    function _initialize() {
        parent::_initialize();
        $client = new \HproseHttpClient(C('RAPIURL').'/GdEmsPtwo');     //读取、查询操作
        $this->client = $client;        //全局变量
    }

//====================================================
// 支付通知 ok
//====================================================
    /**
     * 支付通知 以批次号形式列出数据
     * @return [type] [description]
     */
    public function send_payment(){

        $keyword    = trim(I('get.keyword'));
        $searchtype = I('get.searchtype');
        $tcid       = (C('Transit_Type.GdEms_Transit')) ? trim(C('Transit_Type.GdEms_Transit')) : '';//
        $starttime  = intval(I('starttime'));
        $endtime    = intval(I('get.endtime'));

        //按查询类型搜索
        if(!empty($keyword) && !empty($searchtype)){
            $map['tn.'.$searchtype] = array('like','%'.$keyword.'%');
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
        $list = $client->paymentList($map);

        $this->assign('list',$list);

        $this->assign($_GET);
        $this->display();
    }

    /**
     * 支付通知  以批次号形式执行推送
     * @return [type] [description]
     */
    public function post_payInfo(){

        $id = I('id');

        // 批次号所属的线路id
        $tcid = (C('Transit_Type.GdEms_Transit')) ? trim(C('Transit_Type.GdEms_Transit')) : '';

        $client = $this->client;

        $res = $client->_sendlist($id, $tcid);

        $this->ajaxReturn($res);
    }

    /**
     * payList页面  视图  数据查询
     * @return [type] [description]
     */
    public function payList(){
        $p = I('get.p')?I('get.p'):1;   //当前页数，如果没有则默认显示第一页

        $stype  = I('get.kind')?I('get.kind'):""; //done 已发送； not 未发送
        $noid   = I('get.nid')?I('get.nid'):""; //tran_list.noid

        $client = $this->client;
        $res = $client->_payList($noid, $stype, $p);

        $this->ilstarr = C('ilstarr');

        //$ilstarri中的0-9 是与 $ilstarr 中的key对应
        $this->ilstarri = C('ilstarri');

        $this->assign('list',$res['0']);    //数据列表
        // dump($res['0']);die;
        $page = new \Think\Page($res['1'],30); // 实例化分页类 传入总记录数和每页显示的记录数(20)
        $page->setConfig('prev', "上一页");//上一页
        $page->setConfig('next', '下一页');//下一页
        $page->setConfig('first', '首页');//第一页
        $page->setConfig('last', "末页");//最后一页
        $page->setConfig ( 'theme', '%FIRST% %UP_PAGE% %LINK_PAGE% %DOWN_PAGE% %END% %HEADER%' );
            
        $show = $page->show(); // 分页显示输出
        $this->assign('page',$show);// 赋值分页输出
        $this->assign('mstr',$res['2']);// 
        $this->assign('record_list',$res['3']);// 
        $this->assign('sel_list',C('LOGARTHM_SELECT')); //20160511 Jie
        $this->assign('send_pay_list',C('SendPayStateList')); //20160511 Jie

        $this->assign('nid',trim(I('get.nid'))); //20170119 jie
        $limit = $page->firstRow.','.$page->listRows;

        $this->display();
    }

    /**
     * 支付通知  分别是 已发送/未发送 的 单个或多个订单 进行推送
     * @return [type] [description]
     */
    public function post_pay(){
        if(!IS_AJAX){
            echo '非法访问';die;
        }

        ini_set('memory_limit','4088M');
        ini_set('max_execution_time', 0);

        $nos    = trim(I('num'));   //tran_list.id集
        $nid    = trim(I('nid'));   //transit_no.id
        $tcid   = (C('Transit_Type.GdEms_Transit')) ? trim(C('Transit_Type.GdEms_Transit')) : '';//transit_center.id

        $client = $this->client;

        $res = $client->_post_pay($nos, $nid, $tcid);

        $this->ajaxReturn($res);
    }

//====================================================
// 报关(订单报备)
//====================================================
    /**
     * 报关 列表
     * @return [type] [description]
     */
    public function apply_customs(){

        $keyword    = trim(I('get.keyword'));
        $searchtype = I('get.searchtype');
        $tcid       = (C('Transit_Type.GdEms_Transit')) ? trim(C('Transit_Type.GdEms_Transit')) : '';//20161116 jie   增加 TrandKd = 中转线路.id 的查询
        $starttime  = intval(I('starttime'));
        $endtime    = intval(I('get.endtime'));

        //按查询类型搜索
        if(!empty($keyword) && !empty($searchtype)){
            $map['tn.'.$searchtype] = array('like','%'.$keyword.'%');
        }

        // 必须符合这两个状态条件
        $map['tn.send_report'] = array('eq','1'); //1 已执行发货通知
        $map['tn.pay_report'] = array('eq','1'); //1 已执行支付通知

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
        $list = $client->customsList($map);
// dump($list);
        $this->assign('list',$list);
        $this->assign($_GET);
        $this->display();
    }

    /**
     * 获取报关状态
     * @return [type] [description]
     */
    public function getStatus(){
        $id = I('id');

        // 批次号所属的线路id
        $tcid = (C('Transit_Type.GdEms_Transit')) ? trim(C('Transit_Type.GdEms_Transit')) : '';

        $client = $this->client;

        $res = $client->_getStatus($id, $tcid);

        // dump($res);die;

        $this->ajaxReturn($res);
    }

    /**
     * orderList页面  视图  数据查询
     * @return [type] [description]
     */
    public function orderList(){
        $p = I('get.p')?I('get.p'):1;   //当前页数，如果没有则默认显示第一页

        $stype  = I('get.kind')?I('get.kind'):""; //done 已发送； not 未发送
        $noid   = I('get.nid')?I('get.nid'):""; //tran_list.noid

        $client = $this->client;
        $res = $client->_orderList($noid, $stype, $p);

        $this->ilstarr = C('ilstarr');

        //$ilstarri中的0-9 是与 $ilstarr 中的key对应
        $this->ilstarri = C('ilstarri');

        $this->assign('list',$res['0']);    //数据列表
        // dump($res['0']);die;
        $page = new \Think\Page($res['1'],30); // 实例化分页类 传入总记录数和每页显示的记录数(20)
        $page->setConfig('prev', "上一页");//上一页
        $page->setConfig('next', '下一页');//下一页
        $page->setConfig('first', '首页');//第一页
        $page->setConfig('last', "末页");//最后一页
        $page->setConfig ( 'theme', '%FIRST% %UP_PAGE% %LINK_PAGE% %DOWN_PAGE% %END% %HEADER%' );
            
        $show = $page->show(); // 分页显示输出
        $this->assign('page',$show);// 赋值分页输出
        $this->assign('mstr',$res['2']);// 
        $this->assign('record_list',$res['3']);// 
        $this->assign('sel_list',C('LOGARTHM_SELECT')); //20160511 Jie
        $this->assign('custom_list',C('CustomStateList')); //20160511 Jie

        $this->assign('nid',trim(I('get.nid'))); //20170119 jie

        $this->display();
    }

    /**
     * 报关  分别是 已发送/未发送 的 单个或多个订单 进行推送
     * @return [type] [description]
     */
    public function post_order(){
        if(!IS_AJAX){
            echo '非法访问';die;
        }

        ini_set('memory_limit','4088M');
        ini_set('max_execution_time', 0);

        $nos    = trim(I('num'));   //tran_list.id集
        $nid    = trim(I('nid'));   //transit_no.id
        $tcid   = (C('Transit_Type.GdEms_Transit')) ? trim(C('Transit_Type.GdEms_Transit')) : '';//transit_center.id

        $client = $this->client;

        $res = $client->_post_order($nos, $nid, $tcid);
        $this->ajaxReturn($res);
    }

//====================================================
// 报关状态
//====================================================
    /**
     * 报关状态 列表
     * @return [type] [description]
     */
    public function customs_status(){
        
        $p = (I('p')) ? trim(I('p')) : '1';
        $ePage = (I('get.ePage')) ? trim(I('get.ePage')) : C('EPAGE');

        $keyword    = trim(I('get.keyword'));
        $searchtype = I('get.searchtype');
        $tcid       = (C('Transit_Type.GdEms_Transit')) ? trim(C('Transit_Type.GdEms_Transit')) : '';//20161116 jie   增加 TrandKd = 中转线路.id 的查询

        //按查询类型搜索
        if(!empty($keyword) && !empty($searchtype)){
            $map[$searchtype] = array('like','%'.$keyword.'%');
        }

        if($tcid != ''){
            $map['TranKd'] = array('eq',$tcid);//标签A
        }

        $client = $this->client;
        $res = $client->state_list($map,$p,$ePage);
        $count = $res['count'];
        $list  = $res['list'];

        $page = new \Think\Page($count,30); // 实例化分页类 传入总记录数和每页显示的记录数(20)
        $page->setConfig('prev', "上一页");//上一页
        $page->setConfig('next', '下一页');//下一页
        $page->setConfig('first', '首页');//第一页
        $page->setConfig('last', "末页");//最后一页
        $page->setConfig ( 'theme', '%FIRST% %UP_PAGE% %LINK_PAGE% %DOWN_PAGE% %END% %HEADER%' );
            
        $show = $page->show(); // 分页显示输出
        $this->assign('page',$show);// 赋值分页输出

        $this->assign('list',$list);
        $this->assign($_GET);
        
        $this->assign('state_arr',C('CustomStateList')); //20160511 Jie
        $this->display();
    }

}