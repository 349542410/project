<?php
/**
 * 操作员管理
 */
namespace Admin\Controller;
use Think\Controller;
class OperatorController extends AdminbaseController{

    function _initialize() {
        parent::_initialize();
		$client = new \HproseHttpClient(C('RAPIURL').'/Operator');		//读取、查询操作
		$this->client = $client;	//全局变量
    }

	public function index(){

		$client = $this->client;

		$keyword    = trim(I('get.keyword'));
		$searchtype = I('get.searchtype');
		$usertype   = I('get.usertype');
		$status     = I('get.status');

		//分页显示的数量
		$p = (I('p')) ? trim(I('p')) : '1';
		$ePage = (I('get.ePage')) ? trim(I('get.ePage')) : C('EPAGE');

		$this->assign($_GET);
		$this->assign('ePage',$ePage);

		//按用户名搜索
		if(!empty($keyword) && !empty($searchtype)){
			$where[$searchtype]=array('like','%'.$keyword.'%');
		}
		//按权限类型搜索
		if($usertype)$where['usertype']=$usertype;

        // if($type)$where['type']=$type;
        // 按状态搜索
        if($status != ''){
            $where['status']=$status;
        }

        // $count = M('operator_list')->where($where)->count(); // 查询满足要求的总记录数
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

		$this->assign('ulist',$list);
		$this->display();
	}

	/**
	 * 查看某个详细信息
	 * @return [type] [description]
	 */
	public function info(){

		$id = I('get.id');

		$client = $this->client;

		$info = $client->getInfo($id);
		
		$this->assign('info',$info);
		$this->display();
	}



	/**
	 * 添加 视图
	 */
	public function add(){
	    $point = $this->client->point();
	    $this->assign('point',$point);
		$this->display();
	}

	/**
	 * 添加 方法
	 * @return [type] [description]
	 */
	public function insert(){

		if(!IS_POST){
			die('非法操作');
		}

		$data['username'] = trim(I('post.name'));
		$data['userpwd']  = md5(md5(I('post.pwd')));
		$data['truename'] = trim(I('post.truename'));
		$data['phone']    = trim(I('post.phone'));
		$data['address']  = trim(I('post.address'));
		$data['usertype'] = I('post.usertype');
		$data['status']   = I('post.status');
		$data['remarks']  = I('post.remarks');
		$data['reg_time'] = time();
		$point_id = I('post.point_id');
		if(!empty($point_id)){
		    $data['point_id'] = I('post.point_id');
        }
        $client = $this->client;

	    $result = $client->add($data);			

	    $this->ajaxReturn($result);

	}

	/**
	 * 编辑 视图
	 * @return [type] [description]
	 */
	public function edit(){

		$id = I('get.id');

        $client = $this->client;
        
		$info = $client->edit($id);

        $point = $this->client->point();
        $this->assign('point',$point);
		$this->assign('info',$info);

		$this->display();
	}

	/**
	 * 更新数据
	 * @return [type] [description]
	 */
	public function update(){
		$id = I('post.id');

		$client = $this->client;

		$getone = $client->getone($id);

		$data['userpwd']  = trim(I('post.pwd')) ? md5(md5(trim(I('post.pwd')))) : $getone['userpwd'];
		$data['truename'] = trim(I('post.truename'));
		$data['phone']    = trim(I('post.phone'));
		$data['address']  = trim(I('post.address'));
		$data['usertype'] = I('post.usertype');
		$data['status']   = I('post.status');
		$data['remarks']  = I('post.remarks');
		$point_id = I('post.point');
        if(!empty($point_id)){
            $data['point_id'] = $point_id;
        }
		$result = $client->update($id,$data);

		$this->ajaxReturn($result);

	}

	/**
	 * 单个删除 方法
	 * @return [type] [description]
	 */
	public function delete(){
		if(IS_AJAX){
			$id = I('post.id');

	        $client = $this->client;
			$result = $client->delete($id);

			$this->ajaxReturn($result);
			
		}else{
			die('非法操作');
		}
	}



}