<?php
/**
 * 美快后台快递公司管理  服务器端
 */
namespace Api\Controller;
use Think\Controller\HproseController;
class EcompanyController extends HproseController{    
    protected $crossDomain =    true;
    protected $P3P         =    true;
    protected $get         =    true;
    protected $debug       =    true;

	/**
	 * 查总数
	 * @param  [type] $where [description]
	 * @return [type]        [description]
	 */
    public function count($where,$p,$ePage){
        $list = M('ExpressCompany')->where($where)->page($p.','.$ePage)->select();

    	$count = M('ExpressCompany')->where($where)->count();
        
    	return array('count'=>$count, 'list'=>$list);
    }

    /**
     * 查看
     * @return [type] [description]
     */
    public function info($map){
        $info = M('ExpressCompany')->where($map)->find();
        return $info;
    }

    /**
     * 添加 方法
     * @param [type] $data [description]
     */
    public function add($data){
        $name = M('ExpressCompany')->where(array('company_name'=>$data['company_name']))->find();
        //验证是否已经存在这个公司名称
        if($name){
            $result = array('state'=>'no', 'msg'=>'该公司名称已存在');
            return $result;
        }

        $short_name = M('ExpressCompany')->where(array('short_name'=>$data['short_name']))->find();
        //验证是否已经存在这个简称
        if($short_name){
            $result = array('state'=>'no', 'msg'=>'该简称已存在');
            return $result;
        }

    	if(M('ExpressCompany')->add($data)){
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
		$info = M('ExpressCompany')->where(array('id'=>$id))->find();
		return $info;
	}

	/**
	 * 更新 方法
	 * @param  [type] $id   [description]
	 * @param  [type] $data [description]
	 * @return [type]       [description]
	 */
	public function update($id,$data){
        $map['id'] = array('neq',$id);
        $map['company_name'] = array('eq',$data['company_name']);

        $name = M('ExpressCompany')->where($map)->find();
        //验证是否已经存在这个公司名称
        if($name){
            $result = array('state'=>'no', 'msg'=>'该公司名称已存在');
            return $result;
        }

        $map2['id'] = array('neq',$id);
        $map2['short_name'] = array('eq',$data['short_name']);

        $short_name = M('ExpressCompany')->where($map2)->find();
        //验证是否已经存在这个简称
        if($short_name){
            $result = array('state'=>'no', 'msg'=>'该简称已存在');
            return $result;
        }

		$save = M('ExpressCompany')->where(array('id'=>$id))->save($data);
		if($save === false){
			$result = array('state' => 'no', 'msg' => '更新失败');
		}else if($save == 0){
            $result = array('state' => 'no', 'msg' => '没有数据更新');
        }else{
            $result = array('state' => 'yes', 'msg' => '更新成功');
		}
		return $result;
	}

    /**
     * 删除
     */
    public function delete($id){
        $he = M('ExpressCompany')->where(array('id'=>$id))->find();

        //不同浏览器之间的误差操作
        if(!$he){
            $result = array('state'=>'no', 'msg'=>'参数错误，请刷新再试');
            return $result;
        }

        //检查mk_logs中是否已经正在使用该物流
		$check = M('logs')->where(array('tranid'=>$id))->select();
		if(count($check) > 0){
			$result = array('state'=>'no', 'msg'=>'该快递公司正被使用中，操作失败');
			return $result;
		}

    	$del = M('ExpressCompany')->where(array('id'=>$id))->delete();
    	if($del){
    		$result = array('state'=>'yes', 'msg'=>'删除成功');
    	}else{
    		$result = array('state'=>'no', 'msg'=>'删除失败');
    	}
    	return $result;
    }


}