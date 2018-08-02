<?php
/**
 * 公共数据的获取
 * 功能包括：状态可用且账户有所属权限的线路
 * 
 */
namespace Admin\Controller;
use Think\Controller;
class PublicLineDataController extends AdminbaseController{

    public $line_id  = '';//其他控制器传入的线路合集 默认字符串形式
    public $ids      = '';//采集的线路id合集
    public $ids_type = 'array';//采集的线路id合集 返回形式 ： string  和   array

    function _initialize() {
        parent::_initialize();
        $Pclient = new \HproseHttpClient(C('RAPIURL').'/AdminPublicLineData');     //读取、查询操作
        $this->Pclient = $Pclient;        //全局变量
    }

    // 比较两个数组，取出相同的值，返回一维数组
    public function intersect(){

        if($_SERVER['HTTP_HOST'] != 'mkadmin.app.megao.hk:83'){
            $all = session('admin.user_point_transit');
            if(empty($all)){
                // 获取账户所拥有的线路权限
                $all = $this->pointAll($type=2);//返回字符串形式的数据
                session('admin.user_point_transit',$all);
            }
        }else{
            // 获取账户所拥有的线路权限   测试用
            $all = array('point_id'=>'ALL','transit_id'=>'ALL');//$this->pointAll($type=1);
        }

        $transit_id = $all['transit_id'];  //权限所分配的线路合集

        // 用户拥有所有线路的权限
        if($transit_id == 'ALL'){
            //$this->line_id传入的线路不做任何限制，而$transit_id又具备所有线路权限，则返回true，允许查询所有线路信息
            if($this->line_id === true){
                $this->ids = true;
                // return '1';
            }else{//$this->line_id 有传入具体的线路限制，而$transit_id具备所有线路权限，则直接返回传入的线路即可
                $this->ids = explode(',', $this->line_id);
                // return '2';
            }
            return $this->ids;
        }

        // $transit_id 没有配置任何线路的权限
        if($transit_id == 'NONE'){
            die('您的账号尚未配置相应的线路权限，如果您的权限被重新分配，请尝试重新登录');
            $this->ids = false;
            return $this->ids;
        }

        $transit_id = explode(',', $transit_id);//字符串转成一维数组

        // $transit_id 只有部分线路的权限
        if(is_array($transit_id) && count($transit_id) > 0){
            // $this->line_id传入的线路不做任何限制，则直接返回$transit_id 所拥有的线路即可
            if($this->line_id === true){
                $this->ids = $transit_id;
                // return '3';
            }else{//如果$this->line_id有具体的线路限制，如“1,3,6”， 则需要跟 $transit_id 比较，然后取交集
                // 非数组
                if(!is_array($this->line_id)){
                    $id_arr = explode(',', $this->line_id);
                    // return '4';
                }else{
                    $id_arr = $this->line_id;
                    // return '5';
                }

                $this->ids = array_intersect($id_arr, $transit_id);//取两个数组的交集

                // 两者之间没有任何交集，那么就表示没有对应模块的线路权限
                if(count($this->ids) == 0){
                    die('您的账号尚未配置相应的线路权限，如果您的权限被重新分配，请尝试重新登录');
                }
            }

            return $this->ids;
        }else{
            die('您的账号尚未配置相应的线路权限，如果您的权限被重新分配，请尝试重新登录');
        }
    }

    // 获取线路列表
    public function get_lines(){
        $map = array();
        if($this->ids !== true) $map['id'] = array('in',$this->ids);
        return $this->Pclient->_get_lines($map);
    }

}