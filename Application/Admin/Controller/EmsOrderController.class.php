<?php
/**
 * EMS推送订单失败时修改订单信息
 */
namespace Admin\Controller;
use Think\Controller;
use AUApi\Controller\KdnoConfig\Kdno23;

class EmsOrderController extends AdminbaseController
{
    function _initialize()
    {
        parent::_initialize();
        $client = new \HproseHttpClient(C('RAPIURL').'/EmsOrder');		//读取、查询操作
        $this->client = $client;		//全局变量
    }

    //获取全部的订单信息
    public function index()
    {
        $p = (I('p')) ? trim(I('p')) : '1';
        $ePage = (I('get.ePage')) ? trim(I('get.ePage')) : C('EPAGE');
        $lading_id = I('get.id');//提单id
        $line      = I('get.line');//线路id
        $res = $this->client->_index($lading_id,$line,$p,$ePage);
        if($res['state'] === 'no'){
            $this->error($res['msg'],U('Logarithm/lading_list?line='.$line),1);
        }

        $count = $res['count'];
        $list  = $res['list'];

        $page = new \Think\Page($count,$ePage); // 实例化分页类 传入总记录数和每页显示的记录数(20)

        $page->setConfig('prev', "上一页");//上一页
        $page->setConfig('next', '下一页');//下一页
        $page->setConfig('first', '首页');//第一页
        $page->setConfig('last', "末页");//最后一页
        $page->setConfig ( 'theme','%FIRST% %UP_PAGE% %LINK_PAGE% %DOWN_PAGE% %END% %HEADER%');

        $show = $page->show(); // 分页显示输出
        $this->assign('page',$show);// 赋值分页输出
        $this->assign('list',$list);
        $this->assign('line',$line);
        $this->assign('lading_id',$lading_id);
        $this->display();
    }

    //订单推送
    public function pushOrder()
    {
        $order_id  = I('post.orderid');
        $line_id   = I('post.line');
        $lading_id = I('post.ladingid');

        if($line_id != 23){
            $this->ajaxReturn(array('state'=>'no','msg'=>'线路错误'));
        }

        //处理需要传给EMS的数据
        $data = $this->client->_pushData($order_id,$lading_id);

        $new = new Kdno23();

        $res = $new->data($data);

        $this->ajaxReturn($res);
    }
}