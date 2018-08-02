<?php
/**
 * 美快后台快递公司管理  服务器端
 */
namespace Api\Controller;
use Think\Controller\HproseController;
class TransitController extends HproseController{
    protected $crossDomain =    true;
    protected $P3P         =    true;
    protected $get         =    true;
    protected $debug       =    true;

	/**
	 * 查总数 中转线路
	 * @param  [type] $where [description]
	 * @return [type]        [description]
	 */
    public function _center($where,$p,$ePage){

        $list = M('TransitCenter tc')->field('tc.*,ec.company_name')->join('LEFT JOIN mk_express_company ec ON tc.airid = ec.id')->where($where)->order('id asc,ctime desc')->page($p.','.$ePage)->select();

    	$count = M('TransitCenter tc')->join('LEFT JOIN mk_express_company ec ON tc.airid = ec.id')->where($where)->count();

    	return array('count'=>$count, 'list'=>$list);
    }

    /**
     * 航空公司列表
     * @return [type] [description]
     */
    public function ec(){
    	$elist = M('ExpressCompany')->where(array('status'=>'1'))->select();
    	return $elist;
    }

    /**
     * 查看  中转线路
     * @return [type] [description]
     */
    public function center_info($map,$type=''){

    	if($type == '2'){	//如果是修改页面请求的
    		$info = M('TransitCenter')->where($map)->find();
    	}else{
    		$info = M('TransitCenter tc')->field('tc.*,ec.company_name')->join('LEFT JOIN mk_express_company ec ON tc.airid = ec.id')->where($map)->find();
    	}
        
        return $info;
    }

    /**
     * 添加  中转线路
     * @return [type] [description]
     */
    public function center_add($arr,$creater){

        $m = D('TransitCenter');

        if($m->create($arr)){

            foreach($arr as $key=>$v){
                $data[$key] = trim($v);
            }
            if($data['input_idno'] == 0){
                $data['member_sfpic_state'] = 0;
            }
            $data['creater'] = $creater;
            $data['ctime']   = date('Y-m-d H:i:s');

            $res = M('TransitCenter')->add($data);
            
            if($res){
                $result = array('state'=>'yes', 'msg'=>'添加成功');
            }else{
                $result = array('state'=>'no', 'msg'=>'添加失败');
            }
            return $result;

        }else{
            $msg = $m->getError();
            $result = array('state'=>'no','msg'=>$msg);
            return $result;
        }

    }

    /**
     * 修改 中转线路
     * @return [type] [description]
     */
    public function center_edit($arr){

    	$id = trim($arr['id']);
    	unset($arr['id']);

    	//检查是否存在此id的对应信息
    	$check = M('TransitCenter')->where(array('id'=>$id))->find();
    	if(!$check){
    		$result = array('state' => 'no', 'msg' => '参数不存在');
            return $result;
    	}

        $m = D('TransitCenter');

        if($m->create($arr)){

            foreach($arr as $key=>$v){
                $data[$key] = trim($v);
            }

            if($data['input_idno'] == 0){
                $data['member_sfpic_state'] = 0;
            }
            $res = M('TransitCenter')->where(array('id'=>$id))->save($data);
            
            if($res == 0){
                $result = array('state' => 'no', 'msg' => '没有数据更新');
            }else if($res === false){
                $result = array('state'=>'no', 'msg'=>'修改失败');
            }else{
                $result = array('state'=>'yes', 'msg'=>'修改成功');
            }
            return $result;

        }else{
            $msg = $m->getError();
            $result = array('state'=>'no','msg'=>$msg);
            return $result;
        }

    }

	/**
	 * 查总数  中转单号
	 * @param  [type] $where [description]
	 * @return [type]        [description]
	 */
    public function no_count($where,$p,$ePage){
        $list = M('TransitNo tn')->field('tn.*,op.username as creater,ec.company_name')->join('LEFT JOIN mk_transit_center tc ON tn.tcid = tc.id')->join('LEFT JOIN mk_express_company ec ON tn.airid = ec.id')->join('LEFT JOIN mk_operator_list op ON op.id = tn.createid')->where($where)->order('date desc')->page($p.','.$ePage)->select();

    	$count = M('TransitNo tn')->join('LEFT JOIN mk_transit_center tc ON tn.tcid = tc.id')->join('LEFT JOIN mk_express_company ec ON tn.airid = ec.id')->join('LEFT JOIN mk_operator_list op ON op.id = tn.createid')->where($where)->count();
    	return array('count'=>$count, 'list'=>$list);
    }

    /**
     * 修改 中转单号
     * @return [type] [description]
     */
    public function no_edit(){
            // $m = D('TransitNo');

            // if($m->create()){

            //     $arr = I('post.');

            //     $result  = $client->center_edit($arr);
            //     $this->ajaxReturn($result);

            // }else{
            //     $msg = $m->getError();
            //     $result = array('state'=>'no','msg'=>$msg);
            //     $this->ajaxReturn($result);
            // }
    }

    /**
     * 查看 中转单号
     * @return [type] [description]
     */
    public function no_info($id){
        $map['tn.id'] = array('eq',$id);
        $res = M('TransitNo tn')->field('tn.*,tc.creater,ec.company_name')->join('LEFT JOIN mk_transit_center tc ON tn.tcid = tc.id')->join('LEFT JOIN mk_express_company ec ON tn.airid = ec.id')->where($map)->find();
        return $res;
    }

