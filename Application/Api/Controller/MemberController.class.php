<?php
/**
 * 会员资料 服务器端
 */
namespace Api\Controller;
use Think\Controller\HproseController;
class MemberController extends HproseController{    
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
        $list = M('UserInfo')->join('RIGHT JOIN mk_user_list ON mk_user_list.id=mk_user_info.user_id')->order('reg_time desc')->where($where)->page($p.','.$ePage)->select();

        $count = M('UserInfo')->join('RIGHT JOIN mk_user_list ON mk_user_list.id=mk_user_info.user_id')->where($where)->count(); // 查询满足要求的总记录数
        
        return array('count'=>$count, 'list'=>$list);
    }

    /**
     * 解锁用户
     */

    public function unLock($userId)
    {
        return M('userList')->where(array('id'=>$userId))->save(array('wrong_num'=>0,'wrong_time'=>0));
    }
    /**
     * 会员资料编辑
     * @param  [type] $id [description]
     * @return [type]     [description]
     */
    public function edit($id){
		$result = M('UserInfo')->join('RIGHT JOIN mk_user_list ON mk_user_list.id=mk_user_info.user_id')->where(array('mk_user_list.id'=>$id))->order('reg_time')->select();
		return $result;
    }

    /**
     * 获取被编辑的会员的信息
     * @param  [type] $id [description]
     * @return [type]     [description]
     */
    public function getone($id){
        $getone = M('UserList')->where(array('id'=>$id))->find();
        return $getone;
    }

    /**
     * 更新会员资料
     * @param  [type] $id   [description]
     * @param  [type] $list [description]
     * @param  [type] $info [description]
     * @return [type]       [description]
     */
    public function _update($id,$list,$info){   
        $Model = M();   //实例化
        $Model->startTrans();//开启事务

        //如果数据库中已经有$id这个值
        $res = M('UserInfo')->where(array('user_id'=>$id))->find();
        if($res){
            $save = M('UserInfo')->where(array('user_id'=>$id))->save($info);
        }else{//如果没有
            $save = M('UserInfo')->add($info);
        }

        $rs = M('UserList')->where(array('id'=>$id))->save($list);

        if($save == 0 && $rs == 0){

            $Model->commit();//提交事务成功
            $result = array('state' => 'no', 'msg' => '没有数据更新');

        }else if($save === false || $rs === false){

            $Model->rollback();//事务有错回滚
            $result = array('state' => 'no', 'msg' => '更新失败');

        }else{
            
            $Model->commit();//提交事务成功
            $result = array('state' => 'yes', 'msg' => '更新成功');
        }

        return $result;

    }

    /**
     * 删除
     * @param  [type] $id [description]
     * @return [type]     [description]
     */
    public function delete($id){
        
        $he = M('UserList')->where(array('id'=>$id))->find();

        if(!$he){
            $result = array('state'=>'no', 'msg'=>'参数错误，请刷新再试');
            return $result;
        }

        //查找user_info里面是否有这个id
        $did = M('UserInfo')->where(array('user_id'=>$id))->getField('id');

        $Model = M();   //实例化
        $Model->startTrans();//开启事务

        if($did){
            //user_info有这个ID，可以删除user_info中对应的ID数据
            $del = M('UserInfo')->where(array('id'=>$did))->delete();
        }else{
            //user_info没有这个ID，即user_info里面根本没有对应于此ID的数据，user_list里面的此ID数据可以删了
            M('UserList')->where(array('id'=>$id))->delete();

            $Model->commit();//提交事务成功

            $result = array('state' => 'yes', 'msg' => '删除成功');
            
            return $result;
        }

        //如果已经在user_info上进行了删除，则user_list也删除对应的ID数据
        if($del){

            M('UserList')->where(array('id'=>$id))->delete();

            $Model->commit();//提交事务成功

            $result = array('state' => 'yes', 'msg' => '删除成功');

            return $result;
        }else{//如果未能在user_info上进行删除操作，则终止所有删除行为

            $Model->rollback();//事务有错回滚

            $result = array('state' => 'no', 'msg' => '删除失败');

            return $result;
        }

    }

    /**
     * 20151119 Jie
     * 后台系统人工审核通过 方法
     * @return [type] [description]
     */
    public function _examine($id){

        //检查是否已经存在
        $check = M('UserList')->alias('ul')->join('left join mk_user_info AS uin ON ul.id = uin.user_id')->where(array('ul.id'=>$id))->find();

        if(!$check){
            $result = array('status'=>'0', 'info'=>'参数错误,数据不存在');
            return $result;
        }else if($check['status'] != '0'){
            $result = array('status'=>'0', 'info'=>'请勿重复进行审核操作');
            return $result;
        }

        if(empty($check['username']) || empty($check['email'])  ){
            $result = array('status'=>'0', 'info'=>'必填参数未完善，请重新补填');
            return $result;
        }


        $data['status'] = '1';  //审核通过
        $res = M('UserList')->where(array('id'=>$id))->save($data);

        if($res){

            $result = array('status'=>'yes', 'data'=>$check);

/*            $str = ($check['lang'] == 'zh-cn') ? C('examine_success_content_cn') : C('examine_success_content_en');
            $etitle = ($check['lang'] == 'zh-cn') ? '资料审核已通过' : 'Information has passed verification';
            $content = create_success_content($str, $check['username']);  //创建邮件内容
            // 发送邮件
            $result = $this->send_email($check['email'],$content,$check['username'],$etitle);*/

        }else{
            $result = array('status'=>'0', 'info'=>'操作失败');
        }
        return $result;
    }

    /**
     * 20151120 Jie
     * 后台系统人工审核不通过  方法
     * @param  [type] $id [会员id]
     * @param  [type] $msg [邮件内容]
     * @param  [type] $mid [审核不通过的类型编号  从2至9]
     * @return [type] [description]
     */
    public function _not_examine($id,$msg,$mid){
        //检查是否已经存在
        $check = M('UserList')->where(array('id'=>$id))->find();

        if(!$check){
            $result = array('status'=>'0', 'info'=>'参数错误,数据不存在');
            return $result;
        }else if($check['status'] != '0'){
            $result = array('status'=>'0', 'info'=>'该资料信息已被审核');
            return $result;
        }

        $data['status'] = $mid; //把审核不通过的编号保存到对应的会员信息上
        $data['step']   = $mid-1; //把审核不通过的编号对应到相应的注册步骤  20170623 jie
        $res = M('UserList')->where(array('id'=>$id))->save($data);

        if($res){

            $result = array('status'=>'yes', 'data'=>$check);

/*            $str = ($check['lang'] == 'zh-cn') ? C('examine_fail_content_cn') : C('examine_fail_content_en');
            $etitle = ($check['lang'] == 'zh-cn') ? '资料审核未通过' : 'Information has not passed verification';
            $content = create_fail_content($str, $check['username'], $msg);  //创建邮件内容
            // 发送邮件
            $result = $this->send_email($check['email'],$content,$check['username'],$etitle);*/

        }else{
            $result = array('status'=>'0', 'info'=>'操作失败');
        }
        return $result;
    }

