<?php
/**
 * 管理员管理 客户端
 */
namespace Admin\Controller;
use Think\Controller;
class ManagerController extends AdminbaseController{
	
    function _initialize() {
        parent::_initialize();
        $client = new \HproseHttpClient(C('RAPIURL').'/Manager');	//读取、查询操作
        $this->client = $client;	//全局变量

    }
    
	public function index(){
        
        $client = $this->client;
		$usergroup = $client->group();		
		$this->assign('usergroup',$usergroup);	//用户组列表

        $keyword = trim(I('get.keyword'));
        $searchtype = I('get.searchtype');
		$groupid = intval(I('get.groupid'));
		$status = I('get.status');

		//分页显示的数量
		$p = (I('p')) ? trim(I('p')) : '1';
		$ePage = (I('get.ePage')) ? trim(I('get.ePage')) : C('EPAGE');
		$this->assign('ePage',$ePage);

		$this->assign($_GET);

		//按用户名、邮箱、电话搜索
		if(!empty($keyword) && !empty($searchtype)){
			$where[$searchtype]=array('like','%'.$keyword.'%');
		}
		//按用户组搜索
		if($groupid)$where['groupid']=$groupid;

        // if($type)$where['type']=$type;
        // 按状态搜索
        if($status != ''){
            $where['status']=$status;
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


		$this->assign('ulist',$list);
		$this->display();
	}

	/**
	 * 添加 视图
	 */
	public function add(){

        $client = $this->client;
		$usergroup = $client->group();		

		$this->assign('usergroup',$usergroup);

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

		$pwd = trim(I('post.pwd'));
		$repwd = trim(I('post.repwd'));

		if($pwd != $repwd){
			// $this->error('密码输入不一致');
			$result = array('state' => 'no', 'msg' => '密码输入不一致');
			$this->ajaxReturn($result);
			exit;
		}

		$data['name']     = trim(I('post.name'));
		$data['tname']    = trim(I('post.truename'));
		$data['pwd']      = md5($pwd);
		$data['email']    = trim(I('post.email'));
		$data['phone']    = trim(I('post.phone'));
		$data['status']   = I('post.status');
		$data['groupid']  = I('post.groupid');
		$data['reg_time'] = time();

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
		$usergroup = $client->group();		

		$this->assign('usergroup',$usergroup);	

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

		$id    = I('post.id');
		$pwd   = trim(I('post.pwd'));
		$repwd = trim(I('post.repwd'));

		if($pwd != $repwd){
			// $this->error('密码输入不一致');
			$result = array('state' => 'no', 'msg' => '密码输入不一致');
			$this->ajaxReturn($result);
			exit;
		}

		$client = $this->client;

		$getPWD = $client->getPWD($id);

		$data['pwd']     = trim(I('post.pwd')) ? md5(trim(I('post.pwd'))) : $getPWD['pwd'];
		$data['tname']   = trim(I('post.truename'));
		$data['email']   = trim(I('post.email'));
		$data['phone']   = trim(I('post.phone'));
		$data['status']  = I('post.status');
		$data['groupid'] = I('post.groupid');	

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

			$value = session('admin');

            if($value['adid'] == $id){
                $backArr = array('state'=>'no','msg'=>'您正在使用该账号，操作失败');
                $this->ajaxReturn($backArr);
            }

	        $client = $this->client;
			$result = $client->delete($id);

			$this->ajaxReturn($result);

		}else{
			die('非法操作');
		}
	}

}