<?php
/**
 * 号段管理
 */
namespace Api\Controller;
use Think\Controller\HproseController;

class LogisticsController extends HproseController
{
    //获取所有可用的线路
    public function get_center_list()
    {
        //todo 以前是一条线路一个号段表  23线路之后把所有的号段组合在一起  以前的线路怎么放再讨论
        $where['id'] = array('EGT',23);
        $where['status'] = 1;
        return M('TransitCenter')->where($where)->order('ctime desc')->field('id,name')->select();
    }

    //获取线路下的所有号段
    public function _index($where,$p,$ePage)
    {
        $list = M('LogisticsTransit t')
            ->field('n.id,n.no,n.MKNO,n.status,n.use_time')
            ->join('LEFT JOIN mk_logistics_no n ON n.id=t.logistics_id')
            ->where($where)
            ->order('n.status,n.add_time desc,n.id desc')
            ->page($p.','.$ePage)
            ->select();

        $count = M('LogisticsTransit t')
            ->join('LEFT JOIN mk_logistics_no n ON n.id=t.logistics_id')
            ->where($where)
            ->count();

        //获取未使用号段的数量
        $where2['n.status'] = 0;
        $where2['t.transit_center_id'] = $where['t.transit_center_id'];
        $warn = M('LogisticsTransit t')
            ->join('LEFT JOIN mk_logistics_no n ON n.id=t.logistics_id')
            ->where($where2)
            ->count();

        return array('count'=>$count,'list'=>$list,'warn'=>$warn);
    }

    //判断线路是否可用
    public function TranKd($line_id)
    {
        $where['status'] = 1;
        $where['id'] = $line_id;
        $one = M('TransitCenter')->where($where)->find();
        if(empty($one)){
            return false;
        }else{
            return true;
        }
    }

    //导入
    public function _import_excel($arr,$line_id)
    {
        ini_set('max_execution_time', 0);
        ini_set('memory_limit','4088M');

        //todo 以前是一条线路一个号段表  23线路之后把所有的号段组合在一起  以前的线路怎么放再讨论
        if($line_id < 23){
            return array('status'=>'0', 'msg'=>'该功能不支持该线路');
        }

        $list     = array();
        $not_list = array();//放重复或者不符合正则
        $msg['status'] = 0;

        unset($arr[0]);

        foreach ($arr as $key => $v) {

            if(empty($v[0])){

                unset($arr[$key]);

            }else if($this->noRepeat($v[0]) && preg_match("/^[A-Za-z0-9]{1,30}$/",$v[0])){//
                $msg['no'] = $v[0];
                $list[] = $msg;
            }else{
                $tmp['no']  = $v[0];
                $not_list[] = $tmp;
            }
        }

        //判断$list是否有值
        $count = count($list);

        if($count < 1){
            return array('status'=>'0', 'msg'=>'该文件没有数据或者不符合规范');
        }

        $no_model = M('LogisticsNo');
        $lt_model = M('LogisticsTransit');

        $i = 0;	//保存失败数量
        $j = 0;	//保存成功数量

        foreach ($list as $key => $v) {

            $res = $this->noRepeat($v['no']);

            if($res === true){
                $no_model->startTrans();//开启事务

                $v['add_time'] = date('Y-m-d H:i:s');
                $add = $no_model->add($v);

                $data['logistics_id']      = $add;
                $data['transit_center_id'] = $line_id;
                $add2 = $lt_model->add($data);

                if($add && $add2){
                    $no_model->commit();
                    $j++;

                }else{
                    $lt_model->rollback();
                    $i++;
                }
            }
        }

        $msg = '不符合规范：'.count($not_list).'个，成功：'.$j.'个，失败：'.$i.'个';
        //保存的数量 > 0，则成功
        if($j > 0){

            $backArr = array('status'=>'1', 'msg'=>'导入成功，'.$msg);
            return $backArr;

        }else{

            $backArr = array('status'=>'0', 'msg'=>'导入失败，'.$msg);
            return $backArr;
        }
    }

    //增加一条
    public function _add($no,$line)
    {
        //todo 以前是一条线路一个号段表  23线路之后把所有的号段组合在一起  以前的线路怎么放再讨论
        if($line < 23){
            return array('state'=>'no', 'msg'=>'该功能不支持该线路');
        }

        //判断线路是否可用
        $transit = $this->TranKd($line);
        if($transit === false){
            return array('state'=>'no','msg'=>'线路不可用');
        }

        //判断号段是否重复
        $no_res = $this->noRepeat($no);
        if($no_res === false){
            return array('state'=>'no','msg'=>'该号段已经存在');
        }

        $no_model = M('LogisticsNo');
        $lt_model = M('LogisticsTransit');

        $no_model->startTrans();//开启事务

        $no_data['no'] = $no;
        $no_data['add_time'] = date('Y-m-d H:i:s');
        $add = $no_model->add($no_data);

        $data['logistics_id']      = $add;
        $data['transit_center_id'] = $line;
        $add2 = $lt_model->add($data);

        if($add && $add2){
            $no_model->commit();
            return array('state'=>'yes','msg'=>'添加成功');
        }else{
            $lt_model->rollback();
            return array('state'=>'no','msg'=>'添加失败');
        }
    }

    //判断号段是否重复
    public function noRepeat($no)
    {
        $where['no'] = $no;
        $info = M('LogisticsNo')->where($where)->find();

        if(empty($info)){
            return true;
        }else{
            return false;
        }
    }
}