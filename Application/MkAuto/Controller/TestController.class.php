<?php
/**
 * 测试用，没其他用途
 */
namespace MkAuto\Controller;
use Think\Controller;
class TestController extends Controller{

    public function test(){
        echo 1;exit();
        $js = [1];
        \Think\Log::write( 'API接收MKNO' . json_encode($js, 320));
    }
    private function num_to_change($n)
    {
        $num = floatval($n) * 1000;
        $str = substr($num, (strlen($num) - 1), 1);

        if ($str > 0) {
            $num = floatval($num) - floatval($str);
            $num = floatval($num) + 10;
            $num = sprintf("%.2f", floatval($num) / 1000);
            return $num;
        } else {
            return sprintf("%.2f", floatval($n));
        }
    }
    public function testInfo(){
        echo $rmb = $this->num_to_change(floatval(1) * floatval(0.01));
    }
    // 根据运单号 (审单)推送节点 给中通
    public function toPush(){
        vendor('Hprose.HproseHttpClient');
        $client = new \HproseHttpClient(C('RAPIURL').'/Test');

        $res = $client->toPush();
        dump($res);
    }

    public function extra_step(){
        vendor('Hprose.HproseHttpClient');
        $client = new \HproseHttpClient(C('RAPIURL').'/AutoReceive');



        $id         = '13059';//($info['id']) ? trim($info['id']) : '';//tran_list.id
        $new_weight = '1.30';//($info['new_weight']) ? trim($info['new_weight']) : '';//最新称重重量

        if(empty($id) || empty($new_weight)){
            return array('state'=>'no','msg'=>'缺少必要参数', 'lng'=>'lack_paramer');
        }

        $res = $client->_extra_step($id, $new_weight);
        dump($res);
    }

    public function check_info_same(){
        vendor('Hprose.HproseHttpClient');
        $client = new \HproseHttpClient(C('RAPIURL').'/Mkil');

        $id         = '13063';//($info['id']) ? trim($info['id']) : '';//tran_list.id
        $new_weight = 'MK883059445US';//($info['new_weight']) ? trim($info['new_weight']) : '';//最新称重重量

        if(empty($id) || empty($new_weight)){
            return array('state'=>'no','msg'=>'缺少必要参数', 'lng'=>'lack_paramer');
        }

        $res = $client->_check_info_same($id, $new_weight);
        dump($res);
    }

    public function login(){
        $url = 'http://erpapi.app.megao.hk:83/MkilErp/Transit';
        $json = '{"KD":"toMKIL","CID":"2","SID":"20","CMD5":"fc60e6bdcc5311037ca33c8c4137e007","STM":"20161011080139","LAN":"zh-cn","toMKIL":[{"MKNO":"MK881000469US","TransitNo":"TSF086","Weight":"0.5","place":"美国仓(北加州)"}]}';

        $arr = array('MKIL'=>$json);
        $HTTP = new \Org\MK\HTTP();
        $result = $HTTP->post($url,$arr);
        // $res = $this->posturl($url,$json);
        $arr = json_decode($result,true);
        dump($arr);
    }

