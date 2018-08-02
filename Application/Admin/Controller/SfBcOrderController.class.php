<?php
/**
 * 顺丰BC订单管理 客户端
 */
namespace Admin\Controller;
use Think\Controller;
class SfBcOrderController extends AdminbaseController{

    function _initialize() {
        parent::_initialize();
        $client = new \HproseHttpClient(C('RAPIURL').'/SfBcOrder');        //读取、查询操作
        $this->client = $client;    //全局变量
    }

	public function index(){

        $keyword    = trim(I('get.keyword'));
        $searchtype = I('get.searchtype');
        $starttime  = intval(I('get.starttime'));
        $endtime    = intval(I('get.endtime'));

        //分页显示的数量
        $p = (I('p')) ? trim(I('p')) : '1';
        $ePage = (I('get.ePage')) ? trim(I('get.ePage')) : C('EPAGE');

        $this->assign($_GET);
        $this->assign('ePage',$ePage);

        $map = array();
        $map['print_state'] = array('eq',0); //查询打印状态为未打印的订单

        //按用户名、联系人、手机号码进行搜索
        if(!empty($keyword) && !empty($searchtype)){
            $map[$searchtype]=array('like','%'.$keyword.'%');
        }

        //按时间段搜索
        if($starttime && $endtime){
            $starttime = date('Y-m-d H:i:s',$starttime);
            $endtime   = date('Y-m-d H:i:s',$endtime);
            $map['ctime'] = array('between',array($starttime,$endtime));
        }else if(!$starttime && $endtime){
            $endtime   = date('Y-m-d H:i:s',$endtime);
            $map['ctime'] = array('elt',$endtime);
        }else if($starttime && !$endtime){
            $starttime = date('Y-m-d H:i:s',$starttime);
            $map['ctime'] = array('egt',$starttime);
        }

        $client = $this->client;

        $res = $client->_count($map,$p,$ePage);
        $count = $res['count'];
        $list  = $res['list'];
        $page = new \Think\Page($count,$ePage); // 实例化分页类 传入总记录数和每页显示的记录数(20)

        $page->setConfig('prev', "上一页");//上一页
        $page->setConfig('next', '下一页');//下一页
        $page->setConfig('first', '首页');//第一页
        $page->setConfig('last', "末页");//最后一页
        $page -> setConfig ( 'theme', '%FIRST% %UP_PAGE% %LINK_PAGE% %DOWN_PAGE% %END% %HEADER%' );

        $show = $page->show(); // 分页显示输出
        $this->assign('page',$show);// 赋值分页输出

/*        $cat_list = $client->cat_list();//查询所有类别
        $cat_list = getTree($cat_list);//将普通数据转成树形结构
        getTree(null);//上面调用完一次后重置静态变量

        $naw = array();
        //将所有顶级类别归类
        foreach($cat_list as $k=>$item){
            if($item['level'] == '0'){
                $naw[$item['id']] = $item;
            }
            
        }
        //根据二级类别的fid = 顶级类别的id进行对应归类，即顶级旗下应属于自己的二级都全部归纳一起，以便查看
        foreach($naw as $k1=>$v1){
            foreach($cat_list as $k2=>$v2){
                if($v2['fid'] == $v1['id']){
                    $naw[$k1]['child'][] = $v2;//全部fid相同的二级归纳到其对应的顶级里面，用child归纳
                }
            }
        }

        self::assign('naw',$naw);   //归类好的所有类别列表*/
		$this->assign('list',$list);
		$this->display();
	}

    /**
     * 根据二级类别ID找出对应其对应的货品列表
     * @param  [type] $id      [上级ID]
     * @param  [type] $keyword [搜索关键字]
     * @return [type]          [description]
     */
    public function product(){
        $id      = trim(I('id'));//二级类别的id
        $keyword = (I('keyword')) ? trim(I('keyword')) : '';//搜索关键字

        $client = $this->client;
        $list = $client->_product($id,$keyword);
        // dump($list);
        $this->ajaxReturn($list);
    }

    /**
     * 根据上一级的类别ID找出对应的下一级分类
     * @param  [type] $id [上级ID]
     * @return [type]     [description]
     */
    public function next_level(){
        $id = trim(I('id'));
        $client = $this->client;
        // getTree(null);//上面调用完一次后重置静态变量
        $list = $client->_next_level($id);
        // dump($list);
        $this->ajaxReturn($list);
    }

    public function info(){
        
        $id = I('id');
        $client = $this->client;
        $pro_list = $client->get_pro_list($id);

        $cat_list = $client->cat_list();//查询所有类别
        $cat_list = getTree($cat_list);//将普通数据转成树形结构
        getTree(null);//上面调用完一次后重置静态变量

        $naw = array();
        //将所有顶级类别归类
        foreach($cat_list as $k=>$item){
            if($item['level'] == '0'){
                $naw[$item['id']] = $item;
            }
            
        }
        //根据二级类别的fid = 顶级类别的id进行对应归类，即顶级旗下应属于自己的二级都全部归纳一起，以便查看
        foreach($naw as $k1=>$v1){
            foreach($cat_list as $k2=>$v2){
                if($v2['fid'] == $v1['id']){
                    $naw[$k1]['child'][] = $v2;//全部fid相同的二级归纳到其对应的顶级里面，用child归纳
                }
            }
        }
// dump($naw);
        self::assign('pro_list',$pro_list);   //订单的商品列表
        self::assign('naw',$naw);   //归类好的所有类别列表
        $this->display();
    }











}