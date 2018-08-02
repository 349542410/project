<?php
/**
 * 管理员管理 服务器端
 */
namespace Api\Controller;
use Think\Controller\HproseController;
class ManagerController extends HproseController{
	protected $crossDomain =	true;
	protected $P3P		   =    true;
	protected $get		   =    true;
	protected $debug 	   =    true;

	/**
	 * 查询所属用户组
	 * @return [type] [description]
	 */
	public function group(){
		$result = M('role_list')->select();
		
		$usergroup = array();
		foreach($result as $key => $val){
		    $usergroup[$val['id']] = $result[$key];
		}
		return $usergroup;
	}

	/**
	 * 查总数
	 * @param  [type] $where [description]
	 * @return [type]        [description]
	 */
	public function count($where,$p,$ePage){
		$list = M('ManagerList')->order('id')->where($where)->page($p.','.$ePage)->select();

		$count = M('ManagerList')->where($where)->count(); // 查询满足要求的总记录数
		return array('count'=>$count, 'list'=>$list);
	}

    /**
     * 获取被编辑的会员的信息以便校验密码是否有更改
     * @param  [type] $id [description]
     * @return [type]     [description]
     */
    public function getPWD($id){
        $getPWD = M('ManagerList')->where(array('id'=>$id))->find();
        return $getPWD;
    }

    /**
     * 添加 方法
     * @param [type] $data [description]
     */
    public function add($data){
        $name = M('ManagerList')->where(array('name'=>$data['name']))->find();
        //验证是否已经存在这个名字
        if($name){
            $result = array('state'=>'no', 'msg'=>'该用户名已存在');
            return $result;
        }

    	if(M('ManagerList')->add($data)){
    		$result = array('state'=>'yes', 'msg'=>'添加成功');
    	}else{
    		$result = array('state'=>'no', 'msg'=>'添加失败');
    	}
    	return $result;
    }

	/**
	 * 编辑 视图
	 * @param  [type] $id [description]
	 * @return [type]     [description]
	 */
	public function edit($id){
		$info = M('ManagerList')->where(array('id'=>$id))->find();
		return $info;
	}

	/**
	 * 更新 方法
	 * @param  [type] $id   [description]
	 * @param  [type] $data [description]
	 * @return [type]       [description]
	 */
	public function update($id,$data){

		$save = M('ManagerList')->where(array('id'=>$id))->save($data);
		if($save == 0){
			$result = array('state' => 'no', 'msg' => '没有数据更新');
		}else if($save === false){
			$result = array('state' => 'no', 'msg' => '更新失败');
		}else{
			$result = array('state' => 'yes', 'msg' => '更新成功');
		}
		return $result;
	}

    /**
     * 删除
     */
    public function delete($id){
        $he = M('ManagerList')->where(array('id'=>$id))->find();

        if(!$he){
            $result = array('state'=>'no', 'msg'=>'参数错误，请刷新再试');
            return $result;
        }
        
    	$del = M('ManagerList')->where(array('id'=>$id))->delete();
    	if($del){
    		$result = array('state'=>'yes', 'msg'=>'删除成功');
    	}else{
    		$result = array('state'=>'no', 'msg'=>'删除失败');
    	}
    	return $result;
    }

    
}