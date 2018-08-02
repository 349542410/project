<?php
namespace Admin\Controller;
use Think\Controller;
class AdminCeShiController extends AdminbaseController
{
    public $client;

    function __construct()
    {
        parent::__construct();
        vendor('Hprose.HproseHttpClient');
        $this->client = new \HproseHttpClient(C('RAPIURL') . '/AdminCeShi');        //读取、查询操作
        //$this->writes = new \HproseHttpClient(C('WAPIURL').'/AdminCollectPoint');		//添加，修改
    }

    //导入EMS单号 视图
    public function transfer(){

        $tcid = (C('Transit_Type.MKBc3_Transit')) ? C('Transit_Type.MKBc3_Transit') : '';

        $tcids = explode(",",$tcid);
        $client = $this->client;
        $center_list = $client->_center_list($tcids);

        $this->assign('center_list',$center_list);

        $this->display();
    }

    /**
     * 导入EMS单号 方法   导入CSV(按照批次号归类的，即同一个csv文件里面全是同一个批次号的单号)
     * @return [type] [description]
     */
    public function import_csv(){

        ini_set('memory_limit','4088M');
        ini_set('max_execution_time', 0);

        G('begin');

        /*      功能使用一段时间后没问题的话，这段代码可以删除 20180109 jie
                $upload           = new \Think\Upload();// 实例化上传类
                $upload->maxSize  = 1048576*50 ;// 设置附件上传大小
                $upload->exts     = array('csv', 'xls', 'xlsx');// 设置附件上传类型
                $upload->rootPath = K(ADMIN_ABS_FILE); //设置文件上传保存的根路径
                $upload->savePath = C('UPLOADS'); // 设置文件上传的保存路径（相对于根路径）
                $upload->autoSub  = true; //自动子目录保存文件
                $upload->subName  = array('date','Ymd');
                $upload->saveName = array('uniqid',mt_rand()); //设置上传文件名

                $info = $upload->upload();

                // MkilImportMarket这个类需要读取这个参数
                $info['file']['tmp_name'] = K(ADMIN_ABS_FILE . $info['file']['savepath'] . $info['file']['savename']);
                // dump($info);die;
                if(!$info) {// 上传错误提示错误信息
                    // $this->error($upload->getError());
                    $result = array('status'=>'0','msg'=>$upload->getError());
                    $this->ajaxReturn($result);exit;
                }

              // if (file_exists(UPLOAD_PATH . $_FILES["file"]["name"])) {
              //       // echo $_FILES["file"]["name"] . " already exists. ";
              //   } else {
              //       move_uploaded_file($_FILES["file"]["tmp_name"],
              //       UPLOAD_PATH . $_FILES["file"]["name"]);
              //       // echo "Stored in: " . UPLOAD_PATH . $_FILES["file"]["name"];
              //   // $filename  = $_FILES;//上传的csv文件
              //   }
                $filename  = $info;//上传的csv文件*/

        $tran_type   = I('tran_type');//转发快递之后，承接运单号的快递公司
        $sure_post   = I('sure_post') ? I('sure_post') : '';//是否推送给快递100
        $force_kd100 = I('force_kd100') ? I('force_kd100') : '';//强制推送给快递100
        $force_erp   = I('force_erp') ? I('force_erp') : '';//强制推送给ERP
        $kind        = I('lineId');  //线路ID
        $first_run   = I('first_run') ? trim(I('first_run')) : 'first';  //是否初次提交

        // dump($first_run);die;
        // 20180109 jie
        $importexcel = new \Libm\MKILExcel\MkilImportMarket;
        $importexcel->inputFileName  = $_FILES['file']['tmp_name'];
        $arr = $importexcel->import();
        //dump($arr);die;
        //如果返回的是false
        if($arr === false){
            $this->ajaxReturn(array('status'=>'0','msg'=>$importexcel->getError()));exit;
        }
        unset($arr[0]);//根据实际情况，去除数组第一个分支
        // 20180109 jie end

        // 判断处理后的数组是否为空
        $len_result = count($arr);
        if($len_result == 0){
            $result = array('status'=>'0','msg'=>'没有任何数据！');
            $this->ajaxReturn($result);exit;
        }
        // dump($result);die;
        $client = $this->client;

        // $kind = C('Transit_Type.MKBc3_Transit');//线路ID  //由于MKBc3_Transit可能包含多个线路ID，所以此处改为由页面点击的时候传入线路ID

        $result = $client->_index($arr,$tran_type,$sure_post,$force_kd100,$force_erp,$kind,$first_run);
//        if($first_run != 'first'){
//            dump($result);
//            exit;
//        }
        G('end');
        //$result['msg'] .= '。<br />耗时：'.G('begin','end').'s';  //耗时时间显示
        $this->ajaxReturn($result);
        // echo G('begin','end').'s';
        // dump($result);
    }


}