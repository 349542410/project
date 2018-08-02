<?php
/**
 * CC类别
 */
namespace Api\Controller;
use Think\Controller\HproseController;
class CategoryForCcController extends HproseController{

    //查出 所有类别列表
    public function cat_list($lineID){
        $map['TranKd'] = array('like','%,'.$lineID.',%');
        $cat_list = M('CategoryList')->where($map)->select();
        return $cat_list;
    }

    //只显示cc_state=1的线路，即CC报关管理为开启状态的
    public function get_center_list(){
        return M('TransitCenter')->where(array('cc_state'=>'1'))->select();//只显示cc_state=1的线路，即BC报关管理为开启状态的
    }

    //获取线路所含顶级类别
    public function top_cat_list($map){
        $map['fid'] = array('eq', 0);
        return M('CategoryList')->field('id,cat_name')->where($map)->order('sort asc, cat_name asc')->select();
    }

    /**
     * [get_cat_list 根据传入的类别ID，查出此类别ID所属的所有下级ID。若此ID是顶级类别ID，
     * 则返回的结果($str)里面不包含此ID；若此ID是非顶级类别ID，则返回的结果里面是包含此ID]
     * @param  [type] $id [传入的类别的ID]
     * @return [type]     [description]
     */
    public function get_cat_list($id){
        return get_cat_list($id);
    }

	//查询数据总数
	public function _count($map,$p,$ePage){
        $map['c.fid'] = array('neq',0);//无需显示顶级类别的数据
        $list = M('CategoryList c')->field('c.*,m.name as manager')->join('left join mk_manager_list m on m.id = c.operator_id')->where($map)->order('sort asc, cat_name asc')->page($p.','.$ePage)->select();

/*        $where = array();
        foreach($list as $key=>$item){
            //根据mk_transit_center.id找出线路名称
            $where['id'] = array('in',$item['TranKd']);
            $lname = M('TransitCenter')->field('name as lname')->where($where)->select();

            $lname = array_column($lname, 'lname');//二维数组转一维数组
            $lname = implode($lname,' | ');//数组转字符串

            //只有顶级类别才赋值显示其所含的线路名称
            if($item['fid'] == 0) $list[$key]['line_name'] = $lname;
        }*/

        $count = M('CategoryList c')->join('left join mk_manager_list m on m.id = c.operator_id')->where($map)->count(); // 查询满足要求的总记录数

        // $center = $this->get_center_list();//只显示cc_state=1的线路，即BC报关管理为开启状态的
        return array('count'=>$count, 'list'=>$list);
	}

    //添加 视图
	public function _add(){
		$center = $this->get_center_list();//只显示cc_state=1的线路，即BC报关管理为开启状态的
		return $center;
	}

	// //添加 方法
	// public function _add_method($data){

 //        //20170922
 //        $map['fid']      = array('eq', $data['fid']);
 //        $map['cat_name'] = array('eq', $data['cat_name']);

 //        $name = M('CategoryList')->where($map)->find();
 //        //验证是否已经存在这个名字
 //        if($name){
 //            $result = array('state'=>'no', 'msg'=>'同级类别下该名称已存在');
 //            return $result;
 //        }

 //        if($data['fid'] != 0){
 //            $TranKd = M('CategoryList')->where(array('id'=>$data['fid']))->getField('TranKd');
 //            $data['TranKd'] = $TranKd;
 //        }

 //    	if(M('CategoryList')->add($data)){
 //    		$result = array('state'=>'yes', 'msg'=>'添加成功');
 //    	}else{
 //    		$result = array('state'=>'no', 'msg'=>'添加失败');
 //    	}
 //    	return $result;
	// }

	//编辑 视图
	public function _edit($id){

		$center = $this->get_center_list();//只显示cc_state=1的线路，即BC报关管理为开启状态的

        $info = M('CategoryList')->where(array('id'=>$id))->find();
        
        //20180425 xieyiyi 添加查询行邮税号类别
        $cname_code = $this->livesearch($info['hs_code']);

		return array($center, $info ,$cname_code);
	}

	// //编辑 方法
	// public function _edit_method($id, $data){

