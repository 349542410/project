<?php
/**
 * PDA
 * 称重
 */
namespace MkAuto\Controller;
use Think\Controller;
header('Content-Type:text/html; charset=utf-8');
class WeighingController extends BaseController {

    public function _initialize(){
        
        /*//根据session检验登陆权限
        $value  = session();
        $MKinfo = $value['MKinfo'];
        $this->MKinfo = $MKinfo;       //全局

        if($MKinfo['usertype'] != '10'){
            $this->error('无权限进入或未登陆',U('Index/index'));
        }*/

        $this->usertype = 10;
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
        $MKinfo = $this->MKinfo;
        $tname  = $MKinfo['tname'];
        $ssid    = $MKinfo['ssid'];

        // sleep(1);

        $mkno = trim(I('post.mkno'));     //美快单号
        $weight = trim(I('post.weight'));     //称重重量

        // $mkno = 'MK881000035US';
        // $weight = '2222.6';

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

        //$this->ajaxReturn($data);exit;
        //var_dump(I('post.'));exit;
        //$mkno = 'MK881000038US';    //单号
        //$weight = '1225.0';         //称重重量
        $lang = I('post.weight','zh-cn');            //语言
        $mkno_match = "/^MK[0-9a-zA-Z]{11}$/";
        // $mkno_match = "/^MK\d{9}[A-Za-z0-9]{2}$/";
        $weight_match = "/^(-?\d+)(\.\d{1})?$/";

        //单号格式、称重重量格式匹配，不匹配则返回错误
        if(preg_match($mkno_match,$mkno) != true || preg_match($weight_match,$weight) != true){
            $data['mkno'] = trim($mkno);
            $data['status'] = '0';             //状态
            $data['code'] = '0';
            $data['codestr'] = '错误的客户端，请重新安装';     //提示信息
            //dump($data);
            $this->ajaxReturn($data);
            exit;
        }

        // 列入二维数组中以便发送
        $toMKIL = array(
            array(
                'MKNO'=>$mkno,
                'weight'=>$weight,
            ),
        );
        
        $KD = "MKILWeigh";          //用于确认是哪个类型的JSON
        $CID = "2";                 //2表示国际物流自身使用的appID,appKey

        $SID = $ssid;                //"20";         //150702改为以登录者的真实名为准
        $SNM = $tname;              //"美国加州";
        
        // $newJSON = new \Org\MK\JSON; 
        $newJSON = new \Org\MK\JSON(C('jconf'));
        $newMsg = $newJSON->build($KD,$CID,$SID,$SNM,$toMKIL);
        //var_dump($newMsg);

        $post_data = array("MKIL"=>$newMsg);        //发送的JSON数据
        //$curl_url = "api.megao.us/api/PushWeigh";
        $curl_url = C('PDA_URL')."/PushWeigh";      //发送到能生成二维数组的地址
        $jas = $newJSON->post(0,$curl_url,$post_data,$lang);    //返回的结果是二维数组数据
        //echo '<br>';
        //echo "返回数据:<br>";
        //var_dump($jas);
        //echo '<br>';
        $log = $jas['LOG'];

        $data['mkno'] = $log['0']['MKNO'];       //美快单号
        //当Code=1，表示成功返回，其它为错误  //当KD值有误时，Code为3
        if($jas['Code'] != '1' || $jas['KD'] != 'MKILWeigh'){
            $data['status'] = '0';             //状态
            $data['code'] = $jas['Code'];
            $data['codestr'] = $jas['Error'];     //提示信息
            //dump($data);
            $this->ajaxReturn($data);
            exit;
        }
        //CID固定为2，2表示国际物流自身使用的appID,appKey
        if($jas['CID'] != '2'){
            $data['status'] = '0';             //状态
            $data['code'] = $jas['CID'];
            $data['codestr'] = 'CID值不一致';     //提示信息
            //dump($data);
            $this->ajaxReturn($data);
            exit;
        }
        // dump($log['0']['Success']);
        // echo 'dd'.strtolower($log['0']['Success']);exit;
        // dump($log['0']['Success']);
        // Success: 回传是否保存成功, LOGCODE: 1表示成功
        if($log['0']['Success'] == 'true' && $log['0']['LOGCODE'] == '1'){

            // 读取称重短信发送开关设置
            $WeightSwitch = C('WMD.WeightSwitch');
            // 如果开关为开，则执行短信发送
            if($WeightSwitch == 'on'){

                $cont = C('WMD.CONTENT');        //发送内容
                $type = 'Weighing';     //称重

                // vendor('Hprose.HproseHttpClient');  //2017-08-08 jie 上面加载了，这里就不需要重复加载
                $client = new \HproseHttpClient(C('WAPIURL').'/Send');       //加载短信发送功能Api

                $client->sendSMS($mkno,$tname,$type,$cont);     //调用发送方法
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


}