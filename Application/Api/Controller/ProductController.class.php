<?php
/**
 * BC货品
 */
namespace Api\Controller;
use Think\Controller\HproseController;
class ProductController extends HproseController{

    public $rules = array(
        'name'            =>array(true,100,'报关名称'),
        'show_name'       =>array(true,100,'名称'),
        'brand'           =>array(true,100,'品牌'),
        'hs_code'         =>array(false,20,'HS编码'),
        'tariff_no'       =>array(false,20,'行邮税号'),
        'hgid'            =>array(false,50,'跨境ID'),
        'price'           =>array(false,11,'申报金额'),
        'unit'            =>array(false,10,'计量单位'),
        'source_area'     =>array(false,50,'原产地国别'),
        'barcode'         =>array(false,20,'BarCode'),
        'specifications'  =>array(false,200,'规格型号'),
        'net_weight'      =>array(false,6,'净重'),
        'rough_weight'    =>array(false,6,'毛重'),
        'parameter_one'   =>array(false,32,'参数一'),
        'parameter_two'   =>array(false,32,'参数二'),
        'parameter_three' =>array(false,32,'参数三'),
        'parameter_four'  =>array(false,32,'参数四'),
        'parameter_five'  =>array(false,32,'参数五'),
        'detail'          =>array(false,100,'货品详细描述'),
    );

    //检测
    public function check_data($data,$rules){
        
        foreach($rules as $k=>$v){

            if($v[0] && empty($data[$k])){
                //必须存在
                return array(
                    'success' => false,
                    'error' => '【'.$v[2].'】不能为空',//array($v[2],'不能为空'),
                );
            }

            if(!empty($data[$k]) && strlen($data[$k])>$v[1]){
                //长度不符合要求
                return array(
                    'success' => false,
                    'error' => '【'.$v[2].'字符长度太长',//array($k,'字符长度太长'),
                );
            }
            
        }

        return array(
            'success' => true,
            'error' => '',
        );

    }

	//查出 所有类别列表
	public function cat_list($lineID){
        $map['TranKd'] = array('like','%,'.$lineID.',%');
		$cat_list = M('CategoryList')->field('id,fid,cat_name,TranKd')->where($map)->select();
		return $cat_list;
	}

    //只显示bc_state=1的线路，即BC报关管理为开启状态的
    public function get_center_list(){
        return M('TransitCenter')->where(array('bc_state'=>'1'))->select();//只显示bc_state=1的线路，即BC报关管理为开启状态的
    }

    //获取线路所含顶级类别
    public function top_cat_list($map){
        return M('CategoryList')->field('id,fid,cat_name')->where($map)->order('cat_name asc')->select();
    }

