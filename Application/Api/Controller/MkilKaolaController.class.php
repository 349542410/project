<?php
/**
 * 考拉取美快物流信息接口
 * 20171017
 */
namespace Api\Controller;
//use Think\Controller;
use Think\Controller\HproseController;
class MkilKaolaController extends HproseController {
//class MkilKaolaController extends Controller{

    public function index()
    {
        # code...
    }
    public function getstatus($val)
    {
        $res = 20;
        switch ($val) {
            case 12:
                $res = 10;
                break;
            case 1005:
                $res = 40;
                break;
            case 1003:
                $res = 50;
                break;
            default:
                $res = 20;
                break;
        }
        return $res;
    }
    public function getlogs($mkno)
    {
        $mkno   = trim($mkno);
        //$mkno   = 'MK81000116US';
        $mkil   = array();
        $sname  = '';
        $data   = array(
            'code'      => 1,
            'message'   => '',
            'traces'    => null,
            'signName'  => '',
        );
        if(strlen($mkno)<9){
            $data['message']    = '运单号有误';
        }else{
            $where  = array('MKNO'=>$mkno);
            $tran   = M('tran_list')->field('receiver,IL_State')->where($where)->find();
            if($tran){
                if($tran['IL_State']==1003){
                    $sname = $tran['receiver'];
                }
                $list   = M('il_logs')->where($where)->order('id')->select();
                foreach($list as $val){
                    $mkil[] = array(
                        'state'     => $this->getstatus($val['status']),
                        'time'      => $val['create_time'],
                        'address'   => $val['address'],
                        'remark'    => $val['content'],
                    );
                }
                $data['signName']   = $sname;
                $data['traces']     = $mkil;
            }else{
                $data['message']='运单号('.$mkno.')不存在';
            }
                
        }
        return $data;
        //echo json_encode($data);
    }
}