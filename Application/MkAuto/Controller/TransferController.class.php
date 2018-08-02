<?php
/**
 * PDA
 * 打包中转
 */
namespace MkAuto\Controller;
use Think\Controller;
class TransferController extends BaseController {

    public function _initialize(){
        /* in BaseController
        //根据session检验登陆权限
        $value = session();
        $MKinfo = $value['MKinfo'];
        $this->MKinfo = $MKinfo;       //全局

        if($MKinfo['usertype'] != '30'){
            $this->error('无权限进入或未登陆',U('Index/index'));
        }
        */
        $this->usertype = 30;
        parent::_initialize();
    }

    public function index(){
        //echo date('ymdHis.z').microtime();exit;
        $this->display();
    }

    public function Area(){
        //151231改为不支持该方法
        $this->ajaxReturn(array('code'=>0,'codestr'=>'该方法已不再支持'));
        exit;


        //收到 code='123';
        //暂时直接返回
        //sleep(3);
        //更改为读取数据库 20150706
        $no = trim(I('post.no'));
        //$info = array('no'=>I('post.no'),'id'=>'1','name'=>"GT".$info['no']);
        vendor('Hprose.HproseHttpClient');
        $client = new \HproseHttpClient(C('WAPIURL').'/Send');
        $info = $client->transInfo($no);     //获取物流公司信息

        $this->ajaxReturn($info);
    }

    //20151215分析扫描的新型中转单号+批号，如果正确则返回{物流公司id-name,中转单号id,no,中转批号id,no}
    public function anacode(){
        $code = I('post.code','');
        if($code==''){
            $this->ajaxReturn(array('code'=>0,'codestr'=>'没有传来参数code'));
            exit;
        }
        vendor('Hprose.HproseHttpClient');
        $client = new \HproseHttpClient(C('WAPIURL').'/Mkil');
        $info = $client->anacode($code);
        $this->ajaxReturn($info);
    }

    //中转到旧金山
    public function transferSanFrancisco(){
        $mkno = trim(I('post.mkno'));//单号
        $tname = trim(I('post.tname'));//真实姓名
        if(empty($mkno) || empty($tname)){
            $data['mkno'] = trim($mkno);
            $data['status'] = '0';             //状态
            $data['code'] = '0';
            $data['codestr'] = '错误的客户端，请重新安装';     //提示信息
            $this->ajaxReturn($data);
        }

        //检验
        vendor('Hprose.HproseHttpClient');
        $client = new \HproseHttpClient(C('WAPIURL').'/Mkil');       //加载
        $mkno = $client->transferSanFrancisco($mkno, $tname);     //调用
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
    }

