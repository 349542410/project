<?php
/**
 * 美快官网 找回密码功能 服务器端
 */
namespace Api\Controller;
use Think\Controller\HproseController;
class ResetController extends HproseController{
	protected $crossDomain =	true;
	protected $P3P		   =    true;
	protected $get		   =    true;
	protected $debug 	   =    true;

//========================= 发送邮件找回密码 ======================
    public function getInfo($receiver,$address,$uuid){

        $info = M('UserList')->where(array('username'=>$receiver,'email'=>$address))->find();

        //验证是否存在要找回密码的用户名和邮箱是否一致且正确
        if(!isset($info['id'])){
            $res = array('status' => '0', 'code'=>'f001', 'msg' => '用户名或邮箱不存在');
            // $this->ajaxReturn($result);
           
        }else{

            // $uuid = $this->cuid();     //生成标识码   20160205 Jie 把$uuid转移到客户端控制器上
            $data['uid']  = $info['id'];
            $data['uuid'] = $uuid;

            //查找数据表中是否有这个记录
            $cid = M('RefindPwd')->where(array('uid'=>$info['id']))->getField('id');
            if($cid){
                //如果已存在，则更新
                M('RefindPwd')->where(array('id'=>$cid))->save($data);
            }else{
                //如果不存在，则添加
                M('RefindPwd')->add($data);
            }

            /*
            // 20160205 Jie 把$uuid转移到客户端控制器上，$url也相应的改为从客户端控制器上直接传过来(是完整的url)
            $url = U($sendUrl,array('code'=>base64_encode($uuid)));//$sendUrl."&code=".base64_encode($uuid);*/
            // dump($url);die;
            
            $res = array('status' => '1', 'msg' => '成功', 'info'=>$info);
        }
        return $res;
    }



//========================== 点击邮件设置新密码 ===========================\

    /**
     * 设置新密码 视图
     * @return [type] [description]
     */
    public function vInfo($uuid){
        $res = M('RefindPwd')->where(array('uuid'=>$uuid))->find();

        if($res){
            $info = M('UserList')->where(array('id'=>$res['uid']))->find();

            $backArr = array('status'=>'1', 'info'=>$info, 'time'=>$res['time']);
        }else{
            $backArr = array('status'=>'0', 'code'=>'s001','msg'=>'链接已过期');
        }

        return $backArr;
    }

    /**
     * 设置新密码 方法
     * @return [type] [description]
     */
    public function setPwd($code,$uucode,$pwd,$maxtime){

        $uuid = base64_decode($uucode);   //解析标识码
        $info = M('RefindPwd')->where(array('uuid'=>$uuid))->find();

        if(!$info) return array('status'=>'0', 'code'=>'s003','msg'=>'账户不存在');
            
        $user = M('UserList')->where(array('id'=>$info['uid']))->find();

        if(!$user) return array('status'=>'0', 'code'=>'s003','msg'=>'账户不存在');

        $gtime   = intval(strtotime($info['time']));//发送修改密码的邮件的时间
        $nowtime = intval(time());//现在的时间
        $max     = 60*intval($maxtime);        //获取时限参数值

        $mt = md5($user['reg_time'].$user['username'].$user['pwd']);

        //验证规则
        if($mt == $code){
            if(($nowtime - $gtime) > intval($max)){
                return array('status'=>'0','code'=>'s002','msg'=>'链接已超时，请重新申请再操作');
            }

            //检测新密码是否与旧密码相同
            $oldPwd = M('UserList')->where(array('id'=>$info['uid']))->getField('pwd');    //保存新密码
            if($pwd == $oldPwd){
                return array('status'=>'0','code'=>'s001','msg'=>'新旧密码不能相同');
            }

            $data['pwd'] = $pwd;
            $data['wrong_num'] = 0;
            $data['wrong_time'] = 0;
            $res = M('UserList')->where(array('id'=>$info['uid']))->save($data);    //保存新密码

            if($res){
                M('RefindPwd')->where(array('id'=>$info['id']))->delete();  //删除记录
                return array('status' => '1','msg' => '修改成功');      //成功
            }else{
                return array('status' => '0','code'=>'s400', 'msg' => '修改失败');
            }
        
        }else{

            return array('status' => '0','code'=>'s004', 'msg' => '链接无效');
        }


    }

}