	//查询数据总数
	public function _count($map,$p,$ePage){
        $list = M('ProductList p')->field('p.*,c.cat_name,m.name as manager')->join('left join mk_category_list c on c.id = p.cat_id')->join('left join mk_manager_list m on m.id = p.operator_id')->where($map)->order('p.ctime desc')->page($p.','.$ePage)->select();

        $count = M('ProductList p')->join('left join mk_category_list c on c.id = p.cat_id')->join('left join mk_manager_list m on m.id = p.operator_id')->where($map)->count(); // 查询满足要求的总记录数

        $center = $this->get_center_list();//只显示bc_state=1的线路，即BC报关管理为开启状态的
        return array('count'=>$count, 'list'=>$list, 'center'=>$center);
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

	//添加 方法
	public function _add_method($data){

        $a_model = M('ProductList');
        $data = $a_model->create($data);
        $cat_id = $data['cat_id'];

        //数据表字段验证
        if(empty($cat_id)){
            return array(
                'state' => 'no',
                'msg' => '请选择【所属类别】',
            );
        }

        $check_field = $this->check_data($data,$this->rules);
        if(!$check_field['success']){
            return array(
                'state' => 'no',
                'msg' => $check_field['error'],
            );
        }

        $check = $this->common_check($data, '');

        if($check['state'] == 'no'){
            return $check;
        }

    	if(M('ProductList')->add($data)){
    		$result = array('state'=>'yes', 'msg'=>'添加成功');
    	}else{
    		$result = array('state'=>'no', 'msg'=>'添加失败');
    	}
    	return $result;
	}

    public function common_check($data, $id){
        
        //检查是否在顶级类别下创建货品
        $fid = M('CategoryList')->where(array('id'=>$data['cat_id']))->getField('fid');
        if($fid == 0){
            return array('state'=>'no','msg'=>'顶级类别不允许创建货品');
        }

        if(empty($data['hs_code']) && empty($data['tariff_no'])){
            return array('state'=>'no', 'msg'=>'【HS编码】和【行邮税号】二者必须填写其中一个');
        }

        if(!empty($data['price'])){
            // 验证金额是否为数字
            if(!is_numeric($data['price'])){
                return array('state'=>'no', 'msg'=>'金额格式必须为数字');
            }

            //验证金额格式
            $chemon = "/^\d+(\.{0,1}\d+){0,1}$/";
            if(!preg_match($chemon,$data['price'])){
                return array('state'=>'no', 'msg'=>'错误的金额格式');
            }
        }

        // 20180209 jie 暂时停用所有验证重复的判断
        // $map['cat_id'] = array('eq',$data['cat_id']);
        // if(!empty($id)) $map['id']     = array('neq',$id);//不等于当前编辑中的自己的ID

/*      // 20180201 jie 报关名称 可以 重复
        $map['name']   = array('eq',$data['name']);
        $check_one = M('ProductList')->where($map)->find();
        //验证是否已经存在这个名字
        if($check_one){
            return array('state'=>'no', 'msg'=>'当前线路所选类别，报关名称【'.$data['name'].'】参数已存在');
        }*/

/*      // 20180209 jie 暂时停用所有验证重复的判断  
        unset($map['name']);
        $map['show_name'] = array('eq',$data['show_name']);
        $check_two = M('ProductList')->where($map)->find();
        //验证是否已经存在这个名字
        if($check_two){
            return array('state'=>'no', 'msg'=>'当前线路所选类别，名称【'.$data['show_name'].'】参数已存在');
        }*/

        // unset($map['show_name']);
        // $map['hgid']   = array('eq',$data['hgid']);
        // $check_three = M('ProductList')->where($map)->find();
        // //验证是否已经存在这个名字
        // if($check_three){
        //     return array('state'=>'no', 'msg'=>'当前线路所选类别，跨境ID【'.$data['hgid'].'】参数已存在');
        // }
    }

	//编辑 视图
	public function _edit($id){

		$info = M('ProductList')->where(array('id'=>$id))->find();
		return $info;
	}

	//编辑 方法
	public function _edit_method($id, $data){

        $a_model = M('ProductList');
        $data = $a_model->create($data);
        $cat_id = $data['cat_id'];

        //数据表字段验证
        if(empty($cat_id)){
            return array(
                'state' => 'no',
                'msg' => '请选择【所属类别】',
            );
        }

        $check_field = $this->check_data($data,$this->rules);
        if(!$check_field['success']){
            return array(
                'state' => 'no',
                'msg' => $check_field['error'],
            );
        }

        $check = $this->common_check($data, $id);

        if($check['state'] == 'no'){
            return $check;
        }

    	if(M('ProductList')->where(array('id'=>$id))->save($data) !== false){
    		$result = array('state'=>'yes', 'msg'=>'修改成功');
    	}else{
    		$result = array('state'=>'no', 'msg'=>'修改失败');
    	}
    	return $result;
	}

	// 查看详细信息
	public function _info($id){
		$map['p.id'] = array('eq',$id);
		$info = M('ProductList p')->field('p.*,c.cat_name')->join('left join mk_category_list c on c.id = p.cat_id')->where($map)->find();
		return $info;
	}

    /**
     * 删除
     */
    public function delete($id){

        $he = M('ProductList')->where(array('id'=>$id))->find();

        //检验数据是否存在
        if(!$he){
            $result = array('state'=>'no', 'msg'=>'参数错误，请刷新再试');
            return $result;
        }

        $check = M('TranUorder')->where(array('product_id'=>$id))->select();

        if($check || count($check) > 0){
            return array('state'=>'no', 'msg'=>'该货品分类已被使用或占用');
        }

    	$del = M('ProductList')->where(array('id'=>$id))->delete();
    	if($del){
    		$result = array('state'=>'yes', 'msg'=>'删除成功');
    	}else{
    		$result = array('state'=>'no', 'msg'=>'删除失败');
    	}
    	return $result;
    }


}