    public function Save(){
        $MKinfo = $this->MKinfo;
        
        $tname = $MKinfo['tname'];
        $ssid   = $MKinfo['ssid'];

        //sleep(3);
        $Airlineid = trim(I('post.Airlineid'));//'mtttt';   //数字或字符 //151231 no.id
        $Airline   = trim(I('post.Airline'));//'111';    //字符
        $AirNo     = trim(I('post.AirNo'));//'abc222';      //mk_transit_no.accno
        $mStr1     = trim(I('post.mStr1'));//'abc333';      //字符
        $mStr2     = trim(I('post.mStr2'));//'abc444';      //字符
        $mkno      = trim(I('post.mkno'));//'MK881000030US';        //单号
        $tcid      = trim(I('post.tcid',0)); //man20151231 批次号no.id
        $lang      = I('post.lang');
        /*151231用于测试返回tcid,no.id
            $data['status'] = '0';             //状态
            $data['code'] = $noid;
            $data['codestr'] = $tcid.'-'.$Airlineid;     //当Code不等于1时，Error这里显示出错的文字说明
            //dump($data);
            $this->ajaxReturn($data);
        */

        // $Airlineid = 'mtttt';   //数字或字符
        // $Airline   = '111';    //字符
        // $AirNo     = 'abc222';      //字符
        // $mStr1     = 'abc333';      //字符
        // $mStr2     = 'abc444';      //字符
        // $mkno      = 'MK881000169US';        //单号
        // $lang      = 'zh-cn';        //语言

        //这些都是必需项
        if(!$Airlineid || !$Airline || !$AirNo || !$mStr1 || !$mkno || $tcid==0){
            $data['mkno'] = trim($mkno);
            $data['status'] = '0';             //状态
            $data['code'] = '0';
            $data['codestr'] = '错误的客户端，请重新安装';     //提示信息
            //dump($data);
            $this->ajaxReturn($data);
            exit;
        }
        try{
            //检验是否为MKNO或者STNO  2017-08-08 jie
            vendor('Hprose.HproseHttpClient');
            $client = new \HproseHttpClient(C('WAPIURL').'/Mkil');       //加载
            $mkno = $client->check_MKNO($mkno, $this->usertype, $Airlineid, $tcid);     //调用

            if(isset($mkno['state']) && $mkno['state'] == 'no'){
                $data['mkno'] = trim(I('post.mkno'));
                $data['status'] = $mkno['status_content'] ? $mkno['status_content'] : '0';             //状态
                $data['code'] = '0';
                $data['codestr'] = $mkno['msg'];     //提示信息
                $this->ajaxReturn($data);
                exit;
            }
            // 2017-08-08 End

        }catch (\Exception $e){
            $data['mkno'] = trim(I('post.mkno'));
            $data['status'] = '0';             //状态
            $data['code'] = '0';
            $data['codestr'] = $e->getMessage() . '捕获异常';     //提示信息
            $this->ajaxReturn($data);
            exit;
        }

        // $mkno_match = "/^MK[0-9a-zA-Z]{11}$/";
        // // $mkno_match = "/^MK\d{9}[A-Za-z0-9]{2}$/";

        // //单号格式匹配，不匹配则返回错误
        // if(preg_match($mkno_match,$mkno) != true){
        //     $data['mkno'] = trim($mkno);
        //     $data['status'] = '0';             //状态
        //     $data['code'] = '0';
        //     $data['codestr'] = '运单号格式长度有误';     //提示信息
        //     //dump($data);
        //     $this->ajaxReturn($data);
        //     exit;
        // }
        
        // 列入二维数组中以便发送
        $toMKIL = array(
            array(
                'Airlineid' =>$Airlineid,
                'Airline'   =>$Airline,
                'AirNo'     =>$AirNo,
                'mStr1'     =>$mStr1,
                'mStr2'     =>$mStr2,
                'MKNO'      =>$mkno,
                'tcid'      =>$tcid,
            ),
        );

        $KD = "MKILLOG";            //用于确认是哪个类型的JSON
        $CID = "2";                 //为发送资料方在MK中的ID，美快暂为2
        //使用登录的ID与名称 20150706 Man
        $SID = $ssid;                //"20";
        $SNM = $tname;              //"美国加州";


        //$newJSON = new \Org\MK\JSON; 

        /*
            Man 180319
            增加config传输appid,key
            建议保存到db.php中
            将new放到init里
        */
        // $jconf 放置在db.php中
        // $jconf  = array(
        //     'appID' => '65412888',
        //     'Key'   => 'c260d0ed79c711e4b8ae382c4a62e14e',
        // );

        $newJSON = new \Org\MK\JSON(C('jconf'));

        $newMsg = $newJSON->build($KD,$CID,$SID,$SNM,$toMKIL,'');

        $post_data = array("MKIL"=>$newMsg);
        //$curl_url = "api.megao.us/api/PushWeigh";
        try{

            $curl_url = C('PDA_URL')."/LogsIn";
            $jas = $newJSON->post(0,$curl_url,$post_data,$lang);
        }catch (\Exception $e){
            $data['mkno'] = trim(I('post.mkno'));
            $data['status'] = '0';             //状态
            $data['code'] = '0';
            $data['codestr'] = $e->getMessage();     //提示信息
            $this->ajaxReturn($data);
            exit;
        }

        //dump($jas);
        $log = $jas['LOG'];
        $data['mkno'] = $log['0']['MKNO'];       //美快单号
        //当Code=1，表示成功返回，其它为错误  //当KD值有误时，Code为3
        if($jas['Code'] != '1' || $jas['KD'] != 'MKILLOG'){
            $data['mkno'] = $mkno;
            $data['status'] = '1';             //状态
            $data['code'] = $jas['Code'];
            $data['codestr'] = '完成';//$jas['Error'];     //当Code不等于1时，Error这里显示出错的文字说明
            //dump($data);
            $this->ajaxReturn($data, 'JSON');
            exit;
        }

        //CID固定为2，2表示国际物流自身使用的appID,appKey
        if($jas['CID'] != '2'){
            $data['status'] = '0';             //状态
            $data['code'] = $jas['CID'];
            $data['codestr'] = 'CID值不一致';     //当Code不等于1时，Error这里显示出错的文字说明
            //dump($data);
            $this->ajaxReturn($data, 'JSON');
            exit;
        }

        // Success: 回传是否保存成功, LOGCODE: 1表示成功
        if($log['0']['Success'] == 'true' && $log['0']['LOGCODE'] == '1'){
            $data['status'] = '1';             //状态
            $data['code'] = '1';       //LOGCODE为错误编码作为判断状态
            $data['codestr'] = $log['0']['LOGSTR'];     //LOGSTR：与错误编码对应的错误信息
            //dump($data);
            $this->ajaxReturn($data, 'JSON');
            exit;
        }else{
            $data['status'] = '0';              //状态
            $data['code'] = '0' ;       //LOGCODE为错误编码作为判断状态
            $data['codestr'] = $log['0']['LOGSTR'];     //LOGSTR：与错误编码对应的错误信息
            //dump($data);
            $this->ajaxReturn($data, 'JSON');
            exit;
        }
        exit();
    }

