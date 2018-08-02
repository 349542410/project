<?php
namespace Org\MK;
class JSON {
	protected $config = array(
		'PREFIX'			=> 'MKIL',
		'datetime'			=>  '0',  // 0表示读取当前时间
		'KD'				=> 'toMM',
        'debugs'            => false,
        'appID'             => '', //ID,Key 180319Man新增，为了农户端无需访问数据库取得
        'Key'               => '',
    );
    private $jsarray;

    //180319增加new时传入conf
    public function __construct($config = array()){
        $this->config   =   array_merge($this->config, $config);
    }

    //直接读取POST的字串，生成数组
    //暂时使用这个POST格式，日后再改为HTTP_RAW_POST_DATA 
    public function get(){
    	if(!isset($_POST["MKIL"])) return 0;
    	$js = trim($_POST["MKIL"]);
    	if($js=='') return 0;
    	//return json_decode('{"dsf":"sde","ew":[{"dee":"po"},{"dee":"pe"}]}',true);
    	$this->jsarray	= json_decode($js,true);
    	if(!is_array($this->jsarray)) return 0;
        //if(!$this->compareMD5()) return 2; //暂不检查
    	return $this->jsarray;
    }
    public function str2array($str){
    	if(trim($str)=='') return 0;
		$jsarray	= json_decode($str,true);
    	if($jsarray=='') return 0;
    	return $jsarray;
    }
    public function array2jsonstr($data){

    }
    //校验文件是否正确
    protected function compareMD5(){
        
        $id         = $this->jsarray["CID"];
        $dt         = $this->jsarray["STM"];
        $kd         = $this->jsarray["KD"];
        $m5         = $this->jsarray["CMD5"];

        $mcfg   = array(
            'CID'               => $id,
            'datetime'          => $dt, 
            'KD'                => $kd,         
        );
        //echo 'J-';
        //print_r($mcfg);
        //echo '-J';
        $md5s   = new \Org\MK\AuthMD5($mcfg);
        return $md5s->compare($m5);
    }
    //生成返回的JSON
    public function respons($jsKD,$jsCID,$LOG=null,$ecode='1',$etxt='Done'){
        $mcfg   = array(
            'KD'                => $jsKD,
            'CID'               => $jsCID,
        );
        $md5s   = new \Org\MK\AuthMD5($mcfg);
        $CMD5   = $md5s->create();
        $data   = array(
                "KD"    => $jsKD,
                "CID"   => $jsCID,
                "Code"  => $ecode,
                "Error" => ($etxt=='Done')?L("Done"):$etxt,//L($etxt),
                "CMD5"  => $CMD5["CMD5"],
                "STM"   => $CMD5["STM"],
                "LOG"   => $LOG,
            );
        return json_encode($data);
    }
    //生成发送的JSON 2015-03-18
    public function build($jsKD,$jsCID,$jsSID,$jsSNM,$toMKIL,$Operate){
        //ID,Key 180319Man新增，为了客户端无需访问数据库取得
        $appID      = $this->config['appID'];
        $Key        = $this->config['Key'];

        $mcfg   = array(
            'KD'                => $jsKD,
            'CID'               => $jsCID,
            'appID'             => $appID, 
            'Key'               => $Key,            
        );
        $md5s   = new \Org\MK\AuthMD5($mcfg);
        $CMD5   = $md5s->create();
        $data   = array(
                "KD"    => $jsKD,
                "CID"   => $jsCID,
                "SID"   => $jsSID,
                "SNM"   => $jsSNM,
                "CMD5"  => $CMD5["CMD5"],
                "STM"   => $CMD5["STM"],
                "toMKIL"=> $toMKIL,
            );
        if($Operate){
            $data['Operate'] = $Operate;
        }
        return json_encode($data);
    }
    //生成发送的JSON，返回验证MD5
    public function jsa($str){
        $this->jsarray = json_decode($str,true);
        return $this->compareMD5();
    }
    public function post($ssl = '',$url,$postfields,$lang){
        $post_data = '';
        if(!$lang) $lang = 'en-us';
        $lang      = '?l='.$lang;
        $url       = $url.$lang;
        \Think\Log::write('test--转发快递发送' . json_encode($postfields, 320));
        \Think\Log::write('test--转发快递URL' . $url);
        foreach($postfields as $key=>$value){
            $post_data .="$key=".urlencode($value)."&";}
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL,$url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        /*curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(  
            'Content-Type: application/x-www-form-urlencoded; charset=utf-8',  
            'Content-Length: ' . strlen($data_string))  
        );*/
        //指定post数据
        curl_setopt($ch, CURLOPT_POST, true);
        //添加变量
        curl_setopt($ch, CURLOPT_POSTFIELDS, substr($post_data,0,-1));
        $output = curl_exec($ch);
        $httpStatusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        curl_close($ch);
        \Think\Log::write('test--转发快递内容' . json_encode($output, 320));
        $output = trim($output);
        $bi = strpos($output,'{');
        $output = substr($output,$bi);
        //echo $output;
        \Think\Log::write('test--转发快递内容http请求返回状态' . $httpStatusCode);
        if($httpStatusCode==200){
            $jsa            = json_decode($output,true);
            return $jsa;
        }
    }
    //将申通单号发至kd100
    public function postkd($no,$kdurl,$callback){
        if(strlen($no)<10){
            return false;
        }

    }
}