 //        $map['fid']      = array('eq',$data['fid']);
 //        $map['cat_name'] = array('eq',$data['cat_name']);
	// 	$map['id'] = array('neq',$id);

 //        $name = M('CategoryList')->where($map)->select();
 //        //验证是否已经存在这个名字
 //        if($name){
 //            $result = array('state'=>'no', 'msg'=>'同级类别下该名称已存在');
 //            return $result;
 //        }

 //        //如果选择了自己
 //        if($id == $data['fid']){
 //            $result = array('state'=>'no', 'msg'=>'不可选择自身级别');
 //            return $result;
 //        }

 //        //非顶级类别的类别需要检查其上级 是否显示 或 激活 的状态
 //        if($data['fid'] != 0){
 //            $upper = M('CategoryList')->where(array('id'=>$data['fid']))->find();
 //            $data['TranKd'] = $upper['TranKd'];
            
 //            //如果要该类要修改的显示状态与其上级的显示状态不一致，且其上级的显示状态是 不显示
 //            if($data['is_show'] != $upper['is_show'] && $upper['is_show'] == '0'){
 //                $result = array('state'=>'no', 'msg'=>'不可违背上级的显示状态');
 //                return $result;
 //            }

 //            //如果要该类要修改的激活状态与其上级的激活状态不一致，且其上级的激活状态是 禁用
 //            if($data['status'] != $upper['is_show'] && $upper['status'] == '0'){
 //                $result = array('state'=>'no', 'msg'=>'不可违背上级的激活状态');
 //                return $result;
 //            }
 //        }

 //        //查询该类别下的所有类别ID
 //        $pids = get_cat_list($id);
 //        // return $pids;

 //        $where['id'] = array('in',$pids);

 //        //该类别目录下的所有子类都要跟随修改
 //        $newdata = array();
 //        $newdata['TranKd']  = $data['TranKd'];
 //        $newdata['is_show'] = $data['is_show'];
 //        $newdata['status']  = $data['status'];

 //        $res = M('CategoryList')->where($where)->save($newdata);

 //    	if(M('CategoryList')->where(array('id'=>$id))->save($data) !== false){
 //    		$result = array('state'=>'yes', 'msg'=>'修改成功');
 //    	}else{
 //    		$result = array('state'=>'no', 'msg'=>'修改失败');
 //    	}
 //    	return $result;
	// }

    /**
     * 删除
     */
    public function delete($id){

        $he = M('CategoryList')->where(array('id'=>$id))->find();

        //检验数据是否存在
        if(!$he){
            $result = array('state'=>'no', 'msg'=>'参数错误，请刷新再试');
            return $result;
        }

        //查询该类别下的所有类别ID
        $pids = get_cat_list($id);

        $map = array();
        $map['cat_id'] = array('in',$pids);
        $product = M('ProductList')->where($map)->select();

        //该类别下是否有货品数据的时候，禁止删除
        if(count($product) > 0){
        	$result = array('state'=>'no', 'msg'=>'该类别目录下有货品');
            return $result;
        }

    	$del = M('CategoryList')->where(array('id'=>$id))->delete();
    	if($del){
    		$result = array('state'=>'yes', 'msg'=>'删除成功');
    	}else{
    		$result = array('state'=>'no', 'msg'=>'删除失败');
    	}
    	return $result;
    }
    
    //搜索税则表
    public function livesearch($hs_code){

        $m = M('TaxRulesClass');

        $where['hs_code'] = $hs_code;        

        $info = $m->where($where)->order('sort')->field('cname1,cname2,cname3,cname4,cname5')->find();

        if(empty($info)){

            return '';

        }else{

            $str_cname = implode('/',$info);

            return $str_cname;
        }
    }

    //根据税号获取税率
    public function _rate_moren($hs_code){

        $m = M('TaxRulesClass');
        
        $where['hs_code'] = $hs_code; 

        $one = $info = $m->where($where)->find();

        if($one){

    		$result = array('state'=>'yes', 'msg'=>$one['rate']);

        }else{

    		$result = array('state'=>'no', 'msg'=>'该税号未添加');            
        }

        return $result;
    }
}