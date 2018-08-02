<?php
/**
 * 操作员管理 服务器端
 */
namespace Api\Controller;
use Think\Controller\HproseController;
class OperatorController extends HproseController{
	protected $crossDomain =	true;
	protected $P3P		   =    true;
	protected $get		   =    true;
	protected $debug 	   =    true;

	/**
	 * 查总数
	 * @param  [type] $where [description]
	 * @return [type]        [description]
	 */
	public function count($where,$p,$ePage){
        $list = M('OperatorList')->where($where)->order('id')->page($p.','.$ePage)->select();

		$count = M('OperatorList')->where($where)->count(); // 查询满足要求的总记录数
		return array('count'=>$count, 'list'=>$list);
	}
	
    /**
     * 获取被编辑的操作员的信息以便校验密码是否有更改
     * @param  [type] $id [description]
     * @return [type]     [description]
     */
    public function getone($id){
        $getone = M('OperatorList')->where(array('id'=>$id))->find();
        return $getone;
    }

	/**
	 * 查看某个详细信息
	 * @return [type] [description]
	 */
    public function getInfo($id){
    	$info = M('OperatorList')->alias('oper')->field('oper.*, p.point_name')->join('left join mk_collect_point AS p ON oper.point_id = p.id ')->where(array('oper.id'=>$id))->find();
    	return $info;
    }

    /**
     * 添加 方法
     * @param [type] $data [description]
     */
    public function add($data){
        $name = M('OperatorList')->where(array('username'=>$data['username']))->find();
        //验证是否已经存在这个名字
        if($name){
            $result = array('state'=>'no', 'msg'=>'该用户名已存在');
            return $result;
        }
        
    	if(M('OperatorList')->add($data)){
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
		$info = M('OperatorList')->where(array('id'=>$id))->find();
		return $info;
	}

	/**
	 * 更新 方法
	 * @param  [type] $id   [description]
	 * @param  [type] $data [description]
	 * @return [type]       [description]
	 */
	public function update($id,$data){
        // $map['id'] = array('neq',$id);
        // $map['username'] = array('eq',$data['username']);
        
        // 20151123 Jie 用户名不能被修改，因此注销此
        // $name = M('OperatorList')->where($map)->find();
        // //验证是否已经存在这个名字
        // if($name){
        //     $result = array('state'=>'no', 'msg'=>'该用户名已存在');
        //     return $result;
        // }

		$save = M('OperatorList')->where(array('id'=>$id))->save($data);
		
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
     * 删除 方法
     */
    public function delete($id){
        $he = M('OperatorList')->where(array('id'=>$id))->find();

        if(!$he){
            $result = array('state'=>'no', 'msg'=>'参数错误，请刷新再试');
            return $result;
        }
        
    	$del = M('OperatorList')->where(array('id'=>$id))->delete();
    	if($del){
    		$result = array('state'=>'yes', 'msg'=>'删除成功');
    	}else{
    		$result = array('state'=>'no', 'msg'=>'删除失败');
    	}
    	return $result;
    }


    /**
     * 获取揽收点
     */
    public function point($data = array()){
        if(!empty($data)){
            $res = M('collect_point')->field('id, point_name')->where($data)->select();
        }else{
            $res = M('collect_point')->field('id, point_name')->select();
        }
        return $res;

    }

}