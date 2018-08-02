<?php
/**
 * 快件管理 身份证号码修改  客户端
 */
namespace Admin\Controller;
use Think\Controller;
class EditIdnoController extends AdminbaseController {

    function _initialize() {
        parent::_initialize();
		$client = new \HproseHttpClient(C('RAPIURL').'/PostManagement');	//读取、查询操作
        $this->client = $client;		//全局变量
    }

	/**
	 * 编辑 视图
	 * @return [type] [description]
	 */
	public function editIdno(){
        
		$id = trim(I('get.id'));
		$client = $this->client;
		$info   = $client->getInfo($id);
		
		$this->assign('info',$info[0]);
		$this->display();
	}

	/**
	 * 更新 方法
	 * @return [type] [description]
	 */
	public function update(){

    	if(!IS_POST){
    		die('非法操作~！');
    	}

/*        $value = session();
        $username = $value['admin']['adname'];		//当前登陆的管理员*/

		$id    = trim(I('post.id'));		//mk_tran_list 的id
		$reTel = trim(I('post.reTel'));	//收件人手机号码
		$idno  = trim(I('post.idno'));	//收件人身份证号码

		/* 检验 */
		if($reTel == ''){
			$result = array('state'=>'no','msg'=>'收件人手机号码不能为空');
			$this->ajaxReturn($result);
		}
		if($idno == ''){
			$result = array('state'=>'no','msg'=>'收件人身份证号码不能为空');
			$this->ajaxReturn($result);
		}
		if(!preg_match('/^(13|14|15|17|18)[0-9]{9}$/',$reTel)){
			$result = array('state'=>'no','msg'=>'收件人电话格式不正确');
			$this->ajaxReturn($result);
		}
		/* End */

		$client = new \HproseHttpClient(C('RAPIURL').'/EditIdno');	//读取、查询操作
		
		$result = $client->_update($id,$reTel,$idno);

		$this->ajaxReturn($result);

	}

}