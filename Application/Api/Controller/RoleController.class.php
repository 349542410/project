<?php
/**
 * 角色管理 服务器端
 */
namespace Api\Controller;
use Think\Controller\HproseController;
class RoleController extends HproseController {
    protected $crossDomain =    true;
    protected $P3P         =    true;
    protected $get         =    true;
    protected $debug       =    true;

    /**
     * 列表
     * @return [type] [description]
     */
    public function index(){
    	$list = M('RoleList')->select();

    	return $list;
    }

    /**
     * 添加 方法
     * @param [type] $data [description]
     */
    public function add($data){
        $name = M('RoleList')->where(array('role_name'=>$data['role_name']))->find();
        //验证是否已经存在这个名字
        if($name){
            $result = array('state'=>'no', 'msg'=>'该角色名已存在');
            return $result;
        }

    	if(M('RoleList')->add($data)){
    		$result = array('state'=>'yes', 'msg'=>'添加成功');
    	}else{
    		$result = array('state'=>'no', 'msg'=>'添加失败');
    	}
    	return $result;
    }

    /**
     * 编辑
     * @param  [type] $id [description]
     * @return [type]     [description]
     */
    public function edit($id){

    	$info = M('RoleList')->where(array('id'=>$id))->find();

    	return $info;

    }

    /**
     * 更新
     */
    public function update($id,$data){
        $map['id'] = array('neq',$id);
        $map['role_name'] = array('eq',$data['role_name']);
        
        $name = M('RoleList')->where($map)->find();
        //验证是否已经存在这个名字
        if($name){
            $result = array('state'=>'no', 'msg'=>'该角色名已存在');
            return $result;
        }

        $save = M('RoleList')->where(array('id'=>$id))->save($data);
    	if($save == 0){
    		$result = array('state'=>'no', 'msg'=>'没有数据更新');
    	}else if($save === false){
    		$result = array('state'=>'no', 'msg'=>'修改失败');
    	}else{
            $result = array('state'=>'yes', 'msg'=>'修改成功');
        }

    	return $result;
    }

    /**
     * 删除
     */
    public function delete($id){
        $check = M('ManagerList')->where(array('groupid'=>$id))->select();

        if($check){
           $result = array('state'=>'no', 'msg'=>'操作失败,该用户组下有所属用户');
           return $result;
        }

        $he = M('RoleList')->where(array('id'=>$id))->find();

        if(!$he){
            $result = array('state'=>'no', 'msg'=>'参数错误，请刷新再试');
            return $result;
        }

    	$del = M('RoleList')->where(array('id'=>$id))->delete();
    	if($del){
    		$result = array('state'=>'yes', 'msg'=>'删除成功');
    	}else{
    		$result = array('state'=>'no', 'msg'=>'删除失败');
    	}
    	return $result;
    }


}