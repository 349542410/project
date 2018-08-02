<?php
/**
 * PDA
 * 中转单发
 */
namespace MkAuto\Controller;
use Think\Controller;
//header('Content-Type:text/html; charset=utf-8');
class CommunicateController extends BaseController {
    public function _initialize(){
        $this->usertype = 20;
        parent::_initialize();
    }

    public function index(){
        $MKinfo = $this->MKinfo;
        $tname  = $MKinfo['tname'];
        $ssid    = $MKinfo['ssid'];
        $mobile  = $MKinfo['phone'];

        $this->display();
    }

    public function Save(){
        // echo '111';die;
        $MKinfo = $this->MKinfo;
        $tname  = $MKinfo['tname'];
        $ssid    = $MKinfo['ssid'];

        //收到 JSON {mkno='MK***'(13位)}
        $mkno = trim(I('post.mkno'));        //美快单号

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

        //$mkno = 'MK881000046US';
        $lang = 'zh-cn';                //操作语言
        $mkno_match = "/^MK[0-9a-zA-Z]{11}$/";
        //单号格式匹配，不匹配则返回错误
        if(preg_match($mkno_match,$mkno) != true){
            $data['mkno'] = trim($mkno);
            $data['status'] = '0';             //状态
            $data['code'] = '0';
            $data['codestr'] = '错误的客户端，请重新安装';     //提示信息
            //dump($data);
            $this->ajaxReturn($data);
            exit;
        }
        //echo $dttt = date('H:i:s');
        // 列入二维数组中以便发送
        $toMKIL = array(
            array(
                'MKNO'=>$mkno,
            ),
        );
        $KD = "MKILSHIP";           //用于确认是哪个类型的JSON
        $CID = "2";                 //2表示国际物流自身使用的appID,appKey
        //使用登录的ID与名称 20150706 Man
        $SID = $ssid;                //"40";
        $SNM = $tname;              //"香港中转仓";
        $Operate = '60';
        // $newJSON = new \Org\MK\JSON; 
        $newJSON = new \Org\MK\JSON(C('jconf'));
        $newMsg = $newJSON->build($KD,$CID,$SID,$SNM,$toMKIL,$Operate);     //生成JSON
        //var_dump($newMsg);

        $post_data = array("MKIL"=>$newMsg);    //发送的JSON数据
        $curl_url = C('PDA_URL')."/LogsShip";   //发送到能生成二维数组的地址

        $jn = $newJSON->post(0,$curl_url,$post_data,$lang);     //返回的结果是二维数组数据

        //echo '<br>';
        //echo $dss = date('H:i:s');
        //echo "返回数据:<br>";
        //var_dump($jn);exit;
        //echo '<br>';
        $log = $jn['LOG'];
        $data['mkno'] = $log['0']['MKNO'];       //美快单号

        //当Code=1，表示成功返回，其它为错误  //当KD值有误时，Code为3
        if($jn['Code'] != '1' || $jn['KD'] != 'MKILSHIP'){
            $data['status'] = '0';             //状态
            $data['code'] = $jn['Code'];
            $data['codestr'] = $jn['Error'];//'错误的客户端，请重新安装2';     //提示信息
            //dump($data);
            $this->ajaxReturn($data);
            exit;
        }
        //CID固定为2，2表示国际物流自身使用的appID,appKey
        if($jn['CID'] != '2'){
            $data['status'] = '0';             //状态
            $data['code'] = $jn['CID'];
            $data['codestr'] = 'CID值不一致';     //提示信息
            //dump($data);
            $this->ajaxReturn($data);
            exit;
        }

        // Success: 回传是否保存成功； LOGCODE: 1表示成功
        if($log['0']['Success'] == 'true' && $log['0']['LOGCODE'] == '1'){
            //echo '11';
            $data['status'] = '1';             //状态
            $data['code']   = '1';       //LOGCODE为错误编码
            if($log['0']['TranKd']==1){
                $data['codestr'] = '申通单无需打印，仍需稍候';     //LOGSTR：与错误编码对应的错误信息
            }else{
                $data['codestr'] = '打印中，请稍候';     //LOGSTR：与错误编码对应的错误信息
            }
            
            $data['TranKd']     = $log['0']['TranKd'];
            //Man20150429增加申通号显示
            $data['STNO']       = $log['0']['STNO'];   
            $data['log']        = base64_encode(json_encode($log[0]));     //json加密并使用 MIME base64 对数据进行编码
            //dump($data);
            $this->ajaxReturn($data);
            exit;
        }else{
            //echo '22';
            $data['status'] = '0';              //状态
            $data['code']   = '0';       //LOGCODE为错误编码
            $data['codestr'] = $log['0']['LOGSTR'];     //LOGSTR：与错误编码对应的错误信息
            //dump($data);
            $this->ajaxReturn($data);
            exit;
        }
    }

    public function senderror(){
        //暂无需处理
    }