//====================== 设置 会员优惠 ======================
    /**
     * 某个会员各个线路优惠列表
     * @param  [type] $user_id [会员ID]
     * @return [type]          [description]
     */
    public function _member_of_discount($user_id){

        $map = array();
        $map['t.optional'] = array('eq',1);
        $map['t.status']   = array('eq',1);
        $line_discount = M('TransitCenter t')->field('t.id,t.name,l.user_id,l.discount_service,l.discount_first,l.discount_next')->join('left join mk_line_discount l on l.line_id = t.id and l.user_id = '.$user_id)->where($map)->select();

        return $line_discount;

    }

    /**
     * 设置某个会员各个线路优惠
     * @param  [type] $line_id  [线路ID]
     * @param  [type] $user_id  [会员ID]
     * @param  [type] $name     [编辑的字段]
     * @param  [type] $val      [编辑的值]
     * @param  [type] $operator [操作人ID]
     * @return [type]           [description]
     */
    public function _edit_discount($line_id, $user_id, $name, $val, $operator){

        $check = M('LineDiscount')->where(array('line_id'=>$line_id,'user_id'=>$user_id))->find();

        $data = array();
        $data[$name]         = $val;
        $data['operator_id'] = $operator; //操作人ID
        //此ID已经存在，则数据更新
        if($check){

            $res = M('LineDiscount')->where(array('id'=>$check['id']))->save($data);
        }else{
            $data['line_id'] = $line_id;
            $data['user_id'] = $user_id;
            $res = M('LineDiscount')->add($data);
        }

        if($res !== false){
            return array('state'=>'yes', 'msg'=>'操作成功', 'val'=>$val);
        }else{
            return array('state'=>'no', 'msg'=>'操作失败');
        }
    }

}