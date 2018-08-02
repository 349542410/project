<?php
namespace Admin\Controller;
use Think\Controller;
class CustomsReportController extends AdminbaseController{

    function _initialize() {
        parent::_initialize();
        $client = new \HproseHttpClient(C('RAPIURL').'/CustomsReport');		//读取、查询操作
        $this->client = $client;		//全局变量
    }

    /**
     * Goods列表
     * @return [type] [description]
     */
    public function index(){

		$client = $this->client;

		$keyword    = trim(I('get.keyword'));
		$searchtype = I('get.searchtype');
		// $status     = I('get.status');

		//分页显示的数量
		$p = (I('p')) ? trim(I('p')) : '1';
		$ePage = (I('get.ePage')) ? trim(I('get.ePage')) : C('EPAGE');

		$this->assign($_GET);
		$this->assign('ePage',$ePage);

		//按用户名搜索
		if(!empty($keyword) && !empty($searchtype)){
			$where[$searchtype]=array('like','%'.$keyword.'%');
		}

		$res = $client->count($where,$p,$ePage);
		$count = $res['count'];
		$list  = $res['list'];
		$page = new \Think\Page($count,$ePage); // 实例化分页类 传入总记录数和每页显示的记录数(25)

	    $page->setConfig('prev', "上一页");//上一页
	    $page->setConfig('next', '下一页');//下一页
	    $page->setConfig('first', '首页');//第一页
	    $page->setConfig('last', "末页");//最后一页
	    $page -> setConfig ( 'theme', '%FIRST% %UP_PAGE% %LINK_PAGE% %DOWN_PAGE% %END% %HEADER%' );
    
		$show = $page->show(); // 分页显示输出
        $this->assign('page',$show);// 赋值分页输出

		$this->assign('Customs_Code',C('Customs_Code'));	//报备编码对应的文字说明
		$this->assign('ulist',$list);
    	$this->display();
    }

	/**
	 * 获取logs信息
	 * @return [type] [description]
	 */
	public function info(){
        
		$id     = trim(I('get.id'));
		$client = $this->client;

		$res = $client->getInfo($id,$MKNO);

		$this->assign('info',$res[0]);
		$this->list = $res[1];
		$this->assign('id',$id);	//20160316 Jie
		$this->assign('Customs_Code',C('Customs_Code'));	//报备编码对应的文字说明
		$this->display();
	}
}