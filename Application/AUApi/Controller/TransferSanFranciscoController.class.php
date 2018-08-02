<?php
/*
  中转到旧金山
*/
namespace AUApi\Controller;
use Think\Controller\RestController;
Class TransferSanFranciscoController extends RestController
{
//    protected $xmlsave = UPFILEBASE.'/Upfile/InStock_logs/';
    /**
     * 接受并回传确认发货信息
     * @param [type] [varname] [description]
     * @return [type] [description]
     */
    public function ttt()
    {
        $sfinfo = C('SF.info');
        //print_r($sfinfo);die();
        $res = array(
            'Order' => array(
                'orderid' => 'MK881000260US',  //客户订单号
                /*
                'j_company'     => '美购商城',
                'j_contact'     => '郑先生',
                'j_tel'         => '13920064421',
                'j_mobile'      => '13899524124',
                'j_country'     => '美国',
                'j_province'    => '加州',
                'j_city'        => '三番市',
                'j_county'      => '三番市',
                'j_address'     => '天安科技园',
                */
                'd_contact' => '何先生',
                'd_tel' => '13800138001',
                'd_mobile' => '13800138000',
                'd_country' => '中国',
                'd_province' => '广东省',
                'd_city' => '广州市',
                'd_county' => '',
                'd_address' => '番禺区大北路9号',
                'custid' => '',  //顺丰月结卡号
                'pay_method' => 1,   //付款方式： 1:寄方付 2:收方付 3:第三方付
                'parcel_quantity' => 1,   //包裹数量
                'express_type' => 1,   //业务类型 1.标准快递   2.顺丰特惠   3.电商特惠  7.电商速配
            )
        );
        //array_push($res['Order'], $sfinfo);
        $res['Order'] += $sfinfo;
        echo '<pre>';
        print_r($res);
        die();
        $Mkno = new \Org\MK\Tracking();
        //$data['MKNO'] = $Mkno->run();
        $sfkd = 'OrderService';
        $str = $Mkno->sfno($sfkd, $res);
        echo '<pre>';
        print_r($str);
    }

    //自定义log文件
    private function logger($txt){
        $dir = 'Application/Runtime/Logs/AUApi/';
        $log = date("y") . '_' . date("m") . '_' . date("d") . '_' ."read.log";
        if(!is_dir($dir)){
            mkdir($dir, 0777);
        }
        if(!file_exists($dir . $log)){
            fopen($dir . $log, "w") or die("Unable to open file!");;
        }
        $logUrl = $dir . $log;
        $result = file_put_contents($logUrl,"\n" . date("Y-m-d H:i:s") . "============================" . $txt, FILE_APPEND);
        return $result;
    }

    Public function read()
    {
        $mkno = trim(I('post.mkno'));//单号
        $tname = trim(I('post.tname'));//真实姓名
        if(empty($mkno) || empty($tname)){
            $data['mkno'] = trim($mkno);
            $data['status'] = '0';             //状态
            $data['code'] = '0';
            $data['codestr'] = '错误的客户端，请重新安装';     //提示信息
            $this->ajaxReturn($data);
        }

        // 订单信息
        $where['STNO']  = array('eq', $mkno);
        $where['MKNO']  = array('eq',$mkno);
        $where['_logic'] = 'or';
        $info = M('TranList')->field('MKNO,noid,idno,reTel,reAddr,TranKd,IL_state,receiver,pause_status')->where($where)->find();

        if(!$info || empty($info['MKNO'])){
            $data['mkno'] = trim(I('post.mkno'));
            $data['status'] = '未查询到包裹';             //状态
            $data['code'] = '0';
            $data['codestr'] = '未查询到包裹';     //提示信息
            $this->ajaxReturn($data);
            exit;
        }
        //检测是否已经停运
        if((int)$info['pause_status'] == 20){
            $data['mkno'] = trim(I('post.mkno'));
            $data['status'] = '已停运';             //状态
            $data['code'] = '0';
            $data['codestr'] = '该包裹已停运';     //提示信息
            $this->ajaxReturn($data);
            exit;
        }
        //检测是否重复
        if($info['IL_state'] == 19){
            $data['mkno'] = trim(I('post.mkno'));
            $data['status'] = '请勿重复操作中转旧金山';             //状态
            $data['code'] = '0';
            $data['codestr'] = '请勿重复操作中转旧金山';     //提示信息
            $this->ajaxReturn($data);
            exit;
        }

        //检测是否收寄
        if($info['IL_state'] != 12 && $info['IL_state'] != 16){
            $data['mkno'] = trim(I('post.mkno'));
            $data['status'] = '包裹不符合中转旧金山条件';             //状态
            $data['code'] = '0';
            $data['codestr'] = '包裹不符合中转旧金山条件';     //提示信息
            $this->ajaxReturn($data);
            exit;
        }
        //增加物流信息
        $dataAdd['MKNO'] = $info['MKNO'];
        $dataAdd['content'] = '已离开 '.$tname.' ，发往 旧金山';
        $dataAdd['create_time'] = date("Y-m-d H:i:s");
        $dataAdd['status'] = 19;
        $dataAdd['mantime'] = date("Y-m-d H:i:s");
        M('IlLogs')->data($dataAdd)->add();
        //更改
        $data['IL_state'] = 19;
        $data['ex_time'] = date("Y-m-d H:i:s");
        $data['ex_context'] = '已离开 '.$tname.'，发往 旧金山';
        $result = M('TranList')->where(array('MKNO' => $info['MKNO']))->data($data)->save();

        if($result){
            $mkno = array('state'=>'yes','status_content' => '发往旧金山成功', 'msg'=>'发往旧金山成功');
        }else{
            $mkno = array('state'=>'no','status_content' => '发往旧金山失败', 'msg'=>'发往旧金山失败');
        }

        if(isset($mkno['state']) && $mkno['state'] == 'no'){
            $data['mkno'] = trim(I('post.mkno'));
            $data['status'] = $mkno['status_content'] ? $mkno['status_content'] : '0';             //状态
            $data['code'] = '0';
            $data['codestr'] = $mkno['msg'];     //提示信息
            $this->ajaxReturn($data);
            exit;
        }else{
            $data['mkno'] = trim(I('post.mkno'));
            $data['status'] = '1';             //状态
            $data['code'] = '1';
            $data['codestr'] = $mkno['msg'];     //提示信息
            $this->ajaxReturn($data);
            exit;
        }
    }//END OF FUNCTION READ
}