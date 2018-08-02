<?php
namespace Admin\Controller;
use Think\Controller;
class EcompanyController extends AdminbaseController{

	function _initialize(){
		parent::_initialize();
        $client = new \HproseHttpClient(C('RAPIURL').'/Ecompany');		//读取、查询操作
        $this->client = $client;		//全局变量

	}

	public function index(){

		$keyword    = trim(I('get.keyword'));
		$searchtype = I('get.searchtype');
		$status     = I('get.status');
		
		$p = (I('p')) ? trim(I('p')) : '1';
		$ePage = (I('get.ePage')) ? trim(I('get.ePage')) : C('EPAGE');
		$this->assign($_GET);
		$this->assign('ePage',$ePage);
		
		if(!empty($keyword) && !empty($searchtype)){
			$where[$searchtype]=array('like','%'.$keyword.'%');
		}

        if($status != ''){
            $where['status'] = $status;
        }

        $client = $this->client;
        
		$res = $client->count($where,$p,$ePage);

		$count = $res['count'];
		$list  = $res['list'];

		$page = new \Think\Page($count,$ePage); // 实例化分页类 传入总记录数和每页显示的记录数(20)

		$page->setConfig('prev', "上一页");//上一页
		$page->setConfig('next', '下一页');//下一页
		$page->setConfig('first', '首页');//第一页
		$page->setConfig('last', "末页");//最后一页
		$page->setConfig ( 'theme', '%FIRST% %UP_PAGE% %LINK_PAGE% %DOWN_PAGE% %END% %HEADER%' );
    		
		$show = $page->show(); // 分页显示输出
		$this->assign('page',$show);// 赋值分页输出

		$this->assign('list',$list);
		$this->assign('id',$list['id']);

		$this->display();
	}

	/**
	 * 查看
	 * @return [type] [description]
	 */
	public function info(){
        
		$id = I('get.id');
		$map['id'] = array('eq',$id);

		$client = $this->client;
		//$info =  M('express_company')->where($map)->find();
		$info   = $client->info($map);
		
		$this->assign('info',$info);
		$this->display();

	}

	/**
	 * 添加 视图 & 方法
	 */
	public function add(){

		if($_POST){
			$data['company_name']   = trim(I('post.company_name'));
			$data['short_name']     = trim(I('post.short_name'));
			$data['express_way']    = trim(I('post.express_way'));
			$data['contact_person'] = trim(I('post.contact_person'));
			$data['contact_phone']  = trim(I('post.contact_phone'));
			$data['status']         = I('post.status');
			$data['remarks']        = trim(I('post.remarks'));

		    $client = $this->client;

			$result = $client->add($data);	

			$this->ajaxReturn($result);
		}

		$this->display();
	}

	/**
	 * 编辑 视图
	 * @return [type] [description]
	 */
	public function edit(){

		$id = I('get.id');

        $client = $this->client;

		$info = $client->edit($id);

		$this->assign('info',$info);

		$this->display();

	}

	/**
	 * 更新数据
	 * @return [type] [description]
	 */
	public function update(){

		if(!IS_POST){
			die('非法操作');
		}

		$id = I('post.id');

		//如果被修改的id <= 50 ,抛出错误，终止操作
		if(intval($id) <= 50){
            $result = array('state' => 'no', 'msg' => '无法修改');
            $this->ajaxReturn($result);
            exit;
		}

		$client = $this->client;

		$data['company_name']   = trim(I('post.company_name'));
		$data['short_name']     = trim(I('post.short_name'));
		$data['express_way']    = trim(I('post.express_way'));
		$data['contact_person'] = trim(I('post.contact_person'));
		$data['contact_phone']  = trim(I('post.contact_phone'));
		$data['status']         = I('post.status');
		$data['remarks']        = trim(I('post.remarks'));

		$result = $client->update($id,$data);

		$this->ajaxReturn($result);
	}


	/**
	 * 单个删除 方法
	 * @return [type] [description]
	 */
	public function delete(){

		if(!IS_POST){
			die('非法操作');
		}

		$id = I('post.id');

		//如果被修改的id >= 50 ,抛出错误，终止操作
		if(intval($id) >= 50){
            $this->display('Public/msg');
            exit;
		}

        $client = $this->client;
		$result = $client->delete($id);

		$this->ajaxReturn($result);

		
	}


}