    public function Stop(){
        $this->display();
    }

    public function StopSave(){
        $mkno_match     = "/^MK[0-9a-zA-Z]{11}$/";
        $mkno           = I('post.mkno');
        $lang           = I('post.lang','zh-cn');
        $MKinfo         = $this->MKinfo;
        $SNM            = $MKinfo['tname']; //"20";
        $SID            = $MKinfo['ssid'];  //"美国加州";

        //检验是否为MKNO或者STNO  2017-08-08 jie
        vendor('Hprose.HproseHttpClient');
        $client = new \HproseHttpClient(C('WAPIURL').'/Mkil');       //加载
        $mkno = $client->check_MKNO($mkno);     //调用

        if($mkno['state'] == 'no'){
            $data['mkno']    = trim(I('post.mkno'));
            $data['status']  = '0';             //状态
            $data['code']    = '0';
            $data['codestr'] = $mkno['msg'];     //提示信息
            $this->ajaxReturn($data);
            exit;
        }
        // 2017-08-08 End

        if(preg_match($mkno_match,$mkno) != true){
            $data['mkno'] = trim($mkno);
            $data['status'] = '0';             //状态
            $data['code'] = '0';
            $data['codestr'] = '请使用正常的美快单号';     //提示信息
            $this->ajaxReturn($data);
            exit;
        }
        
        //以下未完成 man 20150730
        // 列入二维数组中以便发送
        $toMKIL = array(
            array(
                'MKNO'      => $mkno,
                'tranid'    => '0',
                'transit'   => $SID,
                'tranNum'   => 'MKILBack',
                'mStr1'     => $SNM,
            ),
        );

        $KD     = "MKILLOG";            //用于确认是哪个类型的JSON
        $CID    = "2";                 //为发送资料方在MK中的ID，美快暂为2  


        // $newJSON = new \Org\MK\JSON; 
        $newJSON = new \Org\MK\JSON(C('jconf'));
        $newMsg = $newJSON->build($KD,$CID,$SID,$SNM,$toMKIL);

        $post_data = array("MKIL"=>$newMsg);
        //$curl_url = "api.megao.us/api/PushWeigh";
        $curl_url = C('PDA_URL')."/LogsBack";
        $jas = $newJSON->post(0,$curl_url,$post_data,$lang);

        //dump($jas);
        $log = $jas['LOG'];

        $data['mkno'] = $log['0']['MKNO'];       //美快单号
        //当Code=1，表示成功返回，其它为错误  //当KD值有误时，Code为3
        if($jas['Code'] != '1' || $jas['KD'] != 'MKILLOG'){
            $data['status']     = '0';             //状态
            $data['code']       = $jas['Code'];
            $data['codestr']    = $jas['Error']. '与错误编码对应的错误信息';     //当Code不等于1时，Error这里显示出错的文字说明
            //dump($data);
            $this->ajaxReturn($data);
            exit;
        }
        //CID固定为2，2表示国际物流自身使用的appID,appKey
        if($jas['CID'] != '2'){
            $data['status']     = '0';             //状态
            $data['code']       = $jas['CID'];
            $data['codestr']    = 'CID值不一致';     //当Code不等于1时，Error这里显示出错的文字说明
            //dump($data);
            $this->ajaxReturn($data);
            exit;
        }

        // Success: 回传是否保存成功, LOGCODE: 1表示成功
        if($log['0']['Success'] == 'true' && $log['0']['LOGCODE'] == '1'){
            $data['status']     = '1';             //状态
            $data['code']       = '1';       //LOGCODE为错误编码作为判断状态
            $data['codestr']    = $log['0']['LOGSTR']. '与错误编码对应的错误信息';;     //LOGSTR：与错误编码对应的错误信息
            //dump($data);
            $this->ajaxReturn($data);
            exit;
        }else{
            $data['status']     = '0';              //状态
            $data['code']       = '0';       //LOGCODE为错误编码作为判断状态
            $data['codestr']    = $log['0']['LOGSTR'] . '与错误编码对应的错误信息else';     //LOGSTR：与错误编码对应的错误信息
            //dump($data);
            $this->ajaxReturn($data);
            exit;
        }
    }
}