    public function tt(){
        // $n = 3.14462;
        // echo 'round 0.35：'.round('0.35',2);echo '<br>';
        // echo '原计算结果：'.$n;echo '<br>';
        // echo '只round：'.round($n, 2);echo '<br>';
        // echo '只sprintf：'.sprintf("%.2f",$n);echo '<br>';
        // echo '先round再sprintf：'.sprintf("%.2f", round($n, 2));echo '<br>';
        // echo 'number_format：';dump(number_format($n,2,'.',''));
        // die;
            // $DataNote               = new \Libm\DataNotes\DataNote();
            // $DataNote->RequestData  = 'test';
            // $DataNote->ResponseData = 'hello';
            // $DataNote->save_dir     = C('AutoSys_Set.Error_Notes');
            // $DataNote->file_name    = C('AutoSys_Set.AutoPrintSysLogs');
            // $DataNote->save();
            // die;
        // $a = '3.12';
        // $b = '3.14';
        // dump(abs(bcsub($a, $b, 2))).'<br>';  // -3.7660
        // if(abs(bcsub($a, $b, 2)) > number_format(0.01,2,'.','')){
        //     echo '超出误差';
        // }else{
        //     echo '通过';
        // }
        // die;
        $js = '{"id":"13180","new_weight":"3.90","new_cost":"14.64","new_freight":"14.64","new_discount":"0","uid":"47","xml":{"KD":"toMKIL","CID":"1","SID":"20","CMD5":"cbb27b92877c34ef374943536ae81b95","STM":"20180504081256","LAN":"zh-cn","toMKIL":[{"MKNO":"MK883061925US","TransitNo":"MKIL","Weight":"3.90","place":"LAX","terminal_code":"3ACFD8C9-CD36-C971-D6E2-986D6DE5BF03","operatorId":"47","operatorName":"张三"}]}}';
        // $arr = array(
        //     "id"=>"13021",
        //     "new_weight"=>"1.40",
        //     "new_cost"=>"4.04",
        //     "new_freight"=>"4.04",
        //     "new_discount"=>"0.66",
        //     "xml"=>array(
        //         'KD'=>'toMKIL',
        //         'CID'=>'1',
        //         'SID'=>'20',
        //         'CMD5'=>'fa057a5d6d4d7e6b9627704afaaa411d',
        //         'STM'=>'20180323091300',
        //         'LAN'=>'zh-cn',
        //         'toMKIL'=>array(
        //             '0'=>array(
        //                 'MKNO'=>'MK883037351US',
        //                 'TransitNo'=>'MKIL',
        //                 'Weight'=>'1.40',
        //                 'place'=>'T1',
        //                 'terminal_code'=>'3ACFD8C9-CD36-C971-D6E2-986D6DE5BF03',
        //                 'operatorId'=>'47',
        //                 'operatorName'=>'张三',
        //             ),
        //         ),
        //     ),
        // );
        // $js = json_encode($arr);
        $info = json_decode($js,true);
        // dump($info);die;
        // return $info;
        $id           = (isset($info['id'])) ? trim($info['id']) : '';//tran_list.id
        $operator_id  = (isset($info['uid'])) ? trim($info['uid']) : '';//操作人id
        $new_weight   = (isset($info['new_weight'])) ? sprintf("%.2f", trim($info['new_weight'])) : '';//最新称重重量
        $new_cost     = (isset($info['new_cost'])) ? sprintf("%.2f", trim($info['new_cost'])) : '';//最新消费金额
        $new_freight  = (isset($info['new_freight'])) ? sprintf("%.2f", trim($info['new_freight'])) : '';//最新运费
        $new_discount = (isset($info['new_discount'])) ? sprintf("%.2f", trim($info['new_discount'])) : '';//最新优惠金额
        $xml          = (isset($info['xml'])) ? $info['xml'] : '';//揽收报文  base64加密的json报文

        if($id == '' || $operator_id == '' || $new_weight == '' || $new_cost == '' || $new_freight == '' || $new_discount == '' || !is_array($xml)){
            return array('state'=>'no','msg'=>'缺少必要参数', 'lng'=>'lack_paramer');
        }

        // 校验 揽收报文中的重量 是否与最新重量 一致
        if($xml['toMKIL']['0']['Weight'] != $new_weight){
            return array('state'=>'no','msg'=>'重量参数不一致', 'lng'=>'weight_not_same');
        }

        vendor('Hprose.HproseHttpClient');
        $client = new \HproseHttpClient(C('RAPIURL').'/AutoReceive');

        $res = $client->_extra_step($info);
        dump($res);
    }
    function posturl($url,$post_data){
        //通过curl函数发送
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_URL,$url);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);

        //当CURLOPT_RETURNTRANSFER设置为1时，如果成功只将结果返回，不自动输出返回的内容。如果失败返回FALSE；
        //若不使用这个选项：如果成功只返回TRUE，自动输出返回的内容。如果失败返回FALSE
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        $result = curl_exec($ch);
        curl_close($ch);
        return $result;
    }

    public function dd(){
        $json = '{"id":"699294","new_weight":"2.60","new_cost":"13.80","new_freight":"13.80","new_discount":"1.80","uid":"43","xml":{"KD":"toMKIL","CID":"1","SID":"20","CMD5":"0d0a41e00f2f266b494ed40e1d87948f","STM":"20180403085132","LAN":"zh-cn","toMKIL":[{"MKNO":"MK883434164US","TransitNo":"MKIL","Weight":"2.60","place":"T1","terminal_code":"9510A424-ECFA-7530-8652-932739C0EEA9","operatorId":"43","operatorName":"rong"}]}}';
        $arr = json_decode($json,true);
        // dump($arr);die;
        vendor('Hprose.HproseHttpClient');
        $client = new \HproseHttpClient(C('RAPIURL').'/AutoReceive');
        $res = $client->_extra_step($arr);
        dump($res);
    }

    public function ff(){
        $file_name = 'AutoReceiveLogs';
        $a = explode('.', $file_name);
        dump($a);
        $file_suffix = strtolower(array_pop(explode('.', $file_name)));//获取文件名的后缀

        if($file_suffix != 'txt'){
            $file_suffix = 'txt';
        }
        
        $file_prefix = array_shift(explode('.', $file_name));//获取文件名的前缀

        $file_prefix .= '_'.date('Ymd');// 前缀拼接上当前日期

        $file_name = $file_prefix.".".$file_suffix;

        dump($file_suffix);
        dump($file_prefix);
        dump($file_name);
    }

    public function test2(){
        $a = 10;
        $b = 1;
        $c = 3;
        $d = sprintf("%.2f", ($b - $c));
        echo sprintf("%.2f", ($a - $d));
    }

    public function dd2(){

        $json = '{"id":"11352","MKNO":"MK883409970US","STNO":"MK883409970US","status":200,"time":"2018-04-20 09:39:58","terminal_code":"FE738727-ECAD-4AA7-EFAD-55D5D72182CB","uid":"43"}';
        $info = json_decode($json, true);

        $id            = $info['id'];       //订单ID
        $status        = $info['status'];   //打印状态
        $time          = $info['time']; //打印时间
        $MKNO          = $info['MKNO']; //MKNO
        $STNO          = $info['STNO']; //STNO
        $terminal_code = $info['terminal_code'];// 终端编号  20171030 jie
        $operator_id   = $info['uid'];// 操作人id

        // dump($arr);die;
        vendor('Hprose.HproseHttpClient');
        $client = new \HproseHttpClient(C('RAPIURL').'/AutoPrintSys');
        $res = $client->_step_three($id, $status, $time, $MKNO, $STNO, $terminal_code, $operator_id);
        dump($res);
    }

    public function dd3(){
        $no  = I('no');
        vendor('Hprose.HproseHttpClient');
        $client = new \HproseHttpClient(C('RAPIURL').'/Test');
        $res = $client->terminal_find($no);
        dump($res);

    }

    public function dd4(){
        vendor('Hprose.HproseHttpClient');
        $client = new \HproseHttpClient(C('RAPIURL').'/Test');
        $res = $client->terminal_list();
        dump($res);

    }

    /**
     *  测试登陆接口
     */
    public function testLogin()
    {
        $url = 'http://mkauto.mk.cc/AutoReceive/test?info=';
        $data = [
            'uname' => 'admin',
            'ucode' => md5('admin'),
            'key' => md5(base64_encode('admin'.md5('admin').C('MkReceive_Set.MkWl2Key'))),
        ];
        $arrays = [
            'type' => 'login',
            'data' => $data,
        ];
        $json = base64_encode(urlencode(json_encode($arrays)));
        $url = $url . $json;
        $result = file_get_contents($url);
        dump($result);
    }

    /**
     *  测试接口
     */
    public function prints()
    {
        /*$result = 'JTdiJTIyJTc0JTc5JTcwJTY1JTIyJTNhJTIyJTY5JTZlJTY0JTY1JTc4JTIyJTJjJTIyJTY0JTYxJTc0JTYxJTIyJTNhJTdiJTIyJTRkJTRiJTRlJTRmJTIyJTNhJTIyJTM0JTMzJTMxJTM1JTM0JTMwJTMwJTMyJTMyJTMyJTMxJTMyJTMzJTM1JTIyJTdkJTdk
';

        dump(json_decode(urldecode(base64_decode($result)),true));die();*/
        $url = 'http://mkauto.mk.cc/AutoReceive/console?info=';
        $url = 'http://mkauto.test.meiquick.com/AutoReceive/console';
        $data = [
            'id' => '1326',
            'uid' => 2,
            'status' => 200,
            'time' => '2017-5-5 0:0:0',
            'terminal_code' => '3ACFD8C9-CD36-C971-D6E2-986D6DE5BF0A',
        ];
        $arrays = [
            'type' => 'step_three',
            'data' => $data,
        ];
        $json = base64_encode(urlencode(json_encode($arrays)));

        //$url = $url . $json;
        $result = file_get_contents($url);
        dump($json);
    }


}