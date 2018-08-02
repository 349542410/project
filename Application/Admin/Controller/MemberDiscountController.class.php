<?php
/**
 * 会员管理---会员优惠
 */
namespace Admin\Controller;
use Think\Controller;
class MemberDiscountController extends AdminbaseController{

    function _initialize() {
        parent::_initialize();
        $client = new \HproseHttpClient(C('RAPIURL').'/AdminMemberDiscount');        //读取、查询操作
        $this->client = $client;    //全局变量
    }

    public function index(){
        
        $uid = (I('uid')) ? trim(I('uid')) : ''; //会员ID
        $line = (I('line')) ? trim(I('line')) : ''; //线路ID

        $keyword    = trim(I('get.keyword'));
        $searchtype = I('get.searchtype');
        $operatorId = I('get.operatorId');

        //分页显示的数量
        $p = (I('p')) ? trim(I('p')) : '1';
        $ePage = (I('get.ePage')) ? trim(I('get.ePage')) : C('EPAGE');

        $where = array();

        if($uid != '') $where['l.user_id'] = array('eq', $uid);
        if($line != '') $where['l.line_id'] = array('eq', $line);

        if(!empty($keyword) && !empty($searchtype)){
            $where["u.".$searchtype]=array('like','%'.$keyword.'%');
        }

        if($operatorId != ''){
            $where['m.id'] = $operatorId;
        }

        $this->assign($_GET);
        $this->assign('ePage',$ePage);

        $client = $this->client;
        $res = $client->_index($where,$p,$ePage);

        $page = new \Think\Page($res['discount_count'],$ePage); // 实例化分页类 传入总记录数和每页显示的记录数(20)

        $page->setConfig('prev', "上一页");//上一页
        $page->setConfig('next', '下一页');//下一页
        $page->setConfig('first', '首页');//第一页
        $page->setConfig('last', "末页");//最后一页
        $page->setConfig('theme', '%FIRST% %UP_PAGE% %LINK_PAGE% %DOWN_PAGE% %END% %HEADER%' );
            
        $show = $page->show(); // 分页显示输出
        $this->assign('page',$show);// 赋值分页输出

        $this->assign('member_list',$res['member']);//只显示有优惠设置的会员列表 左侧导航栏
        $this->assign('line_list',$res['line_list']);//线路列表 搜索栏
        $this->assign('operator_list',$res['operator_list']); //操作人列表
        $this->assign('discount_list',$res['discount_list']); //线路对应的优惠列表
        $this->display();
    }
}