    public function sendDone(){
        //收到 JSON {mkno='MK***'(13位),code='0成功，不是0操作senderror(收到JSON)后直接退出',{'commno'='返回的单号'}}
        $MKinfo = $this->MKinfo;
        $tname  = $MKinfo['tname'];
        $ssid    = $MKinfo['ssid'];
                
        $mkno = trim(I('post.mkno'));
        $EXPNO = trim(I('post.EXPNO'));
        $TranKd = trim(I('post.TranKd'));

        //检验是否为MKNO或者STNO  2017-08-08 jie
        vendor('Hprose.HproseHttpClient');
        $client = new \HproseHttpClient(C('WAPIURL').'/Mkil');       //加载
        $mkno = $client->check_MKNO($mkno);     //调用

        if ($mkno['state'] == 'no') {
            $data['mkno'] = trim(I('post.mkno'));
            $data['status'] = '0';             //状态
            $data['code'] = '0';
            $data['codestr'] = $mkno['msg'];     //提示信息
            $this->ajaxReturn($data);
            exit;
        }
        // 2017-08-08 End

        // $mkno = 'MK881000045US';
        // $EXPNO = '2365988522';
        // $TranKd = '1';
        $lang = 'zh-cn';
        $mkno_match = "/^MK[0-9a-zA-Z]{11}$/";
        //单号格式匹配
        if(preg_match($mkno_match,$mkno) != true){
            $data['mkno'] = trim($mkno);
            $data['status'] = '0';             //状态
            $data['code'] = '0';
            $data['codestr'] = '错误的客户端，请重新安装';     //提示信息
            //dump($data);
            $this->ajaxReturn($data);
            exit;
        }

        // //查找单号的收件人信息
        // vendor('Hprose.HproseHttpClient');
        // $client = new \HproseHttpClient(C('WAPIURL').'/Send');       //加载短信发送功能Api

        // $reInfo = $client->getInfo($mkno);
        // // $reInfo = M('TranList')->where(array('MKNO'=>$mkno))->find();
        // $mobile  = $reInfo['reTel'];
        // $receiver = $reInfo['receiver'];

        //根据$TranKd的值判断$EXPNM的所属快递类型
        switch($TranKd){
            case '0':
            $EXPNM = '香港EMS';
            break;
            case '1':
            $EXPNM = '申通';
            break;
        }
        //拼接发送的资料
        $toMKIL = array(
            array(
                'MKNO'=>$mkno,
                'EXPNO'=>$EXPNO,
                'EXPNM'=>$EXPNM,
            ),
        );
        
        $KD = "MKILSHIP";           //用于确认是哪个类型的JSON
        $CID = "2";                 //固定为2，2表示国际物流自身使用的appID,appKey

        //使用登录者的ID与名称 20150706 Man
        $SID = $ssid;                //40";
        $SNM = $tname;              //"香港中转仓";

        $arr = array('0','1');      
        //判断$TranKd的值是否为数组$arr里面的其中一个，以此为$Operate赋值
        if(in_array($TranKd,$arr,true)){
            $Operate = '200'; 
        }else if($TranKd == '2'){
            $Operate = '100';
        }
        // $newJSON = new \Org\MK\JSON; 
        $newJSON = new \Org\MK\JSON(C('jconf'));
        $newMsg = $newJSON->build($KD,$CID,$SID,$SNM,$toMKIL,$Operate);     //生成json
        //dump($newMsg);

        $post_data = array("MKIL"=>$newMsg);    //发送的数据
        $curl_url = C('PDA_URL')."/LogsShip";   //发送到url
        $jn = $newJSON->post(0,$curl_url,$post_data,$lang); //把json发送出去，并获取返回的数据（二维数组）
        //echo '<br>';
        //echo "返回数据:<br>";
        //var_dump($jn);exit;
        //echo '<br>';

        $log = $jn['LOG'];


        $data['mkno'] = $log['0']['MKNO'];       //单号
        //当Code=1，表示成功返回，其它为错误  //当KD值有误时，Code为3
        if($jn['Code'] != '1' || $jn['KD'] != 'MKILSHIP'){
            $data['status'] = '0';             //状态
            $data['code'] = $jn['Code'];
            $data['codestr'] = $jn['Error'];     //提示信息
            //dump($data);
            $this->ajaxReturn($data);
            exit;
        }

        //CID固定为2，2表示国际物流自身使用的appID,appKey
        if($jn['CID'] != '2'){
            $data['status'] = '0';             //状态
            $data['code'] = $jn['CID'];
            $data['codestr'] = 'CID值不一致';     //提示信息
            //dump($data);

            $this->ajaxReturn($data);
            exit;
        }


        // Success: 回传是否保存成功, LOGCODE: 1表示成功
        if($log['0']['Success'] == 'true' && $log['0']['LOGCODE'] == '1'){
            
            // 读取中转单发 短信发送开关设置
            $WeightSwitch = C('CMD.CommunicateSwitch');
            // 如果开关为开，则执行短信发送
            if($WeightSwitch == 'on'){
                $type ='Communicate';

                $cont = C('CMD.CONTENT');        //发送内容

                // vendor('Hprose.HproseHttpClient');  //2017-08-08 jie 上面加载了，这里就不需要重复加载
                $client = new \HproseHttpClient(C('WAPIURL').'/Send');       //加载短信发送功能Api

                $client->sendSMS($mkno,$tname,$type,$cont,$EXPNM);     //调用发送方法
            }

            $data['status']  = '1';             //状态
            $data['code']    = '1';       //LOGCODE为错误编码作为判断状态
            $data['codestr'] = $log['0']['LOGSTR'];     //LOGSTR：与错误编码对应的错误信息
            $this->ajaxReturn($data);
            exit;
        }else{
            $data['status']  = '0';              //状态
            $data['code']    = '0';       //LOGCODE为错误编码作为判断状态
            $data['codestr'] = $log['0']['LOGSTR'];     //LOGSTR：与错误编码对应的错误信息
            //dump($data);
            $this->ajaxReturn($data);
            exit;
        }
    }
    public function Save00(){ //20150528返回测试网络状态时的JSON
        $data['st']     = '200';
        $data['str']    = 'OK';
        $this->ajaxReturn($data);
    }
}