	/**
	 * 查总数
	 * @param  [type] $where [description]
	 * @return [type]        [description]
	 */
    public function no2_count($where,$p,$ePage){
        $list = M('TransitNo2 tn')->field('tn.*,tc.creater,ec.company_name')->join('LEFT JOIN mk_transit_center tc ON tn.tcid = tc.id')->join('LEFT JOIN mk_express_company ec ON tn.airid = ec.id')->where($where)->order('date desc')->page($p.','.$ePage)->select();

    	$count = M('TransitNo2 tn')->join('LEFT JOIN mk_transit_center tc ON tn.tcid = tc.id')->where($where)->count();
    	return array('count'=>$count, 'list'=>$list);
    }


    /**
     * 授权码列表 总数统计
     * @return [type] [description]
     */
    public function code_count($map,$p,$ePage){
        $list = M('AuthCodeList cl')->field('cl.id,cl.remark,cl.simple_remark,cl.ctime,tc.name,ml.tname creater')
            ->join('LEFT JOIN mk_transit_center tc ON tc.id = cl.tcid')
            ->join('LEFT JOIN mk_manager_list ml ON ml.id = cl.createid')
            ->where($map)->order('ctime desc')->page($p.','.$ePage)->select();

        $count = M('AuthCodeList cl')
            ->join('LEFT JOIN mk_transit_center tc ON tc.id = cl.tcid')
            ->join('LEFT JOIN mk_manager_list ml ON ml.id = cl.createid')
            ->where($map)->count();
            
        return array('count'=>$count, 'list'=>$list);
    }

    /**
     * 授权码修改 视图
     * @return [type] [description]
     */
    public function _code_edit($id){
        //中转线路
        $elist = M('TransitCenter')->field('id,name')->where(array('status'=>'1'))->select();

        $info = M('AuthCodeList')->field('id,tcid,auth_code,remark,simple_remark,email')->where(array('id'=>$id))->find();
        return $res = array($elist,$info);
    }

    /**
     * 授权码添加 视图
     * @return [type] [description]
     */
    public function _code_add(){
        //中转线路
        $elist = M('TransitCenter')->field('id,name')->where(array('status'=>'1'))->select();
        return $elist;
    }

    /**
     * 授权码添加 方法
     * @return [type] [description]
     */
    public function _create_code($arr,$createid){

        $m = D('AuthCodeList');

        if($m->create($arr)){

            foreach($arr as $key=>$v){
                $data[$key] = trim($v);
            }
            
            $data['auth_code'] = MD5($data['auth_code']);   //授权码 加密
            $data['createid'] = $createid;
            $data['ctime']   = date('Y-m-d H:i:s');

            $res = M('AuthCodeList')->field('tcid,auth_code,remark,createid,ctime,simple_remark,email')->filter('strip_tags')->add($data);
            
            if($res){
                $result = array('state'=>'yes', 'msg'=>'添加成功');
            }else{
                $result = array('state'=>'no', 'msg'=>'添加失败');
            }
            return $result;

        }else{
            $msg = $m->getError();
            $result = array('state'=>'no','msg'=>$msg);
            return $result;
        }

    }

    /**
     * 授权码修改 方法
     * @return [type] [description]
     */
    public function _update_code($arr){

        $id = trim($arr['id']);
        unset($arr['id']);

        //检查是否存在此id的对应信息
        $check = M('AuthCodeList')->where(array('id'=>$id))->find();
        if(!$check){
            $result = array('state' => 'no', 'msg' => '参数不存在');
            return $result;
        }

        $m = D('AuthCodeList');

        if($m->create($arr)){

            foreach($arr as $key=>$v){
                $data[$key] = trim($v);
            }

            $data['auth_code'] = $data['auth_code'] ? MD5($data['auth_code']) : $check['auth_code'];   //授权码 加密

            //只允许更新三个字段
            $res = M('AuthCodeList')->field('tcid,auth_code,remark,simple_remark,email')->filter('strip_tags')->where(array('id'=>$id))->save($data);
            
            if($res == 0){
                $result = array('state' => 'no', 'msg' => '没有数据更新');
            }else if($res === false){
                $result = array('state'=>'no', 'msg'=>'修改失败');
            }else{
                $result = array('state'=>'yes', 'msg'=>'修改成功');
            }
            return $result;

        }else{

            $msg = $m->getError();
            $result = array('state'=>'no','msg'=>$msg);
            return $result;

        }
    }

    /**
     * 授权码 删除
     */
    public function _delete_code($id){
        $check = M('AuthCodeList')->where(array('id'=>$id))->find();

        //不同浏览器之间的误差操作
        if(!$check){
            $result = array('state'=>'no', 'msg'=>'参数错误，请刷新再试');
            return $result;
        }

        $del = M('AuthCodeList')->where(array('id'=>$id))->limit(1)->delete();
        if($del){
            $result = array('state'=>'yes', 'msg'=>'删除成功');
        }else{
            $result = array('state'=>'no', 'msg'=>'删除失败');
        }
        return $result;
    }

//===================================
    public function _line_price($id){
        $info = M('LinePrice')->where(array('line_id'=>$id))->find();
        return $info;
    }

    public function _edit_line_price($id,$data){
        $check = M('LinePrice')->where(array('id'=>$id, 'line_id'=>$data['line_id']))->find();

        if($check){
            $res = M('LinePrice')->where(array('id'=>$id))->save($data);
        }else{
            $res = M('LinePrice')->add($data);
        }

        if($res !== false){
            $backArr = array('state'=>'yes', 'msg'=>'编辑成功');
        }else{
            $backArr = array('state'=>'no', 'msg'=>'编辑失败');
        }
        
        return $backArr;
    }
}